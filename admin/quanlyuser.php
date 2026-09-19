<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../DataProvider.php';
require_once '../includes/auth.php';

if (isset($_GET['action']) && $_GET['action'] === 'ajax_toggle_role' && isset($_GET['MaKH'])) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    $maKH = intval($_GET['MaKH']);
    $res = ['success' => false, 'message' => ''];

    $currentUserId = $_SESSION['MaKH'] ?? ($_SESSION['user']['MaKH'] ?? 0);
    if ($currentUserId > 0 && $currentUserId == $maKH) {
        $res['message'] = 'Không thể tự thay đổi vai trò của tài khoản đang đăng nhập!';
        echo json_encode($res);
        exit;
    }

    $rsUser = DataProvider::ExecuteQuery("SELECT Role FROM khachhang WHERE MaKH = $maKH");
    if ($rowUser = mysqli_fetch_assoc($rsUser)) {
        $newRole = ($rowUser['Role'] == 1) ? 0 : 1;
        DataProvider::ExecuteQuery("UPDATE khachhang SET Role = $newRole WHERE MaKH = $maKH");
        $res = [
            'success' => true,
            'newRole' => $newRole,
            'message' => ($newRole == 1) ? 'Đã nâng cấp tài khoản lên Quản trị viên!' : 'Đã chuyển tài khoản về Khách hàng thường!'
        ];
    } else {
        $res['message'] = 'Không tìm thấy tài khoản người dùng!';
    }
    echo json_encode($res);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'ajax_delete_user' && isset($_GET['MaKH'])) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    $maKH = intval($_GET['MaKH']);
    $res = ['success' => false, 'message' => ''];

    $currentUserId = $_SESSION['MaKH'] ?? ($_SESSION['user']['MaKH'] ?? 0);
    if ($currentUserId > 0 && $currentUserId == $maKH) {
        $res['message'] = 'Không thể xóa tài khoản của chính bạn!';
        echo json_encode($res);
        exit;
    }

    if ($maKH > 0) {
        $checkOrder = DataProvider::ExecuteQuery("SELECT MaHD FROM hoadon WHERE MaKH = $maKH LIMIT 1");
        if (mysqli_num_rows($checkOrder) > 0) {
            $res['message'] = 'Không thể xóa tài khoản này vì người dùng đã có lịch sử đặt hàng!';
        } else {
            DataProvider::ExecuteQuery("DELETE FROM khachhang WHERE MaKH = $maKH");
            $res = [
                'success' => true,
                'message' => "Đã xóa thành công tài khoản người dùng #$maKH!"
            ];
        }
    } else {
        $res['message'] = 'Mã tài khoản không hợp lệ!';
    }
    echo json_encode($res);
    exit;
}

require_once '../includes/auth.php';
requireAdmin();
require_once '../includes/header.php';
include_once 'nav.php';

$sql = "SELECT MaKH, TenDN, HoTen, DiaChi, DienThoai, Email, Role FROM khachhang ORDER BY MaKH DESC";
$rs = DataProvider::ExecuteQuery($sql);
$allUsers = [];
$countAdmin = 0;
$countCustomer = 0;

while ($u = mysqli_fetch_assoc($rs)) {
    $allUsers[] = $u;
    if ($u['Role'] == 1) $countAdmin++;
    else $countCustomer++;
}
$totalUsers = count($allUsers);
?>

