<?php
include_once '../DataProvider.php';

if (isset($_GET['action']) && $_GET['action'] === 'ajax_update' && isset($_GET['MaHD']) && isset($_GET['status'])) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    $maHD = intval($_GET['MaHD']);
    $newStatus = intval($_GET['status']);
    $res = ['success' => false];

    if ($newStatus >= 0 && $newStatus <= 3) {
        DataProvider::ExecuteQuery("UPDATE hoadon SET TinhTrang = $newStatus WHERE MaHD = $maHD");
        $badgeHtml = '';
        $filterStatus = 'pending';
        if ($newStatus === 1) {
            $badgeHtml = '<span class="badge-pill info"><i class="fa-solid fa-truck-fast"></i> Đang giao</span>';
            $filterStatus = 'shipping';
        } elseif ($newStatus === 2) {
            $badgeHtml = '<span class="badge-pill success"><i class="fa-solid fa-check"></i> Đã giao</span>';
            $filterStatus = 'completed';
        } elseif ($newStatus === 3) {
            $badgeHtml = '<span class="badge-pill danger"><i class="fa-solid fa-xmark"></i> Đã hủy</span>';
            $filterStatus = 'cancelled';
        } else {
            $badgeHtml = '<span class="badge-pill warning"><i class="fa-solid fa-clock"></i> Chờ xử lý</span>';
            $filterStatus = 'pending';
        }
        $res = [
            'success' => true,
            'maHD' => $maHD,
            'status' => $newStatus,
            'badge' => $badgeHtml,
            'filterStatus' => $filterStatus
        ];
    }
    echo json_encode($res);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'ajax_view' && isset($_GET['MaHD'])) {
    if (ob_get_length()) ob_clean();
    $viewMaHD = intval($_GET['MaHD']);

    $sqlHD = "SELECT hd.*, kh.HoTen, kh.DienThoai, kh.Email, kh.DiaChi 
              FROM hoadon hd 
              JOIN khachhang kh ON hd.MaKH = kh.MaKH 
              WHERE hd.MaHD = $viewMaHD";
    $order = mysqli_fetch_assoc(DataProvider::ExecuteQuery($sqlHD));

    $sqlCT = "SELECT ct.*, h.TenHoa, h.GiaBan, h.Hinh 
              FROM chitiethd ct 
              JOIN hoa h ON ct.MaHoa = h.MaHoa 
              WHERE ct.MaHD = $viewMaHD";
    $rsCT = DataProvider::ExecuteQuery($sqlCT);
    $tamTinh = 0;
    $soTienGiam = ($order && !empty($order['SoTienGiam'])) ? floatval($order['SoTienGiam']) : 0;
    $diaChiGiao = ($order && !empty($order['DiaChiGiao'])) ? $order['DiaChiGiao'] : ($order ? $order['NoiGiao'] : '');
    $ngayGiao = ($order && !empty($order['NgayGiao'])) ? date('d/m/Y', strtotime($order['NgayGiao'])) : '';
    ?>
    <div class="order-detail-card" style="position: relative;">
        <button type="button" id="btn-close-detail" style="position: absolute; top: 18px; right: 18px; border: none; background: #f1f5f9; width: 32px; height: 32px; border-radius: 50%; color: #64748b; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease;">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px; margin-bottom: 20px; padding-right: 40px;">
            <div>
                <span class="badge-pill info" style="margin-bottom: 6px;">Phiếu Chi Tiết Đơn Hàng</span>
                <h3 style="margin: 0; font-size: 20px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    Đơn hàng #HD-<?php echo $viewMaHD; ?>
                    <?php 
                    $tt = $order ? intval($order['TinhTrang']) : 0;
                    if ($tt === 1): ?>
                        <span class="badge-pill info"><i class="fa-solid fa-truck-fast"></i> Đang giao</span>
                    <?php elseif ($tt === 2): ?>
                        <span class="badge-pill success"><i class="fa-solid fa-check"></i> Đã giao</span>
                    <?php elseif ($tt === 3): ?>
                        <span class="badge-pill danger"><i class="fa-solid fa-xmark"></i> Đã hủy</span>
                    <?php else: ?>
                        <span class="badge-pill warning"><i class="fa-solid fa-clock"></i> Chờ xử lý</span>
                    <?php endif; ?>
                </h3>
                <div style="font-size: 13px; color: #64748b; margin-top: 4px;">
                    Đặt ngày: <?php echo date('d/m/Y H:i', strtotime($order['NgayDat'])); ?>
                </div>
            </div>
            <div>
                <a href="inhoadon.php?MaHD=<?php echo $viewMaHD; ?>" target="_blank" class="admin-btn admin-btn-outline admin-btn-sm" style="color: #4f46e5 !important;">
                    <i class="fa-solid fa-print"></i> In Hóa Đơn Khổ A4
                </a>
            </div>
        </div>

        <?php if ($order): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; background: #f8fafc; padding: 16px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #e2e8f0;">
                <div>
                    <div style="font-size: 12px; text-transform: uppercase; color: #94a3b8; font-weight: 700;">Người nhận hàng</div>
                    <div style="font-size: 14.5px; font-weight: 700; color: #0f172a; margin-top: 2px;">
                        <i class="fa-solid fa-user" style="color: #6366f1; margin-right: 5px;"></i> <?php echo htmlspecialchars($order['HoTen']); ?>
                    </div>
                    <div style="font-size: 13px; color: #475569; margin-top: 3px;">
                        <i class="fa-solid fa-phone" style="color: #10b981; margin-right: 5px;"></i> <?php echo htmlspecialchars($order['DienThoai']); ?>
                    </div>
                    <div style="font-size: 13px; color: #475569; margin-top: 3px;">
                        <i class="fa-solid fa-location-dot" style="color: #e11d48; margin-right: 5px;"></i> <?php echo htmlspecialchars($diaChiGiao); ?>
                    </div>
                    <?php if (!empty($ngayGiao)): ?>
                        <div style="font-size: 12.5px; color: #4338ca; margin-top: 3px; font-weight: 600;">
                            <i class="fa-regular fa-calendar-check" style="margin-right: 5px;"></i> Ngày giao dự kiến: <?php echo $ngayGiao; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <div style="font-size: 12px; text-transform: uppercase; color: #94a3b8; font-weight: 700;">Vận chuyển & Thanh toán</div>
                    <div style="font-size: 13.5px; color: #0f172a; margin-top: 2px;">
                        <strong>Khung giờ:</strong> <?php echo !empty($order['GioGiao']) ? htmlspecialchars($order['GioGiao']) : 'Giao tiêu chuẩn trong ngày'; ?>
                    </div>
                    <div style="font-size: 13.5px; margin-top: 4px;">
                        <strong>Hình thức:</strong> 
                        <?php if (isset($order['PhuongThucTT']) && $order['PhuongThucTT'] === 'VIETQR'): ?>
                            <span class="badge-pill warning" style="font-weight: 700;">
                                <i class="fa-solid fa-qrcode"></i> VietQR Techcombank
                            </span>
                        <?php else: ?>
                            <span class="badge-pill slate">
                                <i class="fa-solid fa-money-bill-wave"></i> COD Tiền mặt
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($order['LoiNhan'])): ?>
                        <div style="margin-top: 10px; padding: 10px 12px; background: #fffbeb; border: 1px dashed #f59e0b; border-radius: 8px; font-size: 13px; color: #92400e;">
                            <strong style="display: block; margin-bottom: 2px;"><i class="fa-solid fa-envelope-open-text"></i> Lời chúc kèm theo:</strong>
                            <em>"<?php echo htmlspecialchars($order['LoiNhan']); ?>"</em>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="60">Ảnh</th>
                        <th>Sản phẩm hoa</th>
                        <th width="120" style="text-align: right;">Đơn giá</th>
                        <th width="80" style="text-align: center;">Số lượng</th>
                        <th width="140" style="text-align: right;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = mysqli_fetch_assoc($rsCT)): 
                        $itemPrice = (isset($item['DonGia']) && floatval($item['DonGia']) > 0) ? floatval($item['DonGia']) : floatval($item['GiaBan']);
                        $thanhTien = $itemPrice * $item['SoLuong'];
                        $tamTinh += $thanhTien;
                    ?>
                        <tr>
                            <td>
                                <img src="../hoa/<?php echo htmlspecialchars($item['Hinh']); ?>" width="44" height="44" style="object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0;" onerror="this.src='../images/no-image.png'">
                            </td>
                            <td>
                                <strong style="font-size: 14px; color: #0f172a;"><?php echo htmlspecialchars($item['TenHoa']); ?></strong>
                            </td>
                            <td style="text-align: right;"><?php echo number_format($itemPrice); ?> đ</td>
                            <td style="text-align: center; font-weight: 700;"><?php echo $item['SoLuong']; ?></td>
                            <td style="text-align: right; color: #e11d48; font-weight: 700;"><?php echo number_format($thanhTien); ?> đ</td>
                        </tr>
                    <?php endwhile; ?>
                    <?php 
                    $tongThanhToan = max(0, $tamTinh - $soTienGiam);
                    ?>
                    <?php if ($soTienGiam > 0): ?>
                        <tr style="background: #f8fafc;">
                            <td colspan="4" style="text-align: right; font-weight: 600; font-size: 13px; color: #64748b;">
                                Tạm tính:
                            </td>
                            <td style="text-align: right; font-weight: 700; font-size: 14px; color: #334155;">
                                <?php echo number_format($tamTinh); ?> đ
                            </td>
                        </tr>
                        <tr style="background: #f8fafc;">
                            <td colspan="4" style="text-align: right; font-weight: 600; font-size: 13px; color: #16a34a;">
                                Voucher giảm giá (<?php echo htmlspecialchars($order['MaVoucher'] ?? ''); ?>):
                            </td>
                            <td style="text-align: right; font-weight: 700; font-size: 14px; color: #16a34a;">
                                -<?php echo number_format($soTienGiam); ?> đ
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr style="background: #f8fafc;">
                        <td colspan="4" style="text-align: right; font-weight: 700; font-size: 14px; text-transform: uppercase;">
                            Tổng thanh toán đơn hàng:
                        </td>
                        <td style="text-align: right; color: #e11d48; font-weight: 800; font-size: 18px;">
                            <?php echo number_format($tongThanhToan); ?> đ
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    exit;
}

