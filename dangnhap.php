<?php
if (!ob_get_level()) {
    ob_start();
}
require_once 'includes/auth.php';
include_once 'DataProvider.php';

if (isLoggedIn()) {
    header("Location: index.php");
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

$error = '';
$resetSuccess = '';
$resetError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnResetPassword'])) {
    $resetTenDN = trim($_POST['resetTenDN'] ?? '');
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    if (empty($resetTenDN) || empty($newPassword)) {
        $resetError = 'Vui lòng điền tên tài khoản và mật khẩu mới!';
    } elseif (strlen($newPassword) < 6) {
        $resetError = 'Mật khẩu mới phải có ít nhất 6 ký tự!';
    } elseif ($newPassword !== $confirmPassword) {
        $resetError = 'Mật khẩu xác nhận không khớp!';
    } else {
        $conn = getDbConnection();
        $stmtChk = $conn->prepare("SELECT MaKH FROM khachhang WHERE TenDN = ?");
        $stmtChk->bind_param("s", $resetTenDN);
        $stmtChk->execute();
        $chk = $stmtChk->get_result();
        if ($chk && $chk->num_rows > 0) {
            $row = $chk->fetch_assoc();
            $maKH = intval($row['MaKH']);
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmtUp = $conn->prepare("UPDATE khachhang SET MatKhau = ? WHERE MaKH = ?");
            $stmtUp->bind_param("si", $hash, $maKH);
            $stmtUp->execute();
            $stmtUp->close();
            $resetSuccess = 'Đặt lại mật khẩu thành công! Bạn có thể đăng nhập ngay với mật khẩu mới.';
        } else {
            $resetError = 'Không tìm thấy tài khoản trong hệ thống!';
        }
        $stmtChk->close();
        mysqli_close($conn);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnDangNhap'])) {
    $tenDN = trim($_POST['txtTenDN'] ?? '');
    $matKhau = trim($_POST['txtMatKhau'] ?? '');
    $remember = isset($_POST['chkRemember']);

    if (empty($tenDN) || empty($matKhau)) {
        $error = 'Vui lòng nhập Tên đăng nhập và Mật khẩu.';
    } else {
        $conn = getDbConnection();
        $stmtLogin = $conn->prepare("SELECT * FROM khachhang WHERE TenDN = ?");
        $stmtLogin->bind_param("s", $tenDN);
        $stmtLogin->execute();
        $rs = $stmtLogin->get_result();

        if ($rs && $rs->num_rows === 1) {
            $row = $rs->fetch_assoc();
            $matKhauDB = $row['MatKhau'];
            $isPasswordCorrect = false;

            if (password_verify($matKhau, $matKhauDB)) {
                $isPasswordCorrect = true;
            } elseif ($matKhau === $matKhauDB || md5($matKhau) === $matKhauDB) {
                $isPasswordCorrect = true;
                $newHash = password_hash($matKhau, PASSWORD_DEFAULT);
                $maKH = intval($row['MaKH']);
                $stmtRehash = $conn->prepare("UPDATE khachhang SET MatKhau = ? WHERE MaKH = ?");
                $stmtRehash->bind_param("si", $newHash, $maKH);
                $stmtRehash->execute();
                $stmtRehash->close();
            }

            if ($isPasswordCorrect) {
                if ($remember) {
                    setcookie('remember_tendn', $tenDN, time() + 3600 * 24 * 30, '/');
                } else {
                    setcookie('remember_tendn', '', time() - 3600, '/');
                }

                $_SESSION['user'] = array(
                    'MaKH' => $row['MaKH'],
                    'TenDN' => $row['TenDN'],
                    'HoTen' => $row['HoTen']
                );
                $_SESSION['role'] = $row['Role'];

                loadUserCartFromDb($row['MaKH']);
                syncUserCartToDb($row['MaKH'], $_SESSION['MyCart'] ?? []);

                $stmtLogin->close();
                mysqli_close($conn);

                if (intval($row['Role']) === 1) {
                    header("Location: admin/index.php");
                    echo "<script>window.location.href='admin/index.php';</script>";
                } else {
                    $targetUrl = !empty($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
                    header("Location: $targetUrl");
                    echo "<script>window.location.href='$targetUrl';</script>";
                }
                exit;
            } else {
                $error = 'Mật khẩu không chính xác!';
            }
        } else {
            $error = 'Tài khoản không tồn tại trong hệ thống!';
        }
        $stmtLogin->close();
        mysqli_close($conn);
    }
}

$savedTenDN = $_COOKIE['remember_tendn'] ?? '';

require_once 'includes/header.php';
?>

<style>

.auth-page-wrapper {
    min-height: calc(85vh - 80px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px 15px;
}
.auth-split-card {
    display: flex;
    width: 100%;
    max-width: 860px;
    background: #ffffff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
    border: 1px solid #f1f5f9;
}

.auth-banner-side {
    flex: 1;
    background: linear-gradient(145deg, #e11d48 0%, #be123c 60%, #881337 100%);
    color: #fff;
    padding: 45px 35px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
}
.auth-banner-side::after {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 200px;
    height: 200px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 50%;
}
.auth-banner-title {
    font-size: 26px;
    font-weight: 800;
    margin-bottom: 12px;
    line-height: 1.3;
}
.auth-banner-desc {
    font-size: 14px;
    line-height: 1.6;
    color: #fecdd3;
    margin-bottom: 30px;
}
.auth-perks-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.auth-perk-item {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13.5px;
    color: #fff;
}
.auth-perk-item i {
    width: 28px;
    height: 28px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

.auth-form-side {
    flex: 1.1;
    padding: 45px 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.auth-header-title {
    font-size: 24px;
    font-weight: 800;
    color: #1e293b;
    margin: 0 0 6px;
}
.auth-header-subtitle {
    font-size: 14px;
    color: #64748b;
    margin-bottom: 24px;
}

.auth-input-group {
    position: relative;
    margin-bottom: 18px;
}
.auth-input-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 6px;
}
.auth-input-box {
    position: relative;
    display: flex;
    align-items: center;
}
.auth-input-box i.input-icon {
    position: absolute;
    left: 14px;
    color: #94a3b8;
    font-size: 15px;
}
.auth-input-box input {
    width: 100%;
    padding: 12px 14px 12px 42px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    outline: none;
    transition: all 0.2s ease;
    background: #f8fafc;
}
.auth-input-box input:focus {
    background: #fff;
    border-color: #e11d48;
    box-shadow: 0 0 0 4px rgba(225, 29, 72, 0.1);
}
.toggle-pwd-btn {
    position: absolute;
    right: 14px;
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    font-size: 15px;
    padding: 0;
}
.toggle-pwd-btn:hover {
    color: #334155;
}

.auth-options-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
    margin-bottom: 22px;
}
.auth-remember-label {
    display: flex;
    align-items: center;
    gap: 7px;
    color: #475569;
    cursor: pointer;
    user-select: none;
}
.auth-forgot-link {
    color: #e11d48;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.2s;
}
.auth-forgot-link:hover {
    text-decoration: underline;
}

.btn-auth-submit {
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
}
.btn-auth-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(225, 29, 72, 0.45);
}
.btn-auth-submit::after {
    content: '';
    position: absolute;
    top: 0;
    left: -120%;
    width: 60%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.35), transparent);
    transform: skewX(-20deg);
}
.btn-auth-submit:hover::after {
    animation: sgShimmer 0.8s ease-in-out;
}

.modal-forgot-pwd {
    display: none;
    position: fixed;
    z-index: 999999;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.55);
    backdrop-filter: blur(4px);
    align-items: center;
    justify-content: center;
}
.modal-forgot-card {
    background: #fff;
    width: 90%;
    max-width: 440px;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    position: relative;
    animation: sgScaleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.modal-forgot-close {
    position: absolute;
    top: 15px;
    right: 18px;
    background: none;
    border: none;
    font-size: 22px;
    color: #94a3b8;
    cursor: pointer;
}
.modal-forgot-close:hover {
    color: #e11d48;
}

@media (max-width: 768px) {
    .auth-split-card {
        flex-direction: column;
    }
    .auth-banner-side {
        padding: 30px 25px;
    }
    .auth-form-side {
        padding: 30px 20px;
    }
}
</style>

<div class="auth-page-wrapper">
    <div class="auth-split-card" data-sg-reveal>
        
        <div class="auth-banner-side">
            <div>
                <div style="font-size: 34px; margin-bottom: 12px;">🌸</div>
                <h2 class="auth-banner-title">Sen Garden Floral</h2>
                <p class="auth-banner-desc">
                    Nơi gửi gắm những thông điệp yêu thương qua từng đóa hoa tươi thắm, rạng ngời và ngát hương.
                </p>
                <ul class="auth-perks-list">
                    <li class="auth-perk-item">
                        <i class="fa-solid fa-gift"></i>
                        <span>Ưu đãi thành viên & quà tặng đặc quyền</span>
                    </li>
                    <li class="auth-perk-item">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span>Theo dõi lịch sử đơn hoa chính xác</span>
                    </li>
                    <li class="auth-perk-item">
                        <i class="fa-solid fa-heart"></i>
                        <span>Lưu giữ bộ sưu tập hoa yêu thích 24/7</span>
                    </li>
                </ul>
            </div>
            <div style="font-size: 12px; color: #fecdd3; margin-top: 30px;">
                © 2026 Shop Hoa Tươi Sen Garden. Bản quyền thuộc về bạn.
            </div>
        </div>

        
        <div class="auth-form-side">
            <h1 class="auth-header-title">Đăng Nhập</h1>
            <p class="auth-header-subtitle">Vui lòng điền thông tin để đăng nhập tài khoản</p>

            <?php if (!empty($error)): ?>
                <div style="background: #fef2f2; color: #991b1b; padding: 12px 16px; border-radius: 8px; font-size: 13.5px; border-left: 4px solid #ef4444; margin-bottom: 20px;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($resetSuccess)): ?>
                <div style="background: #f0fdf4; color: #16a34a; padding: 12px 16px; border-radius: 8px; font-size: 13.5px; border-left: 4px solid #22c55e; margin-bottom: 20px;">
                    <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($resetSuccess); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($resetError)): ?>
                <div style="background: #fef2f2; color: #991b1b; padding: 12px 16px; border-radius: 8px; font-size: 13.5px; border-left: 4px solid #ef4444; margin-bottom: 20px;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($resetError); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="dangnhap.php" id="loginForm">
                <div class="auth-input-group">
                    <label>Tên đăng nhập</label>
                    <div class="auth-input-box">
                        <i class="fa-solid fa-user input-icon"></i>
                        <input type="text" name="txtTenDN" id="inputTenDN" value="<?php echo htmlspecialchars($_POST['txtTenDN'] ?? $savedTenDN); ?>" placeholder="Nhập tên đăng nhập..." required autofocus>
                    </div>
                </div>

                <div class="auth-input-group">
                    <label>Mật khẩu</label>
                    <div class="auth-input-box">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input type="password" name="txtMatKhau" id="inputMatKhau" placeholder="Nhập mật khẩu..." required>
                        <button type="button" class="toggle-pwd-btn" id="togglePwd" title="Ẩn / Hiện mật khẩu">
                            <i class="fa-regular fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="auth-options-row">
                    <label class="auth-remember-label">
                        <input type="checkbox" name="chkRemember" <?php echo !empty($savedTenDN) ? 'checked' : ''; ?>>
                        <span>Ghi nhớ đăng nhập</span>
                    </label>
                    <a href="javascript:void(0)" class="auth-forgot-link" id="btnOpenForgot">Quên mật khẩu?</a>
                </div>

                <button type="submit" name="btnDangNhap" class="btn-auth-submit" id="btnLoginSubmit">
                    <span>Đăng Nhập Ngay</span>
                    <i class="fa-solid fa-arrow-right-to-bracket" style="margin-left: 6px;"></i>
                </button>
            </form>

            <div style="text-align: center; margin-top: 22px; font-size: 13.5px; color: #64748b;">
                Chưa có tài khoản? <a href="dangky.php" style="color: #e11d48; font-weight: 700; text-decoration: none;">Đăng ký miễn phí</a>
            </div>
        </div>
    </div>
</div>

<div class="modal-forgot-pwd" id="modalForgot">
    <div class="modal-forgot-card">
        <button type="button" class="modal-forgot-close" id="btnCloseForgot">&times;</button>
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="width: 50px; height: 50px; background: #ffe4e6; color: #e11d48; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 10px;">
                <i class="fa-solid fa-key"></i>
            </div>
            <h3 style="margin: 0; color: #1e293b; font-size: 19px;">Đặt Lại Mật Khẩu</h3>
            <p style="font-size: 13px; color: #64748b; margin: 4px 0 0;">Nhập tên tài khoản và mật khẩu mới bên dưới</p>
        </div>

        <form method="post" action="dangnhap.php">
            <div class="auth-input-group">
                <label>Tên đăng nhập cần khôi phục</label>
                <div class="auth-input-box">
                    <i class="fa-solid fa-user input-icon"></i>
                    <input type="text" name="resetTenDN" placeholder="Nhập tên đăng nhập..." required>
                </div>
            </div>

            <div class="auth-input-group">
                <label>Mật khẩu mới</label>
                <div class="auth-input-box">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" name="newPassword" placeholder="Tối thiểu 6 ký tự..." required>
                </div>
            </div>

            <div class="auth-input-group">
                <label>Xác nhận mật khẩu mới</label>
                <div class="auth-input-box">
                    <i class="fa-solid fa-shield-check input-icon"></i>
                    <input type="password" name="confirmPassword" placeholder="Nhập lại mật khẩu mới..." required>
                </div>
            </div>

            <button type="submit" name="btnResetPassword" class="btn-auth-submit" style="margin-top: 10px;">
                Xác Nhận Đổi Mật Khẩu
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const pwdInput = document.getElementById('inputMatKhau');
    const toggleBtn = document.getElementById('togglePwd');
    const toggleIcon = document.getElementById('toggleIcon');
    const modalForgot = document.getElementById('modalForgot');
    const btnOpenForgot = document.getElementById('btnOpenForgot');
    const btnCloseForgot = document.getElementById('btnCloseForgot');

    if (toggleBtn && pwdInput) {
        toggleBtn.addEventListener('click', function () {
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                toggleIcon.className = 'fa-regular fa-eye-slash';
            } else {
                pwdInput.type = 'password';
                toggleIcon.className = 'fa-regular fa-eye';
            }
        });
    }

    if (btnOpenForgot && modalForgot && btnCloseForgot) {
        btnOpenForgot.addEventListener('click', () => modalForgot.style.display = 'flex');
        btnCloseForgot.addEventListener('click', () => modalForgot.style.display = 'none');
        window.addEventListener('click', (e) => {
            if (e.target === modalForgot) modalForgot.style.display = 'none';
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>