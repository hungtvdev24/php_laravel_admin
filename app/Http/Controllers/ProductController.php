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
        $this->middleware('auth:sanctum')->only(['addToCart', 'getCart']);
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

        return view('admin.products.index', compact('products'))->with('search', $request->search);
    }

    /**
     * Hiển thị form tạo sản phẩm mới (dành cho admin)
     */
    public function create()
    {
        $categories = DanhMuc::all();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Lưu sản phẩm mới vào cơ sở dữ liệu
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_danhMuc' => 'required|exists:danhMuc,id_danhMuc',
            'tenSanPham' => 'required|string|max:255',
            'thuongHieu' => 'required|string|max:255',
            'gia' => 'required|numeric|min:0|max:99999999.99',
            'urlHinhAnh' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'moTa' => 'nullable|string|max:1000',
            'trangThai' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $imagePath = $request->hasFile('urlHinhAnh')
            ? $request->file('urlHinhAnh')->store('products', 'public')
            : null;

        Product::create([
            'id_danhMuc' => $request->id_danhMuc,
            'tenSanPham' => $request->tenSanPham,
            'thuongHieu' => $request->thuongHieu,
            'gia' => $request->gia,
            'urlHinhAnh' => $imagePath,
            'moTa' => $request->moTa,
            'trangThai' => $request->trangThai,
            'soLuongBan' => 0,
            'soSaoDanhGia' => 0,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được thêm thành công!');
    }

    /**
     * Hiển thị form chỉnh sửa sản phẩm (dành cho admin)
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = DanhMuc::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Cập nhật thông tin sản phẩm
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'id_danhMuc' => 'required|exists:danhMuc,id_danhMuc',
            'tenSanPham' => 'required|string|max:255',
            'thuongHieu' => 'required|string|max:255',
            'gia' => 'required|numeric|min:0|max:99999999.99',
            'urlHinhAnh' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'moTa' => 'nullable|string|max:1000',
            'trangThai' => 'required|in:active,inactive',
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
            'id_danhMuc' => $request->id_danhMuc,
            'tenSanPham' => $request->tenSanPham,
            'thuongHieu' => $request->thuongHieu,
            'gia' => $request->gia,
            'urlHinhAnh' => $imagePath,
            'moTa' => $request->moTa,
            'trangThai' => $request->trangThai,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được cập nhật thành công!');
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

        return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được xóa thành công!');
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
            Log::info('Fetched products: ' . json_encode($popularProducts->toArray()));

            if ($popularProducts->isEmpty()) {
                Log::info('No active products found');
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

            Log::info('Returning products: ' . json_encode($productsArray));
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
            Log::info('addToCart request received for product ID: ' . $idSanPham . ', Request: ' . json_encode($request->all()));

            // Xác thực người dùng từ Sanctum
            $user = $request->user();
            if (!$user) {
                Log::warning('Unauthorized access to addToCart');
                return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
            }

            // Lấy thông tin sản phẩm
            $product = Product::find($idSanPham);
            if (!$product || $product->trangThai !== 'active') {
                Log::warning('Product not found or inactive: ' . $idSanPham);
                return response()->json(['error' => 'Sản phẩm không tồn tại hoặc không hoạt động'], 404);
            }

            // Lấy số lượng từ request (mặc định là 1 nếu không có)
            $soLuong = $request->input('soLuong', 1);
            $validator = Validator::make(['soLuong' => $soLuong], [
                'soLuong' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed for soLuong: ' . json_encode($validator->errors()));
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Tìm hoặc tạo giỏ hàng cho người dùng
            $gioHang = GioHang::firstOrCreate(
                ['id_nguoiDung' => $user->id],
                ['id_nguoiDung' => $user->id]
            );

            // Kiểm tra xem sản phẩm đã có trong giỏ chưa
            $mucGioHang = MucGioHang::where('id_gioHang', $gioHang->id_gioHang)
                                   ->where('id_sanPham', $idSanPham)
                                   ->first();

            if ($mucGioHang) {
                // Nếu đã có, cập nhật số lượng
                $newQuantity = $mucGioHang->soLuong + $soLuong;
                $mucGioHang->update([
                    'soLuong' => $newQuantity,
                    'gia' => $product->gia, // Cập nhật giá mới từ sản phẩm
                ]);
                Log::info('Updated existing cart item, new quantity: ' . $newQuantity);
            } else {
                // Nếu chưa có, tạo mới
                $mucGioHang = MucGioHang::create([
                    'id_gioHang' => $gioHang->id_gioHang,
                    'id_sanPham' => $idSanPham,
                    'soLuong' => $soLuong,
                    'gia' => $product->gia,
                ]);
                Log::info('Created new cart item for product: ' . $idSanPham);
            }

            // Load thông tin giỏ hàng để trả về
            $gioHang->load('mucGioHangs.product');
            Log::info('addToCart success, returning cart: ' . json_encode($gioHang));
            return response()->json([
                'message' => 'Sản phẩm đã được thêm vào giỏ hàng',
                'cart' => $gioHang
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in addToCart: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Lấy thông tin giỏ hàng (API cho Flutter)
     */
    public function getCart(Request $request)
    {
        try {
            Log::info('getCart request received');

            $user = $request->user();
            if (!$user) {
                Log::warning('Unauthorized access to getCart');
                return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
            }

            $gioHang = GioHang::where('id_nguoiDung', $user->id)->with('mucGioHangs.product')->first();
            if (!$gioHang) {
                Log::info('Cart not found, returning empty cart');
                return response()->json(['message' => 'Giỏ hàng trống', 'cart' => null], 200);
            }

            Log::info('Returning cart: ' . json_encode($gioHang));
            return response()->json(['message' => 'Lấy giỏ hàng thành công', 'cart' => $gioHang], 200);
        } catch (\Exception $e) {
            Log::error('Error in getCart: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }
}