@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="container py-5">
    <!-- Tiêu đề -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold text-primary">Thống kê Đơn hàng</h1>
        <div class="text-muted">Cập nhật: <span id="last-updated">{{ now()->format('d/m/Y H:i') }}</span></div>
    </div>

    <!-- FORM LỌC THEO KHOẢNG NGÀY -->
    <div class="card mb-5 shadow-lg border-0 rounded-3">
        <div class="card-body p-4">
            <form id="filter-form" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="start_date" class="form-label fw-bold text-secondary">Từ ngày</label>
                    <input type="text" name="start_date" id="start_date" value="{{ $start_date ?? '' }}" 
                           class="form-control shadow-sm" placeholder="dd/mm/yyyy" autocomplete="off" readonly>
                </div>
                <div class="col-auto">
                    <label for="end_date" class="form-label fw-bold text-secondary">Đến ngày</label>
                    <input type="text" name="end_date" id="end_date" value="{{ $end_date ?? '' }}" 
                           class="form-control shadow-sm" placeholder="dd/mm/yyyy" autocomplete="off" readonly>
                </div>
                <div class="col-auto">
                    <label for="view_type" class="form-label fw-bold text-secondary">Hiển thị theo</label>
                    <select name="view_type" id="view_type" class="form-select shadow-sm">
                        <option value="day"   {{ (isset($view_type) && $view_type == 'day')   ? 'selected' : '' }}>Theo ngày</option>
                        <option value="week"  {{ (isset($view_type) && $view_type == 'week')  ? 'selected' : '' }}>Theo tuần</option>
                        <option value="month" {{ (isset($view_type) && $view_type == 'month') ? 'selected' : '' }}>Theo tháng</option>
                    </select>
                </div>
                <div class="col-auto mt-4">
                    <button type="submit" class="btn btn-primary shadow-sm" id="filter-btn">
                        <i class="bi bi-calendar me-2"></i>Lọc thống kê
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- THỐNG KÊ TỔNG QUAN -->
    <div class="row mb-5 g-4">
        <div class="col-md-3 col-sm-6">
            <div class="card text-center p-4 bg-gradient-success text-white shadow-lg border-0 rounded-3 hover-scale">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-check-circle-fill me-2" style="font-size: 1.5rem;"></i>
                    <h5 class="mb-0">Đã giao</h5>
                </div>
                <h3 class="fw-bold" id="delivered-count">{{ $deliveredCount }}</h3>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card text-center p-4 bg-gradient-primary text-white shadow-lg border-0 rounded-3 hover-scale">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-truck me-2" style="font-size: 1.5rem;"></i>
                    <h5 class="mb-0">Đang giao</h5>
                </div>
                <h3 class="fw-bold" id="shipping-count">{{ $shippingCount }}</h3>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card text-center p-4 bg-gradient-warning text-dark shadow-lg border-0 rounded-3 hover-scale">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-hourglass-split me-2" style="font-size: 1.5rem;"></i>
                    <h5 class="mb-0">Chờ xác nhận</h5>
                </div>
                <h3 class="fw-bold" id="pending-count">{{ $pendingCount }}</h3>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card text-center p-4 bg-gradient-danger text-white shadow-lg border-0 rounded-3 hover-scale">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-x-circle-fill me-2" style="font-size: 1.5rem;"></i>
                    <h5 class="mb-0">Đã hủy</h5>
                </div>
                <h3 class="fw-bold" id="canceled-count">{{ $canceledCount }}</h3>
            </div>
        </div>
        <div class="col-md-12 mt-3">
            <div class="card text-center p-4 bg-gradient-info text-white shadow-lg border-0 rounded-3">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-currency-dollar me-2" style="font-size: 1.5rem;"></i>
                    <h5 class="mb-0">Tổng doanh thu</h5>
                </div>
                <h3 class="fw-bold" id="total-revenue">{{ number_format($totalRevenue, 0, ',', '.') }} VNĐ</h3>
            </div>
        </div>
    </div>

    <!-- BIỂU ĐỒ DOANH THU THEO KHOẢNG THỜI GIAN (Line Chart) -->
    <div class="card mb-5 shadow-lg border-0 rounded-3">
        <div class="card-body p-4">
            <h4 class="card-title fw-bold text-primary mb-4">
                Doanh thu theo {{ $view_type == 'day' ? 'ngày' : ($view_type == 'week' ? 'tuần' : 'tháng') }}
            </h4>
            <canvas id="revenueChart" height="150"></canvas>
        </div>
    </div>

    <!-- BIỂU ĐỒ ĐƠN HÀNG ĐÃ HỦY THEO KHOẢNG THỜI GIAN (Bar Chart) -->
    <div class="card mb-5 shadow-lg border-0 rounded-3">
        <div class="card-body p-4">
            <h4 class="card-title fw-bold text-primary mb-4">
                Đơn hàng đã hủy theo {{ $view_type == 'day' ? 'ngày' : ($view_type == 'week' ? 'tuần' : 'tháng') }}
            </h4>
            <canvas id="canceledChart" height="150"></canvas>
        </div>
    </div>

    <!-- TỶ LỆ ĐƠN HÀNG THEO TRẠNG THÁI (Pie Chart) -->
    <div class="card mb-5 shadow-lg border-0 rounded-3">
        <div class="card-body p-4">
            <h4 class="card-title fw-bold text-primary mb-4">Tỷ lệ đơn hàng theo trạng thái</h4>
            <div class="d-flex justify-content-center">
                <canvas id="statusPieChart" height="100" style="max-width: 300px; max-height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- TOP SẢN PHẨM BÁN CHẠY -->
    <div class="card mb-5 shadow-lg border-0 rounded-3">
        <div class="card-body p-4">
            <h4 class="card-title fw-bold text-primary mb-4">Top 5 sản phẩm bán chạy</h4>
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle" id="top-products-table">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" class="text-center">Tên sản phẩm</th>
                            <th scope="col" class="text-center">Số lượng bán</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $index => $product)
                            <tr class="{{ $index % 2 == 0 ? 'table-light' : 'table-secondary' }}">
                                <td class="text-center">{{ $product->ten_san_pham }}</td>
                                <td class="text-center fw-bold text-primary">{{ $product->total_sold }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- KHÁCH HÀNG MỚI THEO KHOẢNG THỜI GIAN (Line Chart) -->
    <div class="card mb-5 shadow-lg border-0 rounded-3">
        <div class="card-body p-4">
            <h4 class="card-title fw-bold text-primary mb-4">
                Khách hàng mới theo {{ $view_type == 'day' ? 'ngày' : ($view_type == 'week' ? 'tuần' : 'tháng') }}
            </h4>
            <canvas id="newCustomersChart" height="150"></canvas>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    /* Hiệu ứng hover cho thẻ thống kê */
    .hover-scale {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .hover-scale:hover {
        transform: scale(1.05);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15) !important;
    }

    /* Gradient background cho thẻ thống kê */
    .bg-gradient-success {
        background: linear-gradient(135deg, #28a745, #34c759);
    }
    .bg-gradient-primary {
        background: linear-gradient(135deg, #007bff, #00aaff);
    }
    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffc107, #ffda6a);
    }
    .bg-gradient-danger {
        background: linear-gradient(135deg, #dc3545, #ff5e6e);
    }
    .bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8, #1ac6e0);
    }

    /* Căn chỉnh biểu đồ */
    canvas {
        max-width: 100%;
    }

    /* Tùy chỉnh bảng */
    .table th, .table td {
        vertical-align: middle;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.1);
        transition: background-color 0.3s ease;
    }

    /* Hiệu ứng loading */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    .spinner-border {
        width: 3rem;
        height: 3rem;
    }
</style>
@endsection

@section('scripts')
{{-- jQuery UI Datepicker --}}
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    $(function(){
        // Datepicker
        $("#start_date, #end_date").datepicker({
            dateFormat: "dd/mm/yy",
            changeMonth: true,
            changeYear: true,
            yearRange: "2000:2030"
        });

        // Khởi tạo các biểu đồ (nếu có dữ liệu ban đầu)
        let revenueChart, canceledChart, statusPieChart, newCustomersChart;

        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const canceledCtx = document.getElementById('canceledChart').getContext('2d');
        const statusCtx   = document.getElementById('statusPieChart').getContext('2d');
        const newCusCtx   = document.getElementById('newCustomersChart').getContext('2d');

        // Biểu đồ doanh thu
        revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: @json($periods ?? []),
                datasets: [
                    {
                        label: 'Doanh thu hiện tại (VNĐ)',
                        data: @json($revenueByPeriod->pluck('total_revenue') ?? []),
                        borderColor: 'rgba(54, 162, 235, 1)', // Xanh dương
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: 'rgba(54, 162, 235, 1)',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Doanh thu trước đó (VNĐ)',
                        data: @json($previousRevenueByPeriod->pluck('total_revenue') ?? []),
                        borderColor: 'rgba(153, 102, 255, 1)', // Tím
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: 'rgba(153, 102, 255, 1)',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('vi-VN') + ' VNĐ';
                            }
                        }
                    }
                },
                plugins: {
                    legend: { display: true },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + 
                                    context.raw.toLocaleString('vi-VN') + ' VNĐ';
                            }
                        }
                    }
                },
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart'
                }
            }
        });

        // Biểu đồ đơn hàng hủy
        canceledChart = new Chart(canceledCtx, {
            type: 'bar',
            data: {
                labels: @json($periods ?? []),
                datasets: [{
                    label: 'Đơn hàng đã hủy',
                    data: @json($canceledByPeriod->pluck('total_canceled') ?? []),
                    backgroundColor: 'rgba(255, 99, 132, 0.7)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1,
                    hoverBackgroundColor: 'rgba(255, 99, 132, 0.9)'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    }
                },
                plugins: {
                    legend: { display: true },
                },
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart'
                }
            }
        });

        // Biểu đồ pie - trạng thái đơn
        statusPieChart = new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: ['Đã giao', 'Đang giao', 'Chờ xác nhận', 'Đã hủy'],
                datasets: [{
                    data: [
                        {{ $statusPercentages['da_giao'] ?? 0 }},
                        {{ $statusPercentages['dang_giao'] ?? 0 }},
                        {{ $statusPercentages['cho_xac_nhan'] ?? 0 }},
                        {{ $statusPercentages['huy'] ?? 0 }}
                    ],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(255, 99, 132, 0.7)'
                    ],
                    borderColor: ['#fff'],
                    borderWidth: 2,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.raw.toFixed(2) + '%';
                            }
                        }
                    }
                },
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart'
                }
            }
        });

        // Biểu đồ khách hàng mới
        newCustomersChart = new Chart(newCusCtx, {
            type: 'line',
            data: {
                labels: @json($periods ?? []),
                datasets: [
                    {
                        label: 'Khách hàng mới hiện tại',
                        data: @json($newCustomersByPeriod->pluck('total_new_customers') ?? []),
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: 'rgba(54, 162, 235, 1)',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Khách hàng mới trước đó',
                        data: @json($previousNewCustomersByPeriod->pluck('total_new_customers') ?? []),
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: 'rgba(153, 102, 255, 1)',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    }
                },
                plugins: {
                    legend: { display: true },
                },
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart'
                }
            }
        });

        // Submit form lọc => AJAX
        $('#filter-form').on('submit', function(e) {
            e.preventDefault();

            // Hiển thị overlay loading
            $('.loading-overlay').show();

            const startDate = $('#start_date').val();
            const endDate   = $('#end_date').val();
            const viewType  = $('#view_type').val();

            $.ajax({
                url: '{{ route("admin.dashboard.filter") }}',
                method: 'GET',
                data: {
                    start_date: startDate,
                    end_date: endDate,
                    view_type: viewType
                },
                success: function(response) {
                    // Cập nhật thống kê tổng quan
                    $('#delivered-count').text(response.deliveredCount || 0);
                    $('#shipping-count').text(response.shippingCount || 0);
                    $('#pending-count').text(response.pendingCount || 0);
                    $('#canceled-count').text(response.canceledCount || 0);
                    $('#total-revenue').text(
                        new Intl.NumberFormat('vi-VN').format(response.totalRevenue || 0) + ' VNĐ'
                    );

                    // Cập nhật thời gian cập nhật
                    $('#last-updated').text(
                        new Date().toLocaleString('vi-VN', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        })
                    );

                    // Cập nhật biểu đồ doanh thu
                    revenueChart.data.labels = response.periods || [];
                    revenueChart.data.datasets[0].data = (response.revenueByPeriod || []).map(item => item.total_revenue);
                    revenueChart.data.datasets[1].data = (response.previousRevenueByPeriod || []).map(item => item.total_revenue);
                    revenueChart.update();

                    // Cập nhật biểu đồ đơn hàng đã hủy
                    canceledChart.data.labels = response.periods || [];
                    canceledChart.data.datasets[0].data = (response.canceledByPeriod || []).map(item => item.total_canceled);
                    canceledChart.update();

                    // Cập nhật biểu đồ pie - trạng thái đơn
                    statusPieChart.data.datasets[0].data = [
                        response.statusPercentages.da_giao || 0,
                        response.statusPercentages.dang_giao || 0,
                        response.statusPercentages.cho_xac_nhan || 0,
                        response.statusPercentages.huy || 0
                    ];
                    statusPieChart.update();

                    // Cập nhật biểu đồ khách hàng mới
                    newCustomersChart.data.labels = response.periods || [];
                    newCustomersChart.data.datasets[0].data = (response.newCustomersByPeriod || []).map(item => item.total_new_customers);
                    newCustomersChart.data.datasets[1].data = (response.previousNewCustomersByPeriod || []).map(item => item.total_new_customers);
                    newCustomersChart.update();

                    // Cập nhật bảng Top 5 sản phẩm
                    const tbody = $('#top-products-table tbody');
                    tbody.empty();
                    if (response.topProducts && response.topProducts.length > 0) {
                        response.topProducts.forEach((product, index) => {
                            const rowClass = index % 2 === 0 ? 'table-light' : 'table-secondary';
                            tbody.append(`
                                <tr class="${rowClass}">
                                    <td class="text-center">${product.ten_san_pham}</td>
                                    <td class="text-center fw-bold text-primary">${product.total_sold}</td>
                                </tr>
                            `);
                        });
                    } else {
                        tbody.append(`
                            <tr>
                                <td colspan="2" class="text-center text-muted">Không có dữ liệu</td>
                            </tr>
                        `);
                    }

                    // Cập nhật tiêu đề biểu đồ theo view_type
                    const periodText = viewType === 'day' ? 'ngày' : (viewType === 'week' ? 'tuần' : 'tháng');
                    $('h4.card-title:contains("Doanh thu")').text(`Doanh thu theo ${periodText}`);
                    $('h4.card-title:contains("Đơn hàng đã hủy")').text(`Đơn hàng đã hủy theo ${periodText}`);
                    $('h4.card-title:contains("Khách hàng mới")').text(`Khách hàng mới theo ${periodText}`);

                    // Ẩn overlay loading
                    $('.loading-overlay').hide();
                },
                error: function(xhr, status, error) {
                    console.error('Lỗi AJAX:', status, error);
                    alert('Đã có lỗi xảy ra. Vui lòng thử lại!');
                    $('.loading-overlay').hide();
                }
            });
        });
    });
</script>

<!-- Thêm overlay loading -->
<div class="loading-overlay">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Đang tải...</span>
    </div>
</div>
@endsection
