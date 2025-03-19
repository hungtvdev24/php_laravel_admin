<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FavoriteProduct;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    /**
     * Lấy danh sách sản phẩm yêu thích của người dùng.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $favorites = FavoriteProduct::with('product')
            ->where('user_id', $user->id)
            ->get();

        // Nếu không có sản phẩm yêu thích, trả về danh sách rỗng thay vì lỗi 404
        if ($favorites->isEmpty()) {
            return response()->json([
                'favorites' => []
            ], 200);
        }

        // Trả về danh sách sản phẩm yêu thích với thông tin sản phẩm
        return response()->json([
            'favorites' => $favorites->map(function ($favorite) {
                return $favorite->product; // Trả về trực tiếp thông tin sản phẩm
            })
        ], 200);
    }

    /**
     * Thêm một sản phẩm vào danh sách yêu thích của người dùng.
     */
    public function add(Request $request, $productId)
    {
        $user = $request->user();

        // Validate yêu cầu
        $validator = Validator::make(['product_id' => $productId], [
            'product_id' => 'required|exists:products,id_sanPham',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Kiểm tra sản phẩm đã có trong danh sách yêu thích hay chưa
        $existing = FavoriteProduct::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Sản phẩm đã có trong danh sách yêu thích'], 200);
        }

        $favorite = FavoriteProduct::create([
            'user_id'    => $user->id,
            'product_id' => $productId,
        ]);

        // Tải lại thông tin sản phẩm để trả về
        $favorite->load('product');

        return response()->json([
            'message'  => 'Thêm sản phẩm yêu thích thành công',
            'favorite' => $favorite->product,
        ], 201);
    }

    /**
     * Xóa một sản phẩm khỏi danh sách yêu thích của người dùng.
     */
    public function remove(Request $request, $productId)
    {
        $user = $request->user();

        $favorite = FavoriteProduct::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if (!$favorite) {
            return response()->json(['message' => 'Sản phẩm yêu thích không tồn tại'], 404);
        }

        $favorite->delete();

        return response()->json(['message' => 'Xóa sản phẩm yêu thích thành công'], 200);
    }
}