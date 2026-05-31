<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceSession extends Model
{
    use HasFactory;

    protected $table = 'service_sessions';

    protected $fillable = [
        'user_statistics_id',
        'ticket_id',
        'start_time',
        'end_time',
        'duration',//unsignedSmallInteger
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time'   => 'datetime',
            'duration'   => 'integer',
        ];
    }

 
    public function calculateDuration(): int
    {
        if (is_null($this->end_time)) return 0;
        return (int) $this->start_time->diffInMinutes($this->end_time);
    }

   
    public function isOutlier(float $avgDuration): bool
    {
        if ($avgDuration <= 0 || is_null($this->duration)) return false;
        return $this->duration > ($avgDuration * 3);
    }


    public function userStatistic(): BelongsTo
    {
        return $this->belongsTo(UserStatistic::class, 'user_statistics_id');
    }
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}