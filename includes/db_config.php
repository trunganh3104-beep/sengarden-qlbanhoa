<?php
mysqli_report(MYSQLI_REPORT_OFF);
if ((isset($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'ct.ws') !== false || strpos($_SERVER['HTTP_HOST'], 'infinityfree') !== false)) || (isset($_SERVER['SERVER_NAME']) && (strpos($_SERVER['SERVER_NAME'], 'ct.ws') !== false || strpos($_SERVER['SERVER_NAME'], 'infinityfree') !== false))) {
    if (!defined('DB_HOST')) define('DB_HOST', 'sql102.infinityfree.com');
    if (!defined('DB_USER')) define('DB_USER', 'if0_42880374');
    if (!defined('DB_PASS')) define('DB_PASS', 'vinh18072006');
    if (!defined('DB_NAME')) define('DB_NAME', 'if0_42880374_qlbanhoa');
} else {
    if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
    if (!defined('DB_USER')) define('DB_USER', 'root');
    if (!defined('DB_PASS')) define('DB_PASS', '');
    if (!defined('DB_NAME')) define('DB_NAME', 'qlbanhoa');
}

if (!function_exists('ensureRequiredTables')) {
    function ensureRequiredTables($conn) {
        if (!$conn) return;
        static $checked = false;
        if ($checked) return;
        $checked = true;

        @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `lienhe` (
          `MaLH` int(11) NOT NULL AUTO_INCREMENT,
          `HoTen` varchar(100) NOT NULL,
          `DienThoai` varchar(20) DEFAULT NULL,
          `Email` varchar(100) DEFAULT NULL,
          `TieuDe` varchar(255) DEFAULT NULL,
          `NoiDung` text NOT NULL,
          `NgayGui` datetime DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`MaLH`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

        @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `qlchat` (
          `MaChat` int(11) NOT NULL AUTO_INCREMENT,
          `MaKH` int(11) NOT NULL,
          `NguoiGui` varchar(20) NOT NULL,
          `NoiDung` text NOT NULL,
          `ThoiGian` datetime DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`MaChat`),
          KEY `MaKH` (`MaKH`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

        @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `voucher` (
          `MaVoucher` int(11) NOT NULL AUTO_INCREMENT,
          `Code` varchar(50) NOT NULL,
          `MoTa` varchar(255) NOT NULL,
          `PhanTramGiam` int(11) DEFAULT 0,
          `SoTienGiam` decimal(18,0) DEFAULT 0,
          `DonToiThieu` decimal(18,0) DEFAULT 0,
          `NgayHetHan` date DEFAULT NULL,
          `LuotDung` int(11) DEFAULT 500,
          `TrangThai` tinyint(1) DEFAULT 1,
          PRIMARY KEY (`MaVoucher`),
          UNIQUE KEY `Code` (`Code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

        @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `truycap` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `ip_address` varchar(50) NOT NULL,
          `thoi_gian` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `ngay` date NOT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_ngay` (`ngay`),
          KEY `idx_thoi_gian` (`thoi_gian`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

        @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `giohang_user` (
          `MaKH` int(11) NOT NULL,
          `MaHoa` int(11) NOT NULL,
          `SoLuong` int(11) NOT NULL DEFAULT 1,
          `NgayTao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`MaKH`, `MaHoa`),
          KEY `idx_makh` (`MaKH`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

        $chk = @mysqli_query($conn, "SELECT COUNT(*) as c FROM `voucher`");
        if ($chk) {
            $r = mysqli_fetch_assoc($chk);
            if (intval($r['c'] ?? 0) === 0) {
                @mysqli_query($conn, "INSERT IGNORE INTO `voucher` (`MaVoucher`, `Code`, `MoTa`, `PhanTramGiam`, `SoTienGiam`, `DonToiThieu`, `NgayHetHan`, `LuotDung`, `TrangThai`) VALUES
                (1, 'SENGARDEN', 'Ưu đãi tri ân giảm 10% toàn bộ đơn hàng', 10, 0, 0, '2026-12-31', 999, 1),
                (2, 'HOATUOI2026', 'Giảm trực tiếp 50.000đ cho đơn hoa từ 350.000đ', 0, 50000, 350000, '2026-12-31', 500, 1),
                (3, 'FREESHIP', 'Giảm 30.000đ phí vận chuyển cho đơn từ 200.000đ', 0, 30000, 200000, '2026-12-31', 500, 1);");
            }
        }

        $chkQtr = @mysqli_query($conn, "SELECT MaKH, Role FROM `khachhang` WHERE `TenDN` = 'qtr'");
        if ($chkQtr) {
            if (mysqli_num_rows($chkQtr) === 0) {
                $hashQtr = password_hash('123', PASSWORD_DEFAULT);
                $hashQtrEsc = mysqli_real_escape_string($conn, $hashQtr);
                @mysqli_query($conn, "INSERT INTO `khachhang` (`TenDN`, `MatKhau`, `HoTen`, `DiaChi`, `DienThoai`, `Email`, `Role`) 
                    VALUES ('qtr', '$hashQtrEsc', 'Quản trị viên QTR', 'Hệ thống Sen Garden', '0909999999', 'qtr@sengarden.vn', 1)");
            } else {
                $rowQtr = mysqli_fetch_assoc($chkQtr);
                if (intval($rowQtr['Role']) !== 1) {
                    @mysqli_query($conn, "UPDATE `khachhang` SET `Role` = 1 WHERE `TenDN` = 'qtr'");
                }
            }
        }

        @mysqli_query($conn, "ALTER TABLE `chitiethd` ADD COLUMN IF NOT EXISTS `DonGia` decimal(18,0) NOT NULL DEFAULT 0");
        @mysqli_query($conn, "ALTER TABLE `hoadon` ADD COLUMN IF NOT EXISTS `DiaChiGiao` varchar(255) NULL AFTER `NgayDat`");
        @mysqli_query($conn, "ALTER TABLE `hoadon` ADD COLUMN IF NOT EXISTS `NgayGiao` date NULL AFTER `DiaChiGiao`");
        @mysqli_query($conn, "ALTER TABLE `hoadon` ADD COLUMN IF NOT EXISTS `MaVoucher` int(11) DEFAULT NULL");
        @mysqli_query($conn, "ALTER TABLE `hoadon` ADD COLUMN IF NOT EXISTS `SoTienGiam` decimal(18,0) DEFAULT 0");

        $chkDonGia = @mysqli_query($conn, "SELECT COUNT(*) AS c FROM `chitiethd` WHERE `DonGia` = 0");
        if ($chkDonGia) {
            $rDg = mysqli_fetch_assoc($chkDonGia);
            if (intval($rDg['c'] ?? 0) > 0) {
                @mysqli_query($conn, "UPDATE `chitiethd` ct JOIN `hoa` h ON ct.MaHoa = h.MaHoa SET ct.DonGia = h.GiaBan WHERE ct.DonGia = 0 OR ct.DonGia IS NULL");
            }
        }

        $checkFk = @mysqli_query($conn, "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = 'hoadon' AND CONSTRAINT_NAME = 'hoadon_ibfk_2'");
        if ($checkFk && mysqli_num_rows($checkFk) === 0) {
            @mysqli_query($conn, "ALTER TABLE `hoadon` ADD CONSTRAINT `hoadon_ibfk_2` FOREIGN KEY (`MaVoucher`) REFERENCES `voucher` (`MaVoucher`) ON DELETE SET NULL ON UPDATE CASCADE");
        }
    }
}

if (!function_exists('getDbConnection')) {
    function getDbConnection() {
        $conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn) {
            mysqli_set_charset($conn, 'utf8mb4');
            ensureRequiredTables($conn);
        }
        return $conn;
    }
}
?>