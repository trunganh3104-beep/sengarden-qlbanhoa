<?php
require_once 'includes/header.php';
include_once("DataProvider.php");

if (isLoggedIn()) {
    $currentMaKH = intval($_SESSION['user']['MaKH']);
    if (empty($_SESSION['MyCart'])) {
        loadUserCartFromDb($currentMaKH);
    } else {
        syncUserCartToDb($currentMaKH, $_SESSION['MyCart']);
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'save_cart') {
    if (isLoggedIn()) {
        syncUserCartToDb($_SESSION['user']['MaKH'], $_SESSION['MyCart'] ?? []);
    }
    header("Location: GioHang.php");
    exit;
}

if (isset($_SESSION['MyCart']) && is_array($_SESSION['MyCart'])) {
    foreach ($_SESSION['MyCart'] as $k => $v) {
        if (intval($k) <= 0 || intval($v) <= 0) {
            unset($_SESSION['MyCart'][$k]);
        }
    }
}
?>

<style>

.cart-page-wrapper {
    max-width: 1200px;
    margin: 10px auto 40px;
}

.cart-steps {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 30px;
    margin-bottom: 30px;
    padding: 15px 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
    border: 1px solid #f1f5f9;
}
.cart-step-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    font-weight: 500;
    color: #94a3b8;
}
.cart-step-item.active {
    color: #e11d48;
    font-weight: 700;
}
.cart-step-num {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #f1f5f9;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
}
.cart-step-item.active .cart-step-num {
    background: #e11d48;
    color: #fff;
    box-shadow: 0 4px 10px rgba(225, 29, 72, 0.3);
}
.cart-step-divider {
    width: 40px;
    height: 2px;
    background: #e2e8f0;
}

.free-shipping-card {
    background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
    border: 1px solid #fecdd3;
    border-radius: 12px;
    padding: 16px 22px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 18px;
    box-shadow: 0 4px 12px rgba(225, 29, 72, 0.05);
}
.fs-icon-wrap {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #fff;
    color: #e11d48;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(225, 29, 72, 0.15);
}
.fs-content {
    flex: 1;
}
.fs-text {
    font-size: 14px;
    color: #1e293b;
    margin-bottom: 8px;
}
.fs-text strong {
    color: #e11d48;
}
.fs-progress-bg {
    width: 100%;
    height: 8px;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 10px;
    overflow: hidden;
}
.fs-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #e11d48, #f43f5e);
    border-radius: 10px;
    transition: width 0.4s ease;
}

.cart-layout {
    display: grid;
    grid-template-columns: 1fr 370px;
    gap: 25px;
    align-items: start;
}

