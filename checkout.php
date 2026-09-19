<?php
require_once 'includes/auth.php';
requireLogin();
require_once 'includes/header.php';
require_once 'includes/mailer.php';
include_once 'DataProvider.php';

if (isset($_SESSION['MyCart']) && is_array($_SESSION['MyCart'])) {
    foreach ($_SESSION['MyCart'] as $k => $v) {
        if (intval($k) <= 0 || intval($v) <= 0) {
            unset($_SESSION['MyCart'][$k]);
        }
    }
}

if (!isset($_SESSION['MyCart']) || empty($_SESSION['MyCart'])) {
    echo "<div style='text-align: center; padding: 50px;'>";
    echo "<h3>Giỏ hàng của bạn đang trống!</h3>";
    echo "<p><a href='index.php' class='btn btn-detail'>Quay lại mua hàng</a></p>";
    echo "</div>";
    require_once 'includes/footer.php';
    exit;
}

$currentUser = getCurrentUser();
$maKH = intval($currentUser['MaKH']);

$connUser = getDbConnection();
$stmtKH = $connUser->prepare("SELECT * FROM khachhang WHERE MaKH = ?");
$stmtKH->bind_param("i", $maKH);
$stmtKH->execute();
$rsKH = $stmtKH->get_result();
$infoKH = $rsKH->fetch_assoc();
$stmtKH->close();

