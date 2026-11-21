<?php

namespace App\Services\Sms\Drivers;

use App\Services\Sms\SmsDriver;
use App\Services\Sms\SmsResult;
use Illuminate\Support\Facades\Http;

/**
 * Generic HTTP gateway for local Pakistani SMS providers. The payload is
 * intentionally simple; adjust the keys here to match your provider.
 */
class HttpDriver implements SmsDriver
{
    public function send(string $to, string $body): SmsResult
    {
        $url = (string) config('services.sms.http.url');
        $key = (string) config('services.sms.http.key');
        $from = (string) config('services.sms.from');

        if ($url === '') {
            return SmsResult::failed('No SMS gateway URL configured. Set SMS_HTTP_URL.');
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders($key !== '' ? ['Authorization' => "Bearer {$key}"] : [])
                ->post($url, [
                    'to' => $to,
                    'message' => $body,
                    'sender' => $from,
                ]);

            if ($response->failed()) {
                return SmsResult::failed('Gateway responded with HTTP '.$response->status());
            }

            return SmsResult::sent((string) ($response->json('id') ?? $response->json('message_id') ?? ''));
        } catch (\Throwable $e) {
            return SmsResult::failed($e->getMessage());
        }
    }
}
