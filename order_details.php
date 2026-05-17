<?php 
require_once 'config.php'; 

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) { die("Order not found."); }

$item_stmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.image, c.name as category_name 
                            FROM order_items oi 
                            JOIN products p ON oi.product_id = p.id 
                            JOIN categories c ON p.cat_id = c.id
                            WHERE oi.order_id = ?");
$item_stmt->execute([$order_id]);
$items = $item_stmt->fetchAll();

$status_steps = ['Pending', 'Processing', 'Shipped', 'Delivered'];
$current_index = array_search($order['status'], $status_steps);
if ($current_index === false) $current_index = 0;

include 'includes/header.php';
?>

<main class="section-padding" style="background: var(--bg-soft); min-height: calc(100vh - 80px);">
    <div class="container">
        
        <!-- Back Navigation -->
        <div style="margin-bottom: 6rem;">
            <a href="profile.php" style="color: var(--text-secondary); font-weight: 800; font-size: 1rem; text-transform: uppercase; letter-spacing: 0.1em; display: flex; align-items: center; gap: 0.75rem;">
                &larr; Back to Dashboard
            </a>
            <h1 style="font-size: var(--fs-h1); margin-top: 2rem;">Order <span style="color: var(--accent);">#VE-<?= $order_id ?></span></h1>
            <p style="color: var(--text-secondary); font-size: 1.4rem;">Real-time shipment telemetry for your QuickCart hardware.</p>
        </div>

        <div class="layout-cart">
            <!-- TRACKING & ITEMS -->
            <section>
                <!-- Modern Progress Bento -->
                <div style="background: var(--white); padding: 5rem; border-radius: var(--radius-xl); box-shadow: var(--shadow-sm); margin-bottom: 4rem;" class="fade-in">
                    <h3 style="font-size: 2rem; margin-bottom: 5rem;">Shipment Telemetry</h3>
                    
                    <div style="display: flex; justify-content: space-between; position: relative; padding: 0 2rem;">
                        <!-- Progress Background -->
                        <div style="position: absolute; top: 15px; left: 2rem; right: 2rem; height: 6px; background: var(--bg-soft); z-index: 1; border-radius: 10px;"></div>
                        <!-- Progress Active -->
                        <div style="position: absolute; top: 15px; left: 2rem; width: calc(<?= ($current_index / (count($status_steps) - 1)) * 100 ?>% - 4rem); height: 6px; background: var(--accent); z-index: 2; border-radius: 10px; transition: 2s cubic-bezier(0.65, 0, 0.35, 1);"></div>

                        <?php foreach($status_steps as $index => $step): ?>
                            <div style="position: relative; z-index: 3; text-align: center; flex: 1;">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: <?= $index <= $current_index ? 'var(--accent)' : 'var(--white)' ?>; border: 4px solid <?= $index <= $current_index ? 'var(--accent)' : 'var(--border)' ?>; margin: 0 auto; display: flex; align-items: center; justify-content: center; transition: 0.8s; box-shadow: <?= $index <= $current_index ? '0 0 20px rgba(0, 113, 227, 0.3)' : 'none' ?>;">
                                    <?php if($index <= $current_index): ?>
                                        <span style="color: white; font-size: 1rem; font-weight: 900;">✓</span>
                                    <?php endif; ?>
                                </div>
                                <p style="margin-top: 2rem; font-size: 1.1rem; font-weight: <?= $index == $current_index ? '800' : '600' ?>; color: <?= $index <= $current_index ? 'var(--text-main)' : 'var(--text-secondary)' ?>;"><?= $step ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Package Details -->
                <div style="background: var(--white); padding: 5rem; border-radius: var(--radius-xl); box-shadow: var(--shadow-sm);" class="fade-in">
                    <h3 style="font-size: 2rem; margin-bottom: 3.5rem;">Package Contents</h3>
                    <div style="display: grid; gap: 0;">
                        <?php foreach($items as $index => $item): ?>
                            <div class="order-item-row" style="border-top: <?= $index === 0 ? 'none' : '1px solid var(--border)' ?>;">
                                <div style="background: var(--bg-soft); border-radius: var(--radius-md); padding: 1.5rem; display: flex; align-items: center; justify-content: center;" class="cart-item-img">
                                    <img src="<?= htmlspecialchars(get_product_image($item['image'])) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                </div>
                                <div>
                                    <span style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em;"><?= htmlspecialchars($item['category_name']) ?></span>
                                    <h4 style="font-size: 1.6rem; margin-top: 0.75rem;"><?= htmlspecialchars($item['product_name']) ?></h4>
                                    <p style="color: var(--text-secondary); font-size: 1.1rem; margin-top: 0.75rem;">Quantity: <?= $item['quantity'] ?> &bull; Unit: ₹<?= number_format($item['price']) ?></p>
                                </div>
                                <p style="font-weight: 800; font-size: 2rem; text-align: right;">₹<?= number_format($item['price'] * $item['quantity']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- SIDEBAR LOG -->
            <aside>
                <div style="position: sticky; top: 140px; background: var(--white); padding: 4.5rem; border-radius: var(--radius-xl); box-shadow: var(--shadow-lg);" class="fade-in">
                    <h2 style="font-size: 2.2rem; margin-bottom: 3.5rem; padding-bottom: 1.5rem; border-bottom: 2.5px solid var(--accent);">Financials</h2>
                    
                    <div style="display: grid; gap: 1.8rem; margin-bottom: 3.5rem;">
                        <div style="display: flex; justify-content: space-between; font-size: 1.2rem;">
                            <span style="color: var(--text-secondary);">Subtotal</span>
                            <span style="font-weight: 700;">₹<?= number_format($order['total_price']) ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 1.2rem;">
                            <span style="color: var(--text-secondary);">Logistics</span>
                            <span style="color: var(--success); font-weight: 900;">INCLUDED</span>
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; padding-top: 2.5rem; border-top: 2px dashed var(--border); margin-bottom: 4rem;">
                        <span style="font-size: 1.8rem; font-weight: 500;">Paid</span>
                        <span style="font-size: 2.8rem; font-weight: 900; color: var(--accent);">₹<?= number_format($order['total_price']) ?></span>
                    </div>
                    
                    <div style="padding-top: 3rem; border-top: 1.5px solid var(--border);">
                        <p style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 800; text-transform: uppercase;">Method</p>
                        <p style="font-weight: 700; font-size: 1.3rem; margin-top: 1rem;"><?= $order['payment_method'] ?></p>
                        <p style="margin-top: 1.5rem; color: var(--text-secondary); font-size: 0.95rem;"><?= date('M d, Y', strtotime($order['created_at'])) ?></p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
