<?php 
require_once 'includes/header.php'; 
include_once("DataProvider.php");

if (!isset($_GET['MaHoa']) || empty($_GET['MaHoa'])) {
    echo "<div class='container' style='text-align:center; padding: 50px;'><h3 style='color:red;'>Sản phẩm không hợp lệ!</h3><a href='index.php' class='btn btn-detail'>Quay lại</a></div>";
    require_once 'includes/footer.php';
    exit;
}

$maHoa = intval($_GET['MaHoa']);
$sql = "SELECT h.*, l.TenLoai FROM hoa h JOIN loaihoa l ON h.MaLoai = l.MaLoai WHERE h.MaHoa = $maHoa";
$rs = DataProvider::ExecuteQuery($sql);

if (mysqli_num_rows($rs) == 0) {
    echo "<div class='container' style='text-align:center; padding: 50px;'><h3 style='color:red;'>Không tìm thấy sản phẩm!</h3><a href='index.php' class='btn btn-detail'>Quay lại</a></div>";
    require_once 'includes/footer.php';
    exit;
}

$hoa = mysqli_fetch_array($rs);
$tenHoa = htmlspecialchars($hoa['TenHoa']);
$giaBan = number_format($hoa['GiaBan']);
$rawThanhPhan = $hoa['ThanhPhan'] ?? '';
$thanhPhan = nl2br(htmlspecialchars($rawThanhPhan));
$rawIngredients = array_filter(array_map('trim', explode(',', $rawThanhPhan)));
$hinh = htmlspecialchars($hoa['Hinh']);
$tenLoai = htmlspecialchars($hoa['TenLoai']);

if (!function_exists('getLoaiHoaIcon')) {
    function getLoaiHoaIcon($tenLoai) {
        $t = mb_strtolower($tenLoai ?? '', 'UTF-8');
        if (strpos($t, 'cưới') !== false) return 'fa-heart';
        if (strpos($t, 'khai trương') !== false) return 'fa-ribbon';
        if (strpos($t, 'sinh nhật') !== false) return 'fa-cake-candles';
        if (strpos($t, 'tình yêu') !== false) return 'fa-heart-pulse';
        if (strpos($t, 'bó') !== false) return 'fa-seedling';
        if (strpos($t, 'giỏ') !== false) return 'fa-gift';
        if (strpos($t, 'văn phòng') !== false) return 'fa-building';
        return 'fa-spa';
    }
}

if (!function_exists('getIngredientIconData')) {
    function getIngredientIconData($text) {
        $t = mb_strtolower($text, 'UTF-8');
        if (strpos($t, 'hồng') !== false) return ['icon' => 'fa-spa', 'color' => '#e11d48', 'bg' => '#ffe4e6'];
        if (strpos($t, 'lan') !== false) return ['icon' => 'fa-seedling', 'color' => '#9333ea', 'bg' => '#f3e8ff'];
        if (strpos($t, 'hướng dương') !== false) return ['icon' => 'fa-sun', 'color' => '#d97706', 'bg' => '#fef3c7'];
        if (strpos($t, 'ly') !== false || strpos($t, 'bách hợp') !== false) return ['icon' => 'fa-leaf', 'color' => '#db2777', 'bg' => '#fce7f3'];
        if (strpos($t, 'cúc') !== false || strpos($t, 'đồng tiền') !== false || strpos($t, 'cát tường') !== false || strpos($t, 'baby') !== false || strpos($t, 'ngọc') !== false) return ['icon' => 'fa-asterisk', 'color' => '#0891b2', 'bg' => '#cffafe'];
        if (strpos($t, 'lá') !== false || strpos($t, 'tùng') !== false || strpos($t, 'cành') !== false) return ['icon' => 'fa-leaf', 'color' => '#16a34a', 'bg' => '#dcfce7'];
        if (strpos($t, 'nơ') !== false || strpos($t, 'ruy băng') !== false) return ['icon' => 'fa-ribbon', 'color' => '#e11d48', 'bg' => '#ffe4e6'];
        if (strpos($t, 'giỏ') !== false || strpos($t, 'hộp') !== false || strpos($t, 'bình') !== false || strpos($t, 'gỗ') !== false || strpos($t, 'giấy') !== false || strpos($t, 'nến') !== false) return ['icon' => 'fa-gift', 'color' => '#b45309', 'bg' => '#fef3c7'];
        return ['icon' => 'fa-circle-check', 'color' => '#059669', 'bg' => '#d1fae5'];
    }
}

