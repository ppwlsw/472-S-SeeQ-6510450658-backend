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
        'address',
        'shop_phone',
        'description',
        'shop_image_url',
        'isOpen',
        'approve_status',
        'user_id',
        'location',
    ];

    public function users() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items() : hasMany
    {
        return $this->hasMany(Item::class);
    }

    public function queues() : hasMany{
        return $this->hasMany(Queue::class);
    }

    public function reminders() : hasMany{
        return $this->hasMany(Reminder::class);
    }
}
