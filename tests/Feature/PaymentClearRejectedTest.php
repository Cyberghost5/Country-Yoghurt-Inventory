<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentClearRejectedTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_clear_all_rejected_payments(): void
    {
        // 1. Arrange: Create admin and some rejected payments
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        Payment::create([
            'user_id'        => $customer->id,
            'payment_number' => 'PAY-20260822-00001',
            'amount'         => 1000,
            'payment_method' => 'cash',
            'status'         => 'rejected',
        ]);

        Payment::create([
            'user_id'        => $customer->id,
            'payment_number' => 'PAY-20260822-00002',
            'amount'         => 2000,
            'payment_method' => 'pos',
            'status'         => 'rejected',
        ]);

        Payment::create([
            'user_id'        => $customer->id,
            'payment_number' => 'PAY-20260822-00003',
            'amount'         => 3000,
            'payment_method' => 'bank_transfer',
            'status'         => 'pending', // Make sure pending is NOT deleted!
        ]);

        $this->actingAs($admin);

        // 2. Act: Send delete request
        $response = $this->delete(route('payments.clearRejected'));

        // 3. Assert: redirection and database state
        $response->assertRedirect(route('payments.index'));
        $response->assertSessionHas('status', 'Successfully cleared 2 rejected payment record(s).');

        // Check DB: pending is still there, rejected are deleted
        $this->assertEquals(1, Payment::count());
        $this->assertEquals(0, Payment::where('status', 'rejected')->count());
        $this->assertEquals(1, Payment::where('status', 'pending')->count());
    }
}
