<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\DeliveryAllocation;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        /* ── Orders ── */
        $ordersQuery = Order::with(['user', 'approvedBy'])->latest();
        if ($user->role === 'customer') {
            $ordersQuery->where('user_id', $user->id);
        } elseif ($user->role === 'staff') {
            $ordersQuery->where('user_id', $user->id);
        }
        $orders = $ordersQuery->get()->map(fn ($o) => (object) [
            'type'        => 'order',
            'id'          => $o->id,
            'ref'         => $o->order_number,
            'description' => 'Order placed' . ($user->isAdmin() ? ' by ' . $o->user->name : ''),
            'amount'      => $o->total_amount,
            'status'      => $o->status,
            'date'        => $o->created_at,
            'url'         => route('orders.show', $o),
            'icon'        => 'bi-bag',
        ]);

        /* ── Payments ── */
        $paymentsQuery = Payment::with(['user', 'order'])->latest();
        if ($user->role === 'customer') {
            $paymentsQuery->where('user_id', $user->id);
        } elseif ($user->role === 'staff') {
            $paymentsQuery->where('user_id', $user->id);
        }
        $payments = $paymentsQuery->get()->map(fn ($p) => (object) [
            'type'        => 'payment',
            'id'          => $p->id,
            'ref'         => $p->order?->order_number ?? ('PMT-' . str_pad($p->id, 5, '0', STR_PAD_LEFT)),
            'description' => 'Payment submitted' . ($user->isAdmin() ? ' by ' . $p->user->name : ''),
            'amount'      => $p->amount,
            'status'      => $p->status,
            'date'        => $p->created_at,
            'url'         => route('payments.show', $p),
            'icon'        => 'bi-credit-card',
        ]);

        /* ── Deliveries ── */
        if ($user->role === 'customer') {
            // Customers see their own delivery allocations
            $deliveries = DeliveryAllocation::where('customer_id', $user->id)
                ->with(['delivery.staff'])
                ->latest()
                ->get()
                ->map(fn ($d) => (object) [
                    'type'        => 'delivery',
                    'id'          => $d->delivery_id,
                    'ref'         => $d->delivery?->delivery_number ?? ('DLV-' . str_pad($d->id, 5, '0', STR_PAD_LEFT)),
                    'description' => 'Delivery allocation',
                    'amount'      => $d->total_amount,
                    'status'      => $d->delivery?->status ?? 'pending',
                    'date'        => $d->created_at,
                    'url'         => route('deliveries.show', $d->delivery_id),
                    'icon'        => 'bi-truck',
                ]);
        } else {
            $deliveriesQuery = Delivery::with(['staff', 'allocations'])->latest();
            if ($user->role === 'staff') {
                $deliveriesQuery->where('staff_id', $user->id);
            }
            $deliveries = $deliveriesQuery->get()->map(fn ($d) => (object) [
                'type'        => 'delivery',
                'id'          => $d->id,
                'ref'         => $d->delivery_number,
                'description' => 'Delivery scheduled' . ($user->isAdmin() ? ' by ' . ($d->staff?->name ?? 'staff') : ''),
                'amount'      => $d->totalAmount() ?: null,
                'status'      => $d->status,
                'date'        => $d->created_at,
                'url'         => route('deliveries.show', $d),
                'icon'        => 'bi-truck',
            ]);
        }

        /* ── Merge & sort ── */
        $transactions = collect()
            ->merge($orders)
            ->merge($payments)
            ->merge($deliveries)
            ->sortByDesc('date')
            ->values();

        /* ── Optional type filter ── */
        if ($type = $request->input('type')) {
            $transactions = $transactions->filter(fn ($t) => $t->type === $type)->values();
        }

        /* ── Paginate manually ── */
        $perPage     = 30;
        $currentPage = (int) ($request->input('page', 1));
        $slice       = $transactions->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $slice,
            $transactions->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('transactions.index', [
            'user'         => $user,
            'transactions' => $paginator,
        ]);
    }
}
