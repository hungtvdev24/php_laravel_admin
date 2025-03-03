<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin</title>
    <style>
        /* Định dạng tổng thể */
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to right, #1e3c72, #2a5298);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        /* Khung đăng nhập */
        .login-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            text-align: center;
            width: 350px;
            color: #fff;
        }

        /* Tiêu đề */
        .login-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
        }

        /* Trường nhập liệu */
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            outline: none;
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        /* Nút đăng nhập */
        .btn-login {
            background: #ff914d;
            color: white;
            font-size: 16px;
            padding: 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            transition: 0.3s;
        }

        .btn-login:hover {
            background: #ff7300;
            transform: scale(1.05);
        }

        /* Hiển thị lỗi */
        .error-message {
            color: #ff4d4d;
            font-size: 14px;
            margin-bottom: 10px;
        }

    </style>
</head>
<body>

    <div class="login-container">
        <h2>Đăng nhập Admin</h2>

        @if(session('error'))
            <p class="error-message">{{ session('error') }}</p>
        @endif

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            <div class="form-group">
                <label for="userNameAD">Tên đăng nhập:</label>
                <input type="text" id="userNameAD" name="userNameAD" placeholder="Nhập tên đăng nhập" required>
            </div>

            <div class="form-group">
                <label for="passwordAD">Mật khẩu:</label>
                <input type="password" id="passwordAD" name="passwordAD" placeholder="Nhập mật khẩu" required>
            </div>

            <button type="submit" class="btn-login">Đăng nhập</button>
        </form>
    </div>

</body>
</html>