$isFavorited = false;
$isLoggedIn = isset($_SESSION['user']) && !empty($_SESSION['user']['MaKH']);
$currentMaKH = $isLoggedIn ? intval($_SESSION['user']['MaKH']) : 0;

if ($isLoggedIn) {
    $checkYT = DataProvider::ExecuteQuery("SELECT MaYT FROM yeuthich WHERE MaKH = $currentMaKH AND MaHoa = $maHoa");
    if ($checkYT && mysqli_num_rows($checkYT) > 0) $isFavorited = true;
} elseif (isset($_SESSION['wishlist']) && is_array($_SESSION['wishlist'])) {
    if (in_array($maHoa, array_map('intval', $_SESSION['wishlist']))) $isFavorited = true;
}

$reviewError = '';
$reviewSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnGuiDanhGia'])) {
    if (!$isLoggedIn) {
        $reviewError = 'Vui lòng đăng nhập để gửi đánh giá!';
    } else {
        $soSao = max(1, min(5, intval($_POST['rating_stars'] ?? 5)));
        $noiDung = trim($_POST['txtNoiDungDG'] ?? '');

        if (empty($noiDung)) {
            $reviewError = 'Vui lòng nhập nội dung nhận xét của bạn!';
        } else {
            $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            mysqli_set_charset($conn, "utf8mb4");
            $ndEsc = mysqli_real_escape_string($conn, $noiDung);
            $insDG = "INSERT INTO danhgia (MaHoa, MaKH, SoSao, NoiDung) VALUES ($maHoa, $currentMaKH, $soSao, '$ndEsc')";
            if (mysqli_query($conn, $insDG)) {
                $reviewSuccess = 'Cảm ơn bạn đã gửi đánh giá cho sản phẩm hoa này!';
            } else {
                $reviewError = 'Có lỗi xảy ra: ' . mysqli_error($conn);
            }
            mysqli_close($conn);
        }
    }
}

$rsAvg = DataProvider::ExecuteQuery("SELECT IFNULL(AVG(SoSao), 5) AS DiemTB, COUNT(*) AS SoLuongDG FROM danhgia WHERE MaHoa = $maHoa");
$rowAvg = mysqli_fetch_assoc($rsAvg);
$diemTB = round(floatval($rowAvg['DiemTB']), 1);
$soLuongDG = intval($rowAvg['SoLuongDG']);

$sqlDG = "SELECT dg.*, kh.HoTen FROM danhgia dg JOIN khachhang kh ON dg.MaKH = kh.MaKH WHERE dg.MaHoa = $maHoa ORDER BY dg.NgayDG DESC";
$rsDG = DataProvider::ExecuteQuery($sqlDG);
?>

