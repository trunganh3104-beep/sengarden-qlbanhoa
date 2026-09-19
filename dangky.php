<?php
if (!ob_get_level()) {
    ob_start();
}

if (isset($_GET['action']) && $_GET['action'] === 'check_exist') {
    header('Content-Type: application/json; charset=utf-8');
    $field = $_GET['field'] ?? '';
    $val = trim($_GET['val'] ?? '');
    $res = ['exists' => false];

    if (!empty($val) && in_array($field, ['TenDN', 'Email'])) {
        $conn = getDbConnection();
        $stmt = ($field === 'Email') 
            ? $conn->prepare("SELECT MaKH FROM khachhang WHERE Email = ?")
            : $conn->prepare("SELECT MaKH FROM khachhang WHERE TenDN = ?");
        $stmt->bind_param("s", $val);
        $stmt->execute();
        $checkRS = $stmt->get_result();
        if ($checkRS && $checkRS->num_rows > 0) {
            $res['exists'] = true;
        }
        $stmt->close();
        mysqli_close($conn);
    }
    echo json_encode($res);
    exit;
}

require_once 'includes/auth.php';
include_once 'DataProvider.php';

if (isLoggedIn()) {
    header("Location: index.php");
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnDangKy'])) {
    $tenDN = trim($_POST['txtTenDN'] ?? '');
    $matKhau = $_POST['txtMatKhau'] ?? '';
    $matKhauNhapLai = $_POST['txtMatKhauNhapLai'] ?? '';
    $hoTen = trim($_POST['txtHoTen'] ?? '');
    $diaChi = trim($_POST['txtDiaChi'] ?? '');
    $dienThoai = trim($_POST['txtDienThoai'] ?? '');
    $email = trim($_POST['txtEmail'] ?? '');

    if (empty($tenDN) || empty($matKhau) || empty($hoTen)) {
        $error = 'Vui lòng điền đầy đủ các thông tin bắt buộc (*)!';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $tenDN)) {
        $error = 'Tên đăng nhập từ 3-30 ký tự, chỉ chứa chữ cái, số và dấu gạch dưới!';
    } elseif (strlen($matKhau) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
    } elseif ($matKhau !== $matKhauNhapLai) {
        $error = 'Mật khẩu nhập lại không khớp!';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Định dạng email không hợp lệ!';
    } elseif (!empty($dienThoai) && !preg_match('/^[0-9]{9,11}$/', $dienThoai)) {
        $error = 'Số điện thoại phải từ 9 đến 11 chữ số!';
    } else {
        $conn = getDbConnection();
        $stmtCheck = $conn->prepare("SELECT MaKH FROM khachhang WHERE TenDN = ?");
        $stmtCheck->bind_param("s", $tenDN);
        $stmtCheck->execute();
        $checkRS = $stmtCheck->get_result();

        if ($checkRS && $checkRS->num_rows > 0) {
            $error = 'Tên đăng nhập đã có người sử dụng. Vui lòng chọn tên khác!';
            $stmtCheck->close();
        } else {
            $stmtCheck->close();
            $matKhauHash = password_hash($matKhau, PASSWORD_DEFAULT);
            $roleDefault = 0;

            $stmtInsert = $conn->prepare("INSERT INTO khachhang (TenDN, MatKhau, HoTen, DiaChi, DienThoai, Email, Role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmtInsert->bind_param("ssssssi", $tenDN, $matKhauHash, $hoTen, $diaChi, $dienThoai, $email, $roleDefault);

            if ($stmtInsert->execute()) {
                $newMaKH = $conn->insert_id;
                $stmtInsert->close();

                $_SESSION['user'] = array(
                    'MaKH' => $newMaKH,
                    'TenDN' => $tenDN,
                    'HoTen' => $hoTen
                );
                $_SESSION['role'] = 0;

                mysqli_close($conn);
                header("Location: index.php?welcome=1");
                echo "<script>window.location.href='index.php?welcome=1';</script>";
                exit;
            } else {
                $error = 'Lỗi hệ thống khi đăng ký: ' . $stmtInsert->error;
                $stmtInsert->close();
            }
        }
        mysqli_close($conn);
    }
}

