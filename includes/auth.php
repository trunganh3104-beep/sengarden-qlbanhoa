<?php
require_once __DIR__ . '/db_config.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!ob_get_level()) {
    ob_start();
}

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 1;
}

function getCurrentUser() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        $in_admin = (basename(dirname($_SERVER['PHP_SELF'])) === 'admin');
        $target = $in_admin ? '../dangnhap.php' : 'dangnhap.php';
        if (!headers_sent()) {
            header("Location: $target");
        } else {
            echo "<script>window.location.href='$target';</script>";
        }
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        $in_admin = (basename(dirname($_SERVER['PHP_SELF'])) === 'admin');
        $target = $in_admin ? '../index.php' : 'index.php';
        if (!headers_sent()) {
            header("Location: $target");
        } else {
            echo "<script>window.location.href='$target';</script>";
        }
        exit;
    }
}

function syncUserCartToDb($maKH, $cart) {
    if (!$maKH) return;
    $conn = getDbConnection();
    if (!$conn) return;
    ensureRequiredTables($conn);
    $maKH = intval($maKH);
    mysqli_query($conn, "DELETE FROM `giohang_user` WHERE `MaKH` = $maKH");
    if (is_array($cart) && !empty($cart)) {
        $stmt = $conn->prepare("INSERT INTO `giohang_user` (`MaKH`, `MaHoa`, `SoLuong`) VALUES (?, ?, ?)");
        if ($stmt) {
            foreach ($cart as $maHoa => $qty) {
                $maHoa = intval($maHoa);
                $qty = intval($qty);
                if ($maHoa > 0 && $qty > 0) {
                    $stmt->bind_param("iii", $maKH, $maHoa, $qty);
                    $stmt->execute();
                }
            }
            $stmt->close();
        }
    }
    mysqli_close($conn);
}

function loadUserCartFromDb($maKH) {
    if (!$maKH) return;
    $conn = getDbConnection();
    if (!$conn) return;
    ensureRequiredTables($conn);
    $maKH = intval($maKH);
    $res = mysqli_query($conn, "SELECT `MaHoa`, `SoLuong` FROM `giohang_user` WHERE `MaKH` = $maKH");
    if ($res && mysqli_num_rows($res) > 0) {
        if (!isset($_SESSION['MyCart']) || !is_array($_SESSION['MyCart'])) {
            $_SESSION['MyCart'] = [];
        }
        while ($row = mysqli_fetch_assoc($res)) {
            $mHoa = intval($row['MaHoa']);
            $q = intval($row['SoLuong']);
            if ($mHoa > 0 && $q > 0) {
                if (isset($_SESSION['MyCart'][$mHoa])) {
                    $_SESSION['MyCart'][$mHoa] = max($_SESSION['MyCart'][$mHoa], $q);
                } else {
                    $_SESSION['MyCart'][$mHoa] = $q;
                }
            }
        }
    }
    mysqli_close($conn);
}

function clearUserCartDb($maKH) {
    if (!$maKH) return;
    $conn = getDbConnection();
    if (!$conn) return;
    $maKH = intval($maKH);
    @mysqli_query($conn, "DELETE FROM `giohang_user` WHERE `MaKH` = $maKH");
    mysqli_close($conn);
}
?>