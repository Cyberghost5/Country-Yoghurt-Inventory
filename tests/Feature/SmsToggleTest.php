<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\BulkSmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SmsToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_toggle_sms_globally(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Default setting should be enabled ('1')
        $this->assertEquals('1', Setting::get('sms_enabled', '1'));

        $this->actingAs($admin);

        // 1. Act: Disable SMS
        $response = $this->post(route('admin.sms.toggle'), []); // no sms_enabled parameter means disable
        $response->assertRedirect();
        $response->assertSessionHas('status', 'SMS sending has been disabled globally.');

        // Assert DB state
        $this->assertEquals('0', Setting::get('sms_enabled', '1'));

        // Assert BulkSmsService suppresses sending (returns true)
        Log::shouldReceive('info')
            ->once()
            ->withArgs(fn($msg) => str_contains($msg, 'suppressed (SMS disabled globally)'));
        
        $smsService = app(BulkSmsService::class);
        $result = $smsService->send('2348000000000', 'Test suppression');
        $this->assertTrue($result);

        // 2. Act: Enable SMS
        $response = $this->post(route('admin.sms.toggle'), ['sms_enabled' => '1']);
        $response->assertRedirect();
        $response->assertSessionHas('status', 'SMS sending has been enabled globally.');

        // Assert DB state
        $this->assertEquals('1', Setting::get('sms_enabled', '1'));
    }
}
