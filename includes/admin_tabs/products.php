<?php
$products = $pdo->query("SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.cat_id = c.id ORDER BY p.id DESC")->fetchAll();
$edit_product = null;
if (isset($_GET['edit_id'])) {
    $ep = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $ep->execute([(int)$_GET['edit_id']]);
    $edit_product = $ep->fetch();
}
?>
<div class="page-header" style="display:flex;justify-content:space-between;align-items:center">
    <div><h1>Products</h1><p>Manage your product catalog.</p></div>
    <div style="display:flex;gap:12px">
        <div class="search-box"><input type="text" id="searchProducts" placeholder="Search products..."></div>
        <button class="btn btn-primary" onclick="openModal('addProductModal')">+ Add Product</button>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding:0">
        <table id="productsTable">
            <thead><tr><th></th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Rating</th><th>Featured</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach($products as $p): ?>
                <tr>
                    <td><img src="<?= get_product_image($p['image']) ?>" class="thumb" alt=""></td>
                    <td><strong><?= e($p['name']) ?></strong></td>
                    <td><span class="badge badge-primary"><?= e($p['cat_name']) ?></span></td>
                    <td>₹<?= number_format($p['price']) ?></td>
                    <td><span class="<?= $p['stock']<=0?'stock-out':($p['stock']<10?'stock-low':'stock-ok') ?>"><?= $p['stock'] ?></span></td>
                    <td><?= $p['rating'] ?> ★</td>
                    <td><?= $p['is_featured'] ? '<span class="badge badge-success">Yes</span>' : '<span class="badge" style="background:#F1F5F9;color:#94A3B8">No</span>' ?></td>
                    <td style="display:flex;gap:6px">
                        <a href="admin.php?tab=products&edit_id=<?= $p['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this product?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <button type="submit" name="delete_product" class="btn btn-sm btn-danger">Del</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal-overlay" id="addProductModal">
    <div class="modal">
        <h3>Add New Product</h3>
        <form method="POST">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
                <div class="form-group"><label>Category</label>
                    <select name="cat_id" required>
                        <?php foreach($all_categories as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Price (₹)</label><input type="number" name="price" step="0.01" required></div>
                <div class="form-group"><label>Stock</label><input type="number" name="stock" value="50"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Rating</label><input type="number" name="rating" step="0.1" min="0" max="5" value="4.5"></div>
                <div class="form-group"><label>Image Filename</label><input type="text" name="image" placeholder="prod_default.jpg"></div>
            </div>
            <div class="form-group"><label>Description</label><textarea name="description" rows="3"></textarea></div>
            <div class="form-group"><label><input type="checkbox" name="is_featured" value="1"> Mark as Featured</label></div>
            <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px">
                <button type="button" class="btn btn-outline" onclick="closeModal('addProductModal')">Cancel</button>
                <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<?php if($edit_product): ?>
<div class="modal-overlay active" id="editProductModal">
    <div class="modal">
        <h3>Edit Product: <?= e($edit_product['name']) ?></h3>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= $edit_product['id'] ?>">
            <div class="form-row">
                <div class="form-group"><label>Name</label><input type="text" name="name" value="<?= e($edit_product['name']) ?>" required></div>
                <div class="form-group"><label>Category</label>
                    <select name="cat_id" required>
                        <?php foreach($all_categories as $c): ?><option value="<?= $c['id'] ?>" <?= $c['id']==$edit_product['cat_id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Price (₹)</label><input type="number" name="price" step="0.01" value="<?= $edit_product['price'] ?>" required></div>
                <div class="form-group"><label>Stock</label><input type="number" name="stock" value="<?= $edit_product['stock'] ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Rating</label><input type="number" name="rating" step="0.1" min="0" max="5" value="<?= $edit_product['rating'] ?>"></div>
                <div class="form-group"><label>Image Filename</label><input type="text" name="image" value="<?= e($edit_product['image']) ?>"></div>
            </div>
            <div class="form-group"><label>Description</label><textarea name="description" rows="3"><?= e($edit_product['description']) ?></textarea></div>
            <div class="form-group"><label><input type="checkbox" name="is_featured" value="1" <?= $edit_product['is_featured']?'checked':'' ?>> Mark as Featured</label></div>
            <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px">
                <a href="admin.php?tab=products" class="btn btn-outline">Cancel</a>
                <button type="submit" name="edit_product" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
