<?php
require_once '../includes/auth.php';
include_once '../DataProvider.php';

requireAdmin();

if (isset($_GET['action']) && $_GET['action'] === 'ajax_toggle') {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    $id = intval($_GET['id'] ?? 0);
    $res = ['success' => false];
    if ($id > 0) {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        mysqli_set_charset($conn, "utf8mb4");
        if (function_exists('ensureRequiredTables')) ensureRequiredTables($conn);
        mysqli_query($conn, "UPDATE voucher SET TrangThai = IF(TrangThai=1, 0, 1) WHERE MaVoucher = $id");
        $q = mysqli_query($conn, "SELECT Code, TrangThai FROM voucher WHERE MaVoucher = $id");
        if ($row = mysqli_fetch_assoc($q)) {
            $cntRes = mysqli_query($conn, "SELECT SUM(IF(TrangThai=1, 1, 0)) as active_cnt FROM voucher");
            $cntRow = mysqli_fetch_assoc($cntRes);
            $res = [
                'success' => true,
                'id' => $id,
                'status' => intval($row['TrangThai']),
                'code' => $row['Code'],
                'active_cnt' => intval($cntRow['active_cnt'] ?? 0)
            ];
        }
        mysqli_close($conn);
    }
    echo json_encode($res);
    exit;
}

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnThemVoucher'])) {
    $code = strtoupper(trim($_POST['txtCode'] ?? ''));
    $moTa = trim($_POST['txtMoTa'] ?? '');
    $loaiGiam = $_POST['txtLoaiGiam'] ?? 'phan_tram';
    $giaTri = floatval($_POST['txtGiaTri'] ?? 0);
    $donToiThieu = floatval($_POST['txtDonToiThieu'] ?? 0);
    $ngayHetHan = trim($_POST['txtNgayHetHan'] ?? '');

    if (empty($code)) {
        $err = 'Vui lòng nhập Mã Voucher!';
    } elseif ($giaTri <= 0) {
        $err = 'Giá trị giảm phải lớn hơn 0!';
    } else {
        $phanTram = ($loaiGiam === 'phan_tram') ? intval($giaTri) : 0;
        $soTien = ($loaiGiam === 'so_tien') ? $giaTri : 0;
        if ($phanTram > 100) $phanTram = 100;
        if (empty($ngayHetHan)) $ngayHetHan = date('Y-m-d', strtotime('+30 days'));

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        mysqli_set_charset($conn, "utf8mb4");
        if (function_exists('ensureRequiredTables')) ensureRequiredTables($conn);

        $codeEsc = mysqli_real_escape_string($conn, $code);
        $moTaEsc = mysqli_real_escape_string($conn, $moTa);
        $ngayEsc = mysqli_real_escape_string($conn, $ngayHetHan);

        $chk = mysqli_query($conn, "SELECT MaVoucher FROM voucher WHERE Code = '$codeEsc'");
        if (mysqli_num_rows($chk) > 0) {
            $err = "Mã voucher '$code' đã tồn tại trong hệ thống!";
        } else {
            $sql = "INSERT INTO voucher (Code, MoTa, PhanTramGiam, SoTienGiam, DonToiThieu, NgayHetHan, LuotDung, TrangThai) 
                    VALUES ('$codeEsc', '$moTaEsc', $phanTram, $soTien, $donToiThieu, '$ngayEsc', 0, 1)";
            if (mysqli_query($conn, $sql)) {
                $msg = "Thêm mã giảm giá '$code' thành công!";
            } else {
                $err = "Lỗi thêm voucher: " . mysqli_error($conn);
            }
        }
        mysqli_close($conn);
    }
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id'] ?? 0);

    if ($action === 'toggle' && $id > 0) {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        mysqli_query($conn, "UPDATE voucher SET TrangThai = IF(TrangThai=1, 0, 1) WHERE MaVoucher = $id");
        mysqli_close($conn);
        header('Location: quanlyvoucher.php?msg=updated');
        exit;
    }

    if ($action === 'delete' && $id > 0) {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        mysqli_query($conn, "DELETE FROM voucher WHERE MaVoucher = $id");
        mysqli_close($conn);
        header('Location: quanlyvoucher.php?msg=deleted');
        exit;
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'updated') $msg = 'Cập nhật trạng thái voucher thành công!';
    if ($_GET['msg'] === 'deleted') $msg = 'Đã xóa mã giảm giá thành công!';
}

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
mysqli_set_charset($conn, "utf8mb4");
if (function_exists('ensureRequiredTables')) ensureRequiredTables($conn);