<div class="admin-container">
    
    <div class="admin-page-header">
        <div class="header-left">
            <h2>
                <i class="fa-solid fa-users" style="color: #0284c7;"></i> Quản Lý Tài Khoản & Khách Hàng
            </h2>
            <p>Quản lý phân quyền quản trị viên, thông tin liên hệ và tài khoản khách hàng</p>
        </div>
        <div class="header-actions">
            <span class="badge-pill info" style="font-size: 13px; padding: 6px 14px;">
                <i class="fa-solid fa-user-shield"></i> <?php echo $countAdmin; ?> Quản trị viên
            </span>
            <span class="badge-pill success" style="font-size: 13px; padding: 6px 14px;">
                <i class="fa-solid fa-users"></i> <?php echo $countCustomer; ?> Khách hàng
            </span>
        </div>
    </div>

    
    <div class="admin-toolbar">
        <div class="admin-toolbar-left">
            <div class="admin-search-wrapper" style="min-width: 280px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="user-search" class="admin-search-input" placeholder="Tìm theo tên đăng nhập, họ tên, SĐT, email...">
            </div>

            <select id="user-filter-role" class="admin-filter-select">
                <option value="">-- Tất cả vai trò --</option>
                <option value="admin">Quản trị viên (Admin)</option>
                <option value="customer">Khách hàng</option>
            </select>
        </div>

        <div style="font-size: 13px; color: #64748b;">
            Hiển thị: <strong id="user-display-count" style="color: #0f172a;"><?php echo $totalUsers; ?></strong> người dùng
        </div>
    </div>

    
    <div class="admin-table-card">
        <div class="admin-table-responsive">
            <table class="admin-table" id="table-users">
                <thead>
                    <tr>
                        <th width="80">Mã KH</th>
                        <th>Người dùng</th>
                        <th>Số điện thoại</th>
                        <th>Email</th>
                        <th>Địa chỉ giao hàng</th>
                        <th>Vai trò (Phân quyền)</th>
                        <th width="90" style="text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="users-tbody">
                    <?php if ($totalUsers > 0): ?>
                        <?php foreach ($allUsers as $row): 
                            $firstLetter = mb_substr($row['HoTen'] ?: $row['TenDN'], 0, 1, 'UTF-8');
                            $roleStr = ($row['Role'] == 1) ? 'admin' : 'customer';
                        ?>
                            <tr id="row-user-<?php echo $row['MaKH']; ?>"
                                class="user-row"
                                data-role="<?php echo $roleStr; ?>"
                                data-id="<?php echo $row['MaKH']; ?>"
                                data-username="<?php echo htmlspecialchars(mb_strtolower($row['TenDN'])); ?>"
                                data-name="<?php echo htmlspecialchars(mb_strtolower($row['HoTen'])); ?>"
                                data-phone="<?php echo htmlspecialchars($row['DienThoai']); ?>"
                                data-email="<?php echo htmlspecialchars(mb_strtolower($row['Email'])); ?>">
                                <td>
                                    <span class="badge-pill slate">#<?php echo $row['MaKH']; ?></span>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div class="user-avatar-circle" style="<?php echo ($row['Role'] == 1) ? 'background: linear-gradient(135deg, #e11d48, #be123c);' : 'background: linear-gradient(135deg, #0284c7, #0369a1);'; ?>">
                                            <?php echo htmlspecialchars($firstLetter); ?>
                                        </div>
                                        <div>
                                            <strong style="color: #0f172a; font-size: 14px;"><?php echo htmlspecialchars($row['HoTen'] ?: $row['TenDN']); ?></strong>
                                            <div style="font-size: 12px; color: #94a3b8;">
                                                @<?php echo htmlspecialchars($row['TenDN']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($row['DienThoai'])): ?>
                                        <i class="fa-solid fa-phone" style="color: #10b981; font-size: 12px; margin-right: 4px;"></i>
                                        <?php echo htmlspecialchars($row['DienThoai']); ?>
                                    <?php else: ?>
                                        <span style="color: #94a3b8; font-style: italic;">Chưa cập nhật</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['Email'])): ?>
                                        <i class="fa-solid fa-envelope" style="color: #6366f1; font-size: 12px; margin-right: 4px;"></i>
                                        <?php echo htmlspecialchars($row['Email']); ?>
                                    <?php else: ?>
                                        <span style="color: #94a3b8; font-style: italic;">Chưa có</span>
                                    <?php endif; ?>
                                </td>
                                <td style="max-width: 220px; font-size: 13px; color: #475569;" title="<?php echo htmlspecialchars($row['DiaChi']); ?>">
                                    <?php echo htmlspecialchars(mb_strimwidth($row['DiaChi'] ?: 'Chưa cập nhật', 0, 32, '...')); ?>
                                </td>
                                <td class="cell-role">
                                    <?php if ($row['Role'] == 1): ?>
                                        <button type="button" class="admin-btn admin-btn-primary admin-btn-sm btn-toggle-role" data-id="<?php echo $row['MaKH']; ?>" title="Bấm để chuyển về Khách hàng">
                                            <i class="fa-solid fa-shield-halved"></i> Admin
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="admin-btn admin-btn-outline admin-btn-sm btn-toggle-role" data-id="<?php echo $row['MaKH']; ?>" style="color: #059669 !important; border-color: #a7f3d0; background: #ecfdf5;" title="Bấm để nâng cấp lên Admin">
                                            <i class="fa-solid fa-user"></i> Khách hàng
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <div class="action-btn-group" style="justify-content: flex-end;">
                                        <button type="button" class="action-btn delete btn-delete-user" data-id="<?php echo $row['MaKH']; ?>" data-name="<?php echo htmlspecialchars($row['TenDN']); ?>" title="Xóa tài khoản">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">
                                Chưa có dữ liệu tài khoản nào.
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
    const searchInput = document.getElementById('user-search');
    const roleSelect = document.getElementById('user-filter-role');
    const countDisplay = document.getElementById('user-display-count');
    const rows = document.querySelectorAll('.user-row');

    function applyUserFilters() {
        const query = searchInput.value.trim().toLowerCase();
        const roleFilter = roleSelect.value;
        let visible = 0;

        rows.forEach(row => {
            const role = row.getAttribute('data-role');
            const id = row.getAttribute('data-id') || '';
            const username = row.getAttribute('data-username') || '';
            const name = row.getAttribute('data-name') || '';
            const phone = row.getAttribute('data-phone') || '';
            const email = row.getAttribute('data-email') || '';

            const matchesRole = !roleFilter || (role === roleFilter);
            const matchesSearch = !query || id.includes(query) || username.includes(query) || name.includes(query) || phone.includes(query) || email.includes(query);

            if (matchesRole && matchesSearch) {
                row.style.display = '';
                visible++;
            } else {
                row.style.display = 'none';
            }
        });

        countDisplay.textContent = visible;
    }

    searchInput.addEventListener('input', applyUserFilters);
    roleSelect.addEventListener('change', applyUserFilters);

    document.addEventListener('click', function (e) {
        const roleBtn = e.target.closest('.btn-toggle-role');
        if (roleBtn) {
            e.preventDefault();
            const id = roleBtn.getAttribute('data-id');
            const row = document.getElementById(`row-user-${id}`);
            roleBtn.style.pointerEvents = 'none';
            roleBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

            fetch(`quanlyuser.php?action=ajax_toggle_role&MaKH=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (data.newRole === 1) {
                            row.setAttribute('data-role', 'admin');
                            roleBtn.className = 'admin-btn admin-btn-primary admin-btn-sm btn-toggle-role';
                            roleBtn.style = '';
                            roleBtn.title = 'Bấm để chuyển về Khách hàng';
                            roleBtn.innerHTML = '<i class="fa-solid fa-shield-halved"></i> Admin';
                        } else {
                            row.setAttribute('data-role', 'customer');
                            roleBtn.className = 'admin-btn admin-btn-outline admin-btn-sm btn-toggle-role';
                            roleBtn.style = 'color: #059669 !important; border-color: #a7f3d0; background: #ecfdf5;';
                            roleBtn.title = 'Bấm để nâng cấp lên Admin';
                            roleBtn.innerHTML = '<i class="fa-solid fa-user"></i> Khách hàng';
                        }
                        showAdminToast(data.message, 'success');
                    } else {
                        showAdminToast(data.message || 'Không thể đổi vai trò!', 'error');
                        roleBtn.innerHTML = (row.getAttribute('data-role') === 'admin') ? '<i class="fa-solid fa-shield-halved"></i> Admin' : '<i class="fa-solid fa-user"></i> Khách hàng';
                    }
                })
                .catch(err => {
                    console.error(err);
                    showAdminToast('Lỗi kết nối khi đổi vai trò!', 'error');
                })
                .finally(() => {
                    roleBtn.style.pointerEvents = 'auto';
                });
            return;
        }

        const delBtn = e.target.closest('.btn-delete-user');
        if (delBtn) {
            e.preventDefault();
            const id = delBtn.getAttribute('data-id');
            const username = delBtn.getAttribute('data-name');

            if (!confirm(`Bạn có chắc chắn muốn xóa tài khoản "${username}" (#${id})?`)) return;

            const row = document.getElementById(`row-user-${id}`);
            delBtn.style.pointerEvents = 'none';
            delBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

            fetch(`quanlyuser.php?action=ajax_delete_user&MaKH=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (row) {
                            row.style.transition = 'all 0.35s ease';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(30px)';
                            setTimeout(() => {
                                row.remove();
                                applyUserFilters();
                            }, 350);
                        }
                        showAdminToast(data.message, 'success');
                    } else {
                        delBtn.style.pointerEvents = 'auto';
                        delBtn.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
                        showAdminToast(data.message || 'Không thể xóa tài khoản này!', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    delBtn.style.pointerEvents = 'auto';
                    delBtn.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
                    showAdminToast('Lỗi máy chủ khi xóa tài khoản!', 'error');
                });
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>