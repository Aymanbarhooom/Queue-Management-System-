<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'queue_id',
        'number',
        'status', // enum ('pending', 'canceled', 'handling', 'no_show', 'completed')
        'expected_start_time',//dateTime
        'expected_wait_min',//unsignedSmallInteger
        'final_session_duration',
    ];

    protected function casts(): array
    {
        return [
            'expected_start_time'    => 'datetime',
            'expected_wait_min'      => 'integer',
          //  'expected_wait_max'      => 'integer',
            'final_session_duration' => 'integer',
            'number'                 => 'integer',
            'status'                 => 'string',
        ];
    }

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isCanceled(): bool  { return $this->status === 'canceled'; }
    public function isHandling(): bool  { return $this->status === 'handling'; }
    public function isNo_show(): bool { return $this->status === 'no_show'; }
    public function isCompleted(): bool {return $this->status === 'completed'; }
    public function isExpired(): bool { return $this->status === 'expired'; }

    
    public function waitRange(): string
    {
        if (is_null($this->expected_wait_min) || is_null($this->expected_wait_max)) {
            return 'N/A';
        }
        return "{$this->expected_wait_min} - {$this->expected_wait_max} min";
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }

    public function sentSwaps(): HasMany
    {
        return $this->hasMany(TicketSwap::class, 'requester_ticket_id');
    }

    public function receivedSwaps(): HasMany
    {
        return $this->hasMany(TicketSwap::class, 'receiver_ticket_id');
    }

    public function pendingSentSwap(): HasOne
    {
        return $this->hasOne(TicketSwap::class, 'requester_ticket_id')
                    ->where('status', 'pending');
    }

    public function pendingReceivedSwap(): HasOne
    {
        return $this->hasOne(TicketSwap::class, 'receiver_ticket_id')
                    ->where('status', ['pending', 'no_show']);
    }
    public function serviceSessions(): HasMany
    {
        return $this->hasMany(ServiceSession::class);
    }


    public function queuePosition(): int
    {
    return $this->queue
        ->tickets()
        ->whereIn('status',['pending','no_show'])
        ->where('number','<',$this->number)
        ->count() + 1;
    }
}