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

        return response()->json([
            'favorites' => $favorites
        ], 200);
    }

    /**
     * Thêm một sản phẩm vào danh sách yêu thích của người dùng.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Validate yêu cầu
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id_sanPham',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Kiểm tra sản phẩm đã có trong danh sách yêu thích hay chưa
        $existing = FavoriteProduct::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Sản phẩm đã có trong danh sách yêu thích'], 200);
        }

        $favorite = FavoriteProduct::create([
            'user_id'    => $user->id,
            'product_id' => $request->product_id,
        ]);

        return response()->json([
            'message'  => 'Thêm sản phẩm yêu thích thành công',
            'favorite' => $favorite,
        ], 201);
    }

    /**
     * Xóa một sản phẩm khỏi danh sách yêu thích của người dùng.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $favorite = FavoriteProduct::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$favorite) {
            return response()->json(['message' => 'Sản phẩm yêu thích không tồn tại'], 404);
        }

        $favorite->delete();

        return response()->json(['message' => 'Xóa sản phẩm yêu thích thành công'], 200);
    }
}
