<?php
$cats = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE cat_id = c.id) as product_count FROM categories c ORDER BY c.name ASC")->fetchAll();
$edit_cat = null;
if (isset($_GET['edit_cat_id'])) {
    $ec = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $ec->execute([(int)$_GET['edit_cat_id']]);
    $edit_cat = $ec->fetch();
}
?>
<div class="page-header" style="display:flex;justify-content:space-between;align-items:center">
    <div><h1>Categories</h1><p>Organize your product catalog.</p></div>
    <button class="btn btn-primary" onclick="openModal('addCatModal')">+ Add Category</button>
</div>
<div class="card"><div class="card-body" style="padding:0">
<table><thead><tr><th>Name</th><th>Image</th><th>Products</th><th>Actions</th></tr></thead><tbody>
<?php foreach($cats as $c): ?>
<tr>
    <td><strong><?= e($c['name']) ?></strong></td>
    <td><code style="font-size:.8rem"><?= e($c['image']) ?></code></td>
    <td><span class="badge badge-primary"><?= $c['product_count'] ?> products</span></td>
    <td style="display:flex;gap:6px">
        <a href="admin.php?tab=categories&edit_cat_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
        <form method="POST" style="display:inline" onsubmit="return confirm('<?= $c['product_count'] > 0 ? 'This category has '.$c['product_count'].' products! They must be moved first.' : 'Delete this category?' ?>')">
            <?= csrf_field() ?><input type="hidden" name="cat_id" value="<?= $c['id'] ?>">
            <button type="submit" name="delete_category" class="btn btn-sm btn-danger" <?= $c['product_count'] > 0 ? 'title="Has products — will be blocked server-side"' : '' ?>>Del</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div></div>

<div class="modal-overlay" id="addCatModal"><div class="modal"><h3>Add Category</h3>
<form method="POST"><?= csrf_field() ?>
    <div class="form-group"><label>Category Name</label><input type="text" name="cat_name" required></div>
    <div class="form-group"><label>Image Filename</label><input type="text" name="cat_image" placeholder="cat_default.jpg"></div>
    <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px">
        <button type="button" class="btn btn-outline" onclick="closeModal('addCatModal')">Cancel</button>
        <button type="submit" name="add_category" class="btn btn-primary">Create</button>
    </div>
</form></div></div>

<?php if($edit_cat): ?>
<div class="modal-overlay active" id="editCatModal"><div class="modal"><h3>Edit Category</h3>
<form method="POST"><?= csrf_field() ?><input type="hidden" name="cat_id" value="<?= $edit_cat['id'] ?>">
    <div class="form-group"><label>Name</label><input type="text" name="cat_name" value="<?= e($edit_cat['name']) ?>" required></div>
    <div class="form-group"><label>Image</label><input type="text" name="cat_image" value="<?= e($edit_cat['image']) ?>"></div>
    <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px">
        <a href="admin.php?tab=categories" class="btn btn-outline">Cancel</a>
        <button type="submit" name="edit_category" class="btn btn-primary">Save</button>
    </div>
</form></div></div>
<?php endif; ?>