require_once '../includes/auth.php';
requireAdmin();
require_once '../includes/header.php';
include_once 'nav.php';

$sqlHD = "SELECT hd.*, kh.HoTen, kh.DienThoai,
                 GREATEST(0, IFNULL(SUM(ct.SoLuong * IF(ct.DonGia > 0, ct.DonGia, h.GiaBan)), 0) - IFNULL(hd.SoTienGiam, 0)) AS TongTien
          FROM hoadon hd 
          JOIN khachhang kh ON hd.MaKH = kh.MaKH 
          LEFT JOIN chitiethd ct ON hd.MaHD = ct.MaHD
          LEFT JOIN hoa h ON ct.MaHoa = h.MaHoa
          GROUP BY hd.MaHD
          ORDER BY hd.MaHD DESC";
$rsHD = DataProvider::ExecuteQuery($sqlHD);
$allOrders = [];
$countPending = 0;
$countShipping = 0;
$countCompleted = 0;
$countCancelled = 0;
$countVietQR = 0;
$countCOD = 0;

while ($r = mysqli_fetch_assoc($rsHD)) {
    $allOrders[] = $r;
    $tt = intval($r['TinhTrang']);
    if ($tt === 1) $countShipping++;
    elseif ($tt === 2) $countCompleted++;
    elseif ($tt === 3) $countCancelled++;
    else $countPending++;

    if (isset($r['PhuongThucTT']) && $r['PhuongThucTT'] === 'VIETQR') $countVietQR++;
    else $countCOD++;
}
$totalOrders = count($allOrders);
?>

