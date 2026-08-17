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
        $sessions = $this->serviceSessions()
            ->orderBy('created_at', 'desc')
            ->get();
        
        $count = $sessions->count();
        
        if ($count === 0) {
            return 0.0;
        }
        
        if ($count <= 5) {
            return (float) $sessions->avg('duration') ?? 0.0;
        }
        
        $recentSessions = $sessions->take(5);
        
    
        $weights = [5, 4, 3, 2, 1];
        $weightedSum = 0;
        $totalWeight = array_sum($weights);
        
        $index = 0;
        foreach ($recentSessions as $session) {
            $weightedSum += $session->duration * $weights[$index];
            $index++;
        }
        
        return round($weightedSum / $totalWeight, 2);
    }

    
    public function calculateBehavioralDuration(float $baseDuration): array
    {
        // 1. حساب متوسط المدة الفعلي من الجلسات السابقة
        $historicalAvg = $this->get_avg_duration();
        
        // 2. حساب نسب السلوك المختلفة
        $onTimeRate = $this->onTimeRate();           
        $cancelRate = $this->cancelRate();           
        $noShowAbsenceRate = $this->noShowAbsenceRate();
        
        
        $reliabilityScore = 1 - ($cancelRate + $noShowAbsenceRate);
        $reliabilityScore = max(0, min(1, $reliabilityScore)); // نطاق 0-1
        
        
        $punctualityFactor = 0.5 + ($onTimeRate * 0.5); 
        
        // 5. معامل التعديل بناءً على الخبرة (عدد الحجوزات)
        $experienceFactor = min(1, $this->total_bookings / 20); // بعد 20 حجز يصل للثبات
        
        // 6. حساب المدة المتوقعة
        $predictedDuration = $baseDuration;
        
        // إذا كان هناك تاريخ سابق، استخدمه مع الأوزان
        if ($historicalAvg > 0) {
            // وزن التاريخ مقابل القاعدة الأساسية
            $historyWeight = min(0.7, 0.3 + ($experienceFactor * 0.4));
            $baseWeight = 1 - $historyWeight;
            
            // المدة المتوقعة = (وزن التاريخ × متوسط التاريخ) + (وزن القاعدة × المدة القاعدية)
            $predictedDuration = ($historyWeight * $historicalAvg) + ($baseWeight * $baseDuration);
        }
        
        // 7. تطبيق معامل الالتزام بالمواعيد
        // العميل الملتزم ينهي أسرع، غير الملتزم قد يطيل
        $predictedDuration = $predictedDuration * (1.2 - ($punctualityFactor * 0.4));
        
        // 8. تطبيق معامل الثقة
        // العميل غير الموثوق (كثير الإلغاء والغياب) نحتاج وقت احتياطي
        $bufferFactor = 1 + ((1 - $reliabilityScore) * 0.3); // يضاف حتى 30% وقت احتياطي
        
        // 9. حساب الوقت الموصى به للحجز في التقويم
        $recommendedBlockTime = $predictedDuration * $bufferFactor;
        
        // 10. التأكد من عدم تجاوز الحدود المعقولة
        $minDuration = $baseDuration * 0.7;
        $maxDuration = $baseDuration * 2.0;
        
        $predictedDuration = max($minDuration, min($maxDuration, $predictedDuration));
        $recommendedBlockTime = max($minDuration, min($maxDuration * 1.2, $recommendedBlockTime));
        
        // 11. حساب مستوى الثقة في التوقع (0-100)
        $confidenceLevel = 0;
        if ($this->total_bookings >= 20) {
            $confidenceLevel = 90;
        } elseif ($this->total_bookings >= 10) {
            $confidenceLevel = 70;
        } elseif ($this->total_bookings >= 5) {
            $confidenceLevel = 50;
        } else {
            $confidenceLevel = 30;
        }
        
        // تعديل مستوى الثقة بناءً على معدل السلوك
        if ($reliabilityScore < 0.5) {
            $confidenceLevel *= 0.7;
        } elseif ($reliabilityScore > 0.8) {
            $confidenceLevel *= 1.1;
        }
        $confidenceLevel = min(100, round($confidenceLevel));
        
        return [
            'base_duration' => round($baseDuration, 2),
            'historical_avg' => round($historicalAvg, 2),
            'predicted_duration' => round($predictedDuration, 2),
            'recommended_block_time' => round($recommendedBlockTime, 2),
            'reliability_score' => round($reliabilityScore * 100, 1),
            'on_time_rate' => round($onTimeRate * 100, 1),
            'cancel_rate' => round($cancelRate * 100, 1),
            'no_show_absence_rate' => round($noShowAbsenceRate * 100, 1),
            'confidence_level' => $confidenceLevel,
            'total_bookings' => $this->total_bookings,
        ];
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
}