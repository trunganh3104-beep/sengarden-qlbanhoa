<?php
include_once '../DataProvider.php';
require_once '../includes/auth.php';

if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action === 'ajax_add') {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        $tenLoai = trim($_POST['txtTenLoai'] ?? '');
        $res = ['success' => false, 'message' => ''];

        if (empty($tenLoai)) {
            $res['message'] = 'Tên loại hoa không được để trống!';
        } else {
            $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            mysqli_set_charset($conn, "utf8");
            $tenLoaiEsc = mysqli_real_escape_string($conn, $tenLoai);

            $sql = "INSERT INTO loaihoa (TenLoai) VALUES ('$tenLoaiEsc')";
            if (mysqli_query($conn, $sql)) {
                $newId = mysqli_insert_id($conn);
                $res = [
                    'success' => true,
                    'message' => "Thêm loại hoa \"$tenLoai\" thành công!",
                    'data' => [
                        'MaLoai' => $newId,
                        'TenLoai' => htmlspecialchars($tenLoai),
                        'SoSanPham' => 0
                    ]
                ];
            } else {
                $res['message'] = 'Lỗi thêm loại hoa: ' . mysqli_error($conn);
            }
            mysqli_close($conn);
        }
        echo json_encode($res);
        exit;
    }

    if ($action === 'ajax_delete') {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        $maLoai = intval($_GET['MaLoai'] ?? 0);
        $res = ['success' => false, 'message' => ''];

        if ($maLoai > 0) {
            $rsCount = DataProvider::ExecuteQuery("SELECT COUNT(*) AS total FROM hoa WHERE MaLoai = $maLoai");
            $rowCount = mysqli_fetch_assoc($rsCount);
            if ($rowCount['total'] > 0) {
                $res['message'] = 'Không thể xóa vì đang có ' . $rowCount['total'] . ' sản phẩm hoa thuộc loại này!';
            } else {
                DataProvider::ExecuteQuery("DELETE FROM loaihoa WHERE MaLoai = $maLoai");
                $res = [
                    'success' => true,
                    'message' => 'Đã xóa loại hoa thành công!'
                ];
            }
        } else {
            $res['message'] = 'Mã loại không hợp lệ!';
        }
        echo json_encode($res);
        exit;
    }

    if ($action === 'ajax_edit') {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        $maLoai = intval($_POST['txtMaLoaiEdit'] ?? 0);
        $tenLoai = trim($_POST['txtTenLoaiEdit'] ?? '');
        $res = ['success' => false, 'message' => ''];

        if ($maLoai > 0 && !empty($tenLoai)) {
            $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            mysqli_set_charset($conn, "utf8");
            $tenEsc = mysqli_real_escape_string($conn, $tenLoai);
            if (mysqli_query($conn, "UPDATE loaihoa SET TenLoai = '$tenEsc' WHERE MaLoai = $maLoai")) {
                $res = [
                    'success' => true,
                    'message' => 'Cập nhật loại hoa thành công!',
                    'data' => [
                        'MaLoai' => $maLoai,
                        'TenLoai' => htmlspecialchars($tenLoai)
                    ]
                ];
            } else {
                $res['message'] = 'Lỗi cập nhật: ' . mysqli_error($conn);
            }
            mysqli_close($conn);
        } else {
            $res['message'] = 'Tên loại hoa không được để trống!';
        }
        echo json_encode($res);
        exit;
    }
}

require_once '../includes/auth.php';
requireAdmin();
require_once '../includes/header.php';
include_once 'nav.php';

$rsLoai = DataProvider::ExecuteQuery("SELECT l.*, COUNT(h.MaHoa) AS SoSanPham FROM loaihoa l LEFT JOIN hoa h ON l.MaLoai = h.MaLoai GROUP BY l.MaLoai ORDER BY l.MaLoai ASC");
$totalCategories = mysqli_num_rows($rsLoai);
?>

