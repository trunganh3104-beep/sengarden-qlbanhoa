<?php
require_once '../includes/auth.php';
include_once '../DataProvider.php';

requireAdmin();

if (isset($_GET['action']) && $_GET['action'] === 'ajax_delete' && isset($_GET['MaHoa'])) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    $maHoa = intval($_GET['MaHoa']);
    $res = ['success' => false, 'message' => ''];

    if ($maHoa > 0) {
        $checkOrder = DataProvider::ExecuteQuery("SELECT MaHoa FROM chitiethd WHERE MaHoa = $maHoa LIMIT 1");
        if (mysqli_num_rows($checkOrder) > 0) {
            $res['message'] = 'Không thể xóa vì hoa này đã có trong đơn hàng của khách!';
        } else {
            $rsHinh = DataProvider::ExecuteQuery("SELECT Hinh FROM hoa WHERE MaHoa = $maHoa");
            if ($rowH = mysqli_fetch_assoc($rsHinh)) {
                $filePath = "../hoa/" . $rowH['Hinh'];
                if (!empty($rowH['Hinh']) && file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            DataProvider::ExecuteQuery("DELETE FROM hoa WHERE MaHoa = $maHoa");
            $res['success'] = true;
            $res['message'] = "Đã xóa thành công sản phẩm #$maHoa!";
        }
    }
    echo json_encode($res);
    exit;
}

require_once '../includes/header.php';
include_once 'nav.php';

$sql = "SELECT h.*, l.TenLoai FROM hoa h JOIN loaihoa l ON h.MaLoai = l.MaLoai ORDER BY h.MaHoa DESC";
$rs = DataProvider::ExecuteQuery($sql);
$totalFlowers = mysqli_num_rows($rs);

$rsLoai = DataProvider::ExecuteQuery("SELECT * FROM loaihoa ORDER BY TenLoai ASC");
?>

<div class="admin-container">
    
    <div class="admin-page-header">
        <div class="header-left">
            <h2>
                <i class="fa-solid fa-fan" style="color: #e11d48;"></i> Quản Lý Sản Phẩm Hoa
            </h2>
            <p>Tổng cộng có <strong id="flower-total-count"><?php echo $totalFlowers; ?></strong> mẫu hoa đang lưu hành trên hệ thống</p>
        </div>
        <div class="header-actions">
            <a href="themhoa.php" class="admin-btn admin-btn-primary">
                <i class="fa-solid fa-circle-plus"></i> + Thêm hoa mới
            </a>
        </div>
    </div>

    
    <div class="admin-toolbar">
        <div class="admin-toolbar-left">
            
            <div class="admin-search-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="filter-keyword" class="admin-search-input" placeholder="Tìm theo tên hoa, mã hoa, thành phần...">
            </div>

            
            <select id="filter-category" class="admin-filter-select">
                <option value="">-- Tất cả loại hoa --</option>
                <?php while ($cat = mysqli_fetch_assoc($rsLoai)): ?>
                    <option value="<?php echo htmlspecialchars($cat['TenLoai']); ?>">
                        <?php echo htmlspecialchars($cat['TenLoai']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div style="font-size: 13px; color: #64748b;">
            Hiển thị: <strong id="filter-count" style="color: #0f172a;"><?php echo $totalFlowers; ?></strong> sản phẩm
        </div>
    </div>

    
    <div class="admin-table-card">
        <div class="admin-table-responsive">
            <table class="admin-table" id="table-flowers">
                <thead>
                    <tr>
                        <th width="70">Mã</th>
                        <th width="80">Ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Loại hoa</th>
                        <th>Giá bán</th>
                        <th>Thành phần & Mô tả</th>
                        <th width="110" style="text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="flower-tbody">
                    <?php if ($totalFlowers > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($rs)): ?>
                            <tr id="row-flower-<?php echo $row['MaHoa']; ?>" 
                                class="flower-row" 
                                data-id="<?php echo $row['MaHoa']; ?>"
                                data-name="<?php echo htmlspecialchars(mb_strtolower($row['TenHoa'])); ?>"
                                data-cat="<?php echo htmlspecialchars($row['TenLoai']); ?>"
                                data-desc="<?php echo htmlspecialchars(mb_strtolower($row['ThanhPhan'])); ?>">
                                <td>
                                    <span class="badge-pill slate">#<?php echo $row['MaHoa']; ?></span>
                                </td>
                                <td>
                                    <div class="flower-thumb-wrapper">
                                        <img src="../hoa/<?php echo htmlspecialchars($row['Hinh']); ?>" 
                                             class="flower-thumb"
                                             alt="<?php echo htmlspecialchars($row['TenHoa']); ?>" 
                                             onerror="this.src='../images/no-image.png';">
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: #0f172a; font-size: 14.5px;">
                                        <?php echo htmlspecialchars($row['TenHoa']); ?>
                                    </div>
                                    <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">
                                        Mã SP: SP-<?php echo str_pad($row['MaHoa'], 3, '0', STR_PAD_LEFT); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-pill info">
                                        <i class="fa-solid fa-tag"></i> <?php echo htmlspecialchars($row['TenLoai']); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong style="color: #e11d48; font-size: 15px;">
                                        <?php echo number_format($row['GiaBan']); ?> đ
                                    </strong>
                                </td>
                                <td style="max-width: 280px; font-size: 13px; color: #475569; line-height: 1.4;">
                                    <?php echo htmlspecialchars(mb_strimwidth($row['ThanhPhan'], 0, 75, '...')); ?>
                                </td>
                                <td style="text-align: right;">
                                    <div class="action-btn-group" style="justify-content: flex-end;">
                                        <a href="suahoa.php?MaHoa=<?php echo $row['MaHoa']; ?>" class="action-btn edit" title="Chỉnh sửa sản phẩm">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <button type="button" 
                                                class="action-btn delete btn-delete-flower" 
                                                data-id="<?php echo $row['MaHoa']; ?>" 
                                                data-name="<?php echo htmlspecialchars($row['TenHoa']); ?>" 
                                                title="Xóa hoa">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr id="empty-row">
                            <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">
                                <i class="fa-solid fa-box-open" style="font-size: 36px; margin-bottom: 10px; display: block; opacity: 0.5;"></i>
                                Chưa có sản phẩm hoa nào. Hãy nhấn "+ Thêm hoa mới" để bắt đầu!
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
    <?php if (isset($_GET['msg'])): ?>
        showAdminToast(<?php echo json_encode($_GET['msg']); ?>, 'success');
    <?php endif; ?>
    <?php if (isset($_GET['err'])): ?>
        showAdminToast(<?php echo json_encode($_GET['err']); ?>, 'error');
    <?php endif; ?>

    const searchInput = document.getElementById('filter-keyword');
    const categorySelect = document.getElementById('filter-category');
    const countDisplay = document.getElementById('filter-count');
    const rows = document.querySelectorAll('.flower-row');

    function applyFilters() {
        const keyword = searchInput.value.trim().toLowerCase();
        const selectedCat = categorySelect.value;
        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const id = row.getAttribute('data-id') || '';
            const cat = row.getAttribute('data-cat') || '';
            const desc = row.getAttribute('data-desc') || '';

            const matchesSearch = !keyword || name.includes(keyword) || id.includes(keyword) || desc.includes(keyword);
            const matchesCategory = !selectedCat || cat === selectedCat;

            if (matchesSearch && matchesCategory) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        countDisplay.textContent = visibleCount;
    }

    searchInput.addEventListener('input', applyFilters);
    categorySelect.addEventListener('change', applyFilters);

    document.addEventListener('click', function (e) {
        const delBtn = e.target.closest('.btn-delete-flower');
        if (!delBtn) return;

        e.preventDefault();
        const id = delBtn.getAttribute('data-id');
        const name = delBtn.getAttribute('data-name');

        if (!confirm(`Bạn có chắc chắn muốn xóa hoa "${name}" (#${id}) không?`)) {
            return;
        }

        const row = document.getElementById(`row-flower-${id}`);
        delBtn.style.pointerEvents = 'none';
        delBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

        fetch(`quanlyhoa.php?action=ajax_delete&MaHoa=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (row) {
                        row.style.transition = 'all 0.4s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(40px)';
                        setTimeout(() => {
                            row.remove();
                            applyFilters();
                        }, 400);
                    }
                    showAdminToast(data.message, 'success');
                } else {
                    delBtn.style.pointerEvents = 'auto';
                    delBtn.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
                    showAdminToast(data.message || 'Không thể xóa hoa!', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                delBtn.style.pointerEvents = 'auto';
                delBtn.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
                showAdminToast('Lỗi kết nối máy chủ!', 'error');
            });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>