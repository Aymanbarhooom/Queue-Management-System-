<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_id',
        'total_bookings',
        'total_on_time',
        'total_cancellations',
        'total_moved_to_no_show',
        'total_no_show_present',
        'total_no_show_absent',
        'session_avg_duration',
    ];

    protected function casts(): array
    {
        return [
            'total_bookings'         => 'integer',
            'total_on_time'          => 'integer',
            'total_cancellations'     => 'integer',
            'total_moved_to_no_show' => 'integer',
            'total_no_show_present'  => 'integer',
            'total_no_show_absent'   => 'integer',
            'session_avg_duration'   => 'float',
        ];
    }


    public function onTimeRate(): float
    {
        if ($this->total_bookings === 0) return 0.0;
        return round($this->total_on_time / $this->total_bookings, 4);
    }


    public function cancelRate(): float
    {
        if ($this->total_bookings === 0) return 0.0;
        return round($this->total_cancellations / $this->total_bookings, 4);
    }


    public function noShowAbsenceRate(): float
    {
        if ($this->total_bookings === 0) return 0.0;
        return round($this->total_no_show_absent / $this->total_bookings, 4);
    }


    public function hasEnoughHistory(): bool
    {
        return $this->serviceSessions()->count() >= 5;
    }

    public function get_avg_duration(): float
    {
        return (float) ($this->serviceSessions()->avg('duration') ?? 0);
    }



    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function serviceSessions(): HasMany
    {
        return $this->hasMany(ServiceSession::class, 'user_statistics_id');
    }
    /**
     * Get a highly precise, behavior-adjusted duration projection.
     *
     * @return array Containing predicted actual time and recommended calendar block time.
     */
    public function calculateBehavioralDuration(): array
    {
        $completedSessions = $this->serviceSessions();
        $completedCount = $completedSessions->count();

        // 1. BASE DURATION (Bayesian Shrinkage)
        // Blends user's personal average with the global service average to handle "cold starts"
        $userRawAvg = $completedCount > 0
            ? (float) $completedSessions->avg('duration')
            : null;

        // Get global average duration for this service across all users
        $globalAvg = (float) (\App\Models\ServiceSession::where('service_id', $this->service_id)
            ->avg('duration') ?? null); // fallback to 30 mins if it is a brand-new service

        // K is our confidence threshold. 
        // If a user has fewer than 5 sessions, we lean more on the global average.
        $k = 5;
        if ($userRawAvg !== null) {
            $baseDuration = (($completedCount * $userRawAvg) + ($k * $globalAvg)) / ($completedCount + $k);
        } else {
            $baseDuration = $globalAvg;
        }

        // 2. PUNCTUALITY DEVIATION (Lateness Penalty)
        // If they are often late (moved_to_no_show but present), their sessions are usually compressed.
        $totalShowedUpBookings = $this->total_bookings - $this->total_cancellations - $this->total_no_show_absent;
        $lateButPresentRate = $totalShowedUpBookings > 0
            ? ($this->total_no_show_present / $totalShowedUpBookings)
            : 0;

        // Cap rate at 1.0 for safety
        $lateButPresentRate = min(1.0, $lateButPresentRate);

        // Assumption: Being late compresses the service duration by up to 20% (0.20 penalty factor)
        $latenessPenaltyFactor = 0.20;
        $punctualityMultiplier = 1.0 - ($lateButPresentRate * $latenessPenaltyFactor);

        // Calculate Predicted Actual Duration
        $predictedActualDuration = $baseDuration * $punctualityMultiplier;

        // 3. FLAKINESS & VARIANCE BUFFER (Scheduling Safety margin)
        // We calculate a safety margin to block out on the calendar based on user's volatility.
        $flakinessIndex = $this->total_bookings > 0
            ? (($this->total_cancellations + $this->total_no_show_absent) / $this->total_bookings)
            : 0;

        $bufferMinutes = 0.0;
        // Rule A: If they cancel/no-show on more than 20% of bookings, add a 10% safety buffer
        if ($flakinessIndex > 0.20) {
            $bufferMinutes += ($predictedActualDuration * 0.10);
        }

        // Rule B: Standard Deviation (Consistency of past session durations)
        if ($completedCount >= 3) {
            $durations = $completedSessions->pluck('duration');
            $mean = $durations->avg();

            $variance = $durations->reduce(function ($carry, $item) use ($mean) {
                return $carry + pow($item - $mean, 2);
            }, 0) / ($completedCount - 1);

            $stdDev = sqrt($variance);

            // If their session variance is high (std deviation > 5 mins), 
            // add 50% of their standard deviation as a protective buffer on the calendar.
            if ($stdDev > 5) {
                $bufferMinutes += ($stdDev * 0.5);
            }
        }

        $suggestedCalendarBlock = $predictedActualDuration + $bufferMinutes;

        return [
            'raw_user_average' => $userRawAvg ? round($userRawAvg, 1) : null,
            'predicted_actual_duration' => round($predictedActualDuration, 1),
            'suggested_calendar_block' => round($suggestedCalendarBlock, 1),
            'reliability_score' => round((1 - $flakinessIndex) * 100, 1), // percentage out of 100
            'punctuality_rate' => round((1 - $lateButPresentRate) * 100, 1)
        ];
    }
    public function getAvgDuration(): float
    {
        return (float) ($this->serviceSessions()->avg('duration') ?? 0);
    }
}
