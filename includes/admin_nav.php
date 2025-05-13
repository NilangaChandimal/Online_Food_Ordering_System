<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Navigation</title>
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
        }

        
        .admin-nav-container {
            background-color: var(--white);
            box-shadow: var(--shadow);
            position: relative;
            z-index: 100;
        }

        .admin-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            height: 70px;
        }

        .admin-brand {
            display: flex;
            align-items: center;
        }

        .admin-logo {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .admin-logo span {
            color: var(--secondary);
        }

        .admin-menu {
            display: flex;
            align-items: center;
            list-style-type: none;
            margin: 0;
            padding: 0;
        }

        .admin-menu-item {
            position: relative;
            margin-left: 5px;
        }

        .admin-menu-link {
            display: block;
            padding: 10px 15px;
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            border-radius: var(--radius);
            transition: all 0.2s ease;
        }

        .admin-menu-link:hover {
            background-color: rgba(0, 0, 0, 0.04);
            color: var(--primary);
        }

        .admin-menu-link.active {
            background-color: rgba(255, 107, 53, 0.1);
            color: var(--primary);
        }

        .admin-menu-link.logout {
            color: var(--danger);
        }

        .admin-menu-link.logout:hover {
            background-color: rgba(231, 76, 60, 0.1);
        }

        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-dark);
            font-size: 24px;
            cursor: pointer;
            padding: 5px;
        }

        
        .auth-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .auth-button {
            padding: 8px 16px;
            border-radius: var(--radius);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .login-button {
            background-color: transparent;
            color: var(--secondary);
            border: 1px solid var(--secondary);
        }

        .login-button:hover {
            background-color: rgba(42, 157, 143, 0.1);
        }

        .register-button {
            background-color: var(--secondary);
            color: var(--white);
            border: 1px solid var(--secondary);
        }

        .register-button:hover {
            background-color: var(--secondary-light);
        }

      
        .admin-menu-item.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background-color: var(--primary);
        }

      
        @media (max-width: 992px) {
            .admin-nav {
                height: 60px;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .admin-menu {
                position: absolute;
                top: 60px;
                left: 0;
                right: 0;
                background-color: var(--white);
                box-shadow: 0 6px 10px rgba(0, 0, 0, 0.1);
                flex-direction: column;
                align-items: flex-start;
                padding: 10px 0;
                display: none;
                z-index: 200;
            }

            .admin-menu.active {
                display: flex;
            }

            .admin-menu-item {
                width: 100%;
                margin-left: 0;
            }

            .admin-menu-link {
                padding: 12px 20px;
                width: 100%;
                border-radius: 0;
            }

            .admin-menu-item.active::after {
                display: none;
            }

            .admin-menu-link.active {
                border-left: 4px solid var(--primary);
            }

            .auth-buttons {
                width: 100%;
                flex-direction: column;
                padding: 10px 20px;
                gap: 10px;
            }

            .auth-button {
                width: 100%;
                text-align: center;
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="admin-nav-container">
        <nav class="admin-nav">
            <?php
            $siteStmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'site_name'");
            $siteStmt->execute();
            $site_name = $siteStmt->fetchColumn();
            if (!$site_name) $site_name = 'Food Ordering System';
            ?>

            <div class="admin-brand">
                <a href="../admin/dashboard.php" class="admin-logo"><?= htmlspecialchars($site_name) ?></a>
            </div>


            <button class="mobile-menu-toggle" onclick="toggleMenu()">
                ☰
            </button>

            <?php if (isset($_SESSION['admin_id'])): ?>
                <ul class="admin-menu" id="adminMenu">
                    <li class="admin-menu-item <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                        <a href="../admin/dashboard.php" class="admin-menu-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
                    </li>
                    <li class="admin-menu-item <?= basename($_SERVER['PHP_SELF']) == 'manage_categories.php' ? 'active' : '' ?>">
                        <a href="../admin/manage_categories.php" class="admin-menu-link <?= basename($_SERVER['PHP_SELF']) == 'manage_categories.php' ? 'active' : '' ?>">Categories</a>
                    </li>
                    <li class="admin-menu-item <?= basename($_SERVER['PHP_SELF']) == 'manage_items.php' ? 'active' : '' ?>">
                        <a href="../admin/manage_items.php" class="admin-menu-link <?= basename($_SERVER['PHP_SELF']) == 'manage_items.php' ? 'active' : '' ?>">Food Items</a>
                    </li>
                    <li class="admin-menu-item <?= basename($_SERVER['PHP_SELF']) == 'manage_orders.php' ? 'active' : '' ?>">
                        <a href="../admin/manage_orders.php" class="admin-menu-link <?= basename($_SERVER['PHP_SELF']) == 'manage_orders.php' ? 'active' : '' ?>">Orders</a>
                    </li>
                    <li class="admin-menu-item <?= basename($_SERVER['PHP_SELF']) == 'manage_settings.php' ? 'active' : '' ?>">
                        <a href="../admin/manage_settings.php" class="admin-menu-link <?= basename($_SERVER['PHP_SELF']) == 'manage_settings.php' ? 'active' : '' ?>">Settings</a>
                    </li>
                    <li class="admin-menu-item">
                        <a href="../admin/logout.php" class="admin-menu-link logout">Logout</a>
                    </li>
                </ul>
            <?php else: ?>
                <div class="auth-buttons" id="adminMenu">
                    <a href="../admin/login.php" class="auth-button login-button">Login</a>
                    <a href="../admin/register.php" class="auth-button register-button">Register</a>
                </div>
            <?php endif; ?>
        </nav>
    </div>

    <script>
        
        function toggleMenu() {
            const menu = document.getElementById('adminMenu');
            menu.classList.toggle('active');
        }

        
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const menuItems = document.querySelectorAll('.admin-menu-item');

            menuItems.forEach(item => {
                const link = item.querySelector('.admin-menu-link');
                if (link && link.getAttribute('href').includes(currentPage)) {
                    item.classList.add('active');
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>

</html>