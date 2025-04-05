<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Hiển thị danh sách thông báo đã gửi (index)
    public function indexAdmin()
    {
        $notifications = Notification::with('users')->latest()->paginate(10); // Sử dụng paginate thay vì get
        return view('admin.affiliate.notifications.index', compact('notifications'));
    }

    // Hiển thị form tạo thông báo mới (create)
    public function createAdmin()
    {
        $users = User::all(); // Lấy danh sách người dùng để admin chọn
        return view('admin.affiliate.notifications.create', compact('users'));
    }

    // Xử lý tạo thông báo mới (store từ form)
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

    // Hiển thị chi tiết thông báo (detail)
    public function showAdmin($id)
    {
        $notification = Notification::with('users')->findOrFail($id);
        return view('admin.affiliate.notifications.detail', compact('notification'));
    }

    // Xóa thông báo
    public function destroyAdmin($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->users()->detach(); // Xóa mối quan hệ với users trong bảng pivot
        $notification->delete(); // Xóa thông báo

        return redirect()->route('admin.affiliate.notifications.index')->with('success', 'Thông báo đã được xóa thành công!');
    }

    // API: Xem danh sách thông báo của người dùng
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications()
                             ->withPivot('is_read')
                             ->latest()
                             ->get();

        return response()->json($notifications);
    }

    // API: Đánh dấu thông báo là đã đọc
    public function markAsRead(Request $request, $notificationId)
    {
        $user = $request->user();
        $notification = Notification::findOrFail($notificationId);

        if (!$user->notifications()->where('notification_id', $notificationId)->exists()) {
            return response()->json(['message' => 'Thông báo không tồn tại hoặc không thuộc về bạn'], 403);
        }

        $user->notifications()->updateExistingPivot($notificationId, ['is_read' => true]);

        return response()->json(['message' => 'Thông báo đã được đánh dấu là đã đọc']);
    }

    // API: Admin tạo thông báo
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
}