.cart-items-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid #f1f5f9;
    overflow: hidden;
}
.cart-items-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #f1f5f9;
}
.cart-items-header h2 {
    font-size: 19px;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.cart-badge-count {
    font-size: 13px;
    background: #ffe4e6;
    color: #e11d48;
    padding: 3px 10px;
    border-radius: 20px;
    font-weight: 600;
}

.cart-table {
    width: 100%;
    border-collapse: collapse;
}
.cart-table th {
    background: #f8fafc;
    color: #64748b;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 14px 20px;
    text-align: left;
    border-bottom: 1px solid #edf2f7;
}
.cart-table td {
    padding: 20px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.cart-table tr:last-child td {
    border-bottom: none;
}
.cart-table tr {
    transition: background-color 0.2s ease;
}
.cart-table tr:hover {
    background-color: #fcfdfe;
}

.cart-product-cell {
    display: flex;
    align-items: center;
    gap: 16px;
}
.cart-product-img {
    width: 80px;
    height: 80px;
    border-radius: 10px;
    object-fit: cover;
    border: 1px solid #f1f5f9;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: transform 0.3s ease;
    flex-shrink: 0;
}
.cart-product-cell:hover .cart-product-img {
    transform: scale(1.05);
}
.cart-product-title {
    font-weight: 600;
    font-size: 15.5px;
    color: #1e293b;
    margin-bottom: 4px;
    line-height: 1.4;
}
.cart-product-title a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s ease;
}
.cart-product-title a:hover {
    color: #e11d48;
}
.cart-product-cat {
    font-size: 12.5px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 6px;
}
.cart-product-stock {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11.5px;
    color: #16a34a;
    font-weight: 500;
    margin-top: 4px;
}

.qty-pill-control {
    display: inline-flex;
    align-items: center;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 30px;
    padding: 3px 5px;
    transition: all 0.2s ease;
}
.qty-pill-control:hover,
.qty-pill-control:focus-within {
    border-color: #e11d48;
    background: #fff;
    box-shadow: 0 2px 8px rgba(225, 29, 72, 0.15);
}
.qty-btn {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: transparent;
    border: none;
    color: #475569;
    font-size: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s ease;
    font-weight: bold;
}
.qty-btn:hover {
    background: #ffe4e6;
    color: #e11d48;
    transform: scale(1.1);
}
.qty-input {
    width: 44px;
    text-align: center;
    border: none;
    background: transparent;
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    outline: none;
    -moz-appearance: textfield;
}
.qty-input::-webkit-outer-spin-button,
.qty-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.btn-delete-item {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #fef2f2;
    color: #ef4444;
    border: 1px solid #fee2e2;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 14px;
}
.btn-delete-item:hover {
    background: #ef4444;
    color: #fff;
    border-color: #ef4444;
    transform: scale(1.1) rotate(6deg);
    box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
}

.cart-items-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 24px;
    background: #fcfcfd;
    border-top: 1px solid #f1f5f9;
}
.btn-continue-shopping {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #475569;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s ease;
}
.btn-continue-shopping:hover {
    color: #e11d48;
    transform: translateX(-3px);
}

.cart-summary-card {
    background: #fff;
    border-radius: 14px;
    padding: 26px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.05);
    border: 1px solid #f1f5f9;
    position: sticky;
    top: 90px;
}
.summary-title {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 20px;
    padding-bottom: 14px;
    border-bottom: 1.5px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 8px;
}
.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 14.5px;
    color: #64748b;
    margin-bottom: 14px;
}
.summary-row.total-row {
    font-size: 16px;
    color: #0f172a;
    font-weight: 700;
    padding-top: 14px;
    margin-top: 14px;
    border-top: 2px dashed #e2e8f0;
    margin-bottom: 22px;
}
.summary-total-price {
    font-size: 24px;
    color: #e11d48;
    font-weight: 800;
    letter-spacing: -0.5px;
}

.coupon-box {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
}
.coupon-input {
    flex: 1;
    padding: 9px 14px;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    font-size: 13.5px;
    outline: none;
    transition: border-color 0.2s;
    text-transform: uppercase;
}
.coupon-input:focus {
    border-color: #e11d48;
}
.coupon-btn {
    background: #f1f5f9;
    border: 1.5px solid #e2e8f0;
    color: #334155;
    padding: 0 16px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
}
.coupon-btn:hover {
    background: #e2e8f0;
}

.btn-checkout-main {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 15px 20px;
    background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
    color: #fff !important;
    text-decoration: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 700;
    box-shadow: 0 8px 24px rgba(225, 29, 72, 0.35);
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    border: none;
    cursor: pointer;
}
.btn-checkout-main:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(225, 29, 72, 0.5);
}
.btn-checkout-main::after {
    content: '';
    position: absolute;
    top: 0;
    left: -120%;
    width: 60%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.35), transparent);
    transform: skewX(-20deg);
}
.btn-checkout-main:hover::after {
    animation: sgShimmer 0.8s ease-in-out;
}

.cart-trust-badges {
    margin-top: 22px;
    padding-top: 18px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.trust-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: #64748b;
}
.trust-item i {
    color: #22c55e;
    font-size: 15px;
}

.cart-empty-box {
    text-align: center;
    padding: 70px 20px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid #f1f5f9;
    max-width: 600px;
    margin: 30px auto;
}
.empty-icon-circle {
    width: 90px;
    height: 90px;
    background: #fff1f2;
    color: #e11d48;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 38px;
    margin-bottom: 20px;
    box-shadow: 0 8px 24px rgba(225, 29, 72, 0.12);
}

