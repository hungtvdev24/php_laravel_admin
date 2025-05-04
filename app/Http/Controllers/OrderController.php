<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DonHang;
use App\Models\ChiTietDonHang;
use App\Models\ThanhToan;
use App\Models\GioHang;
use App\Models\MucGioHang;
use App\Models\DiaChi;
use App\Models\Review;
use App\Models\ProductVariation;
use App\Models\Product;
use App\Models\Voucher;
use App\Models\UserVoucherUsage;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\VNPayController;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        $request->validate([
            'id_diaChi' => 'required|exists:diaChi,id_diaChi',
            'phuongThucThanhToan' => 'required|in:COD,VN_PAY',
            'items' => 'required|array|min:1',
            'items.*.id_mucGioHang' => 'required|exists:mucGioHang,id_mucGioHang',
            'voucher_code' => 'nullable|string',
        ]);

        $user = auth()->user();
        $gioHang = GioHang::where('id_nguoiDung', $user->id)->first();
        if (!$gioHang) {
            return response()->json(['message' => 'Giỏ hàng không tồn tại'], 400);
        }

        $selectedItemsData = $request->input('items');
        $tongTien = 0;
        $selectedMucGioHangs = collect();

        foreach ($selectedItemsData as $itemData) {
            $muc = MucGioHang::where('id_mucGioHang', $itemData['id_mucGioHang'])
                ->where('id_gioHang', $gioHang->id_gioHang)
                ->first();

            if (!$muc) {
                return response()->json([
                    'message' => "Mục giỏ hàng #{$itemData['id_mucGioHang']} không hợp lệ hoặc không thuộc về bạn."
                ], 400);
            }

            if ($muc->variation_id) {
                $variation = ProductVariation::find($muc->variation_id);
                if (!$variation || $variation->stock < $muc->soLuong) {
                    return response()->json(['message' => "Sản phẩm {$muc->id_sanPham} không đủ hàng"], 400);
                }
            } else {
                $product = Product::find($muc->id_sanPham);
                if (!$product || $product->trangThai !== 'active') {
                    return response()->json(['message' => "Sản phẩm {$muc->id_sanPham} không khả dụng"], 400);
                }
            }

            $tongTien += $muc->gia * $muc->soLuong;
            $selectedMucGioHangs->push($muc);
        }

        $diaChi = DiaChi::findOrFail($request->id_diaChi);

        // Áp dụng voucher nếu có
        $discountAmount = 0;
        $voucher = null;
        if ($request->voucher_code) {
            $voucher = Voucher::where('code', $request->voucher_code)->first();
            if (!$voucher) {
                return response()->json(['message' => 'Voucher không tồn tại'], 404);
            }

            if ($voucher->status != 'active') {
                return response()->json(['message' => 'Voucher không hoạt động'], 400);
            }

            if ($voucher->start_date && $voucher->start_date > now()) {
                return response()->json(['message' => 'Voucher chưa có hiệu lực'], 400);
            }
            if ($voucher->end_date && $voucher->end_date < now()) {
                return response()->json(['message' => 'Voucher đã hết hạn'], 400);
            }

            if ($voucher->usage_limit && $voucher->used_count >= $voucher->usage_limit) {
                return response()->json(['message' => 'Voucher đã hết lượt sử dụng'], 400);
            }

            if ($user->vouchers()->where('voucher_id', $voucher->id)->exists()) {
                return response()->json(['message' => 'Bạn đã sử dụng voucher này rồi'], 400);
            }

            if ($voucher->min_order_value && $tongTien < $voucher->min_order_value) {
                return response()->json(['message' => 'Đơn hàng không đạt giá trị tối thiểu để áp dụng voucher'], 400);
            }

            $discountAmount = $voucher->discount_type == 'fixed'
                ? $voucher->discount_value
                : min($voucher->max_discount ?? PHP_INT_MAX, $tongTien * $voucher->discount_value / 100);
        }

        $tongTienSauGiam = $tongTien - $discountAmount;

        // Chuẩn bị dữ liệu đơn hàng để lưu tạm (dùng cho VNPay)
        $orderData = [
            'id_nguoiDung' => $user->id,
            'ten_nguoiNhan' => $diaChi->ten_nguoiNhan,
            'sdt_nhanHang' => $diaChi->sdt_nhanHang,
            'ten_nha' => $diaChi->ten_nha,
            'tinh' => $diaChi->tinh,
            'huyen' => $diaChi->huyen,
            'xa' => $diaChi->xa,
            'tongTien' => $tongTienSauGiam,
            'phuongThucThanhToan' => $request->phuongThucThanhToan,
            'trangThaiDonHang' => 'cho_xac_nhan',
            'voucher_id' => $voucher ? $voucher->id : null,
            'discount_amount' => $discountAmount,
            'items' => $selectedMucGioHangs->map(function ($muc) {
                return [
                    'id_mucGioHang' => $muc->id_mucGioHang,
                    'id_sanPham' => $muc->id_sanPham,
                    'variation_id' => $muc->variation_id,
                    'soLuong' => $muc->soLuong,
                    'gia' => $muc->gia,
                ];
            })->toArray(),
            'message' => $request->message ?? '',
        ];

        if ($request->phuongThucThanhToan === 'VN_PAY') {
            // Với VNPay, không tạo đơn hàng ngay
            $vnPayController = new VNPayController();
            $amountToSend = round($tongTienSauGiam);

            // Mã hóa dữ liệu đơn hàng thành JSON và gửi qua vnp_OrderInfo
            $orderInfo = base64_encode(json_encode($orderData));
            Log::info("OrderController checkout: Gửi yêu cầu tới VNPay", [
                'order_id' => uniqid('temp_'),
                'amount' => $amountToSend,
                'orderInfo' => $orderInfo,
            ]);

            $response = $vnPayController->createPayment(new Request([
                'amount' => $amountToSend,
                'order_id' => uniqid('temp_'),
                'order_info' => $orderInfo,
            ]));

            if ($response->getStatusCode() === 200) {
                $responseData = $response->getData(true);
                if (isset($responseData['payment_url'])) {
                    return response()->json([
                        'message' => 'Yêu cầu thanh toán VNPay đã được tạo',
                        'qr_code' => $responseData['payment_url'],
                    ], 200);
                } else {
                    throw new \Exception('Không tìm thấy payment_url trong phản hồi từ VNPay');
                }
            } else {
                throw new \Exception('Không thể tạo URL thanh toán VNPay: ' . $response->getContent());
            }
        }

        // Với COD, tạo đơn hàng ngay
        DB::beginTransaction();
        try {
            $orderDataForCreation = [
                'id_nguoiDung' => $user->id,
                'ten_nguoiNhan' => $diaChi->ten_nguoiNhan,
                'sdt_nhanHang' => $diaChi->sdt_nhanHang,
                'ten_nha' => $diaChi->ten_nha,
                'tinh' => $diaChi->tinh,
                'huyen' => $diaChi->huyen,
                'xa' => $diaChi->xa,
                'tongTien' => $tongTienSauGiam,
                'phuongThucThanhToan' => $request->phuongThucThanhToan,
                'trangThaiDonHang' => 'cho_xac_nhan',
                'voucher_id' => $voucher ? $voucher->id : null,
                'discount_amount' => $discountAmount,
            ];
            $donHang = DonHang::create($orderDataForCreation);

            foreach ($selectedMucGioHangs as $muc) {
                ChiTietDonHang::create([
                    'id_donHang' => $donHang->id_donHang,
                    'id_sanPham' => $muc->id_sanPham,
                    'variation_id' => $muc->variation_id,
                    'soLuong' => $muc->soLuong,
                    'gia' => $muc->gia,
                ]);

                if ($muc->variation_id) {
                    $variation = ProductVariation::find($muc->variation_id);
                    $variation->stock -= $muc->soLuong;
                    $variation->save();
                }
            }

            $thanhToanData = [
                'id_donHang' => $donHang->id_donHang,
                'soTien' => $tongTienSauGiam,
                'phuongThucThanhToan' => $request->phuongThucThanhToan,
                'trangThaiThanhToan' => 'pending',
            ];

            ThanhToan::create($thanhToanData);

            OrderStatusHistory::create([
                'id_donHang' => $donHang->id_donHang,
                'trangThaiDonHang' => 'cho_xac_nhan',
                'ghiChu' => 'Đơn hàng được tạo bởi ' . $user->name . ($voucher ? " với voucher {$voucher->code}" : ''),
            ]);

            if ($voucher) {
                UserVoucherUsage::create([
                    'user_id' => $user->id,
                    'voucher_id' => $voucher->id,
                    'order_id' => $donHang->id_donHang,
                ]);
                $voucher->increment('used_count');
            }

            foreach ($selectedMucGioHangs as $muc) {
                $muc->delete();
            }

            DB::commit();
            return response()->json([
                'message' => 'Đặt hàng thành công',
                'donHang' => $donHang->load('chiTietDonHang.sanPham.variations'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($selectedMucGioHangs as $muc) {
                if ($muc->variation_id) {
                    $variation = ProductVariation::find($muc->variation_id);
                    if ($variation) {
                        $variation->stock += $muc->soLuong;
                        $variation->save();
                    }
                }
            }
            return response()->json([
                'message' => 'Đặt hàng thất bại',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function userOrders()
    {
        $user = auth()->user();
        $orders = DonHang::with(['chiTietDonHang.sanPham.variations.images', 'voucher'])
            ->where('id_nguoiDung', $user->id)
            ->orderBy('id_donHang', 'desc')
            ->get();

        $orders->each(function ($order) {
            $order->chiTietDonHang->each(function ($detail) {
                $variation = $detail->sanPham->variations->where('id', $detail->variation_id)->first();
                $detail->variation = $variation ? $variation->toArray() : null;
                $detail->image_url = $variation && $variation->images->isNotEmpty()
                    ? asset('storage/' . $variation->images[0]->image_url)
                    : asset('images/default.png');
            });
        });

        return response()->json(['orders' => $orders], 200);
    }

    public function showOrder($id)
    {
        $user = auth()->user();
        $order = DonHang::with(['chiTietDonHang.sanPham.variations.images', 'voucher'])
            ->where('id_donHang', $id)
            ->where('id_nguoiDung', $user->id)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Đơn hàng không tồn tại hoặc không thuộc về bạn'], 404);
        }

        $order->chiTietDonHang->each(function ($detail) use ($user) {
            $variation = $detail->sanPham->variations->where('id', $detail->variation_id)->first();
            $detail->variation = $variation ? $variation->toArray() : null;
            $detail->image_url = $variation && $variation->images->isNotEmpty()
                ? asset('storage/' . $variation->images[0]->image_url)
                : asset('images/default.png');
            $detail->review = Review::where('id_donHang', $detail->id_donHang)
                ->where('id_sanPham', $detail->id_sanPham)
                ->where('id_nguoiDung', $user->id)
                ->first();
        });

        return response()->json($order, 200);
    }

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

        DB::beginTransaction();
        try {
            $order->trangThaiDonHang = 'huy';
            $order->save();

            OrderStatusHistory::create([
                'id_donHang' => $order->id_donHang,
                'trangThaiDonHang' => 'huy',
                'ghiChu' => 'Đơn hàng bị hủy bởi người dùng ' . $user->name,
            ]);

            DB::commit();
            return response()->json(['message' => 'Đơn hàng đã được hủy'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Hủy đơn hàng thất bại: ' . $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        $query = DonHang::with(['chiTietDonHang.sanPham', 'voucher', 'user'])->orderBy('id_donHang', 'desc');

        $phone = $request->input('phone', '');
        $status = $request->input('status', 'all');
        $start_date = $request->input('start_date', '');
        $end_date = $request->input('end_date', '');

        if ($request->filled('phone')) {
            $query->where(function ($q) use ($request) {
                $q->where('sdt_nhanHang', 'LIKE', "%{$request->phone}%")
                  ->orWhereHas('user', function ($q2) use ($request) {
                      $q2->where('phone', 'LIKE', "%{$request->phone}%");
                  });
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('trangThaiDonHang', $request->status);
        }

        if ($start_date && $end_date) {
            try {
                $start = Carbon::createFromFormat('d/m/Y', $start_date)->startOfDay();
                $end = Carbon::createFromFormat('d/m/Y', $end_date)->endOfDay();
                $query->whereBetween('created_at', [$start, $end]);
            } catch (\Exception $e) {
                return redirect()->route('admin.orders.index')
                    ->with('error', 'Khoảng ngày không hợp lệ. Vui lòng nhập theo định dạng dd/mm/yyyy.')
                    ->withInput();
            }
        } elseif ($start_date) {
            try {
                $start = Carbon::createFromFormat('d/m/Y', $start_date)->startOfDay();
                $query->where('created_at', '>=', $start);
            } catch (\Exception $e) {
                return redirect()->route('admin.orders.index')
                    ->with('error', 'Ngày bắt đầu không hợp lệ.')
                    ->withInput();
            }
        } elseif ($end_date) {
            try {
                $end = Carbon::createFromFormat('d/m/Y', $end_date)->endOfDay();
                $query->where('created_at', '<=', $end);
            } catch (\Exception $e) {
                return redirect()->route('admin.orders.index')
                    ->with('error', 'Ngày kết thúc không hợp lệ.')
                    ->withInput();
            }
        }

        $orders = $query->paginate(10);
        $orders->appends([
            'phone' => $phone,
            'status' => $status,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ]);

        return view('admin.orders.index', compact('orders', 'phone', 'status', 'start_date', 'end_date'));
    }

    public function viewOrder($id)
    {
        $order = DonHang::with(['chiTietDonHang.sanPham', 'chiTietDonHang.variation', 'statusHistory', 'voucher', 'user'])
            ->findOrFail($id);
        return view('admin.orders.vieworder', compact('order'));
    }

    public function exportInvoice($id)
    {
        $order = DonHang::with(['chiTietDonHang.sanPham', 'chiTietDonHang.variation', 'voucher', 'user'])
            ->findOrFail($id);

        $data = [
            'order' => $order,
            'title' => 'Hóa đơn #' . $order->id_donHang,
            'date' => Carbon::now()->format('d/m/Y H:i:s'),
        ];

        $pdf = \PDF::loadView('admin.orders.invoice', $data);

        return $pdf->download('hoa_don_' . $order->id_donHang . '.pdf');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'trangThaiDonHang' => 'required|in:cho_xac_nhan,dang_giao,da_giao,huy',
        ]);

        $order = DonHang::findOrFail($id);
        $oldStatus = $order->trangThaiDonHang;
        $newStatus = $request->trangThaiDonHang;

        if (auth()->user()->role === 'employee') {
            if ($oldStatus === 'cho_xac_nhan' && $newStatus !== 'dang_giao') {
                return response()->json(['message' => 'Nhân viên chỉ có thể chuyển trạng thái từ "Chờ xác nhận" sang "Đang giao".'], 403);
            }
        } else {
            if ($oldStatus === 'cho_xac_nhan' && !in_array($newStatus, ['dang_giao', 'huy'])) {
                return response()->json(['message' => 'Trạng thái không hợp lệ.'], 400);
            }
            if ($oldStatus === 'dang_giao' && $newStatus !== 'da_giao') {
                return response()->json(['message' => 'Trạng thái không hợp lệ.'], 400);
            }
        }

        DB::beginTransaction();
        try {
            if ($oldStatus === 'cho_xac_nhan' && $newStatus === 'dang_giao') {
                if (!$order->ngay_du_kien_giao) {
                    $order->ngay_du_kien_giao = now()->addDays(2);
                }
            }
            if ($oldStatus === 'dang_giao' && $newStatus === 'da_giao') {
                if (!$order->ngay_giao_thuc_te) {
                    $order->ngay_giao_thuc_te = now();
                }
            }

            $order->trangThaiDonHang = $newStatus;
            $order->save();

            $userRole = auth()->user()->role === 'employee' ? 'Nhân viên' : 'Admin';
            OrderStatusHistory::create([
                'id_donHang' => $order->id_donHang,
                'trangThaiDonHang' => $newStatus,
                'ghiChu' => "Được cập nhật từ trạng thái $oldStatus bởi " . auth()->user()->name . " ($userRole)",
            ]);

            DB::commit();
            return response()->json(['message' => 'Cập nhật trạng thái đơn hàng thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Cập nhật thất bại: ' . $e->getMessage()], 500);
        }
    }

    public function updateDate(Request $request, $id)
    {
        $order = DonHang::findOrFail($id);

        if ($request->filled('ngay_du_kien_giao')) {
            try {
                $date = Carbon::createFromFormat('d/m/Y', $request->ngay_du_kien_giao);
                $order->ngay_du_kien_giao = $date;
            } catch (\Exception $e) {
                return redirect()->route('admin.orders.index')
                    ->with('error', 'Ngày dự kiến giao không hợp lệ. Vui lòng nhập theo định dạng dd/mm/yyyy.');
            }
        }

        if ($request->filled('ngay_giao_thuc_te')) {
            try {
                $date = Carbon::createFromFormat('d/m/Y', $request->ngay_giao_thuc_te);
                $order->ngay_giao_thuc_te = $date;
            } catch (\Exception $e) {
                return redirect()->route('admin.orders.index')
                    ->with('error', 'Ngày giao thực tế không hợp lệ. Vui lòng nhập theo định dạng dd/mm/yyyy.');
            }
        }

        $order->save();

        return redirect()->route('admin.orders.index')
            ->with('success', 'Cập nhật ngày thành công!');
    }

    public function adminCancelOrder($id)
    {
        $order = DonHang::findOrFail($id);

        if ($order->trangThaiDonHang !== 'cho_xac_nhan') {
            return redirect()->route('admin.orders.index')
                ->with('error', 'Chỉ đơn hàng chờ xác nhận mới có thể hủy!');
        }

        DB::beginTransaction();
        try {
            $order->trangThaiDonHang = 'huy';
            $order->save();

            OrderStatusHistory::create([
                'id_donHang' => $order->id_donHang,
                'trangThaiDonHang' => 'huy',
                'ghiChu' => 'Đơn hàng bị hủy bởi admin ' . auth()->user()->name,
            ]);

            DB::commit();
            return redirect()->route('admin.orders.index')
                ->with('success', 'Đã hủy đơn hàng #' . $id);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.orders.index')
                ->with('error', 'Hủy đơn hàng thất bại: ' . $e->getMessage());
        }
    }

    public function dashboard()
    {
        $deliveredCount = DonHang::where('trangThaiDonHang', 'da_giao')->count();
        $shippingCount = DonHang::where('trangThaiDonHang', 'dang_giao')->count();
        $pendingCount = DonHang::where('trangThaiDonHang', 'cho_xac_nhan')->count();
        $canceledCount = DonHang::where('trangThaiDonHang', 'huy')->count();
        $totalRevenue = DonHang::where('trangThaiDonHang', 'da_giao')->sum('tongTien');

        $chartLabels = ['Đã giao', 'Đang giao', 'Chờ xác nhận', 'Đã hủy'];
        $chartData = [$deliveredCount, $shippingCount, $pendingCount, $canceledCount];

        return view('admin.dashboard', compact('deliveredCount', 'shippingCount', 'pendingCount', 'canceledCount', 'totalRevenue', 'chartLabels', 'chartData'));
    }
}