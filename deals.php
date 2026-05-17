<?php 
require_once 'config.php'; 
include 'includes/header.php'; 

// Fetch featured/highly rated products as "deals"
$stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.cat_id = c.id WHERE p.rating >= 4.5 ORDER BY p.rating DESC LIMIT 8");
$deals = $stmt->fetchAll();
?>

<main class="section-padding">
    <div class="container">
        <div style="text-align: center; margin-bottom: 10rem;">
            <span style="color: var(--accent); font-weight: 800; font-size: 1rem; text-transform: uppercase; letter-spacing: 0.2em; display: block; margin-bottom: 2rem;">Exclusive Opportunities</span>
            <h1 style="font-size: var(--fs-h1); margin-top: 1rem; margin-bottom: 2.5rem;">Premium <span style="color: var(--accent);">Offers.</span></h1>
            <p style="color: var(--text-secondary); font-size: 1.5rem; max-width: 900px; margin: 0 auto; line-height: 1.6;">
                Unbeatable value on the world's most advanced electronics. Limited-time pricing on our most celebrated designs, crafted for performance and beauty.
            </p>
        </div>

        <?php if(empty($deals)): ?>
            <div style="text-align: center; padding: 10rem 0; background: var(--bg-soft); border-radius: var(--radius-xl);">
                <h2 style="color: var(--text-secondary); font-weight: 500;">New deals are dropping soon.</h2>
                <a href="products.php" class="btn btn-primary" style="margin-top: 3rem;">Browse All Products</a>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach($deals as $p): ?>
                    <div class="product-card fade-in">
                        <div style="background: var(--bg-soft); margin: -3rem -3rem 3rem -3rem; display: flex; align-items: center; justify-content: center; height: 400px; overflow: hidden;">
                             <img src="<?= htmlspecialchars(get_product_image($p['image'])) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="product-image" style="margin-bottom: 0; width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <span style="font-size: 0.9rem; color: var(--text-secondary); font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em;">
                            <?= htmlspecialchars($p['category_name']) ?>
                        </span>
                        <h3 style="margin: 1.25rem 0; font-size: 2.2rem;"><?= htmlspecialchars($p['name']) ?></h3>
                        
                        <div style="display: flex; align-items: center; gap: 0.8rem; margin-bottom: 2rem; font-size: 1.1rem;">
                            <span style="color: #FFCC00; font-size: 1.4rem;">★</span>
                            <span style="font-weight: 700;"><?= $p['rating'] ?></span>
                            <span style="background: var(--success); color: white; padding: 0.4rem 1rem; border-radius: 50px; font-size: 0.8rem; font-weight: 800; margin-left: 1rem;">BEST PRICE</span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 3.5rem; border-top: 1.5px solid var(--border);">
                            <span style="font-size: 2.2rem; font-weight: 800; color: var(--text-main);">₹<?= number_format($p['price']) ?></span>
                            <button onclick="addToCart(<?= $p['id'] ?>, event)" class="btn btn-primary" style="padding: 1.25rem 2.5rem;">
                                Add to Bag
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- script.js handles addToCart globally -->
<?php include 'includes/footer.php'; ?>
