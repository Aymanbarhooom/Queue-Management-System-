<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Review extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'business_id',
        'rating',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
