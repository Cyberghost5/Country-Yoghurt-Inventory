<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\DeliveryAllocation;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_payment_linked_to_delivery_allocation(): void
    {
        // 1. Arrange: Create user, staff, delivery, allocation
        $customer = User::factory()->create(['role' => 'customer', 'phone' => '2348000000000']);
        $staff = User::factory()->create(['role' => 'staff']);

        $delivery = Delivery::create([
            'delivery_number' => 'DLV-0001/26',
            'staff_id'        => $staff->id,
            'scheduled_at'    => now()->toDateString(),
            'status'          => 'dispatched',
        ]);

        $allocation = DeliveryAllocation::create([
            'delivery_id'  => $delivery->id,
            'customer_id'  => $customer->id,
            'total_amount' => 50000,
        ]);

        $this->actingAs($customer);

        // 2. Act: POST to payment store route with delivery allocation linked
        $response = $this->post(route('payments.store'), [
            'payment_type'           => 'delivery',
            'delivery_allocation_id' => $allocation->id,
            'amount'                 => 25000,
            'payment_method'         => 'bank_transfer',
            'reference'              => 'TXN-12345',
            'notes'                  => 'Paying half of the delivery run',
        ]);

        // 3. Assert: Verify database entry and redirect
        $payment = Payment::first();
        $this->assertNotNull($payment);
        $this->assertEquals('pending', $payment->status);
        $this->assertEquals(25000, $payment->amount);
        $this->assertEquals($allocation->id, $payment->delivery_allocation_id);
        $this->assertEquals($customer->id, $payment->user_id);
        
        $response->assertRedirect(route('payments.show', $payment));
    }
}
