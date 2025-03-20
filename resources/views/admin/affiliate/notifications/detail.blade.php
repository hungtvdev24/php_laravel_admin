@extends('layouts.admin')

@section('title', 'Chi tiết thông báo')

@section('content')
    <h1 class="mb-4">Chi tiết thông báo #{{ $notification->id }}</h1>

    <!-- Thông tin chi tiết -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $notification->title }}</h5>
            <p class="card-text">{{ $notification->content }}</p>
            <p><strong>Người nhận:</strong> {{ $notification->users->pluck('name')->join(', ') ?: 'Tất cả người dùng' }}</p>
            <p><strong>Ngày tạo:</strong> {{ $notification->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <!-- Nút quay lại -->
    <div class="mt-3">
        <a href="{{ route('admin.affiliate.notifications.index') }}" class="btn btn-secondary">Quay lại danh sách</a>
    </div>
@endsection