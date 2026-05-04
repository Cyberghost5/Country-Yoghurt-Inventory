<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
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
        $admins = User::where('role', 'admin')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'state', 'lga', 'created_at']);

        return view('admin.admin-index', compact('user', 'admins'));
    }

    public function customerIndex(Request $request)
    {
        $this->ensureAdmin($request);

        $user = $request->user();
        $customers = User::where('role', 'customer')
            ->orderBy('state')
            ->orderBy('shop_name')
            ->get(['id', 'name', 'shop_name', 'email', 'phone', 'address', 'state', 'lga', 'created_at']);

        return view('admin.customer-index', compact('user', 'customers'));
    }

    /* ── Staff: customers in their state ── */
    public function staffCustomerIndex(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'staff'], true)) abort(403);

        $customers = User::where('role', 'customer')
            ->when($user->role === 'staff', fn ($q) => $q->where('state', $user->state))
            ->orderBy('shop_name')
            ->orderBy('name')
            ->get(['id', 'name', 'shop_name', 'email', 'phone', 'address', 'state', 'lga', 'created_at']);

        return view('admin.customer-index', compact('user', 'customers'));
    }

    /* ── Customer detail (admin + staff) ── */
    public function customerShow(Request $request, User $customer)
    {
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'staff'], true)) abort(403);
        if ($customer->role !== 'customer') abort(404);

        // Staff can only view customers in their state
        if ($user->role === 'staff' && $customer->state !== $user->state) abort(403);

        $orders = Order::where('user_id', $customer->id)
            ->withCount('items')
            ->with(['payments' => fn ($q) => $q->where('status', 'approved')])
            ->latest()
            ->get();

        $payments = Payment::where('user_id', $customer->id)
            ->with('order:id,order_number')
            ->latest()
            ->get();

        $deliveries = Delivery::whereHas('order', fn ($q) => $q->where('user_id', $customer->id))
            ->with(['order:id,order_number,total_amount', 'staff:id,name'])
            ->latest()
            ->get();

        // Summary stats
        $totalOrders   = $orders->count();
        $totalPaid     = Payment::where('user_id', $customer->id)->where('status', 'approved')->sum('amount');
        $totalDebt     = (float) DB::table('orders')
            ->whereIn('orders.status', ['approved', 'delivered'])
            ->where('orders.user_id', $customer->id)
            ->leftJoinSub(
                DB::table('payments')
                    ->where('status', 'approved')
                    ->select('order_id', DB::raw('SUM(amount) as paid'))
                    ->groupBy('order_id'),
                'ps', 'ps.order_id', '=', 'orders.id'
            )
            ->whereRaw('orders.total_amount > COALESCE(ps.paid, 0)')
            ->selectRaw('COALESCE(SUM(orders.total_amount - COALESCE(ps.paid, 0)), 0) as debt')
            ->value('debt');

        return view('customers.show', compact(
            'user', 'customer',
            'orders', 'payments', 'deliveries',
            'totalOrders', 'totalPaid', 'totalDebt'
        ));
    }

    private function ensureAdmin(Request $request): void
    {
        if (($request->user()->role ?? null) !== 'admin') {
            abort(403);
        }
    }
}
