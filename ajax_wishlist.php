<?php
require_once __DIR__ . '/includes/db_config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

include_once 'DataProvider.php';

$maHoa = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : 'toggle';

if (!isset($_SESSION['wishlist']) || !is_array($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

$isLoggedIn = isset($_SESSION['user']) && !empty($_SESSION['user']['MaKH']);
$maKH = $isLoggedIn ? intval($_SESSION['user']['MaKH']) : 0;

if ($action === 'count') {
    $count = 0;
    if ($isLoggedIn) {
        $rs = DataProvider::ExecuteQuery("SELECT COUNT(*) AS total FROM yeuthich WHERE MaKH = $maKH");
        $row = mysqli_fetch_assoc($rs);
        $count = intval($row['total'] ?? 0);
    } else {
        $count = count($_SESSION['wishlist']);
    }
    echo json_encode(['success' => true, 'count' => $count]);
    exit;
}

if ($maHoa <= 0) {
    echo json_encode(['success' => false, 'message' => 'Mã hoa không hợp lệ']);
    exit;
}

$status = '';
if ($isLoggedIn) {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    mysqli_set_charset($conn, 'utf8');

    $check = mysqli_query($conn, "SELECT MaYT FROM yeuthich WHERE MaKH = $maKH AND MaHoa = $maHoa");
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "DELETE FROM yeuthich WHERE MaKH = $maKH AND MaHoa = $maHoa");
        $status = 'removed';
    } else {
        mysqli_query($conn, "INSERT INTO yeuthich (MaKH, MaHoa) VALUES ($maKH, $maHoa)");
        $status = 'added';
    }

    $rsCount = mysqli_query($conn, "SELECT COUNT(*) AS total FROM yeuthich WHERE MaKH = $maKH");
    $totalCount = intval(mysqli_fetch_assoc($rsCount)['total'] ?? 0);
    mysqli_close($conn);
} else {
    
    $idx = array_search($maHoa, $_SESSION['wishlist']);
    if ($idx !== false) {
        unset($_SESSION['wishlist'][$idx]);
        $_SESSION['wishlist'] = array_values($_SESSION['wishlist']);
        $status = 'removed';
    } else {
        $_SESSION['wishlist'][] = $maHoa;
        $status = 'added';
    }
    $totalCount = count($_SESSION['wishlist']);
}

echo json_encode([
    'success' => true,
    'action' => $status,
    'count' => $totalCount,
    'id' => $maHoa
]);
