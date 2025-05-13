<?php
session_start();
require '../includes/auth.php';
require '../includes/db.php';
require '../includes/admin_nav.php';
require_admin_login();

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalItems = $pdo->query("SELECT COUNT(*) FROM food_items")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Ordering Admin Dashboard</title>
    <style>
        :root {
            --primary: #ff6b35;
            --primary-light: #ff8c5f;
            --secondary: #2a9d8f;
            --text-dark: #333;
            --text-light: #777;
            --bg-light: #f8f9fa;
            --white:rgb(255, 255, 255);
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
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .dashboard-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .dashboard-header h2 {
            color: var(--primary);
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .dashboard-header p {
            color: var(--text-light);
            font-size: 16px;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 24px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card.customers {
            border-top: 4px solid #4361ee;
            /* background-color: #FFC22E; */
        }
        
        .stat-card.orders {
            border-top: 4px solid var(--primary);
        }
        
        .stat-card.items {
            border-top: 4px solid var(--secondary);
        }
        
        .stat-card .stat-icon {
            font-size: 32px;
            margin-bottom: 15px;
            display: inline-block;
            width: 60px;
            height: 60px;
            line-height: 60px;
            text-align: center;
            border-radius: 50%;
            color: white;
        }
        
        .stat-card.customers .stat-icon {
            background-color: #4361ee;
        }
        
        .stat-card.orders .stat-icon {
            background-color: var(--primary);
        }
        
        .stat-card.items .stat-icon {
            background-color: var(--secondary);
        }
        
        .stat-card h3 {
            font-size: 18px;
            color: var(--text-light);
            margin-bottom: 10px;
            font-weight: 500;
        }
        
        .stat-card .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-dark);
        }
        
        .quick-actions {
            background-color: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 24px;
            margin-bottom: 30px;
        }
        
        .quick-actions h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: var(--text-dark);
        }
        
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .action-btn {
            padding: 10px 16px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .action-btn:hover {
            background-color: var(--primary-light);
        }
        
        .action-btn.secondary {
            background-color: var(--secondary);
        }
        
        .action-btn.secondary:hover {
            background-color: #3ab7a7;
        }
        
        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="dashboard-container">
        <div class="dashboard-header">
            <h2>Admin Dashboard</h2>
            <p>Welcome back, Administrator! Here's an overview of your restaurant's performance.</p>
        </div>
        
        <div class="stats-container">
            <div class="stat-card customers">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Total Customers</h3>
                <div class="stat-value"><?= $totalUsers ?></div>
            </div>
            
            <div class="stat-card orders">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3>Total Orders</h3>
                <div class="stat-value"><?= $totalOrders ?></div>
            </div>
            
            <div class="stat-card items">
                <div class="stat-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <h3>Food Items</h3>
                <div class="stat-value"><?= $totalItems ?></div>
            </div>
        </div>
        
        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <div class="action-buttons">
                <a href="manage_items.php" class="action-btn">
                    <i class="fas fa-plus"></i> Add Food Item
                </a>
                <a href="manage_orders.php" class="action-btn secondary">
                    <i class="fas fa-list"></i> View Orders
                </a>
                <a href="manage_users.php" class="action-btn">
                    <i class="fas fa-user-cog"></i> Manage Users
                </a>
            </div>
        </div>
    </div>
</body>
</html>