<?php
ob_start();
require '../includes/auth.php';
require '../includes/db.php';
require '../includes/admin_nav.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name = $_POST['name'];
    $pdo->prepare("INSERT INTO categories (name) VALUES (?)")->execute([$name]);
}

if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?")->execute([$name, $id]);
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: manage_categories.php");
    exit;
}

$cats = $pdo->query("SELECT * FROM categories")->fetchAll();
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Food Ordering Admin</title>
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
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .page-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .page-header h2 {
            color: var(--primary);
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .card {
            background-color: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 24px;
            margin-bottom: 30px;
        }
        
        .card h3 {
            font-size: 18px;
            color: var(--text-dark);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .form-group {
            margin-bottom: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }
        
        label {
            font-weight: 500;
            display: block;
            margin-bottom: 5px;
        }
        
        input[type="text"] {
            width: 300px;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: var(--radius);
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }
        
        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: var(--radius);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-light);
        }
        
        .btn-secondary {
            background-color: var(--secondary);
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: var(--secondary-light);
        }
        
        .btn-danger {
            position: relative;
            left: -400px;
            background-color: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: var(--danger-light);
        }
        
        .btn-sm {
            padding: 8px 12px;
            font-size: 13px;
        }
        
        .category-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .category-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }
        
        .category-item:last-child {
            border-bottom: none;
        }
        
        .category-item:hover {
            background-color: rgba(0,0,0,0.01);
        }
        
        .category-form {
            display: flex;
            align-items: center;
            flex-grow: 1;
            margin-right: 15px;
        }
        
        .category-form input[type="text"] {
            margin-right: 15px;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            margin-top: 15px;
            transition: color 0.3s;
        }
        
        .back-link:hover {
            color: var(--primary-light);
        }
        
        @media (max-width: 768px) {
            .category-item {
                flex-direction: column;
                align-items: flex-start;
                padding: 15px;
            }
            
            .category-form {
                margin-right: 0;
                margin-bottom: 15px;
                width: 100%;
            }
            
            .actions {
                width: 100%;
                justify-content: flex-end;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
 <div class="container">
        <div class="page-header">
            <h2>Manage Categories</h2>
        </div>
        
        <div class="card">
            <h3>Add New Category</h3>
            <form method="post">
                <div class="form-group">
                    <input type="text" name="name" placeholder="Category name" required>
                    <button type="submit" name="add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Category
                    </button>
                </div>
            </form>
        </div>
        
        <div class="card">
            <h3>Existing Categories</h3>
            <?php if (count($cats) > 0): ?>
                <ul class="category-list">
                    <?php foreach ($cats as $cat): ?>
                        <li class="category-item">
                            <form method="post" class="category-form">
                                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                <input type="text" name="name" value="<?= htmlspecialchars($cat['name']) ?>">
                                <button type="submit" name="edit" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-save"></i> Update
                                </button>
                            </form>
                            <div class="actions">
                                <a href="?delete=<?= $cat['id'] ?>" 
                                   onclick="return confirm('Are you sure you want to delete this category?')"
                                   class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No categories found. Add your first category above.</p>
            <?php endif; ?>
        </div>
        
        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</body>
</html>
