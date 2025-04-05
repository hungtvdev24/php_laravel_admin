<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!session('logged_in')) {
            return redirect('/login')->with('error', 'Vui lòng đăng nhập.');
        }

        if (session('role') !== $role) {
            return redirect()->route('admin.dashboard')->with('error', 'Bạn không có quyền truy cập chức năng này.');
        }

        return $next($request);
    }
}