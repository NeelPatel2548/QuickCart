<?php 
require_once 'config.php'; 

if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php?redirect=checkout.php"); 
    exit(); 
}

$cart_items = [];
$total_price = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $pdo->prepare("SELECT p.* FROM products p WHERE p.id IN ($placeholders)");
    $stmt->execute($ids);
    $cart_items = $stmt->fetchAll();
    foreach($cart_items as $item) $total_price += $item['price'] * $_SESSION['cart'][$item['id']];
} else {
    header("Location: products.php"); exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['address']) || empty($_POST['payment_method'])) {
        $error = "Please provide all shipping and payment details.";
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, status, payment_method) VALUES (?, ?, 'Pending', ?)");
            $stmt->execute([$_SESSION['user_id'], $total_price, $_POST['payment_method']]);
            $order_id = $pdo->lastInsertId();

            $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach($cart_items as $item) {
                $stmt_item->execute([$order_id, $item['id'], $_SESSION['cart'][$item['id']], $item['price']]);
            }

            unset($_SESSION['cart']);
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
        <div style="margin-bottom: 8rem;">
            <h1 style="font-size: var(--fs-h1);">Secure <span style="color: var(--accent);">Checkout.</span></h1>
            <p style="color: var(--text-secondary); font-size: 1.4rem;">Finalize your premium acquisition with the highest security standards.</p>
        </div>

        <?php if($error): ?>
            <div style="background: #FEE; color: var(--danger); padding: 2rem; border-radius: var(--radius-md); margin-bottom: 3rem; font-weight: 700;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="checkoutForm">
            <?= csrf_field() ?>
            <div class="layout-cart">
                
                <!-- CHECKOUT FLOW -->
                <div style="display: grid; gap: 4rem;">
                    
                    <!-- Shipping Section -->
                    <section style="background: var(--white); padding: 5rem; border-radius: var(--radius-xl); box-shadow: var(--shadow-sm);" class="fade-in">
                        <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 4rem;">
                            <div style="width: 48px; height: 48px; background: var(--accent); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.4rem;">1</div>
                            <h2 style="font-size: 2.5rem; margin-bottom:0;">Shipping Address</h2>
                        </div>
                        <div style="display: grid; gap: 1.5rem;">
                            <label style="font-weight: 700; color: var(--text-secondary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em;">Delivery Location</label>
                            <textarea name="address" rows="4" placeholder="Full Address, City, State, ZIP..." 
                                      style="padding: 2rem; border-radius: var(--radius-lg); border: 2px solid var(--border); outline: none; font-size: 1.15rem; font-family: inherit; transition: var(--transition); resize: none;"></textarea>
                        </div>
                    </section>

                    <!-- Payment Section -->
                    <section style="background: var(--white); padding: 5rem; border-radius: var(--radius-xl); box-shadow: var(--shadow-sm);" class="fade-in">
                        <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 4rem;">
                            <div style="width: 48px; height: 48px; background: var(--accent); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.4rem;">2</div>
                            <h2 style="font-size: 2.5rem; margin-bottom:0;">Payment Method</h2>
                        </div>
                        <div style="display: grid; gap: 2rem;">
                            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                                <label style="cursor: pointer;">
                                    <input type="radio" name="payment_method" value="Card" style="display: none;" checked>
                                    <div style="padding: 2.5rem; border: 2px solid var(--accent); border-radius: var(--radius-lg); text-align: center; background: var(--white); transition: var(--transition);">
                                        <div style="font-size: 2rem; margin-bottom: 1rem;">💳</div>
                                        <p style="font-weight: 700; font-size: 1.2rem;">Credit Card</p>
                                    </div>
                                </label>
                                <label style="cursor: pointer;">
                                    <input type="radio" name="payment_method" value="COD" style="display: none;">
                                    <div style="padding: 2.5rem; border: 2px solid var(--border); border-radius: var(--radius-lg); text-align: center; background: var(--white); transition: var(--transition);">
                                        <div style="font-size: 2rem; margin-bottom: 1rem;">🚚</div>
                                        <p style="font-weight: 700; font-size: 1.2rem;">Cash on Delivery</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- ORDER SNAPSHOT -->
                <aside>
                    <div style="position: sticky; top: 140px; background: var(--white); padding: 5rem; border-radius: var(--radius-xl); box-shadow: var(--shadow-lg);" class="fade-in">
                        <h2 style="font-size: 2.2rem; margin-bottom: 3.5rem; padding-bottom: 1.5rem; border-bottom: 2.5px solid var(--accent);">Snapshot</h2>
                        
                        <div style="display: grid; gap: 1.5rem; margin-bottom: 3.5rem; max-height: 400px; overflow-y: auto;">
                            <?php foreach($cart_items as $item): ?>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: var(--text-secondary); font-size: 1.1rem;"><?= $item['name'] ?> (x<?= $_SESSION['cart'][$item['id']] ?>)</span>
                                    <span style="font-weight: 700; font-size: 1.1rem;">₹<?= number_format($item['price'] * $_SESSION['cart'][$item['id']]) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; padding-top: 2.5rem; border-top: 2px dashed var(--border); margin-bottom: 4rem;">
                            <span style="font-size: 1.8rem; font-weight: 500;">Grand Total</span>
                            <span style="font-size: 2.8rem; font-weight: 900; color: var(--accent);">₹<?= number_format($total_price) ?></span>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.5rem; font-size: 1.3rem; border-radius: var(--radius-full);">Complete Order</button>
                        
                        <div style="margin-top: 3rem; text-align: center; display: flex; align-items: center; justify-content: center; gap: 1rem; opacity: 0.6;">
                            <span style="font-size: 1.5rem;">🔒</span>
                            <span style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase;">AES-256 Encrypted Connection</span>
                        </div>
                    </div>
                </aside>

            </div>
        </form>
    </div>
</main>

<style>
    input[type="radio"]:checked + div {
        border-color: var(--accent) !important;
        background: var(--bg-soft) !important;
        box-shadow: 0 0 0 4px rgba(0, 113, 227, 0.1);
    }
    textarea:focus { border-color: var(--accent) !important; box-shadow: 0 0 0 4px rgba(0, 113, 227, 0.1); }
    
    /* Premium Loader Overlay */
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
    
    #payment-loader.active {
        display: flex;
        opacity: 1;
    }

    .spinner-ring {
        width: 80px;
        height: 80px;
        border: 4px solid rgba(0, 113, 227, 0.1);
        border-top-color: var(--accent);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 2rem;
    }

    @keyframes spin { 100% { transform: rotate(360deg); } }
</style>

<!-- Fake UI Payment Loader Element -->
<div id="payment-loader">
    <div class="spinner-ring"></div>
    <h2 style="font-size: 2rem; margin-bottom: 1rem;">Processing Secure Payment</h2>
    <p style="color: var(--text-secondary); font-size: 1.1rem; max-width: 400px; text-align: center;">Establishing AES-256 connection with financial institution. Please do not close this window.</p>
</div>

<script>
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Stop immediate submission
    
    const loader = document.getElementById('payment-loader');
    loader.classList.add('active');
    document.body.style.overflow = 'hidden'; // Prevent scrolling
    
    // Simulate payment processing delay (2 seconds)
    setTimeout(() => {
        this.submit(); // Actually submit the form
    }, 2000);
});
</script>

<?php include 'includes/footer.php'; ?>
