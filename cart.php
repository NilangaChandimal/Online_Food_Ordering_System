<?php
session_start();
require 'includes/db.php';
require './includes/customer_nav.php';

$settingStmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'currency_symbol'");
$settingStmt->execute();
$currency = $settingStmt->fetchColumn();
if (!$currency) $currency = '$'; 

$chargeStmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'delivery_charge'");
$chargeStmt->execute();
$delivery_charge = floatval($chargeStmt->fetchColumn());
if (!$delivery_charge) $delivery_charge = 0.00;



if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['food_id'], $_POST['quantity'])) {
    $food_id  = intval($_POST['food_id']);
    $quantity = intval($_POST['quantity']);

    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND food_item_id = ?");
    $stmt->execute([$user_id, $food_id]);

    if ($stmt->rowCount() > 0) {
        $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND food_item_id = ?")
            ->execute([$quantity, $user_id, $food_id]);
    } else {
        $pdo->prepare("INSERT INTO cart (user_id, food_item_id, quantity) VALUES (?, ?, ?)")
            ->execute([$user_id, $food_id, $quantity]);
    }

    $redirect = $_POST['redirect'] ?? 'menu.php';
    header("Location: $redirect");
    exit;
}

$stmt = $pdo->prepare("
    SELECT c.id, f.name, f.price, c.quantity
    FROM cart c
    JOIN food_items f ON c.food_item_id = f.id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();
$total = 0;

$addrStmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ?");
$addrStmt->execute([$user_id]);
$addresses = $addrStmt->fetchAll();

if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?")->execute([$deleteId, $user_id]);
    header("Location: cart.php");
    exit;
}

