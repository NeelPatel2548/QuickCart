<div class="page-header">
    <h1>Dashboard</h1>
    <p>System overview and key metrics at a glance.</p>
</div>

<div class="stats">
    <div class="stat-card">
        <small>Total Revenue</small>
        <h2 style="color:var(--success)">₹<?= number_format($total_revenue, 2) ?></h2>
    </div>
    <div class="stat-card">
        <small>Total Orders</small>
        <h2><?= $total_orders ?></h2>
    </div>
    <div class="stat-card">
        <small>Pending / Processing</small>
        <h2 style="color:var(--warning)"><?= $pending_orders ?></h2>
    </div>
    <div class="stat-card">
        <small>Customers</small>
        <h2><?= $total_users ?></h2>
    </div>
    <div class="stat-card">
        <small>Products</small>
        <h2><?= $total_products ?></h2>
    </div>
</div>

<div class="grid-2">
    <!-- Low Stock Alerts -->
    <div class="card">
        <div class="card-header"><h3>⚠️ Low Stock Alerts</h3></div>
        <div class="card-body">
            <?php if(empty($low_stock)): ?>
                <p style="color:var(--text-light)">All products are well-stocked.</p>
            <?php else: ?>
                <table>
                    <thead><tr><th>Product</th><th>Stock</th></tr></thead>
                    <tbody>
                    <?php foreach($low_stock as $ls): ?>
                        <tr>
                            <td><?= e($ls['name']) ?></td>
                            <td><span class="<?= $ls['stock'] <= 0 ? 'stock-out' : 'stock-low' ?>"><?= $ls['stock'] ?> left</span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="card">
        <div class="card-header"><h3>🕐 Recent Orders</h3></div>
        <div class="card-body">
            <?php if(empty($recent_orders)): ?>
                <p style="color:var(--text-light)">No orders yet.</p>
            <?php else: ?>
                <table>
                    <thead><tr><th>ID</th><th>Customer</th><th>Amount</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach($recent_orders as $ro): ?>
                        <tr>
                            <td>#<?= $ro['id'] ?></td>
                            <td><?= e($ro['username'] ?? 'Guest') ?></td>
                            <td>₹<?= number_format($ro['total_price']) ?></td>
                            <td>
                                <?php
                                $sc = match($ro['status']) {
                                    'Delivered' => 'success', 'Cancelled' => 'danger',
                                    'Shipped' => 'primary', default => 'warning'
                                };
                                ?>
                                <span class="badge badge-<?= $sc ?>"><?= e($ro['status']) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Top Selling Products -->
<?php if(!empty($top_products)): ?>
<div class="card" style="margin-top:8px">
    <div class="card-header"><h3>🏆 Top Selling Products</h3></div>
    <div class="card-body">
        <table>
            <thead><tr><th>Product</th><th>Units Sold</th><th>Revenue</th></tr></thead>
            <tbody>
            <?php foreach($top_products as $tp): ?>
                <tr>
                    <td style="display:flex;align-items:center;gap:12px">
                        <img src="<?= get_product_image($tp['image']) ?>" class="thumb" alt="">
                        <?= e($tp['name']) ?>
                    </td>
                    <td><strong><?= $tp['total_sold'] ?></strong></td>
                    <td>₹<?= number_format($tp['total_revenue']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
