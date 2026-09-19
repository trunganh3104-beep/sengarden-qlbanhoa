<?php
require_once '../includes/auth.php';
include_once '../DataProvider.php';

requireAdmin();

$msg = '';
$err = '';

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (function_exists('ensureRequiredTables')) ensureRequiredTables($conn);
        mysqli_query($conn, "DELETE FROM lienhe WHERE MaLH = $id");
        mysqli_close($conn);
        header('Location: quanlylienhe.php?msg=deleted');
        exit;
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $msg = 'Đã xóa tin nhắn liên hệ thành công!';
}

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
mysqli_set_charset($conn, "utf8mb4");
if (function_exists('ensureRequiredTables')) ensureRequiredTables($conn);

$kw = trim($_GET['kw'] ?? '');
$where = '';
if (!empty($kw)) {
    $kwEsc = mysqli_real_escape_string($conn, $kw);
    $where = "WHERE HoTen LIKE '%$kwEsc%' OR DienThoai LIKE '%$kwEsc%' OR Email LIKE '%$kwEsc%' OR TieuDe LIKE '%$kwEsc%' OR NoiDung LIKE '%$kwEsc%'";
}

$resList = mysqli_query($conn, "SELECT * FROM lienhe $where ORDER BY MaLH DESC");
$lienhes = [];
while ($row = mysqli_fetch_assoc($resList)) {
    $lienhes[] = $row;
}

