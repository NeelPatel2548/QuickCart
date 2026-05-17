<?php 
require_once 'config.php'; 

// Handle cart quantity updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) { die('Invalid request.'); }
    foreach ($_POST['qty'] as $id => $qty) {
        if ($qty <= 0) unset($_SESSION['cart'][$id]);
        else $_SESSION['cart'][$id] = (int)$qty;
    }
    header("Location: cart.php"); exit();
}

// Handle promo code application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_promo'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) { die('Invalid request.'); }
    $code = strtoupper(trim($_POST['promo_code'] ?? ''));
    if ($code === '') {
        $_SESSION['promo_error'] = 'Please enter a promo code.';
        unset($_SESSION['promo_code'], $_SESSION['promo_discount']);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM offers WHERE UPPER(promo_code) = ? LIMIT 1");
        $stmt->execute([$code]);
        $offer = $stmt->fetch();
        if ($offer) {
            $_SESSION['promo_code'] = $offer['promo_code'];
            $_SESSION['promo_discount'] = (int)$offer['discount_percent'];
            unset($_SESSION['promo_error']);
        } else {
            $_SESSION['promo_error'] = 'Invalid promo code.';
            unset($_SESSION['promo_code'], $_SESSION['promo_discount']);
        }
    }
    header("Location: cart.php"); exit();
}

// Handle promo removal
if (isset($_GET['remove_promo'])) {
    unset($_SESSION['promo_code'], $_SESSION['promo_discount'], $_SESSION['promo_error']);
    header("Location: cart.php"); exit();
}

include 'includes/header.php'; 

$cart_items = [];
$subtotal = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.cat_id = c.id WHERE p.id IN ($placeholders)");
    $stmt->execute($ids);
    $cart_items = $stmt->fetchAll();
}

$discount_amount = 0;
$promo_active = isset($_SESSION['promo_code']) && isset($_SESSION['promo_discount']);
?>

