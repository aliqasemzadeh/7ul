<?php

namespace Tests\Feature\Jobs;

use App\Jobs\SendSmsJob;
use App\Models\User;
use App\Services\Sms\SetareganSmsClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
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

        Config::set('logging.channels.sms', [
            'driver' => 'null',
        ]);
    }

    public function test_job_logs_successful_sms_send(): void
    {
        Event::fake([MessageLogged::class]);

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

        Event::assertDispatched(MessageLogged::class, function (MessageLogged $event): bool {
            return $event->level === 'info'
                && str_contains($event->message, 'SMS send queued');
        });
    }

    public function test_job_logs_failed_sms_send(): void
    {
        Event::fake([MessageLogged::class]);

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

        Event::assertDispatched(MessageLogged::class, function (MessageLogged $event): bool {
            return $event->level === 'error'
                && str_contains($event->message, 'SMS send failed');
        });
    }
}
