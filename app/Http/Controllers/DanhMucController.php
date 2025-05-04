<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DanhMuc;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class DanhMucController extends Controller
{
    /**
     * Hiển thị danh sách danh mục (cho admin)
     */
    public function index()
    {
        $danhMucs = DanhMuc::orderBy('id_danhMuc', 'asc')->paginate(10); // Sắp xếp theo ID tăng dần
        return view('admin.danhmucs.index', compact('danhMucs'));
    }

    /**
     * Hiển thị form thêm danh mục (cho admin)
     */
    public function create()
    {
        return view('admin.danhmucs.create');
    }

    /**
     * Xử lý lưu danh mục vào database (cho admin)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenDanhMuc' => 'required|string|max:255|unique:danhMuc,tenDanhMuc',
            'moTa' => 'nullable|string|max:1000',
        ], [
            'tenDanhMuc.required' => 'Tên danh mục là bắt buộc.',
            'tenDanhMuc.unique' => 'Tên danh mục đã tồn tại.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DanhMuc::create([
                'tenDanhMuc' => $request->tenDanhMuc,
                'moTa' => $request->moTa,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating category: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi khi lưu danh mục: ' . $e->getMessage());
        }

        return redirect()->route('admin.danhmucs.index')->with('success', 'Danh mục đã được thêm thành công!');
    }

    /**
     * Hiển thị form chỉnh sửa danh mục (cho admin)
     */
    public function edit($id_danhMuc)
    {
        $danhMuc = DanhMuc::findOrFail($id_danhMuc);
        return view('admin.danhmucs.edit', compact('danhMuc'));
    }

    /**
     * Xử lý cập nhật danh mục (cho admin)
     */
    public function update(Request $request, $id_danhMuc)
    {
        $danhMuc = DanhMuc::findOrFail($id_danhMuc);

        $validator = Validator::make($request->all(), [
            'tenDanhMuc' => 'required|string|max:255|unique:danhMuc,tenDanhMuc,' . $id_danhMuc . ',id_danhMuc',
            'moTa' => 'nullable|string|max:1000',
        ], [
            'tenDanhMuc.required' => 'Tên danh mục là bắt buộc.',
            'tenDanhMuc.unique' => 'Tên danh mục đã tồn tại.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $danhMuc->update([
                'tenDanhMuc' => $request->tenDanhMuc,
                'moTa' => $request->moTa,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating category: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi khi cập nhật danh mục: ' . $e->getMessage());
        }

        return redirect()->route('admin.danhmucs.index')->with('success', 'Danh mục đã được cập nhật thành công!');
    }

    /**
     * Xóa danh mục (cho admin)
     */
    public function destroy($id_danhMuc)
    {
        try {
            $danhMuc = DanhMuc::findOrFail($id_danhMuc);

            // Kiểm tra xem danh mục có sản phẩm hay không
            if ($danhMuc->sanPhams()->count() > 0) {
                return redirect()->route('admin.danhmucs.index')->with('error', 'Không thể xóa danh mục vì danh mục này chứa sản phẩm!');
            }

            $danhMuc->delete();
            return redirect()->route('admin.danhmucs.index')->with('success', 'Danh mục đã được xóa thành công!');
        } catch (\Exception $e) {
            Log::error('Error deleting category: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi khi xóa danh mục: ' . $e->getMessage());
        }
    }

    /**
     * API: Lấy danh sách danh mục (công khai)
     */
    public function getCategories()
    {
        try {
            $danhMucs = DanhMuc::select('id_danhMuc', 'tenDanhMuc', 'moTa')
                ->orderBy('id_danhMuc', 'desc')
                ->get();
            Log::info('Fetched categories', ['count' => $danhMucs->count(), 'data' => $danhMucs]);
            return response()->json($danhMucs, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching categories: ' . $e->getMessage());
            return response()->json(['error' => 'Lỗi khi lấy danh sách danh mục: ' . $e->getMessage()], 500);
        }
    }
}