<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Trang Quản Trị')</title>
   
    <!-- Link CSS (Bootstrap và Font Awesome) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Thêm CSS jQuery UI -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS tùy chỉnh -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            display: flex;
            background: #f5f7fa;
            color: #333;
            overflow-x: hidden;
        }
        .sidebar {
            width: 260px;
            background: linear-gradient(135deg, #4682b4, #5f9ea0);
            height: 100vh;
            padding: 20px;
            color: white;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.15);
            position: fixed;
            transition: transform 0.4s cubic-bezier(0.25, 0.1, 0.25, 1);
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: 700;
            font-size: 24px;
            color: #e6f3ff;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.15);
        }
        .sidebar a, .sidebar form {
            display: block;
            color: white;
            padding: 12px 15px;
            text-decoration: none;
            border-radius: 8px;
            transition: 0.3s ease;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar a:hover, .sidebar a.active, .sidebar form:hover button {
            background: #87ceeb;
            padding-left: 20px;
            color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .sidebar form button {
            background: none;
            border: none;
            color: white;
            font-weight: 500;
            padding: 0;
            width: 100%;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .content {
            flex: 1;
            padding: 30px;
            margin-left: 260px;
            min-height: 100vh;
            background: #ffffff;
            border-radius: 8px 0 0 8px;
            box-shadow: -2px 0 15px rgba(0, 0, 0, 0.1);
            transition: margin-left 0.4s cubic-bezier(0.25, 0.1, 0.25, 1);
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
                transform: translateX(-100%);
                z-index: 1000;
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .content {
                margin-left: 0;
                padding: 15px;
            }
            .sidebar-toggle {
                display: block;
                position: fixed;
                top: 15px;
                left: 15px;
                z-index: 1001;
                background: #4682b4;
                color: white;
                border: none;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                font-size: 20px;
                cursor: pointer;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
                transition: background 0.3s ease;
            }
            .sidebar-toggle:hover {
                background: #87ceeb;
            }
        }
        @keyframes slideIn {
            from { transform: translateX(-100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .sidebar.active {
            animation: slideIn 0.4s cubic-bezier(0.25, 0.1, 0.25, 1);
        }
        h1, h2, h3, h4, h5, h6 {
            color: #4682b4;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        .table-warm-header {
            background-color: #5f9ea0 !important;
            color: white;
        }
        .btn-warm-cool {
            background-color: #87ceeb !important;
            border-color: #87ceeb !important;
            color: white;
        }
        .btn-warm-cool:hover {
            background-color: #76c2d7 !important;
            border-color: #76c2d7 !important;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Nút toggle sidebar cho mobile -->
    <button class="sidebar-toggle d-md-none" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <h2>Quản trị</h2>
        <!-- Phần Hệ Thống -->
        <h3 style="margin-top: 20px; font-size: 16px; color: #e6f3ff;">Hệ Thống</h3>
        <a href="{{ route('admin.dashboard') }}" class="{{ Request::is('admin/dashboard') ? 'active' : '' }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="{{ route('admin.affiliate.notifications.index') }}" class="{{ Request::is('admin/affiliate/notifications*') ? 'active' : '' }}"><i class="fas fa-bell"></i> Thông báo</a>
        <a href="{{ route('admin.affiliate.vouchers.index') }}" class="{{ Request::is('admin/affiliate/vouchers*') ? 'active' : '' }}"><i class="fas fa-ticket-alt"></i> Quản lý Voucher</a>
        @if(session('role') === 'admin')
            <a href="{{ route('admin.employees.index') }}" class="{{ Request::is('admin/employees*') ? 'active' : '' }}"><i class="fas fa-user-tie"></i> Quản lý Nhân viên</a>
        @endif
        <a href="{{ route('admin.users.index') }}" class="{{ Request::is('admin/users*') ? 'active' : '' }}"><i class="fas fa-users"></i> Quản lý Users</a>
        <a href="{{ route('admin.danhmucs.index') }}" class="{{ Request::is('admin/danhmucs*') ? 'active' : '' }}"><i class="fas fa-list"></i> Danh mục</a>

        <!-- Phần Đơn hàng - Sản phẩm -->
        <h3 style="margin-top: 20px; font-size: 16px; color: #e6f3ff;">Quản lý Dữ liệu</h3>
        <a href="{{ route('admin.orders.index') }}" class="{{ Request::is('admin/orders*') ? 'active' : '' }}"><i class="fas fa-box"></i> Đơn hàng</a>
        <a href="{{ route('admin.products.index') }}" class="{{ Request::is('admin/products*') ? 'active' : '' }}"><i class="fas fa-box-open"></i> Sản phẩm</a>
        <a href="{{ route('admin.reviews.index') }}" class="{{ Request::is('admin/reviews*') ? 'active' : '' }}"><i class="fas fa-comment"></i> Bình luận</a>
        <a href="{{ route('admin.chat.index') }}" class="{{ Request::is('admin/chat*') ? 'active' : '' }}"><i class="fas fa-comments"></i> Chat</a>

        <!-- Nút Đăng xuất -->
        <form action="{{ route('admin.logout') }}" method="POST" style="margin-top: auto;">
            @csrf
            <button type="submit"><i class="fas fa-sign-out-alt"></i> Đăng xuất</button>
        </form>
    </div>
    <!-- Nội dung chính -->
    <div class="content">
        @yield('content')
    </div>
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.querySelector('.sidebar-toggle');
            if (window.innerWidth <= 768px && !sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });
        document.querySelector('.sidebar-toggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.style.willChange = 'transform';
            setTimeout(() => sidebar.style.willChange = 'auto', 400);
        });
    </script>
    @yield('scripts')
</body>
</html>