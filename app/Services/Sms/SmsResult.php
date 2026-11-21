<?php

namespace App\Services\Sms;

final class SmsResult
{
    public function __construct(
        public bool $success,
        public string $status,
        public ?string $messageId = null,
        public ?string $error = null,
    ) {}

    public static function logged(?string $messageId = null): self
    {
        return new self(true, 'logged', $messageId);
    }

    public static function sent(?string $messageId = null): self
    {
        return new self(true, 'sent', $messageId);
    }

    public static function failed(string $error): self
    {
        return new self(false, 'failed', null, $error);
    }
}
