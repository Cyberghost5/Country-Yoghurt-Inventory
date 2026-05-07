<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\DeliveryAllocation;
use App\Models\Product;
use App\Models\User;
use App\Services\BulkSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DeliveryController extends Controller
{
    /* ── Index ── */
    public function index(Request $request)
    {
        $user  = $request->user();

        if ($user->role === 'customer') {
            // Customers only see deliveries they have an allocation in
            $query = Delivery::with(['staff', 'allocations'])
                ->whereHas('allocations', fn ($q) => $q->where('customer_id', $user->id))
                ->latest();

            if ($status = $request->input('status')) {
                $query->where('status', $status);
            }

            $deliveries = $query->paginate(20)->withQueryString();

            $base = Delivery::whereHas('allocations', fn ($q) => $q->where('customer_id', $user->id));
            $counts = [
                'all'        => (clone $base)->count(),
                'pending'    => (clone $base)->where('status', 'pending')->count(),
                'dispatched' => (clone $base)->where('status', 'dispatched')->count(),
                'completed'  => (clone $base)->where('status', 'completed')->count(),
            ];

            return view('deliveries.index', compact('user', 'deliveries', 'counts'));
        }

        if (!$user->isAdminOrStaff()) abort(403);

        $query = Delivery::with(['staff', 'allocations'])->latest();

        if ($user->role === 'staff') {
            $query->where('staff_id', $user->id);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $deliveries = $query->paginate(20)->withQueryString();

        $base = Delivery::query();
        if ($user->role === 'staff') {
            $base->where('staff_id', $user->id);
        }

        $counts = [
            'all'        => (clone $base)->count(),
            'pending'    => (clone $base)->where('status', 'pending')->count(),
            'dispatched' => (clone $base)->where('status', 'dispatched')->count(),
            'completed'  => (clone $base)->where('status', 'completed')->count(),
        ];

        return view('deliveries.index', compact('user', 'deliveries', 'counts'));
    }

    /* ── Create form ── */
    public function create(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdminOrStaff()) abort(403);

        $customers = User::where('role', 'customer')
            ->when($user->role === 'staff', fn ($q) => $q->where('state', $user->state))
            ->orderBy('name')
            ->get(['id', 'name', 'shop_name', 'state']);

        $products  = Product::orderBy('name')->get(['id', 'name', 'unit', 'selling_price']);

        return view('deliveries.create', compact('user', 'customers', 'products'));
    }

    /* ── Store ── */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdminOrStaff()) abort(403);

        $request->validate([
            'scheduled_at'                              => 'nullable|date',
            'notes'                                     => 'nullable|string|max:1000',
            'customers'                                 => 'required|array|min:1',
            'customers.*.customer_id'                   => 'required|integer|exists:users,id',
            'customers.*.allocation_date'               => 'nullable|date',
            'customers.*.items'                         => 'required|array|min:1',
            'customers.*.items.*.product_name'          => ['required', 'string', 'max:255', Rule::exists('products', 'name')],
            'customers.*.items.*.quantity'              => 'required|integer|min:1',
        ]);

        $scheduledAt = $request->input('scheduled_at') ?: now()->toDateString();

        // Build canonical price map from DB - ignores any submitted unit_price
        $allProductNames = [];
        foreach ($request->input('customers') as $cData) {
            foreach ($cData['items'] as $item) {
                $allProductNames[] = trim($item['product_name']);
            }
        }
        $priceMap = Product::whereIn('name', array_unique($allProductNames))->pluck('selling_price', 'name');

        $delivery = DB::transaction(function () use ($user, $request, $scheduledAt, $priceMap) {
            $delivery = Delivery::create([
                'delivery_number' => $this->generateDeliveryNumber(),
                'staff_id'        => $user->id,
                'scheduled_at'    => $scheduledAt,
                'notes'           => $request->input('notes'),
                'status'          => 'pending',
            ]);

            foreach ($request->input('customers') as $cData) {
                $customerTotal = 0;
                $itemRecords   = [];

                foreach ($cData['items'] as $item) {
                    $price    = round((float) ($priceMap[trim($item['product_name'])] ?? 0), 2);
                    $qty      = (int) $item['quantity'];
                    $subtotal = round($price * $qty, 2);
                    $customerTotal += $subtotal;
                    $itemRecords[] = [
                        'product_name' => trim($item['product_name']),
                        'unit_price'   => $price,
                        'quantity'     => $qty,
                        'subtotal'     => $subtotal,
                    ];
                }

                $allocation = $delivery->allocations()->create([
                    'customer_id'     => (int) $cData['customer_id'],
                    'total_amount'    => round($customerTotal, 2),
                    'notes'           => $cData['notes'] ?? null,
                    'allocation_date' => $cData['allocation_date'] ?? $scheduledAt,
                ]);

                $allocation->items()->createMany($itemRecords);
            }

            return $delivery;
        });

        return redirect()->route('deliveries.show', $delivery)
            ->with('status', "Delivery {$delivery->delivery_number} created successfully.");
    }

    /* ── Show ── */
    public function show(Request $request, Delivery $delivery)
    {
        $user = $request->user();

        if ($user->role === 'customer') {
            // Customer can only view if they have an allocation in this delivery
            if (!$delivery->allocations()->where('customer_id', $user->id)->exists()) abort(403);
        } else {
            if (!$user->isAdminOrStaff()) abort(403);
            if ($user->role === 'staff' && $delivery->staff_id !== $user->id) abort(403);
        }

        $delivery->load(['staff', 'allocations.customer', 'allocations.items', 'allocations.payments.user']);

        return view('deliveries.show', compact('user', 'delivery'));
    }

    /* ── Edit form ── */
    public function edit(Request $request, Delivery $delivery)
    {
        $user = $request->user();
        if (!$user->isAdminOrStaff()) abort(403);
        if ($user->role === 'staff' && $delivery->staff_id !== $user->id) abort(403);

        $delivery->load(['allocations.customer', 'allocations.items']);

        $customers = User::where('role', 'customer')
            ->when($user->role === 'staff', fn ($q) => $q->where('state', $user->state))
            ->orderBy('name')
            ->get(['id', 'name', 'shop_name', 'state']);

        $products = Product::orderBy('name')->get(['id', 'name', 'unit', 'selling_price']);

        return view('deliveries.edit', compact('user', 'delivery', 'customers', 'products'));
    }

    /* ── Update ── */
    public function update(Request $request, Delivery $delivery)
    {
        $user = $request->user();
        if (!$user->isAdminOrStaff()) abort(403);
        if ($user->role === 'staff' && $delivery->staff_id !== $user->id) abort(403);

        $request->validate([
            'scheduled_at'                              => 'nullable|date',
            'notes'                                     => 'nullable|string|max:1000',
            'customers'                                 => 'required|array|min:1',
            'customers.*.customer_id'                   => 'required|integer|exists:users,id',
            'customers.*.allocation_date'               => 'nullable|date',
            'customers.*.items'                         => 'required|array|min:1',
            'customers.*.items.*.product_name'          => ['required', 'string', 'max:255', Rule::exists('products', 'name')],
            'customers.*.items.*.quantity'              => 'required|integer|min:1',
        ]);

        $scheduledAt = $request->input('scheduled_at') ?: now()->toDateString();

        // Build canonical price map from DB
        $allProductNames = [];
        foreach ($request->input('customers') as $cData) {
            foreach ($cData['items'] as $item) {
                $allProductNames[] = trim($item['product_name']);
            }
        }
        $priceMap = Product::whereIn('name', array_unique($allProductNames))->pluck('selling_price', 'name');

        DB::transaction(function () use ($delivery, $request, $scheduledAt, $priceMap) {
            $delivery->update([
                'scheduled_at' => $scheduledAt,
                'notes'        => $request->input('notes'),
            ]);

            // Delete existing allocations and items
            foreach ($delivery->allocations as $alloc) {
                $alloc->items()->delete();
            }
            $delivery->allocations()->delete();

            // Re-create allocations
            foreach ($request->input('customers') as $cData) {
                $customerTotal = 0;
                $itemRecords   = [];

                foreach ($cData['items'] as $item) {
                    $price    = round((float) ($priceMap[trim($item['product_name'])] ?? 0), 2);
                    $qty      = (int) $item['quantity'];
                    $subtotal = round($price * $qty, 2);
                    $customerTotal += $subtotal;
                    $itemRecords[] = [
                        'product_name' => trim($item['product_name']),
                        'unit_price'   => $price,
                        'quantity'     => $qty,
                        'subtotal'     => $subtotal,
                    ];
                }

                $allocation = $delivery->allocations()->create([
                    'customer_id'     => (int) $cData['customer_id'],
                    'total_amount'    => round($customerTotal, 2),
                    'notes'           => $cData['notes'] ?? null,
                    'allocation_date' => $cData['allocation_date'] ?? $scheduledAt,
                ]);

                $allocation->items()->createMany($itemRecords);
            }
        });

        return redirect()->route('deliveries.show', $delivery)
            ->with('status', "Delivery {$delivery->delivery_number} updated successfully.");
    }

    /* ── Dispatch ── */
    public function dispatch(Request $request, Delivery $delivery)
    {
        $user = $request->user();
        if (!$user->isAdminOrStaff()) abort(403);
        if ($user->role === 'staff' && $delivery->staff_id !== $user->id) abort(403);

        if ($delivery->status !== 'pending') {
            return redirect()->route('deliveries.show', $delivery)
                ->with('error', 'Only pending deliveries can be dispatched.');
        }

        $delivery->update([
            'status'        => 'dispatched',
            'dispatched_at' => now(),
        ]);

        return redirect()->route('deliveries.show', $delivery)
            ->with('status', "Delivery {$delivery->delivery_number} marked as dispatched.");
    }

    /* ── Mark Completed (admin & staff) ── */
    public function markCompleted(Request $request, Delivery $delivery)
    {
        $user = $request->user();
        if (!$user->isAdminOrStaff()) abort(403);

        // Staff can only complete their own deliveries
        if ($user->role === 'staff' && $delivery->staff_id !== $user->id) abort(403);

        if ($delivery->status !== 'dispatched') {
            return redirect()->route('deliveries.show', $delivery)
                ->with('error', 'Only dispatched deliveries can be marked as completed.');
        }

        $delivery->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        // SMS to all super_admins
        $delivery->loadMissing('allocations');
        $total   = $delivery->totalAmount();
        $message = "Goods of NGN " . number_format($total, 2) . " value has been delivered to you. - Country Yoghurt";
        User::where('role', 'super_admin')->whereNotNull('phone')->get()
            ->each(fn ($sa) => app(BulkSmsService::class)->send($sa->phone, $message));

        return redirect()->route('deliveries.show', $delivery)
            ->with('status', "Delivery {$delivery->delivery_number} marked as completed.");
    }

    /* ── AJAX: unpaid delivery allocations for a customer ── */
    public function ajaxCustomerAllocations(Request $request)
    {
        $actor      = $request->user();
        $customerId = $request->integer('customer_id');

        $customer = User::where('role', 'customer')
            ->when($actor->role === 'staff', fn ($q) => $q->where('state', $actor->state))
            ->findOrFail($customerId);

        $allocations = DeliveryAllocation::with(['delivery', 'payments' => fn ($q) => $q->where('status', 'approved')])
            ->where('customer_id', $customer->id)
            ->whereHas('delivery', fn ($q) => $q->where('status', 'dispatched'))
            ->get();

        return response()->json($allocations->map(function ($a) {
            $remaining = $a->remainingAmount();
            return [
                'id'              => $a->id,
                'delivery_number' => $a->delivery->delivery_number ?? '-',
                'total_amount'    => number_format((float) $a->total_amount, 2, '.', ''),
                'remaining'       => number_format($remaining, 2, '.', ''),
                'label'           => ($a->delivery->delivery_number ?? '-') . ' - ₦' . number_format($remaining, 2) . ' remaining',
            ];
        })->filter(fn ($a) => (float) $a['remaining'] > 0)->values());
    }

    /* ── Private ── */

    private function generateDeliveryNumber(): string
    {
        $yy = now()->format('y');   // e.g. "26" for 2026

        $last = Delivery::where('delivery_number', 'like', '%/' . $yy)
            ->orderByDesc('id')
            ->value('delivery_number');

        if ($last && preg_match('/DLV-(\d+)\//', $last, $m)) {
            $next = (int) $m[1] + 1;
        } else {
            $next = 1;
        }

        return 'DLV-' . str_pad($next, 4, '0', STR_PAD_LEFT) . '/' . $yy;
    }
}


