<?php
require '../includes/db.php';

$cats = $pdo->query("SELECT * FROM categories")->fetchAll();

$settingStmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'currency_symbol'");
$settingStmt->execute();
$currency = $settingStmt->fetchColumn();
if (!$currency) $currency = '$'; 

if (!isset($_GET['order_id'])) {
    echo "Order ID is required.";
    exit;
}

$order_id = intval($_GET['order_id']);

$orderStmt = $pdo->prepare("
    SELECT o.*, u.name, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$orderStmt->execute([$order_id]);
$order = $orderStmt->fetch();

if (!$order) {
    echo "Order not found.";
    exit;
}

$addrStmt = $pdo->prepare("
    SELECT * FROM addresses 
    WHERE id = ? AND user_id = ?
    LIMIT 1
");
$addrStmt->execute([$order['address_id'], $order['user_id']]);
$address = $addrStmt->fetch();

if (!$address) {
    echo "Address not found.";
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Food Ordering System</title>
    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f7f7f7;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .order-card {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .order-header h2 {
            color: #333;
            font-size: 24px;
            font-weight: 700;
        }
        
        .order-status {
            padding: 6px 12px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            background-color: #E6F7FF;
            color: #0070CC;
        }
        
        .order-status.processing {
            background-color: #FFF7E6;
            color: #D46B08;
        }
        
        .order-status.delivered {
            background-color: #E6F7ED;
            color: #389E0D;
        }
        
        .order-status.cancelled {
            background-color: #FFF1F0;
            color: #CF1322;
        }
        
        .order-info {
            margin-bottom: 25px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 12px;
        }
        
        .info-label {
            flex: 0 0 140px;
            font-weight: 600;
            color: #666;
        }
        
        .info-value {
            flex: 1;
            color: #333;
        }
        
        .address-section {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-top: 25px;
            border-left: 4px solid #FF6B35;
        }
        
        .address-section h3 {
            color: #333;
            font-size: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .address-section h3::before {
            content: '📍';
            margin-right: 8px;
            font-size: 20px;
        }
        
        .address-content {
            line-height: 1.8;
            color: #444;
        }
        
        .actions-section {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .back-link:hover {
            color: #FF6B35;
        }
        
        .back-link::before {
            content: '←';
            margin-right: 8px;
            font-size: 18px;
        }
        
        .primary-button {
            background-color: #FF6B35;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 20px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .primary-button:hover {
            background-color: #e85a27;
        }
        
        .no-address {
            background-color: #FFF7E6;
            border-radius: 8px;
            padding: 15px;
            color: #D46B08;
            font-style: italic;
            display: flex;
            align-items: center;
        }
        
        .no-address::before {
            content: '⚠️';
            margin-right: 10px;
            font-size: 18px;
        }
        
        .total-price {
            font-weight: 700;
            color: #FF6B35;
            font-size: 18px;
        }
        
        @media (max-width: 576px) {
            .container {
                margin: 20px auto;
            }
            
            .order-card {
                padding: 20px;
            }
            
            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .order-status {
                margin-top: 10px;
            }
            
            .info-row {
                flex-direction: column;
            }
            
            .info-label {
                margin-bottom: 5px;
            }
            
            .actions-section {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="order-card">
            <div class="order-header">
                <h2>Order #<?= htmlspecialchars($order_id) ?> Details</h2>
                <div class="order-status <?= strtolower(htmlspecialchars($order['status'])) ?>">
                    <?= htmlspecialchars($order['status']) ?>
                </div>
            </div>
            
            <div class="order-info">
                <div class="info-row">
                    <div class="info-label">Customer</div>
                    <div class="info-value"><?= htmlspecialchars($order['name']) ?> (<?= htmlspecialchars($order['email']) ?>)</div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Order Total</div>
                    <div class="info-value total-price"><?= $currency ?><?= number_format($order['total_price'], 2) ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Payment Method</div>
                    <div class="info-value"><?= htmlspecialchars($order['payment_method']) ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Order Date</div>
                    <div class="info-value"><?= isset($order['created_at']) ? htmlspecialchars($order['created_at']) : 'N/A' ?></div>
                </div>
            </div>
            
            <?php if ($address): ?>
                <div class="address-section">
                    <h3>Delivery Address</h3>
                    <div class="address-content">
                        <?= nl2br(htmlspecialchars($address['address_line'])) ?><br>
                        <?= htmlspecialchars($address['city']) ?>, 
                        <?= htmlspecialchars($address['state']) ?> - 
                        <?= htmlspecialchars($address['postal_code']) ?>
                        <br>
                        Phone: <?= htmlspecialchars($address['phone']) ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-address">
                    No default address found for this user.
                </div>
            <?php endif; ?>
            
            <div class="actions-section">
                <a href="manage_orders.php" class="back-link">Back to Orders</a>
            </div>
        </div>
    </div>
</body>
</html>