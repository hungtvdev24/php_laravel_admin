<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DonHang;
use App\Models\ChiTietDonHang;
use App\Models\ThanhToan;
use App\Models\GioHang;
use App\Models\MucGioHang;
use App\Models\DiaChi;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Xử lý quá trình đặt hàng (checkout) dựa trên danh sách sản phẩm đã chọn.
     */
    public function checkout(Request $request)
    {
        // Validate thông tin đầu vào, bao gồm cả danh sách items đã chọn
        $request->validate([
            'id_diaChi' => 'required|exists:diaChi,id_diaChi',
            'phuongThucThanhToan' => 'required|in:COD,VN_PAY',
            'items' => 'required|array|min:1'
        ]);

        $user = auth()->user();

        // Lấy giỏ hàng của người dùng
        $gioHang = GioHang::where('id_nguoiDung', $user->id)->first();
        if (!$gioHang) {
            return response()->json(['message' => 'Giỏ hàng không tồn tại'], 400);
        }

        // Lấy danh sách sản phẩm được chọn từ request
        $selectedItemsData = $request->input('items');  // Mảng các item client gửi

        // Kiểm tra danh sách đã gửi có rỗng hay không
        if (empty($selectedItemsData)) {
            return response()->json(['message' => 'Không có sản phẩm được chọn'], 400);
        }

        // Tính tổng tiền và lấy các mục giỏ hàng được chọn từ DB
        $tongTien = 0;
        $selectedMucGioHangs = collect();

        foreach ($selectedItemsData as $itemData) {
            if (!isset($itemData['id_mucGioHang'])) {
                return response()->json(['message' => 'Thiếu id_mucGioHang trong item'], 400);
            }

            // Chỉ lấy mục giỏ hàng thuộc về giỏ hàng của user
            $muc = MucGioHang::where('id_mucGioHang', $itemData['id_mucGioHang'])
                ->where('id_gioHang', $gioHang->id_gioHang)
                ->first();

            if (!$muc) {
                return response()->json([
                    'message' => "Mục giỏ hàng #{$itemData['id_mucGioHang']} không hợp lệ hoặc không thuộc về người dùng."
                ], 400);
            }

            $tongTien += $muc->gia * $muc->soLuong;
            $selectedMucGioHangs->push($muc);
        }

        // Lấy thông tin địa chỉ
        $diaChi = DiaChi::findOrFail($request->id_diaChi);

        DB::beginTransaction();
        try {
            // Tạo đơn hàng (snapshot thông tin địa chỉ)
            $donHang = DonHang::create([
                'id_nguoiDung'          => $user->id,
                'ten_nguoiNhan'        => $diaChi->ten_nguoiNhan,
                'sdt_nhanHang'         => $diaChi->sdt_nhanHang,
                'ten_nha'              => $diaChi->ten_nha,
                'tinh'                 => $diaChi->tinh,
                'huyen'                => $diaChi->huyen,
                'xa'                   => $diaChi->xa,
                'tongTien'             => $tongTien,
                'phuongThucThanhToan'  => $request->phuongThucThanhToan,
                'trangThaiDonHang'     => 'cho_xac_nhan'
            ]);

            // Tạo các dòng chi tiết đơn hàng chỉ cho các mục đã chọn
            foreach ($selectedMucGioHangs as $muc) {
                ChiTietDonHang::create([
                    'id_donHang' => $donHang->id_donHang,
                    'id_sanPham' => $muc->id_sanPham,
                    'soLuong'    => $muc->soLuong,
                    'gia'        => $muc->gia,
                ]);
            }

            // Tạo thông tin thanh toán
            $thanhToanData = [
                'id_donHang'          => $donHang->id_donHang,
                'soTien'              => $tongTien,
                'phuongThucThanhToan' => $request->phuongThucThanhToan,
                'trangThaiThanhToan'  => 'pending'
            ];
            if ($request->phuongThucThanhToan === 'VN_PAY') {
                $thanhToanData['qr_code'] = 'QR_CODE_SAMPLE';
            }
            ThanhToan::create($thanhToanData);

            // Xóa các mục giỏ hàng chỉ của các sản phẩm đã đặt
            foreach ($selectedMucGioHangs as $muc) {
                MucGioHang::where('id_mucGioHang', $muc->id_mucGioHang)
                    ->where('id_gioHang', $gioHang->id_gioHang)
                    ->delete();
            }

            DB::commit();
            return response()->json([
                'message' => 'Đặt hàng thành công',
                'donHang' => $donHang
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Đặt hàng thất bại',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * (Admin) Hiển thị danh sách đơn hàng (trang quản trị).
     */
    public function index()
    {
        $orders = DonHang::orderBy('id_donHang', 'desc')->paginate(10);
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * (Admin) Cập nhật trạng thái đơn hàng và ngày giao hàng.
     */
    public function update(Request $request, $id)
    {
        // Validate trạng thái mới
        $request->validate([
            'trangThaiDonHang' => 'required|in:cho_xac_nhan,dang_giao,da_giao',
        ]);

        $order = DonHang::findOrFail($id);
        $oldStatus = $order->trangThaiDonHang;
        $newStatus = $request->trangThaiDonHang;

        // Nếu chuyển từ "cho_xac_nhan" sang "dang_giao" => đặt ngày giao dự kiến (2 ngày sau)
        if ($oldStatus === 'cho_xac_nhan' && $newStatus === 'dang_giao') {
            $order->ngay_du_kien_giao = now()->addDays(2);
        }

        // Nếu chuyển từ "dang_giao" sang "da_giao" => đặt ngày giao thực tế = hiện tại
        if ($oldStatus === 'dang_giao' && $newStatus === 'da_giao') {
            $order->ngay_giao_thuc_te = now();
        }

        $order->trangThaiDonHang = $newStatus;
        $order->save();

        return redirect()->route('admin.orders.index')->with('success', 'Cập nhật trạng thái đơn hàng thành công!');
    }
}
