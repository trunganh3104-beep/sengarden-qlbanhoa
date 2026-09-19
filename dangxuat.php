<?php
require_once 'includes/auth.php';

if (isset($_SESSION['user']['MaKH']) && isset($_SESSION['MyCart']) && is_array($_SESSION['MyCart'])) {
    syncUserCartToDb($_SESSION['user']['MaKH'], $_SESSION['MyCart']);
}

unset($_SESSION['user']);
unset($_SESSION['role']);
unset($_SESSION['MyCart']);

session_destroy();

header("Location: index.php");
exit;
?>