$error = '';
$orderSuccess = false;
$createdMaHD = 0;
$finalTotal = 0;
$orderPTTT = 'COD';
$homNay = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btnDatHang'])) {
    $noiGiao = trim($_POST['txtNoiGiao'] ?? '');
    $ngayGiao = trim($_POST['txtNgayGiao'] ?? '');
    $gioGiao = trim($_POST['txtGioGiao'] ?? '');
    $loiNhan = trim($_POST['txtLoiNhan'] ?? '');
    $phuongThucTT = trim($_POST['rdPhuongThucTT'] ?? 'COD');
    $orderPTTT = $phuongThucTT;

    if (empty($noiGiao)) {
        $error = 'Vui lòng nhập địa chỉ giao hàng!';
    } elseif (empty($ngayGiao)) {
        $error = 'Vui lòng chọn ngày nhận hàng!';
    } elseif ($ngayGiao < $homNay) {
        $error = 'Ngày nhận hàng không được chọn ngày trong quá khứ!';
    } else {
        $conn = getDbConnection();
        if (!$conn) {
            $error = 'Không thể kết nối cơ sở dữ liệu!';
        } else {
            mysqli_report(MYSQLI_REPORT_OFF);
            mysqli_begin_transaction($conn);

            $ngayDat = date('Y-m-d H:i:s');
            $tinhTrang = 0;

            $resMax = mysqli_query($conn, "SELECT IFNULL(MAX(MaHD), 0) + 1 AS NextID FROM hoadon");
            $rowMax = mysqli_fetch_assoc($resMax);
            $newMaHD = intval($rowMax['NextID']);
            if ($newMaHD <= 0) {
                $newMaHD = 1;
            }

            $subtotal = 0;
            $emailItems = [];
            $cartItems = [];
            $successDetails = true;

            $stmtPrice = $conn->prepare("SELECT TenHoa, GiaBan FROM hoa WHERE MaHoa = ?");
            foreach ($_SESSION['MyCart'] as $maHoaRaw => $soLuongRaw) {
                $maHoa = intval($maHoaRaw);
                $soLuong = intval($soLuongRaw);
                if ($maHoa <= 0 || $soLuong <= 0) continue;

                $stmtPrice->bind_param("i", $maHoa);
                $stmtPrice->execute();
                $resPrice = $stmtPrice->get_result();
                if ($resPrice && $resPrice->num_rows > 0) {
                    $rowPrice = $resPrice->fetch_assoc();
                    $giaBan = floatval($rowPrice['GiaBan']);
                    $thanhTien = $giaBan * $soLuong;
                    $subtotal += $thanhTien;

                    $cartItems[] = [
                        'MaHoa' => $maHoa,
                        'TenHoa' => $rowPrice['TenHoa'],
                        'SoLuong' => $soLuong,
                        'DonGia' => $giaBan,
                        'ThanhTien' => $thanhTien
                    ];
                } else {
                    $error = "Không tìm thấy mã hoa: " . $maHoa;
                    $successDetails = false;
                    break;
                }
            }
            $stmtPrice->close();

            if ($successDetails) {
                $soTienGiam = 0;
                $maVoucher = null;
                $voucherNote = '';
                if (isset($_SESSION['voucher'])) {
                    $v = $_SESSION['voucher'];
                    if ($subtotal >= floatval($v['DonToiThieu'])) {
                        if (intval($v['PhanTramGiam']) > 0) {
                            $soTienGiam = ($subtotal * intval($v['PhanTramGiam'])) / 100;
                        } elseif (floatval($v['SoTienGiam']) > 0) {
                            $soTienGiam = floatval($v['SoTienGiam']);
                        }
                    }
                    if ($soTienGiam > $subtotal) {
                        $soTienGiam = $subtotal;
                    }
                    if ($soTienGiam > 0) {
                        $maVoucher = intval($v['MaVoucher']);
                        $voucherNote = "[Voucher " . $v['Code'] . "] ";
                    }
                }
                $finalTotal = max(0, $subtotal - $soTienGiam);
                $loiNhanFull = $voucherNote . $loiNhan;

                $stmtHD = $conn->prepare("INSERT INTO hoadon (MaHD, NgayDat, DiaChiGiao, NgayGiao, MaKH, TinhTrang, PhuongThucTT, LoiNhan, GioGiao, MaVoucher, SoTienGiam) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmtHD->bind_param("isssissssii", $newMaHD, $ngayDat, $noiGiao, $ngayGiao, $maKH, $tinhTrang, $phuongThucTT, $loiNhanFull, $gioGiao, $maVoucher, $soTienGiam);

                if (!$stmtHD->execute()) {
                    $error = "Lỗi chèn hoadon: " . $stmtHD->error;
                    mysqli_rollback($conn);
                    $stmtHD->close();
                } else {
                    $stmtHD->close();
                    $createdMaHD = $newMaHD;

                    $stmtCT = $conn->prepare("INSERT INTO chitiethd (MaHD, MaHoa, SoLuong, DonGia) VALUES (?, ?, ?, ?)");
                    foreach ($cartItems as $item) {
                        $mHoa = $item['MaHoa'];
                        $sLuong = $item['SoLuong'];
                        $dGia = $item['DonGia'];
                        $stmtCT->bind_param("iiid", $createdMaHD, $mHoa, $sLuong, $dGia);
                        if (!$stmtCT->execute()) {
                            $error = "Lỗi chèn chitiethd: " . $stmtCT->error;
                            $successDetails = false;
                            break;
                        }
                        $emailItems[] = [
                            'TenHoa' => $item['TenHoa'],
                            'SoLuong' => $item['SoLuong'],
                            'GiaBan' => $item['DonGia'],
                            'ThanhTien' => $item['ThanhTien']
                        ];
                    }
                    $stmtCT->close();

                    if ($successDetails) {
                        if ($maVoucher !== null) {
                            $stmtV = $conn->prepare("UPDATE voucher SET LuotDung = GREATEST(0, LuotDung - 1) WHERE MaVoucher = ?");
                            $stmtV->bind_param("i", $maVoucher);
                            $stmtV->execute();
                            $stmtV->close();
                            unset($_SESSION['voucher']);
                        }

                        mysqli_commit($conn);
                        unset($_SESSION['MyCart']);
                        clearUserCartDb($maKH);
                        $orderSuccess = true;

                        $customerEmail = !empty($infoKH['Email']) ? $infoKH['Email'] : '';
                        sendOrderConfirmationEmail($createdMaHD, $infoKH['HoTen'], $customerEmail, $emailItems, $finalTotal, $noiGiao, $ngayGiao, $phuongThucTT, $loiNhan, $gioGiao);
                    } else {
                        mysqli_rollback($conn);
                    }
                }
            } else {
                mysqli_rollback($conn);
            }

            mysqli_close($conn);
        }
    }
}
?>

