<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', //'user', 'employee', 'manager', 'admin'
        'business_id',
        'image'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'role'              => 'string',
        ];
    }

    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isManager(): bool  { return $this->role === 'manager'; }
    public function isEmployee(): bool { return $this->role === 'employee'; }
    public function isUser(): bool     { return $this->role === 'user'; }


    public function business(): BelongsTo //employee belongs to one business
    {
        return $this->belongsTo(Business::class);
    }

    public function managedBusiness(): HasOne //manager manages one business
    {
        return $this->hasOne(Business::class, 'user_id');
    }

    public function queue(): HasOne //employee has one queue
    {
        return $this->hasOne(Queue::class, 'user_id');
    }

    public function tickets(): HasMany //user has many tickets
    {
        return $this->hasMany(Ticket::class);
    }

    public function wallet(): HasOne //user has one wallet
    {
        return $this->hasOne(Wallet::class);
    }

    public function statistics(): HasMany //user has many statistics
    {
        return $this->hasMany(UserStatistic::class);
    }
    public function services(): HasMany //user has many services
    {
        return $this->hasMany(UserService::class);
    }
    public function notifications(): HasMany //user has many notifications
    {
        return $this->hasMany(Notification::class);
    }
    public function reviews(): HasMany //user has many reviews
    {
        return $this->hasMany(Review::class);
    }
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? asset('storage/' . $value) : null,
        );
    }
}