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
        'email_verified_at',
        'password',
        'image_url',
        'phone',
        'address',
        'description',
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
