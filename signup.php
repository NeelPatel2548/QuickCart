<?php 
require_once 'config.php'; 

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Invalid request. Please try again.";
    } else {
    $name = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];

    if ($password !== $confirm_pass) {
        $error = "Passwords do not match!";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        
        if ($check->rowCount() > 0) {
            $error = "Email already registered!";
        } else {
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, phone, address, password) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$name, $email, $phone, $address, $hashed_pass])) {
                $success = "Account created successfully! Redirecting to login...";
                header("refresh:2;url=login.php");
            } else {
                $error = "Something went wrong. Try again.";
            }
        }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join QuickCart | Create Your Account</title>
    <link rel="icon" type="image/png" href="favicon.ico">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="background: var(--bg-soft); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2.5rem;">

<div style="width: 100%; max-width: 800px; background: var(--white); padding: 6rem; border-radius: var(--radius-xl); box-shadow: var(--shadow-lg);">
    <div style="text-align: center; margin-bottom: 3rem;">
        <a href="index.php" class="logo" style="font-size: 2rem;"><?= SITE_NAME ?>.</a>
        <p style="color: var(--text-secondary); margin-top: 0.5rem; font-size: 0.95rem;">Join a community of tech enthusiasts.</p>
    </div>

    <?php if(!empty($error)): ?>
        <div style="background: rgba(255, 59, 48, 0.1); color: var(--danger); padding: 1.25rem; border-radius: var(--radius-md); margin-bottom: 2.5rem; font-size: 0.9rem; border: 1.5px solid rgba(255, 59, 48, 0.2); text-align: center; font-weight: 600;">
            <?= $error ?>
        </div>
    <?php endif; ?>
    <?php if(!empty($success)): ?>
        <div style="background: rgba(52, 199, 89, 0.1); color: var(--success); padding: 1.25rem; border-radius: var(--radius-md); margin-bottom: 2.5rem; font-size: 0.9rem; border: 1.5px solid rgba(52, 199, 89, 0.2); text-align: center; font-weight: 600;">
            <?= $success ?>
        </div>
    <?php endif; ?>

    <form action="signup.php" method="POST">
        <?= csrf_field() ?>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div>
                <label style="display: block; font-size: 0.7rem; color: var(--text-secondary); font-weight: 700; text-transform: uppercase; margin-bottom: 0.75rem;">Full Name</label>
                <input type="text" name="username" placeholder="John Doe" required 
                       style="width: 100%; padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-soft); outline: none; font-family: inherit;">
            </div>
            <div>
                <label style="display: block; font-size: 0.7rem; color: var(--text-secondary); font-weight: 700; text-transform: uppercase; margin-bottom: 0.75rem;">Phone Number</label>
                <input type="text" name="phone" placeholder="10 Digit Number" required 
                       style="width: 100%; padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-soft); outline: none; font-family: inherit;">
            </div>
        </div>
        
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.7rem; color: var(--text-secondary); font-weight: 700; text-transform: uppercase; margin-bottom: 0.75rem;">Email Address</label>
            <input type="email" name="email" placeholder="john@example.com" required 
                   style="width: 100%; padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-soft); outline: none; font-family: inherit;">
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.7rem; color: var(--text-secondary); font-weight: 700; text-transform: uppercase; margin-bottom: 0.75rem;">Shipping Address</label>
            <textarea name="address" rows="2" placeholder="Full Home/Office Address" required 
                   style="width: 100%; padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-soft); outline: none; font-family: inherit; resize: none;"></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2.5rem;">
            <div>
                <label style="display: block; font-size: 0.7rem; color: var(--text-secondary); font-weight: 700; text-transform: uppercase; margin-bottom: 0.75rem;">Password</label>
                <input type="password" name="password" placeholder="••••••••" required 
                       style="width: 100%; padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-soft); outline: none; font-family: inherit;">
            </div>
            <div>
                <label style="display: block; font-size: 0.7rem; color: var(--text-secondary); font-weight: 700; text-transform: uppercase; margin-bottom: 0.75rem;">Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="••••••••" required 
                       style="width: 100%; padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-soft); outline: none; font-family: inherit;">
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.1rem; border-radius: var(--radius-md);">Create Account</button>
    </form>

    <div style="text-align: center; margin-top: 2.5rem; color: var(--text-secondary); font-size: 0.9rem;">
        Already registered? <a href="login.php" style="color: var(--accent); font-weight: 700; text-decoration: none;">Sign In</a>
    </div>
</div>

</body>
</html>
