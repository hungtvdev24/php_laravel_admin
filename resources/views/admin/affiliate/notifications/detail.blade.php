@extends('layouts.admin')

@section('title', 'Chi tiết thông báo')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Chi tiết thông báo #{{ $notification->id }}</h1>
            <div>
                <a href="{{ route('admin.affiliate.notifications.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
                </a>
                <form action="{{ route('admin.affiliate.notifications.destroy', $notification->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa thông báo này?')">
                        <i class="fas fa-trash-alt me-1"></i> Xóa thông báo
                    </button>
                </form>
            </div>
        </div>

        <!-- Thông tin chi tiết -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 text-gray-800">
                    <i class="fas fa-bell me-2 text-primary"></i> {{ $notification->title }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Nội dung thông báo -->
                    <div class="col-md-12 mb-4">
                        <p class="text-gray-700 mb-1">
                            <i class="fas fa-file-alt me-2 text-info"></i>
                            <strong>Nội dung:</strong>
                        </p>
                        <div class="border p-4 bg-white rounded shadow-sm">
                            <p class="text-gray-800 mb-0">{{ $notification->content }}</p>
                        </div>
                    </div>

                    <!-- Người nhận -->
                    <div class="col-md-6 mb-3">
                        <p class="text-gray-700 mb-1">
                            <i class="fas fa-users me-2 text-success"></i>
                            <strong>Người nhận:</strong>
                        </p>
                        <p class="text-gray-800">
                            @if ($notification->users->isEmpty())
                                <span class="badge bg-info text-white">Tất cả</span>
                            @else
                                {{ $notification->users->pluck('name')->take(3)->join(', ') }}
                                @if ($notification->users->count() > 3)
                                    <span class="text-muted">+{{ $notification->users->count() - 3 }} người khác</span>
                                @endif
                            @endif
                        </p>
                    </div>

                    <!-- Ngày tạo -->
                    <div class="col-md-6 mb-3">
                        <p class="text-gray-700 mb-1">
                            <i class="fas fa-calendar-alt me-2 text-warning"></i>
                            <strong>Ngày tạo:</strong>
                        </p>
                        <p class="text-gray-800">{{ $notification->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .card-header {
            background-color: #f8f9fa !important;
            border-bottom: 1px solid #e3e6f0 !important;
        }
        .text-gray-700 {
            color: #5a5c69 !important;
        }
        .text-gray-800 {
            color: #2e2f3a !important;
        }
        .badge {
            font-size: 0.9rem;
            padding: 0.35em 0.65em;
        }
    </style>
@endsection