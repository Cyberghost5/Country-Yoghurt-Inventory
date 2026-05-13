<?php

namespace App\Http\Controllers;

use App\Models\DeliveryAllocation;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentNotification;
use App\Services\BulkSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    /* ── Index ── */
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Payment::with(['order', 'user', 'deliveryAllocation.delivery'])->latest();

        if ($user->role === 'staff') {
            $stateCustomerIds = User::where('role', 'customer')
                ->whereIn('state', $user->staffStates())
                ->pluck('id');
            $query->whereIn('user_id', $stateCustomerIds);
        } elseif ($user->role === 'customer') {
            $query->where('user_id', $user->id);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $payments = $query->paginate(20)->withQueryString();

        $base = match ($user->role) {
            'staff'    => Payment::whereIn('user_id', User::where('role', 'customer')->whereIn('state', $user->staffStates())->pluck('id')),
            'customer' => Payment::where('user_id', $user->id),
            default    => Payment::query(),
        };

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
                if (!$user->isAdminOrStaff() && $order->user_id !== $user->id) abort(403);

                if (!in_array($order->status, ['approved', 'delivered'])) {
                    return redirect()->route('orders.show', $order)
                        ->with('error', 'Only approved or delivered orders can receive a payment.');
                }

                if ($order->isFullyPaid()) {
                    return redirect()->route('orders.show', $order)
                        ->with('error', 'This order is already fully paid.');
                }
            }
        }

        // For staff/admin: load customers so they can select on whose behalf this payment is
        $customers = collect();
        if ($user->isAdminOrStaff()) {
            $customers = User::where('role', 'customer')
                ->when($user->role === 'staff', fn ($q) => $q->whereIn('state', $user->staffStates()))
                ->orderBy('name')
                ->get(['id', 'name', 'shop_name', 'state']);
        }

        // Orders the current user can pay (for direct customer use)
        $payableOrders = collect();
        if ($user->role === 'customer') {
            $payableOrders = Order::whereIn('status', ['approved', 'delivered'])
                ->where('user_id', $user->id)
                ->whereRaw('total_amount > COALESCE((SELECT SUM(p.amount) FROM payments p WHERE p.order_id = orders.id AND p.status = ?), 0)', ['approved'])
                ->orderByDesc('created_at')
                ->get(['id', 'order_number', 'total_amount', 'status']);
        }

        return view('payments.create', compact('user', 'order', 'payableOrders', 'customers'));
    }

    /* ── Store ── */
    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'payment_type'           => 'required|in:order,other',
            'order_id'               => 'nullable|integer|exists:orders,id',
            'customer_id'            => 'nullable|integer|exists:users,id',
            'reason'                 => 'nullable|string|max:1000',
            'amount'                 => 'required|numeric|min:0.01',
            'payment_method'         => 'required|in:bank_transfer,cash,pos,mobile_money',
            'reference'              => 'nullable|string|max:100',
            'proof'                  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'notes'                  => 'nullable|string|max:1000',
        ]);

        $type  = $request->input('payment_type');
        $order = null;

        if ($type === 'order' && $request->filled('order_id')) {
            $order = Order::findOrFail($request->input('order_id'));
            if (!$user->isAdminOrStaff() && $order->user_id !== $user->id) abort(403);
            if (!in_array($order->status, ['approved', 'delivered'])) {
                return back()->withInput()->withErrors(['order_id' => 'That order cannot receive a payment in its current status.']);
            }
            if ($order->isFullyPaid()) {
                return back()->withInput()->withErrors(['order_id' => 'This order is already fully paid.']);
            }
            $remaining = $order->remainingAmount();
            if (round((float) $request->input('amount'), 2) > $remaining) {
                return back()->withInput()->withErrors(['amount' => 'Amount exceeds the remaining balance of ₦' . number_format($remaining, 2) . ' on this order.']);
            }
        } elseif ($type === 'other' && !$request->filled('reason')) {
            return back()->withInput()->withErrors(['reason' => 'A reason is required for a standalone payment.']);
        }

        // Determine the payment owner
        $paymentOwnerId = $user->id;
        if ($user->isAdminOrStaff()) {
            if ($order) {
                $paymentOwnerId = $order->user_id;
            } elseif ($request->filled('customer_id')) {
                $customer = User::where('role', 'customer')
                    ->when($user->role === 'staff', fn ($q) => $q->whereIn('state', $user->staffStates()))
                    ->findOrFail($request->integer('customer_id'));
                $paymentOwnerId = $customer->id;
            }
        }

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('payments', 'public');
        }

        $payment = Payment::create([
            'order_id'               => $order?->id,
            'delivery_allocation_id' => null,
            'user_id'                => $paymentOwnerId,
            'payment_number'         => $this->generatePaymentNumber(),
            'amount'                 => round((float) $request->input('amount'), 2),
            'payment_method'         => $request->input('payment_method'),
            'reference'              => $request->input('reference'),
            'proof_path'             => $proofPath,
            'notes'                  => $request->input('notes'),
            'reason'                 => $request->input('reason'),
            'status'                 => 'pending',
        ]);

        $adminUser = User::whereIn('role', ['admin', 'super_admin'])->first();
        if ($adminUser) {
            $payment->loadMissing('order');
            $adminUser->notify(new PaymentNotification('submitted', $payment));
        }

        return redirect()->route('payments.show', $payment)
            ->with('status', 'Payment submitted. Awaiting admin approval.');
    }

    private function generatePaymentNumber(): string
    {
        $date  = now()->format('Ymd');
        $count = Payment::whereDate('created_at', today())->count();
        return 'PAY-' . $date . '-' . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
    }

    /* ── Show ── */
    public function show(Request $request, Payment $payment)
    {
        $user = $request->user();

        if ($user->role === 'customer' && $payment->user_id !== $user->id) {
            abort(403);
        }

        if ($user->role === 'staff') {
            $stateCustomerIds = User::where('role', 'customer')
                ->whereIn('state', $user->staffStates())
                ->pluck('id');
            if (!$stateCustomerIds->contains($payment->user_id)) {
                abort(403);
            }
        }

        $payment->load(['order', 'user', 'reviewer']);

        return view('payments.show', compact('user', 'payment'));
    }

    /* ── Approve (admin only) ── */
    public function approve(Request $request, Payment $payment)
    {
        $user = $request->user();
        if (!$user->isAdmin()) abort(403);

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

        // SMS to customer
        $customer = $payment->user;
        if ($customer && $customer->phone) {
            $payment->loadMissing(['order', 'deliveryAllocation.delivery']);
            if ($payment->order) {
                $ref       = "for order {$payment->order->order_number}";
                $remaining = $payment->order->remainingAmount();
                $balancePart = $remaining > 0
                    ? " Outstanding balance: NGN " . number_format($remaining, 2) . "."
                    : " Order is now fully paid.";
            } elseif ($payment->deliveryAllocation?->delivery) {
                $ref       = "for delivery {$payment->deliveryAllocation->delivery->delivery_number}";
                $remaining = $payment->deliveryAllocation->remainingAmount();
                $balancePart = $remaining > 0
                    ? " Outstanding balance: NGN " . number_format($remaining, 2) . "."
                    : " Delivery is now fully paid.";
            } else {
                $ref         = $payment->reason ? "({$payment->reason})" : '';
                $balancePart = '';
            }
            $message = "Hi {$customer->name}, your payment of NGN "
                     . number_format($payment->amount, 2)
                     . " {$ref} has been received.{$balancePart} Thank you! - Country Yoghurt";
            app(BulkSmsService::class)->send($customer->phone, $message);
        }

        // Notify all super_admins about this approval
        $approverName = $user->name;
        User::where('role', 'super_admin')->get()
            ->each(fn ($sa) => $sa->notify(new PaymentNotification('admin_approved', $payment, $approverName)));

        return redirect()->route('payments.show', $payment)
            ->with('status', 'Payment approved.');
    }

    /* ── Reject (admin only) ── */
    public function reject(Request $request, Payment $payment)
    {
        $user = $request->user();
        if (!$user->isAdmin()) abort(403);

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
