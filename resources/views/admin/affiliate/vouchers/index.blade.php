@extends('layouts.admin')

@section('title', 'Danh sách Voucher')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4 text-gray-800">Danh sách Voucher</h1>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="mb-4">
            <a href="{{ route('admin.affiliate.vouchers.create') }}" class="btn btn-primary">Tạo Voucher Mới</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Mã Voucher</th>
                            <th>Giảm Giá</th>
                            <th>Trạng Thái</th>
                            <th>Ngày Tạo</th>
                            <th>Ngày Hết Hạn</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vouchers as $voucher)
                            <tr>
                                <td>{{ $voucher->id }}</td>
                                <td>{{ $voucher->code }}</td>
                                <td>{{ $voucher->discount_value }} {{ $voucher->discount_type == 'percentage' ? '%' : 'VNĐ' }}</td>
                                <td>
                                    <form action="{{ route('admin.affiliate.vouchers.updateStatus', $voucher->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" onchange="this.form.submit()" class="form-select form-select-sm">
                                            <option value="active" {{ $voucher->status == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                            <option value="inactive" {{ $voucher->status == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                                            <option value="expired" {{ $voucher->status == 'expired' ? 'selected' : '' }}>Hết hạn</option>
                                        </select>
                                    </form>
                                </td>
                                <td>{{ $voucher->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $voucher->end_date ? $voucher->end_date->format('d/m/Y H:i') : 'Không có' }}</td>
                                <td>
                                    <a href="{{ route('admin.affiliate.vouchers.show', $voucher->id) }}" class="btn btn-info btn-sm">Xem Chi Tiết</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Chưa có voucher nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($vouchers->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $vouchers->links() }}
                    </div>
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
        .table {
            background-color: #ffffff;
        }
        .table-light {
            background-color: #e2e8f0 !important;
        }
        .btn-primary {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }
        .btn-primary:hover {
            background-color: #2563eb;
            border-color: #2563eb;
        }
        .btn-info {
            background-color: #22d3ee;
            border-color: #22d3ee;
        }
        .btn-info:hover {
            background-color: #06b6d4;
            border-color: #06b6d4;
        }
        .text-gray-800 {
            color: #1f2937;
        }
    </style>
@endsection