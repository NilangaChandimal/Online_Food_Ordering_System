<?php
require '../includes/auth.php';
require '../includes/db.php';
require '../includes/admin_nav.php';

$settingStmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'currency_symbol'");
$settingStmt->execute();
$currency = $settingStmt->fetchColumn();
if (!$currency) $currency = '$';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];
    $orderId = $_POST['order_id'];
    $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $orderId]);
}

$orders = $pdo->query("
    SELECT o.*, u.name AS customer
    FROM orders o
    JOIN users u ON u.id = o.user_id
    ORDER BY o.created_at DESC
")->fetchAll();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Orders</title>
    <style>

        :root {
            --primary: #ff6b35;
            --primary-light: #ff8c5f;
            --secondary: #2a9d8f;
            --secondary-light: #3ab7a7;
            --text-dark: #333;
            --text-light: #777;
            --bg-light: #f8f9fa;
            --white: #ffffff;
            --danger: #e74c3c;
            --danger-light: #f55a4a;
            --success: #27ae60;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h2 {
            color: var(--primary);
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: 600;
            border-bottom: 2px solid var(--primary-light);
            padding-bottom: 10px;
        }

        .orders-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .order-card {
            background-color: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 20px;
            position: relative;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .order-id {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
        }

        .order-customer {
            font-weight: 500;
        }

        .order-info {
            display: flex;
            justify-content: space-between;
            background-color: var(--bg-light);
            padding: 10px;
            border-radius: var(--radius);
            margin-bottom: 15px;
        }

        .order-info span {
            display: block;
            font-size: 14px;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background-color:rgb(236, 54, 30);
            color:rgb(65, 65, 59);
        }

        .status-preparing {
            background-color:rgb(245, 229, 10);
            color:rgb(65, 65, 59);
        }

        .status-delivered {
            background-color:rgb(10, 245, 69);
            color:rgb(65, 65, 59);
        }

        .order-items {
            background-color: var(--bg-light);
            border-radius: var(--radius);
            padding: 15px;
            margin-bottom: 15px;
            max-height: 150px;
            overflow-y: auto;
        }

        .order-items h4 {
            color: var(--secondary);
            margin-bottom: 10px;
            border-bottom: 1px solid var(--secondary-light);
            padding-bottom: 5px;
        }

        .order-items ul {
            list-style-type: none;
        }

        .order-items li {
            padding: 5px 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
        }

        .order-items li:last-child {
            border-bottom: none;
        }

        .update-form {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        select {
            flex-grow: 1;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: var(--radius);
            background-color: var(--white);
            font-size: 14px;
        }

        button {
            background-color: var(--secondary);
            color: var(--white);
            border: none;
            padding: 8px 15px;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }

        button:hover {
            background-color: var(--secondary-light);
        }

        .payment-method {
            text-transform: uppercase;
            font-weight: 600;
            font-size: 14px;
            color: var(--text-light);
        }

        .total-price {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 16px;
        }

        .back-link {
            display: inline-block;
            margin-top: 30px;
            text-decoration: none;
            color: var(--primary);
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .back-link:hover {
            color: var(--primary-light);
        }

        .back-link::before {
            content: "←";
            margin-right: 5px;
        }
        .details{
            background-color: var(--primary);
            color: var(--white);
            border: none;
            padding: 8px 15px;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s ease;
            text-decoration: none;

        }

        @media (max-width: 768px) {
            .orders-container {
                grid-template-columns: 1fr;
            }
            
            .order-info {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Manage Orders</h2>
        
        <div class="orders-container">
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <span class="order-id">Order #<?= $order['id'] ?></span>
                        <span class="order-customer"><?= $order['customer'] ?></span>
                    </div>
                    
                    <div class="order-info">
                        <div>
                            <span class="status-badge status-<?= $order['status'] ?>"><?= $order['status'] ?></span>
                            <span class="payment-method"><?= strtoupper($order['payment_method']) ?></span>
                        </div>
                        <span class="total-price"><?= $currency ?><?= $order['total_price'] ?></span>
                    </div>
                    
                    <div class="order-items">
                        <h4>Order Items</h4>
                        <ul>
                        <?php
                        $items = $pdo->prepare("
                            SELECT f.name, oi.quantity, f.price 
                            FROM order_items oi
                            JOIN food_items f ON f.id = oi.food_item_id
                            WHERE oi.order_id = ?
                        ");
                        $items->execute([$order['id']]);
                        foreach ($items as $item): ?>
                            <li>
                                <span><?= $item['name'] ?> × <?= $item['quantity'] ?></span>
                                <?php if(isset($item['price'])): ?>
                                <span><?= $currency ?><?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <form method="post" class="update-form">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <select name="status">
                            <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="preparing" <?= $order['status'] == 'preparing' ? 'selected' : '' ?>>Preparing</option>
                            <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                        </select>
                        <button type="submit">Update</button>
                        <a href="customer_order_details.php?order_id=<?= $order['id'] ?>" class="details">Details</a>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
        
        <a href="dashboard.php" class="back-link">Back to Dashboard</a>
    </div>
</body>
</html>