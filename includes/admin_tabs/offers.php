<?php
$offers = $pdo->query("SELECT * FROM offers ORDER BY id DESC")->fetchAll();
$edit_offer = null;
if (isset($_GET['edit_offer_id'])) {
    $eo = $pdo->prepare("SELECT * FROM offers WHERE id = ?");
    $eo->execute([(int)$_GET['edit_offer_id']]);
    $edit_offer = $eo->fetch();
}
?>
<div class="page-header" style="display:flex;justify-content:space-between;align-items:center">
    <div><h1>Offers</h1><p>Manage promo codes and discounts.</p></div>
    <button class="btn btn-primary" onclick="openModal('addOfferModal')">+ Add Offer</button>
</div>
<div class="card"><div class="card-body" style="padding:0">
<?php if(empty($offers)): ?>
    <div class="empty-state"><h3>No offers</h3></div>
<?php else: ?>
<table><thead><tr><th>Title</th><th>Code</th><th>Discount</th><th>Actions</th></tr></thead><tbody>
<?php foreach($offers as $of): ?>
<tr>
    <td><strong><?= e($of['title']) ?></strong></td>
    <td><code style="background:var(--primary-soft);color:var(--primary);padding:4px 10px;border-radius:6px;font-weight:700"><?= e($of['promo_code']) ?></code>
        <button class="copy-btn" onclick="copyCode('<?= e($of['promo_code']) ?>',this)">Copy</button></td>
    <td><span class="badge badge-success"><?= $of['discount_percent'] ?>% OFF</span></td>
    <td style="display:flex;gap:6px">
        <a href="admin.php?tab=offers&edit_offer_id=<?= $of['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
        <form method="POST" style="display:inline" onsubmit="return confirm('Delete?')">
            <?= csrf_field() ?><input type="hidden" name="offer_id" value="<?= $of['id'] ?>">
            <button type="submit" name="delete_offer" class="btn btn-sm btn-danger">Del</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>
</div></div>

<div class="modal-overlay" id="addOfferModal"><div class="modal"><h3>Create Offer</h3>
<form method="POST"><?= csrf_field() ?>
    <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
    <div class="form-row">
        <div class="form-group"><label>Promo Code</label><input type="text" name="promo_code" required style="text-transform:uppercase"></div>
        <div class="form-group"><label>Discount %</label><input type="number" name="discount_percent" min="1" max="100" required></div>
    </div>
    <div class="form-group"><label>Banner Image</label><input type="text" name="banner_image" placeholder="banner.jpg"></div>
    <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px">
        <button type="button" class="btn btn-outline" onclick="closeModal('addOfferModal')">Cancel</button>
        <button type="submit" name="add_offer" class="btn btn-primary">Create</button>
    </div>
</form></div></div>

<?php if($edit_offer): ?>
<div class="modal-overlay active" id="editOfferModal"><div class="modal"><h3>Edit Offer</h3>
<form method="POST"><?= csrf_field() ?><input type="hidden" name="offer_id" value="<?= $edit_offer['id'] ?>">
    <div class="form-group"><label>Title</label><input type="text" name="title" value="<?= e($edit_offer['title']) ?>" required></div>
    <div class="form-row">
        <div class="form-group"><label>Code</label><input type="text" name="promo_code" value="<?= e($edit_offer['promo_code']) ?>" required></div>
        <div class="form-group"><label>Discount %</label><input type="number" name="discount_percent" value="<?= $edit_offer['discount_percent'] ?>" required></div>
    </div>
    <div class="form-group"><label>Banner</label><input type="text" name="banner_image" value="<?= e($edit_offer['banner_image'] ?? '') ?>"></div>
    <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px">
        <a href="admin.php?tab=offers" class="btn btn-outline">Cancel</a>
        <button type="submit" name="edit_offer" class="btn btn-primary">Save</button>
    </div>
</form></div></div>
<?php endif; ?>
