<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\SmsMessage;
use App\Models\User;
use App\Services\Sms\SmsService;
use Illuminate\Support\Collection;

class ReminderService
{
    public function __construct(private SmsService $sms) {}

    /**
     * Customers who are active, have a phone number, owe money, and have not
     * been reminded within the merchant's reminder window.
     */
    public function dueCustomers(User $user): Collection
    {
        $cutoff = now()->subDays(max(1, (int) $user->reminder_after_days));

        return $user->customers()
            ->where('is_active', true)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->withBalance()
            ->get()
            ->filter(fn (Customer $customer) => $customer->balance > 0)
            ->filter(fn (Customer $customer) => $customer->last_reminder_at === null
                || $customer->last_reminder_at->lte($cutoff))
            ->values();
    }

    public function messageFor(Customer $customer): string
    {
        $user = $customer->user;
        $balance = number_format($customer->calculateBalance(), 0);
        $link = route('public.statement', $customer->public_token);

        return "Assalam-o-Alaikum {$customer->name}, your balance at {$user->storeName()} is Rs {$balance}. "
            ."View statement: {$link} - {$user->storeName()}";
    }

    public function send(Customer $customer): SmsMessage
    {
        $body = $this->messageFor($customer);
        $result = $this->sms->send((string) $customer->phone, $body);

        $message = SmsMessage::create([
            'user_id' => $customer->user_id,
            'customer_id' => $customer->id,
            'phone' => $customer->phone,
            'body' => $body,
            'status' => $result->status,
            'provider' => config('services.sms.driver', 'log'),
            'provider_message_id' => $result->messageId,
            'error' => $result->error,
            'sent_at' => now(),
        ]);

        if ($result->success) {
            $customer->forceFill(['last_reminder_at' => now()])->save();
        }

        return $message;
    }
}
