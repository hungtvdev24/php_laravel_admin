<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DonHang;
use App\Models\ChiTietDonHang;
use App\Models\Product;
use App\Models\Employee;
use App\Models\Admin;
use App\Models\DanhMuc;
use App\Models\OrderStatusHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin')->only([
            'manageEmployees', 'createEmployee', 'storeEmployee', 'editEmployee', 'updateEmployee', 'destroyEmployee',
            'destroyUser', 'manageCustomers', 'managePromotions', 'statisticsOrders', 'statisticsProducts', 'statisticsRevenue',
            'manageAffiliate', 'manageCampaigns', 'manageServices', 'manageTransactions', 'manageNotifications', 'manageComments', 'verification'
        ]);
    }

    public function dashboard(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date   = $request->input('end_date');
        $view_type  = $request->input('view_type', 'month');

        if (!$start_date || !$end_date) {
            $start_date = now()->subMonths(11)->startOfMonth()->format('d/m/Y');
            $end_date   = now()->endOfMonth()->format('d/m/Y');
        }

        try {
            if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $start_date) || !preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $end_date)) {
                throw new \Exception('Định dạng ngày không hợp lệ. Yêu cầu định dạng: dd/mm/yyyy');
            }
            $start = Carbon::createFromFormat('d/m/Y', $start_date)->startOfDay();
            $end   = Carbon::createFromFormat('d/m/Y', $end_date)->endOfDay();

            if ($start->gt($end)) {
                throw new \Exception('Ngày bắt đầu không thể lớn hơn ngày kết thúc');
            }
        } catch (\Exception $e) {
            $start_date = now()->subMonths(11)->startOfMonth()->format('d/m/Y');
            $end_date   = now()->endOfMonth()->format('d/m/Y');
            $start      = now()->subMonths(11)->startOfMonth()->startOfDay();
            $end        = now()->endOfMonth()->endOfDay();
        }

        $data = $this->getDashboardData($start, $end, $view_type);

        return view('admin.dashboard', array_merge([
            'start_date' => $start_date,
            'end_date'   => $end_date,
            'view_type'  => $view_type,
            'role' => session('role'),
        ], $data));
    }

    public function filterDashboard(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date   = $request->input('end_date');
        $view_type  = $request->input('view_type', 'month');

        if (!$start_date || !$end_date) {
            $start_date = now()->subMonths(11)->startOfMonth()->format('d/m/Y');
            $end_date   = now()->endOfMonth()->format('d/m/Y');
        }

        try {
            if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $start_date) || !preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $end_date)) {
                throw new \Exception('Định dạng ngày không hợp lệ. Yêu cầu định dạng: dd/mm/yyyy');
            }
            $start = Carbon::createFromFormat('d/m/Y', $start_date)->startOfDay();
            $end   = Carbon::createFromFormat('d/m/Y', $end_date)->endOfDay();

            if ($start->gt($end)) {
                throw new \Exception('Ngày bắt đầu không thể lớn hơn ngày kết thúc');
            }
        } catch (\Exception $e) {
            $start_date = now()->subMonths(11)->startOfMonth()->format('d/m/Y');
            $end_date   = now()->endOfMonth()->format('d/m/Y');
            $start      = now()->subMonths(11)->startOfMonth()->startOfDay();
            $end        = now()->endOfMonth()->endOfDay();
        }

        $data = $this->getDashboardData($start, $end, $view_type);

        return response()->json($data);
    }

    private function getDashboardData($start, $end, $view_type)
    {
        $deliveredCount = DonHang::whereBetween('created_at', [$start, $end])
            ->where('trangThaiDonHang', 'da_giao')
            ->count();
        $shippingCount = DonHang::whereBetween('created_at', [$start, $end])
            ->where('trangThaiDonHang', 'dang_giao')
            ->count();
        $pendingCount = DonHang::whereBetween('created_at', [$start, $end])
            ->where('trangThaiDonHang', 'cho_xac_nhan')
            ->count();
        $canceledCount = DonHang::whereBetween('created_at', [$start, $end])
            ->where('trangThaiDonHang', 'huy')
            ->count();

        $dateFormatMySQL = match ($view_type) {
            'day'   => '%Y-%m-%d',
            'week'  => '%x-W%v',
            'month' => '%Y-%m',
            default => '%Y-%m',
        };

        $diffDays      = $start->diffInDays($end);
        $previousStart = $start->copy()->subDays($diffDays + 1);
        $previousEnd   = $start->copy()->subDay();

        $revenueByPeriod = DonHang::select(
            DB::raw("DATE_FORMAT(created_at, '$dateFormatMySQL') as period"),
            DB::raw('SUM(tongTien) as total_revenue')
        )
        ->whereBetween('created_at', [$start, $end])
        ->where('trangThaiDonHang', 'da_giao')
        ->groupBy('period')
        ->orderBy('period')
        ->get();

        $previousRevenueByPeriod = DonHang::select(
            DB::raw("DATE_FORMAT(created_at, '$dateFormatMySQL') as period"),
            DB::raw('SUM(tongTien) as total_revenue')
        )
        ->whereBetween('created_at', [$previousStart, $previousEnd])
        ->where('trangThaiDonHang', 'da_giao')
        ->groupBy('period')
        ->orderBy('period')
        ->get();

        $canceledByPeriod = DonHang::select(
            DB::raw("DATE_FORMAT(created_at, '$dateFormatMySQL') as period"),
            DB::raw('COUNT(*) as total_canceled')
        )
        ->whereBetween('created_at', [$start, $end])
        ->where('trangThaiDonHang', 'huy')
        ->groupBy('period')
        ->orderBy('period')
        ->get();

        $topProducts = ChiTietDonHang::join('donHang', 'chiTietDonHang.id_donHang', '=', 'donHang.id_donHang')
            ->join('products', 'chiTietDonHang.id_sanPham', '=', 'products.id_sanPham')
            ->whereBetween('donHang.created_at', [$start, $end])
            ->where('donHang.trangThaiDonHang', 'da_giao')
            ->select('products.tenSanPham as ten_san_pham', DB::raw('SUM(chiTietDonHang.soLuong) as total_sold'))
            ->groupBy('products.tenSanPham')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $totalOrders = $deliveredCount + $shippingCount + $pendingCount + $canceledCount;
        $statusPercentages = [
            'da_giao'      => $totalOrders > 0 ? ($deliveredCount / $totalOrders) * 100 : 0,
            'dang_giao'    => $totalOrders > 0 ? ($shippingCount / $totalOrders) * 100 : 0,
            'cho_xac_nhan' => $totalOrders > 0 ? ($pendingCount / $totalOrders) * 100 : 0,
            'huy'          => $totalOrders > 0 ? ($canceledCount / $totalOrders) * 100 : 0,
        ];

        $newCustomersByPeriod = User::select(
            DB::raw("DATE_FORMAT(created_at, '$dateFormatMySQL') as period"),
            DB::raw('COUNT(*) as total_new_customers')
        )
        ->whereBetween('created_at', [$start, $end])
        ->groupBy('period')
        ->orderBy('period')
        ->get();

        $previousNewCustomersByPeriod = User::select(
            DB::raw("DATE_FORMAT(created_at, '$dateFormatMySQL') as period"),
            DB::raw('COUNT(*) as total_new_customers')
        )
        ->whereBetween('created_at', [$previousStart, $previousEnd])
        ->groupBy('period')
        ->orderBy('period')
        ->get();

        $periodsRaw = DonHang::select(
            DB::raw("DATE_FORMAT(created_at, '$dateFormatMySQL') as period")
        )
        ->whereBetween('created_at', [$start, $end])
        ->groupBy('period')
        ->orderBy('period')
        ->pluck('period');

        $periods = $periodsRaw->map(function ($period) use ($view_type) {
            if ($view_type === 'week') {
                $parts = explode('-', $period);
                if (count($parts) === 2 && str_starts_with($parts[1], 'W')) {
                    $week = substr($parts[1], 1);
                    return "Tuần {$week}/{$parts[0]}";
                }
            } elseif ($view_type === 'day') {
                $pieces = explode('-', $period);
                if (count($pieces) === 3) {
                    return $pieces[2].'/'.$pieces[1].'/'.$pieces[0];
                }
            } elseif ($view_type === 'month') {
                $parts = explode('-', $period);
                if (count($parts) === 2) {
                    return $parts[1].'/'.$parts[0];
                }
            }
            return $period;
        });

        $totalRevenue = DonHang::whereBetween('created_at', [$start, $end])
            ->where('trangThaiDonHang', 'da_giao')
            ->sum('tongTien');

        return [
            'deliveredCount'            => $deliveredCount,
            'shippingCount'             => $shippingCount,
            'pendingCount'              => $pendingCount,
            'canceledCount'             => $canceledCount,
            'totalRevenue'              => $totalRevenue,
            'revenueByPeriod'           => $revenueByPeriod,
            'previousRevenueByPeriod'   => $previousRevenueByPeriod,
            'canceledByPeriod'          => $canceledByPeriod,
            'topProducts'               => $topProducts,
            'statusPercentages'         => $statusPercentages,
            'newCustomersByPeriod'      => $newCustomersByPeriod,
            'previousNewCustomersByPeriod' => $previousNewCustomersByPeriod,
            'periods'                   => $periods,
        ];
    }

    public function manageEmployees()
    {
        $employees = Employee::with('admin')->get();
        return view('admin.employees.index', compact('employees'));
    }

    public function createEmployee()
    {
        return view('admin.employees.create');
    }

    public function storeEmployee(Request $request)
    {
        $request->validate([
            'tenNhanVien' => 'required|string|max:255',
            'tuoi' => 'required|integer|min:18',
            'diaChi' => 'required|string|max:255',
            'tenTaiKhoan' => 'required|string|unique:employees,tenTaiKhoan|max:255',
            'matKhau' => 'required|string|min:6',
            'trangThai' => 'required|in:active,inactive',
        ]);

        $adminId = session('user_id');
        if (!$adminId) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập lại để thực hiện hành động này.');
        }

        Employee::create([
            'tenNhanVien' => $request->tenNhanVien,
            'tuoi' => $request->tuoi,
            'diaChi' => $request->diaChi,
            'tenTaiKhoan' => $request->tenTaiKhoan,
            'matKhau' => Hash::make($request->matKhau),
            'trangThai' => $request->trangThai,
            'id_admin' => $adminId,
        ]);

        return redirect()->route('admin.employees.index')->with('success', 'Thêm nhân viên thành công!');
    }

    public function editEmployee($id)
    {
        $employee = Employee::findOrFail($id);
        return view('admin.employees.edit', compact('employee'));
    }

    public function updateEmployee(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $request->validate([
            'tenNhanVien' => 'required|string|max:255',
            'tuoi' => 'required|integer|min:18',
            'diaChi' => 'required|string|max:255',
            'tenTaiKhoan' => 'required|string|max:255|unique:employees,tenTaiKhoan,' . $employee->id_nhanVien . ',id_nhanVien',
            'matKhau' => 'nullable|string|min:6',
            'trangThai' => 'required|in:active,inactive',
        ]);

        $data = [
            'tenNhanVien' => $request->tenNhanVien,
            'tuoi' => $request->tuoi,
            'diaChi' => $request->diaChi,
            'tenTaiKhoan' => $request->tenTaiKhoan,
            'trangThai' => $request->trangThai,
        ];

        if ($request->filled('matKhau')) {
            $data['matKhau'] = Hash::make($request->matKhau);
        }

        $employee->update($data);

        return redirect()->route('admin.employees.index')->with('success', 'Cập nhật nhân viên thành công!');
    }

    public function destroyEmployee($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();
        return redirect()->route('admin.employees.index')->with('success', 'Xóa nhân viên thành công!');
    }

    public function manageUsers()
    {
        $users = User::all();
        return view('admin.users.index', [
            'users' => $users,
            'readOnly' => request()->attributes->get('user_type') === 'employee'
        ]);
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

    public function manageOrders(Request $request)
    {
        $query = DonHang::with(['chiTietDonHang.sanPham'])->orderBy('id_donHang', 'desc');
        $phone = $request->input('phone', '');
        $status = $request->input('status', 'all');
        $start_date = $request->input('start_date', '');
        $end_date = $request->input('end_date', '');

        if ($request->filled('phone')) {
            $query->where('sdt_nhanHang', 'LIKE', "%{$request->phone}%");
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('trangThaiDonHang', $request->status);
        }
        if ($start_date && $end_date) {
            $start = Carbon::createFromFormat('d/m/Y', $start_date)->startOfDay();
            $end = Carbon::createFromFormat('d/m/Y', $end_date)->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        }

        $orders = $query->paginate(10);
        $orders->appends(['phone' => $phone, 'status' => $status, 'start_date' => $start_date, 'end_date' => $end_date]);

        return view('admin.orders.index', [
            'orders' => $orders,
            'phone' => $phone,
            'status' => $status,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'readOnly' => false // Nhân viên có đầy đủ quyền trên Đơn hàng
        ]);
    }

    public function orderDetails($id)
    {
        $order = DonHang::with(['chiTietDonHang.sanPham', 'chiTietDonHang.variation', 'statusHistory'])->findOrFail($id);
        return view('admin.orders.vieworder', [
            'order' => $order,
            'readOnly' => false // Nhân viên có đầy đủ quyền trên Đơn hàng
        ]);
    }

    public function updateOrder(Request $request, $id)
    {
        $request->validate(['trangThaiDonHang' => 'required|in:cho_xac_nhan,dang_giao,da_giao,huy']);
        $order = DonHang::findOrFail($id);
        $oldStatus = $order->trangThaiDonHang;
        $newStatus = $request->trangThaiDonHang;

        if (request()->attributes->get('user_type') === 'employee' && !($oldStatus === 'cho_xac_nhan' && $newStatus === 'dang_giao')) {
            return redirect()->back()->with('error', 'Nhân viên chỉ có thể chuyển trạng thái từ "Chờ xác nhận" sang "Đang giao".');
        }

        DB::beginTransaction();
        try {
            if ($oldStatus === 'cho_xac_nhan' && $newStatus === 'dang_giao' && !$order->ngay_du_kien_giao) {
                $order->ngay_du_kien_giao = now()->addDays(2);
            }
            if ($oldStatus === 'dang_giao' && $newStatus === 'da_giao' && !$order->ngay_giao_thuc_te) {
                $order->ngay_giao_thuc_te = now();
            }
            $order->trangThaiDonHang = $newStatus;
            $order->save();

            OrderStatusHistory::create([
                'id_donHang' => $order->id_donHang,
                'trangThaiDonHang' => $newStatus,
                'ghiChu' => "Cập nhật từ '$oldStatus' bởi " . session('name') . " (" . session('username') . ")",
            ]);

            DB::commit();
            return redirect()->route('admin.orders.index')->with('success', 'Cập nhật trạng thái đơn hàng thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.orders.index')->with('error', 'Cập nhật thất bại: ' . $e->getMessage());
        }
    }

    public function manageProducts(Request $request)
    {
        $query = Product::with(['danhMuc', 'variations.images'])->orderBy('id_sanPham', 'desc');
        
        if ($request->filled('search')) {
            $query->where('tenSanPham', 'LIKE', "%{$request->search}%");
        }

        $products = $query->paginate(10);
        $products->appends(['search' => $request->search]);

        return view('admin.products.index', [
            'products' => $products,
            'readOnly' => request()->attributes->get('user_type') === 'employee' // Nhân viên chỉ có quyền xem
        ]);
    }

    public function manageCustomers()
    {
        return view('admin.customers.index', [
            'readOnly' => request()->attributes->get('user_type') === 'employee' // Nhân viên chỉ có quyền xem
        ]);
    }

    public function managePromotions()
    {
        return view('admin.promotions.index', [
            'readOnly' => request()->attributes->get('user_type') === 'employee' // Nhân viên chỉ có quyền xem
        ]);
    }

    public function statisticsOrders()
    {
        return view('admin.statistics.orders', [
            'readOnly' => request()->attributes->get('user_type') === 'employee' // Nhân viên chỉ có quyền xem
        ]);
    }

    public function statisticsProducts()
    {
        return view('admin.statistics.products', [
            'readOnly' => request()->attributes->get('user_type') === 'employee' // Nhân viên chỉ có quyền xem
        ]);
    }

    public function statisticsRevenue()
    {
        return view('admin.statistics.revenue', [
            'readOnly' => request()->attributes->get('user_type') === 'employee' // Nhân viên chỉ có quyền xem
        ]);
    }

    public function manageAffiliate()
    {
        return view('admin.affiliate.index', [
            'readOnly' => request()->attributes->get('user_type') === 'employee' // Nhân viên chỉ có quyền xem
        ]);
    }

    public function manageCampaigns()
    {
        return view('admin.campaigns.index', [
            'readOnly' => request()->attributes->get('user_type') === 'employee' // Nhân viên chỉ có quyền xem
        ]);
    }

    public function manageServices()
    {
        return view('admin.services.index', [
            'readOnly' => request()->attributes->get('user_type') === 'employee' // Nhân viên chỉ có quyền xem
        ]);
    }

    public function manageTransactions()
    {
        return view('admin.transactions.index', [
            'readOnly' => request()->attributes->get('user_type') === 'employee' // Nhân viên chỉ có quyền xem
        ]);
    }

    public function manageNotifications()
    {
        return view('admin.notifications', [
            'readOnly' => request()->attributes->get('user_type') === 'employee' // Nhân viên chỉ có quyền xem
        ]);
    }

    public function manageComments()
    {
        return view('admin.comments', [
            'readOnly' => request()->attributes->get('user_type') === 'employee' // Nhân viên chỉ có quyền xem
        ]);
    }

    public function verification()
    {
        return view('admin.verification', [
            'readOnly' => request()->attributes->get('user_type') === 'employee' // Nhân viên chỉ có quyền xem
        ]);
    }
}