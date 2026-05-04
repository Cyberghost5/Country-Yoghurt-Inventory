<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    /* ── Index ── */
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Payment::with(['order', 'user'])->latest();

        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $payments = $query->paginate(20)->withQueryString();

        $base = $user->role !== 'admin'
            ? Payment::where('user_id', $user->id)
            : Payment::query();

        $counts = [
            'all'      => (clone $base)->count(),
            'pending'  => (clone $base)->where('status', 'pending')->count(),
            'approved' => (clone $base)->where('status', 'approved')->count(),
            'rejected' => (clone $base)->where('status', 'rejected')->count(),
        ];

        return view('payments.index', compact('user', 'payments', 'counts'));
    }

    /* ── Create form ── */
    public function create(Request $request)
    {
        $user = $request->user();

        // Optionally pre-link to a specific order
        $order = null;
        if ($orderId = $request->input('order_id')) {
            $order = Order::find($orderId);

            if ($order) {
                if ($user->role !== 'admin' && $order->user_id !== $user->id) abort(403);

                if (!in_array($order->status, ['approved', 'delivered'])) {
                    return redirect()->route('orders.show', $order)
                        ->with('error', 'Only approved or delivered orders can receive a payment.');
                }

                $active = $order->payments()->whereIn('status', ['pending', 'approved'])->first();
                if ($active) {
                    return redirect()->route('payments.show', $active)
                        ->with('error', 'This order already has an active payment submission.');
                }
            }
        }

        // Orders the user can pay (for the dropdown)
        $payableOrders = Order::where('status', 'approved')
            ->orWhere('status', 'delivered')
            ->when($user->role !== 'admin', fn ($q) => $q->where('user_id', $user->id))
            ->orderByDesc('created_at')
            ->get(['id', 'order_number', 'total_amount', 'status']);

        return view('payments.create', compact('user', 'order', 'payableOrders'));
    }

    /* ── Store ── */
    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'order_id'       => 'nullable|integer|exists:orders,id',
            'reason'         => 'required_without:order_id|nullable|string|max:1000',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:bank_transfer,cash,pos,mobile_money',
            'reference'      => 'nullable|string|max:100',
            'proof'          => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $order = null;
        if ($orderId = $request->input('order_id')) {
            $order = Order::findOrFail($orderId);

            if ($user->role !== 'admin' && $order->user_id !== $user->id) abort(403);

            if (!in_array($order->status, ['approved', 'delivered'])) {
                return back()->withInput()
                    ->withErrors(['order_id' => 'That order cannot receive a payment in its current status.']);
            }

            $active = $order->payments()->whereIn('status', ['pending', 'approved'])->first();
            if ($active) {
                return redirect()->route('payments.show', $active)
                    ->with('error', 'This order already has an active payment submission.');
            }
        }

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('payments', 'public');
        }

        $payment = Payment::create([
            'order_id'       => $order?->id,
            'user_id'        => $user->id,
            'amount'         => $request->input('amount'),
            'payment_method' => $request->input('payment_method'),
            'reference'      => $request->input('reference'),
            'proof_path'     => $proofPath,
            'notes'          => $request->input('notes'),
            'reason'         => $request->input('reason'),
            'status'         => 'pending',
        ]);

        $adminUser = User::where('role', 'admin')->first();
        if ($adminUser) {
            $payment->loadMissing('order');
            $adminUser->notify(new PaymentNotification('submitted', $payment));
        }

        return redirect()->route('payments.show', $payment)
            ->with('status', 'Payment submitted. Awaiting admin approval.');
    }

    /* ── Show ── */
    public function show(Request $request, Payment $payment)
    {
        $user = $request->user();

        if ($user->role !== 'admin' && $payment->user_id !== $user->id) {
            abort(403);
        }

        $payment->load(['order', 'user', 'reviewer']);

        return view('payments.show', compact('user', 'payment'));
    }

    /* ── Approve (admin only) ── */
    public function approve(Request $request, Payment $payment)
    {
        $user = $request->user();
        if ($user->role !== 'admin') abort(403);

        if ($payment->status !== 'pending') {
            return redirect()->route('payments.show', $payment)
                ->with('error', 'Only pending payments can be approved.');
        }

        $payment->update([
            'status'      => 'approved',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);

        $payment->user->notify(new PaymentNotification('approved', $payment));

        return redirect()->route('payments.show', $payment)
            ->with('status', 'Payment approved.');
    }

    /* ── Reject (admin only) ── */
    public function reject(Request $request, Payment $payment)
    {
        $user = $request->user();
        if ($user->role !== 'admin') abort(403);

        if ($payment->status !== 'pending') {
            return redirect()->route('payments.show', $payment)
                ->with('error', 'Only pending payments can be rejected.');
        }

        $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $payment->update([
            'status'           => 'rejected',
            'reviewed_by'      => $user->id,
            'reviewed_at'      => now(),
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        $payment->user->notify(new PaymentNotification('rejected', $payment));

        return redirect()->route('payments.show', $payment)
            ->with('status', 'Payment rejected.');
    }
}
