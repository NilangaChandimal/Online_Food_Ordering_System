<style>
    nav {
        background: #ff6f00;
        padding: 5px 30px;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        width: 100%;
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
        padding: 0 0px;
        height: 70px;

    }

    .admin-brand {
        display: flex;
        align-items: center;
    }

    .admin-logo {
        font-size: 22px;
        font-weight: 700;
        color: black;
        text-decoration: none;
        display: flex;
        align-items: center;
    }

    .admin-logo span {
        color: var(--secondary);
    }


    nav a {
        color: white;
        text-decoration: none;
        margin: 0 12px;
        font-weight: 600;
        font-size: 16px;
        transition: color 0.3s, background-color 0.3s;
        padding: 8px 14px;
        border-radius: 4px;
    }

    nav a:hover {
        background-color: rgba(255, 255, 255, 0.2);
    }

    nav h2 {
        color: red;
        text-align: center;
        margin: 20px auto;
        font-size: 20px;
    }


    @media (max-width: 600px) {
        nav {
            flex-direction: column;
            align-items: flex-start;
        }

        nav a {
            margin: 6px 0;
        }
    }
</style>

<div class="admin-nav-container">
    <nav class="admin-nav">
        <?php
     
        $maintenance = $pdo->query("SELECT value FROM settings WHERE `key` = 'maintenance_mode' LIMIT 1")->fetchColumn();
        if ($maintenance === 'on') {
            echo "<h2>We are currently under maintenance. Please check back later.</h2>";
            exit;
        }
        ?>
        <?php
        $siteStmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'site_name'");
        $siteStmt->execute();
        $site_name = $siteStmt->fetchColumn();
        if (!$site_name) $site_name = 'Food Ordering System';
        ?>

        <div class="admin-brand">
            <a href="dashboard.php" class="admin-logo"><?= htmlspecialchars($site_name) ?></a>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="index.php">Menu</a>
            <a href="cart.php">Cart</a>
            <a href="manage_address.php">Address</a>
            <a href="order_history.php">My Order</a>
            <a href="review.php">Reviews</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</div>