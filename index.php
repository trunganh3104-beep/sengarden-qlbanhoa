<?php
require_once __DIR__ . '/includes/db_config.php';
if (isset($_GET['action']) && $_GET['action'] === 'live_search') {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    $kw = trim($_GET['kw'] ?? '');
    $results = [];

    if (!empty($kw)) {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        mysqli_set_charset($conn, "utf8");
        $kwEsc = mysqli_real_escape_string($conn, $kw);
        $res = mysqli_query($conn, "SELECT MaHoa, TenHoa, GiaBan, Hinh FROM hoa WHERE TenHoa LIKE '%$kwEsc%' OR ThanhPhan LIKE '%$kwEsc%' LIMIT 5");
        while ($row = mysqli_fetch_assoc($res)) {
            $results[] = [
                'id' => $row['MaHoa'],
                'name' => $row['TenHoa'],
                'price' => number_format($row['GiaBan']) . ' đ',
                'img' => $row['Hinh']
            ];
        }
        mysqli_close($conn);
    }
    echo json_encode($results);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'quick_view') {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    $id = intval($_GET['id'] ?? 0);
    $data = null;

    if ($id > 0) {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        mysqli_set_charset($conn, "utf8");
        $res = mysqli_query($conn, "SELECT h.*, l.TenLoai FROM hoa h JOIN loaihoa l ON h.MaLoai = l.MaLoai WHERE h.MaHoa = $id");
        if ($row = mysqli_fetch_assoc($res)) {
            $data = [
                'id' => $row['MaHoa'],
                'name' => $row['TenHoa'],
                'price' => number_format($row['GiaBan']) . ' đ',
                'desc' => $row['ThanhPhan'] ?: 'Đang cập nhật thông tin...',
                'img' => $row['Hinh'],
                'category' => $row['TenLoai']
            ];
        }
        mysqli_close($conn);
    }
    echo json_encode($data);
    exit;
}

require_once 'includes/header.php';
include_once 'DataProvider.php';

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$maLoai = isset($_GET['MaLoai']) ? intval($_GET['MaLoai']) : 0;
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

$limit = 8;
$offset = ($page - 1) * $limit;

$whereClauses = ["1=1"];

if ($maLoai > 0) {
    $whereClauses[] = "h.MaLoai = $maLoai";
}

if (!empty($keyword)) {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    mysqli_set_charset($conn, "utf8");
    $keywordEscaped = mysqli_real_escape_string($conn, $keyword);
    mysqli_close($conn);
    $whereClauses[] = "(h.TenHoa LIKE '%$keywordEscaped%' OR h.ThanhPhan LIKE '%$keywordEscaped%')";
}

$whereSql = implode(" AND ", $whereClauses);

switch ($sort) {
    case 'price_asc':
        $orderSql = "h.GiaBan ASC";
        break;
    case 'price_desc':
        $orderSql = "h.GiaBan DESC";
        break;
    case 'name_asc':
        $orderSql = "h.TenHoa ASC";
        break;
    case 'name_desc':
        $orderSql = "h.TenHoa DESC";
        break;
    case 'newest':
    default:
        $orderSql = "h.MaHoa DESC";
        $sort = 'newest';
        break;
}

$sqlCount = "SELECT COUNT(*) AS total FROM hoa h WHERE $whereSql";
$rsCount = DataProvider::ExecuteQuery($sqlCount);
$rowCount = mysqli_fetch_assoc($rsCount);
$totalProducts = intval($rowCount['total']);
$totalPages = ceil($totalProducts / $limit);

