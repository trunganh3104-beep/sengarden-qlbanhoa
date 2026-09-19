<?php

$currentPage = basename($_SERVER['PHP_SELF']);
$adminUser = function_exists('getCurrentUser') ? getCurrentUser() : ($_SESSION['user'] ?? null);
$adminDisplayName = !empty($adminUser['HoTen']) ? $adminUser['HoTen'] : (!empty($adminUser['TenDN']) ? $adminUser['TenDN'] : 'Admin');
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin.css?v=<?php echo time(); ?>">

<style>
.admin-navbar-wrapper {
    background: #0f172a;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    position: sticky;
    top: 0;
    z-index: 1000;
    width: 100%;
    box-sizing: border-box;
}

.admin-navbar-inner {
    max-width: 1360px;
    margin: 0 auto;
    padding: 0 20px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    box-sizing: border-box;
}

.admin-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: #ffffff;
    flex-shrink: 0;
}

.admin-brand-logo {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    background: linear-gradient(135deg, #e11d48, #be123c);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 16px;
    box-shadow: 0 4px 12px rgba(225, 29, 72, 0.35);
    flex-shrink: 0;
}

.admin-brand-info {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.admin-brand-name {
    font-size: 14.5px;
    font-weight: 800;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: #ffffff;
    line-height: 1.2;
    margin: 0;
}

.admin-brand-status {
    font-size: 10.5px;
    color: #94a3b8;
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 2px;
}

.admin-brand-status .dot-live {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 8px #10b981;
}

.admin-nav-menu {
    display: flex;
    align-items: center;
    gap: 3px;
    list-style: none;
    margin: 0;
    padding: 0;
    flex-shrink: 1;
}

.admin-nav-item {
    color: #94a3b8;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    padding: 7px 11px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.18s ease;
    white-space: nowrap;
}

.admin-nav-item:hover {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.08);
}

.admin-nav-item.active {
    color: #ffffff;
    background: linear-gradient(135deg, #e11d48, #be123c);
    font-weight: 600;
    box-shadow: 0 2px 10px rgba(225, 29, 72, 0.35);
}

.admin-nav-item.chat-btn {
    color: #38bdf8;
    position: relative;
}

.admin-nav-item.chat-btn:hover {
    color: #ffffff;
    background: rgba(56, 189, 248, 0.15);
}

.admin-nav-item.chat-btn.active {
    background: linear-gradient(135deg, #0284c7, #0369a1);
    color: #ffffff;
    box-shadow: 0 2px 10px rgba(2, 132, 199, 0.35);
}

.admin-nav-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}

.admin-shop-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 11px;
    font-size: 12.5px;
    font-weight: 600;
    color: #fbbf24 !important;
    background: rgba(245, 158, 11, 0.12);
    border: 1px solid rgba(245, 158, 11, 0.25);
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.admin-shop-link:hover {
    background: #f59e0b;
    color: #0f172a !important;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
}

.admin-user-pill {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #e2e8f0;
    font-size: 13px;
    font-weight: 500;
    padding-left: 10px;
    border-left: 1px solid rgba(255, 255, 255, 0.12);
    flex-shrink: 0;
}

.admin-user-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #fff;
    font-size: 12.5px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.admin-user-name {
    font-weight: 600;
    color: #f1f5f9;
    max-width: 95px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: inline-block;
}

.admin-logout-btn {
    color: #ef4444;
    text-decoration: none;
    font-size: 14px;
    padding: 5px 8px;
    border-radius: 6px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.admin-logout-btn:hover {
    background: rgba(239, 68, 68, 0.15);
    color: #f87171;
}

@media (max-width: 1200px) {
    .admin-brand-status {
        display: none;
    }
    .admin-nav-item {
        padding: 6px 9px;
        font-size: 12.5px;
        gap: 5px;
    }
    .admin-shop-link span {
        display: none;
    }
    .admin-shop-link {
        padding: 6px 9px;
    }
}

@media (max-width: 980px) {
    .admin-navbar-inner {
        height: auto;
        padding: 10px 16px;
        flex-wrap: wrap;
        gap: 8px;
    }
    .admin-nav-menu {
        order: 3;
        width: 100%;
        overflow-x: auto;
        padding: 8px 0 2px 0;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        scrollbar-width: none;
    }
    .admin-nav-menu::-webkit-scrollbar {
        display: none;
    }
}
</style>

<nav class="admin-navbar-wrapper">
    <div class="admin-navbar-inner">
        
        <a href="index.php" class="admin-brand">
            <div class="admin-brand-logo">
                <i class="fa-solid fa-spa"></i>
            </div>
            <div class="admin-brand-info">
                <span class="admin-brand-name">Sen Garden</span>
                <span class="admin-brand-status">
                    <span class="dot-live"></span> Quản trị
                </span>
            </div>
        </a>

        
        <div class="admin-nav-menu">
            <a href="index.php" class="admin-nav-item <?php echo ($currentPage === 'index.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-gauge-high"></i> <span>Dashboard</span>
            </a>
            <a href="quanlyhoa.php" class="admin-nav-item <?php echo in_array($currentPage, ['quanlyhoa.php', 'themhoa.php', 'suahoa.php']) ? 'active' : ''; ?>">
                <i class="fa-solid fa-fan"></i> <span>Hoa tươi</span>
            </a>
            <a href="quanlydanhmuc.php" class="admin-nav-item <?php echo ($currentPage === 'quanlydanhmuc.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-tags"></i> <span>Loại hoa</span>
            </a>
            <a href="quanlydonhang.php" class="admin-nav-item <?php echo in_array($currentPage, ['quanlydonhang.php', 'inhoadon.php']) ? 'active' : ''; ?>">
                <i class="fa-solid fa-file-invoice-dollar"></i> <span>Đơn hàng</span>
            </a>
            <a href="quanlyvoucher.php" class="admin-nav-item <?php echo ($currentPage === 'quanlyvoucher.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-ticket"></i> <span>Voucher</span>
            </a>
            <a href="quanlylienhe.php" class="admin-nav-item <?php echo ($currentPage === 'quanlylienhe.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-envelope"></i> <span>Liên hệ</span>
            </a>
            <a href="quanlyuser.php" class="admin-nav-item <?php echo ($currentPage === 'quanlyuser.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-users"></i> <span>Khách hàng</span>
            </a>
            <a href="cskh.php" class="admin-nav-item chat-btn <?php echo ($currentPage === 'cskh.php' || $currentPage === 'quanlychat.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-comments"></i> <span>CSKH</span>
            </a>
        </div>

        
        <div class="admin-nav-right">
            <a href="../index.php" class="admin-shop-link" title="Xem website cửa hàng">
                <i class="fa-solid fa-store"></i> <span>Xem Shop</span>
            </a>

            <div class="admin-user-pill">
                <div class="admin-user-avatar" title="<?php echo htmlspecialchars($adminDisplayName); ?>">
                    <?php echo mb_substr($adminDisplayName, 0, 1, 'UTF-8'); ?>
                </div>
                <span class="admin-user-name" title="<?php echo htmlspecialchars($adminDisplayName); ?>">
                    <?php echo htmlspecialchars($adminDisplayName); ?>
                </span>
                <a href="../dangxuat.php" class="admin-logout-btn" title="Đăng xuất khỏi hệ thống">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </div>
</nav>

<div id="admin-toast-container" class="admin-toast-box"></div>

<script>
window.showAdminToast = function (message, type = 'success', duration = 3500) {
    const container = document.getElementById('admin-toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `admin-toast ${type}`;
    const icon = type === 'success' ? 'fa-circle-check' : (type === 'error' ? 'fa-circle-exclamation' : 'fa-circle-info');
    const iconColor = type === 'success' ? '#10b981' : (type === 'error' ? '#ef4444' : '#6366f1');

    toast.innerHTML = `
        <i class="fa-solid ${icon}" style="color: ${iconColor}; font-size: 18px;"></i>
        <div style="flex: 1; line-height: 1.4;">${message}</div>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, duration);
};
</script>