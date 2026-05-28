<?php

namespace Tests\Feature;

use App\Models\SmsLog;
use App\Models\SmsLogRecipient;
use App\Models\User;
use App\Services\BulkSmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmsRetryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_retry_failed_sms_broadcasts(): void
    {
        // 1. Arrange: Create an admin user and log them in
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '2348000000000']);
        $this->actingAs($admin);

        // Mock BulkSmsService to succeed on some and fail on others
        $this->mock(BulkSmsService::class, function ($mock) {
            // First retry succeeds, second fails
            $mock->shouldReceive('send')
                ->with('2348111111111', 'Test retry message')
                ->once()
                ->andReturn(true);
            $mock->shouldReceive('send')
                ->with('2348222222222', 'Test retry message')
                ->once()
                ->andReturn(false);
        });

        // Create SMS log
        $log = SmsLog::create([
            'sender_id'       => $admin->id,
            'recipient_type'  => 'custom',
            'message'         => 'Test retry message',
            'recipient_count' => 3,
            'sent_count'      => 1,
            'failed_count'    => 2,
            'status'          => 'partial',
        ]);

        // Create recipients: 1 sent, 2 failed
        $recipientSent = SmsLogRecipient::create([
            'sms_log_id' => $log->id,
            'user_id'    => $admin->id,
            'name'       => 'Sent User',
            'phone'      => '2348000000000',
            'status'     => 'sent',
        ]);

        $recipientFailed1 = SmsLogRecipient::create([
            'sms_log_id' => $log->id,
            'user_id'    => $admin->id,
            'name'       => 'Failed User 1',
            'phone'      => '2348111111111',
            'status'     => 'failed',
        ]);

        $recipientFailed2 = SmsLogRecipient::create([
            'sms_log_id' => $log->id,
            'user_id'    => $admin->id,
            'name'       => 'Failed User 2',
            'phone'      => '2348222222222',
            'status'     => 'failed',
        ]);

        // 2. Act: POST to retry route
        $response = $this->post(route('admin.sms.retry', $log));

        // 3. Assert: Verify redirect and database state
        $response->assertRedirect(route('admin.sms.show', $log));
        $response->assertSessionHas('status');

        $log->refresh();
        $this->assertEquals(2, $log->sent_count);
        $this->assertEquals(1, $log->failed_count);
        $this->assertEquals('partial', $log->status);

        $this->assertEquals('sent', $recipientFailed1->refresh()->status);
        $this->assertEquals('failed', $recipientFailed2->refresh()->status);
    }
}