<style>
.checkout-wrapper {
    max-width: 920px;
    margin: 0 auto;
    background: #fff;
    padding: 35px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}
.payment-option-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.payment-option-card:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}
.payment-option-card.selected {
    border-color: #e30019;
    background: #fff5f5;
}
.qr-container {
    display: flex;
    gap: 30px;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    background: #f8fafc;
    border: 2px dashed #0284c7;
    border-radius: 12px;
    padding: 25px;
    margin: 25px 0;
}
.btn-copy-chip {
    background: #e2e8f0;
    border: 1px solid #cbd5e1;
    color: #334155;
    padding: 3px 9px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.15s;
}
.btn-copy-chip:hover {
    background: #0284c7;
    border-color: #0284c7;
    color: #fff;
}
</style>

<div class="checkout-wrapper">
<?php if ($orderSuccess): ?>
    <?php if ($orderPTTT === 'VIETQR'): ?>
        <?php
            $qrBank = "TCB";
            $qrAcc = "9981984539";
            $qrName = "VO VAN VINH";
            $qrAmount = intval($finalTotal);
            $qrMemo = "DH" . $createdMaHD . " " . preg_replace('/[^a-zA-Z0-9]/', '', $currentUser['TenDN']);
            $qrUrl = "https://img.vietqr.io/image/{$qrBank}-{$qrAcc}-compact2.png?amount={$qrAmount}&addInfo=" . urlencode($qrMemo) . "&accountName=" . urlencode($qrName);
        ?>
        <div style="text-align: center; padding: 15px 0;">
            <div style="display: inline-flex; align-items: center; justify-content: center; background: #e8f8f0; color: #27ae60; font-size: 36px; width: 64px; height: 64px; border-radius: 50%; margin-bottom: 12px;">
                <i class="fa-solid fa-check"></i>
            </div>
            <h2 style="color: #27ae60; margin-bottom: 6px;">Đặt hàng thành công!</h2>
            <p style="color: #64748b; font-size: 15px; margin-bottom: 20px;">
                Mã hóa đơn: <strong style="color: #0f172a;">#<?php echo $createdMaHD; ?></strong> | Quý khách vui lòng quét mã QR Techcombank dưới đây để thanh toán đơn hàng.
            </p>

            <div class="qr-container">
                <div style="text-align: center;">
                    <img src="<?php echo $qrUrl; ?>" alt="VietQR Techcombank VO VAN VINH" style="width: 260px; border-radius: 10px; box-shadow: 0 4px 18px rgba(0,0,0,0.12); border: 1px solid #e2e8f0; display: block; margin: 0 auto;">
                    <div style="font-size: 12.5px; color: #64748b; margin-top: 10px;">
                        <i class="fa-solid fa-camera"></i> Mở app ngân hàng bất kỳ để quét mã 24/7
                    </div>
                </div>

                <div style="text-align: left; min-width: 290px; max-width: 400px;">
                    <h3 style="color: #0f172a; margin-bottom: 15px; font-size: 17px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">
                        <i class="fa-solid fa-building-columns" style="color: #e30019;"></i> Thông tin chuyển khoản Techcombank
                    </h3>
                    
                    <div style="margin-bottom: 12px;">
                        <div style="font-size: 12px; color: #64748b;">Ngân hàng thụ hưởng:</div>
                        <div style="font-weight: 600; color: #1e293b;">Ngân hàng TMCP Kỹ thương Việt Nam (Techcombank)</div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <div style="font-size: 12px; color: #64748b;">Chủ tài khoản:</div>
                        <div style="font-weight: 700; color: #e30019; font-size: 17px; letter-spacing: 0.5px;">VO VAN VINH</div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <div style="font-size: 12px; color: #64748b;">Số tài khoản:</div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <strong style="font-size: 18px; color: #0f172a; font-family: monospace; letter-spacing: 1px;">9981 9845 39</strong>
                            <button type="button" class="btn-copy-chip" onclick="copyText('9981984539', 'Đã sao chép số tài khoản!')"><i class="fa-regular fa-copy"></i> Sao chép</button>
                        </div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <div style="font-size: 12px; color: #64748b;">Số tiền chính xác:</div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <strong style="font-size: 18px; color: #e30019;"><?php echo number_format($finalTotal); ?> đ</strong>
                            <button type="button" class="btn-copy-chip" onclick="copyText('<?php echo $finalTotal; ?>', 'Đã sao chép số tiền!')"><i class="fa-regular fa-copy"></i> Sao chép</button>
                        </div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <div style="font-size: 12px; color: #64748b;">Nội dung chuyển khoản (bắt buộc):</div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <strong style="font-size: 15px; color: #d97706; font-family: monospace; background: #fef3c7; padding: 3px 8px; border-radius: 4px;"><?php echo $qrMemo; ?></strong>
                            <button type="button" class="btn-copy-chip" onclick="copyText('<?php echo $qrMemo; ?>', 'Đã sao chép nội dung chuyển khoản!')"><i class="fa-regular fa-copy"></i> Sao chép</button>
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="lichsudonhang.php" class="btn btn-detail" style="text-decoration: none; padding: 12px 24px; font-weight: 500;">
                    <i class="fa-solid fa-receipt"></i> Xem đơn hàng của tôi
                </a>
                <a href="index.php" class="btn btn-cart" style="text-decoration: none; padding: 12px 24px;">
                    <i class="fa-solid fa-store"></i> Tiếp tục mua hoa
                </a>
            </div>
        </div>

        <script>
        function copyText(text, msg) {
            navigator.clipboard.writeText(text).then(function () {
                alert(msg);
            }).catch(function () {
                const el = document.createElement('textarea');
                el.value = text;
                document.body.appendChild(el);
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);
                alert(msg);
            });
        }
        </script>
    <?php else: ?>
        <div style="text-align: center; padding: 30px 0;">
            <div style="display: inline-flex; align-items: center; justify-content: center; background: #e8f8f0; color: #27ae60; font-size: 36px; width: 64px; height: 64px; border-radius: 50%; margin-bottom: 12px;">
                <i class="fa-solid fa-check"></i>
            </div>
            <h2 style="color: #27ae60; margin-bottom: 10px;">Đặt hàng thành công!</h2>
            <p style="font-size: 16px; margin-bottom: 8px;">Mã hóa đơn: <strong>#<?php echo $createdMaHD; ?></strong></p>
            <p style="font-size: 16px; margin-bottom: 15px;">Hình thức: <strong>Thanh toán tiền mặt khi nhận hàng (COD)</strong></p>
            <p style="font-size: 16px; margin-bottom: 20px;">Tổng thanh toán: <strong style="color: #e30019;"><?php echo number_format($finalTotal); ?> đ</strong></p>
            <p style="color: #64748b; margin-bottom: 25px;">Shop đã gửi email xác nhận đơn hàng tới hòm thư của quý khách.</p>
            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="lichsudonhang.php" class="btn btn-detail" style="text-decoration: none; padding: 12px 24px;">
                    <i class="fa-solid fa-receipt"></i> Xem đơn hàng của tôi
                </a>
                <a href="index.php" class="btn btn-cart" style="text-decoration: none; padding: 12px 24px;">
                    <i class="fa-solid fa-store"></i> Tiếp tục mua hàng
                </a>
            </div>
        </div>
    <?php endif; ?>
