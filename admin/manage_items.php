<?php
require '../includes/auth.php';
require '../includes/db.php';
require '../includes/admin_nav.php';

$cats = $pdo->query("SELECT * FROM categories")->fetchAll();
$settingStmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'currency_symbol'");
$settingStmt->execute();
$currency = $settingStmt->fetchColumn();
if (!$currency) $currency = '$';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $imageTmpPath = $_FILES['image']['tmp_name'];
    
    if (!empty($imageTmpPath)) {
        $imageName = basename($_FILES['image']['name']);
        $imagePath = 'uploads/' . $imageName; 

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($imageTmpPath);

        if (!in_array($fileType, $allowedTypes)) {
            echo "Invalid file type. Only JPEG, PNG, and GIF are allowed.";
            exit;
        }

        if (move_uploaded_file($imageTmpPath, $imagePath)) {
            echo "File uploaded successfully!";
        } else {
            echo "Failed to upload file.";
        }
    } else {
        echo "No file uploaded.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $catId = $_POST['category_id'];
    $status = $_POST['status'];
    
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageName = basename($_FILES['image']['name']);
        $imagePath = 'uploads/' . $imageName;
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
        $image = $imagePath; 
    }

    $pdo->prepare("INSERT INTO food_items (name, description, price, category_id, image, status) VALUES (?, ?, ?, ?, ?, ?)")
        ->execute([$name, $desc, $price, $catId, $image, $status]);
}

if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $catId = $_POST['category_id'];
    $status = $_POST['status'];
    
    $image = $_POST['current_image']; 
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageName = basename($_FILES['image']['name']);
        $imagePath = 'uploads/' . $imageName;
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
        $image = $imagePath; 
    }

    $pdo->prepare("UPDATE food_items SET name = ?, description = ?, price = ?, category_id = ?, image = ?, status = ? WHERE id = ?")
        ->execute([$name, $desc, $price, $catId, $image, $status, $id]);
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM food_items WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: manage_items.php");
    exit;
}

