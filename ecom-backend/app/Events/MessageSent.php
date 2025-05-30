<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        // Broadcast uniquement aux canaux privés des utilisateurs concernés
        return [
            new PrivateChannel('messages.' . $this->message->sender_id),
            new PrivateChannel('messages.' . $this->message->recipient_id)
        ];
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message->load('sender', 'recipient')
        ];
    }
}
