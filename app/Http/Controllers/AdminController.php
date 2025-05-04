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
use App\Models\Mess;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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

        $adminId = Auth::guard('admin')->id();
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

            $userType = request()->attributes->get('user_type') === 'employee' ? 'Nhân viên' : 'Admin';
            OrderStatusHistory::create([
                'id_donHang' => $order->id_donHang,
                'trangThaiDonHang' => $newStatus,
                'ghiChu' => "Được cập nhật từ trạng thái $oldStatus bởi " . session('name') . " ($userType)",
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
        $adminId = Auth::guard('admin')->id();
        $notifications = Notification::where('user_id', $adminId)->orderBy('created_at', 'desc')->get();
        return view('admin.notifications.index', compact('notifications'));
    }

    public function sendNotification(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        Notification::create([
            'user_id' => $request->user_id,
            'title' => $request->title,
            'content' => $request->content,
            'is_read' => false,
        ]);

        return redirect()->back()->with('success', 'Thông báo đã được gửi!');
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

    /**
     * Hiển thị danh sách người dùng để admin chọn người chat.
     */
    public function chatIndex()
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Vui lòng đăng nhập lại để xem danh sách người dùng.');
        }

        // Lấy danh sách tất cả người dùng
        $users = User::all();

        // Chuẩn bị initialUsers cho JavaScript
        $initialUsers = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ];
        })->toArray();

        // Lấy số tin nhắn chưa đọc, thời gian tin nhắn gần đây nhất, và nội dung tin nhắn gần đây
        $unreadMessages = [];
        $latestMessages = [];
        $latestMessageContents = [];

        foreach ($users as $user) {
            // Đếm số tin nhắn chưa đọc từ người dùng gửi đến admin
            $unreadCount = Mess::where('sender_id', $user->id)
                ->where('sender_type', User::class)
                ->where('receiver_id', $admin->id)
                ->where('receiver_type', Admin::class)
                ->where('is_read', false)
                ->count();
            $unreadMessages[$user->id] = $unreadCount;

            // Lấy tin nhắn gần đây nhất giữa admin và người dùng
            $latestMessage = Mess::where(function ($query) use ($user, $admin) {
                $query->where('sender_id', $user->id)
                      ->where('sender_type', User::class)
                      ->where('receiver_id', $admin->id)
                      ->where('receiver_type', Admin::class);
            })->orWhere(function ($query) use ($user, $admin) {
                $query->where('sender_id', $admin->id)
                      ->where('sender_type', Admin::class)
                      ->where('receiver_id', $user->id)
                      ->where('receiver_type', User::class);
            })->orderBy('created_at', 'desc')
              ->first();

            // Nếu có tin nhắn, lưu thời gian (theo múi giờ Hà Nội) và nội dung
            if ($latestMessage) {
                $latestMessages[$user->id] = Carbon::parse($latestMessage->created_at)
                    ->setTimezone('Asia/Ho_Chi_Minh')
                    ->format('H:i d/m/Y');
                $latestMessageContents[$user->id] = $latestMessage->content;
            } else {
                $latestMessages[$user->id] = null;
                $latestMessageContents[$user->id] = null;
            }
        }

        return view('admin.chat.index', compact('users', 'unreadMessages', 'latestMessages', 'latestMessageContents', 'admin', 'initialUsers'));
    }

    /**
     * Hiển thị màn hình chat với một người dùng cụ thể.
     */
    public function chatWithUser($userId)
    {
        $user = User::findOrFail($userId);

        $sender = null;
        $senderId = null;
        $senderType = null;

        if (Auth::guard('admin')->check()) {
            $sender = Admin::find(Auth::guard('admin')->id());
            if (!$sender) {
                return redirect()->route('admin.login')->with('error', 'Vui lòng đăng nhập lại để xem tin nhắn.');
            }
            $senderId = $sender->id; // Cột khóa chính của bảng admins là 'id'
            $senderType = Admin::class;
        } elseif (Auth::guard('employee')->check()) {
            $sender = Employee::find(Auth::guard('employee')->id());
            if (!$sender) {
                return redirect()->route('admin.login')->with('error', 'Vui lòng đăng nhập lại để xem tin nhắn.');
            }
            $senderId = $sender->id_nhanVien; // Cột khóa chính của bảng employees là 'id_nhanVien'
            $senderType = Employee::class;
        } else {
            return redirect()->route('admin.login')->with('error', 'Vui lòng đăng nhập lại để xem tin nhắn.');
        }

        $messages = Mess::where(function ($query) use ($senderId, $senderType, $userId) {
            $query->where('sender_id', $senderId)
                  ->where('sender_type', $senderType)
                  ->where('receiver_id', $userId)
                  ->where('receiver_type', User::class);
        })->orWhere(function ($query) use ($senderId, $senderType, $userId) {
            $query->where('sender_id', $userId)
                  ->where('sender_type', User::class)
                  ->where('receiver_id', $senderId)
                  ->where('receiver_type', $senderType);
        })->orderBy('created_at', 'asc')->get();

        // Chuyển đổi thời gian sang múi giờ Hà Nội
        foreach ($messages as $message) {
            $message->created_at = Carbon::parse($message->created_at)->setTimezone('Asia/Ho_Chi_Minh');
        }

        // Đánh dấu tất cả tin nhắn từ người dùng này là đã đọc
        Mess::where('sender_id', $userId)
            ->where('sender_type', User::class)
            ->where('receiver_id', $senderId)
            ->where('receiver_type', $senderType)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('admin.chat.show', compact('user', 'messages', 'senderId'));
    }

    /**
     * Gửi tin nhắn từ admin hoặc nhân viên đến người dùng.
     */
    public function sendMessage(Request $request, $userId)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $sender = null;
        $senderId = null;
        $senderType = null;

        if (Auth::guard('admin')->check()) {
            $sender = Admin::find(Auth::guard('admin')->id());
            if (!$sender) {
                return redirect()->route('admin.login')->with('error', 'Tài khoản admin không hợp lệ.');
            }
            $senderId = $sender->id; // Cột khóa chính của bảng admins là 'id'
            $senderType = Admin::class;
        } elseif (Auth::guard('employee')->check()) {
            $sender = Employee::find(Auth::guard('employee')->id());
            if (!$sender) {
                return redirect()->route('admin.login')->with('error', 'Tài khoản nhân viên không hợp lệ.');
            }
            $senderId = $sender->id_nhanVien; // Cột khóa chính của bảng employees là 'id_nhanVien'
            $senderType = Employee::class;
        } else {
            return redirect()->route('admin.login')->with('error', 'Vui lòng đăng nhập lại để gửi tin nhắn.');
        }

        $receiver = User::find($userId);
        if (!$receiver) {
            return redirect()->route('admin.chat.index')->with('error', 'Người nhận không tồn tại.');
        }

        $message = Mess::create([
            'sender_id' => $senderId,
            'sender_type' => $senderType,
            'receiver_id' => $userId,
            'receiver_type' => User::class,
            'content' => $request->content,
            'is_read' => false,
        ]);

        Log::info('sendMessage called', [
            'sender_id' => $senderId,
            'sender_type' => $senderType,
            'receiver_id' => $userId,
            'receiver_type' => User::class,
            'content' => $request->content,
        ]);

        event(new \App\Events\MessageSent($message));

        Log::info('MessageSent event fired', ['message' => $message->toArray()]);

        return redirect()->route('admin.chat.show', $userId)->with('success', 'Tin nhắn đã được gửi!');
    }

    /**
     * API để lấy tin nhắn giữa admin và một người dùng cụ thể.
     */
    public function getMessages($userId, Request $request)
    {
        $receiverType = $request->query('receiver_type');
        $adminId = Auth::guard('admin')->id();

        if (!$adminId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $messages = Mess::where(function ($query) use ($userId, $adminId, $receiverType) {
            $query->where('sender_id', $userId)
                  ->where('sender_type', User::class)
                  ->where('receiver_id', $adminId)
                  ->where('receiver_type', $receiverType);
        })->orWhere(function ($query) use ($userId, $adminId, $receiverType) {
            $query->where('sender_id', $adminId)
                  ->where('sender_type', $receiverType)
                  ->where('receiver_id', $userId)
                  ->where('receiver_type', User::class);
        })->orderBy('created_at', 'asc')->get();

        Log::info('getMessages called', [
            'userId' => $userId,
            'adminId' => $adminId,
            'receiverType' => $receiverType,
            'messageCount' => $messages->count(),
        ]);

        return response()->json(['data' => $messages]);
    }
}