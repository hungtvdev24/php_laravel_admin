@extends('layouts.admin')

@section('title', 'Quản lý Sản phẩm')

@section('content')
    <div class="container-fluid py-4">
        <h1 class="h3 mb-4 text-warm-brown">Danh sách Sản phẩm</h1>

        <!-- Form tìm kiếm -->
        <div class="mb-4">
            <form action="{{ route('admin.products.index') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Tìm kiếm theo tên sản phẩm..." value="{{ request('search') }}" style="max-width: 300px;">
                <button type="submit" class="btn btn-warm-orange shadow-sm">Tìm kiếm</button>
            </form>
        </div>

        <!-- Nút thêm sản phẩm mới -->
        <a href="{{ route('admin.products.create') }}" class="btn btn-warm-orange mb-3 shadow-sm">Thêm Sản phẩm</a>

        <!-- Bảng hiển thị sản phẩm -->
        <div class="card shadow-sm">
            <div class="card-body">
                @if ($products->isEmpty())
                    <div class="alert alert-info text-center" role="alert">
                        Không có sản phẩm nào để hiển thị.
                    </div>
                @else
                    <table class="table table-hover table-bordered text-center">
                        <thead class="table-warm-header">
                            <tr>
                                <th class="py-3">Tên</th>
                                <th class="py-3">Thương hiệu</th>
                                <th class="py-3">Giá</th>
                                <th class="py-3">Ảnh</th>
                                <th class="py-3">Số lượng bán</th>
                                <th class="py-3">Đánh giá</th>
                                <th class="py-3">Trạng thái</th>
                                <th class="py-3">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr class="align-middle">
                                    <td>{{ $product->tenSanPham ?? 'Chưa có tên' }}</td>
                                    <td>{{ $product->thuongHieu ?? 'Chưa có thương hiệu' }}</td>
                                    <td>{{ number_format($product->gia ?? 0, 0) }} VNĐ</td>
                                    <td>
                                        <img src="{{ $product->urlHinhAnh ? asset('storage/' . $product->urlHinhAnh) : asset('images/default.jpg') }}" alt="{{ $product->tenSanPham ?? 'Hình ảnh sản phẩm' }}" class="img-fluid rounded" style="max-width: 100px; max-height: 100px;" onerror="this.src='{{ asset('images/default.jpg') }}';">
                                    </td>
                                    <td>{{ $product->soLuongBan ?? 0 }}</td>
                                    <td>{{ number_format($product->soSaoDanhGia ?? 0, 1) }} ⭐</td>
                                    <td>
                                        <span class="badge {{ $product->trangThai == 'active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $product->trangThai == 'active' ? 'Kích hoạt' : 'Không kích hoạt' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button type="button" class="btn btn-info btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#viewModal{{ $product->id_sanPham }}">
                                                👁
                                            </button>
                                            <a href="{{ route('admin.products.edit', $product->id_sanPham) }}" class="btn btn-warning btn-sm shadow-sm">
                                                ✏
                                            </a>
                                            <form action="{{ route('admin.products.destroy', $product->id_sanPham) }}" method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm shadow-sm">🗑</button>
                                            </form>
                                        </div>

                                        <!-- Modal hiển thị chi tiết sản phẩm -->
                                        <div class="modal fade" id="viewModal{{ $product->id_sanPham }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $product->id_sanPham }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content bg-light">
                                                    <div class="modal-header bg-warm-brown text-white">
                                                        <h5 class="modal-title" id="viewModalLabel{{ $product->id_sanPham }}">Chi tiết sản phẩm</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body text-dark">
                                                        <ul class="list-group list-group-flush">
                                                            <li class="list-group-item"><strong>Tên:</strong> {{ $product->tenSanPham ?? 'Chưa có tên' }}</li>
                                                            <li class="list-group-item"><strong>Thương hiệu:</strong> {{ $product->thuongHieu ?? 'Chưa có thương hiệu' }}</li>
                                                            <li class="list-group-item"><strong>Giá:</strong> {{ number_format($product->gia ?? 0, 0) }} VNĐ</li>
                                                            <li class="list-group-item"><strong>Mô tả:</strong> {{ $product->moTa ?? 'Chưa có mô tả' }}</li>
                                                            <li class="list-group-item"><strong>Trạng thái:</strong> {{ $product->trangThai == 'active' ? 'Kích hoạt' : 'Không kích hoạt' }}</li>
                                                            <li class="list-group-item"><strong>Số lượng bán:</strong> {{ $product->soLuongBan ?? 0 }}</li>
                                                            <li class="list-group-item"><strong>Đánh giá:</strong> {{ number_format($product->soSaoDanhGia ?? 0, 1) }} ⭐</li>
                                                            <li class="list-group-item"><strong>Danh mục:</strong> {{ $product->danhMuc->tenDanhMuc ?? 'Không xác định' }}</li>
                                                            <li class="list-group-item text-center">
                                                                <strong>Ảnh:</strong>
                                                                <br>
                                                                <img src="{{ $product->urlHinhAnh ? asset('storage/' . $product->urlHinhAnh) : asset('images/default.jpg') }}" alt="{{ $product->tenSanPham ?? 'Hình ảnh sản phẩm' }}" class="img-fluid rounded" style="max-width: 200px;" onerror="this.src='{{ asset('images/default.jpg') }}';">
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Phân trang -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection