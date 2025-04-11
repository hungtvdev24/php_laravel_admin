@extends('layouts.admin')

@section('title', 'Quản lý Bình luận')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4 text-warm-brown">Danh sách Bình luận</h1>

    @if (session('success'))
        <div class="alert alert-success" id="success-message">
            {{ session('success') }}
        </div>
    @endif

    <!-- FORM LỌC THEO TRẠNG THÁI -->
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('admin.reviews.index') }}" method="GET" class="row g-3 align-items-center">
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
                        <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                        <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                        <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>Đã từ chối</option>
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
            <form action="{{ route('admin.reviews.index') }}" method="GET" class="row g-3 align-items-center">
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

    <!-- BẢNG BÌNH LUẬN -->
    <div class="card shadow-sm">
        <div class="card-body">
            @if ($reviews->isEmpty())
                <div class="alert alert-info text-center" role="alert">
                    Không có bình luận nào để hiển thị.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-bordered text-center align-middle">
                        <thead class="table-warm-header">
                            <tr>
                                <th>ID</th>
                                <th>Người dùng</th>
                                <th>Sản phẩm</th>
                                <th>Đơn hàng</th>
                                <th>Số sao</th>
                                <th>Bình luận</th>
                                <th>Ngày đánh giá</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reviews as $review)
                                <tr data-review-id="{{ $review->id_danhGia }}">
                                    <td>{{ $review->id_danhGia }}</td>
                                    <td>{{ $review->user->name ?? 'N/A' }}</td>
                                    <td>{{ $review->product->tenSanPham ?? 'N/A' }}</td>
                                    <td>{{ $review->donHang->id_donHang ?? 'N/A' }}</td>
                                    <td>{{ $review->soSao }} <i class="fas fa-star text-warning"></i></td>
                                    <td>{{ $review->binhLuan ?? 'Không có bình luận' }}</td>
                                    <td>{{ $review->ngayDanhGia->format('d/m/Y H:i') }}</td>
                                    <td class="status-cell">
                                        @if ($review->trangThai == 'pending')
                                            <span class="badge bg-warning">Chờ duyệt</span>
                                        @elseif ($review->trangThai == 'approved')
                                            <span class="badge bg-success">Đã duyệt</span>
                                        @elseif ($review->trangThai == 'rejected')
                                            <span class="badge bg-danger">Đã từ chối</span>
                                        @endif
                                    </td>
                                    <td class="action-cell">
                                        <a href="{{ route('admin.reviews.show', $review->id_danhGia) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> Xem
                                        </a>
                                        @if ($review->trangThai == 'pending')
                                            <button class="btn btn-success btn-sm approve-review" data-review-id="{{ $review->id_danhGia }}">
                                                <i class="fas fa-check"></i> Duyệt
                                            </button>
                                            <button class="btn btn-warning btn-sm reject-review" data-review-id="{{ $review->id_danhGia }}">
                                                <i class="fas fa-times"></i> Từ chối
                                            </button>
                                        @endif
                                        <button class="btn btn-danger btn-sm delete-review" data-review-id="{{ $review->id_danhGia }}">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- PHÂN TRANG -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $reviews->appends(request()->query())->links() }}
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

            // Xử lý duyệt bình luận bằng AJAX
            $('.approve-review').on('click', function() {
                var reviewId = $(this).data('review-id');
                var row = $(this).closest('tr');
                var statusCell = row.find('.status-cell');
                var actionCell = row.find('.action-cell');

                if (confirm('Bạn có chắc chắn muốn duyệt bình luận này?')) {
                    $.ajax({
                        url: '/admin/reviews/' + reviewId + '/approve',
                        method: 'PATCH',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            // Cập nhật trạng thái hiển thị
                            statusCell.html('<span class="badge bg-success">Đã duyệt</span>');
                            // Ẩn nút duyệt và từ chối
                            actionCell.find('.approve-review, .reject-review').remove();
                            // Hiển thị thông báo thành công
                            $('#success-message').remove();
                            $('h1').after('<div class="alert alert-success" id="success-message">' + response.message + '</div>');
                        },
                        error: function(xhr) {
                            // Hiển thị thông báo lỗi
                            $('#success-message').remove();
                            $('h1').after('<div class="alert alert-danger" id="success-message">Có lỗi xảy ra: ' + xhr.responseJSON.message + '</div>');
                        }
                    });
                }
            });

            // Xử lý từ chối bình luận bằng AJAX
            $('.reject-review').on('click', function() {
                var reviewId = $(this).data('review-id');
                var row = $(this).closest('tr');
                var statusCell = row.find('.status-cell');
                var actionCell = row.find('.action-cell');

                if (confirm('Bạn có chắc chắn muốn từ chối bình luận này?')) {
                    $.ajax({
                        url: '/admin/reviews/' + reviewId + '/reject',
                        method: 'PATCH',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            // Cập nhật trạng thái hiển thị
                            statusCell.html('<span class="badge bg-danger">Đã từ chối</span>');
                            // Ẩn nút duyệt và từ chối
                            actionCell.find('.approve-review, .reject-review').remove();
                            // Hiển thị thông báo thành công
                            $('#success-message').remove();
                            $('h1').after('<div class="alert alert-success" id="success-message">' + response.message + '</div>');
                        },
                        error: function(xhr) {
                            // Hiển thị thông báo lỗi
                            $('#success-message').remove();
                            $('h1').after('<div class="alert alert-danger" id="success-message">Có lỗi xảy ra: ' + xhr.responseJSON.message + '</div>');
                        }
                    });
                }
            });

            // Xử lý xóa bình luận bằng AJAX
            $('.delete-review').on('click', function() {
                var reviewId = $(this).data('review-id');
                var row = $(this).closest('tr');

                if (confirm('Bạn có chắc chắn muốn xóa bình luận này?')) {
                    $.ajax({
                        url: '/admin/reviews/' + reviewId,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            // Xóa hàng khỏi bảng
                            row.remove();
                            // Hiển thị thông báo thành công
                            $('#success-message').remove();
                            $('h1').after('<div class="alert alert-success" id="success-message">' + response.message + '</div>');
                        },
                        error: function(xhr) {
                            // Hiển thị thông báo lỗi
                            $('#success-message').remove();
                            $('h1').after('<div class="alert alert-danger" id="success-message">Có lỗi xảy ra: ' + xhr.responseJSON.message + '</div>');
                        }
                    });
                }
            });
        });
    </script>
@endsection