require_once 'includes/header.php';
?>

<style>

.register-page-wrapper {
    min-height: calc(85vh - 80px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 35px 15px;
}
.register-split-card {
    display: flex;
    width: 100%;
    max-width: 960px;
    background: #ffffff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
    border: 1px solid #f1f5f9;
}

.register-banner-side {
    flex: 0.9;
    background: linear-gradient(145deg, #e11d48 0%, #be123c 60%, #881337 100%);
    color: #fff;
    padding: 45px 35px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
}
.register-banner-side::after {
    content: '';
    position: absolute;
    bottom: -60px;
    left: -60px;
    width: 220px;
    height: 220px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 50%;
}
.register-banner-title {
    font-size: 26px;
    font-weight: 800;
    margin-bottom: 12px;
    line-height: 1.3;
}
.register-banner-desc {
    font-size: 14px;
    line-height: 1.6;
    color: #fecdd3;
    margin-bottom: 25px;
}
.reg-benefits-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.reg-benefit-item {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13.5px;
    color: #fff;
}
.reg-benefit-item i {
    width: 32px;
    height: 32px;
    background: rgba(255, 255, 255, 0.18);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.register-form-side {
    flex: 1.3;
    padding: 40px 45px;
}
.reg-header-title {
    font-size: 24px;
    font-weight: 800;
    color: #1e293b;
    margin: 0 0 6px;
}
.reg-header-subtitle {
    font-size: 13.5px;
    color: #64748b;
    margin-bottom: 22px;
}

.reg-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px 18px;
}
.reg-input-group {
    position: relative;
}
.reg-input-group.full-width {
    grid-column: 1 / -1;
}
.reg-input-group label {
    display: block;
    font-size: 12.5px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 5px;
}
.reg-input-group label span.req {
    color: #e11d48;
}
.reg-input-box {
    position: relative;
    display: flex;
    align-items: center;
}
.reg-input-box i.input-icon {
    position: absolute;
    left: 13px;
    color: #94a3b8;
    font-size: 14px;
}
.reg-input-box input {
    width: 100%;
    padding: 10px 12px 10px 38px;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    font-size: 13.5px;
    outline: none;
    transition: all 0.2s ease;
    background: #f8fafc;
}
.reg-input-box input:focus {
    background: #fff;
    border-color: #e11d48;
    box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.1);
}

.pwd-strength-container {
    margin-top: 6px;
}
.pwd-strength-bar {
    height: 4px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
    display: flex;
    gap: 3px;
}
.pwd-strength-segment {
    flex: 1;
    height: 100%;
    background: #e2e8f0;
    transition: background 0.25s ease;
}
.pwd-strength-text {
    font-size: 11px;
    margin-top: 4px;
    color: #64748b;
}

.btn-reg-submit {
    width: 100%;
    padding: 13px;
    background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 6px 20px rgba(225, 29, 72, 0.3);
    position: relative;
    overflow: hidden;
    transition: all 0.25s ease;
    margin-top: 10px;
}
.btn-reg-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(225, 29, 72, 0.45);
}
.btn-reg-submit::after {
    content: '';
    position: absolute;
    top: 0;
    left: -120%;
    width: 60%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.35), transparent);
    transform: skewX(-20deg);
}
.btn-reg-submit:hover::after {
    animation: sgShimmer 0.8s ease-in-out;
}

@media (max-width: 768px) {
    .register-split-card {
        flex-direction: column;
    }
    .reg-form-grid {
        grid-template-columns: 1fr;
    }
    .register-banner-side {
        padding: 30px 25px;
    }
    .register-form-side {
        padding: 30px 20px;
    }
}
</style>

