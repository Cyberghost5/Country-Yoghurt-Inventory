<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use App\Notifications\DeliveryNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    /* ── Index ── */
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Delivery::with(['order', 'staff'])->latest();

        if ($user->role === 'staff') {
            $query->where('staff_id', $user->id);
        } elseif ($user->role !== 'admin') {
            abort(403);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $deliveries = $query->paginate(20)->withQueryString();

        $baseQuery = Delivery::query();
        if ($user->role === 'staff') {
            $baseQuery->where('staff_id', $user->id);
        }

        $counts = [
            'all'       => (clone $baseQuery)->count(),
            'pending'   => (clone $baseQuery)->where('status', 'pending')->count(),
            'approved'  => (clone $baseQuery)->where('status', 'approved')->count(),
            'delivered' => (clone $baseQuery)->where('status', 'delivered')->count(),
        ];

        return view('deliveries.index', compact('user', 'deliveries', 'counts'));
    }

    /* ── Create form (staff only) ── */
    public function create(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'staff') abort(403);

        // Load approved orders that don't already have an active (pending/approved) delivery
        $approvedOrders = Order::where('status', 'approved')
            ->whereDoesntHave('deliveries', fn ($q) => $q->whereIn('status', ['pending', 'approved']))
            ->orderByDesc('created_at')
            ->get(['id', 'order_number', 'total_amount', 'user_id']);

        // Pre-selected order from query string
        $order = null;
        if ($orderId = $request->query('order_id')) {
            $order = Order::find($orderId);
            if ($order && $order->status !== 'approved') {
                $order = null;
            }
        }

        return view('deliveries.create', compact('user', 'approvedOrders', 'order'));
    }

    /* ── Store (staff only) ── */
    public function store(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'staff') abort(403);

        $data = $request->validate([
            'order_id'         => 'required|integer|exists:orders,id',
            'delivery_address' => 'required|string|max:500',
            'scheduled_at'     => 'nullable|date|after_or_equal:today',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $order = Order::findOrFail($data['order_id']);

        if ($order->status !== 'approved') {
            return back()->withInput()
                ->withErrors(['order_id' => 'Only approved orders can be scheduled for delivery.']);
        }

        // Prevent duplicate active delivery
        $exists = $order->deliveries()
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['order_id' => 'This order already has an active delivery scheduled.']);
        }

        $delivery = Delivery::create([
            'order_id'         => $order->id,
            'staff_id'         => $user->id,
            'delivery_address' => $data['delivery_address'],
            'scheduled_at'     => $data['scheduled_at'] ?? null,
            'notes'            => $data['notes'] ?? null,
            'status'           => 'pending',
        ]);

        $delivery->load('order.user', 'staff');
        $adminUser = User::where('role', 'admin')->first();
        if ($adminUser) {
            $adminUser->notify(new DeliveryNotification('scheduled', $delivery));
        }

        return redirect()->route('deliveries.show', $delivery)
            ->with('status', "Delivery for {$order->order_number} scheduled. Awaiting admin approval.");
    }

    /* ── Show ── */
    public function show(Request $request, Delivery $delivery)
    {
        $user = $request->user();

        if ($user->role === 'staff' && $delivery->staff_id !== $user->id) {
            abort(403);
        } elseif ($user->role === 'customer') {
            abort(403);
        }

        $delivery->load(['order.user', 'order.items', 'staff', 'approvedBy']);

        return view('deliveries.show', compact('user', 'delivery'));
    }

    /* ── Approve (admin only) ── */
    public function approve(Request $request, Delivery $delivery)
    {
        $user = $request->user();
        if ($user->role !== 'admin') abort(403);

        if ($delivery->status !== 'pending') {
            return redirect()->route('deliveries.show', $delivery)
                ->with('error', 'Only pending deliveries can be approved.');
        }

        $delivery->update([
            'status'      => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $delivery->load('order.user', 'staff');
        $delivery->staff->notify(new DeliveryNotification('approved', $delivery));

        return redirect()->route('deliveries.show', $delivery)
            ->with('status', "Delivery approved and marked as out for delivery.");
    }

    /* ── Mark Delivered (admin only) ── */
    public function markDelivered(Request $request, Delivery $delivery)
    {
        $user = $request->user();
        if ($user->role !== 'admin') abort(403);

        if ($delivery->status !== 'approved') {
            return redirect()->route('deliveries.show', $delivery)
                ->with('error', 'Only approved (out for delivery) deliveries can be marked as delivered.');
        }

        DB::transaction(function () use ($delivery) {
            $delivery->update([
                'status'       => 'delivered',
                'delivered_at' => now(),
            ]);

            $delivery->order()->update(['status' => 'delivered']);
        });

        $delivery->refresh()->load('order.user', 'staff');
        if ($delivery->order?->user) {
            $delivery->order->user->notify(new DeliveryNotification('delivered', $delivery));
        }

        return redirect()->route('deliveries.show', $delivery)
            ->with('status', "Delivery marked as delivered. Order {$delivery->order->order_number} is now closed.");
    }
}
