<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\DonHang;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_sanPham' => 'required|exists:products,id_sanPham',
            'id_donHang' => 'required|exists:donHang,id_donHang',
            'variation_id' => 'required|exists:product_variations,id', // Thêm validation cho variation_id
            'soSao' => 'required|integer|min:1|max:5',
            'binhLuan' => 'nullable|string',
            'urlHinhAnh' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $donHang = DonHang::with('chiTietDonHang')->find($request->id_donHang);

        // Kiểm tra quyền đánh giá
        if (!$donHang || $donHang->id_nguoiDung != $user->id || $donHang->trangThaiDonHang != 'da_giao') {
            return response()->json(['message' => 'Bạn không có quyền đánh giá sản phẩm này.'], 403);
        }

        // Kiểm tra xem sản phẩm và biến thể có trong đơn hàng không
        $chiTietDonHang = $donHang->chiTietDonHang->firstWhere(function ($item) use ($request) {
            return $item->id_sanPham == $request->id_sanPham && $item->variation_id == $request->variation_id;
        });

        if (!$chiTietDonHang) {
            return response()->json(['message' => 'Sản phẩm hoặc biến thể không tồn tại trong đơn hàng này.'], 400);
        }

        // Kiểm tra xem đã đánh giá chưa
        $existingReview = Review::where('id_nguoiDung', $user->id)
            ->where('id_sanPham', $request->id_sanPham)
            ->where('id_donHang', $request->id_donHang)
            ->where('variation_id', $request->variation_id)
            ->exists();

        if ($existingReview) {
            return response()->json(['message' => 'Bạn đã đánh giá biến thể này của sản phẩm rồi.'], 400);
        }

        // Lưu đánh giá
        $review = Review::create([
            'id_nguoiDung' => $user->id,
            'id_sanPham' => $request->id_sanPham,
            'id_donHang' => $request->id_donHang,
            'variation_id' => $request->variation_id,
            'soSao' => $request->soSao,
            'binhLuan' => $request->binhLuan,
            'urlHinhAnh' => $request->urlHinhAnh,
        ]);

        // Cập nhật số sao trung bình của sản phẩm
        $product = Product::find($request->id_sanPham);
        $product->capNhatSoSaoDanhGia();

        return response()->json(['message' => 'Đánh giá đã được gửi thành công!', 'review' => $review], 201);
    }

    public function index($id_sanPham)
    {
        $reviews = Review::where('id_sanPham', $id_sanPham)
            ->with(['user:id,name', 'product.variations.images', 'variation'])
            ->get();

        $reviews->each(function ($review) {
            if ($review->product && $review->product->variations->isNotEmpty()) {
                foreach ($review->product->variations as $variation) {
                    foreach ($variation->images as $image) {
                        $image->image_url = Storage::disk('public')->exists($image->image_url)
                            ? asset('storage/' . $image->image_url)
                            : asset('images/default.png');
                    }
                }
            }
        });

        return response()->json(['reviews' => $reviews]);
    }

    public function userReviews(Request $request)
    {
        $reviews = Review::where('id_nguoiDung', $request->user()->id)
            ->with(['product.variations.images', 'variation'])
            ->get();

        $reviews->each(function ($review) {
            if ($review->product && $review->product->variations->isNotEmpty()) {
                foreach ($review->product->variations as $variation) {
                    foreach ($variation->images as $image) {
                        $image->image_url = Storage::disk('public')->exists($image->image_url)
                            ? asset('storage/' . $image->image_url)
                            : asset('images/default.png');
                    }
                }
            }
        });

        return response()->json(['reviews' => $reviews]);
    }
}