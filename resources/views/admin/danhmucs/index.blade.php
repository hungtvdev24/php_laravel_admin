@extends('layouts.admin')

@section('title', 'Quản lý Danh mục')

@section('content')
    <div class="container-fluid py-4">
        <h1 class="h3 mb-4" style="color: #4682b4;">Danh sách Danh mục</h1>

        <!-- Nút thêm danh mục mới -->
        <a href="{{ route('admin.danhmucs.create') }}" class="btn btn-warm-cool mb-3 shadow-sm">Thêm Danh mục</a>

        <!-- Bảng hiển thị danh mục -->
        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-hover table-bordered text-center">
                    <thead class="table-warm-header">
                        <tr>
                            <th class="py-3">ID</th>
                            <th class="py-3">Tên Danh mục</th>
                            <th class="py-3">Mô tả</th>
                            <th class="py-3">Ngày tạo</th>
                            <th class="py-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($danhMucs as $danhMuc)
                            <tr class="align-middle">
                                <td>{{ $danhMuc->id_danhMuc }}</td>
                                <td>{{ $danhMuc->tenDanhMuc }}</td>
                                <td>{{ Str::limit($danhMuc->moTa, 50) }}</td>
                                <td>{{ $danhMuc->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('admin.danhmucs.edit', $danhMuc->id_danhMuc) }}" class="btn btn-warning btn-sm shadow-sm">Sửa</a>
                                        <button type="button" class="btn btn-danger btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $danhMuc->id_danhMuc }}">
                                            Xóa
                                        </button>
                                    </div>

                                    <!-- Modal xác nhận xóa -->
                                    <div class="modal fade" id="deleteModal{{ $danhMuc->id_danhMuc }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $danhMuc->id_danhMuc }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content bg-light">
                                                <div class="modal-header" style="background-color: #5f9ea0; color: white;">
                                                    <h5 class="modal-title" id="deleteModalLabel{{ $danhMuc->id_danhMuc }}">Xác nhận xóa danh mục</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-dark">
                                                    <p>Bạn có chắc muốn xóa danh mục sau?</p>
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item"><strong>Tên:</strong> {{ $danhMuc->tenDanhMuc }}</li>
                                                        <li class="list-group-item"><strong>Mô tả:</strong> {{ $danhMuc->moTa ?? 'Không có mô tả' }}</li>
                                                    </ul>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Hủy</button>
                                                    <form action="{{ route('admin.danhmucs.destroy', $danhMuc->id_danhMuc) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger shadow-sm">Xác nhận Xóa</button>
                                                    </form>
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
                {{ $danhMucs->links() }}
            </div>
        </div>
    </div>
@endsection
