<?php

namespace App\Services\Sms;

class SmsService
{
    public function __construct(private SmsDriver $driver) {}

    public function send(string $to, string $body): SmsResult
    {
        return $this->driver->send($to, $body);
    }

    public function driver(): SmsDriver
    {
        return $this->driver;
    }
}