<style>
.detail-container {
    display: flex;
    gap: 40px;
    background: #fff;
    padding: 35px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    margin-bottom: 35px;
}
.detail-image {
    flex: 1;
    max-width: 440px;
    overflow: hidden;
    border-radius: 12px;
}
.detail-image img {
    width: 100%;
    height: 420px;
    object-fit: cover;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
.detail-image:hover img {
    transform: scale(1.08);
}
.detail-info {
    flex: 1.2;
    display: flex;
    flex-direction: column;
}
.rating-stars-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #fffbeb;
    border: 1px solid #fef3c7;
    padding: 6px 14px;
    border-radius: 20px;
    margin-bottom: 15px;
    width: fit-content;
}
.star-gold {
    color: #f59e0b;
    font-size: 14px;
}
.star-gray {
    color: #cbd5e1;
    font-size: 14px;
}
.btn-wishlist-detail {
    background: #fff0f2;
    color: #e11d48;
    border: 1px solid #fecdd3;
    padding: 12px 20px;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.btn-wishlist-detail:hover {
    background: #ffe4e6;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(225, 29, 72, 0.2);
}
.btn-wishlist-detail.active {
    background: #e11d48;
    color: #fff;
    border-color: #e11d48;
}

.reviews-section {
    background: #fff;
    padding: 35px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}
.rating-picker i {
    font-size: 26px;
    color: #cbd5e1;
    cursor: pointer;
    transition: transform 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275), color 0.15s ease;
    display: inline-block;
    padding: 0 3px;
}
.rating-picker i:hover {
    transform: scale(1.35) rotate(-6deg);
    color: #f59e0b;
}
.rating-picker i.active {
    color: #f59e0b;
    transform: scale(1.1);
}
.review-item {
    border-bottom: 1px solid #f1f5f9;
    padding: 18px 0;
    display: flex;
    gap: 15px;
}
.review-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.detail-meta-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
}
.detail-category-badge {
    background: #fff1f2;
    color: #e11d48;
    border: 1px solid #fecdd3;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    letter-spacing: 0.2px;
}
.detail-sku-badge {
    background: #f8fafc;
    color: #64748b;
    border: 1px solid #e2e8f0;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}
.detail-stock-badge {
    background: #f0fdf4;
    color: #16a34a;
    border: 1px solid #bbf7d0;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.detail-product-title {
    font-size: 28px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 12px;
    line-height: 1.3;
}

.detail-price-box {
    background: linear-gradient(135deg, #fff5f5 0%, #fff1f2 100%);
    border: 1px solid #ffe4e6;
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 22px;
    box-shadow: 0 2px 10px rgba(225, 29, 72, 0.04);
}
.detail-price-main {
    font-size: 28px;
    font-weight: 800;
    color: #e11d48;
    letter-spacing: -0.5px;
}
.detail-price-note {
    font-size: 13px;
    color: #64748b;
    margin-left: 8px;
    font-weight: 400;
}
.detail-price-perk {
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px dashed rgba(225, 29, 72, 0.2);
    font-size: 13px;
    color: #059669;
    display: flex;
    align-items: center;
    gap: 7px;
    font-weight: 500;
}

.flower-recipe-card {
    background: #ffffff;
    border: 1px solid #fce7f3;
    border-radius: 14px;
    padding: 20px 22px;
    margin-bottom: 22px;
    box-shadow: 0 4px 18px rgba(225, 29, 72, 0.05);
    position: relative;
    overflow: hidden;
}
.flower-recipe-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(to bottom, #f43f5e, #fda4af);
}
.recipe-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 14px;
    border-bottom: 1px dashed #fce7f3;
    padding-bottom: 12px;
}
.recipe-icon-circle {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #ffe4e6;
    color: #e11d48;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.recipe-header-title {
    font-size: 15.5px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 2px;
}
.recipe-header-subtitle {
    font-size: 12.5px;
    color: #64748b;
    margin: 0;
}
.recipe-chips-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
}
.ingredient-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #fdf2f8;
    border: 1px solid #fbcfe8;
    color: #4a044e;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 13.5px;
    font-weight: 500;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.ingredient-chip:hover {
    background: #fce7f3;
    border-color: #f472b6;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(225, 29, 72, 0.12);
}
.ingredient-chip .chip-icon {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 10.5px;
}
.recipe-card-footer {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
    color: #64748b;
    background: #f8fafc;
    padding: 8px 12px;
    border-radius: 8px;
}

.service-guarantees {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 22px;
}
.guarantee-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
    color: #334155;
}
.guarantee-item i {
    color: #e11d48;
    font-size: 13px;
    flex-shrink: 0;
}
</style>