<main class="section-padding" style="background: var(--bg-soft); min-height: calc(100vh - 80px);">
    <div class="container">
        <div style="margin-bottom: 5rem;">
            <span style="color: var(--accent); font-weight: 800; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.15em; display: block; margin-bottom: 1rem;">Shopping Bag</span>
            <h1 style="font-size: var(--fs-h1);">Your <span style="color: var(--accent);">Bag.</span></h1>
            <p style="color: var(--text-secondary); font-size: 1.3rem; margin-top: 1rem;">Review your selection of premium electronics before checkout.</p>
        </div>

        <?php if(empty($cart_items)): ?>
            <!-- EMPTY CART STATE -->
            <div class="fade-in" style="background: var(--white); border-radius: var(--radius-xl); padding: 6rem 4rem; text-align: center; box-shadow: var(--shadow-sm);">
                <div style="width: 120px; height: 120px; background: var(--bg-soft); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2.5rem; font-size: 3.5rem;">🛒</div>
                <h2 style="font-weight: 700; font-size: 2rem; margin-bottom: 1rem;">Your bag is empty</h2>
                <p style="color: var(--text-secondary); font-size: 1.15rem; max-width: 420px; margin: 0 auto 3rem;">Explore our latest innovations to find something special for your setup.</p>
                <a href="products.php" class="btn btn-primary" style="padding: 1.25rem 3.5rem; font-size: 1.1rem;">Shop All Products</a>
            </div>
        <?php else: ?>
            <div class="layout-cart">
                <!-- ITEM LIST -->
                <div>
                    <form method="POST" id="cartForm">
                        <?= csrf_field() ?>
                        <div style="display: grid; gap: 2rem;">
                            <?php foreach($cart_items as $item): 
                                $qty = $_SESSION['cart'][$item['id']];
                                $line_total = $item['price'] * $qty;
                                $subtotal += $line_total;
                            ?>
                            <div class="cart-card fade-in">
                                <div class="cart-card-img">
                                    <img src="<?= htmlspecialchars(get_product_image($item['image'])) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                </div>
                                <div class="cart-card-info">
                                    <span class="cart-card-cat"><?= htmlspecialchars($item['category_name']) ?></span>
                                    <h3 class="cart-card-name"><?= htmlspecialchars($item['name']) ?></h3>
                                    <p class="cart-card-price">₹<?= number_format($item['price']) ?></p>
                                    <div class="qty-controls">
                                        <button type="button" class="qty-btn" onclick="changeQty(this, -1)" aria-label="Decrease">−</button>
                                        <input type="number" name="qty[<?= $item['id'] ?>]" value="<?= $qty ?>" min="1" max="99" class="qty-input" readonly>
                                        <button type="button" class="qty-btn" onclick="changeQty(this, 1)" aria-label="Increase">+</button>
                                    </div>
                                </div>
                                <div class="cart-card-end">
                                    <p class="cart-card-subtotal">₹<?= number_format($line_total) ?></p>
                                    <a href="add_to_cart.php?action=remove&id=<?= $item['id'] ?>" class="cart-remove-link">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                        Remove
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="submit" name="update_cart" id="updateCartBtn" class="btn btn-outline" style="margin-top: 1.5rem; padding: 0.8rem 2rem; font-size: 0.9rem; display: none;">Update Bag</button>
                    </form>
                </div>

                <!-- STICKY SUMMARY SIDEBAR -->
                <aside>
                    <div class="summary-card fade-in">
                        <h2 class="summary-title">Summary</h2>
                        
                        <!-- Promo Code Section -->
                        <div class="promo-section">
                            <?php if($promo_active): ?>
                                <div class="promo-applied">
                                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                                        <span style="font-weight: 800; color: var(--success); font-size: 0.9rem;"><?= $_SESSION['promo_discount'] ?>% OFF applied</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.5rem;">
                                        <span class="promo-badge"><?= e($_SESSION['promo_code']) ?></span>
                                        <a href="?remove_promo=1" style="color: var(--danger); font-size: 0.8rem; font-weight: 700;">Remove</a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <form method="POST" style="display: flex; gap: 0.75rem;">
                                    <?= csrf_field() ?>
                                    <input type="text" name="promo_code" placeholder="Promo code" class="promo-input" value="">
                                    <button type="submit" name="apply_promo" class="btn btn-outline promo-apply-btn">Apply</button>
                                </form>
                                <?php if(isset($_SESSION['promo_error'])): ?>
                                    <p style="color: var(--danger); font-size: 0.8rem; font-weight: 600; margin-top: 0.5rem;"><?= e($_SESSION['promo_error']) ?></p>
                                    <?php unset($_SESSION['promo_error']); ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <?php
                            if ($promo_active) {
                                $discount_amount = round($subtotal * $_SESSION['promo_discount'] / 100, 2);
                            }
                            $grand_total = $subtotal - $discount_amount;
                        ?>

                        <div class="summary-rows">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span style="font-weight: 700;">₹<?= number_format($subtotal) ?></span>
                            </div>
                            <?php if($promo_active): ?>
                            <div class="summary-row" style="color: var(--success);">
                                <span>Discount (<?= $_SESSION['promo_discount'] ?>%)</span>
                                <span style="font-weight: 700;">−₹<?= number_format($discount_amount) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="summary-row">
                                <span>Shipping</span>
                                <span style="color: var(--success); font-weight: 800;">FREE</span>
                            </div>
                        </div>
                        
                        <div class="summary-total">
                            <span>Total</span>
                            <span class="summary-total-price">₹<?= number_format($grand_total) ?></span>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-primary checkout-btn">Proceed to Checkout</a>
                        <p style="text-align: center; margin-top: 1.5rem; color: var(--text-secondary); font-size: 0.85rem;">
                            🔒 Secure checkout guaranteed
                        </p>
                    </div>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
/* === Cart Card === */
.cart-card {
    background: var(--white);
    border-radius: var(--radius-xl);
    display: grid;
    grid-template-columns: 160px 1fr 160px;
    gap: 2.5rem;
    padding: 2.5rem;
    align-items: center;
    transition: box-shadow 0.3s ease, transform 0.3s ease;
    border: 1.5px solid transparent;
}
.cart-card:hover {
    box-shadow: var(--shadow-md);
    border-color: var(--border);
}
.cart-card-img {
    background: var(--bg-soft);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    aspect-ratio: 1;
}
.cart-card-img img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}
.cart-card-cat {
    font-size: 0.75rem;
    color: var(--text-secondary);
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.12em;
}
.cart-card-name {
    font-size: 1.5rem;
    margin: 0.5rem 0;
    font-weight: 700;
}
.cart-card-price {
    color: var(--text-main);
    font-weight: 700;
    font-size: 1.1rem;
}
.cart-card-end {
    text-align: right;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 1.25rem;
}
.cart-card-subtotal {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--text-main);
}
.cart-remove-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    color: var(--danger);
    font-size: 0.85rem;
    font-weight: 700;
    transition: opacity 0.3s;
}
.cart-remove-link:hover { opacity: 0.7; }

