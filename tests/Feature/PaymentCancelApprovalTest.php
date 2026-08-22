<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentCancelApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_cancel_approved_payment(): void
    {
        // 1. Arrange: Create users, order, and approved payment
        $admin    = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        $order = Order::create([
            'order_number' => 'ORD-00001',
            'user_id'      => $customer->id,
            'total_amount' => 100000,
            'status'       => 'approved',
        ]);

        $payment = Payment::create([
            'order_id'       => $order->id,
            'user_id'        => $customer->id,
            'payment_number' => 'PAY-20260822-00001',
            'amount'         => 60000,
            'payment_method' => 'bank_transfer',
            'status'         => 'approved',
            'reviewed_by'    => $admin->id,
            'reviewed_at'    => now(),
        ]);

        // Assert remaining balance is 40k
        $this->assertEquals(40000, $order->remainingAmount());

        $this->actingAs($admin);

        // 2. Act: Cancel approval
        $response = $this->post(route('payments.cancelApproval', $payment));

        // 3. Assert: status reset, fields nullified, balance recalculated
        $response->assertRedirect(route('payments.show', $payment));

        $payment->refresh();
        $this->assertEquals('pending', $payment->status);
        $this->assertNull($payment->reviewed_by);
        $this->assertNull($payment->reviewed_at);

        // Remaining balance should go back to 100k because the payment is no longer approved
        $this->assertEquals(100000, $order->remainingAmount());
    }
}
