@extends('layouts.admin')

@section('title', 'Danh sách thông báo')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4">Danh sách thông báo đã gửi</h1>

        <!-- Thông báo thành công (nếu có) -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Nút tạo thông báo mới và quay lại -->
        <div class="mb-4 d-flex justify-content-between">
            <div>
                <a href="{{ route('admin.affiliate.notifications.create') }}" class="btn btn-primary me-2">Tạo thông báo mới</a>
                <a href="{{ route('admin.affiliate.index') }}" class="btn btn-secondary">Quay lại Affiliate</a>
            </div>
        </div>

        <!-- Bảng danh sách thông báo -->
        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-hover table-bordered">
                    <thead class="table-dark">
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
                                <td>
                                    @if ($notification->users->isEmpty())
                                        Tất cả
                                    @else
                                        {{ $notification->users->pluck('name')->take(3)->join(', ') }}
                                        @if ($notification->users->count() > 3)
                                            +{{ $notification->users->count() - 3 }} người khác
                                        @endif
                                    @endif
                                </td>
                                <td>{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.affiliate.notifications.detail', $notification->id) }}" class="btn btn-info btn-sm me-1">Xem chi tiết</a>
                                    <form action="{{ route('admin.affiliate.notifications.destroy', $notification->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa thông báo này?')">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Chưa có thông báo nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Phân trang -->
                @if ($notifications->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection