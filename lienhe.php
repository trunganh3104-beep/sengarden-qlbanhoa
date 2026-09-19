<?php
require_once 'includes/header.php';
include_once 'DataProvider.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnGuiLienHe'])) {
    $hoTen = trim($_POST['txtHoTen'] ?? '');
    $dienThoai = trim($_POST['txtDienThoai'] ?? '');
    $email = trim($_POST['txtEmail'] ?? '');
    $tieuDe = trim($_POST['txtTieuDe'] ?? '');
    $noiDung = trim($_POST['txtNoiDung'] ?? '');

    if (empty($hoTen) || empty($noiDung)) {
        $error = 'Vui lòng điền Họ tên và Nội dung lời nhắn!';
    } else {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        mysqli_set_charset($conn, "utf8mb4");
        if (function_exists('ensureRequiredTables')) ensureRequiredTables($conn);

        $htEsc = mysqli_real_escape_string($conn, $hoTen);
        $dtEsc = mysqli_real_escape_string($conn, $dienThoai);
        $emEsc = mysqli_real_escape_string($conn, $email);
        $tdEsc = mysqli_real_escape_string($conn, $tieuDe);
        $ndEsc = mysqli_real_escape_string($conn, $noiDung);

        $sql = "INSERT INTO lienhe (HoTen, DienThoai, Email, TieuDe, NoiDung) 
                VALUES ('$htEsc', '$dtEsc', '$emEsc', '$tdEsc', '$ndEsc')";
        if (mysqli_query($conn, $sql)) {
            $success = 'Cảm ơn quý khách! Lời nhắn của bạn đã được gửi thành công. Đội ngũ Sen Garden sẽ liên hệ lại trong thời gian sớm nhất.';
        } else {
            $error = 'Có lỗi xảy ra khi gửi lời nhắn: ' . mysqli_error($conn);
        }
        mysqli_close($conn);
    }
}
?>

<div class="container" style="max-width: 1150px; padding: 30px 15px;">
    <div style="text-align: center; margin-bottom: 40px;" data-sg-reveal>
        <span style="display: inline-block; background: #e0f2fe; color: #0284c7; font-size: 13px; font-weight: 700; padding: 6px 16px; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">
            Kết Nối Với Chúng Tôi
        </span>
        <h1 style="font-size: 32px; font-weight: 800; color: #0f172a; margin-bottom: 12px;">
            Liên Hệ Shop Hoa Tươi Sen Garden
        </h1>
        <p style="font-size: 15px; color: #64748b; max-width: 650px; margin: 0 auto;">
            Quý khách có nhu cầu đặt hoa thiết kế riêng cho sự kiện, tiệc cưới, sinh nhật hoặc cần hỗ trợ đơn hàng, hãy liên hệ ngay với chúng tôi!
        </p>
    </div>

    <?php if ($success): ?>
        <div style="background: #f0fdf4; border: 1px solid #86efac; color: #16a34a; padding: 16px 20px; border-radius: 10px; margin-bottom: 30px; font-weight: 500; display: flex; align-items: center; gap: 10px;" data-sg-reveal>
            <i class="fa-solid fa-circle-check" style="font-size: 20px;"></i>
            <span><?php echo htmlspecialchars($success); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div style="background: #fef2f2; border: 1px solid #fca5a5; color: #dc2626; padding: 16px 20px; border-radius: 10px; margin-bottom: 30px; font-weight: 500; display: flex; align-items: center; gap: 10px;" data-sg-reveal>
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 20px;"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <div style="display: flex; gap: 35px; flex-wrap: wrap; margin-bottom: 50px;">
        <div style="flex: 1; min-width: 320px;" data-sg-reveal>
            <div style="background: #ffffff; border-radius: 16px; padding: 32px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); border: 1px solid #f1f5f9; height: 100%; box-sizing: border-box;">
                <h2 style="font-size: 20px; font-weight: 700; color: #1e293b; margin-bottom: 24px; padding-bottom: 12px; border-bottom: 2px solid #f1f5f9;">
                    Thông Tin Showroom
                </h2>

                <div style="display: flex; gap: 16px; margin-bottom: 22px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: #ffe4e6; color: #e11d48; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; color: #1e293b; font-size: 15px; margin-bottom: 4px;">Địa Chỉ Cửa Hàng</div>
                        <div style="color: #64748b; font-size: 14px; line-height: 1.5;">280 An Dương Vương, Phường 4, Quận 5, TP. Hồ Chí Minh</div>
                    </div>
                </div>

                <div style="display: flex; gap: 16px; margin-bottom: 22px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                        <i class="fa-solid fa-phone"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; color: #1e293b; font-size: 15px; margin-bottom: 4px;">Hotline Tư Vấn 24/7</div>
                        <div style="color: #64748b; font-size: 14px;"><a href="tel:0989366990" style="color: #0284c7; font-weight: 600; text-decoration: none;">0989 366 990</a> (Zalo / Call)</div>
                    </div>
                </div>

                <div style="display: flex; gap: 16px; margin-bottom: 22px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; color: #1e293b; font-size: 15px; margin-bottom: 4px;">Email Hỗ Trợ</div>
                        <div style="color: #64748b; font-size: 14px;">contact@sengarden.vn</div>
                    </div>
                </div>

                <div style="display: flex; gap: 16px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; color: #1e293b; font-size: 15px; margin-bottom: 4px;">Giờ Mở Cửa</div>
                        <div style="color: #64748b; font-size: 14px;">07:00 - 21:30 (Thứ 2 đến Chủ Nhật)</div>
                    </div>
                </div>
            </div>
        </div>

        <div style="flex: 1.3; min-width: 320px;" data-sg-reveal>
            <div style="background: #ffffff; border-radius: 16px; padding: 32px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); border: 1px solid #f1f5f9;">
                <h2 style="font-size: 20px; font-weight: 700; color: #1e293b; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #f1f5f9;">
                    Gửi Lời Nhắn Trực Tuyến
                </h2>

                <form method="post" action="lienhe.php">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 13.5px; color: #334155; margin-bottom: 6px;">
                                Họ và tên <span style="color: #e11d48;">*</span>
                            </label>
                            <input type="text" name="txtHoTen" required class="search-group" style="width: 100%; padding: 11px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;" placeholder="Nhập họ và tên của bạn...">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 13.5px; color: #334155; margin-bottom: 6px;">
                                Số điện thoại
                            </label>
                            <input type="text" name="txtDienThoai" class="search-group" style="width: 100%; padding: 11px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;" placeholder="Nhập số điện thoại...">
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-weight: 600; font-size: 13.5px; color: #334155; margin-bottom: 6px;">
                            Địa chỉ Email
                        </label>
                        <input type="email" name="txtEmail" class="search-group" style="width: 100%; padding: 11px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;" placeholder="Nhập email phản hồi...">
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-weight: 600; font-size: 13.5px; color: #334155; margin-bottom: 6px;">
                            Chủ đề cần hỗ trợ
                        </label>
                        <input type="text" name="txtTieuDe" class="search-group" style="width: 100%; padding: 11px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;" placeholder="Vd: Đặt hoa sự kiện, tư vấn hoa cưới...">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; font-size: 13.5px; color: #334155; margin-bottom: 6px;">
                            Nội dung lời nhắn <span style="color: #e11d48;">*</span>
                        </label>
                        <textarea name="txtNoiDung" required rows="4" style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box; resize: vertical;" placeholder="Nhập chi tiết yêu cầu của bạn..."></textarea>
                    </div>

                    <button type="submit" name="btnGuiLienHe" style="width: 100%; padding: 13px; font-size: 15px; font-weight: 600; color: #ffffff; background: linear-gradient(135deg, #e11d48 0%, #be123c 100%); border: none; cursor: pointer; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 14px rgba(225, 29, 72, 0.25); transition: all 0.2s ease;">
                        <i class="fa-solid fa-paper-plane"></i> Gửi Lời Nhắn
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div style="background: #ffffff; border-radius: 16px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); border: 1px solid #f1f5f9;" data-sg-reveal>
        <h2 style="font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 14px;">
            <i class="fa-solid fa-map-location-dot" style="color: #e11d48;"></i> Bản Đồ Đến Showroom
        </h2>
        <div style="border-radius: 12px; overflow: hidden; height: 320px; width: 100%;">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.6508933221535!2d106.67977467480468!3d10.761369989386345!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f1b88888889%3A0x123456789abcdef!2zMjgwIEFuIETGsMahbmcgVsawxqFuZywgUGjGsOG7nW5nIDQsIFF14bqtbiA1LCBUaMOgbmggcGjhu5EgSOG7kyBDaMOtIE1pbmg!5e0!3m2!1svi!2s!4v1700000000000!5m2!1svi!2s" 
                width="100%" 
                height="320" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
