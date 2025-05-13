<?php
ob_start(); 
session_start();
require '../includes/db.php';
require '../includes/auth.php';
require '../includes/admin_nav.php';
require_admin_login(); 

if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$deleteId]);
    header("Location: manage_users.php");
    exit;
}

if (isset($_GET['promote'])) {
    $promoteId = intval($_GET['promote']);
    $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?")->execute([$promoteId]);
    header("Location: manage_users.php");
    exit;
}

if (isset($_GET['demote'])) {
    $demoteId = intval($_GET['demote']);

    if ($_SESSION['user_id'] == $demoteId) {
        echo "<script>alert('You cannot demote yourself.'); window.location='manage_users.php';</script>";
        exit;
    }

    $pdo->prepare("UPDATE users SET role = 'customer' WHERE id = ?")->execute([$demoteId]);
    header("Location: manage_users.php");
    exit;
}

$stmt = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
ob_end_flush();  

?>


    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Food Ordering System</title>
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
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .header h2 {
            color: #333;
            font-size: 24px;
            font-weight: 600;
        }
        
        .action-button {
            background-color: #FF6B35;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
        }
        
        .action-button:hover {
            background-color: #e85a27;
        }
        
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background-color: #fff;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .users-table th,
        .users-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .users-table th {
            background-color: #f4f4f4;
            font-weight: 600;
            color: #333;
            white-space: nowrap;
        }
        
        .users-table tr:last-child td {
            border-bottom: none;
        }
        
        .users-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .role {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        
        .role-admin {
            background-color: #e6f7ff;
            color: #0070cc;
        }
        
        .role-customer {
            background-color: #f6ffed;
            color: #52c41a;
        }
        
        .action-btns {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .action-link {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 13px;
            text-decoration: none;
            font-weight: 500;
            white-space: nowrap;
        }
        
        .promote-btn {
            background-color: #f6ffed;
            color: #52c41a;
            border: 1px solid #b7eb8f;
        }
        
        .promote-btn:hover {
            background-color: #d9f7be;
        }
        
        .demote-btn {
            background-color: #fffbe6;
            color: #faad14;
            border: 1px solid #ffe58f;
        }
        
        .demote-btn:hover {
            background-color: #fff1b8;
        }
        
        .delete-btn {
            background-color: #fff2f0;
            color: #ff4d4f;
            border: 1px solid #ffccc7;
        }
        
        .delete-btn:hover {
            background-color: #ffd8d6;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            color: #666;
            text-decoration: none;
            font-weight: 500;
            margin-top: 20px;
            transition: color 0.3s;
        }
        
        .back-link:hover {
            color: #FF6B35;
        }
        
        .back-link::before {
            content: '←';
            margin-right: 8px;
            font-size: 16px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 0;
            color: #999;
        }
        
        .empty-state p {
            margin-top: 10px;
        }
        
        @media (max-width: 992px) {
            .container {
                padding: 20px;
            }
            
            .users-table {
                display: block;
                overflow-x: auto;
            }
        }
        
        @media (max-width: 576px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .action-btns {
                flex-direction: column;
                gap: 10px;
            }
            
            .action-link {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Manage Users</h2>
            <a href="dashboard.php" class="back-link">Back to Dashboard</a>
        </div>
        
        <?php if (empty($users)): ?>
            <div class="empty-state">
                <h3>No users found</h3>
                <p>There are currently no registered users in the system.</p>
            </div>
        <?php else: ?>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="role <?= $user['role'] === 'admin' ? 'role-admin' : 'role-customer' ?>">
                                    <?= htmlspecialchars($user['role']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($user['created_at']) ?></td>
                            <td class="action-btns">
                                <?php if ($user['role'] === 'customer'): ?>
                                    <a href="?promote=<?= $user['id'] ?>" class="action-link promote-btn">Promote to Admin</a>
                                <?php elseif ($user['role'] === 'admin'): ?>
                                    <a href="?demote=<?= $user['id'] ?>" class="action-link demote-btn" onclick="return confirm('Are you sure you want to demote this admin to customer?');">Demote to Customer</a>
                                <?php endif; ?>

                                <a href="?delete=<?= $user['id'] ?>" class="action-link delete-btn" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>