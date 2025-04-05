<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Admin;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * Đăng ký người dùng (User)
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:15',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'message' => 'Đăng ký thành công',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'tuoi' => $user->tuoi,
            ],
            'token' => $token
        ], 201);
    }

    /**
     * Đăng nhập người dùng (User) qua API
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return response()->json(['message' => 'Email hoặc mật khẩu không đúng'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'tuoi' => $user->tuoi,
            ],
            'token' => $token
        ], 200);
    }

    /**
     * Đăng nhập Admin hoặc Nhân viên qua web (sử dụng session)
     */
    public function loginAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userNameAD' => 'required|string',
            'passwordAD' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Vui lòng nhập đầy đủ thông tin.');
        }

        // Kiểm tra trong bảng admins trước
        $admin = Admin::where('userNameAD', $request->userNameAD)->first();
        if ($admin && Hash::check($request->passwordAD, $admin->passwordAD)) {
            session([
                'logged_in' => true,
                'role' => 'admin',
                'user_id' => $admin->id,
                'username' => $admin->userNameAD,
                'name' => $admin->userNameAD,
            ]);
            return redirect()->route('admin.dashboard')->with('success', 'Đăng nhập thành công với vai trò Admin');
        }

        // Kiểm tra trong bảng employees
        $employee = Employee::where('tenTaiKhoan', $request->userNameAD)->first();
        if ($employee && Hash::check($request->passwordAD, $employee->matKhau)) {
            if ($employee->trangThai !== 'active') {
                return redirect()->back()->with('error', 'Tài khoản nhân viên không hoạt động.');
            }
            session([
                'logged_in' => true,
                'role' => 'employee',
                'user_id' => $employee->id_nhanVien,
                'username' => $employee->tenTaiKhoan,
                'name' => $employee->tenNhanVien,
            ]);
            return redirect()->route('admin.dashboard')->with('success', 'Đăng nhập thành công với vai trò Nhân viên');
        }

        return redirect()->back()->with('error', 'Sai tài khoản hoặc mật khẩu');
    }

    /**
     * Đăng xuất Admin hoặc Nhân viên
     */
    public function logoutAdmin()
    {
        session()->forget(['logged_in', 'role', 'user_id', 'username', 'name']);
        return redirect('/login')->with('success', 'Đăng xuất thành công');
    }

    /**
     * Đăng xuất người dùng (User) qua API
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Đăng xuất thành công'], 200);
    }

    /**
     * Lấy thông tin người dùng hiện tại (User) qua API
     */
    public function getUser(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'message' => 'Thông tin người dùng',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'tuoi' => $user->tuoi,
            ]
        ], 200);
    }

    /**
     * Cập nhật thông tin người dùng (User) qua API
     */
    public function updateUser(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:15',
            'tuoi' => 'nullable|integer|min:0',
            'old_password' => 'required_with:password|string|min:6',
            'password' => 'sometimes|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        if ($request->has('password')) {
            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json(['error' => ['old_password' => 'Mật khẩu cũ không đúng']], 400);
            }
            $user->password = Hash::make($request->password);
        }

        if ($request->has('name')) $user->name = $request->name;
        if ($request->has('phone')) $user->phone = $request->phone;
        if ($request->has('tuoi')) $user->tuoi = $request->tuoi;

        $user->save();

        return response()->json([
            'message' => 'Cập nhật thông tin người dùng thành công',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'tuoi' => $user->tuoi,
            ],
        ], 200);
    }

    /**
     * Lấy danh sách tất cả người dùng (User) qua API
     */
    public function getUsers()
    {
        $users = User::all();
        return response()->json([
            'message' => 'Danh sách người dùng',
            'users' => $users->makeHidden(['password'])
        ], 200);
    }

    /**
     * Lấy danh sách tất cả Admin qua API
     */
    public function getAdmins()
    {
        $admins = Admin::all();
        return response()->json([
            'message' => 'Danh sách admin',
            'admins' => $admins->makeHidden(['passwordAD'])
        ], 200);
    }
}