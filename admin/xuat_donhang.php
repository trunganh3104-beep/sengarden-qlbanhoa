<?php
require_once '../includes/auth.php';

requireAdmin();

if (ob_get_level()) {
    ob_end_clean();
}

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
mysqli_set_charset($conn, "utf8mb4");

$sql = "SELECT hd.MaHD, hd.NgayDat, hd.DiaChiGiao, hd.NgayGiao, hd.TinhTrang, hd.PhuongThucTT, hd.LoiNhan,
               kh.HoTen, kh.DienThoai,
               GREATEST(0, IFNULL(SUM(ct.SoLuong * ct.DonGia), 0) - IFNULL(hd.SoTienGiam, 0)) AS TongTien
        FROM hoadon hd
        JOIN khachhang kh ON hd.MaKH = kh.MaKH
        LEFT JOIN chitiethd ct ON hd.MaHD = ct.MaHD
        GROUP BY hd.MaHD
        ORDER BY hd.MaHD DESC";
$result = mysqli_query($conn, $sql);

$filename = "BaoCao_DonHang_SenGarden_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

fputcsv($output, [
    'Mã Đơn Hàng',
    'Ngày Đặt',
    'Khách Hàng',
    'Số Điện Thoại',
    'Địa Chỉ Giao Hàng',
    'Phương Thức',
    'Lời Nhắn Thiệp',
    'Tổng Tiền (VNĐ)',
    'Trạng Thái'
]);

while ($row = mysqli_fetch_assoc($result)) {
    $st = intval($row['TinhTrang']);
    $statusText = 'Chờ xử lý';
    if ($st === 2) $statusText = 'Đã giao thành công';
    elseif ($st === 1) $statusText = 'Đang giao hàng';
    elseif ($st === 3) $statusText = 'Đã hủy';

    fputcsv($output, [
        '#' . $row['MaHD'],
        date('d/m/Y H:i', strtotime($row['NgayDat'])),
        $row['HoTen'] ?: 'Khách vãng lai',
        $row['DienThoai'] ?: '',
        $row['DiaChiGiao'] ?: ($row['NoiGiao'] ?? ''),
        $row['PhuongThucTT'] ?: 'COD',
        $row['LoiNhan'] ?: '',
        number_format($row['TongTien']) . ' đ',
        $statusText
    ]);
}

fclose($output);
mysqli_close($conn);
exit;
