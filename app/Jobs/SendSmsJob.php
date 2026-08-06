<?php

namespace App\Jobs;

use App\Services\Sms\SetareganSmsClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SendSmsJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [1, 5, 10];

    public function __construct(
        public string $to,
        public string $message,
        public ?int $userId = null,
    ) {}

    public function handle(SetareganSmsClient $client): void
    {
        $context = [
            'to' => $this->to,
            'user_id' => $this->userId,
            'gateway' => config('services.sms.gateway'),
        ];

        Log::channel('sms')->info('SMS send started', $context);

        try {
            $result = $client->send($this->to, $this->message);

            $context = array_merge($context, [
                'ok' => $result['ok'],
                'code' => $result['code'],
                'provider_message' => $result['message'],
                'message_id' => data_get($result, 'data.message_id'),
                'cost' => data_get($result, 'data.cost'),
                'http_status' => $result['status'],
            ]);

            if (! $result['ok']) {
                Log::channel('sms')->error('SMS send failed', $context);

                throw new RuntimeException(
                    sprintf('SMS send failed [%s]: %s', $result['code'] ?? 'unknown', $result['message'] ?? 'No message')
                );
            }

            Log::channel('sms')->info('SMS send queued', $context);
        } catch (Throwable $exception) {
            Log::channel('sms')->error('SMS send exception', array_merge($context, [
                'exception' => $exception->getMessage(),
            ]));

            throw $exception;
        }
    }
}
