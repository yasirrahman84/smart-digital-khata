<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Customer extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'address',
        'opening_balance',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}