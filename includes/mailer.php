<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendOrderConfirmationEmail($maHD, $customerName, $customerEmail, $items, $finalTotal, $noiGiao, $ngayGiao, $phuongThucTT = 'COD', $loiNhan = '', $gioGiao = '') {
    
    $logDir = dirname(__DIR__) . '/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }
    $logFile = $logDir . '/order_email.log';

    $ptttText = ($phuongThucTT === 'VIETQR') ? 'Chuyển khoản Ngân hàng (VietQR Techcombank)' : 'Tiền mặt khi nhận hàng (COD)';
    $body = "<h2>SEN GARDEN - XÁC NHẬN ĐƠN HÀNG #$maHD</h2>\n";
    $body .= "<p>Xin chào <strong>" . htmlspecialchars($customerName) . "</strong>,</p>\n";
    $body .= "<p>Cảm ơn bạn đã đặt hoa tại Sen Garden. Đơn hàng của bạn đã được tiếp nhận thành công!</p>\n";
    $body .= "<p><strong>Địa chỉ nhận hoa:</strong> " . htmlspecialchars($noiGiao) . "<br>\n";
    $body .= "<strong>Ngày giao hẹn:</strong> " . htmlspecialchars($ngayGiao) . ($gioGiao ? " (" . htmlspecialchars($gioGiao) . ")" : "") . "<br>\n";
    $body .= "<strong>Phương thức thanh toán:</strong> " . htmlspecialchars($ptttText) . "</p>\n";
    if (!empty($loiNhan)) {
        $body .= "<p style='background:#fff4e6; padding:10px; border-left:4px solid #f39c12;'><strong>💌 Lời nhắn thiệp chúc mừng:</strong><br><em>\"" . htmlspecialchars($loiNhan) . "\"</em></p>\n";
    }

    $body .= "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%; max-width: 600px;'>\n";
    $body .= "<tr style='background-color: #f2f2f2;'><th>Tên hoa</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th></tr>\n";
    
    foreach ($items as $item) {
        $body .= "<tr>\n";
        $body .= "<td>" . htmlspecialchars($item['TenHoa']) . "</td>\n";
        $body .= "<td align='center'>" . $item['SoLuong'] . "</td>\n";
        $body .= "<td align='right'>" . number_format($item['GiaBan']) . " đ</td>\n";
        $body .= "<td align='right'>" . number_format($item['ThanhTien']) . " đ</td>\n";
        $body .= "</tr>\n";
    }

    $body .= "<tr><td colspan='3' align='right'><strong>Tổng thanh toán:</strong></td><td align='right'><strong style='color: #e30019;'>" . number_format($finalTotal) . " đ</strong></td></tr>\n";
    $body .= "</table>\n";
    $body .= "<p>Đơn hàng sẽ được chuẩn bị và giao đúng hẹn. Chúc bạn một ngày tốt lành!</p>\n";

 
    $logContent = "==================================================\n";
    $logContent .= "THỜI GIAN: " . date('d/m/Y H:i:s') . "\n";
    $logContent .= "GỬI ĐẾN EMAIL: " . ($customerEmail ? $customerEmail : '(Khách chưa có email)') . "\n";
    $logContent .= "NGƯỜI NHẬN: " . $customerName . "\n";
    $logContent .= "MÃ ĐƠN HÀNG: #" . $maHD . "\n";
    $logContent .= "NỘI DUNG EMAIL:\n";
    $logContent .= strip_tags(str_replace(['<br>', '</tr>', '</h2>', '</p>'], "\n", $body)) . "\n";
    $logContent .= "==================================================\n\n";

    file_put_contents($logFile, $logContent, FILE_APPEND);

   
    $phpmailerPath = dirname(__DIR__) . '/libs/PHPMailer/PHPMailer.php';
    if (file_exists($phpmailerPath)) {
        require_once dirname(__DIR__) . '/libs/PHPMailer/Exception.php';
        require_once dirname(__DIR__) . '/libs/PHPMailer/PHPMailer.php';
        require_once dirname(__DIR__) . '/libs/PHPMailer/SMTP.php';

        $smtpUser = 'your_email@gmail.com';
        $smtpPass = 'your_app_password';

        
        if ($smtpUser !== 'your_email@gmail.com' && !empty($smtpUser) && !empty($smtpPass) && $smtpPass !== 'your_app_password') {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = $smtpUser;
                $mail->Password   = $smtpPass;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';
                $mail->Timeout    = 5; 

                $mail->setFrom($smtpUser, 'Flower Shop');
                if (!empty($customerEmail) && filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
                    $mail->addAddress($customerEmail, $customerName);
                    $mail->isHTML(true);
                    $mail->Subject = "Flower Shop - Xác nhận đơn hàng #$maHD";
                    $mail->Body    = $body;
                    $mail->send();
                }
            } catch (Exception $e) {
                file_put_contents($logFile, "Ghi chú SMTP: " . $mail->ErrorInfo . "\n\n", FILE_APPEND);
            }
        }
    }
    return true;
}