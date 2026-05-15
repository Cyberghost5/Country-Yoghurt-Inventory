<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403);
        }

        // ── Date range ──────────────────────────────────────────────
        $range     = $request->input('range', 'this_month');
        $fromInput = $request->input('from');
        $toInput   = $request->input('to');
        $now       = Carbon::now();

        [$dateStart, $dateEnd] = $this->resolveDateRange($range, $fromInput, $toInput, $now);

        // Closure: applies date window to any builder
        $dr = function ($query) use ($dateStart, $dateEnd) {
            if ($dateStart) $query->where('created_at', '>=', $dateStart);
            if ($dateEnd)   $query->where('created_at', '<=', $dateEnd);
            return $query;
        };

        // ── Orders ──────────────────────────────────────────────────
        $ordersTotal     = $dr(Order::query())->count();
        $ordersPending   = $dr(Order::query())->where('status', 'pending')->count();
        $ordersApproved  = $dr(Order::query())->where('status', 'approved')->count();
        $ordersDelivered = $dr(Order::query())->where('status', 'delivered')->count();
        $ordersRejected  = $dr(Order::query())->where('status', 'rejected')->count();
        $ordersValue     = $dr(Order::query())->sum('total_amount');
        $ordersAvgValue  = $ordersTotal > 0 ? ($ordersValue / $ordersTotal) : 0;

        // ── Revenue / Payments ──────────────────────────────────────
        $revenueTotal    = $dr(Payment::query())->where('status', 'approved')->sum('amount');
        $paymentsPending = $dr(Payment::query())->where('status', 'pending')->count();
        $paymentsTotal   = $dr(Payment::query())->count();

        // Revenue by payment method
        $revenueByMethod = $dr(Payment::query())
            ->where('status', 'approved')
            ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        // ── Debt ────────────────────────────────────────────────────
        $debtQ = DB::table('delivery_allocations')
            ->join('deliveries', 'deliveries.id', '=', 'delivery_allocations.delivery_id')
            ->whereIn('deliveries.status', ['dispatched', 'completed'])
            ->leftJoinSub(
                DB::table('payments')
                    ->where('status', 'approved')
                    ->select('delivery_allocation_id', DB::raw('SUM(amount) as paid'))
                    ->groupBy('delivery_allocation_id'),
                'ps', 'ps.delivery_allocation_id', '=', 'delivery_allocations.id'
            )
            ->whereRaw('delivery_allocations.total_amount > COALESCE(ps.paid, 0)')
            ->selectRaw('COALESCE(SUM(delivery_allocations.total_amount - COALESCE(ps.paid, 0)), 0) as debt');
        if ($dateStart) $debtQ->where('deliveries.created_at', '>=', $dateStart);
        if ($dateEnd)   $debtQ->where('deliveries.created_at', '<=', $dateEnd);
        $totalDebt = (float) $debtQ->value('debt');

        // Delivery allocations with outstanding debt (date-filtered)
        $debtOrdersQ = DB::table('delivery_allocations')
            ->join('deliveries', 'deliveries.id', '=', 'delivery_allocations.delivery_id')
            ->join('users', 'users.id', '=', 'delivery_allocations.customer_id')
            ->whereIn('deliveries.status', ['dispatched', 'completed'])
            ->leftJoinSub(
                DB::table('payments')
                    ->where('status', 'approved')
                    ->select('delivery_allocation_id', DB::raw('SUM(amount) as paid'))
                    ->groupBy('delivery_allocation_id'),
                'ps', 'ps.delivery_allocation_id', '=', 'delivery_allocations.id'
            )
            ->whereRaw('delivery_allocations.total_amount > COALESCE(ps.paid, 0)')
            ->select(
                'deliveries.id as delivery_id',
                'deliveries.delivery_number',
                'users.name as customer_name',
                'users.state',
                'delivery_allocations.total_amount',
                DB::raw('COALESCE(ps.paid, 0) as paid'),
                DB::raw('delivery_allocations.total_amount - COALESCE(ps.paid, 0) as remaining'),
                'deliveries.created_at'
            )
            ->orderByDesc('remaining');
        if ($dateStart) $debtOrdersQ->where('deliveries.created_at', '>=', $dateStart);
        if ($dateEnd)   $debtOrdersQ->where('deliveries.created_at', '<=', $dateEnd);
        $debtOrders = $debtOrdersQ->limit(20)->get();

        // ── Deliveries ──────────────────────────────────────────────
        $deliveriesTotal      = $dr(Delivery::query())->count();
        $deliveriesPending    = $dr(Delivery::query())->where('status', 'pending')->count();
        $deliveriesDispatched = $dr(Delivery::query())->where('status', 'dispatched')->count();
        $deliveriesDelivered  = $dr(Delivery::query())->where('status', 'completed')->count();

        // ── Top Products ────────────────────────────────────────────
        $topProductsQ = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->select(
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.subtotal) as total_revenue'),
                DB::raw('COUNT(DISTINCT order_items.order_id) as order_count')
            )
            ->groupBy('order_items.product_name')
            ->orderByDesc('total_revenue');
        if ($dateStart) $topProductsQ->where('orders.created_at', '>=', $dateStart);
        if ($dateEnd)   $topProductsQ->where('orders.created_at', '<=', $dateEnd);
        $topProducts = $topProductsQ->limit(10)->get();

        // ── Top Customers ───────────────────────────────────────────
        $topCustomersQ = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->select(
                'users.id',
                'users.name',
                'users.shop_name',
                'users.state',
                DB::raw('COUNT(orders.id) as order_count'),
                DB::raw('SUM(orders.total_amount) as total_value')
            )
            ->where('users.role', 'customer')
            ->groupBy('users.id', 'users.name', 'users.shop_name', 'users.state')
            ->orderByDesc('total_value');
        if ($dateStart) $topCustomersQ->where('orders.created_at', '>=', $dateStart);
        if ($dateEnd)   $topCustomersQ->where('orders.created_at', '<=', $dateEnd);
        $topCustomers = $topCustomersQ->limit(10)->get();

        // ── Orders by State ─────────────────────────────────────────
        $ordersByStateQ = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->select(
                'users.state',
                DB::raw('COUNT(orders.id) as order_count'),
                DB::raw('SUM(orders.total_amount) as total_value')
            )
            ->whereNotNull('users.state')
            ->groupBy('users.state')
            ->orderByDesc('total_value');
        if ($dateStart) $ordersByStateQ->where('orders.created_at', '>=', $dateStart);
        if ($dateEnd)   $ordersByStateQ->where('orders.created_at', '<=', $dateEnd);
        $ordersByState = $ordersByStateQ->get();

        // ── Staff Performance ───────────────────────────────────────
        $staffPerformanceQ = DB::table('deliveries')
            ->join('users', 'users.id', '=', 'deliveries.staff_id')
            ->select(
                'users.name as staff_name',
                'users.state',
                DB::raw('COUNT(deliveries.id) as total_deliveries'),
                DB::raw('SUM(CASE WHEN deliveries.status = "completed" THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(CASE WHEN deliveries.status = "pending" THEN 1 ELSE 0 END) as pending')
            )
            ->groupBy('users.id', 'users.name', 'users.state')
            ->orderByDesc('completed');
        if ($dateStart) $staffPerformanceQ->where('deliveries.created_at', '>=', $dateStart);
        if ($dateEnd)   $staffPerformanceQ->where('deliveries.created_at', '<=', $dateEnd);
        $staffPerformance = $staffPerformanceQ->get();

        // ── Recent Orders (last 20 in range) ────────────────────────
        $recentOrders = $dr(Order::with('user'))
            ->latest()
            ->limit(20)
            ->get();

        // ── Recent Deliveries (last 20 in range) ─────────────────────
        $recentDeliveries = $dr(Delivery::with('staff'))
            ->latest()
            ->limit(20)
            ->get();

        // ── Chart data ────────────────────────────────────────────────

        // Helper: run a time-bucketed aggregate query and return [{label, value}]
        $runTimeSeries = function (string $table, string $agg, string $dateFmt, ?string $statusFilter = null) use ($dateStart, $dateEnd): array {
            $q = DB::table($table)
                ->select(DB::raw("DATE_FORMAT(created_at, '{$dateFmt}') as period"), DB::raw("{$agg} as value"))
                ->groupBy('period')
                ->orderBy('period');
            if ($statusFilter) $q->where('status', $statusFilter);
            if ($dateStart)    $q->where('created_at', '>=', $dateStart);
            if ($dateEnd)      $q->where('created_at', '<=', $dateEnd);
            return $q->get()->map(function ($r) use ($dateFmt) {
                try {
                    if ($dateFmt === '%x-W%v') {
                        [$yr, $wk] = explode('-W', $r->period);
                        $label = "Wk {$wk}, {$yr}";
                    } elseif ($dateFmt === '%Y-%m') {
                        $label = Carbon::createFromFormat('Y-m', $r->period)->format('M Y');
                    } else {
                        $label = Carbon::createFromFormat('Y-m-d', $r->period)->format('d M Y');
                    }
                } catch (\Exception $e) {
                    $label = $r->period;
                }
                return ['label' => $label, 'value' => (float) $r->value];
            })->values()->toArray();
        };

        // Time series: monthly / weekly / daily × revenue / order_value / orders
        $chartTimeSeries = [];
        foreach (['monthly' => '%Y-%m', 'weekly' => '%x-W%v', 'daily' => '%Y-%m-%d'] as $grp => $dateFmt) {
            $chartTimeSeries[$grp] = [
                'revenue'     => $runTimeSeries('payments', 'SUM(amount)',       $dateFmt, 'approved'),
                'order_value' => $runTimeSeries('orders',   'SUM(total_amount)', $dateFmt),
                'orders'      => $runTimeSeries('orders',   'COUNT(*)',           $dateFmt),
            ];
        }

        // Status doughnuts
        $chartOrderStatus = [
            'labels' => ['Pending', 'Approved', 'Delivered', 'Rejected'],
            'data'   => [$ordersPending, $ordersApproved, $ordersDelivered, $ordersRejected],
        ];
        $chartDeliveryStatus = [
            'labels' => ['Pending', 'Dispatched', 'Completed'],
            'data'   => [$deliveriesPending, $deliveriesDispatched, $deliveriesDelivered],
        ];

        // Payment method: revenue + count per method
        $methodLabels = $revenueByMethod->map(fn ($r) => match ($r->payment_method) {
            'bank_transfer' => 'Bank Transfer',
            'cash'          => 'Cash',
            'pos'           => 'POS',
            'mobile_money'  => 'Mobile Money',
            default         => ucwords(str_replace('_', ' ', $r->payment_method)),
        })->values()->toArray();
        $chartPaymentMethod = [
            'labels'  => $methodLabels,
            'revenue' => $revenueByMethod->pluck('total')->map(fn ($v) => (float) $v)->values()->toArray(),
            'count'   => $revenueByMethod->pluck('count')->map(fn ($v) => (int) $v)->values()->toArray(),
        ];

        // Top products: revenue, qty, order count
        $chartProductRevenue = [
            'labels'  => $topProducts->take(8)->pluck('product_name')->values()->toArray(),
            'revenue' => $topProducts->take(8)->pluck('total_revenue')->map(fn ($v) => (float) $v)->values()->toArray(),
            'qty'     => $topProducts->take(8)->pluck('total_qty')->map(fn ($v) => (int) $v)->values()->toArray(),
            'orders'  => $topProducts->take(8)->pluck('order_count')->map(fn ($v) => (int) $v)->values()->toArray(),
        ];

        // ── Revenue vs Debt summary ──────────────────────────────────
        $chartRevenueVsDebt = [
            'labels' => ['Revenue Collected', 'Outstanding Debt'],
            'data'   => [(float) $revenueTotal, $totalDebt],
        ];

        // ── Top 10 customers by outstanding debt ─────────────────────
        $topDebtorsQ = DB::table('delivery_allocations')
            ->join('deliveries', 'deliveries.id', '=', 'delivery_allocations.delivery_id')
            ->join('users', 'users.id', '=', 'delivery_allocations.customer_id')
            ->whereIn('deliveries.status', ['dispatched', 'completed'])
            ->leftJoinSub(
                DB::table('payments')
                    ->where('status', 'approved')
                    ->select('delivery_allocation_id', DB::raw('SUM(amount) as paid'))
                    ->groupBy('delivery_allocation_id'),
                'ps', 'ps.delivery_allocation_id', '=', 'delivery_allocations.id'
            )
            ->whereRaw('delivery_allocations.total_amount > COALESCE(ps.paid, 0)')
            ->select(
                'users.id',
                'users.name as customer_name',
                DB::raw('SUM(delivery_allocations.total_amount - COALESCE(ps.paid, 0)) as remaining')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('remaining');
        if ($dateStart) $topDebtorsQ->where('deliveries.created_at', '>=', $dateStart);
        if ($dateEnd)   $topDebtorsQ->where('deliveries.created_at', '<=', $dateEnd);
        $topDebtors = $topDebtorsQ->limit(10)->get();

        $chartTopDebtors = [
            'labels' => $topDebtors->pluck('customer_name')->values()->toArray(),
            'data'   => $topDebtors->pluck('remaining')->map(fn ($v) => (float) $v)->values()->toArray(),
        ];

        return view('reports.index', compact(
            'user', 'range', 'fromInput', 'toInput', 'dateStart', 'dateEnd',
            'ordersTotal', 'ordersPending', 'ordersApproved', 'ordersDelivered',
            'ordersRejected', 'ordersValue', 'ordersAvgValue',
            'revenueTotal', 'paymentsPending', 'paymentsTotal', 'revenueByMethod',
            'totalDebt', 'debtOrders',
            'deliveriesTotal', 'deliveriesPending', 'deliveriesDispatched', 'deliveriesDelivered',
            'topProducts', 'topCustomers', 'ordersByState', 'staffPerformance',
            'recentOrders', 'recentDeliveries',
            'chartTimeSeries', 'chartOrderStatus', 'chartDeliveryStatus',
            'chartPaymentMethod', 'chartProductRevenue',
            'chartRevenueVsDebt', 'chartTopDebtors', 'topDebtors'
        ));
    }

    // ── Date range resolver (shared with DashboardController) ────────
    private function resolveDateRange(string $range, ?string $from, ?string $to, Carbon $now): array
    {
        switch ($range) {
            case 'today':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
            case 'yesterday':
                return [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()];
            case 'last_7':
                return [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()];
            case 'last_30':
                return [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()];
            case 'this_month':
                return [
                    $now->copy()->startOfMonth()->startOfDay(),
                    $now->copy()->endOfMonth()->endOfDay(),
                ];
            case 'last_month':
                $lm = $now->copy()->subMonthNoOverflow();
                return [$lm->copy()->startOfMonth()->startOfDay(), $lm->copy()->endOfMonth()->endOfDay()];
            case 'this_month_last_year':
                $tmlyr = $now->copy()->subYear();
                return [$tmlyr->copy()->startOfMonth()->startOfDay(), $tmlyr->copy()->endOfMonth()->endOfDay()];
            case 'this_year':
                return [$now->copy()->startOfYear()->startOfDay(), $now->copy()->endOfYear()->endOfDay()];
            case 'last_year':
                $ly = $now->copy()->subYear();
                return [$ly->copy()->startOfYear()->startOfDay(), $ly->copy()->endOfYear()->endOfDay()];
            case 'current_fy':
                if ($now->month >= 4) {
                    return [
                        Carbon::create($now->year, 4, 1)->startOfDay(),
                        Carbon::create($now->year + 1, 3, 31)->endOfDay(),
                    ];
                }
                return [
                    Carbon::create($now->year - 1, 4, 1)->startOfDay(),
                    Carbon::create($now->year, 3, 31)->endOfDay(),
                ];
            case 'last_fy':
                if ($now->month >= 4) {
                    return [
                        Carbon::create($now->year - 1, 4, 1)->startOfDay(),
                        Carbon::create($now->year, 3, 31)->endOfDay(),
                    ];
                }
                return [
                    Carbon::create($now->year - 2, 4, 1)->startOfDay(),
                    Carbon::create($now->year - 1, 3, 31)->endOfDay(),
                ];
            case 'custom':
                $start = $from ? Carbon::parse($from)->startOfDay() : null;
                $end   = $to   ? Carbon::parse($to)->endOfDay()     : null;
                return [$start, $end];
            default: // 'all'
                return [null, null];
        }
    }
}
