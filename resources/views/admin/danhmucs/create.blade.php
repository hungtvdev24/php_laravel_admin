@extends('layouts.admin')

@section('title', 'Thêm Danh mục')

@section('content')
    <div class="container-fluid py-4">
        <h1 class="h3 mb-4" style="color: #4682b4;">Thêm Danh mục</h1>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.danhmucs.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="tenDanhMuc" class="form-label">Tên danh mục</label>
                        <input type="text" name="tenDanhMuc" id="tenDanhMuc" class="form-control @error('tenDanhMuc') is-invalid @enderror" value="{{ old('tenDanhMuc') }}" required>
                        @error('tenDanhMuc')
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
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warm-cool shadow-sm">Lưu</button>
                        <a href="{{ route('admin.danhmucs.index') }}" class="btn btn-secondary shadow-sm">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection