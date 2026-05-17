<?php 
require_once 'config.php'; 

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // BUG-07 fix: validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Invalid request. Please try again.";
    } else {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = "No account found with this email.";
        } else {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['username'];
                $_SESSION['is_admin'] = (int)$user['is_admin']; 
                regenerate_csrf_token();

                // BUG-09 fix: validate redirect URL is relative (no external redirects)
                if (isset($_SESSION['redirect_after_login'])) {
                    $redirect = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                    // Only allow relative URLs — block absolute URLs, protocol-relative, and javascript:
                    if (preg_match('#^[a-zA-Z0-9_/\-\.\?\&\=]+$#', $redirect) && !preg_match('#^https?://#i', $redirect)) {
                        header("Location: $redirect");
                    } else {
                        header("Location: index.php"); // Fallback to homepage
                    }
                } elseif ((int)$user['is_admin'] === 1) {
                    header("Location: admin.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error = "Incorrect password. Please try again.";
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
    <title>Login | <?= SITE_NAME ?></title>
    <link rel="icon" type="image/png" href="favicon.ico">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="background: var(--bg-soft); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">

<div style="width: 100%; max-width: 700px; background: var(--white); padding: 6rem; border-radius: var(--radius-xl); box-shadow: var(--shadow-lg);">
    <div style="text-align: center; margin-bottom: 3rem;">
        <a href="index.php" class="logo" style="font-size: 2.2rem;"><?= SITE_NAME ?>.</a>
        <p style="color: var(--text-secondary); margin-top: 0.75rem; font-size: 0.95rem;">Enter the next generation of tech.</p>
        <?php if (!empty($success)): ?>
        <div style="background: rgba(52, 199, 89, 0.1); color: var(--success); padding: 1.25rem; border-radius: var(--radius-md); margin-bottom: 2.5rem; font-size: 0.9rem; border: 1.5px solid rgba(52, 199, 89, 0.2); text-align: center; font-weight: 600;">
            <?= $success ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($error)): ?>
        <div style="background: rgba(255, 59, 48, 0.1); color: var(--danger); padding: 1.25rem; border-radius: var(--radius-md); margin-bottom: 2.5rem; font-size: 0.9rem; border: 1.5px solid rgba(255, 59, 48, 0.2); text-align: center; font-weight: 600;">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <?= csrf_field() ?>
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.75rem; color: var(--text-secondary); font-weight: 700; text-transform: uppercase; margin-bottom: 0.75rem;">Email Address</label>
            <input type="email" name="email" placeholder="name@example.com" required 
                   style="width: 100%; padding: 1.1rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-soft); outline: none; font-family: inherit;">
        </div>
        
        <div style="margin-bottom: 2rem;">
            <label style="display: block; font-size: 0.75rem; color: var(--text-secondary); font-weight: 700; text-transform: uppercase; margin-bottom: 0.75rem;">Password</label>
            <input type="password" name="password" placeholder="••••••••" required 
                   style="width: 100%; padding: 1.1rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-soft); outline: none; font-family: inherit;">
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.1rem; border-radius: var(--radius-md);">Sign In</button>
    </form>

    <div style="text-align: center; margin-top: 2.5rem; color: var(--text-secondary); font-size: 0.9rem;">
        New to QuickCart? <a href="signup.php" style="color: var(--accent); font-weight: 700; text-decoration: none;">Create Account</a>
    </div>
</div>

</body>
</html>