@media (max-width: 900px) {
    .cart-layout {
        grid-template-columns: 1fr;
    }
    .cart-summary-card {
        position: static;
    }
    .cart-steps {
        display: none;
    }
}
</style>

<div id="cart-container" class="cart-page-wrapper">
<?php
if (!isset($_SESSION['MyCart']) || empty($_SESSION['MyCart'])) {
?>
    <div class="cart-empty-box" data-sg-reveal>
        <div class="empty-icon-circle">
            <i class="fa-solid fa-basket-shopping"></i>
        </div>
        <h2 style="font-size: 22px; color: #1e293b; margin-bottom: 10px;">Giỏ hàng của bạn đang trống!</h2>
        <p style="color: #64748b; font-size: 15px; margin-bottom: 25px; line-height: 1.6;">
            Bạn chưa chọn mẫu hoa nào. Hãy khám phá bộ sưu tập hoa tươi tuyệt đẹp của Sen Garden nhé!
        </p>
        <a href="index.php" class="btn-checkout-main" style="display: inline-flex; width: auto; padding: 12px 30px;">
            <i class="fa-solid fa-store"></i> Khám phá hoa tươi ngay
        </a>
    </div>
<?php
} else {
    
    $itemsList = [];
    $tongcong = 0;
    $totalQty = 0;

    foreach ($_SESSION['MyCart'] as $MaHoa => $SoLuong) {
        $MaHoa = intval($MaHoa);
        $SoLuong = intval($SoLuong);
        if ($MaHoa <= 0 || $SoLuong <= 0) continue;

        $rs = DataProvider::ExecuteQuery("SELECT h.*, l.TenLoai FROM hoa h LEFT JOIN loaihoa l ON h.MaLoai = l.MaLoai WHERE h.MaHoa = $MaHoa");
        if ($rs && mysqli_num_rows($rs) > 0) {
            $hoa = mysqli_fetch_array($rs);
            $tt = $hoa["GiaBan"] * $SoLuong;
            $tongcong += $tt;
            $totalQty += $SoLuong;

            $itemsList[] = [
                'MaHoa' => $MaHoa,
                'TenHoa' => $hoa['TenHoa'],
                'Hinh' => $hoa['Hinh'],
                'TenLoai' => $hoa['TenLoai'] ?? 'Hoa tươi',
                'GiaBan' => $hoa['GiaBan'],
                'SoLuong' => $SoLuong,
                'ThanhTien' => $tt
            ];
        } else {
            unset($_SESSION['MyCart'][$MaHoa]);
        }
    }

    
    $freeShippingGoal = 500000;
    $fsPercent = min(100, round(($tongcong / $freeShippingGoal) * 100));
    $fsRemain = max(0, $freeShippingGoal - $tongcong);

    $voucherMsg = '';
    $voucherErr = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnApplyVoucher'])) {
        $code = strtoupper(trim($_POST['txtVoucherCode'] ?? ''));
        if (empty($code)) {
            $voucherErr = 'Vui lòng nhập mã giảm giá!';
        } else {
            $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            mysqli_set_charset($conn, "utf8mb4");
            $codeEsc = mysqli_real_escape_string($conn, $code);
            $resV = mysqli_query($conn, "SELECT * FROM voucher WHERE Code = '$codeEsc' AND TrangThai = 1");
            if ($resV && mysqli_num_rows($resV) > 0) {
                $vRow = mysqli_fetch_assoc($resV);
                if (!empty($vRow['NgayHetHan']) && strtotime($vRow['NgayHetHan']) < strtotime(date('Y-m-d'))) {
                    $voucherErr = 'Mã giảm giá này đã hết hạn sử dụng!';
                } elseif ($tongcong < floatval($vRow['DonToiThieu'])) {
                    $voucherErr = 'Mã này chỉ áp dụng cho đơn hàng từ ' . number_format($vRow['DonToiThieu']) . ' đ trở lên!';
                } else {
                    $_SESSION['voucher'] = $vRow;
                    $voucherMsg = 'Áp dụng mã ' . htmlspecialchars($code) . ' thành công!';
                }
            } else {
                $voucherErr = 'Mã giảm giá không hợp lệ hoặc không tồn tại!';
            }
            mysqli_close($conn);
        }
    }

    if (isset($_GET['action']) && $_GET['action'] === 'remove_voucher') {
        unset($_SESSION['voucher']);
        echo "<script>window.location.href='GioHang.php';</script>";
        exit;
    }

    $discountAmount = 0;
    if (isset($_SESSION['voucher'])) {
        $v = $_SESSION['voucher'];
        if ($tongcong >= floatval($v['DonToiThieu'])) {
            if ($v['PhanTramGiam'] > 0) {
                $discountAmount = ($tongcong * $v['PhanTramGiam']) / 100;
            } elseif ($v['SoTienGiam'] > 0) {
                $discountAmount = floatval($v['SoTienGiam']);
            }
            if ($discountAmount > $tongcong) {
                $discountAmount = $tongcong;
            }
        } else {
            unset($_SESSION['voucher']);
        }
    }
    $phiGiao = ($fsRemain == 0) ? 0 : 30000;
    $finalTotal = max(0, $tongcong + $phiGiao - $discountAmount);
