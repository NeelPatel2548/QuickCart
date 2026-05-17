<?php 
require_once 'config.php'; 

if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php?redirect=checkout.php"); 
    exit(); 
}

$cart_items = [];
$subtotal = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $pdo->prepare("SELECT p.* FROM products p WHERE p.id IN ($placeholders)");
    $stmt->execute($ids);
    $cart_items = $stmt->fetchAll();
    foreach($cart_items as $item) $subtotal += $item['price'] * $_SESSION['cart'][$item['id']];
} else {
    header("Location: products.php"); exit();
}

// Handle promo code on checkout page too
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_promo'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) { die('Invalid request.'); }
    $code = strtoupper(trim($_POST['promo_code'] ?? ''));
    if ($code !== '') {
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
    header("Location: checkout.php"); exit();
}

if (isset($_GET['remove_promo'])) {
    unset($_SESSION['promo_code'], $_SESSION['promo_discount'], $_SESSION['promo_error']);
    header("Location: checkout.php"); exit();
}

// Calculate discount
$promo_active = isset($_SESSION['promo_code']) && isset($_SESSION['promo_discount']);
$discount_amount = $promo_active ? round($subtotal * $_SESSION['promo_discount'] / 100, 2) : 0;
$grand_total = $subtotal - $discount_amount;

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_order'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) { die('Invalid request.'); }
    if (empty($_POST['address']) || empty($_POST['payment_method'])) {
        $error = "Please provide all shipping and payment details.";
    } else {
        try {
            $pdo->beginTransaction();
            // Save discounted total
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, status, payment_method) VALUES (?, ?, 'Pending', ?)");
            $stmt->execute([$_SESSION['user_id'], $grand_total, $_POST['payment_method']]);
            $order_id = $pdo->lastInsertId();

            $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach($cart_items as $item) {
                $stmt_item->execute([$order_id, $item['id'], $_SESSION['cart'][$item['id']], $item['price']]);
            }

            unset($_SESSION['cart'], $_SESSION['promo_code'], $_SESSION['promo_discount'], $_SESSION['promo_error']);
            $pdo->commit();
            header("Location: success.php?id=$order_id");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("QuickCart checkout error: " . $e->getMessage());
            $error = "Order could not be processed. Please try again.";
        }
    }
}

include 'includes/header.php';
?>

