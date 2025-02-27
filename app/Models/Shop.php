<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Shop extends Model
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'image_url',
        'address',
        'phone',
        'description',
        'is_open',
        'latitude',
        'longitude',
        'user_id'
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

    public function reminders() : HasMany
    {
        return $this->hasMany(Reminder::class);
    }

}
