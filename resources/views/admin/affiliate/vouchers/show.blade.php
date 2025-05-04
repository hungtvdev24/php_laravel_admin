@extends('layouts.admin')

@section('title', 'Chi tiết Voucher')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4 text-gray-800">Chi tiết Voucher #{{ $voucher->id }}</h1>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="mb-4">
            <a href="{{ route('admin.affiliate.vouchers.index') }}" class="btn btn-secondary">Quay lại Danh sách</a>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light text-gray-800">
                <h5 class="mb-0">Thông tin Voucher</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Mã Voucher:</strong> {{ $voucher->code }}</p>
                        <p><strong>Giảm Giá:</strong> {{ $voucher->discount_value }} {{ $voucher->discount_type == 'percentage' ? '%' : 'VNĐ' }}</p>
                        <p><strong>Loại Giảm Giá:</strong> {{ $voucher->discount_type == 'fixed' ? 'Cố định' : 'Phần trăm' }}</p>
                        <p><strong>Giá trị đơn hàng tối thiểu:</strong> {{ $voucher->min_order_value ? number_format($voucher->min_order_value) . ' VNĐ' : 'Không có' }}</p>
                        <p><strong>Giảm tối đa:</strong> {{ $voucher->max_discount ? number_format($voucher->max_discount) . ' VNĐ' : 'Không giới hạn' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Ngày Bắt Đầu:</strong> {{ $voucher->start_date ? $voucher->start_date->format('d/m/Y H:i') : 'Không có' }}</p>
                        <p><strong>Ngày Kết Thúc:</strong> {{ $voucher->end_date ? $voucher->end_date->format('d/m/Y H:i') : 'Không có' }}</p>
                        <p><strong>Giới Hạn Sử Dụng:</strong> {{ $voucher->usage_limit ?? 'Không giới hạn' }}</p>
                        <p><strong>Trạng Thái:</strong> 
                            <span class="badge {{ $voucher->status == 'active' ? 'bg-success' : ($voucher->status == 'inactive' ? 'bg-warning' : 'bg-danger') }}">
                                {{ $voucher->status == 'active' ? 'Hoạt động' : ($voucher->status == 'inactive' ? 'Không hoạt động' : 'Hết hạn') }}
                            </span>
                        </p>
                        <p><strong>Ngày Tạo:</strong> {{ $voucher->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light text-gray-800">
                <h5 class="mb-0">Người Dùng Đã Sử Dụng</h5>
            </div>
            <div class="card-body">
                @if ($voucher->users->isEmpty())
                    <p class="text-muted">Chưa có người dùng nào sử dụng voucher này.</p>
                @else
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>ID Người Dùng</th>
                                <th>Tên Người Dùng</th>
                                <th>Email</th>
                                <th>Thời Gian Sử Dụng</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($voucher->users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->pivot->used_at ? $user->pivot->used_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    <style>
        body {
            background-color: #f8fafc;
        }
        .card {
            border: none;
            border-radius: 0.5rem;
        }
        .card-header {
            background-color: #e2e8f0;
        }
        .table {
            background-color: #ffffff;
        }
        .table-light {
            background-color: #e2e8f0 !important;
        }
        .btn-secondary {
            background-color: #6b7280;
            border-color: #6b7280;
        }
        .btn-secondary:hover {
            background-color: #4b5563;
            border-color: #4b5563;
        }
        .text-gray-800 {
            color: #1f2937;
        }
    </style>
@endsection