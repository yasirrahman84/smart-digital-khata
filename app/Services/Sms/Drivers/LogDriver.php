<?php

namespace App\Services\Sms\Drivers;

use App\Services\Sms\SmsDriver;
use App\Services\Sms\SmsResult;
use Illuminate\Support\Facades\Log;

/**
 * Default driver. Writes the message to the log and the sms_messages table
 * instead of spending money, which makes the demo safe and repeatable.
 */
class LogDriver implements SmsDriver
{
    public function send(string $to, string $body): SmsResult
    {
        Log::info('SMS (log driver)', ['to' => $to, 'body' => $body]);

        return SmsResult::logged();
    }
}
