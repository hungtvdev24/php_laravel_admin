<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Mess;

class MessageCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Mess $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('messages.' . $this->message->receiver_id);
    }

    public function broadcastWith()
    {
        return ['message' => $this->message];
    }
}