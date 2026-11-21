<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ReminderService;
use Illuminate\Console\Command;

class SendPaymentReminders extends Command
{
    protected $signature = 'khata:send-reminders {--dry-run : List who would be reminded without sending anything}';

    protected $description = 'Send SMS payment reminders to customers with an outstanding balance';

    public function handle(ReminderService $reminders): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $sent = 0;
        $failed = 0;

        $users = User::where('sms_enabled', true)->get();

        if ($users->isEmpty()) {
            $this->info('No shops have SMS reminders enabled.');

            return self::SUCCESS;
        }

        foreach ($users as $user) {
            $due = $reminders->dueCustomers($user);

            if ($due->isEmpty()) {
                $this->line("{$user->storeName()}: nothing due.");

                continue;
            }

            $rows = $due->map(fn ($customer) => [
                $customer->name,
                $customer->phone,
                'Rs '.number_format($customer->calculateBalance(), 0),
                $customer->last_reminder_at?->diffForHumans() ?? 'never',
            ]);

            if ($dryRun) {
                $this->info("{$user->storeName()} — {$due->count()} reminder(s) would be sent:");
                $this->table(['Customer', 'Phone', 'Balance', 'Last reminder'], $rows);

                continue;
            }

            foreach ($due as $customer) {
                $message = $reminders->send($customer);

                if ($message->status === 'failed') {
                    $failed++;
                    $this->warn("  failed: {$customer->name} — {$message->error}");
                } else {
                    $sent++;
                }
            }

            $this->info("{$user->storeName()}: {$due->count()} processed.");
        }

        if ($dryRun) {
            $this->comment('Dry run — no messages were sent.');
        } else {
            $this->info("Done. Sent: {$sent}, failed: {$failed}.");
        }

        return self::SUCCESS;
    }
}
