<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UsersUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $user;
    public $users;

    public function __construct($user, $users)
    {
        $this->user = $user;
        $this->users = $users;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('users-updates');
    }

    public function broadcastWith()
    {
        return [
            'user' => $this->user,
            'users' => $this->users
        ];
    }
}
