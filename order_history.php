<?php
session_start();
require 'includes/db.php';
require './includes/customer_nav.php';

$settingStmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'currency_symbol'");
$settingStmt->execute();
$currency = $settingStmt->fetchColumn();
if (!$currency) $currency = '$'; 


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$orders->execute([$user_id]);
$orders = $orders->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Order History</title>
    <style>
        :root {
            --primary: #ff6b6b;
            --secondary: #4ecdc4;
            --dark: #292f36;
            --light: #f7f7f7;
            --gray: #ced4da;
            --light-gray: #e9ecef;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
            
            --status-pending: #ffc107;
            --status-preparing: #17a2b8;
            --status-completed:rgb(6, 243, 62);
            --status-cancelled: #dc3545;
            --status-delivered:rgb(25, 218, 41);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f9f9f9;
            color: var(--dark);
            padding: 20px;
            line-height: 1.6;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.2rem;
            color: var(--dark);
            position: relative;
            padding-bottom: 15px;
        }
        
        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--primary);
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 30px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-top: 30px;
        }
        
        .empty-state p {
            color: #6c757d;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
        
        .order-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 25px;
            transition: transform 0.3s;
        }
        
        .order-card:hover {
            transform: translateY(-5px);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background-color: var(--light);
            border-bottom: 1px solid var(--light-gray);
        }
        
        .order-id {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
        }
        
        .order-date {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
            margin-left: 10px;
        }
        
        .status-pending {
            background-color: var(--status-pending);
        }
        
        .status-preparing {
            background-color: var(--status-preparing);
        }
        
        .status-completed {
            background-color: var(--status-completed);
        }
        
        .status-cancelled {
            background-color: var(--status-cancelled);
        }
        
        .status-delivered {
            background-color: var(--status-delivered);
        }
        
        .order-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background-color: #fcfcfc;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .order-total {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--dark);
        }
        
        .order-payment {
            display: flex;
            gap: 15px;
            font-size: 0.95rem;
        }
        
        .order-payment-method, .order-delivery-charge {
            display: flex;
            align-items: center;
            color: #495057;
        }
        
        .payment-icon, .delivery-icon {
            margin-right: 5px;
            opacity: 0.7;
        }
        
        .order-details {
            padding: 20px;
        }
        
        .order-items {
            list-style-type: none;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 15px;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-name {
            font-weight: 500;
            color: var(--dark);
        }
        
        .item-price {
            color: #495057;
            font-weight: 500;
        }
        
        .item-quantity {
            background-color: var(--light);
            padding: 3px 8px;
            border-radius: 15px;
            font-size: 0.85rem;
            margin-left: 8px;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            margin-top: 20px;
            text-decoration: none;
            color: var(--dark);
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .back-link:hover {
            color: var(--primary);
        }
        
        .back-icon {
            margin-right: 8px;
        }
        
        @media (max-width: 768px) {
            .order-header, .order-summary {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .order-date, .order-payment {
                margin-top: 8px;
            }
            
            .status-badge {
                margin-left: 0;
                margin-top: 8px;
                display: inline-block;
            }
            
            .order-payment {
                flex-direction: column;
                gap: 5px;
                margin-top: 10px;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 10px;
            }
            
            .page-title {
                font-size: 1.8rem;
            }
            
            .order-item {
                flex-direction: column;
            }
            
            .item-price {
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="page-title">My Order History</h1>
        
        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <p>You haven't placed any orders yet.</p>
                <a href="menu.php" class="btn">Browse Menu</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-id">
                            Order #<?= $order['id'] ?>
                            <?php
                                $statusClass = 'status-' . strtolower($order['status']);
                            ?>
                            <span class="status-badge <?= $statusClass ?>"><?= $order['status'] ?></span>
                        </div>
                        <div class="order-date"><?= date('F j, Y \a\t g:i a', strtotime($order['created_at'])) ?></div>
                    </div>
                    
                    <div class="order-summary">
                        <div class="order-total">Total: <?= $currency ?><?= number_format($order['total_price'], 2) ?></div>
                        <div class="order-payment">
                            <div class="order-payment-method">
                                <span class="payment-icon">💳</span>
                                <?= $order['payment_method'] ?>
                            </div>
                            <div class="order-delivery-charge">
                                <span class="delivery-icon">🚚</span>
                                Delivery: <?= $currency ?><?= number_format($order['delivery_charge'], 2) ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="order-details">
                        <ul class="order-items">
                            <?php
                            $items = $pdo->prepare("
                                SELECT f.name, oi.quantity, oi.price_at_order_time 
                                FROM order_items oi
                                JOIN food_items f ON f.id = oi.food_item_id
                                WHERE oi.order_id = ?
                            ");
                            $items->execute([$order['id']]);
                            foreach ($items as $item):
                            ?>
                                <li class="order-item">
                                    <span class="item-name">
                                        <?= htmlspecialchars($item['name']) ?>
                                        <span class="item-quantity">x<?= $item['quantity'] ?></span>
                                    </span>
                                    <span class="item-price"><?= $currency ?><?= number_format($item['price_at_order_time'] * $item['quantity'], 2) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <a href="index.php" class="back-link">
                <span class="back-icon">←</span> Back to Menu
            </a>
        <?php endif; ?>
    </div>
</body>
</html>
