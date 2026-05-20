<?php
require_once 'config.php';
include 'includes/header.php';

// Fetch a few featured products for the bento grid
try {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.cat_id = c.id LIMIT 8");
    $featured = $stmt->fetchAll();
} catch (PDOException $e) {
    $featured = [];
}
?>

<main>
    <!-- 2026 Immersive Hero -->
    <section class="section-padding" style="padding-top: 4rem; padding-bottom: 2rem;">
        <div class="container" style="text-align: center;">
            <div class="fade-in" style="margin-bottom: 6rem;">
                <span style="color: var(--accent); font-weight: 700; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.2em; display: block; margin-bottom: 1.5rem;">The Future of Tech</span>
                <h1 style="max-width: 1200px; margin: 0 auto 3rem;">Designed for those who <br><span style="color: var(--text-secondary);">define tomorrow.</span></h1>
                <div style="display: flex; gap: 1.5rem; justify-content: center;">
                    <a href="products.php" class="btn btn-primary" style="padding: 1.25rem 3rem;">Shop Collection</a>
                    <a href="deals.php" class="btn btn-outline" style="padding: 1.25rem 3rem;">Current Deals</a>
                </div>
            </div>

            <!-- Massive Bento Grid -->
            <div class="bento-grid fade-in" style="animation-delay: 0.2s;">
                <!-- Hero Laptop Item -->
                <?php if(isset($featured[1])): ?>
                <div class="bento-hero bento-item" style="z-index: 10;">
                    <div style="flex: 1; text-align: left; padding-right: 2rem;">
                        <span style="font-weight: 800; color: var(--accent); font-size: 0.8rem; text-transform: uppercase;"><?= $featured[1]['category_name'] ?></span>
                        <h2 style="margin-top: 1rem; font-size: 3.5rem;"><?= $featured[1]['name'] ?></h2>
                        <p style="color: var(--text-secondary); max-width: 400px; margin-top: 1.5rem; font-size: 1.2rem;"><?= substr($featured[1]['description'], 0, 100) ?>...</p>
                        <a href="products.php?cat=Laptops" class="btn btn-primary" style="margin-top: 3rem;">Learn more &rarr;</a>
                    </div>
                    <div style="flex: 1.2; position: relative;">
                         <img src="<?= get_product_image($featured[1]['image']) ?>" style="width: 140%; position: absolute; right: -20%; top: 50%; transform: translateY(-50%); filter: drop-shadow(0 30px 60px rgba(0,0,0,0.15));" alt="Laptop">
                    </div>
                </div>
                <?php endif; ?>

                <!-- Featured Watch -->
                <?php if(isset($featured[0])): ?>
                <div class="bento-side-1 bento-item">
                    <div style="text-align: left;">
                        <h3 style="font-size: 2rem;"><?= $featured[0]['name'] ?></h3>
                        <p style="color: var(--text-secondary); margin-top: 0.5rem;">Elegance in every tick.</p>
                        <a href="products.php?cat=Watches" style="margin-top: 2rem; display: inline-block; font-weight: 700; color: var(--accent);">Order Now &rarr;</a>
                    </div>
                    <img src="<?= get_product_image($featured[0]['image']) ?>" style="position: absolute; bottom: -20px; right: -20px; width: 60%;" alt="Watch">
                </div>
                <?php endif; ?>

                <!-- Small Promo -->
                <div class="bento-side-2 bento-item">
                    <h3 style="font-size: 1.8rem;">Sonic Elite</h3>
                    <p style="color: var(--text-secondary);">Immersion redefined.</p>
                    <img src="img/headphones_1.png" style="width: 70%; margin: 2rem auto 0; display: block;" alt="Audio">
                </div>
            </div>
        </div>
    </section>

    <!-- Full-Width Gallery Header -->
    <section class="section-padding" style="background: var(--bg-soft);">
        <div class="container">
            <div class="flex-between align-end" style="margin-bottom: 5rem;">
                <div>
                    <h2 style="font-size: 4rem;">The Modern <span style="color: var(--accent);">Essentials.</span></h2>
                    <p style="color: var(--text-secondary); font-size: 1.3rem; margin-top: 1rem;">Meticulously curated hardware for the ambitious.</p>
                </div>
                <a href="products.php" style="font-size: 1.1rem; font-weight: 700; padding-bottom: 8px; border-bottom: 3px solid var(--accent);">View full catalog &rarr;</a>
            </div>

            <div class="product-grid">
                <?php foreach($featured as $p): ?>
                <div class="product-card fade-in">
                    <div style="background: var(--bg-soft); margin: -3rem -3rem 2rem -3rem; display: flex; align-items: center; justify-content: center; height: 350px; overflow: hidden;">
                        <img src="<?= get_product_image($p['image']) ?>" class="product-image" alt="<?= $p['name'] ?>" style="margin-bottom: 0; width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <span style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em;"><?= $p['category_name'] ?></span>
                    <h3 style="margin: 0.75rem 0; font-size: 1.6rem;"><?= $p['name'] ?></h3>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 2rem;">
                        <p style="font-weight: 800; font-size: 1.5rem;">₹<?= number_format($p['price']) ?></p>
                        <button class="btn btn-primary" style="padding: 0.8rem 1.5rem; font-size: 0.9rem;" onclick="addToCart(<?= $p['id'] ?>, event)">Add to Bag</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Immersive Values -->
    <section class="section-padding">
        <div class="container layout-split">
            <div class="fade-in">
                <span style="color: var(--accent); font-weight: 800; font-size: 0.9rem; text-transform: uppercase;">Engineering Excellence</span>
                <h2 style="font-size: 5rem; margin-top: 2rem;">Utility meets <br>pure beauty.</h2>
                <p style="font-size: 1.4rem; color: var(--text-secondary); line-height: 1.6; margin-top: 2rem;">
                    We believe the devices you use every day should be as beautiful as they are powerful. Every pixel, every screw, every line of code is optimized for your productivity and delight.
                </p>
            </div>
            <div class="fade-in" style="background: var(--bg-soft); padding: 5rem; border-radius: var(--radius-xl); text-align: center;">
                <img src="img/headphones_2.png" style="width: 100%; filter: drop-shadow(0 40px 80px rgba(0,0,0,0.1));" alt="Value">
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
