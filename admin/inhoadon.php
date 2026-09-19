<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../DataProvider.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header("Location: ../dangnhap.php");
    exit;
}

$maHD = isset($_GET['MaHD']) ? intval($_GET['MaHD']) : 0;
if ($maHD <= 0) {
    die("Mã hóa đơn không hợp lệ!");
}

$user = getCurrentUser();

if (!isAdmin()) {
    $maKH = intval($user['MaKH']);
    $checkOwner = DataProvider::ExecuteQuery("SELECT MaHD FROM hoadon WHERE MaHD = $maHD AND MaKH = $maKH");
    if (!mysqli_fetch_assoc($checkOwner)) {
        die("Bạn không có quyền xem hóa đơn của đơn hàng này!");
    }
}

$sqlHD = "SELECT hd.*, kh.HoTen, kh.DienThoai, kh.Email, kh.DiaChi 
          FROM hoadon hd 
          JOIN khachhang kh ON hd.MaKH = kh.MaKH 
          WHERE hd.MaHD = $maHD";
$rsHD = DataProvider::ExecuteQuery($sqlHD);
$order = mysqli_fetch_assoc($rsHD);

if (!$order) {
    die("Không tìm thấy đơn hàng này!");
}

$sqlCT = "SELECT ct.*, h.TenHoa, h.GiaBan 
          FROM chitiethd ct 
          JOIN hoa h ON ct.MaHoa = h.MaHoa 
          WHERE ct.MaHD = $maHD";
