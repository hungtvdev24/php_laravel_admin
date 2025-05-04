@extends('layouts.admin')

@section('title', 'Quản lý Nhân viên')

@section('content')
    <h1>Danh sách Nhân viên</h1>
    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">Thêm Nhân viên</a>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên</th>
                <th>Tuổi</th>
                <th>Địa chỉ</th>
                <th>Tài khoản</th>
                <th>Trạng thái</th>
                <th>Admin tạo</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $employee)
                <tr>
                    <td>{{ $employee->id_nhanVien }}</td>
                    <td>{{ $employee->tenNhanVien }}</td>
                    <td>{{ $employee->tuoi }}</td>
                    <td>{{ $employee->diaChi }}</td>
                    <td>{{ $employee->tenTaiKhoan }}</td>
                    <td>{{ $employee->trangThai }}</td>
                    <td>{{ $employee->admin->userNameAD ?? 'N/A' }}</td>
                    <td>
                        <a href="{{ route('admin.employees.edit', $employee->id_nhanVien) }}" class="btn btn-warning btn-sm">Sửa</a>
                        
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection