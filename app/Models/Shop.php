<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable as AuthenticatedTrait;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image_url',
        'address',
        'phone',
        'description',
        'is_open',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'latitude',
        'longitude',
    ];

    public function items() : hasMany
    {
        return $this->hasMany(Item::class);
    }

    public function queues() : hasMany {
        return $this->hasMany(Queue::class);
    }

    public function users(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
