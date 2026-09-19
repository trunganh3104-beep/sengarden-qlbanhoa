<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'DataProvider.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'msg' => 'Chưa đăng nhập']);
    exit;
}

$maKH = intval($_SESSION['user']['MaKH']);
$action = $_GET['action'] ?? '';

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
mysqli_set_charset($conn, "utf8mb4");
if (function_exists('ensureRequiredTables')) ensureRequiredTables($conn);

if ($action === 'get_messages') {
    $res = mysqli_query($conn, "SELECT * FROM qlchat WHERE MaKH = $maKH ORDER BY MaChat ASC");
    $list = [];
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $list[] = [
                'nguoi_gui' => $row['NguoiGui'],
                'noi_dung' => $row['NoiDung']
            ];
        }
    }
    mysqli_close($conn);
    echo json_encode($list);
    exit;
}

if ($action === 'send_message' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $noiDung = trim($_POST['noi_dung'] ?? '');
    if (!empty($noiDung)) {
        $ndEsc = mysqli_real_escape_string($conn, $noiDung);
        $insert = mysqli_query($conn, "INSERT INTO qlchat (MaKH, NguoiGui, NoiDung) VALUES ($maKH, 'khach', '$ndEsc')");
        $resStatus = $insert ? ['status' => 'success'] : ['status' => 'error', 'msg' => mysqli_error($conn)];
        mysqli_close($conn);
        echo json_encode($resStatus);
        exit;
    }
}
mysqli_close($conn);
echo json_encode(['status' => 'error', 'msg' => 'Invalid request']);