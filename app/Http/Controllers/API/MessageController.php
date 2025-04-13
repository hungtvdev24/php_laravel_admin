<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Mess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class MessageController extends Controller
{
    public function sendMessage(Request $request)
    {
        \Log::info('sendMessage called', $request->all());

        $request->validate([
            'receiver_id' => 'required|integer',
            'receiver_type' => 'required|string|in:App\Models\User,App\Models\Admin,App\Models\Employee',
            'content' => 'required|string',
        ]);

        $sender = Auth::user();
        if (!$sender) {
            \Log::error('sendMessage: User not authenticated');
            return response()->json(['error' => 'Vui lòng đăng nhập để gửi tin nhắn.'], 401);
        }

        $message = Mess::create([
            'sender_id' => $sender->id,
            'sender_type' => User::class,
            'receiver_id' => $request->receiver_id,
            'receiver_type' => $request->receiver_type,
            'content' => $request->content,
            'is_read' => false,
        ]);

        \Log::info('Message created', ['message' => $message]);

        // Kích hoạt sự kiện broadcasting đến kênh chat.{receiver_id}
        event(new \App\Events\MessageSent($message));

        return response()->json(['message' => 'Tin nhắn đã được gửi!', 'data' => $message], 201);
    }

    public function getMessages($receiverId, Request $request)
    {
        \Log::info('getMessages called', [
            'receiverId' => $receiverId,
            'receiverType' => $request->query('receiver_type', 'App\Models\User'),
            'user' => Auth::user()
        ]);

        $receiverType = $request->query('receiver_type', 'App\Models\User');
        if (!in_array($receiverType, ['App\Models\User', 'App\Models\Admin', 'App\Models\Employee'])) {
            \Log::error('getMessages: Invalid receiver type', ['receiverType' => $receiverType]);
            return response()->json(['error' => 'Loại người nhận không hợp lệ.'], 400);
        }

        $sender = Auth::user();
        if (!$sender) {
            \Log::error('getMessages: User not authenticated');
            return response()->json(['error' => 'Vui lòng đăng nhập để xem tin nhắn.'], 401);
        }

        $messages = Mess::where(function ($query) use ($sender, $receiverId, $receiverType) {
            $query->where('sender_id', $sender->id)
                  ->where('sender_type', User::class)
                  ->where('receiver_id', $receiverId)
                  ->where('receiver_type', $receiverType);
        })->orWhere(function ($query) use ($sender, $receiverId, $receiverType) {
            $query->where('sender_id', $receiverId)
                  ->where('sender_type', $receiverType)
                  ->where('receiver_id', $sender->id)
                  ->where('receiver_type', User::class);
        })->orderBy('created_at', 'asc')->get();

        \Log::info('Messages fetched', ['messages' => $messages]);

        return response()->json(['data' => $messages], 200);
    }

    public function markAsRead($messageId)
    {
        \Log::info('markAsRead called', ['messageId' => $messageId]);

        $user = Auth::user();
        if (!$user) {
            \Log::error('markAsRead: User not authenticated');
            return response()->json(['error' => 'Vui lòng đăng nhập để thực hiện hành động này.'], 401);
        }

        $message = Mess::find($messageId);
        if (!$message) {
            \Log::error('markAsRead: Message not found', ['messageId' => $messageId]);
            return response()->json(['error' => 'Tin nhắn không tồn tại.'], 404);
        }

        if ($message->receiver_id !== $user->id || $message->receiver_type !== User::class) {
            \Log::error('markAsRead: Unauthorized', [
                'receiver_id' => $message->receiver_id,
                'receiver_type' => $message->receiver_type,
                'user_id' => $user->id
            ]);
            return response()->json(['error' => 'Bạn không có quyền đánh dấu tin nhắn này.'], 403);
        }

        $message->is_read = true;
        $message->save();

        \Log::info('Message marked as read', ['message' => $message]);

        // Broadcast sự kiện cập nhật trạng thái tin nhắn đến kênh chat.{receiver_id}
        event(new \App\Events\MessageSent($message));

        return response()->json(['message' => 'Tin nhắn đã được đánh dấu là đã đọc!'], 200);
    }
}