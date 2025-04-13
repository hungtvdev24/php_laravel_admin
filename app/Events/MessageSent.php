<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Mess;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Mess $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        // Broadcast tới cả sender và receiver để đảm bảo cả hai đều nhận được tin nhắn thời gian thực
        return [
            new Channel('chat.' . $this->message->receiver_id),
            new Channel('chat.' . $this->message->sender_id),
        ];
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }

    public function broadcastWith()
    {
        $senderName = '';
        if ($this->message->sender_type === 'user') {
            $senderName = $this->message->sender->name ?? 'Người dùng';
        } elseif ($this->message->sender_type === 'admin') {
            $senderName = 'Admin';
        } elseif ($this->message->sender_type === 'employee') {
            $senderName = $this->message->sender->tenNhanVien ?? 'Nhân viên';
        }

        return [
            'message' => [
                'id' => $this->message->id,
                'sender_id' => $this->message->sender_id,
                'sender_type' => $this->message->sender_type,
                'sender_name' => $senderName,
                'receiver_id' => $this->message->receiver_id,
                'receiver_type' => $this->message->receiver_type,
                'content' => $this->message->content,
                'is_read' => $this->message->is_read,
                'created_at' => $this->message->created_at->toDateTimeString(),
            ]
        ];
    }
}