$resStats = mysqli_query($conn, "SELECT 
    COUNT(*) as total, 
    SUM(IF(DATE(NgayGui) = CURDATE(), 1, 0)) as today_cnt 
FROM lienhe");
$stats = mysqli_fetch_assoc($resStats);
mysqli_close($conn);

$pageTitle = 'Quản Lý Tin Nhắn Liên Hệ';
require_once '../includes/header.php';
include 'nav.php';
?>

<div class="admin-container">
    <div class="admin-page-header">
        <div class="header-left">
            <h2>
                <i class="fa-solid fa-envelope" style="color: #0284c7;"></i> Quản Lý Tin Nhắn Liên Hệ
            </h2>
            <p>Xem thông điệp, yêu cầu đặt hoa thiết kế và phản hồi tư vấn khách hàng</p>
        </div>
    </div>

    <?php if ($msg): ?>
        <div style="background: #f0fdf4; border: 1px solid #86efac; color: #16a34a; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-circle-check" style="font-size: 18px;"></i>
            <span><?php echo htmlspecialchars($msg); ?></span>
        </div>
    <?php endif; ?>

    <div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
        <div class="kpi-card kpi-products">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
                <i class="fa-solid fa-inbox"></i>
            </div>
            <div>
                <div style="font-size: 12.5px; color: #64748b; font-weight: 600; text-transform: uppercase;">Tổng số liên hệ</div>
                <div style="font-size: 24px; font-weight: 800; color: #0284c7; line-height: 1.2;"><?php echo number_format($stats['total'] ?? 0); ?></div>
                <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">Đã ghi nhận trong hệ thống</div>
            </div>
        </div>

        <div class="kpi-card kpi-revenue">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
            <div>
                <div style="font-size: 12.5px; color: #64748b; font-weight: 600; text-transform: uppercase;">Liên hệ mới hôm nay</div>
                <div style="font-size: 24px; font-weight: 800; color: #10b981; line-height: 1.2;"><?php echo number_format($stats['today_cnt'] ?? 0); ?></div>
                <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">Cần phản hồi sớm</div>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <form method="get" action="quanlylienhe.php" style="display: flex; gap: 8px; flex: 1; max-width: 450px;">
                <input type="text" name="kw" value="<?php echo htmlspecialchars($kw); ?>" placeholder="Tìm theo họ tên, SĐT, email, chủ đề..." style="flex: 1; padding: 9px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px; outline: none;">
                <button type="submit" class="admin-btn admin-btn-secondary" style="padding: 9px 16px;">
                    <i class="fa-solid fa-magnifying-glass"></i> Tìm
                </button>
                <?php if (!empty($kw)): ?>
                    <a href="quanlylienhe.php" class="admin-btn admin-btn-secondary" style="padding: 9px 14px;" title="Xóa tìm kiếm">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th style="width: 140px;">Thời Gian</th>
                        <th>Khách Hàng</th>
                        <th>Liên Hệ (SĐT / Email)</th>
                        <th>Chủ Đề</th>
                        <th>Nội Dung Lời Nhắn</th>
                        <th style="text-align: right; width: 140px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lienhes)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: #94a3b8;">
                                <i class="fa-solid fa-inbox" style="font-size: 32px; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                Chưa có tin nhắn liên hệ nào.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($lienhes as $lh): ?>
                            <tr>
                                <td>#<?php echo $lh['MaLH']; ?></td>
                                <td style="font-size: 13px; color: #64748b;">
                                    <?php echo date('d/m/Y H:i', strtotime($lh['NgayGui'])); ?>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: #1e293b; font-size: 13.5px;">
                                        <?php echo htmlspecialchars($lh['HoTen']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 13px;">
                                        <?php if (!empty($lh['DienThoai'])): ?>
                                            <div>
                                                <a href="tel:<?php echo htmlspecialchars($lh['DienThoai']); ?>" style="color: #0284c7; font-weight: 600; text-decoration: none;">
                                                    <i class="fa-solid fa-phone" style="font-size: 11px;"></i> <?php echo htmlspecialchars($lh['DienThoai']); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($lh['Email'])): ?>
                                            <div style="color: #64748b; font-size: 12px; margin-top: 2px;">
                                                <a href="mailto:<?php echo htmlspecialchars($lh['Email']); ?>" style="color: #64748b; text-decoration: none;">
                                                    <i class="fa-solid fa-envelope" style="font-size: 11px;"></i> <?php echo htmlspecialchars($lh['Email']); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span style="display: inline-block; font-size: 12.5px; font-weight: 600; background: #f1f5f9; color: #334155; padding: 3px 8px; border-radius: 6px;">
                                        <?php echo !empty($lh['TieuDe']) ? htmlspecialchars($lh['TieuDe']) : 'Tư vấn chung'; ?>
                                    </span>
                                </td>
                                <td style="max-width: 320px;">
                                    <div style="font-size: 13px; color: #475569; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?php echo htmlspecialchars($lh['NoiDung']); ?>
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 6px;">
                                        <button type="button" class="action-btn" onclick='viewLienHe(<?php echo json_encode($lh, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>)' title="Xem chi tiết" style="color: #0284c7; background: #e0f2fe; border: none; border-radius: 6px; padding: 5px 9px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; font-size: 12px;">
                                            <i class="fa-solid fa-eye"></i> Xem
                                        </button>
                                        <a href="quanlylienhe.php?action=delete&id=<?php echo $lh['MaLH']; ?>" class="action-btn" onclick="return confirm('Bạn có chắc muốn xóa tin nhắn này?');" title="Xóa" style="color: #ef4444; background: #fee2e2; border-radius: 6px; padding: 5px 9px; display: inline-flex; align-items: center; gap: 4px; font-size: 12px; text-decoration: none;">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="viewLienHeModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; border-radius: 16px; width: 100%; max-width: 550px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); overflow: hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; background: #fafbfc;">
            <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-message" style="color: #0284c7;"></i> Chi Tiết Lời Nhắn Khách Hàng
            </h3>
            <button type="button" onclick="closeViewModal()" style="border: none; background: transparent; font-size: 18px; color: #94a3b8; cursor: pointer;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div style="padding: 24px;">
            <div style="display: flex; gap: 14px; margin-bottom: 20px; align-items: center; background: #f8fafc; padding: 14px 18px; border-radius: 10px; border: 1px solid #e2e8f0;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700;" id="modalAvatar">
                    K
                </div>
                <div>
                    <div style="font-weight: 700; font-size: 15px; color: #0f172a;" id="modalHoTen">...</div>
                    <div style="font-size: 12.5px; color: #64748b;" id="modalNgayGui">...</div>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Thông tin kết nối:</div>
                <div style="display: flex; gap: 16px; flex-wrap: wrap; font-size: 13.5px;">
                    <div id="modalSdtWrap">
                        <i class="fa-solid fa-phone" style="color: #0284c7;"></i> <a id="modalSdt" href="" style="color: #0284c7; font-weight: 600; text-decoration: none;">...</a>
                    </div>
                    <div id="modalEmailWrap">
                        <i class="fa-solid fa-envelope" style="color: #10b981;"></i> <a id="modalEmail" href="" style="color: #475569; text-decoration: none;">...</a>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Chủ đề yêu cầu:</div>
                <div style="font-weight: 600; color: #1e293b; font-size: 14.5px;" id="modalTieuDe">...</div>
            </div>

            <div style="margin-bottom: 22px;">
                <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; margin-bottom: 6px;">Nội dung chi tiết:</div>
                <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 14px; font-size: 14px; line-height: 1.6; color: #334155; white-space: pre-wrap; max-height: 200px; overflow-y: auto;" id="modalNoiDung">...</div>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="button" class="admin-btn admin-btn-secondary" onclick="closeViewModal()">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewLienHe(data) {
    document.getElementById('modalHoTen').innerText = data.HoTen || 'Khách hàng';
    document.getElementById('modalAvatar').innerText = (data.HoTen || 'K').charAt(0).toUpperCase();
    document.getElementById('modalNgayGui').innerText = 'Gửi lúc: ' + (data.NgayGui || '');
    
    const sdtLink = document.getElementById('modalSdt');
    sdtLink.innerText = data.DienThoai || 'Chưa cung cấp';
    sdtLink.href = data.DienThoai ? ('tel:' + data.DienThoai) : '#';

    const emailLink = document.getElementById('modalEmail');
    emailLink.innerText = data.Email || 'Chưa cung cấp';
    emailLink.href = data.Email ? ('mailto:' + data.Email) : '#';

    document.getElementById('modalTieuDe').innerText = data.TieuDe || 'Tư vấn chung';
    document.getElementById('modalNoiDung').innerText = data.NoiDung || '';

    document.getElementById('viewLienHeModal').style.display = 'flex';
}

function closeViewModal() {
    document.getElementById('viewLienHeModal').style.display = 'none';
}
</script>

<?php include '../includes/footer.php'; ?>
