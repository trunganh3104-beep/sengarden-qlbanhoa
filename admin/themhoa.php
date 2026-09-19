<?php
require_once '../includes/auth.php';
requireAdmin();
require_once '../includes/header.php';
include_once '../DataProvider.php';
include_once 'nav.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btnThem'])) {
    $maLoai = intval($_POST['cboLoai']);
    $tenHoa = trim($_POST['txtTenHoa']);
    $giaBan = floatval($_POST['txtGiaBan']);
    $thanhPhan = trim($_POST['txtThanhPhan']);

    if (empty($tenHoa) || $giaBan <= 0 || $maLoai <= 0) {
        $error = 'Vui lòng điền đầy đủ thông tin tên, loại hoa và giá bán hợp lệ!';
    } elseif (!isset($_FILES['fileHinh']) || $_FILES['fileHinh']['error'] != 0) {
        $error = 'Vui lòng tải lên file hình ảnh hoa!';
    } else {
        $ext = strtolower(pathinfo($_FILES['fileHinh']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowed)) {
            $error = 'Chỉ chấp nhận file ảnh định dạng JPG, JPEG, PNG, WEBP!';
        } else {
            $fileName = time() . '_' . basename($_FILES['fileHinh']['name']);
            $targetPath = '../hoa/' . $fileName;

            if (move_uploaded_file($_FILES['fileHinh']['tmp_name'], $targetPath)) {
                $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                mysqli_set_charset($conn, "utf8");

                $tenHoaEsc = mysqli_real_escape_string($conn, $tenHoa);
                $thanhPhanEsc = mysqli_real_escape_string($conn, $thanhPhan);

                $sql = "INSERT INTO hoa (MaLoai, TenHoa, GiaBan, Hinh, ThanhPhan) 
                        VALUES ($maLoai, '$tenHoaEsc', $giaBan, '$fileName', '$thanhPhanEsc')";

                if (mysqli_query($conn, $sql)) {
                    mysqli_close($conn);
                    echo "<script>location.href='quanlyhoa.php?msg=Thêm hoa mới thành công!';</script>";
                    exit;
                } else {
                    $error = 'Lỗi cơ sở dữ liệu: ' . mysqli_error($conn);
                }
                mysqli_close($conn);
            } else {
                $error = 'Không thể lưu file ảnh vào thư mục hoa!';
            }
        }
    }
}

$rsLoai = DataProvider::ExecuteQuery("SELECT * FROM loaihoa ORDER BY TenLoai ASC");
?>

<div class="admin-container" style="max-width: 760px;">
    <div class="admin-page-header">
        <div class="header-left">
            <h2><i class="fa-solid fa-circle-plus" style="color: #e11d48;"></i> Thêm Hoa Mới</h2>
            <p>Nhập thông tin chi tiết sản phẩm hoa để trưng bày trên website</p>
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
                        <input type="text" id="txtTenHoa" name="txtTenHoa" class="admin-form-control" placeholder="Ví dụ: Bó Hoa Hồng Đỏ Ecuador Luxury" required value="<?php echo htmlspecialchars($_POST['txtTenHoa'] ?? ''); ?>">
                    </div>

                    
                    <div class="admin-form-group">
                        <label for="cboLoai">Danh mục loại hoa <span style="color: #e11d48;">*</span></label>
                        <select id="cboLoai" name="cboLoai" class="admin-form-control" required>
                            <option value="">-- Chọn danh mục loại hoa --</option>
                            <?php while ($l = mysqli_fetch_assoc($rsLoai)): ?>
                                <option value="<?php echo $l['MaLoai']; ?>" <?php echo (isset($_POST['cboLoai']) && $_POST['cboLoai'] == $l['MaLoai']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($l['TenLoai']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    
                    <div class="admin-form-group">
                        <label for="txtGiaBan">Giá niêm yết (VNĐ) <span style="color: #e11d48;">*</span></label>
                        <input type="number" id="txtGiaBan" name="txtGiaBan" class="admin-form-control" placeholder="Ví dụ: 450000" min="1000" step="1000" required value="<?php echo htmlspecialchars($_POST['txtGiaBan'] ?? ''); ?>">
                    </div>
                </div>

                
                <div class="admin-form-group">
                    <label>Hình ảnh đại diện <span style="color: #e11d48;">*</span></label>
                    <div style="display: flex; gap: 20px; align-items: center; background: #f8fafc; padding: 16px; border-radius: var(--adm-radius-sm); border: 1px dashed var(--adm-border);">
                        <div id="preview-wrapper" style="width: 80px; height: 80px; border-radius: 8px; border: 1px solid #e2e8f0; background: #fff; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                            <img id="image-preview" src="#" alt="Xem trước" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                            <i id="preview-icon" class="fa-solid fa-image" style="font-size: 28px; color: #cbd5e1;"></i>
                        </div>
                        <div style="flex: 1;">
                            <input type="file" id="fileHinh" name="fileHinh" accept="image/*" required style="font-size: 13.5px; color: #475569;">
                            <p style="margin: 6px 0 0; font-size: 12px; color: #94a3b8;">Hỗ trợ định dạng JPG, PNG, WEBP. Nên chọn ảnh vuông sắc nét.</p>
                        </div>
                    </div>
                </div>

                
                <div class="admin-form-group">
                    <label for="txtThanhPhan">Thành phần chi tiết & Ý nghĩa hoa</label>
                    <textarea id="txtThanhPhan" name="txtThanhPhan" rows="4" class="admin-form-control" placeholder="Ví dụ: 19 bông hồng đỏ Ecuador, lá bạc nhập khẩu, giấy gói phong cách Hàn Quốc..."><?php echo htmlspecialchars($_POST['txtThanhPhan'] ?? ''); ?></textarea>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--adm-border);">
                    <a href="quanlyhoa.php" class="admin-btn admin-btn-outline">Hủy bỏ</a>
                    <button type="submit" name="btnThem" class="admin-btn admin-btn-primary">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Lưu Sản Phẩm Mới
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
    const icon = document.getElementById('preview-icon');
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            preview.src = event.target.result;
            preview.style.display = 'block';
            icon.style.display = 'none';
        }
        reader.readAsDataURL(file);
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>