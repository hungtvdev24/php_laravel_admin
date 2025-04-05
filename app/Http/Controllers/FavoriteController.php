<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FavoriteProduct;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class FavoriteController extends Controller
{
    public function __construct()
    {
        // Đảm bảo chỉ người dùng đã đăng nhập mới có thể truy cập
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $favorites = FavoriteProduct::with(['product.variations.images'])
            ->where('user_id', $user->id)
            ->get();

        if ($favorites->isEmpty()) {
            return response()->json(['favorites' => []], 200);
        }

        $favoritesData = $favorites->map(function ($favorite) {
            if (!$favorite->product) {
                return null; // Bỏ qua nếu không load được sản phẩm
            }

            $productData = $favorite->product->toArray();

            // Nếu không có biến thể, thêm một biến thể mặc định
            if (empty($productData['variations'])) {
                $productData['variations'] = [
                    [
                        'id' => null,
                        'color' => 'Mặc định',
                        'size' => null,
                        'price' => $productData['gia'] ?? 0.0,
                        'stock' => 0,
                        'images' => [
                            ['image_url' => asset('images/default.png')]
                        ]
                    ]
                ];
            } else {
                // Xử lý hình ảnh trong variations
                foreach ($productData['variations'] as &$variation) {
                    if (isset($variation['images'])) {
                        foreach ($variation['images'] as &$image) {
                            $image['image_url'] = Storage::disk('public')->exists($image['image_url'])
                                ? asset('storage/' . $image['image_url'])
                                : asset('images/default.png');
                        }
                    }
                }
            }

            return $productData;
        })->filter()->values(); // Loại bỏ null và đánh số lại mảng

        return response()->json(['favorites' => $favoritesData], 200);
    }

    public function add(Request $request, $productId)
    {
        $user = $request->user();

        $validator = Validator::make(['product_id' => $productId], [
            'product_id' => 'required|exists:products,id_sanPham',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $existing = FavoriteProduct::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Sản phẩm đã có trong danh sách yêu thích'], 200);
        }

        $favorite = FavoriteProduct::create([
            'user_id' => $user->id,
            'product_id' => $productId,
        ]);

        $favorite->load(['product.variations.images']);
        if (!$favorite->product) {
            return response()->json(['message' => 'Không thể load thông tin sản phẩm'], 500);
        }

        $productData = $favorite->product->toArray();

        // Nếu không có biến thể, thêm một biến thể mặc định
        if (empty($productData['variations'])) {
            $productData['variations'] = [
                [
                    'id' => null,
                    'color' => 'Mặc định',
                    'size' => null,
                    'price' => $productData['gia'] ?? 0.0,
                    'stock' => 0,
                    'images' => [
                        ['image_url' => asset('images/default.png')]
                    ]
                ]
            ];
        } else {
            // Xử lý hình ảnh trong variations
            foreach ($productData['variations'] as &$variation) {
                if (isset($variation['images'])) {
                    foreach ($variation['images'] as &$image) {
                        $image['image_url'] = Storage::disk('public')->exists($image['image_url'])
                            ? asset('storage/' . $image['image_url'])
                            : asset('images/default.png');
                    }
                }
            }
        }

        return response()->json([
            'message' => 'Thêm sản phẩm yêu thích thành công',
            'favorite' => $productData,
        ], 201);
    }

    public function remove(Request $request, $productId)
    {
        $user = $request->user();

        $validator = Validator::make(['product_id' => $productId], [
            'product_id' => 'required|exists:products,id_sanPham',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $favorite = FavoriteProduct::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if (!$favorite) {
            return response()->json(['message' => 'Sản phẩm không có trong danh sách yêu thích'], 404);
        }

        $favorite->delete();

        return response()->json(['message' => 'Xóa sản phẩm yêu thích thành công'], 200);
    }
}