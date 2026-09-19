<?php
require_once 'includes/auth.php';
requireLogin();
require_once 'includes/header.php';
include_once 'DataProvider.php';

$user = getCurrentUser();
$maKH = intval($user['MaKH']);

$conn = getDbConnection();
$stmt = $conn->prepare("SELECT hd.*, GREATEST(0, IFNULL(SUM(ct.SoLuong * ct.DonGia), 0) - IFNULL(hd.SoTienGiam, 0)) AS TongTien FROM hoadon hd LEFT JOIN chitiethd ct ON hd.MaHD = ct.MaHD WHERE hd.MaKH = ? GROUP BY hd.MaHD ORDER BY hd.MaHD DESC");
$stmt->bind_param("i", $maKH);
$stmt->execute();
$rs = $stmt->get_result();
?>

<style>
.history-wrapper {
    max-width: 960px;
    margin: 30px auto;
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.06);
}
.history-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
.history-table th, .history-table td {
    border: 1px solid #eef0f3;
    padding: 12px 14px;
    text-align: center;
    font-size: 14px;
}
.history-table th {
    background-color: #f8f9fa;
    color: #333;
    font-weight: 600;
}
.history-table tr:hover {
    background-color: #fafbfc;
}
.badge-status {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.status-done {
    background: #e8f8f5;
    color: #27ae60;
}
.status-shipping {
    background: #e0f2fe;
    color: #0284c7;
}
.status-pending {
    background: #fff9e6;
    color: #f39c12;
}
.status-cancelled {
    background: #fee2e2;
    color: #dc2626;
}
.btn-invoice-action {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    background: #2980b9;
    color: #fff;
    border-radius: 5px;
    font-size: 13px;
    text-decoration: none;
    transition: background 0.2s ease;
}
.btn-invoice-action:hover {
    background: #1f6391;
}
</style>

<div class="history-wrapper">
    <div style="border-bottom: 2px solid #f1f2f6; padding-bottom: 12px;">
        <h2 style="margin: 0; color: #2c3e50; font-size: 22px;">
            <i class="fa-solid fa-clock-rotate-left" style="color: #e74c3c;"></i> Lịch Sử Mua Hàng Của Bạn
        </h2>
        <p style="margin: 6px 0 0; color: #7f8c8d; font-size: 14px;">
            Xem lại các đơn hàng đã đặt và theo dõi tình trạng xử lý tại Sen Garden.
        </p>
    </div>

    <?php if ($rs && mysqli_num_rows($rs) > 0): ?>
        <table class="history-table">
            <thead>
                <tr>
                    <th>Mã Đơn</th>
                    <th>Ngày Đặt</th>
                    <th>Tổng Tiền</th>
                    <th>Thanh Toán</th>
                    <th>Tình Trạng</th>
                    <th>Hóa Đơn</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($hd = mysqli_fetch_assoc($rs)): ?>
                    <?php 
                        $rawDate = $hd['NgayDat'] ?? $hd['NgayHD'] ?? $hd['NgayGiao'] ?? null;
                        $formattedDate = $rawDate ? date('d/m/Y H:i', strtotime($rawDate)) : '---';
                        $tongTien = number_format(floatval($hd['TongTien'] ?? 0)) . ' đ';
                        $tinhTrang = intval($hd['TinhTrang'] ?? 0);
                        $pttt = $hd['PhuongThucTT'] ?? 'COD';
                    ?>
                    <tr>
                        <td><strong>#<?php echo $hd['MaHD']; ?></strong></td>
                        <td><?php echo $formattedDate; ?></td>
                        <td style="color: #e30019; font-weight: bold;"><?php echo $tongTien; ?></td>
                        <td>
                            <?php if ($pttt === 'VIETQR'): ?>
                                <span style="color: #0284c7; font-weight: 600; font-size: 12.5px;"><i class="fa-solid fa-qrcode"></i> VietQR (Techcombank)</span>
                            <?php else: ?>
                                <span style="color: #64748b; font-size: 12.5px;"><i class="fa-solid fa-money-bill"></i> Tiền mặt (COD)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($tinhTrang === 2): ?>
                                <span class="badge-status status-done"><i class="fa-solid fa-circle-check"></i> Đã giao</span>
                            <?php elseif ($tinhTrang === 1): ?>
                                <span class="badge-status status-shipping"><i class="fa-solid fa-truck-fast"></i> Đang giao</span>
                            <?php elseif ($tinhTrang === 3): ?>
                                <span class="badge-status status-cancelled"><i class="fa-solid fa-ban"></i> Đã hủy</span>
                            <?php else: ?>
                                <span class="badge-status status-pending"><i class="fa-solid fa-hourglass-half"></i> Chờ xử lý</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="admin/inhoadon.php?MaHD=<?php echo $hd['MaHD']; ?>" target="_blank" class="btn-invoice-action">
                                <i class="fa-solid fa-print"></i> Xem & In
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="text-align: center; padding: 45px 15px;">
            <i class="fa-solid fa-bag-shopping" style="font-size: 40px; color: #bdc3c7; margin-bottom: 12px; display: block;"></i>
            <p style="color: #7f8c8d; font-size: 15px; margin-bottom: 16px;">Bạn chưa thực hiện đơn hàng nào trên hệ thống.</p>
            <a href="index.php" style="display: inline-block; padding: 10px 22px; background: #e30019; color: #fff; text-decoration: none; border-radius: 5px; font-weight: 500;">
                Khám phá hoa tươi ngay
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>