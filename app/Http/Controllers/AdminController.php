<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;

class AdminController extends Controller
{
    // Dashboard
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    // Quản lý người dùng
    public function manageUsers()
    {
        $users = User::all(); // Lấy danh sách người dùng
        return view('admin.users.index', compact('users'));
    }

    // Xóa người dùng
    public function destroy($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->delete();
            return redirect()->route('admin.users.index')->with('success', 'Xóa người dùng thành công');
        }
        return redirect()->route('admin.users.index')->with('error', 'Người dùng không tồn tại');
    }

    // Quản lý sản phẩm
    public function manageProducts()
    {
        return view('admin.products.index');
    }

    // Quản lý đơn hàng
    public function manageOrders()
    {
        return view('admin.orders.index');
    }

    // Chi tiết đơn hàng
    public function orderDetails($id)
    {
        $order = Order::findOrFail($id);
        return view('admin.orders.details', compact('order'));
    }

    // Quản lý nhân viên
    public function manageEmployees()
    {
        return view('admin.employees.index');
    }

    // Quản lý khách hàng
    public function manageCustomers()
    {
        return view('admin.customers.index');
    }

    // Quản lý khuyến mãi
    public function managePromotions()
    {
        return view('admin.promotions.index');
    }

    // Thống kê
    public function statisticsOrders()
    {
        return view('admin.statistics.orders');
    }

    public function statisticsProducts()
    {
        return view('admin.statistics.products');
    }

    public function statisticsRevenue()
    {
        return view('admin.statistics.revenue');
    }

    // Quản lý tiếp thị liên kết (Affiliate)
    public function manageAffiliate()
    {
        return view('admin.affiliate.index');
    }

    // Quản lý chiến dịch
    public function manageCampaigns()
    {
        return view('admin.campaigns.index');
    }

    // Quản lý dịch vụ
    public function manageServices()
    {
        return view('admin.services.index');
    }

    // Quản lý giao dịch
    public function manageTransactions()
    {
        return view('admin.transactions.index');
    }

    // Quản lý thông báo
    public function manageNotifications()
    {
        return view('admin.notifications');
    }

    // Quản lý bình luận
    public function manageComments()
    {
        return view('admin.comments');
    }

    // Xác minh
    public function verification()
    {
        return view('admin.verification');
    }
    public function destroyUser($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return redirect()->route('admin.users.index')->with('success', 'Người dùng đã được xóa thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi xóa người dùng: ' . $e->getMessage());
        }
    }
}