?>
    
    <div class="cart-steps" data-sg-reveal>
        <div class="cart-step-item active">
            <div class="cart-step-num"><i class="fa-solid fa-check"></i></div>
            <span>1. Xem Giỏ Hàng</span>
        </div>
        <div class="cart-step-divider"></div>
        <div class="cart-step-item">
            <div class="cart-step-num">2</div>
            <span>2. Thông Tin Giao & Lời Nhắn</span>
        </div>
        <div class="cart-step-divider"></div>
        <div class="cart-step-item">
            <div class="cart-step-num">3</div>
            <span>3. Thanh Toán VietQR</span>
        </div>
    </div>

    
    <div class="free-shipping-card" data-sg-reveal>
        <div class="fs-icon-wrap">
            <i class="fa-solid fa-truck-fast"></i>
        </div>
        <div class="fs-content">
            <div class="fs-text">
                <?php if ($fsRemain > 0): ?>
                    Mua thêm <strong><?php echo number_format($fsRemain); ?> đ</strong> để được <strong>MIỄN PHÍ GIAO HOA</strong> toàn thành phố!
                <?php else: ?>
                    🎉 <strong>Tuyệt vời!</strong> Đơn hàng của bạn đã đủ điều kiện <strong>MIỄN PHÍ VẬN CHUYỂN</strong>!
                <?php endif; ?>
            </div>
            <div class="fs-progress-bg">
                <div class="fs-progress-bar" style="width: <?php echo $fsPercent; ?>%;"></div>
            </div>
        </div>
    </div>


    <div class="cart-layout">
        
        <div class="cart-items-card" data-sg-reveal>
            <div class="cart-items-header">
                <h2>
                    <i class="fa-solid fa-cart-shopping" style="color: #e11d48;"></i> Giỏ Hàng Của Bạn
                </h2>
                <span class="cart-badge-count"><?php echo $totalQty; ?> sản phẩm</span>
            </div>

            <table class="cart-table">
                <thead>
                    <tr>
                        <th style="width: 48%;">Sản phẩm hoa</th>
                        <th style="width: 17%;">Đơn giá</th>
                        <th style="width: 18%; text-align: center;">Số lượng</th>
                        <th style="width: 17%; text-align: right;">Thành tiền</th>
                        <th style="width: 8%; text-align: center;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itemsList as $item): 
                        $giamSL = max(1, $item['SoLuong'] - 1);
                        $tangSL = $item['SoLuong'] + 1;
                    ?>
                    <tr>
                        <td>
                            <div class="cart-product-cell">
                                <a href="chitiet.php?MaHoa=<?php echo $item['MaHoa']; ?>">
                                    <img class="cart-product-img" src="hoa/<?php echo htmlspecialchars($item['Hinh']); ?>" alt="<?php echo htmlspecialchars($item['TenHoa']); ?>" onerror="this.src='images/no-image.png';">
                                </a>
                                <div>
                                    <div class="cart-product-title">
                                        <a href="chitiet.php?MaHoa=<?php echo $item['MaHoa']; ?>">
                                            <?php echo htmlspecialchars($item['TenHoa']); ?>
                                        </a>
                                    </div>
                                    <div class="cart-product-cat">
                                        <i class="fa-solid fa-tag" style="color: #3b82f6;"></i> <?php echo htmlspecialchars($item['TenLoai']); ?>
                                    </div>
                                    <div class="cart-product-stock">
                                        <i class="fa-solid fa-circle-check"></i> Hoa tươi có sẵn
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span style="font-weight: 600; color: #475569;"><?php echo number_format($item['GiaBan']); ?> đ</span>
                        </td>
                        <td style="text-align: center;">
                            <div class="qty-pill-control">
                                <a href="MyCart.php?action=update&id=<?php echo $item['MaHoa']; ?>&qty=<?php echo $giamSL; ?>" 
                                   class="qty-btn btn-qty" title="Giảm số lượng">-</a>

                                <input type="number" min="1" max="99" value="<?php echo $item['SoLuong']; ?>" 
                                       data-id="<?php echo $item['MaHoa']; ?>" class="qty-input cart-input-qty">

                                <a href="MyCart.php?action=update&id=<?php echo $item['MaHoa']; ?>&qty=<?php echo $tangSL; ?>" 
                                   class="qty-btn btn-qty" title="Tăng số lượng">+</a>
                            </div>
                        </td>
                        <td style="text-align: right;">
                            <strong style="color: #e11d48; font-size: 16px;"><?php echo number_format($item['ThanhTien']); ?> đ</strong>
                        </td>
                        <td style="text-align: center;">
                            <a href="MyCart.php?action=delete&id=<?php echo $item['MaHoa']; ?>" 
                               class="btn-delete-item" title="Xóa đóa hoa này">
                                <i class="fa-regular fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-items-footer">
                <a href="index.php" class="btn-continue-shopping">
                    <i class="fa-solid fa-arrow-left-long"></i> Tiếp tục chọn hoa khác
                </a>
                <span style="font-size: 13px; color: #94a3b8;">
                    <i class="fa-solid fa-shield-halved"></i> Đảm bảo hoa tươi khi giao tận nơi
                </span>
            </div>
        </div>

        
        <div class="cart-summary-card" data-sg-reveal>
            <h3 class="summary-title">
                <i class="fa-solid fa-receipt" style="color: #e11d48;"></i> Tóm Tắt Đơn Hàng
            </h3>

            <div class="summary-row">
                <span>Số lượng:</span>
                <strong style="color: #1e293b;"><?php echo $totalQty; ?> sản phẩm</strong>
            </div>

            <div class="summary-row">
                <span>Tạm tính tiền hoa:</span>
                <strong style="color: #1e293b;"><?php echo number_format($tongcong); ?> đ</strong>
            </div>

            <div class="summary-row">
                <span>Phí giao hàng:</span>
                <?php if ($fsRemain == 0): ?>
                    <span style="color: #16a34a; font-weight: 600;">Miễn phí (0 đ)</span>
                <?php else: ?>
                    <span style="color: #64748b;">30,000 đ</span>
                <?php endif; ?>
            </div>

            
            <div style="margin: 18px 0 10px;">
                <div style="font-size: 12.5px; color: #64748b; margin-bottom: 6px;">Mã ưu đãi / Voucher:</div>
                <?php if (isset($_SESSION['voucher'])): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 10px 12px; margin-bottom: 6px;">
                        <div>
                            <span style="font-weight: 700; color: #16a34a; font-size: 13.5px;"><i class="fa-solid fa-tag"></i> <?php echo htmlspecialchars($_SESSION['voucher']['Code']); ?></span>
                            <div style="font-size: 11.5px; color: #15803d;"><?php echo htmlspecialchars($_SESSION['voucher']['MoTa']); ?></div>
                        </div>
                        <a href="GioHang.php?action=remove_voucher" style="color: #ef4444; font-size: 12px; text-decoration: none; font-weight: 600; padding: 4px 8px; background: #fee2e2; border-radius: 6px;" title="Gỡ mã">Xóa mã</a>
                    </div>
                <?php else: ?>
                    <form method="post" action="GioHang.php" class="coupon-box" style="display: flex; gap: 8px;">
                        <input type="text" name="txtVoucherCode" class="coupon-input" placeholder="Nhập mã (Vd: SENGARDEN, HOATUOI2026)" required style="text-transform: uppercase;">
                        <button type="submit" name="btnApplyVoucher" class="coupon-btn">Áp dụng</button>
                    </form>
                <?php endif; ?>
                <?php if ($voucherMsg): ?>
                    <div style="font-size: 12px; color: #16a34a; margin-top: 5px;"><i class="fa-solid fa-circle-check"></i> <?php echo $voucherMsg; ?></div>
                <?php endif; ?>
                <?php if ($voucherErr): ?>
                    <div style="font-size: 12px; color: #ef4444; margin-top: 5px;"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $voucherErr; ?></div>
                <?php endif; ?>
            </div>

            <?php if ($discountAmount > 0): ?>
            <div class="summary-row" style="color: #16a34a;">
                <span>Giảm giá (Voucher <?php echo htmlspecialchars($_SESSION['voucher']['Code']); ?>):</span>
                <strong>-<?php echo number_format($discountAmount); ?> đ</strong>
            </div>
            <?php endif; ?>

            <div class="summary-row total-row">
                <span>Tổng thanh toán:</span>
                <span class="summary-total-price">
                    <?php echo number_format($finalTotal); ?> đ
                </span>
            </div>

            <a href="checkout.php" class="btn-checkout-main">
                <span>Tiến Hành Đặt Hàng & Thanh Toán</span>
                <i class="fa-solid fa-arrow-right-long"></i>
            </a>

            <div class="cart-trust-badges">
                <div class="trust-item">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Thanh toán quét mã <strong>VietQR Techcombank</strong> hoặc COD</span>
                </div>
                <div class="trust-item">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Tặng kèm <strong>thiệp chúc mừng</strong> viết tay miễn phí</span>
                </div>
                <div class="trust-item">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Cam kết đổi trả nếu hoa dập nát khi giao</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('cart-container');
    if (!container) return;

    function updateCartAjax(url) {
        container.style.opacity = '0.5';
        container.style.pointerEvents = 'none';

        fetch(url)
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.getElementById('cart-container');
                if (newContent) {
                    container.innerHTML = newContent.innerHTML;
                    if (window.IntersectionObserver) {
                        container.querySelectorAll('[data-sg-reveal]').forEach(el => el.classList.add('sg-revealed'));
                    }
                }

                const currentCartLink = document.querySelector('a[href*="GioHang"], a[href*="giohang"]');
                const newCartLink = doc.querySelector('a[href*="GioHang"], a[href*="giohang"]');
                if (currentCartLink && newCartLink) {
                    currentCartLink.innerHTML = newCartLink.innerHTML;
                }

                if (window.triggerCartJiggle) {
                    window.triggerCartJiggle();
                }
            })
            .catch(err => console.error(err))
            .finally(() => {
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
            });
    }

    container.addEventListener('click', function (e) {
        const delBtn = e.target.closest('.btn-delete-item');
        if (delBtn) {
            e.preventDefault();
            if (confirm('Bạn có chắc muốn xóa mẫu hoa này khỏi giỏ hàng?')) {
                updateCartAjax(delBtn.getAttribute('href'));
            }
            return;
        }

        const qtyBtn = e.target.closest('.btn-qty');
        if (qtyBtn) {
            e.preventDefault();
            updateCartAjax(qtyBtn.getAttribute('href'));
        }
    });

    container.addEventListener('change', function (e) {
        if (e.target.classList.contains('cart-input-qty')) {
            let val = parseInt(e.target.value);
            if (isNaN(val) || val < 1) val = 1;
            const id = e.target.getAttribute('data-id');
            updateCartAjax('MyCart.php?action=update&id=' + id + '&qty=' + val);
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>