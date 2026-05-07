<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /* ── Index ── */
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Order::with(['user', 'items', 'payments' => fn ($q) => $q->where('status', 'approved')])->latest();

        if ($user->role === 'staff') {
            $stateCustomerIds = User::where('role', 'customer')
                ->where('state', $user->state)
                ->pluck('id');
            $query->whereIn('user_id', $stateCustomerIds);
        } elseif ($user->role === 'customer') {
            $query->where('user_id', $user->id);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(20)->withQueryString();

        // Reusable scope for counts
        $scoped = function ($q) use ($user) {
            if ($user->role === 'staff') {
                $ids = User::where('role', 'customer')->where('state', $user->state)->pluck('id');
                return $q->whereIn('user_id', $ids);
            }
            if ($user->role === 'customer') {
                return $q->where('user_id', $user->id);
            }
            return $q;
        };

        $counts = [
            'all'       => $scoped(Order::query())->count(),
            'pending'   => $scoped(Order::where('status', 'pending'))->count(),
            'approved'  => $scoped(Order::where('status', 'approved'))->count(),
            'rejected'  => $scoped(Order::where('status', 'rejected'))->count(),
            'delivered' => $scoped(Order::where('status', 'delivered'))->count(),
        ];

        return view('orders.index', compact('user', 'orders', 'counts'));
    }

    /* ── Create form ── */
    public function create(Request $request)
    {
        $user = $request->user();

        // Staff/admin can place order on behalf of a customer
        $customers = collect();
        if ($user->isAdminOrStaff()) {
            $customers = User::where('role', 'customer')
                ->when($user->role === 'staff', fn ($q) => $q->where('state', $user->state))
                ->orderBy('name')
                ->get(['id', 'name', 'shop_name', 'state']);
        }

        $products = Product::orderBy('name')->get(['id', 'name', 'unit', 'selling_price']);

        return view('orders.create', compact('user', 'customers', 'products'));
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
            'items.*.product_name'   => ['required', 'string', 'max:255', Rule::exists('products', 'name')],
            'items.*.quantity'       => 'required|integer|min:1',
            'notes'                  => 'nullable|string|max:1000',
            'customer_id'            => 'nullable|integer|exists:users,id',
        ]);

        // Determine the order owner
        $orderOwner = $user;
        if ($user->isAdminOrStaff() && $request->filled('customer_id')) {
            $customer = User::where('role', 'customer')
                ->when($user->role === 'staff', fn ($q) => $q->where('state', $user->state))
                ->findOrFail($request->integer('customer_id'));
            $orderOwner = $customer;
        }

        $rawItems = $request->input('items');

        // Always use canonical selling_price from DB - ignores any submitted unit_price
        $priceMap = Product::whereIn('name', array_column($rawItems, 'product_name'))
            ->pluck('selling_price', 'name');

        $itemRecords = [];
        $totalAmount = 0;

        foreach ($rawItems as $item) {
            $qty      = (int) $item['quantity'];
            $price    = round((float) ($priceMap[trim($item['product_name'])] ?? 0), 2);
            $subtotal = round($price * $qty, 2);
            $itemRecords[] = [
                'product_id'   => null,
                'product_name' => trim($item['product_name']),
                'unit_price'   => $price,
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
                'status'       => 'approved',
                'approved_by'  => $user->id,
                'approved_at'  => now(),
            ]);

            $order->items()->createMany($itemRecords);

            return $order;
        });

        return redirect()->route('orders.show', $order)
                         ->with('status', "Order {$order->order_number} placed and approved.");
    }

    /* ── Show ── */
    public function show(Request $request, Order $order)
    {
        $user = $request->user();

        if ($user->role === 'customer' && $order->user_id !== $user->id) {
            abort(403);
        }

        if ($user->role === 'staff') {
            $stateCustomerIds = User::where('role', 'customer')
                ->where('state', $user->state)
                ->pluck('id');
            if (!$stateCustomerIds->contains($order->user_id)) {
                abort(403);
            }
        }

        $order->load(['user', 'items', 'approvedBy', 'payments']);

        return view('orders.show', compact('user', 'order'));
    }

    /* ── Approve (admin only) ── */
    public function approve(Request $request, Order $order)
    {
        $user = $request->user();
        if (!$user->isAdmin()) abort(403);

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
        if (!$user->isAdmin()) abort(403);

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
        if (!$user->isAdmin()) abort(403);

        if ($order->status !== 'approved') {
            return redirect()->route('orders.show', $order)
                             ->with('error', 'Only approved orders can be marked as delivered.');
        }

        $order->update(['status' => 'delivered']);

        return redirect()->route('orders.show', $order)
                         ->with('status', "Order {$order->order_number} marked as delivered.");
    }

    /* ── Delete single order (admin/staff) ── */
    public function destroy(Request $request, Order $order)
    {
        $user = $request->user();
        if (!$user->isAdminOrStaff()) abort(403);

        if ($user->role === 'staff') {
            $stateCustomerIds = User::where('role', 'customer')
                ->where('state', $user->state)
                ->pluck('id');
            if (!$stateCustomerIds->contains($order->user_id)) abort(403);
        }

        $orderNumber = $order->order_number;
        $order->items()->delete();
        $order->payments()->delete();
        $order->delete();

        return redirect()->route('orders.index')
                         ->with('status', "Order {$orderNumber} deleted.");
    }

    /* ── Delete all visible orders (admin/staff) ── */
    public function destroyAll(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdminOrStaff()) abort(403);

        if ($user->role === 'staff') {
            $ids    = User::where('role', 'customer')->where('state', $user->state)->pluck('id');
            $orders = Order::whereIn('user_id', $ids)->get();
        } else {
            $orders = Order::all();
        }

        foreach ($orders as $order) {
            $order->items()->delete();
            $order->payments()->delete();
            $order->delete();
        }

        return redirect()->route('orders.index')
                         ->with('status', 'All orders cleared.');
    }

    /* ── Helpers ── */

    private function generateOrderNumber(): string
    {
        $date  = now()->format('Ymd');
        $count = Order::whereDate('created_at', today())->count();
        return 'ORD-' . $date . '-' . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
    }
}
