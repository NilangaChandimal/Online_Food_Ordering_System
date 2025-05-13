<?php
session_start();
require 'includes/db.php';
require './includes/customer_nav.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? 'COD';

    $address_id = $_POST['address_id'] ?? null;

    $stmt = $pdo->prepare("
        SELECT c.food_item_id, c.quantity, f.price
        FROM cart c
        JOIN food_items f ON c.food_item_id = f.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cartItems = $stmt->fetchAll();

    if (empty($cartItems)) {
        echo "<div class='container'><p class='error'>Your cart is empty. <a href='index.php'>Go to Menu</a></p></div>";
        exit;
    }

    $subtotal = 0;
    foreach ($cartItems as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }

    $deliveryChargeStmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'delivery_charge'");
    $deliveryChargeStmt->execute();
    $delivery_charge = floatval($deliveryChargeStmt->fetchColumn() ?? 0);

    $total = $subtotal + $delivery_charge;

    $orderStmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, delivery_charge, payment_method, address_id) VALUES (?, ?, ?, ?, ?)");
    $orderStmt->execute([$user_id, $total, $delivery_charge, $payment_method, $address_id]);
    $order_id = $pdo->lastInsertId();

    $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, food_item_id, quantity, price_at_order_time) VALUES (?, ?, ?, ?)");
    foreach ($cartItems as $item) {
        $itemStmt->execute([
            $order_id,
            $item['food_item_id'],
            $item['quantity'],
            $item['price']
        ]);
    }

    $paymentStmt = $pdo->prepare("INSERT INTO payments (order_id, amount, method, status) VALUES (?, ?, ?, 'Paid')");
    $paymentStmt->execute([$order_id, $total, $payment_method]);

    $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);

    echo "
    <html>
    <head>
        <title>Order Confirmation</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background-color: #f8f8f8;
                padding: 30px;
                text-align: center;
            }
            .container {
                background-color: #fff;
                max-width: 600px;
                margin: auto;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 0 15px rgba(0,0,0,0.1);
            }
            h3 {
                color: #2ecc71;
            }
            p {
                font-size: 18px;
                margin: 10px 0;
            }
            a.button {
                display: inline-block;
                margin-top: 20px;
                padding: 10px 20px;
                background-color: #3498db;
                color: #fff;
                text-decoration: none;
                border-radius: 5px;
                transition: background-color 0.3s ease;
            }
            a.button:hover {
                background-color: #2980b9;
            }
            .order-id {
                font-size: 20px;
                font-weight: bold;
                color: #333;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <h3>🎉 Order Placed Successfully!</h3>
            <p class='order-id'>Order ID: #$order_id</p>
            <p>Total Paid: <strong>Rs.$total</strong> via <strong>$payment_method</strong></p>
            <a href='order_history.php' class='button'>View Order History</a>
        </div>
    </body>
    </html>";
    exit;
}
?>
