<?php
session_start();
require 'includes/db.php';
require './includes/customer_nav.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $food_id = $_POST['food_item_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    $stmt = $pdo->prepare("INSERT INTO reviews (user_id, food_item_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $food_id, $rating, $comment]);

    echo "Review submitted.";
}

$stmt = $pdo->prepare("
    SELECT DISTINCT f.id, f.name
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    JOIN food_items f ON f.id = oi.food_item_id
    WHERE o.user_id = ?
");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Ordering System - Submit a Review</title>
    <style>
        :root {
            --primary-color: #ff6b6b;
            --secondary-color: #4ecdc4;
            --dark-color: #1a535c;
            --light-color: #f7fff7;
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
            color: #333;
            padding: 20px;
            line-height: 1.6;
        }
        
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }
        
        h2 {
            color: var(--dark-color);
            margin-bottom: 25px;
            text-align: center;
            font-size: 28px;
            position: relative;
            padding-bottom: 10px;
        }
        
        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background-color: var(--primary-color);
            border-radius: 2px;
        }
        
        .review-form {
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark-color);
            font-weight: 600;
            font-size: 16px;
        }
        
        select, input, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        select:focus, input:focus, textarea:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(78, 205, 196, 0.2);
        }
        
        .rating-container {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        
        .rating-container input {
            display: none;
        }
        
        .rating-container label {
            cursor: pointer;
            font-size: 30px;
            color: #ddd;
            transition: color 0.3s;
            margin-right: 5px;
        }
        
        .rating-container label:hover,
        .rating-container label:hover ~ label,
        .rating-container input:checked ~ label {
            color: #ffcc00;
        }
        
        .rating-container label:before {
            content: "★";
        }
        
        button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 14px 25px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            width: 100%;
        }
        
        button:hover {
            background-color: #ff5252;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(255, 107, 107, 0.2);
        }
        
        .form-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .form-icon {
            font-size: 24px;
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 20px 15px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Submit a Review</h2>
        <div class="review-form">
            <form method="post">
                <div class="form-group">
                    <label for="food_item_id">Food Item:</label>
                    <select name="food_item_id" id="food_item_id" required>
                        <?php foreach ($items as $item): ?>
                            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Rating:</label>
                    <div class="rating-container">
                        <input type="radio" id="star5" name="rating" value="5" required>
                        <label for="star5" title="5 stars"></label>
                        <input type="radio" id="star4" name="rating" value="4">
                        <label for="star4" title="4 stars"></label>
                        <input type="radio" id="star3" name="rating" value="3">
                        <label for="star3" title="3 stars"></label>
                        <input type="radio" id="star2" name="rating" value="2">
                        <label for="star2" title="2 stars"></label>
                        <input type="radio" id="star1" name="rating" value="1">
                        <label for="star1" title="1 star"></label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="comment">Comment:</label>
                    <textarea name="comment" id="comment" rows="4" placeholder="Tell us about your experience..."></textarea>
                </div>
                
                <button type="submit">Submit Review</button>
            </form>
        </div>
    </div>
</body>
</html>
