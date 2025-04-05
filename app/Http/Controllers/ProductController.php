<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GioHang;
use App\Models\MucGioHang;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\ProductVariationImage;
use App\Models\DanhMuc;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only([
            'addToCart', 'getCart', 'updateCartItem', 'removeCartItem'
        ]);
        $this->middleware('role:admin')->only([
            'create', 'store', 'edit', 'update', 'destroy'
        ]);
    }

    public function index(Request $request)
    {
        $query = Product::with(['danhMuc', 'variations.images']);

        if ($request->has('search') && !empty($request->search)) {
            $query->where('tenSanPham', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('id_sanPham', 'desc')->paginate(10);

        return view('admin.products.index', compact('products'))
            ->with('search', $request->search);
    }

    public function create()
    {
        $categories = DanhMuc::all();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // Cập nhật validation: bỏ required cho trường gia
        $validator = Validator::make($request->all(), [
            'id_danhMuc'               => 'required|exists:danhMuc,id_danhMuc',
            'tenSanPham'               => 'required|string|max:255',
            'thuongHieu'               => 'required|string|max:255',
            'moTa'                     => 'required|string|max:1000',
            'trangThai'                => 'required|in:active,inactive',
            'gia'                      => 'nullable|numeric|min:0|max:99999999.99', // Không bắt buộc
            'variations'               => 'required|array|min:1',
            'variations.*.color'       => 'required|string|max:50',
            'variations.*.sizes'       => 'nullable|array',
            'variations.*.sizes.*'     => 'string|max:50',
            'variations.*.stocks'      => 'required|array|min:1',
            'variations.*.stocks.*'    => 'required|integer|min:0',
            'variations.*.prices'      => 'required|array|min:1',
            'variations.*.prices.*'    => 'required|numeric|min:0|max:99999999.99',
            'variations.*.images'      => 'required|array|min:1',
            'variations.*.images.*'    => 'required|image|mimes:jpg,jpeg,png|max:5120', // 5MB
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Tạo sản phẩm
        $productData = [
            'id_danhMuc'   => $request->id_danhMuc,
            'tenSanPham'   => $request->tenSanPham,
            'thuongHieu'   => $request->thuongHieu,
            'moTa'         => $request->moTa,
            'trangThai'    => $request->trangThai,
            'soLuongBan'   => 0,
            'soSaoDanhGia' => 0,
        ];

        // Nếu có giá mặc định thì thêm vào dữ liệu
        if ($request->filled('gia')) {
            $productData['gia'] = $request->gia;
        }

        $product = Product::create($productData);

        // Lặp qua từng biến thể
        foreach ($request->variations as $index => $variationData) {
            $sizes = !empty($variationData['sizes']) ? $variationData['sizes'] : [null];
            $prices = $variationData['prices'];
            $stocks = $variationData['stocks'];

            foreach ($sizes as $sizeIndex => $size) {
                $price = count($prices) === 1 ? $prices[0] : (isset($prices[$sizeIndex]) ? $prices[$sizeIndex] : $prices[0]);
                $stock = count($stocks) === 1 ? $stocks[0] : (isset($stocks[$sizeIndex]) ? $stocks[$sizeIndex] : $stocks[0]);

                $variation = ProductVariation::create([
                    'product_id' => $product->id_sanPham,
                    'color'      => $variationData['color'],
                    'size'       => $size,
                    'price'      => $price,
                    'stock'      => $stock,
                ]);

                if (isset($variationData['images']) && $request->hasFile("variations.{$index}.images")) {
                    foreach ($request->file("variations.{$index}.images") as $image) {
                        $imagePath = $image->store('variation_images', 'public');
                        ProductVariationImage::create([
                            'product_variation_id' => $variation->id,
                            'image_url' => $imagePath,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Sản phẩm và biến thể đã được thêm thành công!');
    }

    public function edit($id)
    {
        $product = Product::with('variations.images')->findOrFail($id);
        $categories = DanhMuc::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Cập nhật validation: bỏ required cho trường gia
        $validator = Validator::make($request->all(), [
            'id_danhMuc'               => 'required|exists:danhMuc,id_danhMuc',
            'tenSanPham'               => 'required|string|max:255',
            'thuongHieu'               => 'required|string|max:255',
            'moTa'                     => 'required|string|max:1000',
            'trangThai'                => 'required|in:active,inactive',
            'gia'                      => 'nullable|numeric|min:0|max:99999999.99', // Không bắt buộc
            'variations'               => 'required|array|min:1',
            'variations.*.id'          => 'nullable|exists:product_variations,id',
            'variations.*.color'       => 'required|string|max:50',
            'variations.*.sizes'       => 'nullable|array',
            'variations.*.sizes.*'     => 'string|max:50',
            'variations.*.stocks'      => 'required|array|min:1',
            'variations.*.stocks.*'    => 'required|integer|min:0',
            'variations.*.prices'      => 'required|array|min:1',
            'variations.*.prices.*'    => 'required|numeric|min:0|max:99999999.99',
            'variations.*.images'      => 'required|array|min:1',
            'variations.*.images.*'    => 'required|image|mimes:jpg,jpeg,png|max:5120', // 5MB
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $productData = [
            'id_danhMuc'   => $request->id_danhMuc,
            'tenSanPham'   => $request->tenSanPham,
            'thuongHieu'   => $request->thuongHieu,
            'moTa'         => $request->moTa,
            'trangThai'    => $request->trangThai,
        ];

        if ($request->filled('gia')) {
            $productData['gia'] = $request->gia;
        } else {
            $productData['gia'] = null; // Xóa giá mặc định nếu không có
        }

        $product->update($productData);

        $existingVariationIds = $product->variations->pluck('id')->toArray();
        $submittedVariationIds = array_filter(array_column($request->variations, 'id'));

        // Xóa các biến thể cũ không có trong dữ liệu mới
        foreach ($existingVariationIds as $variationId) {
            if (!in_array($variationId, $submittedVariationIds)) {
                $variation = ProductVariation::find($variationId);
                foreach ($variation->images as $image) {
                    Storage::disk('public')->delete($image->image_url);
                    $image->delete();
                }
                $variation->delete();
            }
        }

        // Xử lý cập nhật hoặc tạo mới các biến thể
        foreach ($request->variations as $index => $variationData) {
            $sizes = !empty($variationData['sizes']) ? $variationData['sizes'] : [null];
            $prices = $variationData['prices'];
            $stocks = $variationData['stocks'];

            if (isset($variationData['id']) && $variationData['id']) {
                $existingVariations = $product->variations->where('color', $variationData['color']);
                $existingSizes = $existingVariations->pluck('size')->toArray();
                $newSizes = $sizes;

                foreach ($existingVariations as $var) {
                    if (!in_array($var->size, $newSizes)) {
                        foreach ($var->images as $image) {
                            Storage::disk('public')->delete($image->image_url);
                            $image->delete();
                        }
                        $var->delete();
                    }
                }

                foreach ($sizes as $sizeIndex => $size) {
                    $existingVariation = $existingVariations->where('size', $size)->first();
                    $price = count($prices) === 1 ? $prices[0] : (isset($prices[$sizeIndex]) ? $prices[$sizeIndex] : $prices[0]);
                    $stock = count($stocks) === 1 ? $stocks[0] : (isset($stocks[$sizeIndex]) ? $stocks[$sizeIndex] : $stocks[0]);

                    if ($existingVariation) {
                        $existingVariation->update([
                            'color' => $variationData['color'],
                            'size'  => $size,
                            'price' => $price,
                            'stock' => $stock,
                        ]);
                    } else {
                        $newVariation = ProductVariation::create([
                            'product_id' => $product->id_sanPham,
                            'color'      => $variationData['color'],
                            'size'       => $size,
                            'price'      => $price,
                            'stock'      => $stock,
                        ]);

                        if (isset($variationData['images']) && $request->hasFile("variations.{$index}.images")) {
                            foreach ($request->file("variations.{$index}.images") as $image) {
                                $imagePath = $image->store('variation_images', 'public');
                                ProductVariationImage::create([
                                    'product_variation_id' => $newVariation->id,
                                    'image_url' => $imagePath,
                                ]);
                            }
                        }
                    }
                }
            } else {
                foreach ($sizes as $sizeIndex => $size) {
                    $price = count($prices) === 1 ? $prices[0] : (isset($prices[$sizeIndex]) ? $prices[$sizeIndex] : $prices[0]);
                    $stock = count($stocks) === 1 ? $stocks[0] : (isset($stocks[$sizeIndex]) ? $stocks[$sizeIndex] : $stocks[0]);

                    $variation = ProductVariation::create([
                        'product_id' => $product->id_sanPham,
                        'color'      => $variationData['color'],
                        'size'       => $size,
                        'price'      => $price,
                        'stock'      => $stock,
                    ]);

                    if (isset($variationData['images']) && $request->hasFile("variations.{$index}.images")) {
                        foreach ($request->file("variations.{$index}.images") as $image) {
                            $imagePath = $image->store('variation_images', 'public');
                            ProductVariationImage::create([
                                'product_variation_id' => $variation->id,
                                'image_url' => $imagePath,
                            ]);
                        }
                    }
                }
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Sản phẩm và biến thể đã được cập nhật thành công!');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        foreach ($product->variations as $variation) {
            foreach ($variation->images as $image) {
                Storage::disk('public')->delete($image->image_url);
                $image->delete();
            }
            $variation->delete();
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Sản phẩm đã được xóa thành công!');
    }

    public function search(Request $request)
    {
        try {
            $query = $request->query('query', '');

            if (empty($query)) {
                return response()->json(['products' => []], 200);
            }

            $products = Product::with('variations.images')
                ->where('tenSanPham', 'LIKE', "%{$query}%")
                ->where('trangThai', 'active')
                ->get()
                ->map(function ($product) {
                    $data = $product->toArray();
                    $data['urlHinhAnh'] = asset('images/default.png');
                    foreach ($data['variations'] as &$variation) {
                        foreach ($variation['images'] as &$image) {
                            $image['image_url'] = Storage::disk('public')->exists($image['image_url'])
                                ? asset('storage/' . $image['image_url'])
                                : asset('images/default.png');
                        }
                    }
                    return $data;
                });

            return response()->json(['products' => $products], 200);
        } catch (\Exception $e) {
            Log::error('Error in search: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function getPopularProducts()
    {
        try {
            $popularProducts = Product::with('variations.images')
                ->where('trangThai', 'active')
                ->orderBy('soLuongBan', 'desc')
                ->orderBy('id_sanPham', 'desc')
                ->take(10)
                ->get()
                ->map(function ($product) {
                    $data = $product->toArray();
                    $data['urlHinhAnh'] = asset('images/default.png');
                    foreach ($data['variations'] as &$variation) {
                        foreach ($variation['images'] as &$image) {
                            $image['image_url'] = Storage::disk('public')->exists($image['image_url'])
                                ? asset('storage/' . $image['image_url'])
                                : asset('images/default.png');
                        }
                    }
                    return $data;
                });

            return response()->json($popularProducts->isEmpty() ? ['message' => 'No active products found'] : $popularProducts);
        } catch (\Exception $e) {
            Log::error('Error in getPopularProducts: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

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

            $variationId = $request->input('variation_id');
            $soLuong = $request->input('soLuong', 1);

            $validator = Validator::make($request->all(), [
                'variation_id' => 'required|exists:product_variations,id',
                'soLuong'      => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $variation = ProductVariation::find($variationId);
            if (!$variation || $variation->product_id !== $product->id_sanPham || $variation->stock < $soLuong) {
                return response()->json(['error' => 'Biến thể không hợp lệ hoặc không đủ hàng'], 400);
            }

            $gioHang = GioHang::firstOrCreate(['id_nguoiDung' => $user->id]);
            $mucGioHang = MucGioHang::where('id_gioHang', $gioHang->id_gioHang)
                ->where('id_sanPham', $idSanPham)
                ->where('variation_id', $variationId)
                ->first();

            if ($mucGioHang) {
                $newQuantity = $mucGioHang->soLuong + $soLuong;
                if ($newQuantity > $variation->stock) {
                    return response()->json(['error' => 'Số lượng vượt quá tồn kho'], 400);
                }
                $mucGioHang->update(['soLuong' => $newQuantity, 'gia' => $variation->price]);
            } else {
                MucGioHang::create([
                    'id_gioHang'   => $gioHang->id_gioHang,
                    'id_sanPham'   => $idSanPham,
                    'variation_id' => $variationId,
                    'soLuong'      => $soLuong,
                    'gia'          => $variation->price,
                ]);
            }

            $gioHang->load('mucGioHangs.product.variations');
            return response()->json(['message' => 'Sản phẩm đã được thêm vào giỏ hàng', 'cart' => $gioHang], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function getCart(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
            }

            $gioHang = GioHang::where('id_nguoiDung', $user->id)
                ->with('mucGioHangs.product.variations.images')
                ->first();

            if (!$gioHang) {
                return response()->json(['message' => 'Giỏ hàng trống', 'cart' => null], 200);
            }

            return response()->json(['message' => 'Lấy giỏ hàng thành công', 'cart' => $gioHang], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function updateCartItem(Request $request, $idMucGioHang)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
            }

            $request->validate(['soLuong' => 'required|integer|min:1']);

            $muc = MucGioHang::where('id_mucGioHang', $idMucGioHang)
                ->whereHas('gioHang', fn($q) => $q->where('id_nguoiDung', $user->id))
                ->first();

            if (!$muc) {
                return response()->json(['error' => 'Không tìm thấy mục giỏ hàng'], 404);
            }

            $variation = ProductVariation::find($muc->variation_id);
            if ($request->soLuong > $variation->stock) {
                return response()->json(['error' => 'Số lượng vượt quá tồn kho'], 400);
            }

            $muc->update(['soLuong' => $request->soLuong]);
            return response()->json(['message' => 'Cập nhật thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Lỗi khi cập nhật: ' . $e->getMessage()], 500);
        }
    }

    public function removeCartItem(Request $request, $idMucGioHang)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
            }

            $muc = MucGioHang::where('id_mucGioHang', $idMucGioHang)
                ->whereHas('gioHang', fn($q) => $q->where('id_nguoiDung', $user->id))
                ->first();

            if (!$muc) {
                return response()->json(['error' => 'Không tìm thấy mục giỏ hàng'], 404);
            }

            $muc->delete();
            return response()->json(['message' => 'Xóa thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Lỗi khi xóa: ' . $e->getMessage()], 500);
        }
    }
}