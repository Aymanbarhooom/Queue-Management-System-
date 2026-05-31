<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Queue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'service_id',
        'name',
        'description',
        'status', //enum (active, inactive)
        'type', //enum (main, no_show)
        'congestion', //enum (low, medium, high)
    ];

    protected function casts(): array
    {
        return [
            'status'  => 'string',
            'type'    => 'string',
        ];
    }


    public function isMain(): bool    { return $this->type === 'main'; }
    public function isNoShow(): bool  { return $this->type === 'no_show'; }
    public function isActive(): bool  { return $this->status === 'active'; }
    public function isLowCongestion(): bool  { return $this->congestion === 'low'; }
    public function isMediumCongestion(): bool  { return $this->congestion === 'medium'; }
    public function isHighCongestion(): bool  { return $this->congestion === 'high'; }


    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }


    public function pendingTickets(): HasMany
    {
        return $this->hasMany(Ticket::class)
                    ->where('status', 'pending')
                    ->orderBy('number');
    }
    public function estimatedRemainingDuration(): int
    {
    return $this->tickets()
        ->whereIn('status',['pending','no_show'])
        ->sum('final_session_duration');
    }
}