/* === Quantity Controls === */
.qty-controls {
    display: inline-flex;
    align-items: center;
    gap: 0;
    margin-top: 1.25rem;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-full);
    overflow: hidden;
}
.qty-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: var(--bg-soft);
    font-size: 1.2rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
    color: var(--text-main);
}
.qty-btn:hover { background: var(--border); }
.qty-btn:active { transform: scale(0.92); }
.qty-input {
    width: 48px;
    height: 40px;
    border: none;
    border-left: 1.5px solid var(--border);
    border-right: 1.5px solid var(--border);
    text-align: center;
    font-weight: 800;
    font-size: 1rem;
    background: var(--white);
    -moz-appearance: textfield;
    outline: none;
}
.qty-input::-webkit-outer-spin-button,
.qty-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

/* === Summary Card === */
.summary-card {
    position: sticky;
    top: 120px;
    background: var(--white);
    padding: 3rem;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-lg);
}
.summary-title {
    font-size: 1.8rem;
    margin-bottom: 2rem;
    padding-bottom: 1.25rem;
    border-bottom: 2.5px solid var(--accent);
}
.promo-section {
    background: var(--bg-soft);
    border-radius: var(--radius-lg);
    padding: 1.25rem;
    margin-bottom: 2rem;
}
.promo-input {
    flex: 1;
    padding: 0.75rem 1rem;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-full);
    font-size: 0.9rem;
    font-weight: 600;
    outline: none;
    font-family: inherit;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    transition: border-color 0.3s;
}
.promo-input:focus { border-color: var(--accent); }
.promo-apply-btn {
    padding: 0.75rem 1.25rem !important;
    font-size: 0.85rem !important;
    border-radius: var(--radius-full) !important;
    white-space: nowrap;
}
.promo-badge {
    background: var(--success);
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: var(--radius-full);
    font-size: 0.8rem;
    font-weight: 800;
    letter-spacing: 0.05em;
}
.promo-applied {
    padding: 0.25rem 0;
}
.summary-rows {
    display: grid;
    gap: 1.25rem;
    margin-bottom: 2rem;
}
.summary-row {
    display: flex;
    justify-content: space-between;
    font-size: 1.05rem;
    color: var(--text-secondary);
}
.summary-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1.5rem;
    border-top: 2px dashed var(--border);
    margin-bottom: 2.5rem;
}
.summary-total span:first-child {
    font-size: 1.4rem;
    font-weight: 500;
}
.summary-total-price {
    font-size: 2.2rem;
    font-weight: 900;
    color: var(--accent);
}
.checkout-btn {
    width: 100%;
    padding: 1.4rem;
    font-size: 1.15rem;
    border-radius: var(--radius-full);
    text-align: center;
}

/* === Mobile === */
@media (max-width: 768px) {
    .cart-card {
        grid-template-columns: 1fr !important;
        gap: 1.5rem !important;
        padding: 2rem 1.5rem !important;
        text-align: center;
    }
    .cart-card-img { max-width: 200px; margin: 0 auto; }
    .cart-card-end { align-items: center; text-align: center; }
    .qty-controls { justify-content: center; }
    .summary-card { position: static; }
}
</style>

<script>
function changeQty(btn, delta) {
    const input = btn.parentElement.querySelector('.qty-input');
    let val = parseInt(input.value) || 1;
    val = Math.max(1, Math.min(99, val + delta));
    input.value = val;
    // Show update button
    document.getElementById('updateCartBtn').style.display = 'inline-flex';
    // Animate the button
    btn.style.transform = 'scale(0.85)';
    setTimeout(() => btn.style.transform = '', 150);
}

// Fade-in observer
document.addEventListener('DOMContentLoaded', function() {
    const observer = new IntersectionObserver(entries => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('appear'); observer.unobserve(e.target); }});
    }, { threshold: 0.1 });
    document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
});
</script>

<?php include 'includes/footer.php'; ?>
