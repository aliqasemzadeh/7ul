<?php

namespace App\Services\Sms;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SetareganSmsClient
{
    /**
     * @return array{ok: bool, code: string|null, message: string|null, data: array<string, mixed>|null, status: int, body: array<string, mixed>|null}
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function send(string $to, string $message): array
    {
        $token = config('services.sms.token');
        $gateway = config('services.sms.gateway');
        $url = config('services.sms.url');

        if (blank($token) || blank($gateway) || blank($url)) {
            throw new RuntimeException('SMS service is not configured.');
        }

        $response = Http::timeout(10)
            ->connectTimeout(3)
            ->retry([100, 500, 1000], throw: false)
            ->withToken($token)
            ->acceptJson()
            ->asJson()
            ->post($url, [
                'to' => $to,
                'message' => $message,
                'gateway' => $gateway,
            ]);

        /** @var array<string, mixed>|null $body */
        $body = $response->json();

        return [
            'ok' => (bool) data_get($body, 'ok', false),
            'code' => data_get($body, 'code'),
            'message' => data_get($body, 'message'),
            'data' => data_get($body, 'data'),
            'status' => $response->status(),
            'body' => $body,
        ];
    }
}
