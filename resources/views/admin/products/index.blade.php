@extends('layouts.admin')

@section('title', 'Quản lý Sản phẩm')

@section('content')
    <div class="container-fluid py-4">
        <h1 class="h3 mb-4 text-warm-brown font-weight-bold">Danh sách Sản phẩm</h1>

        <!-- Form tìm kiếm -->
        <div class="mb-4 d-flex align-items-center">
            <form action="{{ route('admin.products.index') }}" method="GET" class="d-flex w-100" style="max-width: 500px;">
                <input type="text" name="search" class="form-control me-2 shadow-sm" placeholder="Tìm kiếm theo tên sản phẩm..." value="{{ request('search') }}" style="border-radius: 8px;">
                <button type="submit" class="btn btn-warm-orange shadow-sm" style="border-radius: 8px;">Tìm kiếm</button>
            </form>
            <a href="{{ route('admin.products.create') }}" class="btn btn-warm-orange ms-3 shadow-sm" style="border-radius: 8px;">Thêm Sản phẩm</a>
        </div>

        <!-- Danh sách sản phẩm -->
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-body p-4">
                @if ($products->isEmpty())
                    <div class="alert alert-info text-center" role="alert">
                        Không có sản phẩm nào để hiển thị.
                    </div>
                @else
                    <div class="row">
                        @foreach($products as $product)
                            <div class="col-12 mb-4">
                                <div class="card shadow-sm border-0 h-100" style="border-radius: 10px;">
                                    <div class="card-body d-flex align-items-center p-3">
                                        <!-- Ảnh sản phẩm -->
                                        <div class="me-4" style="flex: 0 0 120px;">
                                            @if($product->variations->flatMap->images->isEmpty())
                                                <img src="{{ asset('images/default.jpg') }}" alt="Default Image" class="rounded" style="width: 120px; height: 120px; object-fit: cover;">
                                            @else
                                                <div id="carousel{{ $product->id_sanPham }}" class="carousel slide carousel-fade" data-bs-ride="carousel" style="width: 120px; height: 120px;">
                                                    <div class="carousel-inner">
                                                        @foreach($product->variations->flatMap->images as $index => $image)
                                                            <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                                                <img src="{{ asset('storage/' . $image->image_url) }}" class="d-block rounded" alt="Variation Image" style="width: 120px; height: 120px; object-fit: cover;" onerror="this.src='{{ asset('images/default.jpg') }}';">
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
                                        </div>

                                        <!-- Thông tin sản phẩm -->
                                        <div class="flex-grow-1">
                                            <h5 class="card-title mb-2">{{ $product->tenSanPham ?? 'Chưa có tên' }}</h5>
                                            <p class="text-muted mb-1">
                                                <strong>Danh mục:</strong> {{ $product->danhMuc->tenDanhMuc ?? 'Không xác định' }} |
                                                <strong>Thương hiệu:</strong> {{ $product->thuongHieu ?? 'Chưa có thương hiệu' }}
                                            </p>
                                            <p class="text-muted mb-1">
                                                <strong>Trạng thái:</strong>
                                                <span class="badge {{ $product->trangThai == 'active' ? 'bg-success' : 'bg-secondary' }} rounded-pill">
                                                    {{ $product->trangThai == 'active' ? 'Kích hoạt' : 'Không kích hoạt' }}
                                                </span>
                                            </p>
                                            <p class="text-muted mb-1">
                                                <strong>Đánh giá:</strong> {{ number_format($product->soSaoDanhGia ?? 0, 1) }} ⭐ |
                                                <strong>Số lượng bán:</strong> {{ $product->soLuongBan ?? 0 }}
                                            </p>
                                            @if($product->variations->isEmpty())
                                                <p class="text-muted mb-0">Không có biến thể</p>
                                            @else
                                                <p class="text-muted mb-0">
                                                    <strong>Biến thể:</strong>
                                                    @foreach($product->variations->take(2) as $variation)
                                                        <span>
                                                            {{ $variation->color ?? 'N/A' }}
                                                            @if($variation->size)
                                                                - {{ $variation->size }}
                                                            @endif
                                                            ({{ number_format($variation->price, 0) }} VNĐ, Tồn: {{ $variation->stock }})
                                                        </span>
                                                        @if(!$loop->last), @endif
                                                    @endforeach
                                                    @if($product->variations->count() > 2)
                                                        <span class="text-muted">+{{ $product->variations->count() - 2 }} biến thể khác</span>
                                                    @endif
                                                </p>
                                            @endif
                                        </div>

                                        <!-- Thao tác -->
                                        <div class="ms-3">
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-info btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#viewModal{{ $product->id_sanPham }}">
                                                    <i class="fas fa-eye"></i> Xem chi tiết
                                                </button>
                                                <a href="{{ route('admin.products.edit', $product->id_sanPham) }}" class="btn btn-warning btn-sm shadow-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal chi tiết sản phẩm (toàn màn hình) -->
                            <div class="modal fade" id="viewModal{{ $product->id_sanPham }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $product->id_sanPham }}" aria-hidden="true">
                                <div class="modal-dialog modal-fullscreen">
                                    <div class="modal-content bg-light">
                                        <div class="modal-header bg-warm-brown text-white">
                                            <h5 class="modal-title" id="viewModalLabel{{ $product->id_sanPham }}">Chi tiết sản phẩm: {{ $product->tenSanPham ?? 'Chưa có tên' }}</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-dark">
                                            <div class="container-fluid">
                                                <div class="row">
                                                    <!-- Cột ảnh sản phẩm -->
                                                    <div class="col-lg-6 mb-4">
                                                        <h6 class="font-weight-bold mb-3">Hình ảnh sản phẩm</h6>
                                                        @if($product->variations->flatMap->images->isEmpty())
                                                            <div class="text-center">
                                                                <img src="{{ asset('images/default.jpg') }}" alt="Default Image" class="rounded" style="max-width: 300px; max-height: 300px; object-fit: cover;">
                                                                <p class="text-muted mt-2">Không có ảnh</p>
                                                            </div>
                                                        @else
                                                            <div id="carouselFull{{ $product->id_sanPham }}" class="carousel slide carousel-fade" data-bs-ride="carousel" style="max-width: 500px; margin: 0 auto;">
                                                                <div class="carousel-inner">
                                                                    @foreach($product->variations->flatMap->images as $index => $image)
                                                                        <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                                                            <img src="{{ asset('storage/' . $image->image_url) }}" class="d-block rounded" alt="Variation Image" style="width: 100%; max-height: 400px; object-fit: cover;" onerror="this.src='{{ asset('images/default.jpg') }}';">
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                                @if($product->variations->flatMap->images->count() > 1)
                                                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselFull{{ $product->id_sanPham }}" data-bs-slide="prev">
                                                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                                        <span class="visually-hidden">Previous</span>
                                                                    </button>
                                                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselFull{{ $product->id_sanPham }}" data-bs-slide="next">
                                                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                                        <span class="visually-hidden">Next</span>
                                                                    </button>
                                                                @endif
                                                            </div>
                                                            <div class="text-center mt-3">
                                                                <small class="text-muted">Tổng cộng {{ $product->variations->flatMap->images->count() }} ảnh</small>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <!-- Cột thông tin sản phẩm -->
                                                    <div class="col-lg-6 mb-4">
                                                        <h6 class="font-weight-bold mb-3">Thông tin cơ bản</h6>
                                                        <table class="table table-borderless">
                                                            <tr>
                                                                <th style="width: 30%;">Tên:</th>
                                                                <td>{{ $product->tenSanPham ?? 'Chưa có tên' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Danh mục:</th>
                                                                <td>{{ $product->danhMuc->tenDanhMuc ?? 'Không xác định' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Thương hiệu:</th>
                                                                <td>{{ $product->thuongHieu ?? 'Chưa có thương hiệu' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Mô tả:</th>
                                                                <td>{{ $product->moTa ?? 'Chưa có mô tả' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Trạng thái:</th>
                                                                <td>
                                                                    <span class="badge {{ $product->trangThai == 'active' ? 'bg-success' : 'bg-secondary' }} rounded-pill">
                                                                        {{ $product->trangThai == 'active' ? 'Kích hoạt' : 'Không kích hoạt' }}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Số lượng bán:</th>
                                                                <td>{{ $product->soLuongBan ?? 0 }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Đánh giá:</th>
                                                                <td>{{ number_format($product->soSaoDanhGia ?? 0, 1) }} ⭐</td>
                                                            </tr>
                                                        </table>

                                                        <h6 class="font-weight-bold mt-4 mb-3">Biến thể</h6>
                                                        @if($product->variations->isEmpty())
                                                            <p class="text-muted">Không có biến thể</p>
                                                        @else
                                                            <div class="accordion" id="accordionVariations{{ $product->id_sanPham }}">
                                                                @foreach($product->variations as $index => $variation)
                                                                    <div class="accordion-item">
                                                                        <h2 class="accordion-header" id="heading{{ $variation->id }}">
                                                                            <button class="accordion-button {{ $index != 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $variation->id }}" aria-expanded="{{ $index == 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $variation->id }}">
                                                                                <strong>{{ $variation->color ?? 'N/A' }}</strong>
                                                                                @if($variation->size)
                                                                                    - {{ $variation->size }}
                                                                                @endif
                                                                            </button>
                                                                        </h2>
                                                                        <div id="collapse{{ $variation->id }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" aria-labelledby="heading{{ $variation->id }}" data-bs-parent="#accordionVariations{{ $product->id_sanPham }}">
                                                                            <div class="accordion-body">
                                                                                <p><strong>Giá:</strong> {{ number_format($variation->price, 0) }} VNĐ</p>
                                                                                <p><strong>Tồn kho:</strong> {{ $variation->stock }}</p>
                                                                                <p><strong>Hình ảnh:</strong></p>
                                                                                @if($variation->images->isEmpty())
                                                                                    <span class="text-muted">Không có ảnh</span>
                                                                                @else
                                                                                    <div class="d-flex flex-wrap gap-2">
                                                                                        @foreach($variation->images as $image)
                                                                                            <img src="{{ asset('storage/' . $image->image_url) }}" alt="Variation Image" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;" onerror="this.src='{{ asset('images/default.jpg') }}';">
                                                                                        @endforeach
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                            <a href="{{ route('admin.products.edit', $product->id_sanPham) }}" class="btn btn-warning shadow-sm">Chỉnh sửa</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $products->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        /* Tinh chỉnh carousel trong danh sách */
        .carousel, .carousel-inner, .carousel-item {
            width: 120px !important;
            height: 120px !important;
        }

        .carousel-fade .carousel-item {
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }

        .carousel-fade .carousel-item.active {
            opacity: 1;
        }

        .carousel-control-prev, .carousel-control-next {
            width: 20% !important;
            background: rgba(0, 0, 0, 0.2);
        }

        /* Tinh chỉnh card sản phẩm */
        .card-body {
            padding: 1.5rem !important;
        }

        .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
        }

        /* Tinh chỉnh modal toàn màn hình */
        .modal-fullscreen .modal-content {
            border-radius: 0;
        }

        .modal-fullscreen .modal-body {
            padding: 2rem;
        }

        .modal-fullscreen .carousel {
            max-width: 500px !important;
        }

        .modal-fullscreen .carousel-item img {
            max-height: 400px !important;
        }

        /* Tinh chỉnh accordion trong modal */
        .accordion-button {
            font-size: 1rem;
            padding: 0.75rem 1rem;
        }

        .accordion-body {
            padding: 1rem;
        }

        /* Tinh chỉnh nút và badge */
        .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.9rem;
        }

        .badge {
            font-size: 0.85rem;
            padding: 0.35rem 0.75rem;
        }

        /* Tinh chỉnh bảng thông tin trong modal */
        .table-borderless th {
            font-weight: 600;
            color: #5a3e1b;
        }

        .table-borderless td {
            font-size: 0.95rem;
        }
    </style>
@endsection