<div class="admin-container">
    <div class="admin-page-header">
        <div class="header-left">
            <h2>
                <i class="fa-solid fa-file-invoice-dollar" style="color: #e11d48;"></i> Quản Lý Đơn Đặt Hàng
            </h2>
            <p>Theo dõi xử lý, in phiếu vận chuyển và duyệt đơn hàng cho khách hàng</p>
        </div>
    </div>

    <div id="detail-box" style="display: none;"></div>

    <div class="admin-toolbar">
        <div class="admin-toolbar-left">
            <div class="admin-tabs" id="order-tabs">
                <button type="button" class="admin-tab-btn active" data-filter="all">
                    Tất cả <span class="badge-pill slate" style="padding: 2px 7px;"><?php echo $totalOrders; ?></span>
                </button>
                <button type="button" class="admin-tab-btn" data-filter="pending">
                    ⏳ Chờ xử lý <span class="badge-pill warning" style="padding: 2px 7px;"><?php echo $countPending; ?></span>
                </button>
                <button type="button" class="admin-tab-btn" data-filter="shipping">
                    🚚 Đang giao <span class="badge-pill info" style="padding: 2px 7px;"><?php echo $countShipping; ?></span>
                </button>
                <button type="button" class="admin-tab-btn" data-filter="completed">
                    ✔ Đã giao <span class="badge-pill success" style="padding: 2px 7px;"><?php echo $countCompleted; ?></span>
                </button>
                <button type="button" class="admin-tab-btn" data-filter="cancelled">
                    ✖ Đã hủy <span class="badge-pill danger" style="padding: 2px 7px;"><?php echo $countCancelled; ?></span>
                </button>
                <button type="button" class="admin-tab-btn" data-filter="vietqr">
                    <i class="fa-solid fa-qrcode"></i> VietQR (<?php echo $countVietQR; ?>)
                </button>
                <button type="button" class="admin-tab-btn" data-filter="cod">
                    <i class="fa-solid fa-money-bill-wave"></i> COD (<?php echo $countCOD; ?>)
                </button>
            </div>

            <div class="admin-search-wrapper" style="min-width: 220px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="order-search" class="admin-search-input" placeholder="Tìm Mã ĐH, tên khách, SĐT...">
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 14px;">
            <a href="xuat_donhang.php" class="admin-btn" style="background: #10b981; color: #ffffff; text-decoration: none; padding: 7px 14px; font-size: 13px; font-weight: 600; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);">
                <i class="fa-solid fa-file-excel"></i> <span>Xuất Excel</span>
            </a>
            <div style="font-size: 13px; color: #64748b;">
                Hiển thị: <strong id="order-display-count" style="color: #0f172a;"><?php echo $totalOrders; ?></strong> đơn hàng
            </div>
        </div>
    </div>

    <div class="admin-table-card">
        <div class="admin-table-responsive">
            <table class="admin-table" id="table-orders">
                <thead>
                    <tr>
                        <th width="80">Mã ĐH</th>
                        <th>Khách hàng</th>
                        <th>Số điện thoại</th>
                        <th>Nơi giao & Thời gian</th>
                        <th>Thanh toán</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th width="190" style="text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="orders-tbody">
                    <?php if ($totalOrders > 0): ?>
                        <?php foreach ($allOrders as $hd): 
                            $isVietQR = (isset($hd['PhuongThucTT']) && $hd['PhuongThucTT'] === 'VIETQR');
                            $tt = intval($hd['TinhTrang']);
                            $statusStr = 'pending';
                            if ($tt === 1) $statusStr = 'shipping';
                            elseif ($tt === 2) $statusStr = 'completed';
                            elseif ($tt === 3) $statusStr = 'cancelled';
                            $payStr = $isVietQR ? 'vietqr' : 'cod';
                            $diaChi = !empty($hd['DiaChiGiao']) ? $hd['DiaChiGiao'] : $hd['NoiGiao'];
                            $ngayGiao = !empty($hd['NgayGiao']) ? date('d/m/Y', strtotime($hd['NgayGiao'])) : '';
                        ?>
                            <tr id="row-<?php echo $hd['MaHD']; ?>" 
                                class="order-row"
                                data-status="<?php echo $statusStr; ?>"
                                data-pay="<?php echo $payStr; ?>"
                                data-id="<?php echo $hd['MaHD']; ?>"
                                data-customer="<?php echo htmlspecialchars(mb_strtolower($hd['HoTen'])); ?>"
                                data-phone="<?php echo htmlspecialchars($hd['DienThoai']); ?>"
                                data-address="<?php echo htmlspecialchars(mb_strtolower($diaChi)); ?>">
                                <td>
                                    <strong style="color: #0f172a; font-size: 14px;">#<?php echo $hd['MaHD']; ?></strong>
                                    <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">
                                        <?php echo date('d/m H:i', strtotime($hd['NgayDat'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: #0f172a;"><?php echo htmlspecialchars($hd['HoTen']); ?></div>
                                    <?php if (!empty($hd['LoiNhan'])): ?>
                                        <span class="badge-pill warning" style="font-size: 10.5px; padding: 1px 6px; margin-top: 3px;" title="<?php echo htmlspecialchars($hd['LoiNhan']); ?>">
                                            💌 Có thiệp
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($hd['DienThoai']); ?></td>
                                <td style="max-width: 240px; font-size: 13px; color: #475569;">
                                    <div title="<?php echo htmlspecialchars($diaChi); ?>">
                                        <?php echo htmlspecialchars(mb_strimwidth($diaChi, 0, 35, '...')); ?>
                                    </div>
                                    <?php if (!empty($ngayGiao)): ?>
                                        <div style="font-size: 11.5px; color: #4338ca; margin-top: 2px; font-weight: 500;">
                                            <i class="fa-regular fa-calendar-check"></i> Ngày: <?php echo $ngayGiao; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($hd['GioGiao'])): ?>
                                        <div style="font-size: 11.5px; color: #6366f1; margin-top: 1px; font-weight: 500;">
                                            <i class="fa-regular fa-clock"></i> Hẹn: <?php echo htmlspecialchars($hd['GioGiao']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isVietQR): ?>
                                        <span class="badge-pill warning" style="font-weight: 700;">
                                            <i class="fa-solid fa-qrcode"></i> VietQR
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-pill slate">
                                            <i class="fa-solid fa-money-bill-wave"></i> COD
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong style="color: #e11d48; font-size: 14.5px;">
                                        <?php echo number_format($hd['TongTien']); ?> đ
                                    </strong>
                                </td>
                                <td class="status-cell">
                                    <?php if ($tt === 1): ?>
                                        <span class="badge-pill info"><i class="fa-solid fa-truck-fast"></i> Đang giao</span>
                                    <?php elseif ($tt === 2): ?>
                                        <span class="badge-pill success"><i class="fa-solid fa-check"></i> Đã giao</span>
                                    <?php elseif ($tt === 3): ?>
                                        <span class="badge-pill danger"><i class="fa-solid fa-xmark"></i> Đã hủy</span>
                                    <?php else: ?>
                                        <span class="badge-pill warning"><i class="fa-solid fa-clock"></i> Chờ xử lý</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <div class="action-btn-group" style="justify-content: flex-end; align-items: center; gap: 6px;">
                                        <button type="button" class="action-btn view btn-view-order" data-id="<?php echo $hd['MaHD']; ?>" title="Xem chi tiết đơn">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <a href="inhoadon.php?MaHD=<?php echo $hd['MaHD']; ?>" target="_blank" class="action-btn print" title="In phiếu đơn">
                                            <i class="fa-solid fa-print"></i>
                                        </a>
                                        <select class="admin-select-status" data-id="<?php echo $hd['MaHD']; ?>" data-current="<?php echo $tt; ?>" style="padding: 4px 6px; border-radius: 6px; font-size: 12px; border: 1px solid #cbd5e1; background: #fff; font-weight: 600; cursor: pointer; color: #1e293b;">
                                            <option value="0" <?php echo ($tt === 0) ? 'selected' : ''; ?>>Chờ xử lý</option>
                                            <option value="1" <?php echo ($tt === 1) ? 'selected' : ''; ?>>Đang giao</option>
                                            <option value="2" <?php echo ($tt === 2) ? 'selected' : ''; ?>>Đã giao</option>
                                            <option value="3" <?php echo ($tt === 3) ? 'selected' : ''; ?>>Đã hủy</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #94a3b8;">
                                Chưa có đơn hàng nào trong hệ thống.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const detailBox = document.getElementById('detail-box');
    const tabBtns = document.querySelectorAll('#order-tabs .admin-tab-btn');
    const searchInput = document.getElementById('order-search');
    const countDisplay = document.getElementById('order-display-count');
    const rows = document.querySelectorAll('.order-row');
    let currentFilter = 'all';

    function applyOrderFilters() {
        const query = searchInput.value.trim().toLowerCase();
        let visible = 0;

        rows.forEach(row => {
            const status = row.getAttribute('data-status');
            const pay = row.getAttribute('data-pay');
            const id = row.getAttribute('data-id') || '';
            const customer = row.getAttribute('data-customer') || '';
            const phone = row.getAttribute('data-phone') || '';
            const address = row.getAttribute('data-address') || '';

            let matchesTab = true;
            if (currentFilter === 'pending') matchesTab = (status === 'pending');
            else if (currentFilter === 'shipping') matchesTab = (status === 'shipping');
            else if (currentFilter === 'completed') matchesTab = (status === 'completed');
            else if (currentFilter === 'cancelled') matchesTab = (status === 'cancelled');
            else if (currentFilter === 'vietqr') matchesTab = (pay === 'vietqr');
            else if (currentFilter === 'cod') matchesTab = (pay === 'cod');

            const matchesSearch = !query || id.includes(query) || customer.includes(query) || phone.includes(query) || address.includes(query);

            if (matchesTab && matchesSearch) {
                row.style.display = '';
                visible++;
            } else {
                row.style.display = 'none';
            }
        });

        countDisplay.textContent = visible;
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.getAttribute('data-filter');
            applyOrderFilters();
        });
    });

    searchInput.addEventListener('input', applyOrderFilters);

    document.addEventListener('change', function (e) {
        const selectStatus = e.target.closest('.admin-select-status');
        if (selectStatus) {
            const maHD = selectStatus.getAttribute('data-id');
            const newStatus = selectStatus.value;
            const prevStatus = selectStatus.getAttribute('data-current') || '0';

            selectStatus.disabled = true;
            fetch(`quanlydonhang.php?action=ajax_update&MaHD=${maHD}&status=${newStatus}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        selectStatus.setAttribute('data-current', newStatus);
                        const row = document.getElementById(`row-${maHD}`);
                        if (row) {
                            row.setAttribute('data-status', data.filterStatus);
                            const statusCell = row.querySelector('.status-cell');
                            if (statusCell) statusCell.innerHTML = data.badge;
                        }
                        if (typeof showAdminToast === 'function') {
                            showAdminToast(`Đã cập nhật trạng thái đơn hàng #${maHD}!`, 'success');
                        }
                        applyOrderFilters();
                    } else {
                        selectStatus.value = prevStatus;
                        if (typeof showAdminToast === 'function') {
                            showAdminToast('Không thể cập nhật trạng thái đơn hàng!', 'error');
                        }
                    }
                })
                .catch(err => {
                    console.error(err);
                    selectStatus.value = prevStatus;
                    if (typeof showAdminToast === 'function') {
                        showAdminToast('Lỗi kết nối máy chủ!', 'error');
                    }
                })
                .finally(() => {
                    selectStatus.disabled = false;
                });
            return;
        }
    });

    document.addEventListener('click', function (e) {
        const viewBtn = e.target.closest('.btn-view-order');
        if (viewBtn) {
            e.preventDefault();
            const maHD = viewBtn.getAttribute('data-id');
            detailBox.style.opacity = '0.5';

            fetch(`quanlydonhang.php?action=ajax_view&MaHD=${maHD}`)
                .then(res => res.text())
                .then(html => {
                    detailBox.innerHTML = html;
                    detailBox.style.display = 'block';
                    detailBox.style.opacity = '1';
                    detailBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
                })
                .catch(err => {
                    console.error(err);
                    if (typeof showAdminToast === 'function') {
                        showAdminToast('Lỗi tải chi tiết đơn hàng!', 'error');
                    }
                });
            return;
        }

        if (e.target.closest('#btn-close-detail')) {
            detailBox.style.display = 'none';
            detailBox.innerHTML = '';
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>