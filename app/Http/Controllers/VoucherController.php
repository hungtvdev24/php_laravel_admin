<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VoucherController extends Controller
{
    // Hiển thị danh sách voucher (index)
    public function indexAdmin()
    {
        Log::info('indexAdmin method called');
        $vouchers = Voucher::latest()->paginate(10);
        return view('admin.affiliate.vouchers.index', compact('vouchers'));
    }

    // Hiển thị form tạo voucher mới (create)
    public function createAdmin()
    {
        Log::info('createAdmin method called');
        return view('admin.affiliate.vouchers.create');
    }

    // Xử lý tạo voucher mới (store từ form)
    public function storeAdmin(Request $request)
    {
        Log::info('storeAdmin method called', $request->all());
        $request->validate([
            'code' => 'required|string|max:255|unique:vouchers',
            'discount_value' => 'required|numeric|min:0',
            'discount_type' => 'required|in:fixed,percentage',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive,expired',
        ]);

        Voucher::create([
            'code' => $request->code,
            'discount_value' => $request->discount_value,
            'discount_type' => $request->discount_type,
            'min_order_value' => $request->min_order_value,
            'max_discount' => $request->max_discount,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'usage_limit' => $request->usage_limit,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.affiliate.vouchers.index')->with('success', 'Voucher đã được tạo thành công!');
    }

    // Hiển thị chi tiết voucher (show)
    public function showAdmin($id)
    {
        Log::info('showAdmin method called', ['id' => $id]);
        $voucher = Voucher::with('users')->findOrFail($id);
        return view('admin.affiliate.vouchers.show', compact('voucher'));
    }

    // Cập nhật trạng thái voucher
    public function updateStatus(Request $request, $id)
    {
        Log::info('updateStatus method called', ['id' => $id, 'status' => $request->status]);
        $request->validate([
            'status' => 'required|in:active,inactive,expired',
        ]);

        $voucher = Voucher::findOrFail($id);
        $voucher->status = $request->status;
        $voucher->save();

        return redirect()->route('admin.affiliate.vouchers.index')->with('success', 'Trạng thái voucher đã được cập nhật!');
    }

    // Xóa voucher
    public function destroyAdmin($id)
    {
        Log::info('destroyAdmin method called', ['id' => $id]);
        $voucher = Voucher::findOrFail($id);
        $voucher->delete();

        return redirect()->route('admin.affiliate.vouchers.index')->with('success', 'Voucher đã được xóa thành công!');
    }

    // API: Xem danh sách voucher của người dùng
    public function index(Request $request)
    {
        Log::info('index API method called');
        $user = $request->user();
        $vouchers = Voucher::where('status', 'active')
                           ->where(function ($query) {
                               $query->whereNull('start_date')
                                     ->orWhere('start_date', '<=', now());
                           })
                           ->where(function ($query) {
                               $query->whereNull('end_date')
                                     ->orWhere('end_date', '>=', now());
                           })
                           ->get();

        $vouchers->each(function ($voucher) use ($user) {
            $voucher->is_used = $user->vouchers()->where('voucher_id', $voucher->id)->exists();
        });

        return response()->json($vouchers);
    }
}