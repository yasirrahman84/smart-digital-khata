<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerEntry extends Model
{
    use HasFactory;

    public const TYPE_UDHAAR = 'udhaar';

    public const TYPE_PAYMENT = 'payment';

    protected $fillable = [
        'user_id',
        'customer_id',
        'sale_id',
        'type',
        'amount',
        'entry_date',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'entry_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function isUdhaar(): bool
    {
        return $this->type === self::TYPE_UDHAAR;
    }

    public function isPayment(): bool
    {
        return $this->type === self::TYPE_PAYMENT;
    }

    /**
     * Positive when the customer owes more, negative when they paid.
     */
    public function signedAmount(): float
    {
        return $this->isUdhaar() ? (float) $this->amount : -(float) $this->amount;
    }
}
