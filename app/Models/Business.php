<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'description',
        'longitude',//decimal
        'latitude',//decimal
        'phone',
        'image',//string
        'avg_rating',
    ];

    protected function casts(): array
    {
        return [
            'longitude' => 'decimal:7',
            'latitude'  => 'decimal:7',
            'avg_rating' => 'float',
        ];
    }


    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function workingTimes(): HasMany
    {
        return $this->hasMany(BusinessWorkingTime::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'employee');
    }

    public function userStatistics(): HasMany
    {
        return $this->hasMany(UserStatistic::class);
    }
    public function Reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function scopeTopRated(Builder $query): Builder
    {
        return $query->orderByDesc('avg_rating')->limit(4);
    }
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? asset('storage/' . $value) : null,
        );
    }
}