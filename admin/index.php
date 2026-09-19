<?php
require_once '../includes/auth.php';
requireAdmin();

require_once '../includes/header.php';
include_once '../DataProvider.php';
include_once 'nav.php';

$user = getCurrentUser();
$tenHienThi = !empty($user['HoTen']) ? $user['HoTen'] : (!empty($user['TenDN']) ? $user['TenDN'] : 'Admin');

$totalHoa = mysqli_fetch_assoc(DataProvider::ExecuteQuery("SELECT COUNT(*) AS total FROM hoa"))['total'];
$totalLoai = mysqli_fetch_assoc(DataProvider::ExecuteQuery("SELECT COUNT(*) AS total FROM loaihoa"))['total'];
$totalDon = mysqli_fetch_assoc(DataProvider::ExecuteQuery("SELECT COUNT(*) AS total FROM hoadon"))['total'];
$donChoDuyet = mysqli_fetch_assoc(DataProvider::ExecuteQuery("SELECT COUNT(*) AS total FROM hoadon WHERE TinhTrang = 0"))['total'];
$totalKH = mysqli_fetch_assoc(DataProvider::ExecuteQuery("SELECT COUNT(*) AS total FROM khachhang WHERE Role = 0"))['total'];
require_once '../includes/counter.php';
$sg_traffic = recordVisitorTraffic(getDbConnection());

$sqlTongTien = "SELECT IFNULL(SUM(OrderTotal), 0) AS TongTien 
                FROM (
                    SELECT hd.MaHD, GREATEST(0, SUM(ct.SoLuong * ct.DonGia) - IFNULL(hd.SoTienGiam, 0)) AS OrderTotal 
                    FROM hoadon hd 
                    JOIN chitiethd ct ON hd.MaHD = ct.MaHD 
                    WHERE hd.TinhTrang = 2 
                    GROUP BY hd.MaHD, hd.SoTienGiam
                ) t";
$rsTongTien = DataProvider::ExecuteQuery($sqlTongTien);
$tongDoanhThu = mysqli_fetch_assoc($rsTongTien)['TongTien'];

$revenueDays = [];
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $revenueDays[$day] = 0;
}
$sqlRev = "SELECT Ngay, SUM(OrderTotal) AS DoanhThu 
           FROM (
               SELECT hd.MaHD, DATE(hd.NgayDat) AS Ngay, GREATEST(0, SUM(ct.SoLuong * ct.DonGia) - IFNULL(hd.SoTienGiam, 0)) AS OrderTotal 
               FROM hoadon hd 
               JOIN chitiethd ct ON hd.MaHD = ct.MaHD 
               WHERE hd.TinhTrang = 2 AND hd.NgayDat >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
               GROUP BY hd.MaHD, DATE(hd.NgayDat), hd.SoTienGiam
           ) t 
           GROUP BY Ngay";
$rsRev = DataProvider::ExecuteQuery($sqlRev);
if ($rsRev) {
    while ($r = mysqli_fetch_assoc($rsRev)) {
        if (isset($revenueDays[$r['Ngay']])) {
            $revenueDays[$r['Ngay']] = floatval($r['DoanhThu']);
        }
    }
}
$chartLabels = [];
$chartRevenueData = [];
foreach ($revenueDays as $date => $rev) {
    $chartLabels[] = date('d/m', strtotime($date));
    $chartRevenueData[] = $rev;
}

$sqlCat = "SELECT lh.TenLoai, COUNT(h.MaHoa) AS SoLuongHoa 
           FROM loaihoa lh 
           LEFT JOIN hoa h ON lh.MaLoai = h.MaLoai 
           GROUP BY lh.MaLoai, lh.TenLoai 
           ORDER BY SoLuongHoa DESC";
$rsCat = DataProvider::ExecuteQuery($sqlCat);
$catLabels = [];
$catCounts = [];
if ($rsCat) {
    while ($c = mysqli_fetch_assoc($rsCat)) {
        $catLabels[] = $c['TenLoai'];
        $catCounts[] = intval($c['SoLuongHoa']);
    }
}

$sqlRecentOrders = "SELECT hd.MaHD, hd.NgayDat, hd.TinhTrang, hd.DiaChiGiao, kh.HoTen, kh.DienThoai,
                           GREATEST(0, IFNULL(SUM(ct.SoLuong * ct.DonGia), 0) - IFNULL(hd.SoTienGiam, 0)) AS TongTien
                    FROM hoadon hd
                    JOIN khachhang kh ON hd.MaKH = kh.MaKH
                    LEFT JOIN chitiethd ct ON hd.MaHD = ct.MaHD
                    GROUP BY hd.MaHD, hd.NgayDat, hd.TinhTrang, hd.DiaChiGiao, kh.HoTen, kh.DienThoai, hd.SoTienGiam
                    ORDER BY hd.MaHD DESC
                    LIMIT 5";
