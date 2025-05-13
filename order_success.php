<?php
require './includes/customer_nav.php';
session_start();
if (!isset($_SESSION['payment_result'])) {
    header("Location: index.php");
    exit;
}
?>

<h2>Order Placed Successfully!</h2>
<p><?= $_SESSION['payment_result'] ?></p>

<a href="index.php">Back to Menu</a>

<?php unset($_SESSION['payment_result']); ?>
