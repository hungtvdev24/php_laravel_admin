<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DiaChi;

class AddressController extends Controller
{
    // 1. Lấy danh sách địa chỉ của người dùng
    public function index()
    {
        // Lấy user hiện đang đăng nhập (nếu bạn dùng Sanctum)
        $user = auth()->user();

        // Lấy tất cả địa chỉ của user này
        $addresses = DiaChi::where('id_nguoiDung', $user->id)->get();

        return response()->json($addresses);
    }

    // 2. Tạo mới một địa chỉ
    public function store(Request $request)
    {
        // Kiểm tra dữ liệu
        $validatedData = $request->validate([
            'sdt_nhanHang'  => 'required',
            'ten_nguoiNhan' => 'required',
            'ten_nha'       => 'required',
            'tinh'          => 'required',
            'huyen'         => 'required',
            'xa'            => 'required',
        ]);

        // Lấy user hiện đang đăng nhập
        $user = auth()->user();

        // Tạo địa chỉ mới
        $diaChi = new DiaChi($validatedData);
        $diaChi->id_nguoiDung = $user->id;  // Gán ID người dùng
        $diaChi->save();

        return response()->json([
            'message' => 'Đã tạo địa chỉ thành công',
            'data'    => $diaChi
        ], 201);
    }

    // 3. Xem chi tiết một địa chỉ (nếu cần)
    public function show($id_diaChi)
    {
        $user = auth()->user();

        // Tìm địa chỉ theo id và phải thuộc về user hiện tại
        $diaChi = DiaChi::where('id_diaChi', $id_diaChi)
                        ->where('id_nguoiDung', $user->id)
                        ->firstOrFail();

        return response()->json($diaChi);
    }

    // 4. Cập nhật địa chỉ
    public function update(Request $request, $id_diaChi)
    {
        $validatedData = $request->validate([
            'sdt_nhanHang'  => 'required',
            'ten_nguoiNhan' => 'required',
            'ten_nha'       => 'required',
            'tinh'          => 'required',
            'huyen'         => 'required',
            'xa'            => 'required',
        ]);

        $user = auth()->user();

        // Chỉ cho phép cập nhật địa chỉ của chính user
        $diaChi = DiaChi::where('id_diaChi', $id_diaChi)
                        ->where('id_nguoiDung', $user->id)
                        ->firstOrFail();

        // Cập nhật thông tin
        $diaChi->update($validatedData);

        return response()->json([
            'message' => 'Đã cập nhật địa chỉ thành công',
            'data'    => $diaChi
        ]);
    }

    // 5. Xóa địa chỉ
    public function destroy($id_diaChi)
    {
        $user = auth()->user();

        $diaChi = DiaChi::where('id_diaChi', $id_diaChi)
                        ->where('id_nguoiDung', $user->id)
                        ->firstOrFail();

        $diaChi->delete();

        return response()->json([
            'message' => 'Đã xóa địa chỉ thành công'
        ]);
    }
}
