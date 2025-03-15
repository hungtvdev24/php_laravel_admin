@extends('layouts.admin')

@section('title', 'Quản lý Đơn hàng')

@section('content')
    <div class="container-fluid py-4">
        <h1 class="h3 mb-4 text-warm-brown">Danh sách Đơn hàng</h1>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                @if ($orders->isEmpty())
                    <div class="alert alert-info text-center" role="alert">
                        Không có đơn hàng nào để hiển thị.
                    </div>
                @else
                    <table class="table table-hover table-bordered text-center">
                        <thead class="table-warm-header">
                            <tr>
                                <th class="py-3">ID</th>
                                <th class="py-3">Người nhận</th>
                                <th class="py-3">SĐT</th>
                                <th class="py-3">Địa chỉ</th>
                                <th class="py-3">Tổng tiền</th>
                                <th class="py-3">PT Thanh toán</th>
                                <th class="py-3">Trạng thái</th>
                                <th class="py-3">Ngày dự kiến giao</th>
                                <th class="py-3">Ngày giao thực tế</th>
                                <th class="py-3">Cập nhật</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr class="align-middle">
                                    <td>{{ $order->id_donHang }}</td>
                                    <td>{{ $order->ten_nguoiNhan }}</td>
                                    <td>{{ $order->sdt_nhanHang }}</td>
                                    <td>
                                        {{ $order->ten_nha }}, {{ $order->xa }}, {{ $order->huyen }}, {{ $order->tinh }}
                                    </td>
                                    <td>{{ number_format($order->tongTien, 0, ',', '.') }} VNĐ</td>
                                    <td>{{ $order->phuongThucThanhToan }}</td>
                                    <td>
                                        @if($order->trangThaiDonHang === 'cho_xac_nhan')
                                            <span class="badge bg-warning">Chờ xác nhận</span>
                                        @elseif($order->trangThaiDonHang === 'dang_giao')
                                            <span class="badge bg-primary">Đang giao</span>
                                        @elseif($order->trangThaiDonHang === 'da_giao')
                                            <span class="badge bg-success">Đã giao</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $order->ngay_du_kien_giao ? $order->ngay_du_kien_giao->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>
                                        {{ $order->ngay_giao_thuc_te ? $order->ngay_giao_thuc_te->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>
                                        @if($order->trangThaiDonHang !== 'da_giao')
                                            <form action="{{ route('admin.orders.update', $order->id_donHang) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <select name="trangThaiDonHang"
                                                        class="form-select form-select-sm d-inline-block w-auto"
                                                        onchange="this.form.submit()">
                                                    @if($order->trangThaiDonHang === 'cho_xac_nhan')
                                                        <option value="cho_xac_nhan" selected>Chờ xác nhận</option>
                                                        <option value="dang_giao">Đang giao</option>
                                                    @elseif($order->trangThaiDonHang === 'dang_giao')
                                                        <option value="dang_giao" selected>Đang giao</option>
                                                        <option value="da_giao">Đã giao</option>
                                                    @endif
                                                </select>
                                            </form>
                                        @else
                                            <span class="text-muted">Không thể thay đổi</span>
                                        @endif

                                        <!-- Nút hủy đơn hàng (vô hiệu hóa) -->
                                        <button type="button" class="btn btn-secondary btn-sm mt-2" disabled>
                                            Không thể hủy
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Phân trang -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
