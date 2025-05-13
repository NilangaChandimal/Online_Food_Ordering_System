<?php
require 'includes/db.php';
require './includes/customer_nav.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')");
    $stmt->execute([$name, $email, $pass]);

    echo "Registered successfully. <a href='login.php'>Login</a>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Ordering System - Registration</title>
    <style>
       
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f7f7f7;
            background-image: linear-gradient(rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.8)), 
                              url('/api/placeholder/1920/1080');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 20px;
        }
        
        .container {
            width: 90%;
            max-width: 400px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            overflow: hidden;
        }
        
        .header {
            background-color: #FF6B35;
            color: white;
            text-align: center;
            padding: 5px;
            position: relative;
        }
        
        .header h2 {
            font-size: 20px;
            letter-spacing: 0.5px;
        }
        
        .header .logo {
            width: 60px;
            height: 60px;
            background-color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }
        
        .form-container {
            padding: 30px;
        }
        
        .input-group {
            margin-bottom: 10px;
            position: relative;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
            font-size: 14px;
        }
        
        .input-group input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .input-group input:focus {
            border-color: #FF6B35;
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }
        
        .input-icon {
            position: absolute;
            right: 15px;
            top: 37px;
            color: #aaa;
        }
        
        button {
            background-color: #FF6B35;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #e85a27;
        }
        
        .footer {
            text-align: center;
            padding: 10px 0;
            color: #777;
            font-size: 13px;
        }
        
        .footer a {
            color: #FF6B35;
            text-decoration: none;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
        
        .terms-group {
            margin: 10px 0;
            display: flex;
            align-items: flex-start;
        }
        
        .terms-group input[type="checkbox"] {
            margin-right: 10px;
            margin-top: 3px;
        }
        
        .terms-group label {
            font-size: 13px;
            color: #555;
            line-height: 1.5;
        }
        
        @media (max-width: 480px) {
            .container {
                width: 95%;
            }
            
            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <div style="font-size: 28px; color: #FF6B35;">🍽️</div>
            </div>
            <h2>Create Account</h2>
        </div>
        
        <div class="form-container">
            <form method="post">
                <div class="input-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                    <span class="input-icon">👤</span>
                </div>
                
                <div class="input-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    <span class="input-icon">✉️</span>
                </div>
                
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a password" required>
                    <span class="input-icon">🔒</span>
                </div>
                
                <div class="terms-group">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">I agree to the Terms of Service and Privacy Policy</label>
                </div>
                
                <button type="submit">Create Your Account</button>
            </form>
            
            <div class="footer">
                <p>Already have an account? <a href="login.php">Login</a></p>
            </div>
        </div>
    </div>
</body>
</html>
