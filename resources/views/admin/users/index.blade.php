@extends('layouts.admin')

@section('title', 'Quản lý Người dùng')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-header bg-warm-brown text-light d-flex justify-content-between align-items-center">
                <h3 class="my-2">📋 Danh sách Người dùng</h3>
                <input type="text" id="searchPhone" class="form-control w-25" placeholder="🔍 Nhập số điện thoại..." onkeyup="searchUser()">
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle text-center" id="usersTable">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Tên</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                <th>Tuổi</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr class="table-light">
                                    <td><strong>{{ $user->id }}</strong></td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td class="user-phone">{{ $user->phone ?? '' }}</td>
                                    <td>{{ $user->tuoi ?? '❌ Chưa có dữ liệu' }}</td>
                                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a href="#" class="btn btn-sm btn-outline-info shadow-sm">👁️ Xem</a>
                                            <a href="#" class="btn btn-sm btn-outline-warning shadow-sm">✏️ Sửa</a>
                                            <button class="btn btn-sm btn-outline-danger shadow-sm" onclick="confirmDelete({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', '{{ $user->phone }}')">🗑️ Xóa</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-end">
                    <a href="#" class="btn btn-lg btn-warm-orange shadow mt-3">➕ Thêm Người dùng</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal xác nhận xóa -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-light">
                <div class="modal-header bg-warm-brown text-white">
                    <h5 class="modal-title" id="deleteUserLabel">⚠️ Xác nhận xóa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-dark">
                    <p><strong>🆔 ID:</strong> <span id="modalUserId"></span></p>
                    <p><strong>👤 Tên:</strong> <span id="modalUserName"></span></p>
                    <p><strong>📧 Email:</strong> <span id="modalUserEmail"></span></p>
                    <p><strong>📞 Số điện thoại:</strong> <span id="modalUserPhone"></span></p>
                    <p class="text-danger"><strong>Bạn có chắc chắn muốn xóa người dùng này?</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Hủy</button>
                    <form id="deleteUserForm" action="" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger shadow-sm">Xác nhận Xóa</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function searchUser() {
            let input = document.getElementById("searchPhone").value.trim().toLowerCase();
            let rows = document.querySelectorAll("#usersTable tbody tr");

            rows.forEach(row => {
                let phoneCell = row.querySelector(".user-phone");
                let userPhone = phoneCell.innerText.trim().toLowerCase();

                // Nếu không có số điện thoại, thay bằng chuỗi rỗng
                if (!userPhone || userPhone === "❌ chưa cập nhật") {
                    userPhone = "";
                }

                // Kiểm tra nếu số điện thoại chứa giá trị tìm kiếm
                row.style.display = userPhone.includes(input) ? "" : "none";
            });
        }

        function confirmDelete(id, name, email, phone) {
            document.getElementById("modalUserId").innerText = id;
            document.getElementById("modalUserName").innerText = name;
            document.getElementById("modalUserEmail").innerText = email;
            document.getElementById("modalUserPhone").innerText = phone || '❌ Chưa cập nhật';
            document.getElementById("deleteUserForm").action = `/admin/users/${id}`;
            new bootstrap.Modal(document.getElementById("deleteUserModal")).show();
        }
    </script>

    <!-- CSS tùy chỉnh cho màu ấm -->
    <style>
        .bg-warm-brown {
            background-color: #a0522d !important; /* Nâu ấm vừa */
        }

        .btn-warm-orange {
            background-color: #f4a460 !important; /* Cam nhạt ấm */
            border-color: #f4a460 !important;
            color: white;
        }

        .btn-warm-orange:hover {
            background-color: #e08e4e !important;
            border-color: #e08e4e !important;
            color: white;
        }

        .shadow-sm {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
        }

        .table-dark {
            background-color: #8b4513 !important; /* Nâu đậm cho header bảng */
            color: white;
        }

        .table-light:hover {
            background-color: #f8f9fa !important;
        }

        .btn-outline-danger:hover {
            background-color: #dc3545 !important;
            color: white !important;
        }

        .btn-outline-info:hover {
            background-color: #17a2b8 !important;
            color: white !important;
        }

        .btn-outline-warning:hover {
            background-color: #ffc107 !important;
            color: white !important;
        }
    </style>
@endsection