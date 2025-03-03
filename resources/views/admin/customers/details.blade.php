@extends('admin')

@section('title', 'Chi tiết Khách hàng')

@section('content')
    <h1>Chi tiết Khách hàng</h1>
    <p>Thông tin chi tiết về khách hàng ID: {{ $customer->id }}</p>
    <!-- Hiển thị thông tin cụ thể từ $customer -->
    <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">Quay lại</a>
    <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-primary">Sửa</a>
@endsection