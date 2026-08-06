<?php

namespace Tests\Feature\Jobs;

use App\Jobs\SendSmsJob;
use App\Models\SmsLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class SendSmsJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.sms', [
            'token' => 'test-token',
            'gateway' => '1000',
            'url' => 'https://srscrm.ir/api/sms/send',
        ]);
    }

    public function test_job_logs_successful_sms_send(): void
    {
        $user = User::factory()->create();

        $log = SmsLog::query()->create([
            'user_id' => $user->id,
            'to' => $user->mobile,
            'message' => 'test message',
            'gateway' => '1000',
            'status' => 'pending',
        ]);

        Http::fake([
            'https://srscrm.ir/api/sms/send' => Http::response([
                'ok' => true,
                'code' => 'queued',
                'message' => 'SMS queued',
                'data' => [
                    'message_id' => 42,
                    'cost' => 120,
                ],
            ]),
        ]);

        (new SendSmsJob(
            to: $user->mobile,
            message: 'test message',
            userId: $user->id,
            smsLogId: $log->id,
        ))->handle(app(\App\Services\Sms\SetareganSmsClient::class));

        $log->refresh();

        $this->assertSame('queued', $log->status);
        $this->assertSame('queued', $log->provider_code);
        $this->assertSame(42, $log->provider_message_id);
        $this->assertEquals('120.00', $log->cost);
        $this->assertIsArray($log->response);
    }

    public function test_job_logs_failed_sms_send(): void
    {
        $user = User::factory()->create();

        $log = SmsLog::query()->create([
            'user_id' => $user->id,
            'to' => $user->mobile,
            'message' => 'test message',
            'gateway' => '1000',
            'status' => 'pending',
        ]);

        Http::fake([
            'https://srscrm.ir/api/sms/send' => Http::response([
                'ok' => false,
                'code' => 'invalid_token',
                'message' => 'Invalid token',
                'data' => null,
            ], 401),
        ]);

        try {
            (new SendSmsJob(
                to: $user->mobile,
                message: 'test message',
                userId: $user->id,
                smsLogId: $log->id,
            ))->handle(app(\App\Services\Sms\SetareganSmsClient::class));

            $this->fail('Expected RuntimeException was not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('invalid_token', $exception->getMessage());
        }

        $log->refresh();

        $this->assertSame('failed', $log->status);
        $this->assertSame('invalid_token', $log->provider_code);
    }
}
