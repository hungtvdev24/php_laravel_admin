<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function indexAdmin()
    {
        $notifications = Notification::with('users')->latest()->paginate(10);
        return view('admin.affiliate.notifications.index', compact('notifications'));
    }

    public function createAdmin()
    {
        $users = User::all();
        return view('admin.affiliate.notifications.create', compact('users'));
    }

    public function storeAdmin(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $notification = Notification::create([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        $users = $request->user_ids ? User::whereIn('id', $request->user_ids)->get() : User::all();
        $notification->users()->attach($users->pluck('id'), ['is_read' => false]);

        return redirect()->route('admin.affiliate.notifications.index')->with('success', 'Thông báo đã được tạo và gửi thành công!');
    }

    public function showAdmin($id)
    {
        $notification = Notification::with('users')->findOrFail($id);
        return view('admin.affiliate.notifications.detail', compact('notification'));
    }

    public function destroyAdmin($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->users()->detach();
        $notification->delete();

        return redirect()->route('admin.affiliate.notifications.index')->with('success', 'Thông báo đã được xóa thành công!');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userId = $request->query('user_id');
        if (!$userId || $user->id != $userId) {
            return response()->json(['message' => 'Invalid user_id'], 403);
        }

        $notifications = $user->notifications()
            ->withPivot('is_read')
            ->latest()
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'content' => $notification->content,
                    'created_at' => $notification->created_at->toIso8601String(),
                    'users' => [
                        [
                            'pivot' => [
                                'is_read' => (bool) $notification->pivot->is_read,
                            ],
                        ],
                    ],
                ];
            });

        return response()->json(['data' => $notifications], 200);
    }

    public function markAsRead(Request $request, $notificationId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $notification = Notification::find($notificationId);
        if (!$notification || !$user->notifications()->where('notification_id', $notificationId)->exists()) {
            return response()->json(['message' => 'Thông báo không tồn tại hoặc không thuộc về bạn'], 404);
        }

        $user->notifications()->updateExistingPivot($notificationId, ['is_read' => true]);

        return response()->json(['message' => 'Thông báo đã được đánh dấu là đã đọc']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $notification = Notification::create([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        $users = $request->user_ids ? User::whereIn('id', $request->user_ids)->get() : User::all();
        $notification->users()->attach($users->pluck('id'), ['is_read' => false]);

        return response()->json([
            'message' => 'Thông báo đã được tạo và gửi thành công',
            'notification' => $notification,
        ], 201);
    }

    public function destroy($notificationId)
    {
        $notification = Notification::findOrFail($notificationId);
        $notification->users()->detach();
        $notification->delete();

        return response()->json(['message' => 'Thông báo đã được xóa thành công']);
    }
}