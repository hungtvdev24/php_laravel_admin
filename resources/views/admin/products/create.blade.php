@extends('layouts.admin')

@section('title', 'Thêm Sản phẩm')

@section('content')
    <div class="container-fluid py-4">
        <h1 class="h3 mb-4 text-warm-brown">Thêm Sản phẩm</h1>

        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="id_danhMuc" class="form-label">Danh mục</label>
                <select name="id_danhMuc" id="id_danhMuc" class="form-control @error('id_danhMuc') is-invalid @enderror" required>
                    @foreach($categories as $category)
                        <option value="{{ $category->id_danhMuc }}" {{ old('id_danhMuc') == $category->id_danhMuc ? 'selected' : '' }}>{{ $category->tenDanhMuc }}</option>
                    @endforeach
                </select>
                @error('id_danhMuc')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="tenSanPham" class="form-label">Tên sản phẩm</label>
                <input type="text" name="tenSanPham" id="tenSanPham" class="form-control @error('tenSanPham') is-invalid @enderror" value="{{ old('tenSanPham') }}" required>
                @error('tenSanPham')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="thuongHieu" class="form-label">Thương hiệu</label>
                <input type="text" name="thuongHieu" id="thuongHieu" class="form-control @error('thuongHieu') is-invalid @enderror" value="{{ old('thuongHieu') }}" required>
                @error('thuongHieu')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="gia" class="form-label">Giá</label>
                <input type="number" name="gia" id="gia" class="form-control @error('gia') is-invalid @enderror" value="{{ old('gia') }}" step="0.01" min="0" max="99999999.99" required>
                @error('gia')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="urlHinhAnh" class="form-label">Ảnh</label>
                <input type="file" name="urlHinhAnh" id="urlHinhAnh" class="form-control @error('urlHinhAnh') is-invalid @enderror">
                @error('urlHinhAnh')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="moTa" class="form-label">Mô tả</label>
                <textarea name="moTa" id="moTa" class="form-control @error('moTa') is-invalid @enderror">{{ old('moTa') }}</textarea>
                @error('moTa')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="trangThai" class="form-label">Trạng thái</label>
                <select name="trangThai" id="trangThai" class="form-control @error('trangThai') is-invalid @enderror" required>
                    <option value="active" {{ old('trangThai') == 'active' ? 'selected' : '' }}>Kích hoạt</option>
                    <option value="inactive" {{ old('trangThai') == 'inactive' ? 'selected' : '' }}>Không kích hoạt</option>
                </select>
                @error('trangThai')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-warm-orange shadow-sm">Lưu</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary shadow-sm">Hủy</a>
        </form>
    </div>
@endsection