<main class="section-padding" style="background: var(--bg-soft); min-height: calc(100vh - 80px);">
    <div class="container">
        <!-- Step Indicator -->
        <div class="checkout-steps">
            <div class="step active"><span class="step-num">1</span><span class="step-label">Shipping</span></div>
            <div class="step-line"></div>
            <div class="step active"><span class="step-num">2</span><span class="step-label">Payment</span></div>
            <div class="step-line"></div>
            <div class="step"><span class="step-num">3</span><span class="step-label">Confirm</span></div>
        </div>

        <div style="margin-bottom: 5rem;">
            <h1 style="font-size: var(--fs-h1);">Secure <span style="color: var(--accent);">Checkout.</span></h1>
            <p style="color: var(--text-secondary); font-size: 1.3rem;">Finalize your order with the highest security standards.</p>
        </div>

        <?php if($error): ?>
            <div class="checkout-error fade-in"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" id="checkoutForm">
            <?= csrf_field() ?>
            <input type="hidden" name="complete_order" value="1">
            <div class="layout-cart">
                
                <!-- CHECKOUT FLOW -->
                <div style="display: grid; gap: 3rem;">
                    
                    <!-- Shipping Section -->
                    <section class="checkout-section fade-in">
                        <div class="checkout-section-header">
                            <div class="checkout-step-badge">1</div>
                            <h2 style="font-size: 2rem; margin-bottom:0;">Shipping Address</h2>
                        </div>
                        <div style="display: grid; gap: 1.25rem;">
                            <label class="field-label">Delivery Location</label>
                            <textarea name="address" rows="4" placeholder="Full Address, City, State, ZIP..." class="checkout-textarea"><?= e($_POST['address'] ?? '') ?></textarea>
                        </div>
                    </section>

                    <!-- Payment Section -->
                    <section class="checkout-section fade-in">
                        <div class="checkout-section-header">
                            <div class="checkout-step-badge">2</div>
                            <h2 style="font-size: 2rem; margin-bottom:0;">Payment Method</h2>
                        </div>
                        <div class="payment-grid">
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="Card" checked>
                                <div class="payment-card">
                                    <div style="font-size: 2rem; margin-bottom: 0.75rem;">💳</div>
                                    <p style="font-weight: 700; font-size: 1.1rem;">Credit Card</p>
                                    <span class="payment-check">✓</span>
                                </div>
                            </label>
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="COD">
                                <div class="payment-card">
                                    <div style="font-size: 2rem; margin-bottom: 0.75rem;">🚚</div>
                                    <p style="font-weight: 700; font-size: 1.1rem;">Cash on Delivery</p>
                                    <span class="payment-check">✓</span>
                                </div>
                            </label>
                        </div>
                    </section>

                    <!-- Trust Badges -->
                    <div class="trust-row fade-in">
                        <div class="trust-badge"><span>🔒</span> SSL Encrypted</div>
                        <div class="trust-badge"><span>🛡️</span> Buyer Protection</div>
                        <div class="trust-badge"><span>↩️</span> Easy Returns</div>
                    </div>
                </div>

                <!-- ORDER SNAPSHOT SIDEBAR -->
                <aside>
                    <div class="summary-card fade-in">
                        <h2 class="summary-title">Order Snapshot</h2>
                        
                        <div style="display: grid; gap: 1rem; margin-bottom: 2rem; max-height: 320px; overflow-y: auto;">
                            <?php foreach($cart_items as $item): ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0;">
                                    <span style="color: var(--text-secondary); font-size: 0.95rem; flex: 1;"><?= e($item['name']) ?> <span style="opacity:0.5;">×<?= $_SESSION['cart'][$item['id']] ?></span></span>
                                    <span style="font-weight: 700; font-size: 0.95rem; white-space: nowrap; margin-left: 1rem;">₹<?= number_format($item['price'] * $_SESSION['cart'][$item['id']]) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Promo Code in Checkout -->
                        <div class="promo-section">
                            <?php if($promo_active): ?>
                                <div class="promo-applied">
                                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                                        <span style="font-weight: 800; color: var(--success); font-size: 0.85rem;"><?= $_SESSION['promo_discount'] ?>% OFF</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.4rem;">
                                        <span class="promo-badge"><?= e($_SESSION['promo_code']) ?></span>
                                        <a href="?remove_promo=1" style="color: var(--danger); font-size: 0.75rem; font-weight: 700;">Remove</a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <form method="POST" style="display: flex; gap: 0.5rem;">
                                    <?= csrf_field() ?>
                                    <input type="text" name="promo_code" placeholder="Promo code" class="promo-input">
                                    <button type="submit" name="apply_promo" class="btn btn-outline promo-apply-btn">Apply</button>
                                </form>
                                <?php if(isset($_SESSION['promo_error'])): ?>
                                    <p style="color: var(--danger); font-size: 0.75rem; font-weight: 600; margin-top: 0.4rem;"><?= e($_SESSION['promo_error']) ?></p>
                                    <?php unset($_SESSION['promo_error']); ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <div class="summary-rows">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span style="font-weight: 700;">₹<?= number_format($subtotal) ?></span>
                            </div>
                            <?php if($promo_active): ?>
                            <div class="summary-row" style="color: var(--success);">
                                <span>Promo (−<?= $_SESSION['promo_discount'] ?>%)</span>
                                <span style="font-weight: 700;">−₹<?= number_format($discount_amount) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="summary-row">
                                <span>Shipping</span>
                                <span style="color: var(--success); font-weight: 800;">FREE</span>
                            </div>
                        </div>
                        
                        <div class="summary-total">
                            <span>Grand Total</span>
                            <span class="summary-total-price">₹<?= number_format($grand_total) ?></span>
                        </div>
                        
                        <button type="submit" class="btn btn-primary checkout-btn" id="placeOrderBtn">Complete Order</button>
                        
                        <div style="margin-top: 1.5rem; text-align: center; display: flex; align-items: center; justify-content: center; gap: 0.6rem; opacity: 0.5;">
                            <span style="font-size: 1rem;">🔒</span>
                            <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">AES-256 Encrypted</span>
                        </div>
                    </div>
                </aside>

            </div>
        </form>
    </div>
</main>

<style>
/* === Checkout Steps === */
.checkout-steps {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 4rem;
    max-width: 400px;
}
.step {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    opacity: 0.35;
    transition: opacity 0.3s;
}
.step.active { opacity: 1; }
.step-num {
    width: 36px; height: 36px;
    background: var(--accent);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.9rem;
}
.step-label { font-weight: 600; font-size: 0.9rem; }
.step-line { flex: 1; height: 2px; background: var(--border); }

