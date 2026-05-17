<?php
/**
 * QuickCart Admin — Backend Action Handlers
 * All POST/GET action processing for admin panel.
 * Included by admin.php before any HTML output.
 */

// --- ALLOWED ORDER STATUSES (ENUM whitelist) ---
define('ALLOWED_STATUSES', ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled']);

// Load image helper for product thumbnails
require_once __DIR__ . '/image_helper.php';

// ============================================================
// 1. SECURITY — strict admin verification
// ============================================================
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== 1) {
    header("Location: index.php"); exit();
}
$_admin_check = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$_admin_check->execute([$_SESSION['user_id']]);
$_admin_row = $_admin_check->fetch();
if (!$_admin_row || (int)$_admin_row['is_admin'] !== 1) {
    unset($_SESSION['is_admin']);
    header("Location: index.php"); exit();
}

$tab = $_GET['tab'] ?? 'dashboard';

// ============================================================
// 2. POST ACTION HANDLERS
// ============================================================

// --- Update Order Status ---
if (isset($_POST['update_status'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) { set_flash('error', 'Invalid CSRF token.'); header("Location: admin.php?tab=orders"); exit(); }
    $new_status = $_POST['status'] ?? '';
    if (!in_array($new_status, ALLOWED_STATUSES)) { set_flash('error', 'Invalid order status.'); header("Location: admin.php?tab=orders"); exit(); }
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, (int)$_POST['order_id']]);
    regenerate_csrf_token();
    set_flash('success', 'Order status updated.');
    header("Location: admin.php?tab=orders"); exit();
}

