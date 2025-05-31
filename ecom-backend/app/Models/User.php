<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'online'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'online' => 'boolean'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Messages where user is the sender
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    // Messages where user is the recipient
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }

    // All messages related to the user
    public function messages()
    {
        return Message::where(function($query) {
            $query->where('sender_id', $this->id)
                  ->orWhere('recipient_id', $this->id);
        });
    }
}
