<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GioHang;
use App\Models\MucGioHang;
use App\Models\Product;
use App\Models\DanhMuc;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function __construct()
    {
        // Yêu cầu xác thực Sanctum cho các API liên quan đến giỏ hàng
        $this->middleware('auth:sanctum')->only([
            'addToCart', 'getCart',
            // Thêm 2 hàm dưới
            'updateCartItem', 'removeCartItem'
        ]);
    }

    /**
     * Hiển thị danh sách sản phẩm (dành cho admin)
     */
    public function index(Request $request)
    {
        $query = Product::with('danhMuc');

        if ($request->has('search') && !empty($request->search)) {
            $query->where('tenSanPham', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('id_sanPham', 'desc')->paginate(10);

        return view('admin.products.index', compact('products'))
            ->with('search', $request->search);
    }

    /**
     * Hiển thị form tạo sản phẩm mới (admin)
     */
    public function create()
    {
        $categories = DanhMuc::all();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Lưu sản phẩm mới
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_danhMuc'   => 'required|exists:danhMuc,id_danhMuc',
            'tenSanPham'   => 'required|string|max:255',
            'thuongHieu'   => 'required|string|max:255',
            'gia'          => 'required|numeric|min:0|max:99999999.99',
            'urlHinhAnh'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'moTa'         => 'nullable|string|max:1000',
            'trangThai'    => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $imagePath = $request->hasFile('urlHinhAnh')
            ? $request->file('urlHinhAnh')->store('products', 'public')
            : null;

        Product::create([
            'id_danhMuc'   => $request->id_danhMuc,
            'tenSanPham'   => $request->tenSanPham,
            'thuongHieu'   => $request->thuongHieu,
            'gia'          => $request->gia,
            'urlHinhAnh'   => $imagePath,
            'moTa'         => $request->moTa,
            'trangThai'    => $request->trangThai,
            'soLuongBan'   => 0,
            'soSaoDanhGia' => 0,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Sản phẩm đã được thêm thành công!');
    }

    /**
     * Hiển thị form chỉnh sửa sản phẩm
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = DanhMuc::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Cập nhật sản phẩm
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'id_danhMuc'   => 'required|exists:danhMuc,id_danhMuc',
            'tenSanPham'   => 'required|string|max:255',
            'thuongHieu'   => 'required|string|max:255',
            'gia'          => 'required|numeric|min:0|max:99999999.99',
            'urlHinhAnh'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'moTa'         => 'nullable|string|max:1000',
            'trangThai'    => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($request->hasFile('urlHinhAnh')) {
            if ($product->urlHinhAnh) {
                Storage::disk('public')->delete($product->urlHinhAnh);
            }
            $imagePath = $request->file('urlHinhAnh')->store('products', 'public');
        } else {
            $imagePath = $product->urlHinhAnh;
        }

        $product->update([
            'id_danhMuc'   => $request->id_danhMuc,
            'tenSanPham'   => $request->tenSanPham,
            'thuongHieu'   => $request->thuongHieu,
            'gia'          => $request->gia,
            'urlHinhAnh'   => $imagePath,
            'moTa'         => $request->moTa,
            'trangThai'    => $request->trangThai,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Sản phẩm đã được cập nhật thành công!');
    }

    /**
     * Xóa sản phẩm
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->urlHinhAnh) {
            Storage::disk('public')->delete($product->urlHinhAnh);
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Sản phẩm đã được xóa thành công!');
    }

    /**
     * Lấy danh sách sản phẩm phổ biến (API cho Flutter)
     */
    public function getPopularProducts()
    {
        try {
            Log::info('Fetching popular products');
            $popularProducts = Product::where('trangThai', 'active')
                ->orderBy('soLuongBan', 'desc')
                ->orderBy('id_sanPham', 'desc')
                ->take(10)
                ->get();

            Log::info('Fetched products count: ' . $popularProducts->count());

            if ($popularProducts->isEmpty()) {
                return response()->json(['message' => 'No active products found'], 200);
            }

            $productsArray = $popularProducts->map(function ($product) {
                $data = $product->toArray();
                $originalUrl = $product->getOriginal('urlHinhAnh');
                $data['urlHinhAnh'] = $originalUrl && Storage::disk('public')->exists($originalUrl)
                    ? asset('storage/' . $originalUrl)
                    : asset('images/default.png');
                return $data;
            })->all();

            return response()->json($productsArray);
        } catch (\Exception $e) {
            Log::error('Error in getPopularProducts: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Thêm sản phẩm vào giỏ hàng (API cho Flutter)
     */
    public function addToCart(Request $request, $idSanPham)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
            }

            $product = Product::find($idSanPham);
            if (!$product || $product->trangThai !== 'active') {
                return response()->json(['error' => 'Sản phẩm không tồn tại hoặc không hoạt động'], 404);
            }

            $soLuong = $request->input('soLuong', 1);
            $validator = Validator::make(['soLuong' => $soLuong], [
                'soLuong' => 'required|integer|min:1',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $gioHang = GioHang::firstOrCreate(
                ['id_nguoiDung' => $user->id],
                ['id_nguoiDung' => $user->id]
            );

            $mucGioHang = MucGioHang::where('id_gioHang', $gioHang->id_gioHang)
                                    ->where('id_sanPham', $idSanPham)
                                    ->first();

            if ($mucGioHang) {
                // Update số lượng
                $newQuantity = $mucGioHang->soLuong + $soLuong;
                $mucGioHang->update([
                    'soLuong' => $newQuantity,
                    'gia' => $product->gia,
                ]);
            } else {
                // Tạo mới
                $mucGioHang = MucGioHang::create([
                    'id_gioHang' => $gioHang->id_gioHang,
                    'id_sanPham' => $idSanPham,
                    'soLuong'    => $soLuong,
                    'gia'        => $product->gia,
                ]);
            }

            $gioHang->load('mucGioHangs.product');
            return response()->json([
                'message' => 'Sản phẩm đã được thêm vào giỏ hàng',
                'cart' => $gioHang
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Lấy thông tin giỏ hàng (API cho Flutter)
     */
    public function getCart(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
            }

            $gioHang = GioHang::where('id_nguoiDung', $user->id)
                              ->with('mucGioHangs.product')
                              ->first();

            if (!$gioHang) {
                return response()->json([
                    'message' => 'Giỏ hàng trống',
                    'cart' => null
                ], 200);
            }

            return response()->json([
                'message' => 'Lấy giỏ hàng thành công',
                'cart' => $gioHang
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * **(MỚI)** Cập nhật số lượng 1 mục giỏ hàng
     */
    public function updateCartItem(Request $request, $idMucGioHang)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
            }

            // Validate soLuong
            $request->validate([
                'soLuong' => 'required|integer|min:1'
            ]);

            // Tìm mucGioHang của user
            $muc = MucGioHang::where('id_mucGioHang', $idMucGioHang)
                ->whereHas('gioHang', function($q) use ($user) {
                    $q->where('id_nguoiDung', $user->id);
                })
                ->first();

            if (!$muc) {
                return response()->json(['error' => 'Không tìm thấy mục giỏ hàng hoặc không thuộc về bạn'], 404);
            }

            // Cập nhật số lượng
            $muc->soLuong = $request->soLuong;
            // Nếu muốn cập nhật lại giá => $muc->gia = ...
            $muc->save();

            return response()->json(['message' => 'Cập nhật thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Lỗi khi cập nhật số lượng: ' . $e->getMessage()], 500);
        }
    }

    /**
     * **(MỚI)** Xoá 1 mục giỏ hàng
     */
    public function removeCartItem(Request $request, $idMucGioHang)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
            }

            $muc = MucGioHang::where('id_mucGioHang', $idMucGioHang)
                ->whereHas('gioHang', function($q) use ($user) {
                    $q->where('id_nguoiDung', $user->id);
                })
                ->first();

            if (!$muc) {
                return response()->json(['error' => 'Không tìm thấy mục giỏ hàng hoặc không thuộc về bạn'], 404);
            }

            $muc->delete();
            return response()->json(['message' => 'Xóa thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Lỗi khi xóa mục: ' . $e->getMessage()], 500);
        }
    }
}