/* === Checkout Sections === */
.checkout-section {
    background: var(--white);
    padding: 3rem;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-sm);
}
.checkout-section-header {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    margin-bottom: 2.5rem;
}
.checkout-step-badge {
    width: 42px; height: 42px;
    background: var(--accent);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 1.2rem;
    flex-shrink: 0;
}
.field-label {
    font-weight: 700;
    color: var(--text-secondary);
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
}
.checkout-textarea {
    padding: 1.5rem;
    border-radius: var(--radius-lg);
    border: 2px solid var(--border);
    outline: none;
    font-size: 1.05rem;
    font-family: inherit;
    transition: border-color 0.3s, box-shadow 0.3s;
    resize: none;
    width: 100%;
}
.checkout-textarea:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 4px rgba(0, 113, 227, 0.1);
}
.checkout-error {
    background: #FEE2E2;
    color: var(--danger);
    padding: 1.25rem 1.5rem;
    border-radius: var(--radius-md);
    margin-bottom: 2rem;
    font-weight: 700;
    font-size: 0.95rem;
    border-left: 4px solid var(--danger);
}

/* === Payment Cards === */
.payment-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}
.payment-option input[type="radio"] { display: none; }
.payment-option { cursor: pointer; }
.payment-card {
    padding: 2rem;
    border: 2px solid var(--border);
    border-radius: var(--radius-lg);
    text-align: center;
    background: var(--white);
    transition: all 0.3s ease;
    position: relative;
}
.payment-check {
    position: absolute;
    top: 0.75rem; right: 0.75rem;
    width: 24px; height: 24px;
    background: var(--accent);
    color: white;
    border-radius: 50%;
    font-size: 0.7rem;
    font-weight: 900;
    display: none;
    align-items: center;
    justify-content: center;
}
.payment-option input:checked + .payment-card {
    border-color: var(--accent);
    background: #F0F7FF;
    box-shadow: 0 0 0 4px rgba(0, 113, 227, 0.08);
}
.payment-option input:checked + .payment-card .payment-check {
    display: flex;
}

/* === Trust Badges === */
.trust-row {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}
.trust-badge {
    flex: 1;
    min-width: 140px;
    background: var(--white);
    padding: 1.25rem;
    border-radius: var(--radius-lg);
    text-align: center;
    font-weight: 700;
    font-size: 0.85rem;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    box-shadow: var(--shadow-sm);
}

/* === Reuse summary styles from cart === */
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
    font-size: 0.85rem;
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
    font-size: 0.8rem !important;
    border-radius: var(--radius-full) !important;
    white-space: nowrap;
}
.promo-badge {
    background: var(--success);
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: var(--radius-full);
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.05em;
}
.promo-applied { padding: 0.25rem 0; }
.summary-rows {
    display: grid;
    gap: 1.25rem;
    margin-bottom: 2rem;
}
.summary-row {
    display: flex;
    justify-content: space-between;
    font-size: 1rem;
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

/* Mobile */
@media (max-width: 768px) {
    .checkout-steps { max-width: 100%; }
    .payment-grid { grid-template-columns: 1fr; }
    .checkout-section { padding: 2rem 1.5rem; }
    .summary-card { position: static; }
    .trust-row { flex-direction: column; }
}
</style>

<!-- Payment Loader Overlay -->
<div id="payment-loader">
    <div class="spinner-ring"></div>
    <h2 style="font-size: 1.8rem; margin-bottom: 1rem;">Processing Secure Payment</h2>
    <p style="color: var(--text-secondary); font-size: 1rem; max-width: 380px; text-align: center;">Establishing encrypted connection with your financial institution.</p>
</div>

<style>
#payment-loader {
    position: fixed;
    top: 0; left: 0; width: 100vw; height: 100vh;
    background: var(--glass-bg);
    backdrop-filter: var(--glass-blur);
    -webkit-backdrop-filter: var(--glass-blur);
    z-index: 9999;
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.4s ease;
}
#payment-loader.active { display: flex; opacity: 1; }
.spinner-ring {
    width: 64px; height: 64px;
    border: 4px solid rgba(0, 113, 227, 0.1);
    border-top-color: var(--accent);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 2rem;
}
@keyframes spin { 100% { transform: rotate(360deg); } }
</style>

<script>
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const loader = document.getElementById('payment-loader');
    loader.classList.add('active');
    document.body.style.overflow = 'hidden';
    setTimeout(() => { this.submit(); }, 2000);
});

// Fade-in observer
document.addEventListener('DOMContentLoaded', function() {
    const observer = new IntersectionObserver(entries => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('appear'); observer.unobserve(e.target); }});
    }, { threshold: 0.1 });
    document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
});
</script>

<?php include 'includes/footer.php'; ?>
