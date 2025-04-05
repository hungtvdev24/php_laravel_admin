@extends('layouts.admin')

@section('title', 'Tạo thông báo mới')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4">Tạo thông báo mới</h1>

        <!-- Form tạo thông báo -->
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.affiliate.notifications.store') }}" method="POST">
                    @csrf

                    <!-- Tiêu đề -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nội dung -->
                    <div class="mb-3">
                        <label for="content" class="form-label">Nội dung <span class="text-danger">*</span></label>
                        <textarea name="content" id="content" class="form-control @error('content') is-invalid @enderror" rows="5" required>{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Chọn người nhận -->
                    <div class="mb-3">
                        <label for="user_ids" class="form-label">Người nhận (Chọn nhiều người, nếu không chọn sẽ gửi cho tất cả)</label>
                        <select name="user_ids[]" id="user_ids" class="form-select select2" multiple>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Nút gửi -->
                    <div class="mb-3 d-flex justify-content-between">
                        <div>
                            <button type="submit" class="btn btn-primary me-2">Gửi thông báo</button>
                            <button type="submit" name="send_to_all" value="1" class="btn btn-success">Gửi cho tất cả</button>
                        </div>
                        <a href="{{ route('admin.affiliate.notifications.index') }}" class="btn btn-secondary">Quay lại</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Chọn người nhận",
                allowClear: true
            });
        });
    </script>
@endsection