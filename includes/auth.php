<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function require_admin_login() {
    if (!isset($_SESSION['admin_id'])) {
        header("Location: ../admin/login.php");
        exit;
    }
}


function require_customer_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../customer/login.php");
        exit;
    }
}
?>
