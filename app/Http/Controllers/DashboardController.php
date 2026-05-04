<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\BulkSmsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // ── Date range resolution (admin analytics only) ───────────
        $range     = $request->input('range', 'all');
        $fromInput = $request->input('from');
        $toInput   = $request->input('to');
        $now       = Carbon::now();

        [$dateStart, $dateEnd] = $this->resolveDateRange($range, $fromInput, $toInput, $now);

        // Closure: applies the active date window to any builder
        $dr = function ($query) use ($dateStart, $dateEnd) {
            if ($dateStart) $query->where('created_at', '>=', $dateStart);
            if ($dateEnd)   $query->where('created_at', '<=', $dateEnd);
            return $query;
        };

        $stateContacts = collect();
        $adminStats    = null;
        $staffStats    = null;
        $customerStats = null;
        $recentStaff      = collect();
        $recentCustomers  = collect();
        $smsBalance       = null;

        // ── Contacts in state (staff & customer) ──────────────────
        if (in_array($user->role, ['staff', 'customer'], true) && $user->state) {
            $stateContacts = User::query()
                ->where('state', $user->state)
                ->whereIn('role', ['staff', 'customer'])
                ->where('id', '!=', $user->id)
                ->orderBy('role')
                ->orderBy('name')
                ->get(['name', 'role', 'phone', 'state', 'lga', 'shop_name']);
        }

        // ── Admin stats ────────────────────────────────────────────
        if ($user->role === 'admin') {
            // Debt needs its own DB::table query — build separately so we can add date clause
            $debtQ = DB::table('orders')
                ->whereIn('orders.status', ['approved', 'delivered'])
                ->leftJoinSub(
                    DB::table('payments')
                        ->where('status', 'approved')
                        ->select('order_id', DB::raw('SUM(amount) as paid'))
                        ->groupBy('order_id'),
                    'ps', 'ps.order_id', '=', 'orders.id'
                )
                ->whereRaw('orders.total_amount > COALESCE(ps.paid, 0)')
                ->selectRaw('COALESCE(SUM(orders.total_amount - COALESCE(ps.paid, 0)), 0) as debt');
            if ($dateStart) $debtQ->where('orders.created_at', '>=', $dateStart);
            if ($dateEnd)   $debtQ->where('orders.created_at', '<=', $dateEnd);

            $adminStats = [
                // ── NOT date-filtered (headcount / inventory snapshots) ──
                'staffCount'    => User::where('role', 'staff')->count(),
                'customerCount' => User::where('role', 'customer')->count(),
                'stateCount'    => User::whereNotNull('state')->distinct('state')->count('state'),
                'totalProducts' => Product::count(),
                'lowStock'      => Product::whereColumn('quantity', '<=', 'reorder_level')
                                          ->where('quantity', '>', 0)->count(),
                'outOfStock'    => Product::where('quantity', 0)->count(),

                // ── Date-filtered transactional stats ────────────────────
                'totalOrders'    => $dr(Order::query())->count(),
                'pendingOrders'  => $dr(Order::query())->where('status', 'pending')->count(),
                'approvedOrders' => $dr(Order::query())->where('status', 'approved')->count(),

                'totalPayments'  => $dr(Payment::query())->count(),
                'pendingPayments'=> $dr(Payment::query())->where('status', 'pending')->count(),
                'totalRevenue'   => $dr(Payment::query())->where('status', 'approved')->sum('amount'),

                'totalDeliveries'     => $dr(Delivery::query())->count(),
                'pendingDeliveries'   => $dr(Delivery::query())->where('status', 'pending')->count(),
                'completedDeliveries' => $dr(Delivery::query())->where('status', 'delivered')->count(),

                'totalDebt'     => (float) $debtQ->value('debt'),
            ];

            $recentStaff = User::where('role', 'staff')
                ->latest()->limit(5)
                ->get(['name', 'email', 'state', 'lga', 'created_at']);

            $recentCustomers = User::where('role', 'customer')
                ->latest()->limit(5)
                ->get(['name', 'shop_name', 'state', 'lga', 'created_at']);

            $smsBalance = app(BulkSmsService::class)->getBalance();
        }

        // ── Staff stats ────────────────────────────────────────────
        if ($user->role === 'staff') {
            // customer IDs in this staff's state
            $stateCustomerIds = User::where('role', 'customer')
                ->where('state', $user->state)
                ->pluck('id');

            $staffStats = [
                // Orders from customers in their state
                'stateOrders'   => Order::whereIn('user_id', $stateCustomerIds)->count(),
                'pendingOrders' => Order::whereIn('user_id', $stateCustomerIds)
                                        ->where('status', 'pending')->count(),

                // Deliveries assigned to this staff
                'myDeliveries'          => Delivery::where('staff_id', $user->id)->count(),
                'myPendingDeliveries'   => Delivery::where('staff_id', $user->id)
                                                   ->where('status', 'pending')->count(),
                'myActiveDeliveries'    => Delivery::where('staff_id', $user->id)
                                                   ->where('status', 'approved')->count(),

                // Payments from customers in their state
                'statePayments'        => Payment::whereIn('user_id', $stateCustomerIds)->count(),
                'statePendingPayments' => Payment::whereIn('user_id', $stateCustomerIds)
                                                 ->where('status', 'pending')->count(),
                'stateRevenue'         => Payment::whereIn('user_id', $stateCustomerIds)
                                                 ->where('status', 'approved')->sum('amount'),

                // Debt: remaining unpaid amount on approved/delivered orders in this state
                'stateDebt'            => (float) DB::table('orders')
                    ->whereIn('orders.status', ['approved', 'delivered'])
                    ->whereIn('orders.user_id', $stateCustomerIds)
                    ->leftJoinSub(
                        DB::table('payments')
                            ->where('status', 'approved')
                            ->select('order_id', DB::raw('SUM(amount) as paid'))
                            ->groupBy('order_id'),
                        'ps', 'ps.order_id', '=', 'orders.id'
                    )
                    ->whereRaw('orders.total_amount > COALESCE(ps.paid, 0)')
                    ->selectRaw('COALESCE(SUM(orders.total_amount - COALESCE(ps.paid, 0)), 0) as debt')
                    ->value('debt'),
            ];
        }

        // ── Customer stats ─────────────────────────────────────────
        if ($user->role === 'customer') {
            $customerStats = [
                'totalOrders'    => Order::where('user_id', $user->id)->count(),
                'pendingOrders'  => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
                'approvedOrders' => Order::where('user_id', $user->id)->where('status', 'approved')->count(),
                'deliveredOrders'=> Order::where('user_id', $user->id)->where('status', 'delivered')->count(),

                'totalPayments'  => Payment::where('user_id', $user->id)->count(),
                'totalPaid'      => Payment::where('user_id', $user->id)->where('status', 'approved')->sum('amount'),
                'pendingPayments'=> Payment::where('user_id', $user->id)->where('status', 'pending')->count(),

                'myDebt'         => (float) DB::table('orders')
                    ->whereIn('orders.status', ['approved', 'delivered'])
                    ->where('orders.user_id', $user->id)
                    ->leftJoinSub(
                        DB::table('payments')
                            ->where('status', 'approved')
                            ->select('order_id', DB::raw('SUM(amount) as paid'))
                            ->groupBy('order_id'),
                        'ps', 'ps.order_id', '=', 'orders.id'
                    )
                    ->whereRaw('orders.total_amount > COALESCE(ps.paid, 0)')
                    ->selectRaw('COALESCE(SUM(orders.total_amount - COALESCE(ps.paid, 0)), 0) as debt')
                    ->value('debt'),

                'totalDeliveries'    => Delivery::whereHas('order', fn($q) => $q->where('user_id', $user->id))->count(),
                'pendingDeliveries'  => Delivery::whereHas('order', fn($q) => $q->where('user_id', $user->id))
                                                ->where('status', 'pending')->count(),
                'completedDeliveries'=> Delivery::whereHas('order', fn($q) => $q->where('user_id', $user->id))
                                                ->where('status', 'delivered')->count(),
            ];
        }

        return view('dashboard', compact(
            'user', 'stateContacts',
            'adminStats', 'staffStats', 'customerStats',
            'recentStaff', 'recentCustomers',
            'smsBalance',
            'range', 'fromInput', 'toInput', 'dateStart', 'dateEnd'
        ));
    }

    // ── Date range resolver ────────────────────────────────────────
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
                return [
                    $lm->copy()->startOfMonth()->startOfDay(),
                    $lm->copy()->endOfMonth()->endOfDay(),
                ];

            case 'this_month_last_year':
                $tmlyr = $now->copy()->subYear();
                return [
                    $tmlyr->copy()->startOfMonth()->startOfDay(),
                    $tmlyr->copy()->endOfMonth()->endOfDay(),
                ];

            case 'this_year':
                return [
                    $now->copy()->startOfYear()->startOfDay(),
                    $now->copy()->endOfYear()->endOfDay(),
                ];

            case 'last_year':
                $ly = $now->copy()->subYear();
                return [
                    $ly->copy()->startOfYear()->startOfDay(),
                    $ly->copy()->endOfYear()->endOfDay(),
                ];

            case 'current_fy':
                // Financial year: 1 Apr – 31 Mar
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
                return [
                    $from ? Carbon::parse($from)->startOfDay() : null,
                    $to   ? Carbon::parse($to)->endOfDay()     : null,
                ];

            default: // 'all'
                return [null, null];
        }
    }
}
