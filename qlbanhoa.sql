-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th9 05, 2026 lúc 04:45 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `qlbanhoa`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitiethd`
--

CREATE TABLE `chitiethd` (
  `SoDH` int(11) NOT NULL,
  `MaHD` int(11) NOT NULL,
  `MaHoa` int(11) NOT NULL,
  `SoLuong` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chitiethd`
--

INSERT INTO `chitiethd` (`SoDH`, `MaHD`, `MaHoa`, `SoLuong`) VALUES
(1, 1, 26, 34),
(2, 1, 25, 7),
(3, 1, 8, 1),
(4, 1, 27, 12),
(5, 1, 33, 4),
(6, 1, 23, 3),
(7, 1, 24, 2),
(8, 2, 33, 3);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoa`
--

CREATE TABLE `hoa` (
  `MaHoa` int(11) NOT NULL,
  `MaLoai` int(11) NOT NULL,
  `TenHoa` varchar(50) NOT NULL,
  `GiaBan` int(11) NOT NULL,
  `ThanhPhan` varchar(250) NOT NULL,
  `Hinh` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `hoa`
--

INSERT INTO `hoa` (`MaHoa`, `MaLoai`, `TenHoa`, `GiaBan`, `ThanhPhan`, `Hinh`) VALUES
(1, 1, 'Đón xuân', 50000, 'Hồng xanh dương', 'hh2.jpg'),
(2, 1, 'Hồn nhiên', 60000, 'Hoa giỏ', 'hh3.jpg'),
(3, 1, 'Tím thuỷ chung', 45000, 'Hoa lan bó', 'hh4.jpg'),
(4, 1, 'Nét duyên tím', 40000, 'Hoa hồng màu tím nhạt', 'hh5.jpg'),
(5, 1, 'Cùng khoe sắc', 70000, 'Hoa hồng các màu', 'hh6.jpg'),
(6, 1, 'Trắng thơ ngây', 65000, 'Hồng nhung', 'hh7.jpg'),
(7, 2, 'Dây tơ hồng', 250000, 'Hoa hồng màu hồng đậm', 'hc1.jpg'),
(8, 2, 'Cầu thuỷ tinh', 220000, 'Hoa hồng xanh dương', 'hc2.jpg'),
(9, 2, 'Duyên thầm', 260000, 'Hoa cúc trắng, baby,', 'hc3.jpg'),
(10, 2, 'Đâm chồi nảy lộc', 180000, 'Hoa hồng trắng và cá', 'hc4.jpg'),
(11, 2, 'Hoà quyện', 270000, 'Hoa hồng trắng, lá t', 'hc5.jpg'),
(12, 2, 'Nồng nàn', 210000, 'Hoa hồng đỏ, lá thuỷ', 'hc6.jpg'),
(13, 3, 'Together', 120000, 'Hồng xác pháo, cúc t', 'hoa_sn_dam-me.jpg'),
(14, 3, 'Long trip', 85000, 'Hoa hồng đỏ, lá kim ', 'hoa_sn_may-man.jpg'),
(15, 3, 'Beautiful life', 100000, 'Hoa hồng đỏ, lá măng', 'hoa_sn_may-man-2.jpg'),
(16, 3, 'Morning Sun', 75000, 'Hoa hồng vàng\r\n', 'hoa_sn_nang-diu-dang.jpg'),
(17, 3, 'Pretty Bloom', 65000, 'Hoa hồng trắng và lá', 'hoa_sn_toa-nang.jpg'),
(18, 3, 'Red Rose', 45000, 'Hoa hồng đỏ và lá mă', 'hoa_sn_yeu-thuong.jpg'),
(19, 5, 'Vấn vương', 150000, 'Hoa hồng đỏ, hồng đậ', 'hoa_tuoi_bo_hanh-phuc.jpg'),
(20, 5, 'Nắng nhẹ nhàng', 50000, 'Hoa cúc xanh, hoa ly', 'hoa_tuoi_bo_ngay-chung-doi.jpg'),
(21, 5, 'Thanh tao', 120000, 'Hoa thủy tiên, cúa t', 'hoa_tuoi_bo_ngay-moi.jpg'),
(22, 5, 'Tinh khiết', 110000, 'Hồng trắng và salem\r', 'hoa_tuoi_bo_nong-am.jpg'),
(23, 5, 'Mùa xuân chín', 150000, 'Hồmg cam, cúc xanh, ', 'hoa_tuoi_bo_sac-thu.jpg'),
(24, 5, 'Rực rỡ nắng vàng', 75000, 'Hồng vàng và cúc vàn', 'hoa_tuoi_bo_toa-nang.jpg'),
(25, 3, 'Love Candy', 95000, 'Hoa hồng trắng tinh ', 'hh11.jpg'),
(26, 2, 'Happy Wedding', 210000, 'Hoa hồng môn và các ', 'hx2.jpg'),
(27, 1, 'Cúc nhiệt đới', 150000, 'Cúc vàng - hồng cam ', 'hx7.jpg'),
(33, 1, 'Hoa cúc', 123456, 'Hoa cúc vàng tự nhiên', 'hx10.jpg');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoadon`
--

