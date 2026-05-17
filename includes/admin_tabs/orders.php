<?php
$status_filter = $_GET['status'] ?? '';
$order_query = "SELECT o.*, u.username, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id";
$params = [];
if ($status_filter && in_array($status_filter, ALLOWED_STATUSES)) {
    $order_query .= " WHERE o.status = ?";
    $params[] = $status_filter;
}
$order_query .= " ORDER BY o.id DESC";
$stmt = $pdo->prepare($order_query);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Get order items for modal
$view_items = [];
if (isset($_GET['view_id'])) {
    $vi = $pdo->prepare("SELECT oi.*, p.name as product_name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $vi->execute([(int)$_GET['view_id']]);
    $view_items = $vi->fetchAll();
}
?>
<div class="page-header" style="display:flex;justify-content:space-between;align-items:center">
    <div><h1>Orders</h1><p><?= count($orders) ?> orders <?= $status_filter ? '('.e($status_filter).')' : 'total' ?>.</p></div>
    <div style="display:flex;gap:12px">
        <div class="search-box"><input type="text" id="searchOrders" placeholder="Search orders..."></div>
        <a href="admin.php?tab=orders&export=orders" class="btn btn-outline">📥 Export CSV</a>
    </div>
</div>

<!-- Status Filter Pills -->
<div style="display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap">
    <a href="admin.php?tab=orders" class="btn btn-sm <?= !$status_filter?'btn-primary':'btn-outline' ?>">All</a>
    <?php foreach(ALLOWED_STATUSES as $s): ?>
        <a href="admin.php?tab=orders&status=<?= $s ?>" class="btn btn-sm <?= $status_filter===$s?'btn-primary':'btn-outline' ?>"><?= $s ?></a>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-body" style="padding:0">
        <?php if(empty($orders)): ?>
            <div class="empty-state"><h3>No orders found</h3><p>Orders will appear here once customers start purchasing.</p></div>
        <?php else: ?>
        <table id="ordersTable">
            <thead><tr><th>Order</th><th>Customer</th><th>Amount</th><th>Payment</th><th>Date</th><th>Status</th><th>Update</th><th></th></tr></thead>
            <tbody>
            <?php foreach($orders as $o):
                $sc = match($o['status']) { 'Delivered'=>'success','Cancelled'=>'danger','Shipped'=>'primary',default=>'warning' };
            ?>
                <tr>
                    <td><strong>#QB-<?= $o['id'] ?></strong></td>
                    <td><?= e($o['username'] ?? 'Guest') ?><br><small style="color:var(--text-light)"><?= e($o['email'] ?? '') ?></small></td>
                    <td>₹<?= number_format($o['total_price']) ?></td>
                    <td><?= e($o['payment_method']) ?></td>
                    <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                    <td><span class="badge badge-<?= $sc ?>"><?= e($o['status']) ?></span></td>
                    <td>
                        <form method="POST" style="display:flex;gap:6px">
                            <?= csrf_field() ?>
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <select name="status" style="width:auto;padding:6px 10px;font-size:.8rem">
                                <?php foreach(ALLOWED_STATUSES as $s): ?>
                                    <option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>><?= $s ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-sm btn-primary">Save</button>
                        </form>
                    </td>
                    <td><a href="admin.php?tab=orders&view_id=<?= $o['id'] ?><?= $status_filter?'&status='.$status_filter:'' ?>" class="btn btn-sm btn-outline">View</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- View Order Items Modal -->
<?php if(!empty($view_items)): ?>
<div class="modal-overlay active" id="viewOrderModal">
    <div class="modal">
        <h3>Order #QB-<?= (int)$_GET['view_id'] ?> — Items</h3>
        <table>
            <thead><tr><th></th><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
            <tbody>
            <?php $item_total = 0; foreach($view_items as $vi): $sub = $vi['price'] * $vi['quantity']; $item_total += $sub; ?>
                <tr>
                    <td><img src="<?= get_product_image($vi['image']) ?>" class="thumb" alt=""></td>
                    <td><?= e($vi['product_name']) ?></td>
                    <td><?= $vi['quantity'] ?></td>
                    <td>₹<?= number_format($vi['price']) ?></td>
                    <td><strong>₹<?= number_format($sub) ?></strong></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot><tr><td colspan="4" style="text-align:right;font-weight:700">Total:</td><td><strong>₹<?= number_format($item_total) ?></strong></td></tr></tfoot>
        </table>
        <div style="text-align:right;margin-top:20px">
            <a href="admin.php?tab=orders<?= $status_filter?'&status='.$status_filter:'' ?>" class="btn btn-outline">Close</a>
        </div>
    </div>
</div>
<?php endif; ?>