<div class="register-page-wrapper">
    <div class="register-split-card" data-sg-reveal>
        
        <div class="register-banner-side">
            <div>
                <div style="font-size: 34px; margin-bottom: 12px;">🌸</div>
                <h2 class="register-banner-title">Gia Nhập Cùng Sen Garden</h2>
                <p class="register-banner-desc">
                    Tạo tài khoản để nhận ngay nhiều ưu đãi đặc quyền, quản lý đơn hoa dễ dàng và tích lũy điểm thưởng thành viên.
                </p>

                <ul class="reg-benefits-list">
                    <li class="reg-benefit-item">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                        <span>Thả tim lưu hoa yêu thích mọi lúc mọi nơi</span>
                    </li>
                    <li class="reg-benefit-item">
                        <i class="fa-solid fa-truck-fast"></i>
                        <span>Lưu sẵn địa chỉ giao hoa siêu tiện lợi</span>
                    </li>
                    <li class="reg-benefit-item">
                        <i class="fa-solid fa-star"></i>
                        <span>Tham gia đánh giá 5 sao & nhận xét dịch vụ</span>
                    </li>
                    <li class="reg-benefit-item">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Bảo mật thông tin khách hàng tuyệt đối</span>
                    </li>
                </ul>
            </div>

            <div style="font-size: 12px; color: #fecdd3; margin-top: 30px;">
                Đã có tài khoản? <a href="dangnhap.php" style="color: #fff; font-weight: 700; text-decoration: underline;">Đăng nhập tại đây</a>
            </div>
        </div>

        
        <div class="register-form-side">
            <h1 class="reg-header-title">Đăng Ký Tài Khoản</h1>
            <p class="reg-header-subtitle">Điền các thông tin dưới đây để tạo tài khoản mới</p>

            <?php if (!empty($error)): ?>
                <div style="background: #fef2f2; color: #991b1b; padding: 12px 16px; border-radius: 8px; font-size: 13.5px; border-left: 4px solid #ef4444; margin-bottom: 20px;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="dangky.php" id="registerForm">
                <div class="reg-form-grid">
                    
                    <div class="reg-input-group">
                        <label>Tên đăng nhập <span class="req">*</span></label>
                        <div class="reg-input-box">
                            <i class="fa-solid fa-user input-icon"></i>
                            <input type="text" name="txtTenDN" id="regTenDN" value="<?php echo htmlspecialchars($_POST['txtTenDN'] ?? ''); ?>" placeholder="Từ 3-30 ký tự..." required autofocus>
                        </div>
                        <div id="tenDNEcho" style="font-size: 11.5px; margin-top: 3px;"></div>
                    </div>

                    
                    <div class="reg-input-group">
                        <label>Họ và tên <span class="req">*</span></label>
                        <div class="reg-input-box">
                            <i class="fa-solid fa-id-card input-icon"></i>
                            <input type="text" name="txtHoTen" value="<?php echo htmlspecialchars($_POST['txtHoTen'] ?? ''); ?>" placeholder="Ví dụ: Nguyễn Văn A..." required>
                        </div>
                    </div>

                    
                    <div class="reg-input-group">
                        <label>Mật khẩu <span class="req">*</span></label>
                        <div class="reg-input-box">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <input type="password" name="txtMatKhau" id="regMatKhau" placeholder="Tối thiểu 6 ký tự..." required>
                        </div>
                        
                        <div class="pwd-strength-container">
                            <div class="pwd-strength-bar">
                                <div class="pwd-strength-segment" id="pwdSeg1"></div>
                                <div class="pwd-strength-segment" id="pwdSeg2"></div>
                                <div class="pwd-strength-segment" id="pwdSeg3"></div>
                            </div>
                            <div class="pwd-strength-text" id="pwdStrengthText">Độ mạnh: Nhập mật khẩu</div>
                        </div>
                    </div>

                    
                    <div class="reg-input-group">
                        <label>Xác nhận mật khẩu <span class="req">*</span></label>
                        <div class="reg-input-box">
                            <i class="fa-solid fa-shield-check input-icon"></i>
                            <input type="password" name="txtMatKhauNhapLai" id="regMatKhauNL" placeholder="Nhập lại đúng mật khẩu..." required>
                        </div>
                        <div id="pwdMatchText" style="font-size: 11.5px; margin-top: 4px;"></div>
                    </div>

                    
                    <div class="reg-input-group">
                        <label>Số điện thoại</label>
                        <div class="reg-input-box">
                            <i class="fa-solid fa-phone input-icon"></i>
                            <input type="tel" name="txtDienThoai" value="<?php echo htmlspecialchars($_POST['txtDienThoai'] ?? ''); ?>" placeholder="Nhập số điện thoại...">
                        </div>
                    </div>

                    
                    <div class="reg-input-group">
                        <label>Hòm thư Email</label>
                        <div class="reg-input-box">
                            <i class="fa-solid fa-envelope input-icon"></i>
                            <input type="email" name="txtEmail" value="<?php echo htmlspecialchars($_POST['txtEmail'] ?? ''); ?>" placeholder="Nhập email liên hệ...">
                        </div>
                    </div>

                    
                    <div class="reg-input-group full-width">
                        <label>Địa chỉ nhận hoa mặc định</label>
                        <div class="reg-input-box">
                            <i class="fa-solid fa-location-dot input-icon"></i>
                            <input type="text" name="txtDiaChi" value="<?php echo htmlspecialchars($_POST['txtDiaChi'] ?? ''); ?>" placeholder="Số nhà, tên đường, phường/xã, quận/huyện...">
                        </div>
                    </div>

                    
                    <div class="reg-input-group full-width" style="margin-top: 4px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: normal; font-size: 13px; color: #475569; cursor: pointer;">
                            <input type="checkbox" required checked style="accent-color: #e11d48;">
                            <span>Tôi đồng ý với <strong>Điều khoản dịch vụ</strong> & <strong>Chính sách bảo mật</strong> của Sen Garden.</span>
                        </label>
                    </div>

                    
                    <div class="reg-input-group full-width">
                        <button type="submit" name="btnDangKy" class="btn-reg-submit" id="btnRegSubmit">
                            <span>Hoàn Tất Đăng Ký & Đăng Nhập Ngay</span>
                            <i class="fa-solid fa-arrow-right" style="margin-left: 6px;"></i>
                        </button>
                    </div>
                </div>
            </form>

            <div style="text-align: center; margin-top: 18px; font-size: 13.5px; color: #64748b;">
                Đã có tài khoản từ trước? <a href="dangnhap.php" style="color: #e11d48; font-weight: 700; text-decoration: none;">Đăng nhập ngay</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const pwd = document.getElementById('regMatKhau');
    const pwdNL = document.getElementById('regMatKhauNL');
    const seg1 = document.getElementById('pwdSeg1');
    const seg2 = document.getElementById('pwdSeg2');
    const seg3 = document.getElementById('pwdSeg3');
    const strengthText = document.getElementById('pwdStrengthText');
    const matchText = document.getElementById('pwdMatchText');

    if (pwd) {
        pwd.addEventListener('input', function () {
            const val = this.value;
            seg1.style.background = '#e2e8f0';
            seg2.style.background = '#e2e8f0';
            seg3.style.background = '#e2e8f0';

            if (val.length === 0) {
                strengthText.textContent = 'Độ mạnh: Nhập mật khẩu';
                strengthText.style.color = '#64748b';
            } else if (val.length < 6) {
                seg1.style.background = '#ef4444';
                strengthText.textContent = 'Độ mạnh: Rất yếu (Ít nhất 6 ký tự)';
                strengthText.style.color = '#ef4444';
            } else if (val.length < 8 || !/[0-9]/.test(val)) {
                seg1.style.background = '#f59e0b';
                seg2.style.background = '#f59e0b';
                strengthText.textContent = 'Độ mạnh: Khá (Nên thêm số hoặc ký tự đặc biệt)';
                strengthText.style.color = '#f59e0b';
            } else {
                seg1.style.background = '#22c55e';
                seg2.style.background = '#22c55e';
                seg3.style.background = '#22c55e';
                strengthText.textContent = 'Độ mạnh: Tuyệt vời (An toàn cao)';
                strengthText.style.color = '#22c55e';
            }
            checkMatch();
        });
    }

    function checkMatch() {
        if (!pwdNL || pwdNL.value.length === 0) {
            matchText.textContent = '';
            return;
        }
        if (pwd.value === pwdNL.value) {
            matchText.textContent = '✔ Mật khẩu xác nhận khớp';
            matchText.style.color = '#22c55e';
        } else {
            matchText.textContent = '✖ Mật khẩu xác nhận chưa khớp';
            matchText.style.color = '#ef4444';
        }
    }

    if (pwdNL) {
        pwdNL.addEventListener('input', checkMatch);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>