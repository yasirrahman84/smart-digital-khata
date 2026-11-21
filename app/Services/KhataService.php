<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\LedgerEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class KhataService
{
    /**
     * Record a single khata entry (udhaar or payment) for a customer.
     */
    public function postEntry(Customer $customer, array $data): LedgerEntry
    {
        return DB::transaction(function () use ($customer, $data) {
            return LedgerEntry::create([
                'user_id' => $customer->user_id,
                'customer_id' => $customer->id,
                'sale_id' => $data['sale_id'] ?? null,
                'type' => $data['type'],
                'amount' => round((float) $data['amount'], 2),
                'entry_date' => $data['entry_date'] ?? now()->toDateString(),
                'description' => $data['description'] ?? null,
            ]);
        });
    }

    /**
     * Ledger entries with a running balance attached to each row.
     *
     * The running balance starts at the opening balance plus everything that
     * happened before the requested window, so filtered statements stay correct.
     */
    public function statement(Customer $customer, ?string $from = null, ?string $to = null): Collection
    {
        $opening = (float) $customer->opening_balance;

        if ($from) {
            $opening += $this->netChangeBefore($customer, $from);
        }

        $entries = $customer->ledgerEntries()
            ->when($from, fn ($query) => $query->whereDate('entry_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('entry_date', '<=', $to))
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        $running = round($opening, 2);

        return $entries->map(function (LedgerEntry $entry) use (&$running) {
            $running = round($running + $entry->signedAmount(), 2);
            $entry->running_balance = $running;

            return $entry;
        });
    }

    /**
     * Net movement (udhaar minus payments) for entries before a given date.
     */
    public function netChangeBefore(Customer $customer, string $date): float
    {
        $totals = $customer->ledgerEntries()
            ->whereDate('entry_date', '<', $date)
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'udhaar' THEN amount ELSE 0 END), 0) AS udhaar_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'payment' THEN amount ELSE 0 END), 0) AS payment_total")
            ->first();

        return round(
            (float) ($totals->udhaar_total ?? 0) - (float) ($totals->payment_total ?? 0),
            2
        );
    }
}
