@extends('layouts.admin')

@section('title', 'Quản lý Sản phẩm')

@section('content')
    <div class="container-fluid py-4">
        <h1 class="h3 mb-4 text-warm-brown">Danh sách Sản phẩm</h1>

        <div class="mb-4">
            <form action="{{ route('admin.products.index') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Tìm kiếm theo tên sản phẩm..." value="{{ request('search') }}" style="max-width: 300px;">
                <button type="submit" class="btn btn-warm-orange shadow-sm">Tìm kiếm</button>
            </form>
        </div>

        <a href="{{ route('admin.products.create') }}" class="btn btn-warm-orange mb-3 shadow-sm">Thêm Sản phẩm</a>

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
                                <th class="py-3">Danh mục</th>
                                <th class="py-3">Thương hiệu</th>
                                <th class="py-3">Kiểu (Màu)</th>
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
                                    <td>{{ $product->danhMuc->tenDanhMuc ?? 'Không xác định' }}</td>
                                    <td>{{ $product->thuongHieu ?? 'Chưa có thương hiệu' }}</td>
                                    <td>
                                        @if($product->variations->isEmpty())
                                            <span class="text-muted">Không có biến thể</span>
                                        @else
                                            @foreach($product->variations->take(3) as $variation)
                                                <div class="mb-1">
                                                    <strong>{{ $variation->color ?? 'N/A' }}</strong>
                                                    @if($variation->size)
                                                        - {{ $variation->size }}
                                                    @endif
                                                </div>
                                            @endforeach
                                            @if($product->variations->count() > 3)
                                                <span class="text-muted">...</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($product->variations->isEmpty())
                                            <span class="text-muted">Không có giá</span>
                                        @else
                                            @foreach($product->variations->take(3) as $variation)
                                                <div class="mb-1">
                                                    {{ number_format($variation->price, 0) }} VNĐ
                                                    <br>
                                                    <small>(Tồn: {{ $variation->stock }})</small>
                                                </div>
                                            @endforeach
                                            @if($product->variations->count() > 3)
                                                <span class="text-muted">...</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($product->variations->flatMap->images->isEmpty())
                                            <div class="image-container" style="width: 100px; height: 100px; display: flex; justify-content: center; align-items: center;">
                                                <img src="{{ asset('images/default.jpg') }}" alt="Default Image" class="rounded" style="width: 100px; height: 100px; object-fit: cover;">
                                            </div>
                                        @else
                                            <div id="carousel{{ $product->id_sanPham }}" class="carousel slide carousel-fade" data-bs-ride="carousel" style="width: 100px; height: 100px;">
                                                <div class="carousel-inner" style="width: 100%; height: 100%;">
                                                    @foreach($product->variations->flatMap->images as $index => $image)
                                                        <div class="carousel-item {{ $index == 0 ? 'active' : '' }}" style="width: 100%; height: 100%;">
                                                            <div class="image-container" style="width: 100px; height: 100px; display: flex; justify-content: center; align-items: center;">
                                                                <img src="{{ asset('storage/' . $image->image_url) }}" class="d-block rounded" alt="Variation Image" style="width: 100px; height: 100px; object-fit: cover;" onerror="this.src='{{ asset('images/default.jpg') }}';">
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @if($product->variations->flatMap->images->count() > 1)
                                                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel{{ $product->id_sanPham }}" data-bs-slide="prev" style="width: 20%;">
                                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                        <span class="visually-hidden">Previous</span>
                                                    </button>
                                                    <button class="carousel-control-next" type="button" data-bs-target="#carousel{{ $product->id_sanPham }}" data-bs-slide="next" style="width: 20%;">
                                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                        <span class="visually-hidden">Next</span>
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
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

                                        <!-- Modal chi tiết sản phẩm -->
                                        <div class="modal fade" id="viewModal{{ $product->id_sanPham }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $product->id_sanPham }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content bg-light">
                                                    <div class="modal-header bg-warm-brown text-white">
                                                        <h5 class="modal-title" id="viewModalLabel{{ $product->id_sanPham }}">Chi tiết sản phẩm</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body text-dark">
                                                        <ul class="list-group list-group-flush">
                                                            <li class="list-group-item"><strong>Tên:</strong> {{ $product->tenSanPham ?? 'Chưa có tên' }}</li>
                                                            <li class="list-group-item"><strong>Danh mục:</strong> {{ $product->danhMuc->tenDanhMuc ?? 'Không xác định' }}</li>
                                                            <li class="list-group-item"><strong>Thương hiệu:</strong> {{ $product->thuongHieu ?? 'Chưa có thương hiệu' }}</li>
                                                            <li class="list-group-item"><strong>Mô tả:</strong> {{ $product->moTa ?? 'Chưa có mô tả' }}</li>
                                                            <li class="list-group-item"><strong>Trạng thái:</strong> {{ $product->trangThai == 'active' ? 'Kích hoạt' : 'Không kích hoạt' }}</li>
                                                            <li class="list-group-item"><strong>Số lượng bán:</strong> {{ $product->soLuongBan ?? 0 }}</li>
                                                            <li class="list-group-item"><strong>Đánh giá:</strong> {{ number_format($product->soSaoDanhGia ?? 0, 1) }} ⭐</li>
                                                            <li class="list-group-item">
                                                                <strong>Biến thể:</strong>
                                                                <ul>
                                                                    @foreach($product->variations as $variation)
                                                                        <li>
                                                                            <strong>{{ $variation->color ?? 'N/A' }}</strong>
                                                                            @if($variation->size)
                                                                                - {{ $variation->size }}
                                                                            @endif
                                                                            : {{ number_format($variation->price, 0) }} VNĐ (Tồn: {{ $variation->stock }})
                                                                            <br>
                                                                            <strong>Hình ảnh:</strong>
                                                                            @if($variation->images->isEmpty())
                                                                                <span class="text-muted">Không có ảnh</span>
                                                                            @else
                                                                                @foreach($variation->images as $image)
                                                                                    <img src="{{ asset('storage/' . $image->image_url) }}" alt="Variation Image" class="img-thumbnail" style="max-width: 100px;" onerror="this.src='{{ asset('images/default.jpg') }}';">
                                                                                @endforeach
                                                                            @endif
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
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

                    <div class="d-flex justify-content-center mt-3">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        /* Đảm bảo carousel không co giãn */
        .carousel, .carousel-inner, .carousel-item {
            width: 100px !important;
            height: 100px !important;
        }

        /* Sử dụng hiệu ứng fade để chuyển cảnh mượt mà hơn */
        .carousel-fade .carousel-item {
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }

        .carousel-fade .carousel-item.active {
            opacity: 1;
        }

        /* Căn chỉnh nút điều hướng */
        .carousel-control-prev, .carousel-control-next {
            width: 20% !important;
        }
    </style>
@endsection