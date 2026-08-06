<?php

namespace App\Jobs;

use App\Models\SmsLog;
use App\Services\Sms\SetareganSmsClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
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
        public ?int $smsLogId = null,
    ) {}

    public function handle(SetareganSmsClient $client): void
    {
        $log = $this->resolveLog();

        try {
            $result = $client->send($this->to, $this->message);

            $log->update([
                'status' => $result['ok'] ? 'queued' : 'failed',
                'provider_code' => $result['code'],
                'provider_message' => $result['message'],
                'provider_message_id' => data_get($result, 'data.message_id'),
                'cost' => data_get($result, 'data.cost'),
                'response' => $result['body'],
            ]);

            if (! $result['ok']) {
                throw new RuntimeException(
                    sprintf('SMS send failed [%s]: %s', $result['code'] ?? 'unknown', $result['message'] ?? 'No message')
                );
            }
        } catch (Throwable $exception) {
            if ($log->status !== 'failed') {
                $log->update([
                    'status' => 'failed',
                    'provider_message' => $exception->getMessage(),
                ]);
            }

            throw $exception;
        }
    }

    protected function resolveLog(): SmsLog
    {
        if ($this->smsLogId !== null) {
            return SmsLog::query()->findOrFail($this->smsLogId);
        }

        return SmsLog::query()->create([
            'user_id' => $this->userId,
            'to' => $this->to,
            'message' => $this->message,
            'gateway' => config('services.sms.gateway'),
            'status' => 'pending',
        ]);
    }
}
