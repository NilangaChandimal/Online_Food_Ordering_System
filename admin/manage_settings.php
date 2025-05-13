<?php
require '../includes/db.php';
require '../includes/auth.php'; 
require '../includes/admin_nav.php';
require_admin_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['keys'])) {
    foreach ($_POST['keys'] as $key => $value) {
        $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE `key` = ?");
        $stmt->execute([$value, $key]);
    }
    $success = "Settings updated successfully.";
}

$stmt = $pdo->query("SELECT * FROM settings ORDER BY `key` ASC");
$settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Settings</title>
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
            max-width: 900px;
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

        .success-message {
            background-color: rgba(39, 174, 96, 0.1);
            color: var(--success);
            padding: 12px 15px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            border-left: 4px solid var(--success);
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .success-message::before {
            content: "✓";
            margin-right: 10px;
            font-weight: bold;
        }

        .settings-card {
            background-color: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 25px;
            margin-bottom: 30px;
        }

        .settings-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .settings-table th {
            text-align: left;
            padding: 12px 15px;
            background-color: rgba(42, 157, 143, 0.1);
            color: var(--secondary);
            font-weight: 600;
            border-bottom: 2px solid var(--secondary-light);
        }

        .settings-table td {
            padding: 12px 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            vertical-align: middle;
        }

        .settings-table tr:last-child td {
            border-bottom: none;
        }

        .settings-table tr:hover {
            background-color: rgba(0, 0, 0, 0.01);
        }

        .key-column {
            width: 30%;
            font-weight: 500;
            color: var(--text-dark);
        }

        .value-column {
            width: 70%;
        }

        .settings-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: var(--radius);
            font-size: 14px;
            transition: border 0.3s ease;
        }

        .settings-input:focus {
            border-color: var(--secondary);
            outline: none;
            box-shadow: 0 0 0 2px rgba(42, 157, 143, 0.2);
        }

        .submit-button {
            background-color: var(--secondary);
            color: var(--white);
            border: none;
            padding: 12px 20px;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 500;
            font-size: 16px;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        .submit-button:hover {
            background-color: var(--secondary-light);
        }

        .submit-button:active {
            transform: scale(0.98);
        }

        .back-link {
            display: inline-block;
            margin-top: 10px;
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

        @media (max-width: 768px) {
            .settings-table th, 
            .settings-table td {
                padding: 10px;
            }
            
            .key-column {
                width: 40%;
            }
            
            .value-column {
                width: 60%;
            }
        }

        @media (max-width: 576px) {
            .settings-table {
                display: block;
            }
            
            .settings-table thead {
                display: none;
            }
            
            .settings-table tbody {
                display: block;
            }
            
            .settings-table tr {
                display: block;
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: var(--radius);
            }
            
            .settings-table td {
                display: block;
                text-align: right;
                padding: 10px 15px;
                position: relative;
                border-bottom: 1px solid #eee;
            }
            
            .settings-table td:last-child {
                border-bottom: none;
            }
            
            .settings-table td::before {
                content: attr(data-label);
                font-weight: 600;
                float: left;
                color: var(--secondary);
            }
            
            .key-column, .value-column {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Site Settings</h2>

        <?php if (isset($success)): ?>
            <div class="success-message"><?= $success ?></div>
        <?php endif; ?>

        <div class="settings-card">
            <form method="POST" action="">
                <table class="settings-table">
                    <thead>
                        <tr>
                            <th>Key</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($settings as $setting): ?>
                            <tr>
                                <td class="key-column" data-label="Key">
                                    <?= htmlspecialchars($setting['key']) ?>
                                </td>
                                <td class="value-column" data-label="Value">
                                    <input type="text" 
                                           class="settings-input"
                                           name="keys[<?= htmlspecialchars($setting['key']) ?>]" 
                                           value="<?= htmlspecialchars($setting['value']) ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" class="submit-button">Update Settings</button>
            </form>
        </div>

        <a href="dashboard.php" class="back-link">Back to Dashboard</a>
    </div>
</body>
</html>