<div class="detail-container" data-sg-reveal>
    <div class="detail-image">
        <img id="detail-main-img" src="./hoa/<?php echo $hinh; ?>" alt="<?php echo $tenHoa; ?>" onerror="this.src='images/no-image.png';">
    </div>
    
    <div class="detail-info">
        <div class="detail-meta-row">
            <span class="detail-category-badge">
                <i class="fa-solid <?php echo getLoaiHoaIcon($tenLoai); ?>"></i> <?php echo $tenLoai; ?>
            </span>
            <span class="detail-sku-badge">Mã SP: #FL-<?php echo sprintf('%03d', $maHoa); ?></span>
            <span class="detail-stock-badge"><i class="fa-solid fa-circle-check"></i> Hoa tươi có sẵn</span>
        </div>

        <h1 class="detail-product-title"><?php echo $tenHoa; ?></h1>

        <div class="rating-stars-badge">
            <span class="star-gold">
                <?php for($i = 1; $i <= 5; $i++): ?>
                    <i class="fa-solid fa-star<?php echo ($i > round($diemTB)) ? ' star-gray' : ''; ?>"></i>
                <?php endfor; ?>
            </span>
            <strong style="color: #b45309; font-size: 14px;"><?php echo ($soLuongDG > 0) ? $diemTB . '/5' : '5.0/5'; ?></strong>
            <span style="color: #64748b; font-size: 13px;">(<?php echo $soLuongDG; ?> đánh giá từ khách hàng)</span>
        </div>
        
        <div class="detail-price-box">
            <div style="display: flex; align-items: baseline; flex-wrap: wrap;">
                <span class="detail-price-main"><?php echo $giaBan; ?> đ</span>
                <span class="detail-price-note">/ Sản phẩm hoàn thiện</span>
            </div>
            <div class="detail-price-perk">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Tặng kèm thiệp thiết kế cao cấp & banner chúc mừng theo yêu cầu
            </div>
        </div>
        
        
        <div class="flower-recipe-card">
            <div class="recipe-card-header">
                <div class="recipe-icon-circle">
                    <i class="fa-solid fa-seedling"></i>
                </div>
                <div>
                    <h4 class="recipe-header-title">Thành phần thiết kế hoa tươi</h4>
                    <p class="recipe-header-subtitle">Nghệ nhân Sen Garden tuyển chọn hoa loại 1 và cắm thủ công</p>
                </div>
            </div>

            <?php if (!empty($rawIngredients)): ?>
                <div class="recipe-chips-grid">
                    <?php foreach ($rawIngredients as $ingredient): 
                        $icData = getIngredientIconData($ingredient);
                    ?>
                        <span class="ingredient-chip">
                            <span class="chip-icon" style="background: <?php echo $icData['bg']; ?>; color: <?php echo $icData['color']; ?>;">
                                <i class="fa-solid <?php echo $icData['icon']; ?>"></i>
                            </span>
                            <span><?php echo htmlspecialchars($ingredient); ?></span>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: #64748b; margin-bottom: 12px; font-size: 14px;">
                    <?php echo $thanhPhan ?: 'Hoa tươi chọn lọc cao cấp theo tiêu chuẩn của Sen Garden.'; ?>
                </p>
            <?php endif; ?>

            <div class="recipe-card-footer">
                <i class="fa-solid fa-droplet" style="color: #0ea5e9;"></i>
                <span>Tặng kèm gói dưỡng hoa Chrysal & hướng dẫn giữ hoa tươi lâu tại nhà</span>
            </div>
        </div>

        
        <div class="service-guarantees">
            <div class="guarantee-item">
                <i class="fa-solid fa-circle-check"></i>
                <span>Hoa tươi loại 1 nhập mới mỗi ngày</span>
            </div>
            <div class="guarantee-item">
                <i class="fa-solid fa-camera"></i>
                <span>Chụp ảnh thực tế trước khi giao hàng</span>
            </div>
            <div class="guarantee-item">
                <i class="fa-solid fa-truck-fast"></i>
                <span>Giao nhanh 60-90 phút nội thành</span>
            </div>
            <div class="guarantee-item">
                <i class="fa-solid fa-shield-heart"></i>
                <span>Đổi mới 100% nếu hoa không đạt chuẩn</span>
            </div>
        </div>
        
        <div style="margin-bottom: 25px; display: flex; align-items: center; gap: 14px;">
            <label style="font-weight: 600; color: #334155;">Số lượng:</label>
            <div style="display: inline-flex; align-items: center; border: 1px solid #cbd5e1; border-radius: 6px; overflow: hidden; background: #fff;">
                <button type="button" id="btn-qty-minus" style="width: 36px; height: 36px; border: none; background: #f1f5f9; cursor: pointer; font-size: 18px; font-weight: bold;">-</button>
                <input type="number" id="detail-qty" min="1" max="99" value="1" style="width: 55px; height: 36px; border: none; text-align: center; font-weight: bold; font-size: 16px; outline: none;">
                <button type="button" id="btn-qty-plus" style="width: 36px; height: 36px; border: none; background: #f1f5f9; cursor: pointer; font-size: 18px; font-weight: bold;">+</button>
            </div>
        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
            <a href="MyCart.php?action=add&id=<?php echo $maHoa; ?>" id="btn-detail-addcart" class="btn btn-cart" style="padding: 13px 28px; font-size: 15px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ hàng
            </a>

            <button type="button" id="btnDetailWishlist" class="btn-wishlist-detail <?php echo $isFavorited ? 'active' : ''; ?>" data-id="<?php echo $maHoa; ?>">
                <i class="<?php echo $isFavorited ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
                <span id="txtWishlist"><?php echo $isFavorited ? 'Đã yêu thích' : 'Yêu thích'; ?></span>
            </button>

            <a href="index.php" class="btn btn-detail" style="padding: 13px 20px; font-size: 15px; text-decoration: none;">
                Tiếp tục mua hoa
            </a>
        </div>

        <div id="toast-notify" style="display: none; margin-top: 18px; padding: 12px 18px; background: #f0fdf4; border: 1px solid #86efac; color: #16a34a; border-radius: 6px; font-weight: 500;">
            ✔ Đã thêm sản phẩm vào giỏ hàng thành công!
        </div>
    </div>
