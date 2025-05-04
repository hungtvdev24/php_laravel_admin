@extends('layouts.admin')

@section('title', 'Quản lý Khách hàng')

@section('content')
<div class="container mt-4">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-warm-brown text-light d-flex justify-content-between align-items-center">
            <h3 class="my-2">📋 Danh sách Khách hàng</h3>
            <input type="text" id="searchPhone" class="form-control w-25" placeholder="🔍 Nhập số điện thoại..." onkeyup="searchUser()">
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle" id="usersTable">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-start">ID</th>
                            <th class="text-start">Tên</th>
                            <th class="text-start">Email</th>
                            <th class="text-start">Số điện thoại</th>
                            <th class="text-start">Ngày tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr class="table-light">
                            <td class="text-start"><strong>{{ $user->id }}</strong></td>
                            <td class="text-start">{{ $user->name }}</td>
                            <td class="text-start">{{ $user->email }}</td>
                            <td class="text-start user-phone">{{ $user->phone ?? '' }}</td>
                            <td class="text-start">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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
</script>

<!-- CSS tùy chỉnh cho màu ấm -->
<style>
.bg-warm-brown {
    background-color:rgb(255, 255, 255) !important; /* Nâu ấm đậm, không quá tối */
    color: #f5e6cc !important; /* Màu chữ kem nhạt, tương phản tốt */
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
    background-color: #3f2b22 !important; /* Nâu đậm hơn cho header bảng */
    color: #f5e6cc !important; /* Màu chữ kem nhạt, tương phản tốt */
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