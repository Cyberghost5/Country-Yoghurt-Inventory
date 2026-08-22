<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\DeliveryAllocation;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_customer_with_cascading_records(): void
    {
        // 1. Arrange: Create admin and customer
        $admin    = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        // Create orders and order items
        $order = Order::create([
            'order_number' => 'ORD-00001',
            'user_id'      => $customer->id,
            'total_amount' => 10000,
            'status'       => 'approved',
        ]);
        $orderItem = $order->items()->create([
            'product_name' => 'Vanilla Yoghurt',
            'unit_price'   => 5000,
            'quantity'     => 2,
            'subtotal'     => 10000,
        ]);

        // Create delivery allocation and items
        $delivery = Delivery::create([
            'delivery_number' => 'DLV-0001/26',
            'staff_id'        => $admin->id,
            'scheduled_at'    => now()->toDateString(),
            'status'          => 'dispatched',
        ]);
        $allocation = DeliveryAllocation::create([
            'delivery_id'  => $delivery->id,
            'customer_id'  => $customer->id,
            'total_amount' => 20000,
        ]);
        $allocItem = $allocation->items()->create([
            'product_name' => 'Vanilla Yoghurt',
            'unit_price'   => 5000,
            'quantity'     => 4,
            'subtotal'     => 20000,
        ]);

        // Create payment
        $payment = Payment::create([
            'user_id'                => $customer->id,
            'delivery_allocation_id' => $allocation->id,
            'payment_number'         => 'PAY-20260822-00001',
            'amount'                 => 10000,
            'payment_method'         => 'cash',
            'status'                 => 'approved',
        ]);

        // Verify initial database state
        $this->assertEquals(2, User::count());
        $this->assertEquals(1, Order::count());
        $this->assertEquals(1, DeliveryAllocation::count());
        $this->assertEquals(1, Payment::count());

        $this->actingAs($admin);

        // 2. Act: Send delete request
        $response = $this->delete(route('users.destroy', $customer));

        // 3. Assert: redirection, customer deletion, and cascade deletions
        $response->assertRedirect(route('admin.customers.index'));
        $response->assertSessionHas('status', "Customer account '{$customer->name}' and all associated records deleted successfully.");

        // Assert database clean up
        $this->assertEquals(1, User::count()); // only admin remains
        $this->assertNull(User::find($customer->id));

        $this->assertEquals(0, Order::count());
        $this->assertEquals(0, DeliveryAllocation::count());
        $this->assertEquals(0, Payment::count());

        // Assert items are also deleted
        $this->assertDatabaseMissing('order_items', ['id' => $orderItem->id]);
        $this->assertDatabaseMissing('delivery_allocation_items', ['id' => $allocItem->id]);
    }
}
