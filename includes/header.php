<?php 
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/image_helper.php';

$cart_count = 0;
if (isset($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> | Designed for Tomorrow</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>favicon.ico">
    <link rel="shortcut icon" type="image/png" href="<?= BASE_URL ?>favicon.ico">
    <!-- Premium Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap">
    <!-- Core Stylesheet -->
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css?v=<?= time(); ?>">
</head>
<body>

<header>
    <div class="container">
        <a href="<?= BASE_URL ?>index.php" class="logo"><?= SITE_NAME ?></a>
        
        <nav>
            <ul class="nav-links">
                <li><a href="<?= BASE_URL ?>index.php">Home</a></li>
                <li><a href="<?= BASE_URL ?>products.php">Store</a></li>
                <li><a href="<?= BASE_URL ?>deals.php">Deals</a></li>
                <li><a href="<?= BASE_URL ?>products.php?cat=Laptops">Laptops</a></li>
                <li><a href="<?= BASE_URL ?>products.php?cat=Watches">Watches</a></li>
            </ul>
        </nav>

        <div class="nav-action">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="<?= BASE_URL ?>profile.php" title="Account" style="font-size: 1.2rem;">👤</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>login.php" style="font-size: 0.95rem; font-weight: 500; color: var(--text-secondary);">Sign In</a>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>cart.php" class="btn btn-primary" style="padding: 0.6rem 1.4rem; font-size: 0.9rem; gap: 0.5rem;">
                Cart
                <?php if($cart_count > 0): ?>
                    <span style="background: var(--white); color: var(--text-main); border-radius: 50%; width: 18px; height: 18px; font-size: 0.7rem; display: flex; align-items: center; justify-content: center; font-weight: 800;">
                        <?= $cart_count ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>
    </div>
</header>
