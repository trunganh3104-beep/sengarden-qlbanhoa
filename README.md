# 🌸 SEN GARDEN - WEBSITE BÁN HOA TƯƠI TRỰC TUYẾN

> **Đồ án môn học:** Thiết Kế và Phát Triển Ứng Dụng Web  
> **Trường:** Đại học Nguyễn Tất Thành (NTTU) - Viện Đào tạo Quốc tế NTT  
> **Giảng viên hướng dẫn:** ThS. Phan Thị Nam Anh  
> **Nhóm sinh viên thực hiện:**
> * **Võ Công Thành** – MSSV: `2400003782`
> * **Võ Văn Vinh** – MSSV: `2400002280`

---

## 🌐 THÔNG TIN TRIỂN KHAI TRỰC TUYẾN
* **Link Website chính thức (Hosting):** [https://sengarden.ct.ws/index.php](https://sengarden.ct.ws/index.php)
* **Tài khoản trải nghiệm hệ thống:**
  * 👤 **Khách hàng (Client):** `hienlth` | Mật khẩu: `hienlth` (hoặc `cuong` / `cuong`)
  * 👑 **Quản trị viên (Admin):** `qtr` | Mật khẩu: `123` (hoặc `admin` / `vanvinh`)

---

## 🚀 CÔNG NGHỆ SỬ DỤNG
* **Backend:** PHP 8.x (Kiến trúc Module hóa, bảo mật mật khẩu BCRYPT một chiều qua `password_hash`).
* **Database:** MariaDB / MySQL (InnoDB Engine, chuẩn hóa 3NF, gồm 12 bảng thực tế với ràng buộc toàn vẹn `FOREIGN KEY ... ON DELETE CASCADE`).
* **Frontend:** HTML5 ngữ nghĩa, CSS3 (Flexbox & CSS Grid Responsive trên Desktop/Tablet/Mobile), JavaScript (ES6+), Font Awesome 6.5.1.
* **Tương tác bất đồng bộ:** AJAX (Kiểm tra trùng lặp đăng ký thời gian thực, Live Chat tư vấn CSKH, Bật/Tắt Voucher).
* **Thư viện & Dịch vụ tích hợp:**
  * **VietQR API:** Tự động sinh mã QR chuyển khoản chuẩn NAPAS247 kèm chính xác số tiền và mã đơn hàng.
  * **PHPMailer (SMTP):** Gửi email hóa đơn xác nhận đặt hàng tự động về hòm thư người mua.
  * **Chart.js:** Trực quan hóa dữ liệu thống kê doanh số (Biểu đồ đường 7 ngày & Biểu đồ tròn cơ cấu danh mục).

---

## 💎 CÁC TÍNH NĂNG NỔI BẬT ĐẠT ĐIỂM CỘNG KỸ THUẬT CAO
1. **Cơ chế Gộp & Lưu giỏ hàng bền vững (Hybrid Cart):**
   * Khách vãng lai dùng `$_SESSION['MyCart']`.
   * Khi đăng nhập, hệ thống tự động chạy thuật toán **Merge Cart** (`loadUserCartFromDb()` và `syncUserCartToDb()`), gộp các sản phẩm trong Session với giỏ hàng cũ lưu trong bảng `giohang_user` ở Database, đảm bảo không bao giờ mất giỏ hàng khi đổi thiết bị.
2. **Hẹn ngày & Khung giờ giao hoa + In thiệp mừng tặng kèm:**
   * Cho phép khách hàng chọn chính xác ngày giao (ràng buộc không chọn ngày quá khứ) và khung giờ thuận tiện (Sáng 8h-11h, Chiều 14h-17h, Giao càng sớm càng tốt).
   * Thu thập nội dung lời chúc tình cảm để in thiệp tặng kèm bó hoa.
3. **Cơ chế Tự Động Di Trú & Phục Hồi Dữ Liệu (Self-healing Migration):**
   * Hàm `ensureRequiredTables()` trong `includes/db_config.php` tự động kiểm tra và tạo mới các bảng thiếu (`lienhe`, `qlchat`, `voucher`, `truycap`, `giohang_user`), tự động thêm các cột nâng cấp (`DonGia`, `NgayGiao`, `MaVoucher`, `SoTienGiam`) khi deploy sang máy chủ mới.
4. **Bộ đếm lưu lượng truy cập thông minh:**
   * Tự động nhận diện IP, có cửa sổ trượt 15 phút lọc truy cập trùng (chống F5 ảo).
   * Hiển thị 3 chỉ số KPI: Tổng tích lũy, Hôm nay và Số khách đang Online thời gian thực.
5. **Chiến dịch Flash Sale Marketing kích cầu:**
   * Popup chào mừng khách mới tặng mã ưu đãi, Đồng hồ đếm ngược FOMO và Nút hộp quà nổi rung lắc.

---

## 🗄️ CẤU TRÚC 12 BẢNG CƠ SỞ DỮ LIỆU
| STT | Tên bảng | Khóa chính (PK) | Ý nghĩa nghiệp vụ |
| :---: | :--- | :---: | :--- |
| 1 | `khachhang` | `MaKH` | Tài khoản thành viên và phân quyền Quản trị viên (Role: 0/1) |
| 2 | `loaihoa` | `MaLoai` | Danh mục phân loại các chủ đề hoa tươi |
| 3 | `hoa` | `MaHoa` | Thông tin chi tiết mẫu hoa, đơn giá niêm yết, hình ảnh |
| 4 | `hoadon` | `MaHD` | Đơn hàng, ngày giờ giao, lời chúc thiệp mừng, phương thức TT |
| 5 | `chitiethd` | `SoDH` | Chi tiết từng bó hoa trong đơn, **chốt cứng đơn giá thực tế** |
| 6 | `voucher` | `MaVoucher` | Kho mã giảm giá (% hoặc VNĐ), điều kiện đơn tối thiểu |
| 7 | `giohang_user` | `(MaKH, MaHoa)` | Giỏ hàng lưu trữ bền vững trong CSDL theo tài khoản |
| 8 | `yeuthich` | `MaYT` | Danh sách mẫu hoa yêu thích (Wishlist) của khách hàng |
| 9 | `danhgia` | `MaDG` | Bình luận nhận xét và chấm điểm sao (1 - 5 sao) |
| 10 | `qlchat` | `MaChat` | Lịch sử tin nhắn tư vấn trực tuyến giữa shop và khách |
| 11 | `lienhe` | `MaLH` | Hòm thư góp ý, phản hồi liên hệ của khách hàng |
| 12 | `truycap` | `id` | Nhật ký địa chỉ IP và mốc thời gian truy cập |

---

## 🛠️ HƯỚNG DẪN CÀI ĐẶT CỤC BỘ (LOCALHOST)
1. Cài đặt **XAMPP** (hỗ trợ PHP 8.x và MariaDB).
2. Sao chép toàn bộ thư mục mã nguồn vào đường dẫn: `C:\xampp\htdocs\qlbanhoa`.
3. Mở **XAMPP Control Panel**, nhấn **Start** cả 2 dịch vụ **Apache** và **MySQL**.
4. Truy cập trình duyệt theo địa chỉ: `http://localhost/phpmyadmin/`. Tạo cơ sở dữ liệu tên: `qlbanhoa`.
5. Nhấn tab **Import**, chọn file `qlbanhoa.sql` trong thư mục gốc và nhấn **Import**.
6. Mở trình duyệt và trải nghiệm website tại: `http://localhost/qlbanhoa/`.

---
*© 2026 Sen Garden Team - Trường Đại học Nguyễn Tất Thành.*
