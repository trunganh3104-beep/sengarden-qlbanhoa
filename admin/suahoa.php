<?php
ob_start();
require_once '../includes/auth.php';
include_once '../DataProvider.php';

requireAdmin();

if (!isset($_GET['MaHoa'])) {
    header("Location: quanlyhoa.php");
    exit;
}

$maHoa = intval($_GET['MaHoa']);
$rsHoa = DataProvider::ExecuteQuery("SELECT * FROM hoa WHERE MaHoa = $maHoa");
if (mysqli_num_rows($rsHoa) == 0) {
    header("Location: quanlyhoa.php?err=Không tìm thấy sản phẩm!");
    exit;
}
$hoa = mysqli_fetch_assoc($rsHoa);
$error = '';

require_once '../includes/header.php';
include_once 'nav.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btnSua'])) {
    $maLoai = intval($_POST['cboLoai']);
    $tenHoa = trim($_POST['txtTenHoa']);
    $giaBan = floatval($_POST['txtGiaBan']);
    $thanhPhan = trim($_POST['txtThanhPhan']);
    $hinhMoi = $hoa['Hinh'];

    if (empty($tenHoa) || $giaBan <= 0 || $maLoai <= 0) {
        $error = 'Vui lòng điền đầy đủ dữ liệu hợp lệ!';
    } else {
        if (isset($_FILES['fileHinh']) && $_FILES['fileHinh']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['fileHinh']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $allowed)) {
                $newFileName = time() . '_' . basename($_FILES['fileHinh']['name']);
                if (move_uploaded_file($_FILES['fileHinh']['tmp_name'], '../hoa/' . $newFileName)) {
                    $hinhMoi = $newFileName;
                }
            }
        }

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        mysqli_set_charset($conn, "utf8");

        $tenHoaEsc = mysqli_real_escape_string($conn, $tenHoa);
        $thanhPhanEsc = mysqli_real_escape_string($conn, $thanhPhan);

        $sql = "UPDATE hoa SET 
                    MaLoai = $maLoai, 
                    TenHoa = '$tenHoaEsc', 
                    GiaBan = $giaBan, 
                    Hinh = '$hinhMoi', 
                    ThanhPhan = '$thanhPhanEsc' 
                WHERE MaHoa = $maHoa";

        if (mysqli_query($conn, $sql)) {
            mysqli_close($conn);
            echo "<script>location.href='quanlyhoa.php?msg=Cập nhật thông tin hoa #$maHoa thành công!';</script>";
            exit;
        } else {
            $error = 'Lỗi cập nhật cơ sở dữ liệu: ' . mysqli_error($conn);
        }
        mysqli_close($conn);
    }
}

$rsLoai = DataProvider::ExecuteQuery("SELECT * FROM loaihoa ORDER BY TenLoai ASC");
?>

<div class="admin-container" style="max-width: 760px;">
    <div class="admin-page-header">
        <div class="header-left">
            <h2><i class="fa-solid fa-pen-to-square" style="color: #0284c7;"></i> Sửa Sản Phẩm Hoa #<?php echo $hoa['MaHoa']; ?></h2>
            <p>Cập nhật giá bán, hình ảnh hoặc mô tả thành phần hoa</p>
        </div>
        <div class="header-actions">
            <a href="quanlyhoa.php" class="admin-btn admin-btn-outline">
                <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="admin-card" style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 14px 20px; margin-bottom: 20px; font-weight: 500;">
            <i class="fa-solid fa-triangle-exclamation" style="margin-right: 8px;"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="admin-card">
        <div class="admin-card-body">
            <form method="post" enctype="multipart/form-data">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    
                    <div class="admin-form-group" style="grid-column: 1 / -1;">
                        <label for="txtTenHoa">Tên sản phẩm hoa <span style="color: #e11d48;">*</span></label>
                        <input type="text" id="txtTenHoa" name="txtTenHoa" class="admin-form-control" value="<?php echo htmlspecialchars($hoa['TenHoa']); ?>" required>
                    </div>

                    
                    <div class="admin-form-group">
                        <label for="cboLoai">Danh mục loại hoa <span style="color: #e11d48;">*</span></label>
                        <select id="cboLoai" name="cboLoai" class="admin-form-control" required>
                            <?php while ($l = mysqli_fetch_assoc($rsLoai)): ?>
                                <option value="<?php echo $l['MaLoai']; ?>" <?php if ($l['MaLoai'] == $hoa['MaLoai']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($l['TenLoai']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    
                    <div class="admin-form-group">
                        <label for="txtGiaBan">Giá niêm yết (VNĐ) <span style="color: #e11d48;">*</span></label>
                        <input type="number" id="txtGiaBan" name="txtGiaBan" class="admin-form-control" value="<?php echo $hoa['GiaBan']; ?>" min="1000" step="1000" required>
                    </div>
                </div>

                
                <div class="admin-form-group">
                    <label>Hình ảnh sản phẩm</label>
                    <div style="display: flex; gap: 20px; align-items: center; background: #f8fafc; padding: 16px; border-radius: var(--adm-radius-sm); border: 1px dashed var(--adm-border);">
                        <div style="width: 80px; height: 80px; border-radius: 8px; border: 1px solid #e2e8f0; background: #fff; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                            <img id="image-preview" src="../hoa/<?php echo htmlspecialchars($hoa['Hinh']); ?>" alt="Ảnh hiện tại" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='../images/no-image.png'">
                        </div>
                        <div style="flex: 1;">
                            <label style="font-weight: 500; font-size: 13px; color: #475569; display: block; margin-bottom: 5px;">Chọn ảnh mới (để trống nếu giữ nguyên ảnh hiện tại):</label>
                            <input type="file" id="fileHinh" name="fileHinh" accept="image/*" style="font-size: 13.5px; color: #475569;">
                            <p style="margin: 6px 0 0; font-size: 12px; color: #94a3b8;">Hỗ trợ JPG, PNG, WEBP.</p>
                        </div>
                    </div>
                </div>

                
                <div class="admin-form-group">
                    <label for="txtThanhPhan">Thành phần chi tiết & Ý nghĩa hoa</label>
                    <textarea id="txtThanhPhan" name="txtThanhPhan" rows="4" class="admin-form-control"><?php echo htmlspecialchars($hoa['ThanhPhan']); ?></textarea>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--adm-border);">
                    <a href="quanlyhoa.php" class="admin-btn admin-btn-outline">Hủy bỏ</a>
                    <button type="submit" name="btnSua" class="admin-btn admin-btn-primary">
                        <i class="fa-solid fa-check"></i> Cập Nhật Thay Đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('fileHinh').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('image-preview');
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            preview.src = event.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>