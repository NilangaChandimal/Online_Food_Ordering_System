<?php
session_start();
require 'includes/db.php';
require './includes/customer_nav.php';

$settingStmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'currency_symbol'");
$settingStmt->execute();
$currency = $settingStmt->fetchColumn();
if (!$currency) $currency = '$'; 


$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Menu</title>
    <style>
        :root {
            --primary: #ff6b6b;
            --secondary: #4ecdc4;
            --dark: #292f36;
            --light: #f7f7f7;
            --gray: #ced4da;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --section-spacing: 40px;
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

        .page-title {
            text-align: center;
            margin-bottom: 15px;
            font-size: 2.8rem;
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

        .category-section {
            margin-bottom: var(--section-spacing);
        }

        .category-title {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: var(--dark);
            border-left: 5px solid var(--secondary);
            padding-left: 15px;
            display: flex;
            align-items: center;
        }

        .menu-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr); 
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
            height: 60px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }

        .food-price {
            font-weight: bold;
            font-size: 1.2rem;
            color: var(--dark);
            margin-bottom: 15px;
        }

        .rating-stars {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .star-icon {
            color: #ffc107;
            font-size: 1.2rem;
            margin-right: 5px;
        }

        .rating-text {
            margin-left: 5px;
            font-weight: 500;
            color: #666;
        }

        .not-rated {
            color: #888;
            font-style: italic;
            font-size: 0.9rem;
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

        .login-link {
            display: inline-block;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            padding: 8px 0;
            position: relative;
        }

        .login-link::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--primary);
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.3s ease;
        }

        .login-link:hover::after {
            transform: scaleX(1);
            transform-origin: left;
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

        .stars-container {
            display: inline-flex;
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

        .category-icon {
            margin-right: 10px;
            font-size: 1.5rem;
        }

        @media (max-width: 768px) {
            .menu-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }

            .page-title {
                font-size: 2.2rem;
            }

            .category-title {
                font-size: 1.6rem;
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

            .page-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <h1 class="page-title">Food Menu</h1>

        <?php foreach ($categories as $cat): ?>
            <div class="category-section">
                <h2 class="category-title">
                    <?php
                    $categoryIcons = [
                        'Appetizers' => '🥗',
                        'Main Course' => '🍲',
                        'Desserts' => '🍰',
                        'Beverages' => '🥤',
                        'Pizza' => '🍕',
                        'Pasta' => '🍝',
                        'Burgers' => '🍔',
                        'Seafood' => '🦞',
                        'Vegan' => '🥬',
                        'Breakfast' => '🍳'
                    ];
                    $icon = isset($categoryIcons[$cat['name']]) ? $categoryIcons[$cat['name']] : '🍽️';
                    echo "<span class='category-icon'>$icon</span>";
                    ?>
                    <?= htmlspecialchars($cat['name']) ?>
                </h2>

                <?php
                $stmt = $pdo->prepare("SELECT * FROM food_items WHERE category_id = ? AND status = 'available'");
                $stmt->execute([$cat['id']]);
                $foods = $stmt->fetchAll();
                ?>

                <div class="menu-grid">
                    <?php foreach ($foods as $food): ?>
                        <div class="food-card">
                            <div class="food-image">
                                <img src="/Food/admin/uploads/<?= htmlspecialchars(basename($food['image'])) ?>"
                                    alt="<?= htmlspecialchars($food['name']) ?>" style="width:100%; height:200px; object-fit:cover;">
                            </div>
                            <div class="food-content">
                                <h3 class="food-title"><?= htmlspecialchars($food['name']) ?></h3>
                                <p class="food-description"><?= htmlspecialchars($food['description']) ?></p>
                                <div class="food-price">Price: <?= $currency ?><?= $food['price'] ?></div>

                                <?php
                                $reviewStmt = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE food_item_id = ?");
                                $reviewStmt->execute([$food['id']]);
                                $avg = $reviewStmt->fetchColumn();
                                ?>

                                <div class="rating-stars">
                                    <?php if ($avg): ?>
                                        <div class="stars-container">
                                            <?php
                                            $rating = round($avg, 1);
                                            $fullStars = floor($rating);
                                            $halfStar = $rating - $fullStars >= 0.5;

                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $fullStars) {
                                                    echo '<span class="star-icon">★</span>';
                                                } elseif ($i == $fullStars + 1 && $halfStar) {
                                                    echo '<span class="star-icon">⯨</span>';
                                                } else {
                                                    echo '<span class="star-icon" style="color: #ddd;">☆</span>';
                                                }
                                            }
                                            ?>
                                        </div>
                                        <span class="rating-text"><?= $rating ?>/5</span>
                                    <?php else: ?>
                                        <span class="not-rated">Not rated yet</span>
                                    <?php endif; ?>
                                </div>

                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <form class="food-form" method="post" action="cart.php">
                                        <input type="hidden" name="food_id" value="<?= $food['id'] ?>">
                                        <input type="hidden" name="redirect" value="<?= $_SERVER['REQUEST_URI'] ?>">
                                        <input type="number" class="quantity-input" name="quantity" value="1" min="1">
                                        <button type="submit" class="add-btn">
                                            <span class="btn-icon">🛒</span>
                                            Add to Cart
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <p><a href="login.php" class="login-link">Login to order</a></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="cart.php" class="cart-link">
            <span class="cart-icon">🛒</span>
            View Cart
        </a>
    <?php endif; ?>

</body>

</html>