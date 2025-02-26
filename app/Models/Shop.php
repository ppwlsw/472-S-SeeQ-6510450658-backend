<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable as AuthenticatedTrait;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Shop extends Model implements Authenticatable
{
    use AuthenticatedTrait;
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'verification_token',
        'address',
        'phone',
        'description',
        'image_url',
        'is_open',
        'latitude',
        'longitude',
        'email_verified_at',
    ];

    protected $hidden = [
        'password', 'verification_token','remember_token'
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'email_verified_at' => 'datetime',
        'is_open',
        'latitude',
        'longitude',
    ];

    public function items() : hasMany
    {
        return $this->hasMany(Item::class);
    }

    public function queues() : hasMany{
        return $this->hasMany(Queue::class);
    }
}
