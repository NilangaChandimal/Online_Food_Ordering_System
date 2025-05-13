<?php
session_start();
require 'includes/db.php';
require './includes/customer_nav.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['address_line'], $_POST['city'], $_POST['state'], $_POST['postal_code'], $_POST['phone'])) {
    $address_line = trim($_POST['address_line']);
    $city         = trim($_POST['city']);
    $state        = trim($_POST['state']);
    $postal_code  = trim($_POST['postal_code']);
    $phone        = trim($_POST['phone']);
    $is_default   = isset($_POST['is_default']) ? 1 : 0;

    if ($is_default) {
        $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")->execute([$user_id]);
    }

    $stmt = $pdo->prepare("INSERT INTO addresses (user_id, address_line, city, state, postal_code, phone, is_default) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $address_line, $city, $state, $postal_code, $phone, $is_default]);
    header("Location: manage_address.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    header("Location: manage_address.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ?");
$stmt->execute([$user_id]);
$addresses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Addresses</title>
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
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }
        
        .page-title {
            color: var(--dark);
            margin-bottom: 25px;
            font-size: 1.8rem;
            position: relative;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-gray);
        }
        
        .form-container {
            background-color: var(--light);
            padding: 25px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }
        
        input[type="text"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--gray);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        textarea:focus {
            border-color: var(--secondary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(78, 205, 196, 0.2);
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .checkbox-container {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }
        
        .checkbox-container input[type="checkbox"] {
            margin-right: 10px;
            width: 18px;
            height: 18px;
            accent-color: var(--secondary);
        }
        
        .checkbox-container label {
            margin-bottom: 0;
            font-weight: normal;
        }
        
        .btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: background-color 0.3s, transform 0.2s;
            display: inline-block;
            text-decoration: none;
            text-align: center;
        }
        
        .btn:hover {
            background-color: #ff5252;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: var(--secondary);
        }
        
        .btn-secondary:hover {
            background-color: #3dbdb5;
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 0.9rem;
        }
        
        .btn-danger {
            background-color: #dc3545;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .divider {
            height: 1px;
            background-color: var(--light-gray);
            margin: 30px 0;
            border: none;
        }
        
        .section-title {
            margin-bottom: 20px;
            font-size: 1.4rem;
            color: var(--dark);
        }
        
        .empty-state {
            text-align: center;
            padding: 30px;
            background-color: var(--light);
            border-radius: var(--border-radius);
            color: #6c757d;
            font-style: italic;
        }
        
        .address-card {
            background-color: white;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 15px;
            position: relative;
            transition: box-shadow 0.3s, transform 0.3s;
        }
        
        .address-card:hover {
            box-shadow: var(--shadow);
            transform: translateY(-2px);
        }
        
        .address-text {
            margin-bottom: 12px;
            font-size: 1.05rem;
        }
        
        .address-badge {
            display: inline-block;
            background-color: var(--secondary);
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 20px;
            margin-bottom: 15px;
        }
        
        .address-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            color: var(--dark);
            text-decoration: none;
            margin-top: 20px;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .back-link:hover {
            color: var(--primary);
        }
        
        .back-arrow {
            margin-right: 8px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            
            .form-container {
                padding: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }
            
            .form-container {
                padding: 15px;
            }
            
            .address-actions {
                flex-direction: column;
                gap: 8px;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="page-title">Manage Addresses</h2>
        
        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="address_line">Address Line:</label>
                    <textarea id="address_line" name="address_line" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="city">City:</label>
                    <input type="text" id="city" name="city" required>
                </div>
                
                <div class="form-group">
                    <label for="state">State:</label>
                    <input type="text" id="state" name="state" required>
                </div>
                
                <div class="form-group">
                    <label for="postal_code">Postal Code:</label>
                    <input type="text" id="postal_code" name="postal_code" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="text" id="phone" name="phone" required>
                </div>
                
                <div class="checkbox-container">
                    <input type="checkbox" id="is_default" name="is_default">
                    <label for="is_default">Set as default delivery address</label>
                </div>
                
                <button type="submit" class="btn">Add Address</button>
            </form>
        </div>
        
        <hr class="divider">
        
        <h3 class="section-title">Your Saved Addresses</h3>
        
        <?php if (count($addresses) === 0): ?>
            <div class="empty-state">
                <p>No addresses found. Add an address above to get started.</p>
            </div>
        <?php else: ?>
            <?php foreach ($addresses as $address): ?>
                <div class="address-card">
                    <?php if ($address['is_default']): ?>
                        <span class="address-badge">Default</span>
                    <?php endif; ?>
                    
                    <p class="address-text">
                        <?= htmlspecialchars($address['address_line']) ?>, <?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['state']) ?> - <?= htmlspecialchars($address['postal_code']) ?> - <?= htmlspecialchars($address['phone']) ?>
                    </p>
                    
                    <div class="address-actions">
                        <a href="?delete=<?= $address['id'] ?>" onclick="return confirm('Are you sure you want to delete this address?')" class="btn btn-danger btn-sm">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <a href="cart.php" class="back-link">
            <span class="back-arrow">←</span> Back to Cart
        </a>
    </div>
</body>
</html>