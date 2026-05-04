<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderNotification;
use App\Services\BulkSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(protected BulkSmsService $sms) {}

    /* ── Index ── */
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Order::with(['user', 'items'])->latest();

        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(20)->withQueryString();

        $counts = [
            'all'       => Order::when($user->role !== 'admin', fn ($q) => $q->where('user_id', $user->id))->count(),
            'pending'   => Order::where('status', 'pending')->when($user->role !== 'admin', fn ($q) => $q->where('user_id', $user->id))->count(),
            'approved'  => Order::where('status', 'approved')->when($user->role !== 'admin', fn ($q) => $q->where('user_id', $user->id))->count(),
            'rejected'  => Order::where('status', 'rejected')->when($user->role !== 'admin', fn ($q) => $q->where('user_id', $user->id))->count(),
            'delivered' => Order::where('status', 'delivered')->when($user->role !== 'admin', fn ($q) => $q->where('user_id', $user->id))->count(),
        ];

        return view('orders.index', compact('user', 'orders', 'counts'));
    }

    /* ── Create form ── */
    public function create(Request $request)
    {
        $user     = $request->user();
        $products = Product::where('quantity', '>', 0)
                           ->orderBy('name')
                           ->get(['id', 'name', 'category', 'unit', 'selling_price', 'quantity']);

        // Staff/admin can place order on behalf of a customer
        $customers = collect();
        if (in_array($user->role, ['admin', 'staff'], true)) {
            $customers = User::where('role', 'customer')
                ->when($user->role === 'staff', fn ($q) => $q->where('state', $user->state))
                ->orderBy('name')
                ->get(['id', 'name', 'shop_name', 'state']);
        }

        return view('orders.create', compact('user', 'products', 'customers'));
    }

    /* ── AJAX stock check ── */
    public function stockCheck(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->integer('product_id'));
        $qty     = $request->integer('quantity');

        return response()->json([
            'available' => $product->quantity >= $qty,
            'stock'     => $product->quantity,
            'name'      => $product->name,
        ]);
    }

    /* ── AJAX: orders for a customer (used by delivery/payment forms) ── */
    public function ajaxCustomerOrders(Request $request)
    {
        $actor      = $request->user();
        $customerId = $request->integer('customer_id');
        $filter     = $request->input('filter', 'any'); // approved | payable | any

        // Validate the customer exists and staff can only see their state's customers
        $customer = User::where('role', 'customer')
            ->when($actor->role === 'staff', fn ($q) => $q->where('state', $actor->state))
            ->findOrFail($customerId);

        $query = Order::where('user_id', $customer->id);

        if ($filter === 'approved') {
            $query->where('status', 'approved')
                  ->whereDoesntHave('deliveries', fn ($q) => $q->whereIn('status', ['pending', 'approved']));
        } elseif ($filter === 'payable') {
            $query->whereIn('status', ['approved', 'delivered'])
                  ->whereRaw('total_amount > COALESCE((SELECT SUM(p.amount) FROM payments p WHERE p.order_id = orders.id AND p.status = ?), 0)', ['approved']);
        }

        $orders = $query->orderByDesc('created_at')
            ->get(['id', 'order_number', 'total_amount', 'status']);

        return response()->json($orders->map(fn ($o) => [
            'id'           => $o->id,
            'order_number' => $o->order_number,
            'total_amount' => number_format((float)$o->total_amount, 2, '.', ''),
            'remaining'    => number_format($o->remainingAmount(), 2, '.', ''),
            'status'       => $o->status,
        ]));
    }

    /* ── Store ── */
    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|integer|exists:products,id',
            'items.*.quantity'       => 'required|integer|min:1',
            'notes'                  => 'nullable|string|max:1000',
            'customer_id'            => 'nullable|integer|exists:users,id',
        ]);

        // Determine the order owner
        $orderOwner = $user;
        if (in_array($user->role, ['admin', 'staff'], true) && $request->filled('customer_id')) {
            $customer = User::where('role', 'customer')
                ->when($user->role === 'staff', fn ($q) => $q->where('state', $user->state))
                ->findOrFail($request->integer('customer_id'));
            $orderOwner = $customer;
        }

        $rawItems    = $request->input('items');
        $itemRecords = [];
        $totalAmount = 0;

        // Deduplicate: sum quantities if same product appears twice
        $grouped = [];
        foreach ($rawItems as $item) {
            $pid = (int) $item['product_id'];
            if (isset($grouped[$pid])) {
                $grouped[$pid] += (int) $item['quantity'];
            } else {
                $grouped[$pid] = (int) $item['quantity'];
            }
        }

        foreach ($grouped as $productId => $qty) {
            $product   = Product::findOrFail($productId);

            // Validate stock at submission time
            if ($product->quantity < $qty) {
                return back()
                    ->withInput()
                    ->withErrors(['items' => "{$product->name} only has {$product->quantity} unit(s) available (you requested {$qty})."]);
            }

            $subtotal  = round($product->selling_price * $qty, 2);
            $itemRecords[] = [
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'unit_price'   => $product->selling_price,
                'quantity'     => $qty,
                'subtotal'     => $subtotal,
            ];
            $totalAmount += $subtotal;
        }

        $order = DB::transaction(function () use ($user, $orderOwner, $request, $itemRecords, $totalAmount) {
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id'      => $orderOwner->id,
                'notes'        => $request->input('notes'),
                'total_amount' => round($totalAmount, 2),
                'status'       => 'pending',
            ]);

            $order->items()->createMany($itemRecords);

            return $order;
        });

        // Notify admin via SMS
        $admin = User::where('role', 'admin')->whereNotNull('phone')->first();
        if ($admin && $admin->phone) {
            $count   = count($itemRecords);
            $placedBy = $user->id !== $orderOwner->id ? " (placed by {$user->name})" : '';
            $message = "New order {$order->order_number} placed for {$orderOwner->name}{$placedBy}. "
                     . "{$count} item(s). Total: NGN " . number_format($totalAmount, 2)
                     . ". Login to approve.";
            $this->sms->send($admin->phone, $message);
        }

        // Notify admin via database + email
        $adminUser = User::where('role', 'admin')->first();
        if ($adminUser) {
            $adminUser->notify(new OrderNotification('placed', $order));
        }

        return redirect()->route('orders.show', $order)
                         ->with('status', "Order {$order->order_number} placed. Awaiting admin approval.");
    }

    /* ── Show ── */
    public function show(Request $request, Order $order)
    {
        $user = $request->user();

        if ($user->role !== 'admin' && $order->user_id !== $user->id) {
            abort(403);
        }

        $order->load(['user', 'items', 'approvedBy', 'payments', 'deliveries.staff']);

        return view('orders.show', compact('user', 'order'));
    }

    /* ── Approve (admin only) ── */
    public function approve(Request $request, Order $order)
    {
        $user = $request->user();
        if ($user->role !== 'admin') abort(403);

        if ($order->status !== 'pending') {
            return redirect()->route('orders.show', $order)
                             ->with('error', 'Only pending orders can be approved.');
        }

        $order->load('items');

        // ── Stock availability check ──
        $shortfalls = [];
        foreach ($order->items as $item) {
            if ($item->product_id === null) {
                // Product was deleted after order was placed
                $shortfalls[] = "{$item->product_name} is no longer in the inventory.";
                continue;
            }

            $product = Product::where('id', $item->product_id)->first();
            if (!$product || $product->quantity < $item->quantity) {
                $available = $product ? $product->quantity : 0;
                $shortfalls[] = "{$item->product_name}: need {$item->quantity}, only {$available} in stock.";
            }
        }

        if (!empty($shortfalls)) {
            $message = 'Cannot approve - insufficient stock: ' . implode(' | ', $shortfalls);
            return redirect()->route('orders.show', $order)->with('error', $message);
        }

        // ── Deduct stock and approve inside a transaction ──
        DB::transaction(function () use ($order, $user) {
            foreach ($order->items as $item) {
                Product::where('id', $item->product_id)
                       ->lockForUpdate()
                       ->decrement('quantity', $item->quantity);
            }

            $order->update([
                'status'      => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);
        });

        $order->user->notify(new OrderNotification('approved', $order));

        return redirect()->route('orders.show', $order)
                         ->with('status', "Order {$order->order_number} approved and stock deducted.");
    }

    /* ── Reject (admin only) ── */
    public function reject(Request $request, Order $order)
    {
        $user = $request->user();
        if ($user->role !== 'admin') abort(403);

        if ($order->status !== 'pending') {
            return redirect()->route('orders.show', $order)
                             ->with('error', 'Only pending orders can be rejected.');
        }

        $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $order->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        $order->user->notify(new OrderNotification('rejected', $order));

        return redirect()->route('orders.show', $order)
                         ->with('status', "Order {$order->order_number} rejected.");
    }

    /* ── Mark Delivered (admin only) ── */
    public function deliver(Request $request, Order $order)
    {
        $user = $request->user();
        if ($user->role !== 'admin') abort(403);

        if ($order->status !== 'approved') {
            return redirect()->route('orders.show', $order)
                             ->with('error', 'Only approved orders can be marked as delivered.');
        }

        $order->update(['status' => 'delivered']);

        return redirect()->route('orders.show', $order)
                         ->with('status', "Order {$order->order_number} marked as delivered.");
    }

    /* ── Helpers ── */

    private function generateOrderNumber(): string
    {
        $date  = now()->format('Ymd');
        $count = Order::whereDate('created_at', today())->count();
        return 'ORD-' . $date . '-' . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
    }
}
