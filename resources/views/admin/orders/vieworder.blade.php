@extends('layouts.admin')

@section('title', 'Chi tiết Đơn hàng')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4 text-warm-brown">Chi tiết Đơn hàng #{{ $order->id_donHang }}</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Thông tin Đơn hàng</h5>
            <table class="table table-bordered">
                <tr>
                    <th>ID Đơn hàng</th>
                    <td>{{ $order->id_donHang }}</td>
                </tr>
                <tr>
                    <th>Người nhận</th>
                    <td>{{ $order->ten_nguoiNhan }}</td>
                </tr>
                <tr>
                    <th>SĐT người nhận</th>
                    <td>{{ $order->sdt_nhanHang }}</td>
                </tr>
                <tr>
                    <th>SĐT khách hàng</th>
                    <td>{{ $order->user->phone ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Địa chỉ</th>
                    <td>{{ $order->ten_nha }}, {{ $order->xa }}, {{ $order->huyen }}, {{ $order->tinh }}</td>
                </tr>
                <tr>
                    <th>Tổng tiền</th>
                    <td>{{ number_format($order->tongTien, 0, ',', '.') }} VNĐ</td>
                </tr>
                <tr>
                    <th>Phương thức thanh toán</th>
                    <td>{{ $order->phuongThucThanhToan }}</td>
                </tr>
                <tr>
                    <th>Trạng thái</th>
                    <td>
                        @if($order->trangThaiDonHang === 'cho_xac_nhan')
                            <span class="badge bg-warning">Chờ xác nhận</span>
                        @elseif($order->trangThaiDonHang === 'dang_giao')
                            <span class="badge bg-primary">Đang giao</span>
                        @elseif($order->trangThaiDonHang === 'da_giao')
                            <span class="badge bg-success">Đã giao</span>
                        @elseif($order->trangThaiDonHang === 'huy')
                            <span class="badge bg-danger">Đã hủy</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Ngày đặt hàng</th>
                    <td>{{ $order->created_at->format('d/m/Y H:i:s') }}</td>
                </tr>
                <tr>
                    <th>Ngày dự kiến giao</th>
                    <td>{{ $order->ngay_du_kien_giao ? $order->ngay_du_kien_giao->format('d/m/Y') : '-' }}</td>
                </tr>
                <tr>
                    <th>Ngày giao thực tế</th>
                    <td>{{ $order->ngay_giao_thuc_te ? $order->ngay_giao_thuc_te->format('d/m/Y') : '-' }}</td>
                </tr>
            </table>

            <h5 class="mt-4">Danh sách Sản phẩm</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tên sản phẩm</th>
                        <th>Biến thể</th>
                        <th>Số lượng</th>
                        <th>Giá</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->chiTietDonHang as $detail)
                        <tr>
                            <td>{{ $detail->sanPham->tenSanPham }}</td>
                            <td>
                                @if($detail->variation)
                                    {{ $detail->variation->color }}, {{ $detail->variation->size }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $detail->soLuong }}</td>
                            <td>{{ number_format($detail->gia, 0, ',', '.') }} VNĐ</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <h5 class="mt-4">Lịch sử Thay đổi Trạng thái</h5>
            @if($order->statusHistory->isEmpty())
                <p>Chưa có lịch sử thay đổi trạng thái.</p>
            @else
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Trạng thái</th>
                            <th>Ngày thay đổi</th>
                            <th>Người thực hiện</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->statusHistory as $history)
                            <tr>
                                <td>
                                    @if($history->trangThaiDonHang === 'cho_xac_nhan')
                                        <span class="badge bg-warning">Chờ xác nhận</span>
                                    @elseif($history->trangThaiDonHang === 'dang_giao')
                                        <span class="badge bg-primary">Đang giao</span>
                                    @elseif($history->trangThaiDonHang === 'da_giao')
                                        <span class="badge bg-success">Đã giao</span>
                                    @elseif($history->trangThaiDonHang === 'huy')
                                        <span class="badge bg-danger">Đã hủy</span>
                                    @endif
                                </td>
                                <td>{{ $history->created_at->format('d/m/Y H:i:s') }}</td>
                                <td>{{ $history->ghiChu ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <div class="mt-4">
                <a href="{{ route('admin.orders.exportInvoice', $order->id_donHang) }}" class="btn btn-success">
                    <i class="bi bi-download"></i> Xuất Hóa đơn
                </a>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Quay lại</a>
            </div>
        </div>
    </div>
</div>
@endsection