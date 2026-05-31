<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Process extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'type', //'deposit', 'withdrawal', 'hold', 'release', 'refund'
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'type'   => 'string',
        ];
    }

    public function isDeposit(): bool    { return $this->type === 'deposit'; }
    public function isWithdrawal(): bool { return $this->type === 'withdrawal'; }
    public function isHold(): bool       { return $this->type === 'hold'; }
    public function isRelease(): bool    { return $this->type === 'release'; }
    public function isRefund(): bool     { return $this->type === 'refund'; }
    public function isBookingFee(): bool { return $this->type === 'booking_fee'; }
    public function isManagerPayout(): bool { return $this->type === 'manager_payout'; }
    public function isPlatformFee(): bool { return $this->type === 'platform_fee'; }


    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}