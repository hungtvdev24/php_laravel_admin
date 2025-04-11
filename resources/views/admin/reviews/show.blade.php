@extends('layouts.admin')

@section('title', 'Chi tiết Bình luận')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4 text-warm-brown">Chi tiết Bình luận</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row">
                <!-- Thông tin người dùng -->
                <div class="col-md-6">
                    <h4>Thông tin Người dùng</h4>
                    <table class="table table-bordered">
                        <tr>
                            <th>Tên</th>
                            <td>{{ $review->user->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $review->user->email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Số điện thoại</th>
                            <td>{{ $review->user->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Tuổi</th>
                            <td>{{ $review->user->tuoi ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Thông tin bình luận -->
                <div class="col-md-6">
                    <h4>Thông tin Bình luận</h4>
                    <table class="table table-bordered">
                        <tr>
                            <th>ID Bình luận</th>
                            <td>{{ $review->id_danhGia }}</td>
                        </tr>
                        <tr>
                            <th>Sản phẩm</th>
                            <td>{{ $review->product->tenSanPham ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Đơn hàng</th>
                            <td>{{ $review->donHang->id_donHang ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Số sao</th>
                            <td>{{ $review->soSao }} <i class="fas fa-star text-warning"></i></td>
                        </tr>
                        <tr>
                            <th>Bình luận</th>
                            <td>{{ $review->binhLuan ?? 'Không có bình luận' }}</td>
                        </tr>
                        <tr>
                            <th>Ngày đánh giá</th>
                            <td>{{ $review->ngayDanhGia->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Trạng thái</th>
                            <td>
                                @if ($review->trangThai == 'pending')
                                    <span class="badge bg-warning">Chờ duyệt</span>
                                @elseif ($review->trangThai == 'approved')
                                    <span class="badge bg-success">Đã duyệt</span>
                                @elseif ($review->trangThai == 'rejected')
                                    <span class="badge bg-danger">Đã từ chối</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Hình ảnh (nếu có) -->
            @if ($review->urlHinhAnh)
                <div class="mt-4">
                    <h4>Hình ảnh</h4>
                    <img src="{{ $review->urlHinhAnh }}" alt="Hình ảnh bình luận" class="img-fluid" style="max-width: 300px;">
                </div>
            @endif

            <!-- Biến thể sản phẩm (nếu có) -->
            @if ($review->variation)
                <div class="mt-4">
                    <h4>Biến thể Sản phẩm</h4>
                    <table class="table table-bordered">
                        <tr>
                            <th>Màu sắc</th>
                            <td>{{ $review->variation->color ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Kích thước</th>
                            <td>{{ $review->variation->size ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            @endif

            <!-- Nút hành động -->
            <div class="mt-4">
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                @if ($review->trangThai == 'pending')
                    <form action="{{ route('admin.reviews.approve', $review->id_danhGia) }}" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn duyệt bình luận này?');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Duyệt
                        </button>
                    </form>
                    <form action="{{ route('admin.reviews.reject', $review->id_danhGia) }}" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn từ chối bình luận này?');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-times"></i> Từ chối
                        </button>
                    </form>
                @endif
                <form action="{{ route('admin.reviews.destroy', $review->id_danhGia) }}" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bình luận này?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection