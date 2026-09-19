<?php
require_once 'includes/header.php';

$vouchers = [];
$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn) {
    mysqli_set_charset($conn, "utf8mb4");
    if (function_exists('ensureRequiredTables')) ensureRequiredTables($conn);
    $sql = "SELECT * FROM voucher WHERE TrangThai = 1 AND (NgayHetHan >= CURDATE() OR NgayHetHan IS NULL) ORDER BY MaVoucher DESC";
    $res = mysqli_query($conn, $sql);
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            $vouchers[] = $r;
        }
    }
    mysqli_close($conn);
}
?>

<div class="container" style="max-width: 1140px; padding: 30px 15px;">
    <div style="margin-bottom: 25px;">
        <nav style="font-size: 13.5px; color: #64748b; display: flex; align-items: center; gap: 8px;">
            <a href="index.php" style="color: #64748b; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                <i class="fa-solid fa-house" style="font-size: 12px;"></i> Trang Chủ
            </a>
            <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #cbd5e1;"></i>
            <span style="color: #e11d48; font-weight: 600;">Mã Giảm Giá - Voucher Ưu Đãi</span>
        </nav>
    </div>

    <div style="text-align: center; margin-bottom: 45px;" data-sg-reveal>
        <span style="display: inline-block; background: #fdf2f8; color: #db2777; font-size: 13px; font-weight: 700; padding: 6px 16px; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; border: 1px solid #fbcfe8;">
            <i class="fa-solid fa-gift"></i> Kho Voucher Ưu Đãi
        </span>
        <h1 style="font-size: 30px; font-weight: 800; color: #0f172a; margin-bottom: 12px;">
            Mã Giảm Giá &amp; Khuyến Mãi Mua Hoa Tươi
        </h1>
        <p style="font-size: 15.5px; color: #64748b; max-width: 680px; margin: 0 auto; line-height: 1.6;">
            Thu thập ngay các mã ưu đãi độc quyền từ Sen Garden. Bấm <strong>"Sao chép mã"</strong> và áp dụng tại giỏ hàng để nhận chiết khấu trực tiếp khi đặt hoa!
        </p>
    </div>

    <?php if (empty($vouchers)): ?>
        <div style="background: #ffffff; border-radius: 16px; padding: 60px 20px; text-align: center; border: 1px solid #f1f5f9; box-shadow: 0 4px 20px rgba(0,0,0,0.03); max-width: 600px; margin: 0 auto 50px;" data-sg-reveal>
            <div style="width: 72px; height: 72px; border-radius: 50%; background: #f8fafc; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; font-size: 32px; margin-bottom: 18px;">
                <i class="fa-solid fa-ticket"></i>
            </div>
            <h3 style="font-size: 19px; font-weight: 700; color: #334155; margin-bottom: 8px;">Hiện Chưa Có Mã Giảm Giá Mới</h3>
            <p style="font-size: 14px; color: #64748b; margin-bottom: 22px;">Các chương trình ưu đãi mới đang được cập nhật. Quý khách vui lòng quay lại sau hoặc liên hệ tư vấn để nhận giá tốt nhất.</p>
            <a href="index.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 11px 24px; background: #e11d48; color: #ffffff; border-radius: 999px; text-decoration: none; font-weight: 600; font-size: 14px;">
                <i class="fa-solid fa-bag-shopping"></i> Khám phá hoa tươi
            </a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 24px; margin-bottom: 50px;" data-sg-reveal>
            <?php foreach ($vouchers as $v): ?>
                <?php
                    $isPercent = intval($v['PhanTramGiam']) > 0;
                    $discountText = $isPercent ? (intval($v['PhanTramGiam']) . '%') : (number_format($v['SoTienGiam']) . 'đ');
                    $minOrder = floatval($v['DonToiThieu']);
                    $minOrderText = ($minOrder > 0) ? ('Đơn tối thiểu ' . number_format($minOrder) . 'đ') : 'Cho mọi giá trị đơn hàng';
                    $expiryText = !empty($v['NgayHetHan']) ? date('d/m/Y', strtotime($v['NgayHetHan'])) : 'Vô thời hạn';
                ?>
                <div class="voucher-ticket-card" style="background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; display: flex; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.04); position: relative; transition: all 0.25s ease;">
                    <div style="width: 115px; background: linear-gradient(135deg, #e11d48 0%, #be123c 100%); color: #ffffff; padding: 20px 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; position: relative; flex-shrink: 0;">
                        <i class="fa-solid fa-ticket" style="font-size: 24px; margin-bottom: 8px; opacity: 0.9;"></i>
                        <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9;">GIẢM NGAY</div>
                        <div style="font-size: 21px; font-weight: 800; line-height: 1.2; margin: 4px 0;"><?php echo $discountText; ?></div>
                        <div style="position: absolute; top: -8px; right: -8px; width: 16px; height: 16px; border-radius: 50%; background: #f8fafc; border: 1px solid #e2e8f0;"></div>
                        <div style="position: absolute; bottom: -8px; right: -8px; width: 16px; height: 16px; border-radius: 50%; background: #f8fafc; border: 1px solid #e2e8f0;"></div>
                    </div>

                    <div style="flex: 1; padding: 18px 20px; display: flex; flex-direction: column; justify-content: space-between; border-left: 1px dashed #cbd5e1;">
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; gap: 8px;">
                                <span class="voucher-code-badge" style="display: inline-block; font-family: monospace; font-size: 14.5px; font-weight: 700; color: #1e293b; background: #f1f5f9; padding: 4px 10px; border-radius: 8px; border: 1px dashed #94a3b8; letter-spacing: 1px;">
                                    <?php echo htmlspecialchars($v['Code']); ?>
                                </span>
                                <span style="font-size: 11.5px; color: #16a34a; background: #dcfce7; padding: 3px 8px; border-radius: 999px; font-weight: 600;">
                                    <i class="fa-solid fa-circle-check" style="font-size: 10px;"></i> Khả dụng
                                </span>
                            </div>
                            <h3 style="font-size: 14.5px; font-weight: 600; color: #1e293b; line-height: 1.4; margin-bottom: 6px;">
                                <?php echo htmlspecialchars($v['MoTa']); ?>
                            </h3>
                            <div style="font-size: 12.5px; color: #64748b; margin-bottom: 4px;">
                                <i class="fa-solid fa-circle-info" style="color: #94a3b8;"></i> <?php echo $minOrderText; ?>
                            </div>
                            <div style="font-size: 12px; color: #94a3b8;">
                                <i class="fa-solid fa-calendar-day"></i> HSD: <?php echo $expiryText; ?>
                            </div>
                        </div>

                        <div style="display: flex; gap: 8px; margin-top: 14px; padding-top: 12px; border-top: 1px solid #f8fafc;">
                            <button type="button" class="btn-copy-voucher" data-code="<?php echo htmlspecialchars($v['Code']); ?>" style="flex: 1; padding: 8px 12px; font-size: 13px; font-weight: 600; color: #2563eb; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s ease;">
                                <i class="fa-solid fa-copy"></i> <span>Sao chép mã</span>
                            </button>
                            <a href="GioHang.php" style="padding: 8px 14px; font-size: 13px; font-weight: 600; color: #e11d48; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s ease;">
                                <span>Dùng ngay</span> <i class="fa-solid fa-arrow-right" style="font-size: 11px;"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div style="background: #ffffff; border-radius: 16px; padding: 35px 30px; border: 1px solid #f1f5f9; box-shadow: 0 4px 16px rgba(0,0,0,0.03); margin-bottom: 40px;" data-sg-reveal>
        <div style="text-align: center; margin-bottom: 28px;">
            <h2 style="font-size: 21px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">
                <i class="fa-solid fa-circle-question" style="color: #e11d48;"></i> Cách Áp Dụng Mã Giảm Giá Khi Đặt Hoa
            </h2>
            <p style="font-size: 14px; color: #64748b; margin: 0;">
                Rất đơn giản, quý khách chỉ cần thực hiện theo 3 bước sau:
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
            <div style="background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0;">
                <div style="font-size: 13px; font-weight: 700; color: #2563eb; margin-bottom: 6px;">BƯỚC 1</div>
                <div style="font-size: 15px; font-weight: 600; color: #1e293b; margin-bottom: 6px;">Sao Chép Mã Ưu Đãi</div>
                <div style="font-size: 13.5px; color: #64748b; line-height: 1.5;">
                    Tìm mã phù hợp với giá trị đơn hàng của bạn ở danh sách phía trên và bấm nút <strong>"Sao chép mã"</strong>.
                </div>
            </div>

            <div style="background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0;">
                <div style="font-size: 13px; font-weight: 700; color: #e11d48; margin-bottom: 6px;">BƯỚC 2</div>
                <div style="font-size: 15px; font-weight: 600; color: #1e293b; margin-bottom: 6px;">Chọn Hoa &amp; Vào Giỏ Hàng</div>
                <div style="font-size: 13.5px; color: #64748b; line-height: 1.5;">
                    Chọn những mẫu hoa tươi ưng ý thêm vào giỏ, sau đó bấm vào biểu tượng <strong>Giỏ hàng</strong> ở góc trên.
                </div>
            </div>

            <div style="background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0;">
                <div style="font-size: 13px; font-weight: 700; color: #16a34a; margin-bottom: 6px;">BƯỚC 3</div>
                <div style="font-size: 15px; font-weight: 600; color: #1e293b; margin-bottom: 6px;">Dán Mã &amp; Nhận Giảm Giá</div>
                <div style="font-size: 13.5px; color: #64748b; line-height: 1.5;">
                    Tại khung <strong>"Mã giảm giá"</strong> ở trang giỏ hàng, dán mã đã sao chép và bấm <strong>"Áp dụng"</strong> để được giảm tiền ngay!
                </div>
            </div>
        </div>
    </div>
</div>

<div id="voucherToast" style="position: fixed; bottom: 30px; right: 30px; background: #0f172a; color: #ffffff; padding: 12px 22px; border-radius: 10px; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.25); z-index: 999999; transform: translateY(100px); opacity: 0; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); pointer-events: none;">
    <i class="fa-solid fa-circle-check" style="color: #4ade80; font-size: 17px;"></i>
    <span id="voucherToastText">Đã sao chép mã giảm giá!</span>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const copyBtns = document.querySelectorAll('.btn-copy-voucher');
    const toast = document.getElementById('voucherToast');
    const toastText = document.getElementById('voucherToastText');
    let toastTimeout = null;

    copyBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const code = btn.getAttribute('data-code');
            if (!code) return;

            navigator.clipboard.writeText(code).then(function () {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> <span>Đã sao chép!</span>';
                btn.style.background = '#dcfce7';
                btn.style.color = '#15803d';
                btn.style.borderColor = '#86efac';

                if (toast && toastText) {
                    toastText.textContent = 'Đã sao chép mã: ' + code;
                    toast.style.transform = 'translateY(0)';
                    toast.style.opacity = '1';

                    if (toastTimeout) clearTimeout(toastTimeout);
                    toastTimeout = setTimeout(function () {
                        toast.style.transform = 'translateY(100px)';
                        toast.style.opacity = '0';
                    }, 2600);
                }

                setTimeout(function () {
                    btn.innerHTML = originalHtml;
                    btn.style.background = '#eff6ff';
                    btn.style.color = '#2563eb';
                    btn.style.borderColor = '#bfdbfe';
                }, 2200);
            }).catch(function () {
                prompt('Mã giảm giá của bạn:', code);
            });
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
