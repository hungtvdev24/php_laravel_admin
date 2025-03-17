@extends('layouts.admin')

@section('title', 'Quản lý Đơn hàng')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4 text-warm-brown">Danh sách Đơn hàng</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- FORM TÌM KIẾM THEO SĐT -->
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-3 align-items-center">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif

                <div class="col-auto">
                    <label for="phone" class="visually-hidden">SĐT</label>
                    <input type="text" name="phone" id="phone"
                           value="{{ request('phone') }}"
                           class="form-control"
                           placeholder="Tìm theo SĐT...">
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Tìm
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- END TÌM KIẾM THEO SĐT -->

    <!-- FORM LỌC THEO TRẠNG THÁI -->
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-3 align-items-center">
                @if(request('phone'))
                    <input type="hidden" name="phone" value="{{ request('phone') }}">
                @endif

                <div class="col-auto">
                    <label for="status" class="visually-hidden">Trạng thái</label>
                    <select name="status" id="status" class="form-select">
                        <option value="all">-- Tất cả trạng thái --</option>
                        <option value="cho_xac_nhan" {{ request('status')=='cho_xac_nhan' ? 'selected':'' }}>Chờ xác nhận</option>
                        <option value="dang_giao" {{ request('status')=='dang_giao' ? 'selected':'' }}>Đang giao</option>
                        <option value="da_giao" {{ request('status')=='da_giao' ? 'selected':'' }}>Đã giao</option>
                        <option value="huy" {{ request('status')=='huy' ? 'selected':'' }}>Đã hủy</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-funnel"></i> Lọc
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- END LỌC THEO TRẠNG THÁI -->

    <div class="card shadow-sm">
        <div class="card-body">
            @if ($orders->isEmpty())
                <div class="alert alert-info text-center" role="alert">
                    Không có đơn hàng nào để hiển thị.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-bordered text-center align-middle">
                        <thead class="table-warm-header">
                            <tr>
                                <th>ID</th>
                                <th>Người nhận</th>
                                <th>SĐT</th>
                                <th>Địa chỉ</th>
                                <th>Tổng tiền</th>
                                <th>PT Thanh toán</th>
                                <th>Trạng thái</th>
                                <th>Ngày dự kiến giao</th>
                                <th>Ngày giao thực tế</th>
                                <th>Chi tiết</th>
                                <th>Cập nhật</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>{{ $order->id_donHang }}</td>
                                    <td>{{ $order->ten_nguoiNhan }}</td>
                                    <td>{{ $order->sdt_nhanHang }}</td>
                                    <td>
                                        {{ $order->ten_nha }}, {{ $order->xa }},
                                        {{ $order->huyen }}, {{ $order->tinh }}
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
                                        @elseif($order->trangThaiDonHang === 'huy')
                                            <span class="badge bg-danger">Đã hủy</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $order->ngay_du_kien_giao
                                            ? $order->ngay_du_kien_giao->format('d/m/Y')
                                            : '-' }}
                                    </td>
                                    <td>
                                        {{ $order->ngay_giao_thuc_te
                                            ? $order->ngay_giao_thuc_te->format('d/m/Y')
                                            : '-' }}
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.orders.view', $order->id_donHang) }}"
                                           class="btn btn-info btn-sm">
                                           Xem chi tiết
                                        </a>
                                    </td>
                                    <td>
                                        @if($order->trangThaiDonHang !== 'da_giao'
                                             && $order->trangThaiDonHang !== 'huy')
                                            <form action="{{ route('admin.orders.update', $order->id_donHang) }}"
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <select name="trangThaiDonHang"
                                                        class="form-select form-select-sm d-inline-block w-auto"
                                                        onchange="this.form.submit()">
                                                    @if($order->trangThaiDonHang === 'cho_xac_nhan')
                                                        <option value="cho_xac_nhan" selected>Chờ xác nhận</option>
                                                        <option value="dang_giao">Đang giao</option>
                                                        <option value="huy">Hủy</option>
                                                    @elseif($order->trangThaiDonHang === 'dang_giao')
                                                        <option value="dang_giao" selected>Đang giao</option>
                                                        <option value="da_giao">Đã giao</option>
                                                    @endif
                                                </select>
                                            </form>
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif

                                        @if($order->trangThaiDonHang === 'cho_xac_nhan')
                                            <form action="{{ route('admin.orders.cancel', $order->id_donHang) }}"
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm mt-2">
                                                    Hủy đơn hàng
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- PHÂN TRANG -->
                <div class="d-flex justify-content-center mt-3">
                    {{--
                        Nếu đã Paginator::useBootstrapFive() 
                        và đã load bootstrap.min.css,
                        pagination sẽ tự đẹp.
                    --}}
                    {{ $orders->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