if ($totalPages > 0 && $page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

$sqlHoa = "SELECT h.*, l.TenLoai FROM hoa h LEFT JOIN loaihoa l ON h.MaLoai = l.MaLoai WHERE $whereSql ORDER BY $orderSql LIMIT $limit OFFSET $offset";
$rsHoa = DataProvider::ExecuteQuery($sqlHoa);

$rsLoai = DataProvider::ExecuteQuery("SELECT * FROM loaihoa ORDER BY TenLoai ASC");
$loaiHoaList = [];
$selectedLoaiTen = 'Tất cả loại hoa';
if ($rsLoai) {
    while ($loaiRow = mysqli_fetch_assoc($rsLoai)) {
        $loaiHoaList[] = $loaiRow;
        if ($maLoai == intval($loaiRow['MaLoai'])) {
            $selectedLoaiTen = $loaiRow['TenLoai'];
        }
    }
}

if (!function_exists('getLoaiHoaIcon')) {
    function getLoaiHoaIcon($tenLoai) {
        $t = mb_strtolower($tenLoai ?? '', 'UTF-8');
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

$sortLabels = [
    'newest' => ['label' => 'Mới nhất', 'icon' => 'fa-wand-magic-sparkles'],
    'price_asc' => ['label' => 'Giá: Thấp đến Cao', 'icon' => 'fa-arrow-trend-up'],
    'price_desc' => ['label' => 'Giá: Cao đến Thấp', 'icon' => 'fa-arrow-trend-down'],
    'name_asc' => ['label' => 'Tên: A → Z', 'icon' => 'fa-arrow-down-a-z'],
    'name_desc' => ['label' => 'Tên: Z → A', 'icon' => 'fa-arrow-down-z-a']
];
$selectedSortItem = $sortLabels[$sort] ?? $sortLabels['newest'];

function makePageUrl($p, $keyword, $maLoai, $sort) {
    $params = [
        'page' => $p,
        'sort' => $sort
    ];
    if (!empty($keyword)) {
        $params['keyword'] = $keyword;
    }
    if ($maLoai > 0) {
        $params['MaLoai'] = $maLoai;
    }
    return 'index.php?' . http_build_query($params);
}

$userWishlist = [];
if (isset($_SESSION['user']) && !empty($_SESSION['user']['MaKH'])) {
    $uMaKH = intval($_SESSION['user']['MaKH']);
    $rsWish = DataProvider::ExecuteQuery("SELECT MaHoa FROM yeuthich WHERE MaKH = $uMaKH");
    if ($rsWish) {
        while ($rw = mysqli_fetch_assoc($rsWish)) {
            $userWishlist[] = intval($rw['MaHoa']);
        }
    }
} elseif (isset($_SESSION['wishlist']) && is_array($_SESSION['wishlist'])) {
    $userWishlist = array_map('intval', $_SESSION['wishlist']);
}
?>

<style>

.filter-bar {
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid rgba(254, 205, 211, 0.5);
    box-shadow: 0 8px 30px -6px rgba(225, 29, 72, 0.05), 0 2px 6px rgba(0, 0, 0, 0.02);
    padding: 16px 22px;
    margin-bottom: 24px;
    position: relative;
    z-index: 50;
}
.filter-form {
    display: flex;
    gap: 14px;
    align-items: center;
    flex-wrap: wrap;
}

.search-group {
    position: relative;
    flex: 1.4;
    min-width: 240px;
}
.search-field-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 14px;
    pointer-events: none;
    transition: color 0.25s ease;
    z-index: 2;
}
.search-group:focus-within .search-field-icon {
    color: #f43f5e;
}
.search-group input[type="text"] {
    width: 100%;
    padding: 11px 40px 11px 42px;
    border: 1px solid #e2e8f0;
    border-radius: 999px;
    font-size: 13.5px;
    color: #334155;
    background: #fbfcfe;
    outline: none;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    box-sizing: border-box;
}
.search-group input[type="text"]:focus {
    border-color: #f43f5e;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(244, 63, 94, 0.12);
}
.search-clear-btn {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    background: #f1f5f9;
    border: none;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    z-index: 2;
}
.search-clear-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
}

.custom-select-wrap {
    position: relative;
    min-width: 200px;
    user-select: none;
}
.custom-select-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    width: 100%;
    padding: 11px 18px;
    background: #fbfcfe;
    border: 1px solid #e2e8f0;
    border-radius: 999px;
    color: #334155;
    font-size: 13.5px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
    outline: none;
}
.custom-select-trigger:hover {
    background: #ffffff;
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transform: translateY(-1px);
}
.custom-select-wrap.open .custom-select-trigger {
    border-color: #f43f5e;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(244, 63, 94, 0.12);
}
.trigger-icon {
    color: #f43f5e;
    font-size: 13.5px;
    display: flex;
    align-items: center;
}
.trigger-label {
    flex: 1;
    text-align: left;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.trigger-arrow {
    font-size: 11px;
    color: #94a3b8;
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), color 0.2s ease;
}
.custom-select-wrap.open .trigger-arrow {
    transform: rotate(180deg);
    color: #f43f5e;
}

.custom-select-menu {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    min-width: 220px;
    max-height: 290px;
    overflow-y: auto;
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(254, 205, 211, 0.6);
    box-shadow: 0 16px 36px -4px rgba(0, 0, 0, 0.12), 0 4px 12px rgba(225, 29, 72, 0.06);
    padding: 6px;
    z-index: 99999;
    display: none;
    animation: sgMenuDropdown 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}
