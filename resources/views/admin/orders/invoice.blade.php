<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        @font-face {
            font-family: 'DejaVuSans';
            font-style: normal;
            font-weight: normal;
            src: url({{ public_path('storage/fonts/DejaVuSans.ttf') }}) format('truetype');
        }

        body {
            font-family: 'DejaVuSans', sans-serif;
            font-size: 12pt;
            color: #333;
        }
        .invoice-box {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            line-height: 24px;
        }
        .invoice-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-box table td {
            padding: 5px 10px;
            vertical-align: top;
        }
        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }
        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }
        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #000;
        }
        .invoice-box table tr.top table td.info {
            text-align: right;
        }
        .invoice-box table tr.heading td {
            background: #f5f5f5;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }
        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }
        .invoice-box table tr.item.last td {
            border-bottom: none;
        }
        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .company-info {
            font-size: 10pt;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <!-- Thông tin cửa hàng (thêm tùy chọn) -->
        <div class="company-info">
            <strong>CÔNG TY XYZ</strong><br>
            Địa chỉ: 123 Đường ABC, Quận 1, TP. Hồ Chí Minh<br>
            SĐT: 0909 123 456 | Email: info@xyz.com<br>
            Mã số thuế: 123456789
        </div>

        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title"><b>HÓA ĐƠN</b></td>
                            <td class="info">
                                Ngày xuất: {{ $date }}<br>
                                ID Đơn hàng: #{{ $order->id_donHang }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                <strong>Thông tin người nhận:</strong><br>
                                Họ và tên: {{ $order->ten_nguoiNhan }}<br>
                                SĐT: {{ $order->sdt_nhanHang }}<br>
                                Địa chỉ: {{ $order->ten_nha }}, {{ $order->xa }}, {{ $order->huyen }}, {{ $order->tinh }}
                            </td>
                            <td>
                                <strong>Thông tin đơn hàng:</strong><br>
                                Ngày đặt: {{ $order->created_at->format('d/m/Y H:i:s') }}<br>
                                Phương thức thanh toán: {{ $order->phuongThucThanhToan }}<br>
                                Ngày dự kiến giao: {{ $order->ngay_du_kien_giao ? $order->ngay_du_kien_giao->format('d/m/Y') : '-' }}<br>
                                Ngày giao thực tế: {{ $order->ngay_giao_thuc_te ? $order->ngay_giao_thuc_te->format('d/m/Y') : '-' }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="heading">
                <td>Sản phẩm</td>
                <td class="text-right">Giá</td>
            </tr>

            @foreach($order->chiTietDonHang as $detail)
                <tr class="item @if($loop->last) last @endif">
                    <td>
                        {{ $detail->sanPham->tenSanPham }}
                        @if($detail->variation)
                            ({{ $detail->variation->color }}, {{ $detail->variation->size }})
                        @endif
                        - x{{ $detail->soLuong }}
                    </td>
                    <td class="text-right">{{ number_format($detail->gia * $detail->soLuong, 0, ',', '.') }} VNĐ</td>
                </tr>
            @endforeach

            <tr class="total">
                <td>Tổng cộng:</td>
                <td class="text-right">{{ number_format($order->tongTien, 0, ',', '.') }} VNĐ</td>
            </tr>
        </table>
    </div>
</body>
</html>