$kw = trim($_GET['kw'] ?? '');
$where = '';
if (!empty($kw)) {
    $kwEsc = mysqli_real_escape_string($conn, $kw);
    $where = "WHERE Code LIKE '%$kwEsc%' OR MoTa LIKE '%$kwEsc%'";
}

$resList = mysqli_query($conn, "SELECT * FROM voucher $where ORDER BY MaVoucher DESC");
$vouchers = [];
while ($row = mysqli_fetch_assoc($resList)) {
    $vouchers[] = $row;
}

$resStats = mysqli_query($conn, "SELECT 
    COUNT(*) as total, 
    SUM(IF(TrangThai = 1, 1, 0)) as active, 
    SUM(LuotDung) as total_used 
FROM voucher");
$stats = mysqli_fetch_assoc($resStats);
mysqli_close($conn);

$pageTitle = 'Quản Lý Mã Giảm Giá - Voucher';
require_once '../includes/header.php';
include 'nav.php';
?>

<style>
.voucher-switch {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 24px;
    vertical-align: middle;
    cursor: pointer;
    margin: 0;
}
.voucher-switch input {
    opacity: 0;
    width: 0;
    height: 0;
    position: absolute;
}
.voucher-slider {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #cbd5e1;
    transition: 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 999px;
}
.voucher-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
input:checked + .voucher-slider {
    background-color: #10b981;
}
input:checked + .voucher-slider:before {
    transform: translateX(24px);
}
</style>

<div class="admin-container">
    <div class="admin-page-header">
        <div class="header-left">
            <h2>
                <i class="fa-solid fa-ticket" style="color: #e11d48;"></i> Quản Lý Mã Giảm Giá (Voucher)
            </h2>
            <p>Tạo và thiết lập chính sách khuyến mãi, mã ưu đãi tự động áp dụng tại Giỏ hàng & Thanh toán</p>
        </div>
        <div class="header-actions">
            <button type="button" class="admin-btn admin-btn-primary" onclick="openAddVoucherModal()">
                <i class="fa-solid fa-plus"></i> Thêm Voucher Mới
            </button>
        </div>
    </div>

    <?php if ($msg): ?>
        <div style="background: #f0fdf4; border: 1px solid #86efac; color: #16a34a; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-circle-check" style="font-size: 18px;"></i>
            <span><?php echo htmlspecialchars($msg); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($err): ?>
        <div style="background: #fef2f2; border: 1px solid #fca5a5; color: #dc2626; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-circle-exclamation" style="font-size: 18px;"></i>
            <span><?php echo htmlspecialchars($err); ?></span>
        </div>
    <?php endif; ?>

    <div class="kpi-grid">
        <div class="kpi-card kpi-orders">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #ffe4e6; color: #e11d48; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
                <i class="fa-solid fa-ticket"></i>
            </div>
            <div>
                <div style="font-size: 12.5px; color: #64748b; font-weight: 600; text-transform: uppercase;">Tổng số voucher</div>
                <div style="font-size: 24px; font-weight: 800; color: #0f172a; line-height: 1.2;"><?php echo number_format($stats['total'] ?? 0); ?></div>
                <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">Trong cơ sở dữ liệu</div>
            </div>
        </div>

        <div class="kpi-card kpi-revenue">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
                <i class="fa-solid fa-toggle-on"></i>
            </div>
            <div>
                <div style="font-size: 12.5px; color: #64748b; font-weight: 600; text-transform: uppercase;">Đang kích hoạt</div>
                <div style="font-size: 24px; font-weight: 800; color: #10b981; line-height: 1.2;" id="kpi-active-count"><?php echo number_format($stats['active'] ?? 0); ?></div>
                <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">Khách có thể nhập</div>
            </div>
        </div>

        <div class="kpi-card kpi-products">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div>
                <div style="font-size: 12.5px; color: #64748b; font-weight: 600; text-transform: uppercase;">Tổng lượt sử dụng</div>
                <div style="font-size: 24px; font-weight: 800; color: #0284c7; line-height: 1.2;"><?php echo number_format($stats['total_used'] ?? 0); ?></div>
                <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">Đã áp dụng thành công</div>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <form method="get" action="quanlyvoucher.php" style="display: flex; gap: 8px; flex: 1; max-width: 400px;">
                <input type="text" name="kw" value="<?php echo htmlspecialchars($kw); ?>" placeholder="Tìm kiếm theo mã hoặc mô tả..." style="flex: 1; padding: 9px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px; outline: none;">
                <button type="submit" class="admin-btn admin-btn-secondary" style="padding: 9px 16px;">
                    <i class="fa-solid fa-magnifying-glass"></i> Tìm
                </button>
                <?php if (!empty($kw)): ?>
                    <a href="quanlyvoucher.php" class="admin-btn admin-btn-secondary" style="padding: 9px 14px;" title="Xóa tìm kiếm">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Mã Voucher</th>
                        <th>Mô Tả Khuyến Mãi</th>
                        <th>Giá Trị Giảm</th>
                        <th>Đơn Tối Thiểu</th>
                        <th>Hạn Dùng</th>
                        <th>Lượt Dùng</th>
                        <th style="text-align: center; width: 150px;">Bật / Tắt</th>
                        <th style="text-align: right; width: 100px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($vouchers)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 30px; color: #94a3b8;">
                                <i class="fa-solid fa-ticket-simple" style="font-size: 32px; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                Chưa có mã giảm giá nào phù hợp.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($vouchers as $v): ?>
                            <?php 
                                $isExpired = (!empty($v['NgayHetHan']) && strtotime($v['NgayHetHan']) < strtotime('today'));
                                $giaTriText = ($v['PhanTramGiam'] > 0) ? ($v['PhanTramGiam'] . '%') : (number_format($v['SoTienGiam']) . ' đ');
                                $isActive = ($v['TrangThai'] == 1 && !$isExpired);
                            ?>
                            <tr id="voucher-row-<?php echo $v['MaVoucher']; ?>">
                                <td>#<?php echo $v['MaVoucher']; ?></td>
                                <td>
                                    <span style="display: inline-flex; align-items: center; gap: 6px; font-family: monospace; font-weight: 700; font-size: 13.5px; background: #fff1f2; color: #e11d48; padding: 4px 10px; border-radius: 6px; border: 1px solid #fecdd3;">
                                        <i class="fa-solid fa-tag"></i> <?php echo htmlspecialchars($v['Code']); ?>
                                    </span>
                                </td>
                                <td style="font-size: 13.5px; color: #334155;">
                                    <?php echo htmlspecialchars($v['MoTa']); ?>
                                </td>
                                <td>
                                    <span style="font-weight: 700; color: #16a34a; font-size: 14px;">
                                        <?php echo $giaTriText; ?>
                                    </span>
                                </td>
                                <td style="color: #64748b; font-size: 13px;">
                                    <?php echo number_format($v['DonToiThieu']); ?> đ
                                </td>
                                <td>
                                    <?php if ($isExpired): ?>
                                        <span style="color: #dc2626; font-weight: 600; font-size: 12.5px; display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fa-solid fa-triangle-exclamation"></i> <?php echo date('d/m/Y', strtotime($v['NgayHetHan'])); ?> (Hết hạn)
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #475569; font-size: 13px;">
                                            <?php echo date('d/m/Y', strtotime($v['NgayHetHan'])); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center; font-weight: 600; color: #334155;">
                                    <?php echo number_format($v['LuotDung']); ?>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: inline-flex; align-items: center; gap: 8px;">
                                        <label class="voucher-switch" title="Bấm để bật hoặc tắt mã voucher">
                                            <input type="checkbox" <?php echo $isActive ? 'checked' : ''; ?> onchange="ajaxToggleVoucher(<?php echo $v['MaVoucher']; ?>, this)">
                                            <span class="voucher-slider"></span>
                                        </label>
                                        <span id="badge-status-<?php echo $v['MaVoucher']; ?>" class="badge-pill <?php echo $isActive ? 'success' : 'danger'; ?>" style="font-size: 11px; padding: 2px 7px;">
                                            <?php echo $isActive ? 'Bật' : 'Tắt'; ?>
                                        </span>
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    <a href="quanlyvoucher.php?action=delete&id=<?php echo $v['MaVoucher']; ?>" class="action-btn" onclick="return confirm('Bạn có chắc chắn muốn xóa mã voucher này?');" title="Xóa voucher" style="color: #ef4444; background: #fee2e2; border-radius: 6px; padding: 5px 10px; font-size: 12px; display: inline-flex; align-items: center; gap: 4px; text-decoration: none;">
                                        <i class="fa-solid fa-trash"></i> Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="addVoucherModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; border-radius: 16px; width: 100%; max-width: 500px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); overflow: hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; background: #fafbfc;">
            <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-ticket" style="color: #e11d48;"></i> Thêm Mã Giảm Giá Mới
            </h3>
            <button type="button" onclick="closeAddVoucherModal()" style="border: none; background: transparent; font-size: 18px; color: #94a3b8; cursor: pointer;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="post" action="quanlyvoucher.php" style="padding: 24px;">
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Mã Voucher (Code) *</label>
                <input type="text" name="txtCode" required placeholder="Vd: TET2026, TRIAN10" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; font-weight: 700; text-transform: uppercase; outline: none; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Mô tả ưu đãi *</label>
                <input type="text" name="txtMoTa" required placeholder="Vd: Giảm 10% cho tất cả đơn hàng..." style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Hình thức giảm</label>
                    <select name="txtLoaiGiam" id="selLoaiGiam" onchange="updateLoaiGiamUnit()" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px; outline: none; box-sizing: border-box;">
                        <option value="phan_tram">Giảm theo %</option>
                        <option value="so_tien">Giảm số tiền cố định (VNĐ)</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Mức giảm <span id="lblDonVi">(%)</span> *</label>
                    <input type="number" name="txtGiaTri" required min="1" placeholder="10" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 22px;">
                <div>
                    <label style="display: block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Đơn tối thiểu (VNĐ)</label>
                    <input type="number" name="txtDonToiThieu" value="0" min="0" step="10000" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Ngày hết hạn</label>
                    <input type="date" name="txtNgayHetHan" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px; outline: none; box-sizing: border-box;">
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="admin-btn admin-btn-secondary" onclick="closeAddVoucherModal()">Hủy</button>
                <button type="submit" name="btnThemVoucher" class="admin-btn admin-btn-primary">
                    <i class="fa-solid fa-check"></i> Lưu Voucher
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddVoucherModal() {
    const modal = document.getElementById('addVoucherModal');
    if (modal) modal.style.display = 'flex';
}

function closeAddVoucherModal() {
    const modal = document.getElementById('addVoucherModal');
    if (modal) modal.style.display = 'none';
}

function updateLoaiGiamUnit() {
    const sel = document.getElementById('selLoaiGiam');
    const lbl = document.getElementById('lblDonVi');
    if (sel && lbl) {
        lbl.innerText = (sel.value === 'phan_tram') ? '(%)' : '(VNĐ)';
    }
}

function ajaxToggleVoucher(id, checkbox) {
    checkbox.disabled = true;
    fetch('quanlyvoucher.php?action=ajax_toggle&id=' + id)
        .then(res => res.json())
        .then(data => {
            checkbox.disabled = false;
            if (data.success) {
                checkbox.checked = (data.status === 1);
                const badge = document.getElementById('badge-status-' + id);
                if (badge) {
                    if (data.status === 1) {
                        badge.className = 'badge-pill success';
                        badge.innerText = 'Bật';
                    } else {
                        badge.className = 'badge-pill danger';
                        badge.innerText = 'Tắt';
                    }
                }
                const kpiActive = document.getElementById('kpi-active-count');
                if (kpiActive && typeof data.active_cnt !== 'undefined') {
                    kpiActive.innerText = data.active_cnt;
                }
                if (window.showAdminToast) {
                    const actionName = (data.status === 1) ? 'Bật' : 'Tắt';
                    window.showAdminToast('Đã ' + actionName.toLowerCase() + ' voucher ' + data.code + ' thành công!', 'success');
                }
            } else {
                checkbox.checked = !checkbox.checked;
                if (window.showAdminToast) {
                    window.showAdminToast('Không thể cập nhật voucher!', 'error');
                }
            }
        })
        .catch(err => {
            checkbox.disabled = false;
            checkbox.checked = !checkbox.checked;
            console.error(err);
        });
}
</script>

<?php include '../includes/footer.php'; ?>
