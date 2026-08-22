<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Delivery;
use App\Models\DeliveryAllocation;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_reset_system_with_correct_code(): void
    {
        // 1. Arrange: Setup databases
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $admin      = User::factory()->create(['role' => 'admin']);
        $customer   = User::factory()->create(['role' => 'customer']);

        $product = Product::create([
            'name'          => 'Vanilla Yoghurt',
            'unit'          => 'pack',
            'selling_price' => 5000,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-00001',
            'user_id'      => $customer->id,
            'total_amount' => 10000,
            'status'       => 'approved',
        ]);
        $order->items()->create([
            'product_name' => 'Vanilla Yoghurt',
            'unit_price'   => 5000,
            'quantity'     => 2,
            'subtotal'     => 10000,
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
            'total_amount' => 20000,
        ]);
        $allocation->items()->create([
            'product_name' => 'Vanilla Yoghurt',
            'unit_price'   => 5000,
            'quantity'     => 4,
            'subtotal'     => 20000,
        ]);

        $payment = Payment::create([
            'user_id'                => $customer->id,
            'delivery_allocation_id' => $allocation->id,
            'payment_number'         => 'PAY-20260822-00001',
            'amount'                 => 10000,
            'payment_method'         => 'cash',
            'status'                 => 'approved',
        ]);

        $bankAccount = BankAccount::create([
            'bank_name'      => 'GTBank',
            'account_number' => '0123456789',
            'account_name'   => 'Country Yoghurt Ltd',
            'staff_id'       => $admin->id,
            'created_by'     => $admin->id,
        ]);

        // Assert setup counts
        $this->assertEquals(3, User::count());
        $this->assertEquals(1, Product::count());
        $this->assertEquals(1, Order::count());
        $this->assertEquals(1, DeliveryAllocation::count());
        $this->assertEquals(1, Payment::count());
        $this->assertEquals(1, BankAccount::count());

        $this->actingAs($superAdmin);

        // 2. Act: POST reset request with correct code
        $response = $this->post(route('admin.system.reset.perform'), [
            'reset_code' => 'CountryYoghurtReset2026',
        ]);

        // 3. Assert redirection and cleaned tables
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('status', 'System has been successfully wiped and reset. Only your Super Admin account remains.');

        $this->assertEquals(1, User::count());
        $this->assertEquals($superAdmin->id, User::first()->id);

        $this->assertEquals(0, Product::count());
        $this->assertEquals(0, Order::count());
        $this->assertEquals(0, DeliveryAllocation::count());
        $this->assertEquals(0, Payment::count());
        $this->assertEquals(0, BankAccount::count());
        $this->assertEquals(0, Delivery::count());
    }

    public function test_super_admin_cannot_reset_system_with_incorrect_code(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin);

        // Act: POST reset with wrong code
        $response = $this->post(route('admin.system.reset.perform'), [
            'reset_code' => 'WRONG_CODE_123',
        ]);

        $response->assertSessionHasErrors('reset_code');
        $this->assertEquals(1, User::count()); // nothing wiped
    }

    public function test_regular_admin_cannot_access_reset(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get(route('admin.system.reset.show'));
        $response->assertStatus(403);

        $response2 = $this->post(route('admin.system.reset.perform'), [
            'reset_code' => 'CountryYoghurtReset2026',
        ]);
        $response2->assertStatus(403);
    }
}
