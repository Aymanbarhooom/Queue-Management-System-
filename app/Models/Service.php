<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'description',
        'price', //decimal
        'base_duration', //unsignedSmallInteger
    ];

    protected function casts(): array
    {
        return [
            'price'         => 'decimal:2',
            'base_duration' => 'integer',
        ];
    }

  
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    public function userStatistics(): HasMany
    {
        return $this->hasMany(UserStatistic::class);
    }
}