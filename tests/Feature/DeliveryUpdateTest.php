<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\DeliveryAllocation;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_edit_preserves_existing_payments(): void
    {
        // 1. Arrange: Create users, product, delivery, allocation and payment
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        
        $product = Product::create([
            'name'          => 'Vanilla Yoghurt',
            'unit'          => 'pack',
            'selling_price' => 5000,
        ]);

        $delivery = Delivery::create([
            'delivery_number' => 'DLV-0001/26',
            'staff_id'        => $admin->id,
            'scheduled_at'    => now()->toDateString(),
            'status'          => 'dispatched',
        ]);

        $allocation = DeliveryAllocation::create([
            'delivery_id'  => $delivery->id,
            'customer_id'  => $customer->id,
            'total_amount' => 10000,
        ]);

        $payment = Payment::create([
            'delivery_allocation_id' => $allocation->id,
            'user_id'                => $customer->id,
            'payment_number'         => 'PAY-20260822-00001',
            'amount'                 => 5000,
            'payment_method'         => 'bank_transfer',
            'status'                 => 'approved',
        ]);

        $this->actingAs($admin);

        // 2. Act: PUT request to edit the delivery (updating scheduled_at and details)
        $response = $this->put(route('deliveries.update', $delivery), [
            'scheduled_at' => now()->addDay()->toDateString(),
            'notes'        => 'Updated notes',
            'customers'    => [
                [
                    'customer_id'     => $customer->id,
                    'allocation_date' => now()->addDay()->toDateString(),
                    'notes'           => 'Updated customer notes',
                    'items'           => [
                        [
                            'product_name' => 'Vanilla Yoghurt',
                            'quantity'     => 3,
                        ]
                    ]
                ]
            ]
        ]);

        // 3. Assert: Verify response is a redirect and database states
        $response->assertRedirect(route('deliveries.show', $delivery));

        $delivery->refresh();
        $this->assertEquals(now()->addDay()->toDateString(), $delivery->scheduled_at->toDateString());
        $this->assertEquals('Updated notes', $delivery->notes);

        // Verify the allocation is still there, updated total amount, and retains the same ID
        $newAllocation = $delivery->allocations()->where('customer_id', $customer->id)->first();
        $this->assertNotNull($newAllocation);
        $this->assertEquals($allocation->id, $newAllocation->id); // ID preserved!
        $this->assertEquals(15000, $newAllocation->total_amount); // 3 * 5000 = 15000

        // Verify that the payment's delivery_allocation_id is STILL connected and NOT null
        $payment->refresh();
        $this->assertEquals($newAllocation->id, $payment->delivery_allocation_id);
    }
}
