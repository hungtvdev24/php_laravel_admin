@extends('layouts.admin')

@section('title', 'Chi tiết Đơn hàng #' . $order->id_donHang)

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 text-warm-brown">Chi tiết Đơn hàng #{{ $order->id_donHang }}</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-warm-brown text-white">
                    Thông tin người nhận
                </div>
                <div class="card-body">
                    <p><strong>Họ tên:</strong> {{ $order->ten_nguoiNhan }}</p>
                    <p><strong>SĐT:</strong> {{ $order->sdt_nhanHang }}</p>
                    <p>
                        <strong>Địa chỉ:</strong> 
                        {{ $order->ten_nha }}, {{ $order->xa }}, {{ $order->huyen }}, {{ $order->tinh }}
                    </p>
                    <p><strong>Phương thức thanh toán:</strong> {{ $order->phuongThucThanhToan }}</p>
                    <p><strong>Tổng tiền:</strong> {{ number_format($order->tongTien, 0, ',', '.') }} VNĐ</p>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-warm-brown text-white">
                    Thông tin đơn hàng
                </div>
                <div class="card-body">
                    <p>
                        <strong>Trạng thái:</strong>
                        @if($order->trangThaiDonHang === 'cho_xac_nhan')
                            <span class="badge bg-warning">Chờ xác nhận</span>
                        @elseif($order->trangThaiDonHang === 'dang_giao')
                            <span class="badge bg-primary">Đang giao</span>
                        @elseif($order->trangThaiDonHang === 'da_giao')
                            <span class="badge bg-success">Đã giao</span>
                        @elseif($order->trangThaiDonHang === 'huy')
                            <span class="badge bg-danger">Đã hủy</span>
                        @endif
                    </p>
                    <p>
                        <strong>Ngày dự kiến giao:</strong>
                        {{ $order->ngay_du_kien_giao ? $order->ngay_du_kien_giao->format('d/m/Y') : '-' }}
                    </p>
                    <p>
                        <strong>Ngày giao thực tế:</strong>
                        {{ $order->ngay_giao_thuc_te ? $order->ngay_giao_thuc_te->format('d/m/Y') : '-' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-warm-brown text-white">
                    Sản phẩm trong đơn hàng
                </div>
                <div class="card-body">
                    @if($order->chiTietDonHang->isEmpty())
                        <p class="mb-0">Không có sản phẩm nào trong đơn hàng.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead>
                                    <tr class="text-center">
                                        <th>Sản phẩm</th>
                                        <th>Số lượng</th>
                                        <th>Giá (VNĐ)</th>
                                        <th>Thành tiền (VNĐ)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->chiTietDonHang as $chiTiet)
                                        <tr>
                                            <td>
                                                @if($chiTiet->sanPham)
                                                    <div>
                                                        <strong>{{ $chiTiet->sanPham->tenSanPham }}</strong>
                                                    </div>
                                                    <div class="text-muted small">
                                                        {{ $chiTiet->sanPham->thuongHieu ?? '' }}
                                                    </div>
                                                @else
                                                    #{{ $chiTiet->id_sanPham }}
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $chiTiet->soLuong }}</td>
                                            <td class="text-right">
                                                {{ number_format($chiTiet->gia, 0, ',', '.') }}
                                            </td>
                                            <td class="text-right">
                                                {{ number_format($chiTiet->gia * $chiTiet->soLuong, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12 text-end">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                Quay lại danh sách
            </a>
        </div>
    </div>
</div>
@endsection
