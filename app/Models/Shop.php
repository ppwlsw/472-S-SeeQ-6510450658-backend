<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    use HasFactory;
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
        'approve_status',
        'user_id',
        'location',
        'email_verified_at',
    ];

    protected $hidden = [
        'password', 'verification_token',
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'email_verified_at' => 'datetime',
    ];


    public function items() : hasMany
    {
        return $this->hasMany(Item::class);
    }

    public function queues() : hasMany{
        return $this->hasMany(Queue::class);
    }
}
