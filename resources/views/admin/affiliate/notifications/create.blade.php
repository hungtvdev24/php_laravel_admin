@extends('layouts.admin')

@section('title', 'Tạo thông báo mới')

@section('content')
    <h1 class="mb-4">Tạo thông báo mới</h1>

    <!-- Form tạo thông báo -->
    <form action="{{ route('admin.affiliate.notifications.store') }}" method="POST">
        @csrf

        <!-- Tiêu đề -->
        <div class="mb-3">
            <label for="title" class="form-label">Tiêu đề</label>
            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Nội dung -->
        <div class="mb-3">
            <label for="content" class="form-label">Nội dung</label>
            <textarea name="content" id="content" class="form-control @error('content') is-invalid @enderror" rows="5" required>{{ old('content') }}</textarea>
            @error('content')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Chọn người nhận -->
        <div class="mb-3">
            <label for="user_ids" class="form-label">Người nhận (Chọn nhiều người, nếu không chọn sẽ gửi cho tất cả)</label>
            <select name="user_ids[]" id="user_ids" class="form-select" multiple>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
        </div>

        <!-- Nút gửi -->
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Gửi thông báo</button>
            <a href="{{ route('admin.affiliate.notifications.index') }}" class="btn btn-secondary">Quay lại</a>
        </div>
    </form>
@endsection