$rsCT = DataProvider::ExecuteQuery($sqlCT);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn bán hàng #<?php echo $order['MaHD']; ?> - Sen Garden</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 15px;
            color: #111;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }
        .invoice-wrapper {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .shop-info h2 {
            margin: 0 0 5px 0;
            text-transform: uppercase;
            color: #c0392b;
            font-size: 22px;
        }
        .shop-info p {
            margin: 3px 0;
            font-size: 14px;
        }
        .invoice-title {
            text-align: center;
            margin-bottom: 25px;
        }
        .invoice-title h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .invoice-title span {
            font-style: italic;
            color: #555;
            font-size: 14px;
        }
        .customer-info {
            margin-bottom: 25px;
            line-height: 1.6;
        }
        .table-invoice {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .table-invoice th, .table-invoice td {
            border: 1px solid #333;
            padding: 10px;
            text-align: center;
        }
        .table-invoice th {
            background-color: #f2f2f2;
            text-transform: uppercase;
            font-size: 13px;
        }
        .total-row td {
            font-weight: bold;
            font-size: 16px;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            text-align: center;
            margin-top: 40px;
            padding: 0 20px;
        }
        .signature-box p {
            margin: 5px 0;
        }
        .signature-space {
            height: 80px;
        }
        .btn-controls {
            max-width: 800px;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-back {
            background: #7f8c8d;
            color: #fff;
        }
        .btn-print {
            background: #27ae60;
            color: #fff;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .invoice-wrapper {
                box-shadow: none;
                padding: 0;
                width: 100%;
            }
            .btn-controls {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="btn-controls">
    <?php if (isAdmin()): ?>
        <a href="quanlydonhang.php" class="btn btn-back">← Quay lại danh sách</a>
    <?php else: ?>
        <a href="../lichsudonhang.php" class="btn btn-back">← Quay lại lịch sử đơn hàng</a>
    <?php endif; ?>
    <button onclick="window.print();" class="btn btn-print">🖨 In hóa đơn này</button>
</div>

<div class="invoice-wrapper">
    <div class="invoice-header">
        <div class="shop-info">
            <h2>Shop Hoa Tươi Sen Garden</h2>
            <p>Địa chỉ: 280 An Dương Vương, Phường 4, Quận 5, TP. Hồ Chí Minh</p>
            <p>Điện thoại: 0989 366 990 - Website: sengarden.vn</p>
        </div>
        <div style="text-align: right;">
            <p style="margin: 0;"><strong>Mã ĐH:</strong> #<?php echo $order['MaHD']; ?></p>
            <p style="margin: 5px 0 0 0;"><strong>Ngày in:</strong> <?php echo date('d/m/Y H:i'); ?></p>
        </div>
    </div>

    <div class="invoice-title">
        <h1>Hóa Đơn Bán Hàng</h1>
        <span>Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['NgayDat'])); ?></span>
    </div>

    <div class="customer-info">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <strong>Khách hàng:</strong> <?php echo htmlspecialchars($order['HoTen']); ?><br>
                    <strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['DienThoai']); ?><br>
                    <strong>Email:</strong> <?php echo htmlspecialchars($order['Email']); ?>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <strong>Địa chỉ giao hàng:</strong> <?php echo htmlspecialchars(!empty($order['DiaChiGiao']) ? $order['DiaChiGiao'] : ($order['NoiGiao'] ?? '')); ?><br>
                    <strong>Ngày hẹn giao:</strong> <?php echo !empty($order['NgayGiao']) ? date('d/m/Y', strtotime($order['NgayGiao'])) : 'Trong ngày'; ?><br>
                    <strong>Khung giờ hẹn:</strong> <?php echo !empty($order['GioGiao']) ? htmlspecialchars($order['GioGiao']) : 'Tiêu chuẩn'; ?><br>
                    <strong>Phương thức TT:</strong> <?php echo (isset($order['PhuongThucTT']) && $order['PhuongThucTT'] === 'VIETQR') ? '<span style="color:#d35400; font-weight:bold;">Chuyển khoản VietQR (Techcombank)</span>' : 'Tiền mặt (COD)'; ?><br>
                    <strong>Tình trạng:</strong> 
                    <?php 
                        $st = intval($order['TinhTrang'] ?? 0);
                        if ($st === 2) echo 'Đã giao thành công';
                        elseif ($st === 1) echo 'Đang giao hàng';
                        elseif ($st === 3) echo 'Đã hủy';
                        else echo 'Chờ xử lý / Đang chuẩn bị';
                    ?>
                    <?php if (!empty($order['LoiNhan'])): ?>
                        <div style="margin-top: 8px; padding: 6px 10px; background: #fffde7; border: 1px dashed #f57f17; border-radius: 4px;">
                            <strong>💌 Thiệp kèm theo:</strong> <em>"<?php echo htmlspecialchars($order['LoiNhan']); ?>"</em>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <table class="table-invoice">
        <thead>
            <tr>
                <th width="40">STT</th>
                <th>Tên sản phẩm</th>
                <th width="120">Đơn giá</th>
                <th width="70">SL</th>
                <th width="140">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $stt = 1;
            $tongTien = 0;
            while ($row = mysqli_fetch_assoc($rsCT)): 
                $donGia = (isset($row['DonGia']) && floatval($row['DonGia']) > 0) ? floatval($row['DonGia']) : floatval($row['GiaBan']);
                $thanhTien = $donGia * $row['SoLuong'];
                $tongTien += $thanhTien;
            ?>
                <tr>
                    <td><?php echo $stt++; ?></td>
                    <td style="text-align: left; font-weight: bold;"><?php echo htmlspecialchars($row['TenHoa']); ?></td>
                    <td><?php echo number_format($donGia); ?> đ</td>
                    <td><?php echo $row['SoLuong']; ?></td>
                    <td style="text-align: right; font-weight: bold;"><?php echo number_format($thanhTien); ?> đ</td>
                </tr>
            <?php endwhile; ?>
            <?php 
                $soTienGiam = floatval($order['SoTienGiam'] ?? 0);
                if ($soTienGiam > 0): 
            ?>
                <tr>
                    <td colspan="4" style="text-align: right; color: #16a34a; font-weight: bold;">Giảm giá voucher:</td>
                    <td style="text-align: right; color: #16a34a; font-weight: bold;">-<?php echo number_format($soTienGiam); ?> đ</td>
                </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td colspan="4" style="text-align: right;">Tổng thanh toán:</td>
                <td style="text-align: right; color: #c0392b;"><?php echo number_format(max(0, $tongTien - $soTienGiam)); ?> đ</td>
            </tr>
        </tbody>
    </table>

    <div class="signature-section">
        <div class="signature-box">
            <p><strong>Khách hàng</strong></p>
            <p><i>(Ký và ghi rõ họ tên)</i></p>
            <div class="signature-space"></div>
        </div>
        <div class="signature-box">
            <p><strong>Người giao hàng</strong></p>
            <p><i>(Ký và ghi rõ họ tên)</i></p>
            <div class="signature-space"></div>
        </div>
        <div class="signature-box">
            <p><strong>Người lập phiếu</strong></p>
            <p><i>(Ký và ghi rõ họ tên)</i></p>
            <div class="signature-space"></div>
            <p><strong>Admin</strong></p>
        </div>
    </div>
</div>

</body>
</html>