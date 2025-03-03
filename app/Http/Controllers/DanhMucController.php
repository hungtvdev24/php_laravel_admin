<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DanhMuc;
use Illuminate\Support\Facades\Validator;

class DanhMucController extends Controller
{
    /**
     * Hiển thị danh sách danh mục
     */
    public function index()
    {
        $danhMucs = DanhMuc::orderBy('id_danhMuc', 'desc')->paginate(10);
        return view('admin.danhmucs.index', compact('danhMucs'));
    }

    /**
     * Hiển thị form thêm danh mục
     */
    public function create()
    {
        return view('admin.danhmucs.create');
    }

    /**
     * Xử lý lưu danh mục vào database
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
            return redirect()->back()->with('error', 'Lỗi khi lưu danh mục: ' . $e->getMessage());
        }

        return redirect()->route('admin.danhmucs.index')->with('success', 'Danh mục đã được thêm thành công!');
    }

    /**
     * Hiển thị form chỉnh sửa danh mục
     */
    public function edit($id_danhMuc)
    {
        $danhMuc = DanhMuc::findOrFail($id_danhMuc);
        return view('admin.danhmucs.edit', compact('danhMuc'));
    }

    /**
     * Xử lý cập nhật danh mục
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
            return redirect()->back()->with('error', 'Lỗi khi cập nhật danh mục: ' . $e->getMessage());
        }

        return redirect()->route('admin.danhmucs.index')->with('success', 'Danh mục đã được cập nhật thành công!');
    }

    /**
     * Xóa danh mục
     */
    public function destroy($id_danhMuc)
    {
        try {
            $danhMuc = DanhMuc::findOrFail($id_danhMuc);
            $danhMuc->delete();
            return redirect()->route('admin.danhmucs.index')->with('success', 'Danh mục đã được xóa thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi xóa danh mục: ' . $e->getMessage());
        }
    }
    

}