</div>

<div class="reviews-section" id="reviews-section" data-sg-reveal>
    <div style="border-bottom: 2px solid #f1f5f9; padding-bottom: 16px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <h2 style="margin: 0; color: #1e293b; font-size: 22px;">
            <i class="fa-solid fa-comments" style="color: #3b82f6;"></i> Đánh Giá & Nhận Xét Từ Khách Hàng
        </h2>
        <div style="font-size: 14px; color: #64748b;">
            Điểm đánh giá: <strong style="color: #f59e0b; font-size: 18px;"><?php echo ($soLuongDG > 0) ? $diemTB : '5.0'; ?> / 5 ⭐</strong> (<?php echo $soLuongDG; ?> lượt)
        </div>
    </div>

    <?php if (!empty($reviewError)): ?>
        <div style="background: #fef2f2; color: #991b1b; padding: 12px 16px; border: 1px solid #fecaca; border-radius: 6px; margin-bottom: 20px;">
            <?php echo $reviewError; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($reviewSuccess) || isset($_GET['review'])): ?>
        <div style="background: #f0fdf4; color: #16a34a; padding: 12px 16px; border: 1px solid #86efac; border-radius: 6px; margin-bottom: 20px;">
            ✔ Cảm ơn bạn đã gửi đánh giá cho sản phẩm hoa này!
        </div>
    <?php endif; ?>

    
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 22px; margin-bottom: 30px;">
        <h3 style="margin: 0 0 12px; font-size: 16px; color: #1e293b;">
            <i class="fa-solid fa-pen-to-square"></i> Viết nhận xét của bạn
        </h3>

        <?php if (!$isLoggedIn): ?>
            <p style="color: #64748b; font-size: 14px; margin: 0;">
                Vui lòng <a href="dangnhap.php" style="color: #e30019; font-weight: 600;">Đăng nhập</a> để chia sẻ cảm nhận về bó hoa này.
            </p>
        <?php else: ?>
            <form method="post" action="chitiet.php?MaHoa=<?php echo $maHoa; ?>#reviews-section">
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 14px; font-weight: 600; color: #334155; margin-bottom: 6px;">Chọn mức độ hài lòng:</label>
                    <div class="rating-picker" id="starPicker">
                        <i class="fa-solid fa-star active" data-val="1"></i>
                        <i class="fa-solid fa-star active" data-val="2"></i>
                        <i class="fa-solid fa-star active" data-val="3"></i>
                        <i class="fa-solid fa-star active" data-val="4"></i>
                        <i class="fa-solid fa-star active" data-val="5"></i>
                        <span id="starText" style="margin-left: 10px; font-size: 14px; font-weight: 600; color: #f59e0b;">5 sao - Tuyệt vời</span>
                    </div>
                    <input type="hidden" name="rating_stars" id="inputStars" value="5">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 14px; font-weight: 600; color: #334155; margin-bottom: 6px;">Nội dung nhận xét:</label>
                    <textarea name="txtNoiDungDG" rows="3" required placeholder="Chia sẻ cảm nhận về độ tươi, cách cắm hoa, thời gian giao hoa..." style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-family: inherit; font-size: 14px;"></textarea>
                </div>

                <button type="submit" name="btnGuiDanhGia" class="btn btn-cart" style="padding: 10px 22px; font-size: 14px; font-weight: 600;">
                    <i class="fa-solid fa-paper-plane"></i> Gửi đánh giá
                </button>
            </form>
        <?php endif; ?>
    </div>

    
    <div>
        <h3 style="margin: 0 0 15px; font-size: 16px; color: #1e293b;">
            Các nhận xét gần đây (<?php echo $soLuongDG; ?>)
        </h3>

        <?php if ($soLuongDG == 0): ?>
            <p style="color: #94a3b8; font-style: italic; margin: 0; padding: 20px 0;">Chưa có nhận xét nào cho mẫu hoa này. Hãy là người đầu tiên để lại đánh giá!</p>
        <?php else: ?>
            <?php while ($dg = mysqli_fetch_assoc($rsDG)): ?>
                <div class="review-item">
                    <div class="review-avatar">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; flex-wrap: wrap;">
                            <strong style="color: #1e293b; font-size: 15px;"><?php echo htmlspecialchars($dg['HoTen']); ?></strong>
                            <span style="color: #94a3b8; font-size: 12px;"><?php echo date('d/m/Y H:i', strtotime($dg['NgayDG'])); ?></span>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <i class="fa-solid fa-star <?php echo ($s <= $dg['SoSao']) ? 'star-gold' : 'star-gray'; ?>" style="font-size: 12px;"></i>
                            <?php endfor; ?>
                        </div>
                        <p style="margin: 0; color: #475569; font-size: 14px; line-height: 1.5;">
                            <?php echo nl2br(htmlspecialchars($dg['NoiDung'])); ?>
                        </p>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const qtyInput = document.getElementById('detail-qty');
    const btnMinus = document.getElementById('btn-qty-minus');
    const btnPlus = document.getElementById('btn-qty-plus');
    const btnAddCart = document.getElementById('btn-detail-addcart');
    const mainImg = document.getElementById('detail-main-img');
    const toast = document.getElementById('toast-notify');
    const btnWish = document.getElementById('btnDetailWishlist');
    const txtWish = document.getElementById('txtWishlist');

    if (btnMinus && qtyInput) {
        btnMinus.addEventListener('click', function () {
            let v = parseInt(qtyInput.value) || 1;
            if (v > 1) qtyInput.value = v - 1;
        });
    }

    if (btnPlus && qtyInput) {
        btnPlus.addEventListener('click', function () {
            let v = parseInt(qtyInput.value) || 1;
            if (v < 99) qtyInput.value = v + 1;
        });
    }

    if (btnWish) {
        btnWish.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            fetch('ajax_wishlist.php?action=toggle&id=' + id)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const icon = btnWish.querySelector('i');
                        if (data.action === 'added') {
                            btnWish.classList.add('active');
                            icon.className = 'fa-solid fa-heart';
                            if (txtWish) txtWish.textContent = 'Đã yêu thích';
                            alert('Đã thêm vào danh sách hoa yêu thích ❤️');
                        } else {
                            btnWish.classList.remove('active');
                            icon.className = 'fa-regular fa-heart';
                            if (txtWish) txtWish.textContent = 'Yêu thích';
                            alert('Đã xóa khỏi danh sách hoa yêu thích');
                        }
                    }
                })
                .catch(err => console.error(err));
        });
    }

    const starPicker = document.getElementById('starPicker');
    if (starPicker) {
        const stars = starPicker.querySelectorAll('i');
        const inputStars = document.getElementById('inputStars');
        const starText = document.getElementById('starText');
        const starDesc = [
            '',
            '1 sao - Rất không hài lòng',
            '2 sao - Tạm được',
            '3 sao - Bình thường',
            '4 sao - Hài lòng',
            '5 sao - Tuyệt vời'
        ];

        stars.forEach(star => {
            star.addEventListener('click', function () {
                const val = parseInt(this.getAttribute('data-val'));
                inputStars.value = val;
                starText.textContent = starDesc[val];
                stars.forEach(s => {
                    const sVal = parseInt(s.getAttribute('data-val'));
                    if (sVal <= val) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            });
        });
    }

    function getCartLink() {
        return document.querySelector('a[href*="GioHang"], a[href*="giohang"]');
    }

    if (btnAddCart) {
        btnAddCart.addEventListener('click', function (e) {
            e.preventDefault();
            const qty = parseInt(qtyInput.value) || 1;
            const baseUrl = this.getAttribute('href');
            const targetUrl = baseUrl + '&qty=' + qty;

            const cartNav = getCartLink();
            if (mainImg) {
                const imgRect = mainImg.getBoundingClientRect();
                const cartRect = cartNav 
                    ? cartNav.getBoundingClientRect() 
                    : { top: 20, left: window.innerWidth - 120, width: 40, height: 30 };

                const flyer = mainImg.cloneNode();
                flyer.style.position = 'fixed';
                flyer.style.zIndex = '9999999';
                flyer.style.borderRadius = '50%';
                flyer.style.border = '2px solid #e30019';
                flyer.style.boxShadow = '0 6px 20px rgba(0,0,0,0.3)';
                flyer.style.objectFit = 'cover';
                flyer.style.pointerEvents = 'none';
                flyer.style.top = imgRect.top + 'px';
                flyer.style.left = imgRect.left + 'px';
                flyer.style.width = '140px';
                flyer.style.height = '140px';
                flyer.style.transition = 'all 0.8s cubic-bezier(0.2, 0.9, 0.3, 1)';

                document.body.appendChild(flyer);
                void flyer.offsetWidth;

                flyer.style.top = (cartRect.top + 5) + 'px';
                flyer.style.left = (cartRect.left + 15) + 'px';
                flyer.style.width = '24px';
                flyer.style.height = '24px';
                flyer.style.opacity = '0.2';
                flyer.style.transform = 'rotate(360deg)';

                setTimeout(() => { flyer.remove(); }, 800);
            }

            fetch(targetUrl)
                .then(() => {
                    const cartCountElem = document.getElementById('cart-count');
                    if (cartCountElem) {
                        const current = parseInt(cartCountElem.textContent) || 0;
                        cartCountElem.textContent = current + qty;
                    }

                    if (toast) {
                        toast.style.display = 'block';
                        setTimeout(() => { toast.style.display = 'none'; }, 3000);
                    }
                })
                .catch(err => console.error(err));
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>