<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\ThanhToan;
use Illuminate\Support\Facades\Log;

class VNPayController extends Controller
{
    public function createPayment(Request $request)
    {
        // Kiểm tra đầu vào
        $amount = $request->input('amount');
        $orderId = $request->input('order_id');

        if (!is_numeric($amount) || $amount <= 0) {
            Log::error("VNPay createPayment: Số tiền không hợp lệ", ['amount' => $amount]);
            return response()->json(['message' => 'Số tiền không hợp lệ'], 400);
        }
        if (empty($orderId)) {
            Log::error("VNPay createPayment: ID đơn hàng không hợp lệ", ['order_id' => $orderId]);
            return response()->json(['message' => 'ID đơn hàng không hợp lệ'], 400);
        }

        // Thông tin tài khoản test của VNPay
        $vnp_TmnCode = "D73JVM97"; // Cập nhật từ email VNPay
        $vnp_HashSecret = "AO5JJDHBTIL40AT7O45DBXK3SJFKTF04"; // Cập nhật từ email VNPay
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_ReturnUrl = config('app.url') . '/api/vnpay/return';

        $vnp_TxnRef = (string)$orderId; // Đảm bảo là chuỗi
        $vnp_OrderInfo = "Thanh toan don hang #{$orderId}";
        $vnp_OrderType = "billpayment";
        $vnp_Amount = (int)($amount * 100); // Đảm bảo số tiền là số nguyên
        $vnp_Locale = "vn";
        $vnp_IpAddr = $request->ip() ?? '127.0.0.1'; // Giá trị mặc định nếu không lấy được IP
        $vnp_CreateDate = Carbon::now('Asia/Ho_Chi_Minh')->format('YmdHis');
        $vnp_ExpireDate = Carbon::now('Asia/Ho_Chi_Minh')->addMinutes(15)->format('YmdHis');

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => $vnp_CreateDate,
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate" => $vnp_ExpireDate,
        ];

        // Sắp xếp tham số theo thứ tự bảng chữ cái
        ksort($inputData);

        $query = "";
        $hashdata = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                $query .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $query .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        Log::info("VNPay createPayment: Tham số", [
            'inputData' => $inputData,
            'hashdata' => $hashdata,
            'query' => $query,
        ]);

        $vnp_Url = $vnp_Url . "?" . $query;
        $vnpSecureHash = hash_hmac("sha512", $hashdata, $vnp_HashSecret);
        $vnp_Url .= '&vnp_SecureHash=' . $vnpSecureHash;

        Log::info("VNPay createPayment: URL cuối cùng", ['url' => $vnp_Url]);

        return response()->json([
            'payment_url' => $vnp_Url,
        ], 200);
    }

    public function returnPayment(Request $request)
    {
        $vnp_HashSecret = "AO5JJDHBTIL40AT7O45DBXK3SJFKTF04"; // Cập nhật từ email VNPay
        $vnp_SecureHash = $request->input('vnp_SecureHash');
        $inputData = $request->except('vnp_SecureHash');

        ksort($inputData);
        $hashData = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac("sha512", $hashData, $vnp_HashSecret);
        if ($secureHash === $vnp_SecureHash) {
            $responseCode = $request->input('vnp_ResponseCode');
            if ($responseCode == '00') {
                $orderId = $request->input('vnp_TxnRef');
                $thanhToan = ThanhToan::where('id_donHang', $orderId)->first();
                if ($thanhToan) {
                    $thanhToan->trangThaiThanhToan = 'success';
                    $thanhToan->save();
                    return response()->json(['message' => 'Thanh toán thành công'], 200);
                }
                return response()->json(['message' => 'Không tìm thấy thông tin thanh toán'], 400);
            } else {
                Log::error("VNPay returnPayment: Thanh toán thất bại", ['responseCode' => $responseCode]);
                return response()->json(['message' => 'Thanh toán thất bại', 'errorCode' => $responseCode], 400);
            }
        } else {
            Log::error("VNPay returnPayment: Chữ ký không hợp lệ", ['secureHash' => $secureHash, 'vnp_SecureHash' => $vnp_SecureHash]);
            return response()->json(['message' => 'Chữ ký không hợp lệ'], 400);
        }
    }
}