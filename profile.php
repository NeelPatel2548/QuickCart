<?php 
require_once 'config.php'; 

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$order_stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$order_stmt->execute([$user_id]);
$orders = $order_stmt->fetchAll();

include 'includes/header.php';
?>

<main class="section-padding" style="background: var(--bg-soft); min-height: calc(100vh - 80px);">
    <div class="container">
        
        <!-- Dashboard Header -->
        <div class="flex-between align-end fade-in" style="margin-bottom: 8rem;">
            <div>
                <span style="color: var(--accent); font-weight: 800; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.2em;">Welcome Back</span>
                <h1 style="font-size: var(--fs-h1); margin-top: 1rem;"><?= htmlspecialchars($user['username']) ?>.</h1>
                <p style="color: var(--text-secondary); font-size: 1.4rem;">Manager your premium hardware collection and order history.</p>
            </div>
            <div style="display: flex; gap: 1.5rem;">
                <a href="logout.php" class="btn btn-outline" style="padding: 1.25rem 2.5rem; border-color: var(--danger); color: var(--danger);">Sign Out</a>
            </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="layout-sidebar">
            
            <!-- SIDEBAR NAV -->
            <aside>
                <div style="position: sticky; top: 140px; background: var(--white); padding: 4rem; border-radius: var(--radius-xl); box-shadow: var(--shadow-sm);">
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 2.5rem;">
                        <li><a href="#" style="font-size: 1.25rem; font-weight: 800; color: var(--accent); display: flex; align-items: center; gap: 1rem;">📦 Order History</a></li>
                        <li><a href="#" style="font-size: 1.25rem; font-weight: 500; color: var(--text-secondary); display: flex; align-items: center; gap: 1rem;">⚙️ Account Settings</a></li>
                        <li><a href="#" style="font-size: 1.25rem; font-weight: 500; color: var(--text-secondary); display: flex; align-items: center; gap: 1rem;">💳 Payment Methods</a></li>
                        <li><a href="#" style="font-size: 1.25rem; font-weight: 500; color: var(--text-secondary); display: flex; align-items: center; gap: 1rem;">🛠️ Support</a></li>
                    </ul>
                </div>
            </aside>

            <!-- MAIN CONTENT (Order History) -->
            <section>
                <h2 style="font-size: 2.5rem; margin-bottom: 4rem;">Recent Orders <span style="color: var(--text-secondary); font-weight: 400; font-size: 1.25rem;">(<?= count($orders) ?>)</span></h2>

                <?php if(empty($orders)): ?>
                    <div style="background: var(--white); padding: 8rem; border-radius: var(--radius-xl); text-align: center;" class="fade-in">
                        <div style="font-size: 4rem; margin-bottom: 2rem; opacity: 0.2;">📦</div>
                        <h3 style="font-size: 1.8rem;">No orders yet.</h3>
                        <p style="color: var(--text-secondary); margin-top: 1rem;">Your future tech is waiting for you in the store.</p>
                        <a href="products.php" class="btn btn-primary" style="margin-top: 3rem;">Browse Devices</a>
                    </div>
                <?php else: ?>
                    <div style="display: grid; gap: 3rem;">
                        <?php foreach($orders as $order): ?>
                            <div style="background: var(--white); padding: 4rem; border-radius: var(--radius-xl); box-shadow: var(--shadow-sm);" class="flex-between fade-in">
                                <div>
                                    <div style="display: flex; gap: 1.5rem; margin-bottom: 1.5rem; align-items: center;">
                                        <span style="font-size: 0.85rem; font-weight: 800; text-transform: uppercase; color: var(--text-secondary);">#VE-<?= $order['id'] ?></span>
                                        <span style="background: <?= $order['status'] == 'Delivered' ? 'var(--success)' : 'var(--accent)' ?>; color: white; padding: 0.5rem 1.25rem; border-radius: 50px; font-size: 0.8rem; font-weight: 800;"><?= $order['status'] ?></span>
                                    </div>
                                    <h3 style="font-size: 1.8rem; margin-bottom: 1.25rem;">₹<?= number_format($order['total_price']) ?></h3>
                                    <p style="color: var(--text-secondary); font-size: 1rem;"><?= date('F d, Y', strtotime($order['created_at'])) ?> &bull; <?= $order['payment_method'] ?></p>
                                </div>
                                <div style="text-align: right;">
                                    <a href="order_details.php?id=<?= $order['id'] ?>" class="btn btn-primary" style="padding: 1.25rem 2.5rem;">Track Shipment</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
