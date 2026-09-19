<?php
ob_start();
require_once '../includes/auth.php';
requireAdmin();
include_once '../DataProvider.php';

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
mysqli_set_charset($conn, "utf8mb4");
if (function_exists('ensureRequiredTables')) ensureRequiredTables($conn);

$maKH_chon = intval($_GET['makh'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnGoiTin'], $_POST['MaKH'])) {
    $khID = intval($_POST['MaKH']);
    $noiDungAdmin = trim($_POST['NoiDungAdmin'] ?? '');
    if (!empty($noiDungAdmin) && $khID > 0) {
        $ndEsc = mysqli_real_escape_string($conn, $noiDungAdmin);
        mysqli_query($conn, "INSERT INTO qlchat (MaKH, NguoiGui, NoiDung) VALUES ($khID, 'admin', '$ndEsc')");
        header("Location: cskh.php?makh=$khID");
        echo "<script>window.location.href='cskh.php?makh=$khID';</script>";
        exit;
    }
}

$sqlKhach = "SELECT k.MaKH, k.HoTen, k.TenDN, k.DienThoai, MAX(q.MaChat) AS MaxChat,
                    (SELECT NoiDung FROM qlchat WHERE MaChat = MAX(q.MaChat)) AS LastMsg,
                    (SELECT NguoiGui FROM qlchat WHERE MaChat = MAX(q.MaChat)) AS LastSender
             FROM qlchat q 
             JOIN khachhang k ON q.MaKH = k.MaKH 
             GROUP BY k.MaKH, k.HoTen, k.TenDN, k.DienThoai
             ORDER BY MaxChat DESC";
$dsKhach = mysqli_query($conn, $sqlKhach);

if ($maKH_chon <= 0 && $dsKhach && mysqli_num_rows($dsKhach) > 0) {
    $firstRow = mysqli_fetch_assoc($dsKhach);
    $maKH_chon = intval($firstRow['MaKH']);
    mysqli_data_seek($dsKhach, 0); 
}

require_once '../includes/header.php';
include_once 'nav.php';
?>

<div class="admin-container">
    <div class="admin-page-header" style="margin-bottom: 18px;">
        <div class="header-left">
            <h2>
                <i class="fa-solid fa-headset" style="color: #0284c7;"></i> Trung Tâm CSKH & Chat Trực Tuyến
            </h2>
            <p>Hỗ trợ giải đáp thắc mắc, tư vấn chọn hoa và theo dõi phản hồi của khách hàng theo thời gian thực</p>
        </div>
    </div>

    
    <div class="admin-card" style="height: 680px; display: flex; flex-direction: row; overflow: hidden; margin-bottom: 20px; box-shadow: var(--adm-shadow-lg);">
        
        
        <div style="width: 340px; border-right: 1px solid var(--adm-border); display: flex; flex-direction: column; background: #ffffff; flex-shrink: 0;">
            
            <div style="padding: 16px; border-bottom: 1px solid var(--adm-border); background: #f8fafc;">
                <div style="font-weight: 700; font-size: 15px; color: #0f172a; display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <span>Hội thoại khách hàng</span>
                    <span class="badge-pill info" style="font-size: 11px;">Trực tuyến</span>
                </div>
                
                <div style="position: relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px;"></i>
                    <input type="text" id="search-customer-chat" placeholder="Tìm tên hoặc số điện thoại..." class="admin-form-control" style="padding: 7px 12px 7px 32px; font-size: 13px; background: #fff;">
                </div>
            </div>

            
            <div style="flex: 1; overflow-y: auto;" id="customer-list-box">
                <?php if ($dsKhach && mysqli_num_rows($dsKhach) > 0): ?>
                    <?php while ($kh = mysqli_fetch_assoc($dsKhach)): 
                        $isSelected = ($maKH_chon == $kh['MaKH']);
                        $displayName = $kh['HoTen'] ?: $kh['TenDN'];
                        $firstChar = mb_substr($displayName, 0, 1, 'UTF-8');
                    ?>
                        <a href="cskh.php?makh=<?php echo $kh['MaKH']; ?>" 
                           class="customer-chat-item" 
                           data-name="<?php echo htmlspecialchars(mb_strtolower($displayName)); ?>"
                           data-phone="<?php echo htmlspecialchars($kh['DienThoai'] ?? ''); ?>"
                           style="display: flex; gap: 12px; align-items: center; padding: 14px 16px; border-bottom: 1px solid #f1f5f9; text-decoration: none; transition: background 0.15s ease; <?php echo $isSelected ? 'background: #f0f9ff; border-left: 4px solid #0284c7;' : 'background: #ffffff;'; ?>">
                            
                            
                            <div style="position: relative; flex-shrink: 0;">
                                <div class="user-avatar-circle" style="<?php echo $isSelected ? 'background: linear-gradient(135deg, #0284c7, #0369a1);' : 'background: #64748b;'; ?>">
                                    <?php echo htmlspecialchars($firstChar); ?>
                                </div>
                                <span class="status-dot" style="position: absolute; bottom: 0; right: 0; background: #10b981; border: 2px solid #fff;"></span>
                            </div>

                            
                            <div style="flex: 1; min-width: 0;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div style="font-weight: 700; font-size: 14px; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo htmlspecialchars($displayName); ?>
                                    </div>
                                    <span style="font-size: 11px; color: #94a3b8;">#<?php echo $kh['MaKH']; ?></span>
                                </div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                                    <i class="fa-solid fa-phone" style="font-size: 10px; color: #10b981;"></i> <?php echo htmlspecialchars($kh['DienThoai'] ?: 'Chưa có SĐT'); ?>
                                </div>
                                <div style="font-size: 12px; color: #94a3b8; margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?php if ($kh['LastSender'] === 'admin'): ?>
                                        <span style="color: #0284c7; font-weight: 600;">Bạn: </span>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars(mb_strimwidth($kh['LastMsg'] ?? '', 0, 32, '...')); ?>
                                </div>
                            </div>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="padding: 40px 20px; text-align: center; color: #94a3b8; font-size: 13px;">
                        <i class="fa-regular fa-comment-dots" style="font-size: 32px; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                        Chưa có cuộc hội thoại nào.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div style="flex: 1; display: flex; flex-direction: column; background: #f8fafc; min-width: 0;">
            <?php if ($maKH_chon > 0): ?>
                <?php 
                    $rsInfo = mysqli_query($conn, "SELECT HoTen, TenDN, DienThoai, Email, DiaChi FROM khachhang WHERE MaKH = $maKH_chon");
                    $infoKH = mysqli_fetch_assoc($rsInfo);
                    $tenKH = $infoKH['HoTen'] ?: $infoKH['TenDN'];
                    $tinNhan = mysqli_query($conn, "SELECT * FROM qlchat WHERE MaKH = $maKH_chon ORDER BY MaChat ASC");
                ?>
                
                <div style="padding: 14px 20px; background: #ffffff; border-bottom: 1px solid var(--adm-border); display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div class="user-avatar-circle" style="background: linear-gradient(135deg, #0284c7, #0369a1); width: 42px; height: 42px; font-size: 16px;">
                            <?php echo mb_substr($tenKH, 0, 1, 'UTF-8'); ?>
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 16px; color: #0f172a; line-height: 1.2;">
                                <?php echo htmlspecialchars($tenKH); ?>
                            </div>
                            <div style="font-size: 12.5px; color: #64748b; margin-top: 2px;">
                                <span>SĐT: <strong><?php echo htmlspecialchars($infoKH['DienThoai'] ?: 'Chưa cập nhật'); ?></strong></span>
                                <?php if (!empty($infoKH['DiaChi'])): ?>
                                    <span style="margin-left: 8px; color: #94a3b8;">• <?php echo htmlspecialchars(mb_strimwidth($infoKH['DiaChi'], 0, 30, '...')); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <a href="quanlydonhang.php" class="admin-btn admin-btn-outline admin-btn-sm" title="Kiểm tra đơn hàng của khách">
                            <i class="fa-solid fa-receipt"></i> Đơn hàng
                        </a>
                        <?php if (!empty($infoKH['DienThoai'])): ?>
                            <a href="tel:<?php echo htmlspecialchars($infoKH['DienThoai']); ?>" class="admin-btn admin-btn-outline admin-btn-sm" style="color: #059669 !important;">
                                <i class="fa-solid fa-phone"></i> Gọi điện
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div id="adminChatContainer" style="flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 14px; background: #f8fafc;">
                    <div style="text-align: center; margin-bottom: 8px;">
                        <span style="background: #e2e8f0; color: #64748b; font-size: 11.5px; font-weight: 600; padding: 4px 12px; border-radius: 999px;">
                            Bắt đầu cuộc trò chuyện với khách hàng
                        </span>
                    </div>

                    <?php if ($tinNhan && mysqli_num_rows($tinNhan) > 0): ?>
                        <?php while ($msg = mysqli_fetch_assoc($tinNhan)): 
                            $isAdmin = ($msg['NguoiGui'] === 'admin');
                        ?>
                            <div style="display: flex; flex-direction: column; max-width: 68%; <?php echo $isAdmin ? 'align-self: flex-end; align-items: flex-end;' : 'align-self: flex-start; align-items: flex-start;'; ?>">
                                <div style="padding: 12px 16px; border-radius: 14px; font-size: 14px; line-height: 1.5; box-shadow: 0 1px 3px rgba(0,0,0,0.06); <?php echo $isAdmin ? 'background: linear-gradient(135deg, #e11d48, #be123c); color: #ffffff; border-bottom-right-radius: 3px;' : 'background: #ffffff; color: #0f172a; border: 1px solid #e2e8f0; border-bottom-left-radius: 3px;'; ?>">
                                    <?php echo nl2br(htmlspecialchars($msg['NoiDung'])); ?>
                                </div>
                                <div style="font-size: 11px; color: #94a3b8; margin-top: 4px; padding: 0 4px;">
                                    <?php echo $isAdmin ? '<span style="color: #e11d48; font-weight: 600;">Bạn (Admin)</span> • ' : ''; ?>
                                    <?php echo $msg['ThoiGian']; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align: center; color: #94a3b8; font-size: 13px; margin: auto;">
                            Khách hàng này chưa gửi tin nhắn nào gần đây. Bạn có thể chủ động chào hỏi!
                        </div>
                    <?php endif; ?>
                </div>

                
                <div style="padding: 8px 16px; background: #ffffff; border-top: 1px solid #f1f5f9; display: flex; gap: 8px; overflow-x: auto; white-space: nowrap;">
                    <span style="font-size: 12px; color: #94a3b8; align-self: center; font-weight: 600;">Mẫu nhanh:</span>
                    <button type="button" class="btn-quick-reply badge-pill slate" style="cursor: pointer; border: 1px solid #cbd5e1;" data-text="Dạ shop Sen Garden xin chào bạn, shop có thể hỗ trợ gì cho bạn ạ?">
                        👋 Chào hỏi
                    </button>
                    <button type="button" class="btn-quick-reply badge-pill slate" style="cursor: pointer; border: 1px solid #cbd5e1;" data-text="Dạ đơn hàng của bạn đã được đóng gói cẩn thận và đang giao đến bạn nhé!">
                        📦 Báo đang giao hàng
                    </button>
                    <button type="button" class="btn-quick-reply badge-pill slate" style="cursor: pointer; border: 1px solid #cbd5e1;" data-text="Dạ shop đã xác nhận thông tin chuyển khoản VietQR của bạn thành công rồi ạ!">
                        💳 Xác nhận VietQR
                    </button>
                    <button type="button" class="btn-quick-reply badge-pill slate" style="cursor: pointer; border: 1px solid #cbd5e1;" data-text="Dạ shop chân thành cảm ơn bạn đã luôn tin tưởng và ủng hộ Sen Garden ạ! 🌸">
                        💐 Lời cảm ơn
                    </button>
                </div>

                
                <form method="post" id="chat-form" style="padding: 14px 20px; background: #ffffff; border-top: 1px solid var(--adm-border); display: flex; gap: 12px; align-items: center;">
                    <input type="hidden" name="MaKH" value="<?php echo $maKH_chon; ?>">
                    <input type="text" 
                           id="input-admin-msg" 
                           name="NoiDungAdmin" 
                           placeholder="Nhập nội dung phản hồi khách hàng... (Nhấn Enter để gửi)" 
                           class="admin-form-control" 
                           style="flex: 1; padding: 11px 16px; font-size: 14px; background: #f8fafc;" 
                           required 
                           autocomplete="off" 
                           autofocus>
                    <button type="submit" name="btnGoiTin" class="admin-btn admin-btn-primary" style="padding: 11px 22px;">
                        <i class="fa-solid fa-paper-plane"></i> Gửi
                    </button>
                </form>
            <?php else: ?>
                <div style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #94a3b8; text-align: center; padding: 20px;">
                    <div style="width: 70px; height: 70px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 30px; color: #64748b; margin-bottom: 16px;">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <h3 style="margin: 0 0 6px 0; color: #0f172a; font-size: 18px;">Chọn khách hàng để xem tin nhắn</h3>
                    <p style="margin: 0; font-size: 13.5px; max-width: 320px;">Nhấp vào bất kỳ khách hàng nào ở cột bên trái để bắt đầu hỗ trợ tư vấn và trả lời câu hỏi.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('adminChatContainer');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }

    const inputMsg = document.getElementById('input-admin-msg');
    document.querySelectorAll('.btn-quick-reply').forEach(btn => {
        btn.addEventListener('click', function () {
            const text = this.getAttribute('data-text');
            if (inputMsg) {
                inputMsg.value = text;
                inputMsg.focus();
            }
        });
    });

    const searchCustInput = document.getElementById('search-customer-chat');
    if (searchCustInput) {
        searchCustInput.addEventListener('input', function () {
            const kw = this.value.trim().toLowerCase();
            document.querySelectorAll('.customer-chat-item').forEach(item => {
                const name = item.getAttribute('data-name') || '';
                const phone = item.getAttribute('data-phone') || '';
                if (!kw || name.includes(kw) || phone.includes(kw)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
});
</script>

<?php 
mysqli_close($conn);
require_once '../includes/footer.php'; 
?>