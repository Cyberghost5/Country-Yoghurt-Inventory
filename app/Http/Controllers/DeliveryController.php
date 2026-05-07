<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\DeliveryAllocation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    /* ── Index ── */
    public function index(Request $request)
    {
        $user  = $request->user();
        if (!in_array($user->role, ['admin', 'staff'], true)) abort(403);

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
        if (!in_array($user->role, ['admin', 'staff'], true)) abort(403);

        $customers = User::where('role', 'customer')
            ->when($user->role === 'staff', fn ($q) => $q->where('state', $user->state))
            ->orderBy('name')
            ->get(['id', 'name', 'shop_name', 'state']);

        return view('deliveries.create', compact('user', 'customers'));
    }

    /* ── Store ── */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'staff'], true)) abort(403);

        $request->validate([
            'scheduled_at'                              => 'nullable|date',
            'notes'                                     => 'nullable|string|max:1000',
            'customers'                                 => 'required|array|min:1',
            'customers.*.customer_id'                   => 'required|integer|exists:users,id',
            'customers.*.allocation_date'               => 'nullable|date',
            'customers.*.items'                         => 'required|array|min:1',
            'customers.*.items.*.product_name'          => 'required|string|max:255',
            'customers.*.items.*.unit_price'            => 'required|numeric|min:0',
            'customers.*.items.*.quantity'              => 'required|integer|min:1',
        ]);

        $scheduledAt = $request->input('scheduled_at') ?: now()->toDateString();

        $delivery = DB::transaction(function () use ($user, $request, $scheduledAt) {
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
                    $price    = round((float) $item['unit_price'], 2);
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
        if (!in_array($user->role, ['admin', 'staff'], true)) abort(403);

        if ($user->role === 'staff' && $delivery->staff_id !== $user->id) abort(403);

        $delivery->load(['staff', 'allocations.customer', 'allocations.items', 'allocations.payments.user']);

        return view('deliveries.show', compact('user', 'delivery'));
    }

    /* ── Edit form ── */
    public function edit(Request $request, Delivery $delivery)
    {
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'staff'], true)) abort(403);
        if ($user->role === 'staff' && $delivery->staff_id !== $user->id) abort(403);
        if ($delivery->status !== 'pending') {
            return redirect()->route('deliveries.show', $delivery)
                ->with('error', 'Only pending deliveries can be edited.');
        }

        $delivery->load(['allocations.customer', 'allocations.items']);

        $customers = User::where('role', 'customer')
            ->when($user->role === 'staff', fn ($q) => $q->where('state', $user->state))
            ->orderBy('name')
            ->get(['id', 'name', 'shop_name', 'state']);

        return view('deliveries.edit', compact('user', 'delivery', 'customers'));
    }

    /* ── Update ── */
    public function update(Request $request, Delivery $delivery)
    {
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'staff'], true)) abort(403);
        if ($user->role === 'staff' && $delivery->staff_id !== $user->id) abort(403);
        if ($delivery->status !== 'pending') {
            return redirect()->route('deliveries.show', $delivery)
                ->with('error', 'Only pending deliveries can be edited.');
        }

        $request->validate([
            'scheduled_at'                              => 'nullable|date',
            'notes'                                     => 'nullable|string|max:1000',
            'customers'                                 => 'required|array|min:1',
            'customers.*.customer_id'                   => 'required|integer|exists:users,id',
            'customers.*.allocation_date'               => 'nullable|date',
            'customers.*.items'                         => 'required|array|min:1',
            'customers.*.items.*.product_name'          => 'required|string|max:255',
            'customers.*.items.*.unit_price'            => 'required|numeric|min:0',
            'customers.*.items.*.quantity'              => 'required|integer|min:1',
        ]);

        $scheduledAt = $request->input('scheduled_at') ?: now()->toDateString();

        DB::transaction(function () use ($delivery, $request, $scheduledAt) {
            $delivery->update([
                'scheduled_at' => $scheduledAt,
                'notes'        => $request->input('notes'),
            ]);

            // Delete existing allocations and items (pending = no payments yet)
            foreach ($delivery->allocations as $alloc) {
                $alloc->items()->delete();
            }
            $delivery->allocations()->delete();

            // Re-create allocations
            foreach ($request->input('customers') as $cData) {
                $customerTotal = 0;
                $itemRecords   = [];

                foreach ($cData['items'] as $item) {
                    $price    = round((float) $item['unit_price'], 2);
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
        if (!in_array($user->role, ['admin', 'staff'], true)) abort(403);
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
        if (!in_array($user->role, ['admin', 'staff'], true)) abort(403);

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
                'label'           => ($a->delivery->delivery_number ?? '-') . ' — ₦' . number_format($remaining, 2) . ' remaining',
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


