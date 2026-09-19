<?php
require_once 'includes/header.php';
include_once 'DataProvider.php';

$isLoggedIn = isset($_SESSION['user']) && !empty($_SESSION['user']['MaKH']);
$maKH = $isLoggedIn ? intval($_SESSION['user']['MaKH']) : 0;

$wishlistIds = [];
if ($isLoggedIn) {
    $rsYT = DataProvider::ExecuteQuery("SELECT MaHoa FROM yeuthich WHERE MaKH = $maKH");
    while ($r = mysqli_fetch_assoc($rsYT)) {
        $wishlistIds[] = intval($r['MaHoa']);
    }
} else {
    $wishlistIds = isset($_SESSION['wishlist']) && is_array($_SESSION['wishlist']) ? array_map('intval', $_SESSION['wishlist']) : [];
}

$flowers = [];
if (!empty($wishlistIds)) {
    $idList = implode(',', $wishlistIds);
    $sql = "SELECT h.*, lh.TenLoai FROM hoa h JOIN loaihoa lh ON h.MaLoai = lh.MaLoai WHERE h.MaHoa IN ($idList) ORDER BY h.MaHoa DESC";
    $rsFlowers = DataProvider::ExecuteQuery($sql);
    if ($rsFlowers) {
        while ($f = mysqli_fetch_assoc($rsFlowers)) {
            $flowers[] = $f;
        }
    }
}
?>

<style>
.wishlist-wrapper {
    max-width: 1100px;
    margin: 0 auto;
    padding: 10px;
}
.wishlist-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #f1f5f9;
    padding-bottom: 16px;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 12px;
}
.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 24px;
}
.wishlist-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    position: relative;
    transition: transform 0.2s, box-shadow 0.2s;
}
.wishlist-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}
.wishlist-img-wrap {
    height: 200px;
    position: relative;
    overflow: hidden;
}
.wishlist-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.btn-remove-wishlist {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(255, 255, 255, 0.9);
    border: none;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    color: #e30019;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    transition: all 0.2s;
}
.btn-remove-wishlist:hover {
    background: #e30019;
    color: #fff;
    transform: scale(1.1);
}
.wishlist-body {
    padding: 16px;
    display: flex;
    flex-direction: column;
    flex: 1;
    justify-content: space-between;
}
.wishlist-title {
    font-weight: 600;
    font-size: 16px;
    color: #1e293b;
    margin-bottom: 6px;
}
.wishlist-cat {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 10px;
}
.wishlist-price {
    font-size: 18px;
    font-weight: 700;
    color: #e30019;
    margin-bottom: 14px;
}
.wishlist-actions {
    display: flex;
    gap: 8px;
}
.wishlist-actions .btn {
    flex: 1;
    text-align: center;
    padding: 8px 10px;
    font-size: 13px;
}
</style>

<div class="wishlist-wrapper">
    <div class="wishlist-header" data-sg-reveal>
        <div>
            <h2 style="margin: 0; color: #1e293b; font-size: 24px;">
                <i class="fa-solid fa-heart" style="color: #e11d48;"></i> Danh Sách Hoa Yêu Thích
            </h2>
            <p style="margin: 6px 0 0; color: #64748b; font-size: 14px;">
                Bộ sưu tập những mẫu hoa bạn đã lưu lại để tham khảo và đặt mua sau.
            </p>
        </div>
        <div style="font-size: 14px; color: #475569;">
            Tổng cộng: <strong id="wishlistTotalCount" style="color: #e11d48; font-size: 18px;"><?php echo count($flowers); ?></strong> mẫu hoa
        </div>
    </div>

    <?php if (empty($flowers)): ?>
        <div style="text-align: center; padding: 60px 20px; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0;" data-sg-reveal>
            <div style="font-size: 48px; color: #cbd5e1; margin-bottom: 15px;"><i class="fa-regular fa-heart"></i></div>
            <h3 style="color: #334155; margin-bottom: 8px;">Danh sách yêu thích của bạn đang trống!</h3>
            <p style="color: #64748b; margin-bottom: 22px;">Hãy bấm vào biểu tượng trái tim trên các sản phẩm hoa để lưu lại nhé.</p>
            <a href="index.php" class="btn btn-cart" style="text-decoration: none; padding: 12px 24px;">
                <i class="fa-solid fa-store"></i> Khám phá hoa tươi ngay
            </a>
        </div>
    <?php else: ?>
        <div class="wishlist-grid" id="wishlistContainer">
            <?php foreach ($flowers as $idx => $hoa): ?>
                <div class="wishlist-card sg-delay-<?php echo ($idx % 6) + 1; ?>" id="wishCard-<?php echo $hoa['MaHoa']; ?>" data-sg-reveal>
                    <div class="wishlist-img-wrap">
                        <img src="hoa/<?php echo htmlspecialchars($hoa['Hinh']); ?>" alt="<?php echo htmlspecialchars($hoa['TenHoa']); ?>" onerror="this.src='images/no-image.png';">
                        <button type="button" class="btn-remove-wishlist" data-id="<?php echo $hoa['MaHoa']; ?>" title="Xóa khỏi yêu thích">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                    <div class="wishlist-body">
                        <div>
                            <div class="wishlist-title">
                                <a href="chitiet.php?MaHoa=<?php echo $hoa['MaHoa']; ?>" style="color: inherit;">
                                    <?php echo htmlspecialchars($hoa['TenHoa']); ?>
                                </a>
                            </div>
                            <div class="wishlist-cat">Danh mục: <?php echo htmlspecialchars($hoa['TenLoai']); ?></div>
                        </div>
                        <div>
                            <div class="wishlist-price"><?php echo number_format($hoa['GiaBan']); ?> đ</div>
                            <div class="wishlist-actions">
                                <a href="chitiet.php?MaHoa=<?php echo $hoa['MaHoa']; ?>" class="btn btn-detail">Chi tiết</a>
                                <a href="MyCart.php?action=add&id=<?php echo $hoa['MaHoa']; ?>" class="btn btn-cart">Thêm giỏ</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const removeButtons = document.querySelectorAll('.btn-remove-wishlist');
    removeButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi danh sách yêu thích?')) return;

            fetch('ajax_wishlist.php?action=toggle&id=' + id)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const card = document.getElementById('wishCard-' + id);
                        if (card) {
                            card.style.transition = 'all 0.3s ease';
                            card.style.opacity = '0';
                            card.style.transform = 'scale(0.8)';
                            setTimeout(() => {
                                card.remove();
                                const countElem = document.getElementById('wishlistTotalCount');
                                if (countElem) countElem.textContent = data.count;
                                if (data.count === 0) location.reload();
                            }, 300);
                        }
                    }
                })
                .catch(err => console.error(err));
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