// --- Delete User (POST only) ---
if (isset($_POST['delete_user'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) { set_flash('error', 'Invalid CSRF token.'); header("Location: admin.php?tab=users"); exit(); }
    $pdo->prepare("DELETE FROM users WHERE id = ? AND is_admin = 0")->execute([(int)$_POST['delete_user_id']]);
    regenerate_csrf_token();
    set_flash('success', 'User removed successfully.');
    header("Location: admin.php?tab=users"); exit();
}

// --- Add Product ---
if (isset($_POST['add_product'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) { set_flash('error', 'Invalid CSRF token.'); header("Location: admin.php?tab=products"); exit(); }
    $sql = "INSERT INTO products (cat_id, name, price, description, rating, image, stock, is_featured) VALUES (?,?,?,?,?,?,?,?)";
    $pdo->prepare($sql)->execute([
        (int)$_POST['cat_id'], trim($_POST['name']), (float)$_POST['price'],
        trim($_POST['description']), (float)($_POST['rating'] ?? 4.5),
        trim($_POST['image'] ?: 'prod_default.jpg'), (int)($_POST['stock'] ?? 50),
        isset($_POST['is_featured']) ? 1 : 0
    ]);
    regenerate_csrf_token();
    set_flash('success', 'Product added successfully.');
    header("Location: admin.php?tab=products"); exit();
}

// --- Edit Product ---
if (isset($_POST['edit_product'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) { set_flash('error', 'Invalid CSRF token.'); header("Location: admin.php?tab=products"); exit(); }
    $sql = "UPDATE products SET cat_id=?, name=?, price=?, description=?, rating=?, image=?, stock=?, is_featured=? WHERE id=?";
    $pdo->prepare($sql)->execute([
        (int)$_POST['cat_id'], trim($_POST['name']), (float)$_POST['price'],
        trim($_POST['description']), (float)$_POST['rating'],
        trim($_POST['image'] ?: 'prod_default.jpg'), (int)$_POST['stock'],
        isset($_POST['is_featured']) ? 1 : 0, (int)$_POST['product_id']
    ]);
    regenerate_csrf_token();
    set_flash('success', 'Product updated.');
    header("Location: admin.php?tab=products"); exit();
}

// --- Delete Product ---
if (isset($_POST['delete_product'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) { set_flash('error', 'Invalid CSRF token.'); header("Location: admin.php?tab=products"); exit(); }
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([(int)$_POST['product_id']]);
    regenerate_csrf_token();
    set_flash('success', 'Product deleted.');
    header("Location: admin.php?tab=products"); exit();
}

// --- Add Offer ---
if (isset($_POST['add_offer'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) { set_flash('error', 'Invalid CSRF token.'); header("Location: admin.php?tab=offers"); exit(); }
    $pdo->prepare("INSERT INTO offers (title, promo_code, banner_image, discount_percent) VALUES (?,?,?,?)")->execute([
        trim($_POST['title']), strtoupper(trim($_POST['promo_code'])),
        trim($_POST['banner_image'] ?? ''), (int)$_POST['discount_percent']
    ]);
    regenerate_csrf_token();
    set_flash('success', 'Offer created.');
    header("Location: admin.php?tab=offers"); exit();
}

// --- Edit Offer ---
if (isset($_POST['edit_offer'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) { set_flash('error', 'Invalid CSRF token.'); header("Location: admin.php?tab=offers"); exit(); }
    $pdo->prepare("UPDATE offers SET title=?, promo_code=?, banner_image=?, discount_percent=? WHERE id=?")->execute([
        trim($_POST['title']), strtoupper(trim($_POST['promo_code'])),
        trim($_POST['banner_image'] ?? ''), (int)$_POST['discount_percent'], (int)$_POST['offer_id']
    ]);
    regenerate_csrf_token();
    set_flash('success', 'Offer updated.');
    header("Location: admin.php?tab=offers"); exit();
}

// --- Delete Offer ---
if (isset($_POST['delete_offer'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) { set_flash('error', 'Invalid CSRF token.'); header("Location: admin.php?tab=offers"); exit(); }
    $pdo->prepare("DELETE FROM offers WHERE id = ?")->execute([(int)$_POST['offer_id']]);
    regenerate_csrf_token();
    set_flash('success', 'Offer deleted.');
    header("Location: admin.php?tab=offers"); exit();
}

// --- Add Category ---
if (isset($_POST['add_category'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) { set_flash('error', 'Invalid CSRF token.'); header("Location: admin.php?tab=categories"); exit(); }
    $pdo->prepare("INSERT INTO categories (name, image) VALUES (?,?)")->execute([
        trim($_POST['cat_name']), trim($_POST['cat_image'] ?: 'cat_default.jpg')
    ]);
    regenerate_csrf_token();
    set_flash('success', 'Category added.');
    header("Location: admin.php?tab=categories"); exit();
}

// --- Edit Category ---
if (isset($_POST['edit_category'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) { set_flash('error', 'Invalid CSRF token.'); header("Location: admin.php?tab=categories"); exit(); }
    $pdo->prepare("UPDATE categories SET name=?, image=? WHERE id=?")->execute([
        trim($_POST['cat_name']), trim($_POST['cat_image'] ?: 'cat_default.jpg'), (int)$_POST['cat_id']
    ]);
    regenerate_csrf_token();
    set_flash('success', 'Category updated.');
    header("Location: admin.php?tab=categories"); exit();
}

// --- Delete Category ---
if (isset($_POST['delete_category'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) { set_flash('error', 'Invalid CSRF token.'); header("Location: admin.php?tab=categories"); exit(); }
    // Check product count first
    $cnt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE cat_id = ?");
    $cnt->execute([(int)$_POST['cat_id']]);
    $product_count = $cnt->fetchColumn();
    if ($product_count > 0) {
        set_flash('error', "Cannot delete: {$product_count} products still in this category. Move or delete them first.");
    } else {
        $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([(int)$_POST['cat_id']]);
        set_flash('success', 'Category deleted.');
    }
    regenerate_csrf_token();
    header("Location: admin.php?tab=categories"); exit();
}

// --- CSV Export Orders ---
if (isset($_GET['export']) && $_GET['export'] === 'orders') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="quickcart_orders_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Order ID', 'User', 'Total', 'Status', 'Payment', 'Date']);
    $rows = $pdo->query("SELECT o.id, u.username, o.total_price, o.status, o.payment_method, o.created_at FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.id DESC")->fetchAll();
    foreach ($rows as $r) { fputcsv($out, $r); }
    fclose($out);
    exit();
}

// ============================================================
// 3. DATA FETCHING (used by dashboard and tabs)
// ============================================================
$total_revenue = $pdo->query("SELECT COALESCE(SUM(total_price),0) FROM orders WHERE status='Delivered'")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('Pending','Processing')")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin=0")->fetchColumn();
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

// Low stock products (stock < 10)
$low_stock = $pdo->query("SELECT id, name, stock FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 5")->fetchAll();

// Recent orders
$recent_orders = $pdo->query("SELECT o.*, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5")->fetchAll();

// Top selling products
$top_products = $pdo->query("SELECT p.name, p.image, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price) as total_revenue FROM order_items oi JOIN products p ON oi.product_id = p.id GROUP BY p.id ORDER BY total_sold DESC LIMIT 5")->fetchAll();

// Categories for dropdowns
$all_categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$flash = get_flash();
