@extends('admin')

@section('title', 'Thêm Khách hàng')

@section('content')
    <h1>Thêm Khách hàng</h1>
    <form action="{{ route('admin.customers.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Tên Khách hàng</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Lưu</button>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">Hủy</a>
    </form>
@endsection