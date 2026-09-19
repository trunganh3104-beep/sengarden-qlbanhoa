<?php
require_once '../includes/auth.php';
include_once '../DataProvider.php';

requireAdmin();

if (isset($_GET['MaHoa'])) {
    $maHoa = intval($_GET['MaHoa']);

    
    $rsCheck = DataProvider::ExecuteQuery("SELECT COUNT(*) AS total FROM chitiethd WHERE MaHoa = $maHoa");
    $checkRow = mysqli_fetch_assoc($rsCheck);

    if ($checkRow['total'] > 0) {
        header("Location: quanlyhoa.php?err=Không thể xóa vì hoa này đã có trong " . $checkRow['total'] . " đơn hàng!");
        exit;
    }

    DataProvider::ExecuteQuery("DELETE FROM hoa WHERE MaHoa = $maHoa");
    header("Location: quanlyhoa.php?msg=Đã xóa hoa thành công!");
    exit;
} else {
    header("Location: quanlyhoa.php");
    exit;
}