$rsRecentOrders = DataProvider::ExecuteQuery($sqlRecentOrders);
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="admin-container">
    
    <div class="admin-card welcome-banner" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #3730a3 70%, #4338ca 100%); color: #ffffff; border: none; padding: 28px 32px; position: relative; overflow: hidden; border-radius: 16px; box-shadow: 0 10px 30px rgba(49, 46, 129, 0.18); margin-bottom: 24px;">
        
        <div style="position: absolute; right: -40px; top: -40px; width: 220px; height: 220px; background: radial-gradient(circle, rgba(244,63,94,0.25) 0%, transparent 70%); border-radius: 50%; pointer-events: none; filter: blur(30px);"></div>
        <div style="position: absolute; left: 30%; bottom: -50px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(56,189,248,0.2) 0%, transparent 70%); border-radius: 50%; pointer-events: none; filter: blur(35px);"></div>
        
        <div style="position: absolute; right: 20px; top: -10px; font-size: 140px; color: rgba(255,255,255,0.04); pointer-events: none;">
            <i class="fa-solid fa-spa"></i>
        </div>
        
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; position: relative; z-index: 2;">
            <div>
                <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.22); padding: 5px 14px; border-radius: 999px; font-size: 12px; font-weight: 600; color: #e0e7ff; margin-bottom: 12px; backdrop-filter: blur(8px);">
                    <span class="status-dot pulse" style="background: #38bdf8;"></span> Trung tâm Điều hành Bán hàng Sen Garden
                </div>
                <h1 style="margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -0.02em; color: #ffffff;">
                    Xin chào, <?php echo htmlspecialchars($tenHienThi); ?> 👋
                </h1>
                <p style="margin: 6px 0 0; font-size: 14px; color: #cbd5e1;">
                    Hôm nay là <strong><?php echo date('d/m/Y'); ?></strong> — Chúc bạn một ngày làm việc hiệu quả và tràn đầy năng lượng!
                </p>
            </div>

            
            <div style="background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.25); border-radius: 14px; padding: 18px 26px; text-align: right; box-shadow: 0 8px 24px rgba(0,0,0,0.12);">
                <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.06em; color: #e0e7ff; font-weight: 600;">
                    <i class="fa-solid fa-wallet" style="color: #38bdf8;"></i> Tổng Doanh Thu Tích Lũy
                </div>
                <div style="font-size: 28px; font-weight: 800; color: #38bdf8; margin-top: 4px; letter-spacing: -0.02em;">
                    <?php echo number_format($tongDoanhThu); ?> <span style="font-size: 18px; font-weight: 600; color: #e0e7ff;">đ</span>
                </div>
            </div>
        </div>
    </div>

    
    <div class="kpi-grid">
        
        <a href="#chartRevenue" class="kpi-card kpi-revenue" title="Bấm để xem biểu đồ doanh thu chi tiết">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #38bdf8, #0284c7);">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div class="kpi-info">
                <div class="kpi-label">Tổng Doanh Thu</div>
                <div class="kpi-value" style="color: #0369a1;"><?php echo number_format($tongDoanhThu); ?> đ</div>
                <span class="kpi-badge badge-pill success">
                    <i class="fa-solid fa-circle-check"></i> Tích lũy tự động
                </span>
            </div>
        </a>

        
        <a href="quanlydonhang.php" class="kpi-card kpi-orders" title="Bấm để mở trang Quản lý đơn hàng">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #fb7185, #e11d48);">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <div class="kpi-info">
                <div class="kpi-label">Tổng Đơn Đặt Hàng</div>
                <div class="kpi-value" style="color: #be123c;"><?php echo number_format($totalDon); ?></div>
                <?php if ($donChoDuyet > 0): ?>
                    <span class="kpi-badge badge-pill warning">
                        <i class="fa-solid fa-clock"></i> <?php echo $donChoDuyet; ?> đơn chờ duyệt
                    </span>
                <?php else: ?>
                    <span class="kpi-badge badge-pill success">
                        <i class="fa-solid fa-check"></i> Đã hoàn tất xử lý
                    </span>
                <?php endif; ?>
            </div>
        </a>

        
        <a href="quanlyhoa.php" class="kpi-card kpi-products" title="Bấm để mở trang Quản lý hoa">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #c084fc, #7e22ce);">
                <i class="fa-solid fa-fan"></i>
            </div>
            <div class="kpi-info">
                <div class="kpi-label">Sản Phẩm Hoa</div>
                <div class="kpi-value" style="color: #6b21a8;"><?php echo number_format($totalHoa); ?></div>
                <span class="kpi-badge badge-pill info">
                    <i class="fa-solid fa-tags"></i> <?php echo $totalLoai; ?> danh mục hoa
                </span>
            </div>
        </a>

        
        <a href="quanlyuser.php" class="kpi-card kpi-customers" title="Bấm để mở trang Quản lý khách hàng">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #34d399, #059669);">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="kpi-info">
                <div class="kpi-label">Khách Hàng Đăng Ký</div>
                <div class="kpi-value" style="color: #047857;"><?php echo number_format($totalKH); ?></div>
                <span class="kpi-badge badge-pill success">
                    <i class="fa-solid fa-user-check"></i> Thành viên hoạt động
                </span>
            </div>
        </a>

        <div class="kpi-card kpi-traffic" title="Thống kê lưu lượng truy cập website">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div class="kpi-info">
                <div class="kpi-label">Lượt Truy Cập Web</div>
                <div class="kpi-value" style="color: #b45309;"><?php echo number_format($sg_traffic['total']); ?></div>
                <span class="kpi-badge badge-pill warning">
                    <i class="fa-solid fa-users"></i> Hôm nay: <?php echo number_format($sg_traffic['today']); ?> | Online: <?php echo $sg_traffic['online']; ?>
                </span>
            </div>
        </div>
    </div>

    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(480px, 1fr)); gap: 24px; margin-bottom: 28px;">
        
        <div class="admin-card" style="margin-bottom: 0; border-top: 3px solid #38bdf8; border-radius: 16px;">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="fa-solid fa-chart-simple" style="color: #0284c7;"></i> Doanh Thu 7 Ngày Gần Nhất (VNĐ)
                </h3>
                <span class="badge-pill info">7 ngày qua</span>
            </div>
            <div class="admin-card-body">
                <div style="height: 290px; position: relative;">
                    <canvas id="chartRevenue"></canvas>
                </div>
            </div>
        </div>

        
        <div class="admin-card" style="margin-bottom: 0; border-top: 3px solid #c084fc; border-radius: 16px;">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="fa-solid fa-chart-pie" style="color: #7e22ce;"></i> Cơ Cấu Sản Phẩm Theo Loại Hoa
                </h3>
                <span class="badge-pill slate"><?php echo $totalLoai; ?> loại</span>
            </div>
            <div class="admin-card-body">
                <div style="height: 290px; position: relative;">
                    <canvas id="chartCategories"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card" style="margin-bottom: 28px; border-top: 3px solid #f59e0b; border-radius: 16px; background: #ffffff; padding: 22px 26px;">
        <div class="admin-card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 class="admin-card-title" style="margin: 0; font-size: 17px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-bullhorn" style="color: #f59e0b;"></i> Lập Lịch Tự Động & Chiến Dịch Quảng Cáo Khuyến Mãi
            </h3>
            <span class="badge-pill success" style="background: #dcfce7; color: #15803d; font-weight: 700; padding: 4px 12px; border-radius: 999px; font-size: 12px;">
                <i class="fa-solid fa-circle-check"></i> Đang tự động phát (Active)
            </span>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px;">
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 18px;">
                <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Chiến dịch quảng cáo</div>
                <div style="font-size: 15px; font-weight: 700; color: #0f172a; margin-top: 4px;">🎁 Flash Sale Tặng Voucher 50.000đ & Freeship</div>
                <div style="font-size: 12px; color: #475569; margin-top: 4px;">Kích hoạt tự động sau 1.2s trên trang chủ khi khách truy cập.</div>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 18px;">
                <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Lập lịch đếm ngược tự động</div>
                <div style="font-size: 15px; font-weight: 700; color: #e11d48; margin-top: 4px;"><i class="fa-solid fa-clock"></i> Tự động gia hạn theo chu kỳ 24h</div>
                <div style="font-size: 12px; color: #475569; margin-top: 4px;">Đồng hồ đếm ngược thời gian thực trên Popup quảng cáo.</div>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 18px;">
                <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Mã khuyến mãi liên kết</div>
                <div style="font-size: 15px; font-weight: 700; color: #2563eb; margin-top: 4px;">Mã: <code>HOATUOI2026</code> & <code>SENGARDEN</code></div>
                <div style="font-size: 12px; color: #475569; margin-top: 4px;">Tự động kiểm tra điều kiện đơn hàng tối thiểu tại giỏ hàng.</div>
            </div>
        </div>
    </div>

    <div class="admin-table-card" style="border-top: 3px solid #fb7185; border-radius: 16px;">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <i class="fa-solid fa-clock-rotate-left" style="color: #e11d48;"></i> Đơn Hàng Mới Đặt Gần Đây
            </h3>
            <a href="quanlydonhang.php" class="admin-btn admin-btn-outline admin-btn-sm" style="border-radius: 8px;">
                Xem tất cả đơn <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
        <div class="admin-table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="90">Mã ĐH</th>
                        <th>Khách hàng</th>
                        <th>Số điện thoại</th>
                        <th>Nơi giao</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th width="100" style="text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rsRecentOrders && mysqli_num_rows($rsRecentOrders) > 0): ?>
                        <?php while ($ord = mysqli_fetch_assoc($rsRecentOrders)): ?>
                            <tr>
                                <td><strong style="color: #0f172a;">#<?php echo $ord['MaHD']; ?></strong></td>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($ord['HoTen']); ?></td>
                                <td><?php echo htmlspecialchars($ord['DienThoai']); ?></td>
                                <?php $diaChiDisplay = !empty($ord['DiaChiGiao']) ? $ord['DiaChiGiao'] : ($ord['NoiGiao'] ?? ''); ?>
                                <td style="max-width: 220px; font-size: 13px; color: #475569;" title="<?php echo htmlspecialchars($diaChiDisplay); ?>">
                                    <?php echo htmlspecialchars(mb_strimwidth($diaChiDisplay, 0, 32, '...')); ?>
                                </td>
                                <td style="font-size: 13px; color: #64748b;">
                                    <?php echo date('d/m/Y H:i', strtotime($ord['NgayDat'])); ?>
                                </td>
                                <td>
                                    <strong style="color: #e11d48;"><?php echo number_format($ord['TongTien']); ?> đ</strong>
                                </td>
                                <td>
                                    <?php 
                                        $st = intval($ord['TinhTrang']);
                                        if ($st === 2): ?>
                                        <span class="badge-pill success"><i class="fa-solid fa-check"></i> Đã giao</span>
                                    <?php elseif ($st === 1): ?>
                                        <span class="badge-pill info"><i class="fa-solid fa-truck-fast"></i> Đang giao</span>
                                    <?php elseif ($st === 3): ?>
                                        <span class="badge-pill danger"><i class="fa-solid fa-xmark"></i> Đã hủy</span>
                                    <?php else: ?>
                                        <span class="badge-pill warning"><i class="fa-solid fa-clock"></i> Chờ xử lý</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <div class="action-btn-group" style="justify-content: flex-end;">
                                        <a href="quanlydonhang.php" class="action-btn view" title="Xem chi tiết đơn">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="inhoadon.php?MaHD=<?php echo $ord['MaHD']; ?>" target="_blank" class="action-btn print" title="In phiếu đơn">
                                            <i class="fa-solid fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 30px; color: #94a3b8;">
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
    const ctxRev = document.getElementById('chartRevenue');
    if (ctxRev) {
        new Chart(ctxRev, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chartLabels); ?>,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: <?php echo json_encode($chartRevenueData); ?>,
                    backgroundColor: 'rgba(56, 189, 248, 0.75)',
                    hoverBackgroundColor: '#0284c7',
                    borderColor: '#0284c7',
                    borderWidth: 1,
                    borderRadius: 8,
                    maxBarThickness: 42
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 13 },
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.raw) + ' đ';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000) + ' Tr';
                                if (value >= 1000) return (value / 1000) + ' k';
                                return value;
                            },
                            font: { size: 11 }
                        },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11, weight: 'bold' } }
                    }
                }
            }
        });
    }

    const ctxCat = document.getElementById('chartCategories');
    if (ctxCat) {
        new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($catLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($catCounts); ?>,
                    backgroundColor: [
                        '#f43f5e', '#38bdf8', '#34d399', '#a855f7', '#fbbf24', 
                        '#f472b6', '#2dd4bf', '#818cf8', '#fb923c', '#94a3b8'
                    ],
                    borderWidth: 3,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            padding: 14,
                            font: { size: 12, weight: '500' }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return ` ${context.label}: ${context.raw} sản phẩm`;
                            }
                        }
                    }
                }
            }
        });
    }

    document.addEventListener('pointerdown', function (e) {
        const target = e.target.closest('.kpi-card, .admin-btn, .action-btn, .admin-tab-btn, .admin-nav-item, .admin-shop-link');
        if (!target) return;

        const rect = target.getBoundingClientRect();
        const ripple = document.createElement('span');
        ripple.className = 'sg-click-ripple';
        
        const size = Math.max(rect.width, rect.height) * 2;
        ripple.style.width = ripple.style.height = `${size}px`;
        ripple.style.left = `${e.clientX - rect.left - size / 2}px`;
        ripple.style.top = `${e.clientY - rect.top - size / 2}px`;

        const computed = window.getComputedStyle(target);
        if (computed.position === 'static') target.style.position = 'relative';
        target.style.overflow = 'hidden';

        target.appendChild(ripple);
        setTimeout(() => ripple.remove(), 600);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>