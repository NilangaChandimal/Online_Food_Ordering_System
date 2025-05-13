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
$stmt = $pdo->query("SELECT * FROM food_items WHERE status = 'available'");
$foods = $stmt->fetchAll();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Ordering System</title>
    <style>
        :root {
            --primary: #ff6b6b;
            --secondary: #4ecdc4;
            --dark: #292f36;
            --light: #f7f7f7;
            --gray: #ced4da;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        header h1 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        header p {
            color: var(--dark);
            font-size: 1.1rem;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .food-card {
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
            position: relative;
        }
        
        .food-card:hover {
            transform: translateY(-5px);
        }
        
        .food-image {
            height: 180px;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #aaa;
        }
        
        .food-content {
            padding: 20px;
        }
        
        .food-title {
            font-size: 1.4rem;
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        .food-description {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
            font-size: 0.95rem;
        }
        
        .food-price {
            font-weight: bold;
            font-size: 1.2rem;
            color: var(--dark);
            margin-bottom: 15px;
        }
        
        .food-form {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
        }
        
        .quantity-input {
            width: 40px;
            padding: 8px;
            border: 1px solid var(--gray);
            border-radius: 4px;
            text-align: center;
        }
        
        .add-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 10px 10px;
            border-radius: 4px;
            cursor: pointer;
            flex-grow: 1;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background-color 0.2s;
        }
        
        .add-btn:hover {
            background-color: #ff5252;
        }
        
        .cart-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background-color: var(--secondary);
            color: white;
            text-decoration: none;
            padding: 5px 15px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            position: fixed;
            bottom: 30px;
            right: 30px;
            box-shadow: var(--shadow);
            z-index: 100;
            transition: transform 0.3s, background-color 0.3s;
        }
        
        .cart-link:hover {
            background-color: #3dbdb5;
            transform: scale(1.05);
        }
        
        .cart-icon {
            font-size: 1.3rem;
        }
        
        .food-image-placeholder {
            height: 100%;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: #ddd;
        }
        
        @media (max-width: 768px) {
            .menu-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .cart-link {
                padding: 12px 20px;
                font-size: 1rem;
                right: 20px;
                bottom: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .menu-grid {
                grid-template-columns: 1fr;
            }
            
            .food-card {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>Delicious Meals</h1>
        <p>Order your favorite food and get it delivered to your doorstep</p>
    </header>
    
    <div class="menu-grid">
        <?php foreach ($foods as $food): ?>
            <div class="food-card">
                  <div class="food-image">
            <img src="/Food/admin/uploads/<?= htmlspecialchars(basename($food['image'])) ?>" 
         alt="<?= htmlspecialchars($food['name']) ?>" style="width:100%; height:200px; object-fit:cover;">
        </div>
                <div class="food-content">
                    <h3 class="food-title"><?= $food['name'] ?></h3>
                    <p class="food-description"><?= $food['description'] ?></p>
                    <div class="food-price">Price: <?= $currency ?><?= $food['price'] ?></div>
                    <form class="food-form" method="post" action="cart.php">
                        <input type="hidden" name="food_id" value="<?= $food['id'] ?>">
                        <input type="hidden" name="redirect" value="<?= $_SERVER['REQUEST_URI'] ?>">
                        <input type="number" class="quantity-input" name="quantity" value="1" min="1">
                        <button type="submit" class="add-btn">
                            <span class="btn-icon">🛒</span>
                            Add to Cart
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<a href="cart.php" class="cart-link">
    <span class="cart-icon">🛒</span>
    View Cart
</a>

</body>
</html>
