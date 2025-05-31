<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user_id;
    public $status;

    public function __construct($user_id, $status)
    {
        $this->user_id = $user_id;
        $this->status = $status;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('messages');
    }

    public function broadcastWith()
    {
        return [
            'user_id' => $this->user_id,
            'status' => $this->status
        ];
    }
}