.custom-select-wrap.open .custom-select-menu {
    display: block;
}
@keyframes sgMenuDropdown {
    from { opacity: 0; transform: translateY(-8px) scale(0.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.custom-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 14px;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 500;
    color: #475569;
    cursor: pointer;
    transition: all 0.18s ease;
}
.custom-option:hover {
    background: #fff1f2;
    color: #e11d48;
    padding-left: 17px;
}
.custom-option.selected {
    background: #ffe4e6;
    color: #be123c;
    font-weight: 600;
}
.custom-option .opt-icon {
    width: 20px;
    text-align: center;
    color: #f43f5e;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.custom-option.selected .opt-icon {
    color: #be123c;
}
.custom-option .opt-text {
    flex: 1;
}
.custom-option .opt-check {
    color: #e11d48;
    font-size: 11px;
}

.btn-search {
    background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
    color: #ffffff;
    border: none;
    border-radius: 999px;
    padding: 11px 24px;
    font-weight: 600;
    font-size: 13.5px;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(244, 63, 94, 0.28);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    outline: none;
    user-select: none;
}
.btn-search:hover {
    background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
    box-shadow: 0 6px 20px rgba(244, 63, 94, 0.38);
    transform: translateY(-1px);
}

.btn-reset {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 18px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    background: #f1f5f9;
    text-decoration: none;
    transition: all 0.2s ease;
    user-select: none;
}
.btn-reset:hover {
    background: #e2e8f0;
    color: #0f172a;
    transform: translateY(-1px);
}

.search-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    background: #ffffff;
    border: 1px solid rgba(254, 205, 211, 0.6);
    border-radius: 16px;
    box-shadow: 0 16px 36px rgba(0,0,0,0.12);
    max-height: 320px;
    overflow-y: auto;
    z-index: 999999;
    display: none;
    padding: 6px;
}
.search-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 10px;
    text-decoration: none;
    color: #333;
    transition: all 0.18s ease;
}
.search-item:hover {
    background: #fff1f2;
    padding-left: 18px;
}
.search-item img {
    width: 44px;
    height: 44px;
    object-fit: cover;
    border-radius: 8px;
}
.search-item .info {
    flex: 1;
    text-align: left;
}
.search-item .name {
    font-size: 13.5px;
    font-weight: 600;
    color: #1e293b;
}
.search-item .price {
    font-size: 13px;
    font-weight: 700;
    color: #e11d48;
}
@media (max-width: 600px) {
    .filter-bar {
        padding: 14px 16px;
    }
    .search-group {
        width: 100%;
        min-width: 100%;
    }
    .custom-select-wrap {
        flex: 1 1 100%;
        min-width: 100%;
    }
    .btn-search, .btn-reset {
        flex: 1;
        justify-content: center;
    }
}
.quickview-modal {
    display: none;
    position: fixed;
    z-index: 9999999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.6);
    align-items: center;
    justify-content: center;
}
.quickview-content {
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    width: 90%;
    max-width: 620px;
    position: relative;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}
.quickview-close {
    position: absolute;
    top: 8px;
    right: 14px;
    font-size: 28px;
    font-weight: bold;
    color: #888;
    cursor: pointer;
    border: none;
    background: none;
}
.quickview-close:hover {
    color: #e30019;
}

.product-grid {
    display: grid !important;
    grid-template-columns: repeat(4, 1fr) !important;
    gap: 22px !important;
    margin-top: 20px !important;
}
@media (max-width: 1100px) {
    .product-grid { grid-template-columns: repeat(3, 1fr) !important; }
}
@media (max-width: 800px) {
    .product-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 14px !important; }
}
@media (max-width: 480px) {
    .product-grid { grid-template-columns: 1fr !important; }
}

.product-card {
    background: #ffffff !important;
    border: 1px solid #eef2f6 !important;
    border-radius: 14px !important;
    overflow: hidden !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: space-between !important;
    padding: 0 !important;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04) !important;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
    position: relative !important;
}
.product-card:hover {
    transform: translateY(-6px) !important;
    box-shadow: 0 16px 30px rgba(225, 29, 72, 0.1) !important;
    border-color: #fecdd3 !important;
}

.product-card .card-img-wrap {
    position: relative !important;
    width: 100% !important;
    height: 240px !important;
    overflow: hidden !important;
    background: #f8fafc !important;
}
.product-card .card-img-wrap a {
    display: block !important;
    width: 100% !important;
    height: 100% !important;
}
.product-card .card-img-wrap .hinh {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    display: block !important;
    transition: transform 0.5s ease !important;
    border-radius: 0 !important;
    margin: 0 !important;
}
.product-card:hover .card-img-wrap .hinh {
    transform: scale(1.06) !important;
}

.product-card .btn-card-wishlist {
    position: absolute !important;
    top: 10px !important;
    right: 10px !important;
    width: 34px !important;
    height: 34px !important;
    border-radius: 50% !important;
    background: rgba(255, 255, 255, 0.9) !important;
    backdrop-filter: blur(6px) !important;
    -webkit-backdrop-filter: blur(6px) !important;
    border: 1px solid rgba(255, 255, 255, 0.9) !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
    color: #94a3b8 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    cursor: pointer !important;
    z-index: 5 !important;
    transition: all 0.2s ease !important;
    padding: 0 !important;
    outline: none !important;
}
.product-card .btn-card-wishlist:hover {
    transform: scale(1.12) !important;
    color: #e11d48 !important;
    background: #ffffff !important;
}
.product-card .btn-card-wishlist.active {
    color: #e11d48 !important;
    background: #ffffff !important;
}

.product-card .card-content-wrap {
    padding: 14px 16px 18px 16px !important;
    display: flex !important;
    flex-direction: column !important;
    flex: 1 !important;
    text-align: center !important;
}

