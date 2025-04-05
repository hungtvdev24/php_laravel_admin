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
                @if($status)
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                @if($start_date)
                    <input type="hidden" name="start_date" value="{{ $start_date }}">
                @endif
                @if($end_date)
                    <input type="hidden" name="end_date" value="{{ $end_date }}">
                @endif
                <div class="col-auto">
                    <label for="phone" class="visually-hidden">SĐT</label>
                    <input type="text" name="phone" id="phone"
                           value="{{ $phone ?? '' }}"
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
                @if($phone)
                    <input type="hidden" name="phone" value="{{ $phone }}">
                @endif
                @if($start_date)
                    <input type="hidden" name="start_date" value="{{ $start_date }}">
                @endif
                @if($end_date)
                    <input type="hidden" name="end_date" value="{{ $end_date }}">
                @endif
                <div class="col-auto">
                    <label for="status" class="visually-hidden">Trạng thái</label>
                    <select name="status" id="status" class="form-select">
                        <option value="all">-- Tất cả trạng thái --</option>
                        <option value="cho_xac_nhan" {{ $status == 'cho_xac_nhan' ? 'selected' : '' }}>Chờ xác nhận</option>
                        <option value="dang_giao" {{ $status == 'dang_giao' ? 'selected' : '' }}>Đang giao</option>
                        <option value="da_giao" {{ $status == 'da_giao' ? 'selected' : '' }}>Đã giao</option>
                        <option value="huy" {{ $status == 'huy' ? 'selected' : '' }}>Đã hủy</option>
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

    <!-- FORM LỌC THEO KHOẢNG NGÀY -->
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-3 align-items-center">
                @if($phone)
                    <input type="hidden" name="phone" value="{{ $phone }}">
                @endif
                @if($status)
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <div class="col-auto">
                    <label for="start_date" class="form-label">Từ ngày</label>
                    <input type="text" name="start_date" id="start_date"
                           value="{{ $start_date ?? '' }}"
                           class="form-control"
                           placeholder="dd/mm/yyyy"
                           autocomplete="off"
                           readonly>
                </div>
                <div class="col-auto">
                    <label for="end_date" class="form-label">Đến ngày</label>
                    <input type="text" name="end_date" id="end_date"
                           value="{{ $end_date ?? '' }}"
                           class="form-control"
                           placeholder="dd/mm/yyyy"
                           autocomplete="off"
                           readonly>
                </div>
                <div class="col-auto mt-4">
                    <button type="submit" class="btn btn-info">
                        <i class="bi bi-calendar"></i> Lọc theo ngày
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- END LỌC THEO KHOẢNG NGÀY -->

    <!-- BẢNG ĐƠN HÀNG -->
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
                                <th>Sản phẩm</th>
                                <th>Tổng tiền</th>
                                <th>PT Thanh toán</th>
                                <th>Trạng thái</th>
                                <th>Ngày đặt hàng</th>
                                <th>Ngày dự kiến giao</th>
                                <th>Ngày giao thực tế</th>
                                <th>Chi tiết</th>
                                <th>Cập nhật</th>
                                <th>Xuất hóa đơn</th>
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
                                    <td>
                                        @foreach($order->chiTietDonHang as $detail)
                                            <div class="text-left">
                                                <strong>{{ $detail->sanPham->tenSanPham }}</strong>
                                                @if($detail->variation_id)
                                                    <?php $variation = $detail->variation; ?>
                                                    @if($variation)
                                                        <small>({{ $variation->color }}, {{ $variation->size }})</small>
                                                    @endif
                                                @endif
                                                <br>
                                                <small>Số lượng: {{ $detail->soLuong }} - Giá: {{ number_format($detail->gia, 0, ',', '.') }} VNĐ</small>
                                            </div>
                                            @if(!$loop->last)
                                                <hr class="my-1">
                                            @endif
                                        @endforeach
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
                                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        {{ $order->ngay_du_kien_giao ? $order->ngay_du_kien_giao->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>
                                        {{ $order->ngay_giao_thuc_te ? $order->ngay_giao_thuc_te->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.orders.view', $order->id_donHang) }}" class="btn btn-info btn-sm">
                                            Xem chi tiết
                                        </a>
                                    </td>
                                    <td>
                                        @if($order->trangThaiDonHang !== 'da_giao' && $order->trangThaiDonHang !== 'huy')
                                            <form action="{{ route('admin.orders.update', $order->id_donHang) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <select name="trangThaiDonHang" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                                    @if(session('role') === 'employee')
                                                        @if($order->trangThaiDonHang === 'cho_xac_nhan')
                                                            <option value="cho_xac_nhan" selected>Chờ xác nhận</option>
                                                            <option value="dang_giao">Đang giao</option>
                                                        @endif
                                                    @else
                                                        @if($order->trangThaiDonHang === 'cho_xac_nhan')
                                                            <option value="cho_xac_nhan" selected>Chờ xác nhận</option>
                                                            <option value="dang_giao">Đang giao</option>
                                                            <option value="huy">Hủy</option>
                                                        @elseif($order->trangThaiDonHang === 'dang_giao')
                                                            <option value="dang_giao" selected>Đang giao</option>
                                                            <option value="da_giao">Đã giao</option>
                                                        @endif
                                                    @endif
                                                </select>
                                            </form>
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.orders.exportInvoice', $order->id_donHang) }}" class="btn btn-success btn-sm">
                                            <i class="bi bi-download"></i> Xuất hóa đơn
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- PHÂN TRANG -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <!-- jQuery UI -->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#start_date, #end_date").datepicker({
                dateFormat: "dd/mm/yy",
                changeMonth: true,
                changeYear: true,
                yearRange: "2000:2030"
            });
        });
    </script>
@endsection