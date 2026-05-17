<?php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Collect and Sanitize Data
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $address  = trim($_POST['address']);
    $pass     = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    // 2. Simple Validation
    if ($pass !== $confirm) {
        die("Passwords do not match! <a href='signup.php'>Try again</a>");
    }

    try {
        // 3. Check if Email Already Exists
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        
        if ($check->rowCount() > 0) {
            die("Email already registered! <a href='signup.php'>Go back</a>");
        }

        // 4. Hash Password for Security
        $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

        // 5. Insert into Database
        $sql = "INSERT INTO users (username, email, phone, address, password, is_admin) VALUES (?, ?, ?, ?, ?, 0)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$username, $email, $phone, $address, $hashed_password])) {
            // Success: Redirect to login with a message
            echo "<div style='text-align:center; padding:50px; font-family:sans-serif;'>
                    <h2 style='color:#7FB77E;'>Welcome to QuickCart!</h2>
                    <p>Account created successfully. Redirecting to login...</p>
                  </div>";
            header("refresh:3;url=login.php");
        }

    } catch (PDOException $e) {
        // BUG-11 fix: log error internally, show generic message to user
        error_log("QuickCart signup error: " . $e->getMessage());
        die("Something went wrong. Please try again later. <a href='signup.php'>Go back</a>");
    }
} else {
    // Redirect if someone tries to access this file directly
    header("Location: signup.php");
    exit();
}
?>