if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?")->execute([$deleteId, $user_id]);
    header("Location: cart.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Ordering System - Cart</title>
    <style>
        
        :root {
          --primary-color: #ff6b6b;
          --secondary-color: #4ecdc4;
          --accent-color: #ffd166;
          --dark-color: #2d3436;
          --light-color: #f8f9fa;
          --success-color: #06d6a0;
          --danger-color: #ef476f;
          --gray-color: #6c757d;
          --border-radius: 8px;
          --box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }
        
        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
          font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
          background-color: #f5f7fa;
          color: var(--dark-color);
          padding: 20px;
        }
        
        .container {
          max-width: 1200px;
          margin: 0 auto;
          padding: 20px;
        }
        
        h2 {
          font-size: 2.2rem;
          color: var(--dark-color);
          margin-bottom: 25px;
          text-align: center;
          position: relative;
        }
        
        h2:after {
          content: '';
          position: absolute;
          bottom: -10px;
          left: 50%;
          transform: translateX(-50%);
          width: 100px;
          height: 3px;
          background-color: var(--primary-color);
          border-radius: 3px;
        }
        
        h3 {
          font-size: 1.3rem;
          margin: 25px 0 15px;
          color: var(--dark-color);
        }
        
        p:first-of-type:only-of-type {
          text-align: center;
          font-size: 1.2rem;
          color: var(--gray-color);
          padding: 40px 20px;
          background-color: white;
          border-radius: var(--border-radius);
          box-shadow: var(--box-shadow);
          margin-bottom: 20px;
        }
        
        .cart-table {
          width: 100%;
          border-collapse: collapse;
          margin-bottom: 30px;
          background-color: white;
          box-shadow: var(--box-shadow);
          border-radius: var(--border-radius);
          overflow: hidden;
        }
        
        .cart-table thead {
          background-color: var(--primary-color);
          color: white;
        }
        
        .cart-table th {
          padding: 15px;
          text-align: left;
          font-weight: 600;
          text-transform: uppercase;
          font-size: 0.9rem;
          letter-spacing: 0.5px;
        }
        
        .cart-table td {
          padding: 15px;
          border-bottom: 1px solid #edf2f7;
        }
        
        .cart-table tbody tr:last-child td {
          border-bottom: none;
        }
        
        .cart-table tbody tr:hover {
          background-color: #f9fafb;
        }
        
        .delete-btn {
          display: inline-block;
          padding: 8px 12px;
          background-color: var(--danger-color);
          color: white;
          text-decoration: none;
          border-radius: 4px;
          font-size: 0.9rem;
          transition: all 0.3s ease;
        }
        
        .delete-btn:hover {
          background-color: #d64161;
          transform: translateY(-2px);
          box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .totals {
          background-color: white;
          padding: 20px;
          border-radius: var(--border-radius);
          box-shadow: var(--box-shadow);
          margin-bottom: 30px;
        }
        
        .totals p {
          display: flex;
          justify-content: space-between;
          margin-bottom: 10px;
          padding: 5px 0;
          font-size: 1.05rem;
        }
        
        .totals p:last-child {
          margin-top: 15px;
          padding-top: 15px;
          border-top: 2px dashed #edf2f7;
          font-size: 1.4rem;
          font-weight: 700;
          color: var(--primary-color);
        }
        
        p strong {
          display: block;
          text-align: center;
          padding: 5px;
          background-color:rgb(20, 228, 131);
          border: 1px solid #ffeeba;
          color:rgb(7, 6, 2);
          border-radius: var(--border-radius);
          margin-bottom: 5px;
        }
        
        p strong a {
          color: var(--primary-color);
          text-decoration: none;
          font-weight: 700;
        }
        
        p strong a:hover {
          text-decoration: underline;
        }
        
        form {
          background-color: white;
          padding: 25px;
          border-radius: var(--border-radius);
          box-shadow: var(--box-shadow);
        }
        
        form label {
          display: block;
          padding: 12px 15px;
          margin-bottom: 10px;
          background-color: #f8f9fa;
          border-radius: 6px;
          cursor: pointer;
          transition: all 0.2s ease;
        }
        
        form label:hover {
          background-color: #e9ecef;
        }
        
        form input[type="radio"] {
          margin-right: 10px;
          transform: scale(1.2);
          accent-color: var(--primary-color);
        }
        
        form button[type="submit"] {
          display: block;
          width: 30%;
          padding: 15px;
          margin-top: 25px;
          background-color: var(--primary-color);
          color: white;
          border: none;
          border-radius: 16px;
          font-size: 1.1rem;
          font-weight: 600;
          cursor: pointer;
          transition: all 0.3s ease;
          box-shadow: 0 4px 6px rgba(255, 107, 107, 0.2);
          position: relative;
          left: 350px;
        }
        
        form button[type="submit"]:hover {
          background-color: #ff5252;
          transform: translateY(-2px);
          box-shadow: 0 6px 12px rgba(255, 107, 107, 0.3);
        }
        
        @media (max-width: 768px) {
          .cart-table {
            display: block;
            overflow-x: auto;
          }
          
          .cart-table th, 
          .cart-table td {
            padding: 12px 10px;
          }
          
          .delete-btn {
            padding: 6px 10px;
            font-size: 0.8rem;
          }
          
          form {
            padding: 20px 15px;
          }
        
          .totals p {
            flex-direction: column;
          }
        
          .totals p strong {
            margin-bottom: 5px;
            text-align: left;
            background-color: transparent;
            border: none;
            padding: 0;
          }
        }
        
        @media (max-width: 480px) {
          h2 {
            font-size: 1.8rem;
          }
          
          .totals p:last-child {
            font-size: 1.2rem;
          }
          
          form button[type="submit"] {
            padding: 12px;
            font-size: 1rem;
          }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your Cart</h2>

        <?php if (count($items) === 0): ?>
            <p>Your cart is empty.</p>
        <?php else: ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): 
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= $currency ?><?= number_format($item['price'], 2) ?></td>
                        <td><?= $currency ?><?= number_format($subtotal, 2) ?></td>
                        <td>
                            <a href="?delete=<?= $item['id'] ?>" onclick="return confirm('Remove this item from cart?')" class="delete-btn">🗑 Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="totals">
                <p><strong>Subtotal:</strong> <?= $currency ?><?= number_format($total, 2) ?></p>
                <p><strong>Delivery:</strong> <?= $currency ?><?= number_format($delivery_charge, 2) ?></p>
                <p><strong>Grand Total:</strong> <?= $currency ?><?= number_format($total + $delivery_charge, 2) ?></p>
            </div>

            <?php if (count($addresses) === 0): ?>
                <p><strong>Please <a href="manage_address.php">add a delivery address</a> before placing the order.</strong></p>
            <?php else: ?>
                <form action="checkout.php" method="POST">
    <h3>Select Delivery Address:</h3>
    <?php foreach ($addresses as $addr): ?>
        <label>
            <input type="radio" name="address_id" value="<?= $addr['id'] ?>" <?= $addr['is_default'] ? 'checked' : '' ?> required>
            <?= htmlspecialchars($addr['address_line']) ?>, <?= htmlspecialchars($addr['city']) ?>, <?= htmlspecialchars($addr['state']) ?> - <?= htmlspecialchars($addr['postal_code']) ?>
        </label><br>
    <?php endforeach; ?>

    <h3>Payment Method:</h3>
    <label><input type="radio" name="payment_method" value="COD" checked> Cash on Delivery</label>
    <label><input type="radio" name="payment_method" value="Card">Card</label>

    <button type="submit">Place Order</button>
</form>

            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>