$items = $pdo->query("
    SELECT f.*, c.name AS category 
    FROM food_items f
    JOIN categories c ON f.category_id = c.id
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Food Items - Food Ordering Admin</title>
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
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            font-weight: 500;
            display: block;
            margin-bottom: 8px;
            color: var(--text-dark);
        }
        
        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: var(--radius);
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .custom-file-input {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .custom-file-input input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .custom-file-label {
            display: block;
            padding: 10px 12px;
            background: #f1f3f5;
            border: 1px dashed #ccc;
            border-radius: var(--radius);
            text-align: center;
            color: var(--text-light);
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .custom-file-input:hover .custom-file-label {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .food-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-top: 10px;
            border: 3px solid white;
        }
        
        .btn {
            padding: 12px 20px;
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
            background-color: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: var(--danger-light);
        }
        
        .form-actions {
            margin-top: 20px;
            display: flex;
            gap: 15px;
        }
        
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .item-card {
            border: 1px solid #eee;
            border-radius: var(--radius);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        
        .item-image {
            height: 180px;
            width: 100%;
            object-fit: cover;
        }
        
        .item-details {
            padding: 15px;
        }
        
        .item-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-dark);
        }
        
        .item-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .item-price {
            font-weight: 600;
            color: var(--primary);
        }
        
        .item-category {
            color: var(--text-light);
            font-size: 14px;
        }
        
        .item-description {
            color: var(--text-light);
            font-size: 14px;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .item-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 15px;
        }
        
        .status-available {
            background-color: rgba(39, 174, 96, 0.1);
            color: var(--success);
        }
        
        .status-unavailable {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger);
        }
        
        .item-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
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
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            overflow-y: auto;
            padding: 50px 0;
        }
        
        .modal-content {
            background-color: white;
            max-width: 700px;
            margin: 0 auto;
            border-radius: var(--radius);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            position: relative;
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--primary);
        }
        
        .modal-close {
            cursor: pointer;
            font-size: 24px;
            color: var(--text-light);
            border: none;
            background: transparent;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background-color: rgba(39, 174, 96, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .alert-danger {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .items-grid {
                grid-template-columns: 1fr;
            }
            
            .item-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .item-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

 <div class="container">
        <div class="page-header">
            <h2>Manage Food Items</h2>
        </div>
        
        <div class="card">
            <h3><i class="fas fa-plus"></i> Add New Food Item</h3>
            <form method="post" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Item Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price (<?= $currency ?>)</label>
                        <input type="number" id="price" step="0.01" name="price" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category_id" required>
                            <option value="">-- Select Category --</option>
                            <?php foreach ($cats as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Availability Status</label>
                        <select id="status" name="status">
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Food Image</label>
                    <div class="custom-file-input">
                        <input type="file" id="image" name="image" accept="image/*">
                        <label class="custom-file-label" for="image">
                            <i class="fas fa-cloud-upload-alt"></i> Choose an image file
                        </label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="add" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Add Food Item
                    </button>
                </div>
            </form>
        </div>
        
        <div class="card">
            <h3><i class="fas fa-utensils"></i> Existing Food Items</h3>
            
            <?php if (count($items) > 0): ?>
                <div class="items-grid">
                    <?php foreach ($items as $item): ?>
                        <div class="item-card">
                            <img 
                                src="<?= !empty($item['image']) ? $item['image'] : 'https://via.placeholder.com/300x180?text=No+Image' ?>" 
                                alt="<?= $item['name'] ?>" 
                                class="item-image"
                            >
                            <div class="item-details">
                                <h4 class="item-name"><?= $item['name'] ?></h4>
                                
                                <div class="item-meta">
                                    <span class="item-price"><?= $currency ?><?= number_format($item['price'], 2) ?></span>
                                    <span class="item-category"><?= $item['category'] ?></span>
                                </div>
                                
                                <span class="item-status status-<?= $item['status'] ?>">
                                    <i class="fas fa-<?= $item['status'] === 'available' ? 'check-circle' : 'times-circle' ?>"></i>
                                    <?= ucfirst($item['status']) ?>
                                </span>
                                
                                <p class="item-description"><?= $item['description'] ?></p>
                                
                                <div class="item-actions">
                                    <button 
                                        onclick="openEditModal(<?= htmlspecialchars(json_encode($item)) ?>)" 
                                        class="btn btn-secondary"
                                    >
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    
                                    <a 
                                        href="?delete=<?= $item['id'] ?>" 
                                        onclick="return confirm('Are you sure you want to delete this item? This action cannot be undone.')" 
                                        class="btn btn-danger"
                                    >
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No food items found. Add your first food item above.</p>
            <?php endif; ?>
        </div>
        
        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Food Item</h3>
                <button class="modal-close" onclick="closeEditModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" id="edit_id" name="id">
                    <input type="hidden" id="current_image" name="current_image">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="edit_name">Item Name</label>
                            <input type="text" id="edit_name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_price">Price (<?= $currency ?>)</label>
                            <input type="number" id="edit_price" step="0.01" name="price" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_category">Category</label>
                            <select id="edit_category" name="category_id" required>
                                <?php foreach ($cats as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_status">Availability Status</label>
                            <select id="edit_status" name="status">
                                <option value="available">Available</option>
                                <option value="unavailable">Unavailable</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea id="edit_description" name="description" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Current Image</label>
                        <div>
                            <img id="preview_image" class="food-image" src="" alt="Food Image">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_image">Change Image (optional)</label>
                        <div class="custom-file-input">
                            <input type="file" id="edit_image" name="image" accept="image/*">
                            <label class="custom-file-label" for="edit_image">
                                <i class="fas fa-cloud-upload-alt"></i> Choose a new image
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="edit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Food Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.querySelectorAll('input[type="file"]').forEach(fileInput => {
            fileInput.addEventListener('change', function() {
                const label = this.nextElementSibling;
                label.innerHTML = this.files.length ? 
                    `<i class="fas fa-file-image"></i> ${this.files[0].name}` : 
                    `<i class="fas fa-cloud-upload-alt"></i> Choose an image file`;
            });
        });
        
        function openEditModal(item) {
            document.getElementById('edit_id').value = item.id;
            document.getElementById('edit_name').value = item.name;
            document.getElementById('edit_description').value = item.description;
            document.getElementById('edit_price').value = item.price;
            document.getElementById('edit_category').value = item.category_id;
            document.getElementById('edit_status').value = item.status;
            document.getElementById('current_image').value = item.image;
            
            const previewImage = document.getElementById('preview_image');
            previewImage.src = item.image ? item.image : 'https://via.placeholder.com/300x180?text=No+Image';
            
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>