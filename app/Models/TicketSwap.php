<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketSwap extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_ticket_id',
        'receiver_ticket_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isAccepted(): bool  { return $this->status === 'accepted'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }
    public function isCanceled(): bool  { return $this->status === 'canceled'; }


    public function requesterTicket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'requester_ticket_id');
    }

    public function receiverTicket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'receiver_ticket_id');
    }
}