<div class="admin-container" style="max-width: 960px;">
    
    <div class="admin-page-header">
        <div class="header-left">
            <h2>
                <i class="fa-solid fa-tags" style="color: #7e22ce;"></i> Quản Lý Danh Mục (Loại Hoa)
            </h2>
            <p>Phân loại danh mục sản phẩm hoa để khách hàng dễ dàng tìm kiếm</p>
        </div>
        <div class="header-actions">
            <span class="badge-pill info" style="font-size: 13px; padding: 6px 14px;">
                <i class="fa-solid fa-layer-group"></i> <strong id="total-cat-count"><?php echo $totalCategories; ?></strong> danh mục
            </span>
        </div>
    </div>

    
    <div class="admin-card" style="margin-bottom: 24px;">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <i class="fa-solid fa-plus-circle" style="color: #10b981;"></i> Thêm Danh Mục Mới
            </h3>
        </div>
        <div class="admin-card-body">
            <form id="form-add-loai" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 260px; position: relative;">
                    <i class="fa-solid fa-tag" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                    <input type="text" name="txtTenLoai" id="input-ten-loai" placeholder="Nhập tên loại hoa (ví dụ: Hoa Khai Trương, Hoa Sinh Nhật...)" required class="admin-form-control" style="padding-left: 38px;">
                </div>
                <button type="submit" id="btn-submit-add" class="admin-btn admin-btn-success">
                    <i class="fa-solid fa-plus"></i> Thêm Danh Mục
                </button>
            </form>
        </div>
    </div>

    
    <div class="admin-table-card">
        <div class="admin-table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="90">Mã loại</th>
                        <th>Tên loại hoa</th>
                        <th width="160">Số sản phẩm</th>
                        <th width="120" style="text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="list-loaihoa">
                    <?php while ($row = mysqli_fetch_assoc($rsLoai)): ?>
                        <tr id="row-loai-<?php echo $row['MaLoai']; ?>">
                            <td>
                                <span class="badge-pill slate">#CAT-<?php echo str_pad($row['MaLoai'], 2, '0', STR_PAD_LEFT); ?></span>
                            </td>
                            <td class="cell-name" data-name="<?php echo htmlspecialchars($row['TenLoai']); ?>">
                                <strong style="font-size: 14.5px; color: #0f172a;"><?php echo htmlspecialchars($row['TenLoai']); ?></strong>
                            </td>
                            <td>
                                <?php if ($row['SoSanPham'] > 0): ?>
                                    <span class="badge-pill success">
                                        <i class="fa-solid fa-fan"></i> <?php echo $row['SoSanPham']; ?> sản phẩm
                                    </span>
                                <?php else: ?>
                                    <span class="badge-pill slate">
                                        0 sản phẩm
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;" class="cell-actions">
                                <div class="action-btn-group" style="justify-content: flex-end;">
                                    <button type="button" class="action-btn edit btn-edit-loai" data-id="<?php echo $row['MaLoai']; ?>" title="Sửa tên loại hoa">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" class="action-btn delete btn-del-loai" data-id="<?php echo $row['MaLoai']; ?>" title="Xóa loại hoa">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const formAdd = document.getElementById('form-add-loai');
    const inputTen = document.getElementById('input-ten-loai');
    const btnSubmit = document.getElementById('btn-submit-add');
    const tbody = document.getElementById('list-loaihoa');
    const catCounter = document.getElementById('total-cat-count');

    formAdd.addEventListener('submit', function (e) {
        e.preventDefault();
        const tenLoai = inputTen.value.trim();
        if (!tenLoai) return;

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang thêm...';

        const formData = new FormData(formAdd);
        formData.append('action', 'ajax_add');

        fetch('quanlydanhmuc.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const row = document.createElement('tr');
                row.id = `row-loai-${data.data.MaLoai}`;
                row.style.background = '#ecfdf5';
                row.innerHTML = `
                    <td><span class="badge-pill slate">#CAT-${String(data.data.MaLoai).padStart(2, '0')}</span></td>
                    <td class="cell-name" data-name="${data.data.TenLoai}">
                        <strong style="font-size: 14.5px; color: #0f172a;">${data.data.TenLoai}</strong>
                    </td>
                    <td><span class="badge-pill slate">0 sản phẩm</span></td>
                    <td style="text-align: right;" class="cell-actions">
                        <div class="action-btn-group" style="justify-content: flex-end;">
                            <button type="button" class="action-btn edit btn-edit-loai" data-id="${data.data.MaLoai}" title="Sửa tên loại hoa">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button type="button" class="action-btn delete btn-del-loai" data-id="${data.data.MaLoai}" title="Xóa loại hoa">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
                setTimeout(() => { row.style.background = ''; }, 1000);
                inputTen.value = '';
                if (catCounter) catCounter.textContent = parseInt(catCounter.textContent || '0') + 1;
                showAdminToast(data.message, 'success');
            } else {
                showAdminToast(data.message || 'Thêm danh mục thất bại!', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showAdminToast('Lỗi kết nối máy chủ!', 'error');
        })
        .finally(() => {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fa-solid fa-plus"></i> Thêm Danh Mục';
        });
    });

    tbody.addEventListener('click', function (e) {
        const delBtn = e.target.closest('.btn-del-loai');
        if (delBtn) {
            e.preventDefault();
            const id = delBtn.getAttribute('data-id');
            const row = document.getElementById(`row-loai-${id}`);
            const ten = row ? row.querySelector('.cell-name').getAttribute('data-name') : '';

            if (!confirm(`Bạn có chắc chắn muốn xóa loại hoa "${ten}" (#${id})?`)) return;

            delBtn.style.pointerEvents = 'none';
            delBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

            fetch(`quanlydanhmuc.php?action=ajax_delete&MaLoai=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (row) {
                            row.style.transition = 'all 0.35s ease';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(30px)';
                            setTimeout(() => {
                                row.remove();
                                if (catCounter) catCounter.textContent = Math.max(0, parseInt(catCounter.textContent || '1') - 1);
                            }, 350);
                        }
                        showAdminToast(data.message, 'success');
                    } else {
                        delBtn.style.pointerEvents = 'auto';
                        delBtn.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
                        showAdminToast(data.message || 'Không thể xóa loại hoa này!', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    delBtn.style.pointerEvents = 'auto';
                    delBtn.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
                    showAdminToast('Lỗi máy chủ khi xóa!', 'error');
                });
            return;
        }

        const editBtn = e.target.closest('.btn-edit-loai');
        if (editBtn) {
            e.preventDefault();
            const id = editBtn.getAttribute('data-id');
            const row = document.getElementById(`row-loai-${id}`);
            const cellName = row.querySelector('.cell-name');
            const currentName = cellName.getAttribute('data-name');

            if (cellName.querySelector('.box-inline-edit')) return;

            cellName.innerHTML = `
                <div class="box-inline-edit" style="display: flex; gap: 8px; align-items: center;">
                    <input type="text" class="input-inline-val admin-form-control" value="${currentName}" style="padding: 6px 10px; font-size: 13.5px; width: 220px;">
                    <button type="button" class="btn-save-inline admin-btn admin-btn-success admin-btn-sm">
                        <i class="fa-solid fa-check"></i> Lưu
                    </button>
                    <button type="button" class="btn-cancel-inline admin-btn admin-btn-outline admin-btn-sm">
                        Hủy
                    </button>
                </div>
            `;
            const inputField = cellName.querySelector('.input-inline-val');
            inputField.focus();
            inputField.select();
            return;
        }

        const saveBtn = e.target.closest('.btn-save-inline');
        if (saveBtn) {
            e.preventDefault();
            const row = saveBtn.closest('tr');
            const id = row.id.replace('row-loai-', '');
            const inputVal = row.querySelector('.input-inline-val');
            const newName = inputVal.value.trim();

            if (!newName) {
                showAdminToast('Tên loại hoa không được để trống!', 'error');
                return;
            }

            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

            const formData = new FormData();
            formData.append('action', 'ajax_edit');
            formData.append('txtMaLoaiEdit', id);
            formData.append('txtTenLoaiEdit', newName);

            fetch('quanlydanhmuc.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const cellName = row.querySelector('.cell-name');
                    cellName.setAttribute('data-name', data.data.TenLoai);
                    cellName.innerHTML = `<strong style="font-size: 14.5px; color: #0f172a;">${data.data.TenLoai}</strong>`;
                    showAdminToast(data.message, 'success');
                } else {
                    showAdminToast(data.message || 'Cập nhật thất bại!', 'error');
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fa-solid fa-check"></i> Lưu';
                }
            })
            .catch(err => {
                console.error(err);
                showAdminToast('Lỗi máy chủ khi cập nhật!', 'error');
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fa-solid fa-check"></i> Lưu';
            });
            return;
        }

        const cancelBtn = e.target.closest('.btn-cancel-inline');
        if (cancelBtn) {
            e.preventDefault();
            const cellName = cancelBtn.closest('.cell-name');
            const originalName = cellName.getAttribute('data-name');
            cellName.innerHTML = `<strong style="font-size: 14.5px; color: #0f172a;">${originalName}</strong>`;
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>