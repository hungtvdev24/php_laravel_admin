@extends('layouts.admin')

@section('title', 'Danh sách thông báo')

@section('content')
    <h1 class="mb-4">Danh sách thông báo đã gửi</h1>

    <!-- Thông báo thành công (nếu có) -->
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Nút tạo thông báo mới -->
    <div class="mb-3">
        <a href="{{ route('admin.affiliate.notifications.create') }}" class="btn btn-primary">Tạo thông báo mới</a>
        <a href="{{ route('admin.affiliate.index') }}" class="btn btn-secondary">Quay lại Affiliate</a>
    </div>

    <!-- Bảng danh sách thông báo -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tiêu đề</th>
                <th>Nội dung</th>
                <th>Người nhận</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($notifications as $notification)
                <tr>
                    <td>{{ $notification->id }}</td>
                    <td>{{ $notification->title }}</td>
                    <td>{{ Str::limit($notification->content, 50) }}</td>
                    <td>{{ $notification->users->pluck('name')->join(', ') ?: 'Tất cả người dùng' }}</td>
                    <td>{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.affiliate.notifications.detail', $notification->id) }}" class="btn btn-info btn-sm">Xem chi tiết</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Chưa có thông báo nào.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection