<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentCreatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_tracks_creator_and_displays_it_in_list_and_details_views(): void
    {
        // 1. Arrange: Create customer, staff, and admin users
        $customer = User::factory()->create(['role' => 'customer', 'name' => 'John Customer']);
        $staff    = User::factory()->create(['role' => 'staff', 'name' => 'Sarah Staff']);
        $admin    = User::factory()->create(['role' => 'admin', 'name' => 'Albert Admin']);

        // Log in as staff to post a payment on behalf of customer
        $this->actingAs($staff);

        // 2. Act: Post payment on behalf of customer
        $response = $this->post(route('payments.store'), [
            'payment_type'   => 'other',
            'customer_id'    => $customer->id,
            'reason'         => 'Paid for extra yoghurt packages',
            'amount'         => 15000,
            'payment_method' => 'cash',
            'notes'          => 'Handed cash directly in shop',
        ]);

        // 3. Assert database: payment created, user_id is customer, created_by is staff
        $payment = Payment::first();
        $this->assertNotNull($payment);
        $this->assertEquals($customer->id, $payment->user_id);
        $this->assertEquals($staff->id, $payment->created_by);
        $response->assertRedirect(route('payments.show', $payment));

        // 4. Act: Log in as admin to check payment index view
        $this->actingAs($admin);
        $indexResponse = $this->get(route('payments.index'));
        $indexResponse->assertStatus(200);
        
        // Assert that the index view renders customer as "Submitted By" and staff as "Posted By"
        $indexResponse->assertSee('John Customer');
        $indexResponse->assertSee('Sarah Staff');

        // 5. Act: Check payment show view as admin
        $showResponse = $this->get(route('payments.show', $payment));
        $showResponse->assertStatus(200);
        
        // Assert that the show view renders both cards correctly
        $showResponse->assertSee('Submitted By');
        $showResponse->assertSee('John Customer');
        $showResponse->assertSee('Posted By');
        $showResponse->assertSee('Sarah Staff');
    }
}