CREATE TABLE `hoadon` (
  `MaHD` int(11) NOT NULL,
  `NgayDat` datetime NOT NULL,
  `NoiGiao` varchar(255) NOT NULL,
  `MaKH` int(11) NOT NULL,
  `TinhTrang` varchar(50) NOT NULL DEFAULT '0',
  `PhuongThucTT` varchar(50) NOT NULL DEFAULT 'COD',
  `LoiNhan` text DEFAULT NULL,
  `GioGiao` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `hoadon`
--

INSERT INTO `hoadon` (`MaHD`, `NgayDat`, `NoiGiao`, `MaKH`, `TinhTrang`) VALUES
(1, '2026-09-05 04:13:53', '280 An Dương Vương, P4, Q5 (Giao: 2026-09-06)', 1, '1'),
(2, '2026-09-05 04:40:27', '280 An Dương Vương, P4, Q5 (Giao: 2026-09-06)', 1, '0');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khachhang`
--

CREATE TABLE `khachhang` (
  `MaKH` int(11) NOT NULL,
  `TenDN` varchar(50) NOT NULL,
  `MatKhau` varchar(255) NOT NULL,
  `HoTen` varchar(100) NOT NULL,
  `DiaChi` varchar(255) DEFAULT NULL,
  `DienThoai` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Role` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `khachhang`
--

INSERT INTO `khachhang` (`MaKH`, `TenDN`, `MatKhau`, `HoTen`, `DiaChi`, `DienThoai`, `Email`, `Role`) VALUES
(1, 'admin', 'vanvinh', 'Quản trị Hệ thống', '280 An Dương Vương, P4, Q5', '0989366990', 'admin@hoadep.com', 1),
(2, 'hienlth', 'hienlth', 'Lương Trần Hy Hiến', '396 Dương Bá Trạc, Q8', '0989366990', 'hienlth@hcmup.edu.vn', 0),
(3, 'cuong', 'cuong', 'Chung Quốc Cường', '1bis Nguyễn Văn Trỗi Q.1', '0912345678', 'cqcuong@hcmuns.edu.vn', 0),
(4, 'tung', 'tung', 'Lưu Hải Tùng', '1 Mạc Đỉnh Chi Q.1', '0989766569', 'lhtung@yahoo.com', 0),
(5, 'dlthien', 'dlthien', 'Đỗ Lâm Thiên', '357 Lê Hồng Phong Q.10 ', '0903123456', 'dlthien@hcmuns.edu.vn', 0),
(6, 'thanh', 'thanh', 'Nguyễn Ngọc Thanh', '357 Lê Hồng Phong Q.10', '0903456789', 'lthanh@hcmuns.edu.vn', 0),
(9, 'trungdalat', '123456', 'Trung Đà Lạt', 'Phổ Quang, Tân Bình', '0902314340', 'trungvhh@gmail.com', 0),
(10, 'trunginfo', '123123', 'Trung Info', '2132 Lê Văn Sỹ', '0123213213', '123@yahoo.com', 0),
(12, 'qtr', '$2y$10$NOSk2EHmCc1jDlxyYY10tOwsHJ3tX2pfBl2WWK2HzbOt7MuNAQDuK', 'Quản trị viên QTR', 'Hệ thống Sen Garden', '0909999999', 'qtr@sengarden.vn', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `loaihoa`
--

CREATE TABLE `loaihoa` (
  `MaLoai` int(11) NOT NULL,
  `TenLoai` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `loaihoa`
--

INSERT INTO `loaihoa` (`MaLoai`, `TenLoai`) VALUES
(1, 'Hoa tình yêu'),
(2, 'Hoa cưới'),
(3, 'Hoa sinh nhật'),
(4, 'Hoa văn phòng'),
(5, 'Hoa tươi bó'),
(6, 'Hoa tươi giỏ'),
(7, 'Hoa khai trương');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `chitiethd`
--
ALTER TABLE `chitiethd`
  ADD PRIMARY KEY (`SoDH`),
  ADD KEY `maHoa` (`MaHoa`),
  ADD KEY `maHD` (`MaHD`);

--
-- Chỉ mục cho bảng `hoa`
--
ALTER TABLE `hoa`
  ADD PRIMARY KEY (`MaHoa`),
  ADD KEY `maLoai` (`MaLoai`);

--
-- Chỉ mục cho bảng `hoadon`
--
ALTER TABLE `hoadon`
  ADD PRIMARY KEY (`MaHD`),
  ADD KEY `maKH` (`MaKH`);

--
-- Chỉ mục cho bảng `khachhang`
--
ALTER TABLE `khachhang`
  ADD PRIMARY KEY (`MaKH`),
  ADD UNIQUE KEY `TenDN` (`TenDN`);

--
-- Chỉ mục cho bảng `loaihoa`
--
ALTER TABLE `loaihoa`
  ADD PRIMARY KEY (`MaLoai`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `chitiethd`
--
ALTER TABLE `chitiethd`
  MODIFY `SoDH` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `hoa`
--
ALTER TABLE `hoa`
  MODIFY `MaHoa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT cho bảng `hoadon`
--
ALTER TABLE `hoadon`
  MODIFY `MaHD` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `khachhang`
--
ALTER TABLE `khachhang`
  MODIFY `MaKH` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `loaihoa`
--
ALTER TABLE `loaihoa`
  MODIFY `MaLoai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `chitiethd`
--
ALTER TABLE `chitiethd`
  ADD CONSTRAINT `chitiethd_ibfk_1` FOREIGN KEY (`MaHoa`) REFERENCES `hoa` (`MaHoa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `chitiethd_ibfk_2` FOREIGN KEY (`MaHD`) REFERENCES `hoadon` (`MaHD`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `hoa`
--
ALTER TABLE `hoa`
  ADD CONSTRAINT `hoa_ibfk_1` FOREIGN KEY (`MaLoai`) REFERENCES `loaihoa` (`MaLoai`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `hoadon`
--
ALTER TABLE `hoadon`
  ADD CONSTRAINT `hoadon_ibfk_1` FOREIGN KEY (`MaKH`) REFERENCES `khachhang` (`MaKH`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Cấu trúc bảng cho bảng `yeuthich`
--
CREATE TABLE IF NOT EXISTS `yeuthich` (
  `MaYT` int(11) NOT NULL AUTO_INCREMENT,
  `MaKH` int(11) NOT NULL,
  `MaHoa` int(11) NOT NULL,
  `NgayThem` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`MaYT`),
  UNIQUE KEY `uk_kh_hoa` (`MaKH`, `MaHoa`),
  CONSTRAINT `fk_yt_kh` FOREIGN KEY (`MaKH`) REFERENCES `khachhang` (`MaKH`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_yt_hoa` FOREIGN KEY (`MaHoa`) REFERENCES `hoa` (`MaHoa`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Cấu trúc bảng cho bảng `danhgia`
--
CREATE TABLE IF NOT EXISTS `danhgia` (
  `MaDG` int(11) NOT NULL AUTO_INCREMENT,
  `MaHoa` int(11) NOT NULL,
  `MaKH` int(11) NOT NULL,
  `SoSao` int(11) NOT NULL,
  `NoiDung` text NOT NULL,
  `NgayDG` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`MaDG`),
  CONSTRAINT `fk_dg_kh` FOREIGN KEY (`MaKH`) REFERENCES `khachhang` (`MaKH`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_dg_hoa` FOREIGN KEY (`MaHoa`) REFERENCES `hoa` (`MaHoa`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lienhe`
--

CREATE TABLE IF NOT EXISTS `lienhe` (
  `MaLH` int(11) NOT NULL AUTO_INCREMENT,
  `HoTen` varchar(100) NOT NULL,
  `DienThoai` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `TieuDe` varchar(255) DEFAULT NULL,
  `NoiDung` text NOT NULL,
  `NgayGui` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`MaLH`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `qlchat`
--

CREATE TABLE IF NOT EXISTS `qlchat` (
  `MaChat` int(11) NOT NULL AUTO_INCREMENT,
  `MaKH` int(11) NOT NULL,
  `NguoiGui` varchar(20) NOT NULL,
  `NoiDung` text NOT NULL,
  `ThoiGian` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`MaChat`),
  KEY `MaKH` (`MaKH`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `voucher`
--

CREATE TABLE IF NOT EXISTS `voucher` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đổ dữ liệu mẫu cho bảng `voucher`
--
INSERT IGNORE INTO `voucher` (`MaVoucher`, `Code`, `MoTa`, `PhanTramGiam`, `SoTienGiam`, `DonToiThieu`, `NgayHetHan`, `LuotDung`, `TrangThai`) VALUES
(1, 'SENGARDEN', 'Ưu đãi tri ân giảm 10% toàn bộ đơn hàng', 10, 0, 0, '2026-12-31', 999, 1),
(2, 'HOATUOI2026', 'Giảm trực tiếp 50.000đ cho đơn hoa từ 350.000đ', 0, 50000, 350000, '2026-12-31', 500, 1),
(3, 'FREESHIP', 'Giảm 30.000đ phí vận chuyển cho đơn từ 200.000đ', 0, 30000, 200000, '2026-12-31', 500, 1);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
