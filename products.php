<?php 
require_once 'config.php'; 
include 'includes/header.php'; 

$cat_param = isset($_GET['cat']) ? $_GET['cat'] : '0';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch Categories
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

// Fetch Products
$query = "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.cat_id = c.id WHERE 1=1";
$params = [];

if ($cat_param !== '0' && !empty($cat_param)) {
    if (is_numeric($cat_param)) {
        $query .= " AND p.cat_id = ?";
        $params[] = (int)$cat_param;
    } else {
        $query .= " AND c.name = ?";
        $params[] = $cat_param;
    }
}

if (!empty($search)) {
    $query .= " AND p.name LIKE ?";
    $params[] = '%' . $search . '%';
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Determine Page Title
$display_title = "All Devices";
if ($cat_param !== '0' && !empty($cat_param)) {
    if (is_numeric($cat_param)) {
        foreach($categories as $c) if($c['id'] == $cat_param) $display_title = $c['name'];
    } else {
        $display_title = htmlspecialchars($cat_param);
    }
}
?>

<main class="section-padding" style="padding-top: 5rem;">
    <div class="container">
        <!-- Explorer Header -->
        <div class="flex-between" style="margin-bottom: 8rem;">
            <div>
                <h1 style="font-size: var(--fs-h2); text-transform: capitalize; margin-bottom: 1rem;"><?= $display_title ?>.</h1>
                <p style="color: var(--text-secondary); font-size: 1.25rem;">Our most advanced hardware, curated for excellence.</p>
            </div>
            
            <form action="products.php" method="GET" class="w-100-mobile" style="display: flex; gap: 1rem; max-width: 450px; width: 100%;">
                <input type="text" name="search" placeholder="Search the collection..." value="<?= htmlspecialchars($search) ?>" 
                       style="padding: 1.25rem 2rem; flex: 1; border-radius: var(--radius-full); border: 1.5px solid var(--border); background: var(--bg-soft); outline: none; font-size: 1rem; transition: var(--transition);">
                <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem;">Search</button>
            </form>
        </div>

        <div class="layout-sidebar">
            <!-- SIDEBAR -->
            <aside>
                <div style="position: sticky; top: 140px;">
                    <h3 style="margin-bottom: 3rem; font-size: 0.85rem; color: var(--text-main); text-transform: uppercase; letter-spacing: 0.15em; font-weight: 800;">Collections</h3>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 1.8rem;">
                        <li><a href="products.php" style="font-size: 1.1rem; color: <?= $cat_param == '0' ? 'var(--accent)' : 'var(--text-secondary)' ?>; font-weight: <?= $cat_param == '0' ? '700' : '500' ?>;">All Products</a></li>
                        <?php foreach($categories as $cat): ?>
                            <li><a href="products.php?cat=<?= $cat['id'] ?>" style="font-size: 1.1rem; color: <?= ($cat_param == $cat['id'] || $cat_param == $cat['name']) ? 'var(--accent)' : 'var(--text-secondary)' ?>; font-weight: <?= ($cat_param == $cat['id'] || $cat_param == $cat['name']) ? '700' : '500' ?>;"><?= $cat['name'] ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <div style="margin-top: 6rem; padding: 2.5rem; background: var(--bg-soft); border-radius: var(--radius-lg); text-align: center;">
                        <h4 style="font-size: 1.2rem; margin-bottom: 1rem;">Need Help?</h4>
                        <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.6; margin-bottom: 1.5rem;">Talk to an expert about your tech needs.</p>
                        <a href="#" class="btn btn-outline" style="width: 100%; font-size: 0.85rem;">Contact Sales</a>
                    </div>
                </div>
            </aside>

            <!-- PRODUCT GRID -->
            <section>
                <?php if(empty($products)): ?>
                    <div style="text-align: center; padding: 10rem 0; background: var(--bg-soft); border-radius: var(--radius-xl);">
                        <h2 style="color: var(--text-secondary);">No items found in this collection.</h2>
                        <a href="products.php" style="display: inline-block; margin-top: 2rem; color: var(--accent); font-weight: 700;">View All Products &rarr;</a>
                    </div>
                <?php else: ?>
                    <div class="product-grid">
                        <?php foreach($products as $p): ?>
                        <div class="product-card fade-in">
                            <div style="background: var(--bg-soft); margin: -3rem -3rem 2.5rem -3rem; display: flex; align-items: center; justify-content: center; height: 350px; overflow: hidden;">
                                <img src="<?= htmlspecialchars(get_product_image($p['image'])) ?>" class="product-image" alt="<?= htmlspecialchars($p['name']) ?>" style="margin-bottom: 0; width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <span style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em;"><?= htmlspecialchars($p['category_name']) ?></span>
                            <h3 style="margin: 1rem 0; font-size: 1.8rem;"><?= htmlspecialchars($p['name']) ?></h3>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 3rem;">
                                <p style="font-weight: 800; font-size: 1.8rem;">₹<?= number_format($p['price']) ?></p>
                                <button class="btn btn-primary" onclick="addToCart(<?= $p['id'] ?>, event)">Add to Bag</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>

<!-- script.js handles addToCart globally -->
<?php include 'includes/footer.php'; ?>
