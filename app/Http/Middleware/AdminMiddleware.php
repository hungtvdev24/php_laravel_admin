<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Kiểm tra nếu người dùng chưa đăng nhập vào guard admin hoặc employee
        if (!Auth::guard('admin')->check() && !Auth::guard('employee')->check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập.');
        }

        // Xác định vai trò dựa trên guard
        $role = Auth::guard('admin')->check() ? 'admin' : 'employee';

        // Thêm thông tin user_type vào request để sử dụng trong controller
        $request->attributes->add(['user_type' => $role]);

        return $next($request);
    }
}