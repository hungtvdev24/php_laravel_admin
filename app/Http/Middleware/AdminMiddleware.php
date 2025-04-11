<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Kiểm tra nếu người dùng chưa đăng nhập
        if (!session()->has('logged_in')) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập.');
        }

        // Kiểm tra vai trò
        $role = session('role');
        if (!in_array($role, ['admin', 'employee'])) {
            return redirect()->route('login')->with('error', 'Bạn không có quyền truy cập.');
        }

        // Thêm thông tin user_type vào request để sử dụng trong controller
        $request->attributes->add(['user_type' => $role]);

        return $next($request);
    }
}