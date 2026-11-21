<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'address',
        'notes',
        'opening_balance',
        'is_active',
        'last_reminder_at',
        'public_token',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'is_active' => 'boolean',
            'last_reminder_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Customer $customer) {
            $customer->public_token ??= Str::random(32);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function smsMessages(): HasMany
    {
        return $this->hasMany(SmsMessage::class);
    }

    /**
     * Add the udhaar and payment totals as `udhaar_total` / `payment_total`
     * attributes so lists can render balances without N+1 queries.
     */
    public function scopeWithBalance(Builder $query): Builder
    {
        return $query
            ->withSum(['ledgerEntries as udhaar_total' => fn ($q) => $q->where('type', LedgerEntry::TYPE_UDHAAR)], 'amount')
            ->withSum(['ledgerEntries as payment_total' => fn ($q) => $q->where('type', LedgerEntry::TYPE_PAYMENT)], 'amount');
    }

    /**
     * The one source of truth for what this customer owes.
     *
     * balance = opening_balance + sum(udhaar) - sum(payment)
     * A negative balance means the customer has paid in advance.
     */
    protected function balance(): Attribute
    {
        return Attribute::get(function (): float {
            $udhaar = $this->attributes['udhaar_total'] ?? null;
            $payment = $this->attributes['payment_total'] ?? null;

            if ($udhaar !== null || $payment !== null) {
                return round((float) $this->opening_balance + (float) $udhaar - (float) $payment, 2);
            }

            return $this->calculateBalance();
        });
    }

    public function calculateBalance(): float
    {
        $totals = $this->ledgerEntries()
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'udhaar' THEN amount ELSE 0 END), 0) AS udhaar_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'payment' THEN amount ELSE 0 END), 0) AS payment_total")
            ->first();

        return round(
            (float) $this->opening_balance
                + (float) ($totals->udhaar_total ?? 0)
                - (float) ($totals->payment_total ?? 0),
            2
        );
    }

    public function hasOutstandingBalance(): bool
    {
        return $this->balance > 0;
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');
    }
}
