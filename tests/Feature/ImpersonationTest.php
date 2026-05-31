<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_impersonate_any_user(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $targetUser = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($superAdmin)
            ->post(route('users.impersonate', $targetUser));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('status', 'Now impersonating ' . $targetUser->name . '.');
        $this->assertEquals($targetUser->id, auth()->id());
        $this->assertEquals($superAdmin->id, session('impersonating_admin_id'));
    }

    public function test_regular_admin_cannot_impersonate_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($admin)
            ->post(route('users.impersonate', $targetUser));

        $response->assertStatus(403);
        $this->assertNull(session('impersonating_admin_id'));
        $this->assertEquals($admin->id, auth()->id());
    }

    public function test_staff_cannot_impersonate_users(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $targetUser = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($staff)
            ->post(route('users.impersonate', $targetUser));

        $response->assertStatus(403);
        $this->assertNull(session('impersonating_admin_id'));
        $this->assertEquals($staff->id, auth()->id());
    }

    public function test_customer_cannot_impersonate_users(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $targetUser = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($customer)
            ->post(route('users.impersonate', $targetUser));

        $response->assertStatus(403);
        $this->assertNull(session('impersonating_admin_id'));
        $this->assertEquals($customer->id, auth()->id());
    }

    public function test_super_admin_cannot_impersonate_self(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($superAdmin)
            ->post(route('users.impersonate', $superAdmin));

        $response->assertStatus(422);
        $this->assertNull(session('impersonating_admin_id'));
        $this->assertEquals($superAdmin->id, auth()->id());
    }

    public function test_super_admin_can_stop_impersonating(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $targetUser = User::factory()->create(['role' => 'customer']);

        // Set up the impersonation session
        $this->actingAs($superAdmin);
        
        $response = $this->post(route('users.impersonate', $targetUser));
        $response->assertRedirect(route('dashboard'));

        // Now we are acting as the customer, but session('impersonating_admin_id') is set
        $this->assertEquals($targetUser->id, auth()->id());

        // Stop impersonating
        $stopResponse = $this->post(route('impersonate.stop'));
        $stopResponse->assertRedirect(route('dashboard'));
        $stopResponse->assertSessionHas('status', 'Returned to your admin account.');

        $this->assertEquals($superAdmin->id, auth()->id());
        $this->assertNull(session('impersonating_admin_id'));
    }
}
