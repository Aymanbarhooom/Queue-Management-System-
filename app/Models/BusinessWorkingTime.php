<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessWorkingTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'day_of_week', //enum (sundy, monday...)
        'open_hour',
        'close_hour',
        'is_closed',
    ];

    protected function casts(): array
    {
        return [
            'is_closed'  => 'boolean',
            'open_hour'  => 'string',
            'close_hour' => 'string',
        ];
    }



    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function isOpenNow(): bool
{
   $today=strtolower(now()->format('l'));

   $schedule=
      $this->workingTimes()
       ->where('day_of_week',$today)
       ->first();

   if(!$schedule || $schedule->is_closed){
      return false;
   }

   $now=now()->format('H:i');

   return
      $now >= $schedule->open_hour
      &&
      $now <= $schedule->close_hour;
}
}