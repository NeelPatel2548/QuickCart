<?php 
require_once 'config.php'; 

// CSRF / Security or Cart Update Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['qty'] as $id => $qty) {
        if ($qty <= 0) unset($_SESSION['cart'][$id]);
        else $_SESSION['cart'][$id] = $qty;
    }
}

include 'includes/header.php'; 

$cart_items = [];
$total_price = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.cat_id = c.id WHERE p.id IN ($placeholders)");
    $stmt->execute($ids);
    $cart_items = $stmt->fetchAll();
}
?>

<main class="section-padding" style="background: var(--bg-soft); min-height: calc(100vh - 80px);">
    <div class="container">
        <div style="margin-bottom: 8rem;">
            <h1 style="font-size: var(--fs-h1);">Your <span style="color: var(--accent);">Bag.</span></h1>
            <p style="color: var(--text-secondary); font-size: 1.4rem;">Review your selection of premium electronics before checkout.</p>
        </div>

        <?php if(empty($cart_items)): ?>
            <div style="background: var(--white); border-radius: var(--radius-xl); padding: 8rem; text-align: center;" class="fade-in">
                <div style="font-size: 5rem; margin-bottom: 2rem; opacity: 0.2;">🛒</div>
                <h2 style="font-weight: 500; font-size: 2.2rem;">Your bag is empty.</h2>
                <p style="color: var(--text-secondary); margin-top: 1rem; font-size: 1.2rem;">Explore our latest innovations to find something special.</p>
                <a href="products.php" class="btn btn-primary" style="margin-top: 3rem; padding: 1.25rem 3rem;">Shop All Products</a>
            </div>
        <?php else: ?>
            <div class="layout-cart">
                <!-- ITEM LIST -->
                <form method="POST" id="cartForm">
                    <?= csrf_field() ?>
                    <div style="display: grid; gap: 3rem;">
                        <?php foreach($cart_items as $item): 
                            $qty = $_SESSION['cart'][$item['id']];
                            $subtotal = $item['price'] * $qty;
                            $total_price += $subtotal;
                        ?>
                        <div style="background: var(--white); border-radius: var(--radius-xl);" class="cart-item-row fade-in">
                            <div style="background: var(--bg-soft); border-radius: var(--radius-lg); padding: 2rem; display: flex; align-items: center; justify-content: center;" class="cart-item-img">
                                <img src="<?= htmlspecialchars(get_product_image($item['image'])) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            </div>
                            
                            <div>
                                <span style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em;"><?= htmlspecialchars($item['category_name']) ?></span>
                                <h3 style="font-size: 1.8rem; margin: 0.75rem 0;"><?= htmlspecialchars($item['name']) ?></h3>
                                <p style="color: var(--text-main); font-weight: 700; font-size: 1.25rem;">₹<?= number_format($item['price']) ?></p>
                                
                                <div style="display: flex; align-items: center; gap: 1.5rem; margin-top: 2rem;">
                                    <label style="font-size: 0.95rem; font-weight: 600;">Quantity</label>
                                    <input type="number" name="qty[<?= $item['id'] ?>]" value="<?= $qty ?>" min="1" 
                                           class="qty-input"
                                           style="width: 80px; padding: 0.8rem; border: 1.5px solid var(--border); border-radius: var(--radius-md); font-weight: 700; text-align: center; outline: none;">
                                    <button type="submit" name="update_cart" class="btn btn-outline" style="padding: 0.6rem 1rem; font-size: 0.8rem; border-radius: var(--radius-sm);">Update</button>
                                </div>
                            </div>

                            <div style="text-align: right;">
                                <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.5rem;">Subtotal</p>
                                <p style="font-size: 2rem; font-weight: 800; color: var(--text-main);">₹<?= number_format($subtotal) ?></p>
                                <a href="add_to_cart.php?action=remove&id=<?= $item['id'] ?>" style="display: block; margin-top: 1.5rem; color: var(--danger); font-size: 0.9rem; font-weight: 700;">Remove Item</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </form>

                <!-- STICKY SUMMARY -->
                <aside>
                    <div style="position: sticky; top: 140px; background: var(--white); padding: 5rem; border-radius: var(--radius-xl); box-shadow: var(--shadow-lg);" class="fade-in">
                        <h2 style="font-size: 2.2rem; margin-bottom: 3.5rem; padding-bottom: 1.5rem; border-bottom: 2.5px solid var(--accent);">Summary</h2>
                        
                        <div style="display: grid; gap: 1.8rem; margin-bottom: 3.5rem;">
                            <div style="display: flex; justify-content: space-between; font-size: 1.25rem;">
                                <span style="color: var(--text-secondary);">Subtotal</span>
                                <span style="font-weight: 700;">₹<?= number_format($total_price) ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 1.25rem;">
                                <span style="color: var(--text-secondary);">Shipping</span>
                                <span style="color: var(--success); font-weight: 900;">FREE</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 1.25rem;">
                                <span style="color: var(--text-secondary);">Tax</span>
                                <span style="font-weight: 700;">₹0.00</span>
                            </div>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; padding-top: 2.5rem; border-top: 2px dashed var(--border); margin-bottom: 4rem;">
                            <span style="font-size: 1.8rem; font-weight: 500;">Total</span>
                            <span style="font-size: 2.8rem; font-weight: 900; color: var(--accent);">₹<?= number_format($total_price) ?></span>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-primary" style="width: 100%; padding: 1.5rem; font-size: 1.3rem; border-radius: var(--radius-full);">Checkout Now</a>
                        <p style="text-align: center; margin-top: 2rem; color: var(--text-secondary); font-size: 0.95rem;">
                            Ready for ultra-fast performance?
                        </p>
                    </div>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
