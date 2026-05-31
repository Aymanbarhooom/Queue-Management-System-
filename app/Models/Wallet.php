<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'held_balance',
        'status', // ('active', 'suspended', 'frozen')
    ];

    protected function casts(): array
    {
        return [
            'balance'      => 'decimal:2',
            'held_balance' => 'decimal:2',
            'status'       => 'string',
        ];
    }


    public function isActive(): bool    { return $this->status === 'active'; }
    public function isSuspended(): bool { return $this->status === 'suspended'; }
    public function isFrozen(): bool    { return $this->status === 'frozen'; }


    public function availableBalance(): float
    {
        return (float) $this->balance - (float) $this->held_balance;
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processes(): HasMany
    {
        return $this->hasMany(Process::class);
    }
}