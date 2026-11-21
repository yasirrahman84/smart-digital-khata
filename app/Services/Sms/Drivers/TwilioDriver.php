<?php

namespace App\Services\Sms\Drivers;

use App\Services\Sms\SmsDriver;
use App\Services\Sms\SmsResult;
use Illuminate\Support\Facades\Http;

class TwilioDriver implements SmsDriver
{
    public function send(string $to, string $body): SmsResult
    {
        $sid = (string) config('services.sms.twilio.sid');
        $token = (string) config('services.sms.twilio.token');
        $from = (string) config('services.sms.from');

        if ($sid === '' || $token === '' || $from === '') {
            return SmsResult::failed('Twilio is not configured. Set TWILIO_SID, TWILIO_TOKEN and SMS_FROM.');
        }

        try {
            $response = Http::asForm()
                ->withBasicAuth($sid, $token)
                ->timeout(15)
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => $from,
                    'To' => $to,
                    'Body' => $body,
                ]);

            if ($response->failed()) {
                return SmsResult::failed($response->json('message') ?? 'Twilio request failed.');
            }

            return SmsResult::sent($response->json('sid'));
        } catch (\Throwable $e) {
            return SmsResult::failed($e->getMessage());
        }
    }
}
