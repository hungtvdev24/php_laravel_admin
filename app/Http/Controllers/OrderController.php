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
     * (User) Xử lý đặt hàng (checkout).
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'id_diaChi'           => 'required|exists:diaChi,id_diaChi',
            'phuongThucThanhToan' => 'required|in:COD,VN_PAY',
            'items'               => 'required|array|min:1'
        ]);

        $user = auth()->user();
        $gioHang = GioHang::where('id_nguoiDung', $user->id)->first();
        if (!$gioHang) {
            return response()->json(['message' => 'Giỏ hàng không tồn tại'], 400);
        }

        $selectedItemsData = $request->input('items');
        if (empty($selectedItemsData)) {
            return response()->json(['message' => 'Không có sản phẩm được chọn'], 400);
        }

        $tongTien = 0;
        $selectedMucGioHangs = collect();

        foreach ($selectedItemsData as $itemData) {
            if (!isset($itemData['id_mucGioHang'])) {
                return response()->json(['message' => 'Thiếu id_mucGioHang trong item'], 400);
            }

            $muc = MucGioHang::where('id_mucGioHang', $itemData['id_mucGioHang'])
                ->where('id_gioHang', $gioHang->id_gioHang)
                ->first();

            if (!$muc) {
                return response()->json([
                    'message' => "Mục giỏ hàng #{$itemData['id_mucGioHang']} không hợp lệ hoặc không thuộc về bạn."
                ], 400);
            }

            $tongTien += $muc->gia * $muc->soLuong;
            $selectedMucGioHangs->push($muc);
        }

        $diaChi = DiaChi::findOrFail($request->id_diaChi);

        DB::beginTransaction();
        try {
            // Tạo đơn hàng
            $donHang = DonHang::create([
                'id_nguoiDung'         => $user->id,
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

            // Tạo chi tiết đơn hàng
            foreach ($selectedMucGioHangs as $muc) {
                ChiTietDonHang::create([
                    'id_donHang' => $donHang->id_donHang,
                    'id_sanPham' => $muc->id_sanPham,
                    'soLuong'    => $muc->soLuong,
                    'gia'        => $muc->gia,
                ]);
            }

            // Tạo thanh toán
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

            // Xóa các mục giỏ hàng đã đặt
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
     * (User) Lấy danh sách đơn hàng của người dùng.
     */
    public function userOrders()
    {
        $user = auth()->user();
        $orders = DonHang::with(['chiTietDonHang.sanPham'])
            ->where('id_nguoiDung', $user->id)
            ->orderBy('id_donHang', 'desc')
            ->get();

        return response()->json([
            'orders' => $orders
        ], 200);
    }

    /**
     * (User) Xem chi tiết một đơn hàng của người dùng.
     */
    public function showOrder($id)
    {
        $user = auth()->user();
        $order = DonHang::with(['chiTietDonHang.sanPham'])
            ->where('id_donHang', $id)
            ->where('id_nguoiDung', $user->id)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Đơn hàng không tồn tại hoặc không thuộc về bạn'], 404);
        }

        return response()->json($order, 200);
    }

    /**
     * (User) Hủy đơn hàng nếu đơn hàng ở trạng thái "cho_xac_nhan".
     */
    public function cancelOrder($id)
    {
        $user = auth()->user();
        $order = DonHang::where('id_donHang', $id)
            ->where('id_nguoiDung', $user->id)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Đơn hàng không tồn tại'], 404);
        }

        if ($order->trangThaiDonHang !== 'cho_xac_nhan') {
            return response()->json(['message' => 'Chỉ đơn hàng ở trạng thái chờ xác nhận mới có thể hủy'], 400);
        }

        $order->trangThaiDonHang = 'huy';
        $order->save();

        return response()->json(['message' => 'Đơn hàng đã được hủy'], 200);
    }

    /**
     * (Admin) Hiển thị danh sách đơn hàng (trang quản trị) + tìm & lọc.
     */
    public function index(Request $request)
    {
        $query = DonHang::orderBy('id_donHang', 'desc');

        // Tìm theo SĐT
        if ($request->filled('phone')) {
            $phone = $request->phone;
            $query->where('sdt_nhanHang', 'LIKE', "%{$phone}%");
        }

        // Lọc theo trạng thái
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('trangThaiDonHang', $request->status);
        }

        $orders = $query->paginate(10);

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * (Admin) Hiển thị chi tiết một đơn hàng.
     */
    public function viewOrder($id)
    {
        $order = DonHang::with(['chiTietDonHang.sanPham'])->findOrFail($id);
        return view('admin.orders.vieworder', compact('order'));
    }

    /**
     * (Admin) Cập nhật trạng thái đơn hàng + ngày giao.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'trangThaiDonHang' => 'required|in:cho_xac_nhan,dang_giao,da_giao,huy',
        ]);

        $order = DonHang::findOrFail($id);
        $oldStatus = $order->trangThaiDonHang;
        $newStatus = $request->trangThaiDonHang;

        // Nếu chuyển từ "cho_xac_nhan" -> "dang_giao"
        if ($oldStatus === 'cho_xac_nhan' && $newStatus === 'dang_giao') {
            $order->ngay_du_kien_giao = now()->addDays(2);
        }
        // Nếu chuyển "dang_giao" -> "da_giao"
        if ($oldStatus === 'dang_giao' && $newStatus === 'da_giao') {
            $order->ngay_giao_thuc_te = now();
        }

        // Nếu admin chọn "huy"
        if ($newStatus === 'huy' && $oldStatus === 'cho_xac_nhan') {
            $order->trangThaiDonHang = 'huy';
        } else {
            $order->trangThaiDonHang = $newStatus;
        }

        $order->save();

        return redirect()->route('admin.orders.index')
            ->with('success', 'Cập nhật trạng thái đơn hàng thành công!');
    }

    /**
     * (Admin) Hủy đơn hàng (nếu muốn tách route admin riêng).
     */
    public function adminCancelOrder($id)
    {
        $order = DonHang::findOrFail($id);

        if ($order->trangThaiDonHang !== 'cho_xac_nhan') {
            return redirect()->route('admin.orders.index')
                ->with('error', 'Chỉ đơn hàng chờ xác nhận mới có thể hủy!');
        }

        $order->trangThaiDonHang = 'huy';
        $order->save();

        return redirect()->route('admin.orders.index')
            ->with('success', 'Đã hủy đơn hàng #' . $id);
    }
}