<?php else: ?>
    <h2 style="margin-bottom: 25px; text-align: center; color: #0f172a;">Xác nhận đặt hàng & Thanh toán</h2>

    <?php if (!empty($error)): ?>
        <div style="background: #fef2f2; color: #991b1b; padding: 14px 18px; border: 1px solid #fecaca; border-radius: 8px; margin-bottom: 20px;">
            <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <table border="1" cellpadding="10" cellspacing="0" width="100%" style="text-align: center; border-collapse: collapse; margin-bottom: 25px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
        <tr style="background-color: #f8fafc; color: #475569; font-size: 14px;">
            <th style="text-align: left; padding-left: 15px;">Sản phẩm hoa</th>
            <th>Số lượng</th>
            <th>Đơn giá</th>
            <th style="text-align: right; padding-right: 15px;">Thành tiền</th>
        </tr>
        <?php
        $tongTienTam = 0;
        $connCart = getDbConnection();
        $stmtCart = $connCart->prepare("SELECT * FROM hoa WHERE MaHoa = ?");
        foreach ($_SESSION['MyCart'] as $maHoaKey => $soLuongVal) {
            $maHoa = intval($maHoaKey);
            $soLuong = intval($soLuongVal);
            if ($maHoa <= 0) continue;
            
            $stmtCart->bind_param("i", $maHoa);
            $stmtCart->execute();
            $rsCart = $stmtCart->get_result();
            if ($rsCart && $rsCart->num_rows > 0) {
                $hoa = $rsCart->fetch_assoc();
                $gia = floatval($hoa['GiaBan']);
                $thanhTien = $gia * $soLuong;
                $tongTienTam += $thanhTien;
                echo "<tr>";
                echo "<td style='text-align: left; padding: 10px 15px; font-weight: 500;'>" . htmlspecialchars($hoa['TenHoa']) . "</td>";
                echo "<td>" . $soLuong . "</td>";
                echo "<td>" . number_format($gia) . " đ</td>";
                echo "<td style='text-align: right; padding-right: 15px; font-weight: 600;'>" . number_format($thanhTien) . " đ</td>";
                echo "</tr>";
            }
        }
        $stmtCart->close();
        ?>
        <?php
        $discountAmountCheckout = 0;
        if (isset($_SESSION['voucher'])) {
            $v = $_SESSION['voucher'];
            if ($tongTienTam >= floatval($v['DonToiThieu'])) {
                if ($v['PhanTramGiam'] > 0) {
                    $discountAmountCheckout = ($tongTienTam * $v['PhanTramGiam']) / 100;
                } elseif ($v['SoTienGiam'] > 0) {
                    $discountAmountCheckout = floatval($v['SoTienGiam']);
                }
                if ($discountAmountCheckout > $tongTienTam) {
                    $discountAmountCheckout = $tongTienTam;
                }
            }
        }
        ?>
        <?php if ($discountAmountCheckout > 0): ?>
        <tr style="background: #f0fdf4; color: #16a34a;">
            <td colspan="3" style="text-align: right; font-weight: 600; padding: 10px 15px; font-size: 14px;">
                <i class="fa-solid fa-tag"></i> Giảm giá (Voucher <?php echo htmlspecialchars($_SESSION['voucher']['Code']); ?>):
            </td>
            <td style="font-weight: 600; font-size: 15px; text-align: right; padding-right: 15px;">
                -<?php echo number_format($discountAmountCheckout); ?> đ
            </td>
        </tr>
        <?php endif; ?>
        <tr style="background: #fafafa;">
            <td colspan="3" style="text-align: right; font-weight: bold; padding: 12px 15px; font-size: 15px;">Tổng cộng:</td>
            <td style="color: #e30019; font-weight: bold; font-size: 18px; text-align: right; padding-right: 15px;"><?php echo number_format(max(0, $tongTienTam - $discountAmountCheckout)); ?> đ</td>
        </tr>
    </table>

    <form method="post" action="">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px; margin-bottom: 18px;">
            <div>
                <label style="display:block; margin-bottom: 6px; font-weight: 600; font-size: 14px; color: #334155;">Họ tên người nhận:</label>
                <input type="text" value="<?php echo htmlspecialchars($infoKH['HoTen']); ?>" readonly style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; background: #f1f5f9; box-sizing: border-box;">
            </div>
            <div>
                <label style="display:block; margin-bottom: 6px; font-weight: 600; font-size: 14px; color: #334155;">Điện thoại liên hệ:</label>
                <input type="text" value="<?php echo htmlspecialchars($infoKH['DienThoai']); ?>" readonly style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; background: #f1f5f9; box-sizing: border-box;">
            </div>
        </div>

        <div style="margin-bottom: 18px;">
            <label style="display:block; margin-bottom: 6px; font-weight: 600; font-size: 14px; color: #334155;">Địa chỉ giao hoa (*):</label>
            <input type="text" name="txtNoiGiao" value="<?php echo htmlspecialchars($infoKH['DiaChi']); ?>" required placeholder="Số nhà, tên đường, phường/xã, quận/huyện..." style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px; margin-bottom: 18px;">
            <div>
                <label style="display:block; margin-bottom: 6px; font-weight: 600; font-size: 14px; color: #334155;">Ngày nhận hoa (*):</label>
                <input type="date" name="txtNgayGiao" min="<?php echo $homNay; ?>" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
            </div>
            <div>
                <label style="display:block; margin-bottom: 6px; font-weight: 600; font-size: 14px; color: #334155;">Khung giờ giao hoa:</label>
                <select name="txtGioGiao" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; box-sizing: border-box;">
                    <option value="Càng sớm càng tốt">Giao càng sớm càng tốt</option>
                    <option value="08:00 - 11:00 (Buổi sáng)">08:00 - 11:00 (Buổi sáng)</option>
                    <option value="11:00 - 14:00 (Buổi trưa)">11:00 - 14:00 (Buổi trưa)</option>
                    <option value="14:00 - 17:00 (Buổi chiều)">14:00 - 17:00 (Buổi chiều)</option>
                    <option value="17:00 - 20:00 (Buổi tối)">17:00 - 20:00 (Buổi tối)</option>
                </select>
            </div>
        </div>

        <div style="margin-bottom: 22px;">
            <label style="display:block; margin-bottom: 6px; font-weight: 600; font-size: 14px; color: #334155;">💌 Lời nhắn thiệp chúc mừng (Tặng kèm miễn phí):</label>
            <textarea name="txtLoiNhan" rows="2" placeholder="Ví dụ: Chúc mừng sinh nhật em yêu, chúc em luôn xinh đẹp và hạnh phúc!..." style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-family: inherit; font-size: 14px;"></textarea>
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display:block; margin-bottom: 10px; font-weight: 600; font-size: 14px; color: #334155;">💳 Phương thức thanh toán:</label>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <label class="payment-option-card selected" id="optCOD">
                    <input type="radio" name="rdPhuongThucTT" value="COD" checked style="accent-color: #e30019; width: 18px; height: 18px;">
                    <div>
                        <div style="font-weight: 600; color: #1e293b;"><i class="fa-solid fa-money-bill-wave" style="color: #27ae60;"></i> Thanh toán tiền mặt khi nhận hoa (COD)</div>
                        <div style="font-size: 13px; color: #64748b;">Nhận hoa kiểm tra tươi tắn rồi mới thanh toán cho shipper.</div>
                    </div>
                </label>

                <label class="payment-option-card" id="optVietQR">
                    <input type="radio" name="rdPhuongThucTT" value="VIETQR" style="accent-color: #e30019; width: 18px; height: 18px;">
                    <div>
                        <div style="font-weight: 600; color: #1e293b;"><i class="fa-solid fa-qrcode" style="color: #e30019;"></i> Chuyển khoản Ngân hàng (Quét mã VietQR Techcombank)</div>
                        <div style="font-size: 13px; color: #64748b;">Quét mã QR Techcombank (VO VAN VINH) tự động điền đúng số tiền và nội dung.</div>
                    </div>
                </label>
            </div>
        </div>

        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <a href="GioHang.php" class="btn btn-detail" style="flex: 1; min-width: 160px; text-align: center; line-height: 25px;">Quay lại giỏ hàng</a>
            <button type="submit" name="btnDatHang" class="btn btn-cart" style="flex: 2; min-width: 220px; padding: 14px; font-size: 16px; font-weight: 600;">
                <i class="fa-solid fa-check-circle"></i> Hoàn tất đặt hàng
            </button>
        </div>
    </form>

    <script>
    document.querySelectorAll('.payment-option-card').forEach(card => {
        card.addEventListener('click', function () {
            document.querySelectorAll('.payment-option-card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
    </script>
<?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>