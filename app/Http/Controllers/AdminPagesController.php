<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\DeliveryAllocation;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPagesController extends Controller
{
    public function staffIndex(Request $request)
    {
        $this->ensureAdmin($request);

        $user = $request->user();
        $staff = User::where('role', 'staff')
            ->orderBy('state')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'state', 'lga', 'created_at']);

        return view('admin.staff-index', compact('user', 'staff'));
    }

    public function adminIndex(Request $request)
    {
        $this->ensureAdmin($request);

        $user = $request->user();
        $admins = User::whereIn('role', ['admin', 'super_admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'state', 'lga', 'created_at']);

        return view('admin.admin-index', compact('user', 'admins'));
    }

    public function customerIndex(Request $request)
    {
        $this->ensureAdmin($request);

        $user = $request->user();
        $debtSub = "(SELECT COALESCE(SUM(da.total_amount - COALESCE(ps.paid, 0)), 0)
            FROM delivery_allocations da
            JOIN deliveries d ON d.id = da.delivery_id
            LEFT JOIN (SELECT delivery_allocation_id, SUM(amount) as paid FROM payments WHERE status = 'approved' GROUP BY delivery_allocation_id) ps ON ps.delivery_allocation_id = da.id
            WHERE da.customer_id = users.id AND d.status IN ('dispatched','completed') AND da.total_amount > COALESCE(ps.paid, 0)) as outstanding_debt";

        $customers = User::where('role', 'customer')
            ->select(['id', 'name', 'shop_name', 'email', 'phone', 'address', 'state', 'lga', 'created_at'])
            ->addSelect(DB::raw($debtSub))
            ->orderBy('state')
            ->orderBy('shop_name')
            ->get();

        return view('admin.customer-index', compact('user', 'customers'));
    }

    /* ── Staff: customers in their state ── */
    public function staffCustomerIndex(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdminOrStaff()) abort(403);

        $debtSub = "(SELECT COALESCE(SUM(da.total_amount - COALESCE(ps.paid, 0)), 0)
            FROM delivery_allocations da
            JOIN deliveries d ON d.id = da.delivery_id
            LEFT JOIN (SELECT delivery_allocation_id, SUM(amount) as paid FROM payments WHERE status = 'approved' GROUP BY delivery_allocation_id) ps ON ps.delivery_allocation_id = da.id
            WHERE da.customer_id = users.id AND d.status IN ('dispatched','completed') AND da.total_amount > COALESCE(ps.paid, 0)) as outstanding_debt";

        $customers = User::where('role', 'customer')
            ->select(['id', 'name', 'shop_name', 'email', 'phone', 'address', 'state', 'lga', 'created_at'])
            ->addSelect(DB::raw($debtSub))
            ->when($user->role === 'staff', fn ($q) => $q->where('state', $user->state))
            ->orderBy('shop_name')
            ->orderBy('name')
            ->get();

        return view('admin.customer-index', compact('user', 'customers'));
    }

    /* ── Customer detail (admin + staff) ── */
    public function customerShow(Request $request, User $customer)
    {
        $user = $request->user();
        if (!$user->isAdminOrStaff()) abort(403);
        if ($customer->role !== 'customer') abort(404);

        // Staff can only view customers in their state
        if ($user->role === 'staff' && $customer->state !== $user->state) abort(403);

        $orders = Order::where('user_id', $customer->id)
            ->withCount('items')
            ->with(['payments' => fn ($q) => $q->where('status', 'approved')])
            ->latest()
            ->get();

        $payments = Payment::where('user_id', $customer->id)
            ->with('order:id,order_number', 'deliveryAllocation.delivery:id,delivery_number')
            ->latest()
            ->get();

        $deliveries = DeliveryAllocation::where('customer_id', $customer->id)
            ->with(['delivery' => fn($q) => $q->with('staff:id,name'), 'items'])
            ->latest()
            ->get();

        // Summary stats
        $totalOrders   = $orders->count();
        $totalPaid     = Payment::where('user_id', $customer->id)->where('status', 'approved')->sum('amount');
        $totalDebt     = (float) DB::table('delivery_allocations')
            ->join('deliveries', 'deliveries.id', '=', 'delivery_allocations.delivery_id')
            ->whereIn('deliveries.status', ['dispatched', 'completed'])
            ->where('delivery_allocations.customer_id', $customer->id)
            ->leftJoinSub(
                DB::table('payments')
                    ->where('status', 'approved')
                    ->select('delivery_allocation_id', DB::raw('SUM(amount) as paid'))
                    ->groupBy('delivery_allocation_id'),
                'ps', 'ps.delivery_allocation_id', '=', 'delivery_allocations.id'
            )
            ->whereRaw('delivery_allocations.total_amount > COALESCE(ps.paid, 0)')
            ->selectRaw('COALESCE(SUM(delivery_allocations.total_amount - COALESCE(ps.paid, 0)), 0) as debt')
            ->value('debt');

        return view('customers.show', compact(
            'user', 'customer',
            'orders', 'payments', 'deliveries',
            'totalOrders', 'totalPaid', 'totalDebt'
        ));
    }

    private function ensureAdmin(Request $request): void
    {
        if (!$request->user()?->isAdmin()) {
            abort(403);
        }
    }

    public function debtsIndex(Request $request)
    {
        $this->ensureAdmin($request);

        $user = $request->user();

        $debtRows = DB::table('delivery_allocations')
            ->join('deliveries', 'deliveries.id', '=', 'delivery_allocations.delivery_id')
            ->join('users', 'users.id', '=', 'delivery_allocations.customer_id')
            ->leftJoinSub(
                DB::table('payments')
                    ->where('status', 'approved')
                    ->select('delivery_allocation_id', DB::raw('SUM(amount) as paid'))
                    ->groupBy('delivery_allocation_id'),
                'ps', 'ps.delivery_allocation_id', '=', 'delivery_allocations.id'
            )
            ->whereIn('deliveries.status', ['dispatched', 'completed'])
            ->whereRaw('delivery_allocations.total_amount > COALESCE(ps.paid, 0)')
            ->select(
                'deliveries.id as delivery_id',
                'deliveries.delivery_number',
                'deliveries.status as delivery_status',
                'delivery_allocations.total_amount',
                'deliveries.created_at as delivery_date',
                DB::raw('COALESCE(ps.paid, 0) as paid_amount'),
                DB::raw('delivery_allocations.total_amount - COALESCE(ps.paid, 0) as outstanding'),
                'users.id as customer_id',
                'users.name as customer_name',
                'users.shop_name',
                'users.phone',
                'users.state',
            )
            ->orderByDesc('outstanding')
            ->get();

        $totalOutstanding = $debtRows->sum('outstanding');

        return view('admin.debts-index', compact('user', 'debtRows', 'totalOutstanding'));
    }
}