.product-card .card-cat-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 500;
    color: #e11d48;
    background: #fff1f2;
    padding: 2px 10px;
    border-radius: 20px;
    margin: 0 auto 8px auto;
    width: fit-content;
    border: 1px solid #ffe4e6;
    letter-spacing: 0.2px;
}
.product-card .card-cat-badge i {
    font-size: 10.5px;
}

.product-card .ten {
    font-size: 15.5px !important;
    font-weight: 600 !important;
    color: #1e293b !important;
    margin: 0 0 6px 0 !important;
    line-height: 1.35 !important;
    height: 40px !important;
    overflow: hidden !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 2 !important;
    -webkit-box-orient: vertical !important;
    text-align: center !important;
}
.product-card .ten a {
    color: #1e293b !important;
    text-decoration: none !important;
    transition: color 0.2s ease !important;
}
.product-card .ten a:hover {
    color: #e11d48 !important;
}

.product-card .giaban {
    font-size: 17.5px !important;
    font-weight: 700 !important;
    color: #e11d48 !important;
    letter-spacing: -0.02em !important;
    margin: 0 0 14px 0 !important;
    text-align: center !important;
}

.product-card .btn-cart {
    width: 100% !important;
    padding: 10px 16px !important;
    background: linear-gradient(135deg, #e11d48 0%, #be123c 100%) !important;
    color: #ffffff !important;
    font-weight: 600 !important;
    font-size: 13.5px !important;
    border-radius: 999px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    border: none !important;
    box-shadow: 0 4px 12px rgba(225, 29, 72, 0.25) !important;
    transition: all 0.22s ease !important;
    text-decoration: none !important;
    cursor: pointer !important;
    margin-top: auto !important;
}
.product-card .btn-cart:hover {
    background: linear-gradient(135deg, #be123c 0%, #9f1239 100%) !important;
    box-shadow: 0 6px 16px rgba(225, 29, 72, 0.35) !important;
    transform: translateY(-1px) !important;
}
.toast-cart {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: #2c3e50;
    color: #fff;
    padding: 14px 20px;
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.25);
    display: flex;
    align-items: center;
    gap: 12px;
    z-index: 9999999;
    transform: translateY(100px);
    opacity: 0;
    transition: all 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    pointer-events: none;
}
.toast-cart.show {
    transform: translateY(0);
    opacity: 1;
}
.toast-cart .toast-icon {
    background: #27ae60;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: bold;
}

    display: none;
    position: fixed;
    bottom: 28px;
    left: 28px;
    z-index: 9999;
    border: none;
    outline: none;
    background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
    color: white;
    cursor: pointer;
    width: 46px;
    height: 46px;
    border-radius: 50%;
    font-size: 18px;
    box-shadow: 0 6px 18px rgba(244, 63, 94, 0.35);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    align-items: center;
    justify-content: center;
}
    background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
    transform: translateY(-3px) scale(1.06);
    box-shadow: 0 10px 24px rgba(244, 63, 94, 0.45);
}
    .toolbar-display {
        display: flex;
        align-items: center;
        margin-bottom: 22px;
        padding: 12px 20px;
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid rgba(254, 205, 211, 0.45);
        box-shadow: 0 4px 16px -4px rgba(225, 29, 72, 0.04), 0 2px 6px rgba(0, 0, 0, 0.02);
    }
    .result-count {
        color: #64748b;
        font-size: 14.5px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .result-count strong {
        color: #e11d48;
        font-weight: 700;
    }
    .result-count i {
        color: #f43f5e;
        font-size: 14px;
    }
</style>

<?php if ($page == 1 && empty($keyword) && $maLoai == 0): ?>
    
    <div class="sg-hero-section" data-sg-reveal>
        <div class="sg-hero-content">
            <div class="sg-hero-badge">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Bộ Sưu Tập Hoa Tươi Mùa Mới 2026
            </div>
            <h2 class="sg-hero-title">
                Trao Gửi Yêu Thương Qua <span>Từng Cánh Hoa Tươi</span>
            </h2>
            <p class="sg-hero-subtitle">
                Những đóa hoa rạng ngời được tuyển chọn khắt khe mỗi sớm mai, gói ghém trọn vẹn cảm xúc và trao tận tay người thương đúng hẹn.
            </p>
            <div>
                <a href="#content-area" class="sg-hero-cta">
                    <span>Khám phá bộ sưu tập</span>
                    <i class="fa-solid fa-arrow-down-long"></i>
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="product-container">
    <div class="filter-bar" data-sg-reveal>
        <form method="get" action="index.php" class="filter-form">
            <div class="search-group">
                <i class="fa-solid fa-magnifying-glass search-field-icon"></i>
                <input type="text" name="keyword" autocomplete="off" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Tìm tên hoa hoặc thành phần...">
                <button type="button" class="search-clear-btn" id="btnClearSearch" title="Xóa tìm kiếm" <?php echo empty($keyword) ? 'style="display:none;"' : ''; ?>>&times;</button>
                <div class="search-dropdown" id="search-results"></div>
            </div>

            
            <div class="custom-select-wrap" id="wrap-maloai">
                <input type="hidden" name="MaLoai" id="input-maloai" value="<?php echo $maLoai; ?>">
                <button type="button" class="custom-select-trigger" title="Chọn loại hoa">
                    <span class="trigger-icon"><i class="fa-solid <?php echo ($maLoai == 0) ? 'fa-spa' : getLoaiHoaIcon($selectedLoaiTen); ?>"></i></span>
                    <span class="trigger-label"><?php echo htmlspecialchars($selectedLoaiTen); ?></span>
                    <i class="fa-solid fa-chevron-down trigger-arrow"></i>
                </button>
                <div class="custom-select-menu">
                    <div class="custom-option <?php echo ($maLoai == 0) ? 'selected' : ''; ?>" data-value="0" data-icon="fa-layer-group">
                        <span class="opt-icon"><i class="fa-solid fa-layer-group"></i></span>
                        <span class="opt-text">Tất cả loại hoa</span>
                        <?php if ($maLoai == 0): ?><i class="fa-solid fa-check opt-check"></i><?php endif; ?>
                    </div>
                    <?php foreach ($loaiHoaList as $loai): 
                        $isSel = ($maLoai == intval($loai['MaLoai']));
                        $loaiIcon = getLoaiHoaIcon($loai['TenLoai']);
                    ?>
                        <div class="custom-option <?php echo $isSel ? 'selected' : ''; ?>" data-value="<?php echo $loai['MaLoai']; ?>" data-icon="<?php echo $loaiIcon; ?>">
                            <span class="opt-icon"><i class="fa-solid <?php echo $loaiIcon; ?>"></i></span>
                            <span class="opt-text"><?php echo htmlspecialchars($loai['TenLoai']); ?></span>
                            <?php if ($isSel): ?><i class="fa-solid fa-check opt-check"></i><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            
            <div class="custom-select-wrap" id="wrap-sort">
                <input type="hidden" name="sort" id="input-sort" value="<?php echo htmlspecialchars($sort); ?>">
                <button type="button" class="custom-select-trigger" title="Sắp xếp theo">
                    <span class="trigger-icon"><i class="fa-solid <?php echo $selectedSortItem['icon']; ?>"></i></span>
                    <span class="trigger-label"><?php echo htmlspecialchars($selectedSortItem['label']); ?></span>
                    <i class="fa-solid fa-chevron-down trigger-arrow"></i>
                </button>
                <div class="custom-select-menu">
                    <?php foreach ($sortLabels as $sKey => $sVal): 
                        $isSel = ($sort === $sKey);
                    ?>
                        <div class="custom-option <?php echo $isSel ? 'selected' : ''; ?>" data-value="<?php echo $sKey; ?>" data-icon="<?php echo $sVal['icon']; ?>">
                            <span class="opt-icon"><i class="fa-solid <?php echo $sVal['icon']; ?>"></i></span>
                            <span class="opt-text"><?php echo htmlspecialchars($sVal['label']); ?></span>
                            <?php if ($isSel): ?><i class="fa-solid fa-check opt-check"></i><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="btn-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span>Tìm kiếm</span>
            </button>

            <?php if (!empty($keyword) || $maLoai > 0 || $sort !== 'newest'): ?>
                <a href="index.php" class="btn-reset">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Đặt lại</span>
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="toolbar-display" data-sg-reveal>
        <div class="result-count">
            <i class="fa-solid fa-shapes"></i>
            <?php if (!empty($keyword)): ?>
                Kết quả tìm kiếm cho: "<strong><?php echo htmlspecialchars($keyword); ?></strong>" — 
            <?php endif; ?>
            <span>Tìm thấy <strong><?php echo $totalProducts; ?></strong> sản phẩm hoa tươi</span>
        </div>
    </div>

    <?php if ($totalProducts == 0): ?>
        <div class="empty-alert" data-sg-reveal>
            <p>Không tìm thấy sản phẩm hoa nào phù hợp với yêu cầu tìm kiếm!</p>
            <p><a href="index.php" class="btn-reset" style="display:inline-block; margin-top:10px;">Xem tất cả sản phẩm</a></p>
        </div>
    <?php else: ?>
        <div class="product-grid" id="content-area">
            <?php $itemIndex = 0; ?>
            <?php while ($hoa = mysqli_fetch_assoc($rsHoa)): ?>
                <?php $itemIndex++; ?>
                <div class="product-card sg-delay-<?php echo (($itemIndex - 1) % 6) + 1; ?>" data-sg-reveal>
                    <div class="card-img-wrap">
                        <button type="button" class="btn-card-wishlist <?php echo in_array(intval($hoa['MaHoa']), $userWishlist) ? 'active' : ''; ?>" data-id="<?php echo $hoa['MaHoa']; ?>" title="Thêm vào yêu thích">
                            <i class="<?php echo in_array(intval($hoa['MaHoa']), $userWishlist) ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
                        </button>

                        <a href="chitiet.php?MaHoa=<?php echo $hoa['MaHoa']; ?>">
                            <img class="hinh" src="hoa/<?php echo htmlspecialchars($hoa['Hinh']); ?>" alt="<?php echo htmlspecialchars($hoa['TenHoa']); ?>" onerror="this.src='images/no-image.png';">
                        </a>
                    </div>

                    <div class="card-content-wrap">
                        <?php if (!empty($hoa['TenLoai'])): ?>
                            <span class="card-cat-badge">
                                <i class="fa-solid <?php echo getLoaiHoaIcon($hoa['TenLoai']); ?>"></i> <?php echo htmlspecialchars($hoa['TenLoai']); ?>
                            </span>
                        <?php endif; ?>
                        <h3 class="ten">
                            <a href="chitiet.php?MaHoa=<?php echo $hoa['MaHoa']; ?>" title="<?php echo htmlspecialchars($hoa['TenHoa']); ?>">
                                <?php echo htmlspecialchars($hoa['TenHoa']); ?>
                            </a>
                        </h3>

                        <div class="giaban"><?php echo number_format($hoa['GiaBan']); ?> đ</div>

                        <a href="MyCart.php?action=add&id=<?php echo $hoa['MaHoa']; ?>" class="btn-cart" data-name="<?php echo htmlspecialchars($hoa['TenHoa']); ?>">
                            <i class="fa-solid fa-cart-shopping"></i> Thêm vào giỏ
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="<?php echo makePageUrl($page - 1, $keyword, $maLoai, $sort); ?>">« Trước</a>
                <?php else: ?>
                    <span class="disabled">« Trước</span>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="<?php echo makePageUrl($i, $keyword, $maLoai, $sort); ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="<?php echo makePageUrl($page + 1, $keyword, $maLoai, $sort); ?>">Sau »</a>
                <?php else: ?>
                    <span class="disabled">Sau »</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="quickview-modal" id="qvModal">
    <div class="quickview-content">
        <button type="button" class="quickview-close" id="qvClose">&times;</button>
        <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px; text-align: center;">
                <img id="qvImg" src="" style="width: 100%; max-height: 240px; object-fit: cover; border-radius: 6px;" alt="">
            </div>
            <div style="flex: 1.2; min-width: 240px;">
                <h3 id="qvName" style="color: #333; margin-bottom: 8px;"></h3>
                <div style="color: #888; font-size: 13px; margin-bottom: 8px;">Danh mục: <strong id="qvCat"></strong></div>
                <div id="qvPrice" style="color: #e30019; font-weight: bold; font-size: 18px; margin-bottom: 12px;"></div>
                <div id="qvDesc" style="font-size: 13px; color: #555; background: #f9f9f9; padding: 10px; border-radius: 4px; margin-bottom: 16px; max-height: 90px; overflow-y: auto;"></div>
                <a href="" id="qvAddCart" class="btn btn-cart" data-name="" style="padding: 10px 20px; font-weight: bold; text-decoration: none; display: inline-block;">Thêm vào giỏ</a>
            </div>
        </div>
    </div>
</div>

<div class="toast-cart" id="toastCart">
    <span class="toast-icon">✓</span>
    <span id="toastMsg">Đã thêm sản phẩm vào giỏ!</span>
</div>

<button type="button" id="btnBackToTop" title="Về đầu trang">↑</button>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let toastTimer;
    function showToast(productName) {
        const toast = document.getElementById('toastCart');
        const toastMsg = document.getElementById('toastMsg');
        if (!toast || !toastMsg) return;

        toastMsg.textContent = productName ? `Đã thêm "${productName}" vào giỏ hàng!` : 'Đã thêm sản phẩm vào giỏ hàng!';
        toast.classList.add('show');

        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toast.classList.remove('show');
        }, 2500);
    }

    const btnBackTop = document.getElementById('btnBackToTop');
    window.addEventListener('scroll', function () {
        if (window.scrollY > 300) {
            btnBackTop.style.display = 'block';
        } else {
            btnBackTop.style.display = 'none';
        }
    });

    btnBackTop.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    function getCartLink() {
        return document.querySelector('a[href*="GioHang"], a[href*="giohang"], a[href*="LayGio"]') 
            || Array.from(document.querySelectorAll('a')).find(a => /giỏ hàng/i.test(a.textContent));
    }

    function initFlyToCart() {
        const cartButtons = document.querySelectorAll('.btn-cart');

        cartButtons.forEach(button => {
            if (button.dataset.flyBound) return;
            button.dataset.flyBound = "true";

            button.addEventListener('click', function (e) {
                e.preventDefault();
                const addUrl = this.getAttribute('href');
                const pName = this.getAttribute('data-name') || '';
                const card = this.closest('.product-card') || this.closest('.quickview-content');
                const flowerImg = card ? card.querySelector('img') : null;

                if (!flowerImg) {
                    window.location.href = addUrl;
                    return;
                }

                const currentCart = getCartLink();
                const imgRect = flowerImg.getBoundingClientRect();
                const cartRect = currentCart 
                    ? currentCart.getBoundingClientRect() 
                    : { top: 20, left: window.innerWidth - 120, width: 40, height: 30 };

                const flyer = flowerImg.cloneNode();
                flyer.style.position = 'fixed';
                flyer.style.zIndex = '99999999';
                flyer.style.borderRadius = '50%';
                flyer.style.border = '2px solid #e30019';
                flyer.style.boxShadow = '0 6px 20px rgba(0,0,0,0.3)';
                flyer.style.objectFit = 'cover';
                flyer.style.pointerEvents = 'none';
                flyer.style.top = imgRect.top + 'px';
                flyer.style.left = imgRect.left + 'px';
                flyer.style.width = imgRect.width + 'px';
                flyer.style.height = imgRect.height + 'px';
                flyer.style.transition = 'all 0.75s cubic-bezier(0.2, 0.9, 0.3, 1)';

                document.body.appendChild(flyer);
                void flyer.offsetWidth;

                flyer.style.top = (cartRect.top + 5) + 'px';
                flyer.style.left = (cartRect.left + 15) + 'px';
                flyer.style.width = '24px';
                flyer.style.height = '24px';
                flyer.style.opacity = '0.2';
                flyer.style.transform = 'rotate(360deg)';

                fetch(addUrl).catch(err => console.error(err));

                setTimeout(() => {
                    flyer.remove();

                    const cartCountElem = document.getElementById('cart-count');
                    if (cartCountElem) {
                        let currentCount = parseInt(cartCountElem.textContent.trim(), 10);
                        if (isNaN(currentCount)) currentCount = 0;
                        cartCountElem.textContent = currentCount + 1;
                    }

                    const targetLink = getCartLink();
                    if (targetLink) {
                        targetLink.style.display = 'inline-block';
                        targetLink.style.transition = 'transform 0.2s ease';
                        targetLink.style.transform = 'scale(1.25)';
                        targetLink.style.color = '#e30019';
                        setTimeout(() => {
                            targetLink.style.transform = 'scale(1)';
                            targetLink.style.color = '';
                        }, 250);
                    }

                    if (window.triggerCartJiggle) {
                        window.triggerCartJiggle();
                    }

                    showToast(pName);
                }, 750);
            });
        });
    }

    const modal = document.getElementById('qvModal');
    const closeBtn = document.getElementById('qvClose');

    document.addEventListener('click', function (e) {
        const qvBtn = e.target.closest('.btn-quickview');
        if (qvBtn) {
            e.preventDefault();
            const id = qvBtn.getAttribute('data-id');
            fetch('index.php?action=quick_view&id=' + id)
                .then(res => res.json())
                .then(data => {
                    if (data && modal) {
                        document.getElementById('qvImg').src = 'hoa/' + data.img;
                        document.getElementById('qvName').textContent = data.name;
                        document.getElementById('qvCat').textContent = data.category;
                        document.getElementById('qvPrice').textContent = data.price;
                        document.getElementById('qvDesc').textContent = data.desc;
                        
                        const qvAdd = document.getElementById('qvAddCart');
                        qvAdd.href = 'MyCart.php?action=add&id=' + data.id;
                        qvAdd.setAttribute('data-name', data.name);
                        
                        modal.style.display = 'flex';
                        initFlyToCart();
                    }
                })
                .catch(err => console.error('Lỗi QuickView:', err));
            return;
        }

        if (e.target === modal || e.target === closeBtn) {
            modal.style.display = 'none';
        }
    });

    let liveTimer;
    document.addEventListener('input', function (e) {
        if (e.target && e.target.name === 'keyword') {
            const input = e.target;
            const box = document.getElementById('search-results');
            if (!box) return;

            clearTimeout(liveTimer);
            const val = input.value.trim();
            if (val.length === 0) {
                box.style.display = 'none';
                box.innerHTML = '';
                return;
            }

            liveTimer = setTimeout(() => {
                fetch('index.php?action=live_search&kw=' + encodeURIComponent(val))
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            box.innerHTML = data.map(item => `
                                <a href="chitiet.php?MaHoa=${item.id}" class="search-item">
                                    <img src="hoa/${item.img}" onerror="this.src='images/no-image.png'">
                                    <div class="info">
                                        <div class="name">${item.name}</div>
                                        <div class="price">${item.price}</div>
                                    </div>
                                </a>
                            `).join('');
                            box.style.display = 'block';
                        } else {
                            box.innerHTML = '<div style="padding:10px; font-size:13px; color:#888; text-align:center;">Không tìm thấy hoa phù hợp</div>';
                            box.style.display = 'block';
                        }
                    })
                    .catch(err => console.error('Lỗi tìm kiếm:', err));
            }, 250);
        }
    });

    document.addEventListener('click', function (e) {
        const box = document.getElementById('search-results');
        const input = document.querySelector('input[name="keyword"]');
        if (box && input && !input.contains(e.target) && !box.contains(e.target)) {
            box.style.display = 'none';
        }
    });

    try { localStorage.removeItem('qlbanhoa_view_mode'); } catch(e){}

    function bindAjaxFilter() {
        const form = document.querySelector('.filter-form');
        const container = document.querySelector('.product-container');
        if (!form || !container) return;

        function loadProducts(url) {
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContainer = doc.querySelector('.product-container');
                    if (newContainer) {
                        container.innerHTML = newContainer.innerHTML;
                        container.querySelectorAll('[data-sg-reveal]').forEach(el => el.classList.add('sg-revealed'));
                        window.history.pushState({}, '', url);
                        bindAjaxFilter();
                        initFlyToCart();
                    }
                })
                .catch(err => console.error('Lỗi lọc:', err));
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const params = new URLSearchParams(new FormData(form));
            loadProducts('index.php?' + params.toString());
        });

        form.querySelectorAll('.custom-select-wrap').forEach(wrap => {
            const trigger = wrap.querySelector('.custom-select-trigger');
            const menu = wrap.querySelector('.custom-select-menu');
            const hiddenInput = wrap.querySelector('input[type="hidden"]');
            const triggerLabel = wrap.querySelector('.trigger-label');
            const triggerIcon = wrap.querySelector('.trigger-icon i');

            if (!trigger || !menu || !hiddenInput) return;

            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const isOpen = wrap.classList.contains('open');
                document.querySelectorAll('.custom-select-wrap.open').forEach(w => {
                    if (w !== wrap) w.classList.remove('open');
                });
                wrap.classList.toggle('open', !isOpen);
            });

            menu.querySelectorAll('.custom-option').forEach(opt => {
                opt.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const val = opt.getAttribute('data-value');
                    const text = opt.querySelector('.opt-text').textContent;
                    const iconName = opt.getAttribute('data-icon') || 'fa-spa';

                    hiddenInput.value = val;
                    if (triggerLabel) triggerLabel.textContent = text;
                    if (triggerIcon) triggerIcon.className = 'fa-solid ' + iconName;

                    menu.querySelectorAll('.custom-option').forEach(o => {
                        o.classList.remove('selected');
                        const c = o.querySelector('.opt-check');
                        if (c) c.remove();
                    });
                    opt.classList.add('selected');
                    const check = document.createElement('i');
                    check.className = 'fa-solid fa-check opt-check';
                    opt.appendChild(check);

                    wrap.classList.remove('open');

                    const params = new URLSearchParams(new FormData(form));
                    loadProducts('index.php?' + params.toString());
                });
            });
        });

        const searchInput = form.querySelector('input[name="keyword"]');
        const clearBtn = form.querySelector('#btnClearSearch');
        if (searchInput && clearBtn) {
            searchInput.addEventListener('input', function () {
                clearBtn.style.display = this.value.trim() ? 'flex' : 'none';
            });
            clearBtn.addEventListener('click', function (e) {
                e.preventDefault();
                searchInput.value = '';
                clearBtn.style.display = 'none';
                searchInput.focus();
                const params = new URLSearchParams(new FormData(form));
                loadProducts('index.php?' + params.toString());
            });
        }

        container.querySelectorAll('.pagination a, .btn-reset').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                loadProducts(this.getAttribute('href'));
            });
        });

        initFlyToCart();
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.custom-select-wrap')) {
            document.querySelectorAll('.custom-select-wrap.open').forEach(w => w.classList.remove('open'));
        }
    });

    document.addEventListener('click', function (e) {
        const wishBtn = e.target.closest('.btn-card-wishlist');
        if (wishBtn) {
            e.preventDefault();
            e.stopPropagation();
            const id = wishBtn.getAttribute('data-id');
            fetch('ajax_wishlist.php?action=toggle&id=' + id)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const icon = wishBtn.querySelector('i');
                        if (data.action === 'added') {
                            wishBtn.classList.add('active');
                            if (icon) icon.className = 'fa-solid fa-heart';
                            wishBtn.style.transform = 'scale(1.25)';
                            setTimeout(() => wishBtn.style.transform = '', 200);
                            showToast('Đã thêm vào hoa yêu thích ❤️');
                        } else {
                            wishBtn.classList.remove('active');
                            if (icon) icon.className = 'fa-regular fa-heart';
                            showToast('Đã xóa khỏi hoa yêu thích');
                        }
                    }
                })
                .catch(err => console.error(err));
        }
    });

    bindAjaxFilter();
});
</script>

<?php include_once 'includes/promo_popup.php'; ?>
<?php require_once 'includes/footer.php'; ?>