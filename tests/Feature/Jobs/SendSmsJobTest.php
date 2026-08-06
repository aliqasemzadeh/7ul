<?php

namespace Tests\Feature\Jobs;

use App\Jobs\SendSmsJob;
use App\Models\User;
use App\Services\Sms\SetareganSmsClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        Log::fake();

        $user = User::factory()->create();

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
        ))->handle(app(SetareganSmsClient::class));

        Log::channel('sms')->assertLogged('info', fn ($message) => str_contains($message, 'SMS send queued'));
    }

    public function test_job_logs_failed_sms_send(): void
    {
        Log::fake();

        $user = User::factory()->create();

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
            ))->handle(app(SetareganSmsClient::class));

            $this->fail('Expected RuntimeException was not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('invalid_token', $exception->getMessage());
        }

        Log::channel('sms')->assertLogged('error', fn ($message) => str_contains($message, 'SMS send failed'));
    }
}
