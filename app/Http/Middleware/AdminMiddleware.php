<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Kiểm tra nếu admin chưa đăng nhập hoặc role không phải admin
        if (!session()->has('admin_logged_in') || session('roleID') !== 1) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập Admin.');
        }

        return $next($request);
    }
}
