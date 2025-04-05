@extends('layouts.admin')

@section('title', 'Sửa Nhân viên')

@section('content')
    <h1>Sửa Thông tin Nhân viên</h1>
    <form action="{{ route('admin.employees.update', $employee->id_nhanVien) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label>Tên nhân viên</label>
            <input type="text" name="tenNhanVien" class="form-control @error('tenNhanVien') is-invalid @enderror" value="{{ old('tenNhanVien', $employee->tenNhanVien) }}" required>
            @error('tenNhanVien')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label>Tuổi</label>
            <input type="number" name="tuoi" class="form-control @error('tuoi') is-invalid @enderror" value="{{ old('tuoi', $employee->tuoi) }}" min="18" required>
            @error('tuoi')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label>Địa chỉ</label>
            <input type="text" name="diaChi" class="form-control @error('diaChi') is-invalid @enderror" value="{{ old('diaChi', $employee->diaChi) }}" required>
            @error('diaChi')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label>Tên tài khoản</label>
            <input type="text" name="tenTaiKhoan" class="form-control @error('tenTaiKhoan') is-invalid @enderror" value="{{ old('tenTaiKhoan', $employee->tenTaiKhoan) }}" required>
            @error('tenTaiKhoan')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label>Mật khẩu (để trống nếu không đổi)</label>
            <input type="password" name="matKhau" class="form-control @error('matKhau') is-invalid @enderror">
            @error('matKhau')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label>Trạng thái</label>
            <select name="trangThai" class="form-control @error('trangThai') is-invalid @enderror" required>
                <option value="active" {{ $employee->trangThai == 'active' ? 'selected' : '' }}>Hoạt động</option>
                <option value="inactive" {{ $employee->trangThai == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
            </select>
            @error('trangThai')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Cập nhật</button>
    </form>
@endsection