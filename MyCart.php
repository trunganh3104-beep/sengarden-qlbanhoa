<?php
require_once 'includes/auth.php';

if (!isset($_SESSION['MyCart']) || !is_array($_SESSION['MyCart'])) {
    $_SESSION['MyCart'] = [];
}

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_GET['mahoa']) ? intval($_GET['mahoa']) : 0);

if ($action === 'add' && $id > 0) {
    if (isset($_SESSION['MyCart'][$id])) {
        $_SESSION['MyCart'][$id] += 1;
    } else {
        $_SESSION['MyCart'][$id] = 1;
    }
    if (isset($_SESSION['user']['MaKH'])) {
        syncUserCartToDb($_SESSION['user']['MaKH'], $_SESSION['MyCart']);
    }
    header("Location: GioHang.php");
    exit;
}

if ($action === 'update' && $id > 0) {
    $qty = isset($_GET['qty']) ? intval($_GET['qty']) : 1;
    if ($qty > 0) {
        $_SESSION['MyCart'][$id] = $qty;
    } else {
        unset($_SESSION['MyCart'][$id]);
    }
    if (isset($_SESSION['user']['MaKH'])) {
        syncUserCartToDb($_SESSION['user']['MaKH'], $_SESSION['MyCart']);
    }
    header("Location: GioHang.php");
    exit;
}

if ($action === 'delete' && $id > 0) {
    if (isset($_SESSION['MyCart'][$id])) {
        unset($_SESSION['MyCart'][$id]);
    }
    if (isset($_SESSION['user']['MaKH'])) {
        syncUserCartToDb($_SESSION['user']['MaKH'], $_SESSION['MyCart']);
    }
    header("Location: GioHang.php");
    exit;
}

header("Location: index.php");
exit;