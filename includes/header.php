<?php
require_once __DIR__ . '/auth.php';

$in_admin = (basename(dirname($_SERVER['PHP_SELF'])) === 'admin');
$base_url = $in_admin ? '../' : '';

$cart_count = 0;
if (isset($_SESSION['MyCart']) && is_array($_SESSION['MyCart'])) {
    foreach ($_SESSION['MyCart'] as $qty) {
        $cart_count += intval($qty);
    }
}

$drawer_categories = [];
if (!$in_admin) {
    $drawer_conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($drawer_conn) {
        mysqli_set_charset($drawer_conn, "utf8");
        $drawer_res = @mysqli_query($drawer_conn, "SELECT MaLoai, TenLoai FROM loaihoa ORDER BY MaLoai ASC");
        if ($drawer_res) {
            while ($drawer_row = mysqli_fetch_assoc($drawer_res)) {
                $drawer_categories[] = $drawer_row;
            }
        }
        mysqli_close($drawer_conn);
    }
}

if (!function_exists('getDrawerCatIcon')) {
    function getDrawerCatIcon($name) {
        $t = mb_strtolower($name ?? '', 'UTF-8');
        if (strpos($t, 'cưới') !== false) return 'fa-heart';
        if (strpos($t, 'khai trương') !== false) return 'fa-ribbon';
        if (strpos($t, 'sinh nhật') !== false) return 'fa-cake-candles';
        if (strpos($t, 'tình yêu') !== false) return 'fa-heart-pulse';
        if (strpos($t, 'tốt nghiệp') !== false) return 'fa-graduation-cap';
        if (strpos($t, 'chia buồn') !== false || strpos($t, 'kính viếng') !== false) return 'fa-dove';
        if (strpos($t, 'lan') !== false || strpos($t, 'hồ điệp') !== false) return 'fa-fan';
        if (strpos($t, 'hộp') !== false || strpos($t, 'mica') !== false) return 'fa-box-open';
        if (strpos($t, 'bó') !== false) return 'fa-seedling';
        if (strpos($t, 'giỏ') !== false) return 'fa-gift';
        if (strpos($t, 'văn phòng') !== false) return 'fa-building';
        return 'fa-spa';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Hoa Tươi Sen Garden</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
    <link href="<?php echo $base_url; ?>css/style.css?v=<?php echo time(); ?>" rel="stylesheet" />
    <link href="<?php echo $base_url; ?>css/animations.css?v=<?php echo time(); ?>" rel="stylesheet" />
    <script src="<?php echo $base_url; ?>js/jquery-3.3.1.min.js"></script>
    <script src="<?php echo $base_url; ?>js/animations.js?v=<?php echo time(); ?>" defer></script>
    <style>
        .site-header {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px) saturate(180%) !important;
            -webkit-backdrop-filter: blur(20px) saturate(180%) !important;
            border-bottom: 1px solid rgba(254, 205, 211, 0.5) !important;
            box-shadow: 0 4px 20px -2px rgba(225, 29, 72, 0.05), 0 2px 6px rgba(0, 0, 0, 0.02) !important;
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .site-header .container {
            max-width: 1280px !important;
            width: 100% !important;
            padding: 0 15px !important;
            margin: 0 auto !important;
        }

        .header-inner {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            height: 72px !important;
            min-height: 72px !important;
            gap: 10px !important;
        }

        .logo {
            flex-shrink: 0 !important;
        }

        .logo-link {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .logo-link:hover {
            transform: translateY(-1px);
        }
        .logo-icon-badge {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
            border: 1px solid rgba(254, 205, 211, 0.8);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #e11d48;
            font-size: 19px;
            box-shadow: 0 4px 12px rgba(225, 29, 72, 0.12);
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .logo-link:hover .logo-icon-badge {
            transform: rotate(12deg) scale(1.06);
            background: linear-gradient(135deg, #ffe4e6 0%, #fecdd3 100%);
            box-shadow: 0 6px 16px rgba(225, 29, 72, 0.2);
        }
        .logo-text {
            font-size: 21px;
            font-weight: 500;
            color: #334155;
            letter-spacing: -0.3px;
        }
        .logo-text strong {
            font-weight: 800;
            background: linear-gradient(135deg, #e11d48 0%, #db2777 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .main-nav {
            display: flex !important;
            align-items: center !important;
            flex-shrink: 0 !important;
        }

        .main-nav > ul {
            display: flex !important;
            align-items: center !important;
            gap: 4px !important;
            flex-wrap: nowrap !important;
            margin: 0 !important;
            padding: 0 !important;
            list-style: none !important;
        }

        .main-nav > ul > li {
            list-style: none !important;
            margin: 0 !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
        }

        .main-nav > ul > li > a {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            padding: 6px 11px !important;
            border-radius: 999px !important;
            font-size: 13.5px !important;
            font-weight: 500 !important;
            color: #475569 !important;
            background: transparent !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
            text-decoration: none !important;
            white-space: nowrap !important;
            line-height: 1.4 !important;
            position: relative !important;
            user-select: none !important;
        }
        .main-nav > ul > li > a:hover {
            background: #f8fafc;
            color: #0f172a;
            transform: translateY(-1px);
        }
        .main-nav .nav-link-home i { color: #3b82f6; transition: transform 0.2s ease; }
        .main-nav .nav-link-home:hover {
            background: rgba(59, 130, 246, 0.08);
            color: #2563eb;
        }
        .main-nav .nav-link-home:hover i { transform: scale(1.15); }

        .main-nav .nav-link-about i { color: #10b981; transition: transform 0.2s ease; }
        .main-nav .nav-link-about:hover {
            background: rgba(16, 185, 129, 0.08) !important;
            color: #059669 !important;
        }
        .main-nav .nav-link-about:hover i { transform: scale(1.15); }

        .main-nav .nav-link-contact i { color: #f59e0b; transition: transform 0.2s ease; }
        .main-nav .nav-link-contact:hover {
            background: rgba(245, 158, 11, 0.08) !important;
            color: #d97706 !important;
        }
        .main-nav .nav-link-contact:hover i { transform: scale(1.15); }

        .main-nav .nav-link-wishlist i { color: #f43f5e; transition: transform 0.2s ease; }
        .main-nav .nav-link-wishlist:hover {
            background: rgba(244, 63, 94, 0.08);
            color: #e11d48;
        }
        .main-nav .nav-link-wishlist:hover i { transform: scale(1.2); }

        .main-nav .nav-link-login i { color: #10b981; transition: transform 0.2s ease; }
        .main-nav .nav-link-login:hover {
            background: rgba(16, 185, 129, 0.08);
            color: #059669;
        }
        .main-nav .nav-link-reg i { color: #8b5cf6; transition: transform 0.2s ease; }
        .main-nav .nav-link-reg:hover {
            background: rgba(139, 92, 246, 0.08);
            color: #7c3aed;
        }
        
        
        .nav-cart-btn {
            display: inline-flex !important;
            align-items: center !important;
            gap: 7px !important;
            padding: 6px 13px !important;
            border-radius: 999px !important;
            background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%) !important;
            color: #e11d48 !important;
            font-weight: 600 !important;
            font-size: 13.5px !important;
            border: 1px solid rgba(254, 205, 211, 0.8) !important;
            box-shadow: 0 2px 8px rgba(225, 29, 72, 0.08) !important;
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
            user-select: none !important;
            text-decoration: none !important;
            white-space: nowrap !important;
            line-height: 1.4 !important;
        }
        .nav-cart-btn:hover {
            background: linear-gradient(135deg, #ffe4e6 0%, #fecdd3 100%) !important;
            box-shadow: 0 4px 14px rgba(225, 29, 72, 0.18) !important;
            transform: translateY(-1px) !important;
            color: #be123c !important;
        }
        .cart-icon-wrap {
            position: relative;
            display: inline-flex;
            align-items: center;
            font-size: 14px;
        }
        .cart-badge {
            background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 1px 7px;
            border-radius: 999px;
            min-width: 18px;
            text-align: center;
            line-height: 15px;
            box-shadow: 0 2px 6px rgba(225, 29, 72, 0.35);
        }

        .nav-item-dropdown {
            position: relative;
        }
        .user-dropdown-toggle {
            display: inline-flex !important;
            align-items: center !important;
            gap: 7px !important;
            padding: 5px 12px !important;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%) !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 999px !important;
            cursor: pointer !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03) !important;
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
            user-select: none !important;
            white-space: nowrap !important;
            line-height: 1.4 !important;
        }
        .user-dropdown-toggle:hover {
            background: #ffffff !important;
            border-color: #cbd5e1 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06) !important;
            transform: translateY(-1px) !important;
        }
        .user-avatar-badge {
            color: #f43f5e;
            font-size: 17px;
            display: flex;
            align-items: center;
        }
        .user-name-text {
            max-width: 120px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
            font-weight: 600;
            font-size: 13px;
            color: #334155;
            vertical-align: middle;
        }
        .dropdown-arrow {
            font-size: 10px;
            color: #94a3b8;
            transition: transform 0.25s ease;
        }
        .nav-item-dropdown.show .dropdown-arrow {
            transform: rotate(180deg);
        }
        .dropdown-menu {
            position: absolute !important;
            top: 100% !important;
            right: 0 !important;
            margin-top: 10px !important;
            background: #ffffff !important;
            border-radius: 14px !important;
            box-shadow: 0 16px 36px rgba(0,0,0,0.12) !important;
            border: 1px solid #f1f5f9 !important;
            min-width: 230px !important;
            padding: 8px 0 !important;
            display: none !important;
            z-index: 999999 !important;
            flex-direction: column !important;
            gap: 0 !important;
            list-style: none !important;
            animation: menuFadeIn 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .nav-item-dropdown.show .dropdown-menu {
            display: flex !important;
        }
        .dropdown-menu li {
            list-style: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        @media (max-width: 1140px) {
            .logo-text { font-size: 18px; }
            .logo-icon-badge { width: 36px; height: 36px; font-size: 16px; }
            .main-nav > ul { gap: 2px !important; }
            .main-nav > ul > li > a { padding: 5px 8px !important; font-size: 12.5px !important; gap: 4px !important; }
            .nav-cart-btn { padding: 5px 9px !important; font-size: 12.5px !important; gap: 4px !important; }
            .user-dropdown-toggle { padding: 4px 9px !important; font-size: 12px !important; gap: 5px !important; }
            .user-name-text { max-width: 90px; }
        }
        @media (max-width: 880px) {
            .site-header { position: sticky; top: 0; }
        }
        .dropdown-header {
            padding: 12px 18px;
            background: #fafbfc;
            border-bottom: 1px solid #f1f5f9;
        }
        .user-fullname {
            font-weight: 700;
            color: #1e293b;
            font-size: 14px;
            line-height: 1.4;
            word-break: break-word;
        }
        .user-role-tag {
            display: inline-block;
            font-size: 11px;
            background: #eff6ff;
            color: #2563eb;
            padding: 2px 8px;
            border-radius: 999px;
            margin-top: 5px;
            font-weight: 600;
        }
        .user-role-tag.role-admin {
            background: #fff7ed;
            color: #ea580c;
        }
        .dropdown-divider {
            height: 1px;
            background: #f1f5f9;
            margin: 4px 0;
        }
        .dropdown-menu li {
            width: 100%;
        }
        .dropdown-menu li a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 18px;
            color: #475569;
            font-size: 14px;
            font-weight: 500;
            width: 100%;
            box-sizing: border-box;
            transition: all 0.15s ease;
        }
        .dropdown-menu li a:hover {
            background: #f8fafc;
            color: #e11d48;
            padding-left: 22px;
        }
        .dropdown-menu li a.logout-link {
            color: #dc2626;
        }
        .dropdown-menu li a.logout-link:hover {
            background: #fef2f2;
            color: #b91c1c;
        }
        @keyframes menuFadeIn {
            from { opacity: 0; transform: translateY(-10px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        
        .main-nav a:active,
        .nav-cart-btn:active,
        .user-dropdown-toggle:active,
        .dropdown-menu a:active,
        .logo-link:active {
            transform: scale(0.94) !important;
            transition: transform 0.08s ease !important;
        }

        .header-right-tools {
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
        }
        .mobile-cart-btn {
            display: none;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 999px;
            background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
            border: 1px solid rgba(254, 205, 211, 0.8);
            color: #e11d48;
            font-size: 17px;
            text-decoration: none;
            position: relative;
            transition: all 0.2s ease;
        }
        .mobile-cart-btn:hover {
            background: #ffe4e6;
            color: #be123c;
            transform: translateY(-1px);
        }
        .mobile-cart-btn .cart-badge {
            position: absolute;
            top: -3px;
            right: -3px;
            background: #e11d48;
            color: #fff;
            font-size: 10.5px;
            font-weight: 700;
            min-width: 18px;
            height: 18px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            box-shadow: 0 2px 5px rgba(225, 29, 72, 0.4);
        }
        .drawer-toggle-btn {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            padding: 7px 13px !important;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%) !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 999px !important;
            color: #334155 !important;
            font-size: 13.5px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
            user-select: none !important;
            line-height: 1.4 !important;
        }
        .drawer-toggle-btn i {
            font-size: 15px;
            color: #2563eb;
        }
        .drawer-toggle-btn:hover {
            background: #eff6ff !important;
            border-color: #bfdbfe !important;
            color: #1d4ed8 !important;
            transform: translateY(-1px) !important;
        }

        .sg-drawer-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 100001;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.3s ease;
        }
        .sg-drawer-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .sg-drawer {
            position: fixed;
            top: 0;
            left: 0;
            width: 86%;
            max-width: 360px;
            height: 100%;
            background: #ffffff;
            z-index: 100002;
            transform: translateX(-100%);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            box-shadow: 12px 0 32px rgba(15, 23, 42, 0.2);
            overflow: hidden;
        }
        .sg-drawer.active {
            transform: translateX(0);
        }

        .sg-drawer-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 16px;
            border-bottom: 1px solid #f1f5f9;
            background: #ffffff;
            flex-shrink: 0;
        }
        .sg-drawer-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sg-drawer-logo-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
            border: 1px solid rgba(254, 205, 211, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e11d48;
            font-size: 17px;
            flex-shrink: 0;
        }
        .sg-drawer-title {
            font-size: 13.5px;
            font-weight: 700;
            color: #1d4ed8;
            letter-spacing: -0.2px;
            line-height: 1.25;
        }
        .sg-drawer-subtitle {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
            line-height: 1.2;
        }
        .sg-drawer-close {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }
        .sg-drawer-close:hover {
            background: #fee2e2;
            color: #dc2626;
            border-color: #fecdd3;
            transform: rotate(90deg);
        }

        .sg-drawer-body {
            flex: 1;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding: 14px 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .sg-drawer-search-wrap {
            width: 100%;
        }
        .sg-drawer-search-form {
            display: flex;
            align-items: center;
            border: 1.5px solid #2563eb;
            border-radius: 999px;
            padding: 3px 4px 3px 14px;
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.08);
            transition: box-shadow 0.2s ease;
        }
        .sg-drawer-search-form:focus-within {
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.2);
        }
        .sg-drawer-search-input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 13px;
            color: #1e293b;
            background: transparent;
            min-width: 0;
        }
        .sg-drawer-search-input::placeholder {
            color: #94a3b8;
        }
        .sg-drawer-search-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #2563eb;
            color: #ffffff;
            border: none;
            outline: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            flex-shrink: 0;
        }
        .sg-drawer-search-btn:hover {
            background: #1d4ed8;
        }

        .sg-drawer-flash-banner {
            display: flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            padding: 9px 12px;
            text-decoration: none;
            color: #1d4ed8;
            font-size: 12.5px;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .sg-drawer-flash-banner:hover {
            background: #dbeafe;
            color: #1e40af;
            transform: translateY(-1px);
        }
        .sg-drawer-flash-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #2563eb;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            flex-shrink: 0;
        }
        .sg-drawer-flash-text {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sg-drawer-nav {
            display: flex;
            flex-direction: column;
            border-top: 1px solid #f1f5f9;
        }
        .sg-drawer-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 8px;
            border-bottom: 1px solid #f8fafc;
            text-decoration: none;
            color: #334155;
            font-size: 13.5px;
            font-weight: 500;
            border-radius: 8px;
            background: transparent;
            border: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            transition: all 0.15s ease;
            box-sizing: border-box;
        }
        .sg-drawer-item:hover {
            background: #f8fafc;
            color: #2563eb;
            padding-left: 12px;
        }
        .sg-drawer-item-icon {
            width: 22px;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sg-drawer-item-icon.color-info { color: #3b82f6; }
        .sg-drawer-item-icon.color-flower { color: #ec4899; }
        .sg-drawer-item-icon.color-guide { color: #0ea5e9; }
        .sg-drawer-item-icon.color-faq { color: #f59e0b; }
        .sg-drawer-item-icon.color-voucher { color: #8b5cf6; }
        .sg-drawer-item-icon.color-wishlist { color: #f43f5e; }
        .sg-drawer-item-icon.color-cart { color: #e11d48; }
        .sg-drawer-item-icon.color-contact { color: #10b981; }
        .sg-drawer-item-icon.color-admin { color: #ea580c; }
        .sg-drawer-item-label {
            flex: 1;
        }
        .sg-drawer-item-arrow {
            font-size: 11px;
            color: #cbd5e1;
            transition: transform 0.25s ease;
        }
        .sg-drawer-item:hover .sg-drawer-item-arrow {
            color: #64748b;
        }
        .sg-drawer-badge {
            background: #e11d48;
            color: #ffffff;
            font-size: 10.5px;
            font-weight: 700;
            padding: 1px 7px;
            border-radius: 999px;
            margin-right: 4px;
        }

        .sg-drawer-accordion {
            width: 100%;
        }
        .sg-drawer-accordion.open .cat-arrow {
            transform: rotate(90deg);
        }
        .sg-drawer-accordion-content {
            display: none;
            flex-direction: column;
            background: #f8fafc;
            border-radius: 10px;
            padding: 6px 0;
            margin: 4px 0 8px 10px;
            border-left: 2px solid #ec4899;
        }
        .sg-drawer-accordion.open .sg-drawer-accordion-content {
            display: flex;
        }
        .sg-drawer-subitem {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            text-decoration: none;
            color: #475569;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.15s ease;
        }
        .sg-drawer-subitem:hover {
            color: #e11d48;
            background: rgba(244, 63, 94, 0.06);
            padding-left: 18px;
        }
        .sg-drawer-subitem i {
            font-size: 12px;
            width: 16px;
            color: #ec4899;
        }

        .sg-drawer-item-admin {
            background: #fff7ed;
            color: #ea580c;
            margin-top: 4px;
        }
        .sg-drawer-item-admin:hover {
            background: #ffedd5;
            color: #c2410c;
        }

        .sg-drawer-auth-block {
            margin-top: auto;
            padding-top: 10px;
            border-top: 1px solid #f1f5f9;
        }
        .sg-drawer-auth-buttons {
            display: flex;
            gap: 8px;
        }
        .sg-drawer-auth-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 0;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .sg-drawer-auth-btn.btn-login {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }
        .sg-drawer-auth-btn.btn-login:hover {
            background: #2563eb;
            color: #ffffff;
        }
        .sg-drawer-auth-btn.btn-register {
            background: #fdf2f8;
            color: #db2777;
            border: 1px solid #fbcfe8;
        }
        .sg-drawer-auth-btn.btn-register:hover {
            background: #db2777;
            color: #ffffff;
        }

        .sg-drawer-user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f8fafc;
            border-radius: 10px;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
        }
        .sg-drawer-user-avatar {
            font-size: 26px;
            color: #e11d48;
        }
        .sg-drawer-user-name {
            font-size: 13.5px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.3;
        }
        .sg-drawer-user-role {
            font-size: 11px;
            color: #2563eb;
            font-weight: 600;
        }
        .sg-drawer-user-role.admin {
            color: #ea580c;
        }
        .sg-drawer-user-links {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }
        .sg-drawer-user-link {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 0;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 500;
            text-decoration: none;
            background: #f1f5f9;
            color: #475569;
            transition: all 0.15s ease;
        }
        .sg-drawer-user-link:hover {
            background: #e2e8f0;
            color: #1e293b;
        }
        .sg-drawer-user-link.logout {
            color: #dc2626;
            background: #fef2f2;
        }
        .sg-drawer-user-link.logout:hover {
            background: #fee2e2;
            color: #b91c1c;
        }

        .sg-drawer-footer {
            padding: 12px 16px;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
            flex-shrink: 0;
            font-size: 11.5px;
            color: #64748b;
            display: flex;
            flex-direction: column;
            gap: 4px;
            line-height: 1.4;
        }
        .sg-drawer-footer-line {
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .sg-drawer-footer-line i {
            color: #94a3b8;
            font-size: 11px;
            width: 14px;
        }

        .header-auth-group {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
        }
        .desktop-auth-link {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            padding: 6px 12px !important;
            border-radius: 999px !important;
            font-size: 13.5px !important;
            font-weight: 500 !important;
            text-decoration: none !important;
            transition: all 0.2s ease !important;
            white-space: nowrap !important;
            line-height: 1.4 !important;
        }
        .desktop-auth-link.nav-link-login {
            color: #059669 !important;
            background: rgba(16, 185, 129, 0.08) !important;
        }
        .desktop-auth-link.nav-link-login:hover {
            background: rgba(16, 185, 129, 0.16) !important;
            transform: translateY(-1px) !important;
        }
        .desktop-auth-link.nav-link-reg {
            color: #7c3aed !important;
            background: rgba(139, 92, 246, 0.08) !important;
        }
        .desktop-auth-link.nav-link-reg:hover {
            background: rgba(139, 92, 246, 0.16) !important;
            transform: translateY(-1px) !important;
        }

        @media (max-width: 991px) {
            .site-header { position: sticky; top: 0; }
            .header-inner {
                height: 60px !important;
                min-height: 60px !important;
                padding: 0 12px !important;
                flex-wrap: nowrap !important;
                justify-content: space-between !important;
            }
            .header-auth-group {
                display: none !important;
            }
            .cart-btn-label {
                display: none !important;
            }
            .user-name-text {
                display: none !important;
            }
            .dropdown-arrow {
                display: none !important;
            }
            .drawer-toggle-btn .drawer-toggle-text {
                display: none;
            }
            .drawer-toggle-btn {
                padding: 8px 11px !important;
            }
            .logo-text {
                font-size: 17px !important;
            }
            .logo-icon-badge {
                width: 35px !important;
                height: 35px !important;
                font-size: 16px !important;
            }
        }
    </style>
</head>
<body>
<div class="wrapper <?php echo $in_admin ? 'admin-wrapper-reset' : ''; ?>">
    <?php if (!$in_admin): ?>
    <header class="site-header">
        <div class="container header-inner">
            <div class="logo">
                <a href="<?php echo $base_url; ?>index.php" class="logo-link">
                    <span class="logo-icon-badge">
                        <i class="fa-solid fa-fan"></i>
                    </span>
                    <span class="logo-text">Shop Hoa Tươi <strong>Sen Garden</strong></span>
                </a>
            </div>
            <div class="header-right-tools">
                <a href="<?php echo $base_url; ?>GioHang.php" class="nav-cart-btn" aria-label="Giỏ hàng">
                    <div class="cart-icon-wrap">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>
                    <span class="cart-btn-label">Giỏ Hàng</span>
                    <span id="cart-count" class="cart-badge"><?php echo $cart_count; ?></span>
                </a>

                <?php if (!isLoggedIn()): ?>
                    <div class="header-auth-group">
                        <a href="<?php echo $base_url; ?>dangnhap.php" class="nav-link-login desktop-auth-link">
                            <i class="fa-solid fa-arrow-right-to-bracket"></i> <span>Đăng nhập</span>
                        </a>
                        <a href="<?php echo $base_url; ?>dangky.php" class="nav-link-reg desktop-auth-link">
                            <i class="fa-solid fa-user-plus"></i> <span>Đăng ký</span>
                        </a>
                    </div>
                <?php else: ?>
                    <?php 
                        $user = getCurrentUser(); 
                        $tenHienThi = !empty($user['HoTen']) ? $user['HoTen'] : (!empty($user['TenDN']) ? $user['TenDN'] : 'Khách hàng');
                    ?>
                    <div class="nav-item-dropdown" id="navUserDropdown">
                        <div class="user-dropdown-toggle" id="btnUserDropdown">
                            <span class="user-avatar-badge">
                                <i class="fa-solid fa-circle-user"></i>
                            </span>
                            <span class="user-name-text" title="<?php echo htmlspecialchars($tenHienThi); ?>">
                                <?php echo htmlspecialchars($tenHienThi); ?>
                            </span>
                            <i class="fa-solid fa-chevron-down dropdown-arrow"></i>
                        </div>

                        <ul class="dropdown-menu">
                            <li class="dropdown-header">
                                <div class="user-fullname"><?php echo htmlspecialchars($tenHienThi); ?></div>
                                <div class="user-role-tag <?php echo isAdmin() ? 'role-admin' : ''; ?>">
                                    <i class="fa-solid <?php echo isAdmin() ? 'fa-shield-halved' : 'fa-user-check'; ?>"></i>
                                    <?php echo isAdmin() ? 'Quản trị viên' : 'Khách hàng'; ?>
                                </div>
                            </li>
                            <li>
                                <a href="<?php echo $base_url; ?>wishlist.php">
                                    <i class="fa-solid fa-heart" style="color: #e11d48;"></i> Hoa yêu thích
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo $base_url; ?>lichsudonhang.php">
                                    <i class="fa-solid fa-receipt" style="color: #2980b9;"></i> Đơn hàng của tôi
                                </a>
                            </li>
                            <?php if (isAdmin()): ?>
                                <li>
                                    <a href="<?php echo $base_url; ?>admin/index.php" style="color: #ea580c; font-weight: 600;">
                                        <i class="fa-solid fa-gauge-high"></i> Trang Quản trị
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li class="dropdown-divider"></li>
                            <li>
                                <a href="<?php echo $base_url; ?>dangxuat.php" class="logout-link">
                                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Đăng xuất
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>

                <button type="button" class="drawer-toggle-btn" id="btnOpenDrawer" aria-label="Mở menu chức năng">
                    <i class="fa-solid fa-bars"></i>
                    <span class="drawer-toggle-text">Menu</span>
                </button>
            </div>
        </div>
    </header>

    <div class="sg-drawer-overlay" id="sgDrawerOverlay"></div>
    <div class="sg-drawer" id="sgDrawer" aria-hidden="true">
        <div class="sg-drawer-header">
            <div class="sg-drawer-brand">
                <div class="sg-drawer-logo-icon">
                    <i class="fa-solid fa-fan"></i>
                </div>
                <div class="sg-drawer-titles">
                    <div class="sg-drawer-title">SHOP HOA TƯƠI SEN GARDEN</div>
                    <div class="sg-drawer-subtitle">Hoa Tươi Nghệ Thuật &amp; Quà Tặng Cao Cấp</div>
                </div>
            </div>
            <button type="button" class="sg-drawer-close" id="btnCloseDrawer" aria-label="Đóng menu">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="sg-drawer-body">
            <div class="sg-drawer-search-wrap">
                <form action="<?php echo $base_url; ?>index.php" method="GET" class="sg-drawer-search-form">
                    <input type="text" name="keyword" placeholder="Nhập tên hoa tươi, mẫu hoa..." class="sg-drawer-search-input" autocomplete="off" />
                    <button type="submit" class="sg-drawer-search-btn" aria-label="Tìm kiếm">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </form>
            </div>

            <a href="tel:0989366990" class="sg-drawer-flash-banner">
                <span class="sg-drawer-flash-icon">
                    <i class="fa-solid fa-phone"></i>
                </span>
                <span class="sg-drawer-flash-text">Hỗ Trợ Khách Hàng 24/7 (0989 366 990)</span>
            </a>

            <div class="sg-drawer-nav">
                <a href="<?php echo $base_url; ?>gioithieu.php" class="sg-drawer-item">
                    <span class="sg-drawer-item-icon color-info"><i class="fa-solid fa-circle-info"></i></span>
                    <span class="sg-drawer-item-label">Giới Thiệu</span>
                    <span class="sg-drawer-item-arrow"><i class="fa-solid fa-chevron-right"></i></span>
                </a>

                <div class="sg-drawer-accordion" id="drawerCatAccordion">
                    <button type="button" class="sg-drawer-item" id="btnToggleDrawerCat">
                        <span class="sg-drawer-item-icon color-flower"><i class="fa-solid fa-spa"></i></span>
                        <span class="sg-drawer-item-label">Danh Mục Hoa Tươi</span>
                        <span class="sg-drawer-item-arrow cat-arrow"><i class="fa-solid fa-chevron-right"></i></span>
                    </button>
                    <div class="sg-drawer-accordion-content" id="drawerCatContent">
                        <a href="<?php echo $base_url; ?>index.php" class="sg-drawer-subitem">
                            <i class="fa-solid fa-border-all"></i> Tất cả hoa tươi
                        </a>
                        <?php if (!empty($drawer_categories)): ?>
                            <?php foreach ($drawer_categories as $c): ?>
                                <a href="<?php echo $base_url; ?>index.php?MaLoai=<?php echo $c['MaLoai']; ?>" class="sg-drawer-subitem">
                                    <i class="fa-solid <?php echo getDrawerCatIcon($c['TenLoai']); ?>"></i>
                                    <?php echo htmlspecialchars($c['TenLoai']); ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <a href="<?php echo $base_url; ?>gioithieu.php#huong-dan" class="sg-drawer-item">
                    <span class="sg-drawer-item-icon color-guide"><i class="fa-solid fa-book-open"></i></span>
                    <span class="sg-drawer-item-label">Hướng Dẫn Mua Hàng</span>
                    <span class="sg-drawer-item-arrow"><i class="fa-solid fa-chevron-right"></i></span>
                </a>

                <a href="<?php echo $base_url; ?>gioithieu.php#faq" class="sg-drawer-item">
                    <span class="sg-drawer-item-icon color-faq"><i class="fa-solid fa-circle-question"></i></span>
                    <span class="sg-drawer-item-label">Câu Hỏi Thường Gặp (FAQ)</span>
                    <span class="sg-drawer-item-arrow"><i class="fa-solid fa-chevron-right"></i></span>
                </a>

                <a href="<?php echo $base_url; ?>voucher.php" class="sg-drawer-item">
                    <span class="sg-drawer-item-icon color-voucher"><i class="fa-solid fa-ticket"></i></span>
                    <span class="sg-drawer-item-label">Mã Giảm Giá - Voucher Ưu Đãi</span>
                    <span class="sg-drawer-item-arrow"><i class="fa-solid fa-chevron-right"></i></span>
                </a>

                <a href="<?php echo $base_url; ?>wishlist.php" class="sg-drawer-item">
                    <span class="sg-drawer-item-icon color-wishlist"><i class="fa-solid fa-heart"></i></span>
                    <span class="sg-drawer-item-label">Hoa Yêu Thích</span>
                    <span class="sg-drawer-item-arrow"><i class="fa-solid fa-chevron-right"></i></span>
                </a>

                <a href="<?php echo $base_url; ?>GioHang.php" class="sg-drawer-item">
                    <span class="sg-drawer-item-icon color-cart"><i class="fa-solid fa-cart-shopping"></i></span>
                    <span class="sg-drawer-item-label">Giỏ Hàng &amp; Thanh Toán</span>
                    <span class="sg-drawer-badge" id="drawer-cart-count"><?php echo $cart_count; ?></span>
                    <span class="sg-drawer-item-arrow"><i class="fa-solid fa-chevron-right"></i></span>
                </a>

                <a href="<?php echo $base_url; ?>lienhe.php" class="sg-drawer-item">
                    <span class="sg-drawer-item-icon color-contact"><i class="fa-solid fa-phone-volume"></i></span>
                    <span class="sg-drawer-item-label">Liên Hệ &amp; Góp Ý</span>
                    <span class="sg-drawer-item-arrow"><i class="fa-solid fa-chevron-right"></i></span>
                </a>

                <?php if (isAdmin()): ?>
                <a href="<?php echo $base_url; ?>admin/index.php" class="sg-drawer-item sg-drawer-item-admin">
                    <span class="sg-drawer-item-icon color-admin"><i class="fa-solid fa-gauge-high"></i></span>
                    <span class="sg-drawer-item-label">Trang Quản Trị Hệ Thống</span>
                    <span class="sg-drawer-item-arrow"><i class="fa-solid fa-chevron-right"></i></span>
                </a>
                <?php endif; ?>
            </div>

            <div class="sg-drawer-auth-block">
                <?php if (!isLoggedIn()): ?>
                    <div class="sg-drawer-auth-buttons">
                        <a href="<?php echo $base_url; ?>dangnhap.php" class="sg-drawer-auth-btn btn-login">
                            <i class="fa-solid fa-arrow-right-to-bracket"></i> Đăng nhập
                        </a>
                        <a href="<?php echo $base_url; ?>dangky.php" class="sg-drawer-auth-btn btn-register">
                            <i class="fa-solid fa-user-plus"></i> Đăng ký
                        </a>
                    </div>
                <?php else: ?>
                    <div class="sg-drawer-user-info">
                        <div class="sg-drawer-user-avatar">
                            <i class="fa-solid fa-circle-user"></i>
                        </div>
                        <div class="sg-drawer-user-details">
                            <div class="sg-drawer-user-name"><?php echo htmlspecialchars($tenHienThi); ?></div>
                            <div class="sg-drawer-user-role <?php echo isAdmin() ? 'admin' : ''; ?>">
                                <i class="fa-solid <?php echo isAdmin() ? 'fa-shield-halved' : 'fa-user-check'; ?>"></i>
                                <?php echo isAdmin() ? 'Quản trị viên' : 'Khách hàng'; ?>
                            </div>
                        </div>
                    </div>
                    <div class="sg-drawer-user-links">
                        <a href="<?php echo $base_url; ?>lichsudonhang.php" class="sg-drawer-user-link">
                            <i class="fa-solid fa-receipt"></i> Đơn hàng của tôi
                        </a>
                        <a href="<?php echo $base_url; ?>dangxuat.php" class="sg-drawer-user-link logout">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Đăng xuất
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="sg-drawer-footer">
            <div class="sg-drawer-footer-line">
                <i class="fa-solid fa-location-dot"></i> 280 An Dương Vương, Phường 4, Quận 5, TP. Hồ Chí Minh
            </div>
            <div class="sg-drawer-footer-line">
                <i class="fa-solid fa-phone"></i> Hotline: 0989 366 990 (8h00 - 21h30)
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnDropdown = document.getElementById('btnUserDropdown');
        const navDropdown = document.getElementById('navUserDropdown');

        if (btnDropdown && navDropdown) {
            btnDropdown.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                navDropdown.classList.toggle('show');
            });

            document.addEventListener('click', function (e) {
                if (!navDropdown.contains(e.target)) {
                    navDropdown.classList.remove('show');
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    navDropdown.classList.remove('show');
                }
            });
        }

        const btnOpenDrawer = document.getElementById('btnOpenDrawer');
        const btnCloseDrawer = document.getElementById('btnCloseDrawer');
        const sgDrawer = document.getElementById('sgDrawer');
        const sgDrawerOverlay = document.getElementById('sgDrawerOverlay');
        const btnToggleDrawerCat = document.getElementById('btnToggleDrawerCat');
        const drawerCatAccordion = document.getElementById('drawerCatAccordion');

        function openDrawer() {
            if (sgDrawer && sgDrawerOverlay) {
                sgDrawer.classList.add('active');
                sgDrawerOverlay.classList.add('active');
                sgDrawer.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeDrawer() {
            if (sgDrawer && sgDrawerOverlay) {
                sgDrawer.classList.remove('active');
                sgDrawerOverlay.classList.remove('active');
                sgDrawer.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }
        }

        if (btnOpenDrawer) {
            btnOpenDrawer.addEventListener('click', function (e) {
                e.preventDefault();
                openDrawer();
            });
        }

        if (btnCloseDrawer) {
            btnCloseDrawer.addEventListener('click', function (e) {
                e.preventDefault();
                closeDrawer();
            });
        }

        if (sgDrawerOverlay) {
            sgDrawerOverlay.addEventListener('click', function () {
                closeDrawer();
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeDrawer();
            }
        });

        if (btnToggleDrawerCat && drawerCatAccordion) {
            btnToggleDrawerCat.addEventListener('click', function (e) {
                e.preventDefault();
                drawerCatAccordion.classList.toggle('open');
            });
        }

        const drawerAnchorLinks = document.querySelectorAll('.sg-drawer-item[href*="#"]');
        drawerAnchorLinks.forEach(function (link) {
            link.addEventListener('click', function () {
                closeDrawer();
            });
        });
    });

    document.addEventListener('pointerdown', function (e) {
        const target = e.target.closest(
            '.main-nav a, .nav-cart-btn, .user-dropdown-toggle, .dropdown-menu a, .logo-link, ' +
            '.drawer-toggle-btn, .sg-drawer-close, .sg-drawer-item, .sg-drawer-subitem, .sg-drawer-auth-btn, .sg-drawer-user-link, .mobile-cart-btn, ' +
            '.btn-search, .btn-reset, .custom-select-trigger, .custom-option, .product-card, .btn-cart, .btn-card-wishlist, ' +
            '.pagination a, #btnBackToTop, .chat-widget-btn, button, .btn, a.btn-detail, ' +
            '.admin-btn, .action-btn, .admin-tab-btn, .admin-nav-item, .admin-shop-link, .kpi-card'
        );
        if (!target) return;

        const rect = target.getBoundingClientRect();
        const ripple = document.createElement('span');
        ripple.className = 'sg-click-ripple';
        
        const size = Math.max(rect.width, rect.height) * 2;
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
        ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';

        const pos = window.getComputedStyle(target).position;
        if (pos === 'static') {
            target.style.position = 'relative';
        }
        target.style.overflow = 'hidden';

        target.appendChild(ripple);
        setTimeout(function() { ripple.remove(); }, 600);
    });
    </script>
    <?php endif; ?>

    <main class="main-content <?php echo $in_admin ? 'admin-main-wrapper' : ''; ?>">
        <div class="container <?php echo $in_admin ? 'admin-container-fluid' : ''; ?>">