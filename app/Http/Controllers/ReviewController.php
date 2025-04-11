<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\DonHang;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ReviewController extends Controller
{
    // Hiển thị danh sách tất cả bình luận trong khu vực admin
    public function indexAdmin(Request $request)
    {
        $query = Review::with(['user:id,name', 'product:id_sanPham,tenSanPham', 'donHang:id_donHang', 'variation']);

        // Lọc theo trạng thái
        $status = $request->input('status', 'all');
        if ($status !== 'all') {
            $query->where('trangThai', $status);
        }

        // Lọc theo khoảng ngày
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        if ($start_date) {
            $start = Carbon::createFromFormat('d/m/Y', $start_date)->startOfDay();
            $query->where('ngayDanhGia', '>=', $start);
        }

        if ($end_date) {
            $end = Carbon::createFromFormat('d/m/Y', $end_date)->endOfDay();
            $query->where('ngayDanhGia', '<=', $end);
        }

        // Sắp xếp và phân trang
        $reviews = $query->orderBy('ngayDanhGia', 'desc')->paginate(10);

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

        return view('admin.reviews.index', compact('reviews', 'status', 'start_date', 'end_date'));
    }

    // Hiển thị chi tiết một bình luận
    public function showAdmin($id)
    {
        $review = Review::with(['user:id,name,email,phone,tuoi', 'product:id_sanPham,tenSanPham', 'donHang:id_donHang', 'variation'])
            ->findOrFail($id);

        if ($review->product && $review->product->variations->isNotEmpty()) {
            foreach ($review->product->variations as $variation) {
                foreach ($variation->images as $image) {
                    $image->image_url = Storage::disk('public')->exists($image->image_url)
                        ? asset('storage/' . $image->image_url)
                        : asset('images/default.png');
                }
            }
        }

        return view('admin.reviews.show', compact('review'));
    }

    // Duyệt bình luận
    public function approve($id)
    {
        $review = Review::findOrFail($id);
        $review->trangThai = Review::APPROVED;
        $review->save();

        // Cập nhật số sao trung bình của sản phẩm sau khi duyệt bình luận
        $product = Product::find($review->id_sanPham);
        if ($product) {
            $product->capNhatSoSaoDanhGia();
        }

        return response()->json(['message' => 'Bình luận đã được duyệt thành công!']);
    }

    // Từ chối bình luận
    public function reject($id)
    {
        $review = Review::findOrFail($id);
        $review->trangThai = Review::REJECTED;
        $review->save();

        return response()->json(['message' => 'Bình luận đã bị từ chối!']);
    }

    // Xóa một bình luận
    public function destroyAdmin($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        // Cập nhật lại số sao trung bình của sản phẩm sau khi xóa bình luận
        $product = Product::find($review->id_sanPham);
        if ($product) {
            $product->capNhatSoSaoDanhGia();
        }

        return response()->json(['message' => 'Bình luận đã được xóa thành công!']);
    }

    // Gửi bình luận (người dùng)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_sanPham' => 'required|exists:products,id_sanPham',
            'id_donHang' => 'required|exists:donHang,id_donHang',
            'variation_id' => 'required|exists:product_variations,id',
            'soSao' => 'required|integer|min:1|max:5',
            'binhLuan' => 'nullable|string',
            'urlHinhAnh' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $donHang = DonHang::with('chiTietDonHang')->find($request->id_donHang);

        if (!$donHang || $donHang->id_nguoiDung != $user->id || $donHang->trangThaiDonHang != 'da_giao') {
            return response()->json(['message' => 'Bạn không có quyền đánh giá sản phẩm này.'], 403);
        }

        $chiTietDonHang = $donHang->chiTietDonHang->firstWhere(function ($item) use ($request) {
            return $item->id_sanPham == $request->id_sanPham && $item->variation_id == $request->variation_id;
        });

        if (!$chiTietDonHang) {
            return response()->json(['message' => 'Sản phẩm hoặc biến thể không tồn tại trong đơn hàng này.'], 400);
        }

        $existingReview = Review::where('id_nguoiDung', $user->id)
            ->where('id_sanPham', $request->id_sanPham)
            ->where('id_donHang', $request->id_donHang)
            ->where('variation_id', $request->variation_id)
            ->exists();

        if ($existingReview) {
            return response()->json(['message' => 'Bạn đã đánh giá biến thể này của sản phẩm rồi.'], 400);
        }

        $review = Review::create([
            'id_nguoiDung' => $user->id,
            'id_sanPham' => $request->id_sanPham,
            'id_donHang' => $request->id_donHang,
            'variation_id' => $request->variation_id,
            'soSao' => $request->soSao,
            'binhLuan' => $request->binhLuan,
            'urlHinhAnh' => $request->urlHinhAnh,
            'trangThai' => Review::PENDING,
        ]);

        return response()->json(['message' => 'Đánh giá đã được gửi và đang chờ duyệt!', 'review' => $review], 201);
    }

    // Hiển thị danh sách bình luận của một sản phẩm (chỉ hiển thị bình luận đã được duyệt)
    public function index($id_sanPham)
    {
        $reviews = Review::where('id_sanPham', $id_sanPham)
            ->where('trangThai', Review::APPROVED)
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

    // Hiển thị danh sách bình luận của người dùng (bao gồm cả bình luận chưa được duyệt)
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