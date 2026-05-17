<?php
$users = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count FROM users ORDER BY u.id ASC")->fetchAll();
?>
<div class="page-header" style="display:flex;justify-content:space-between;align-items:center">
    <div><h1>Customers</h1><p><?= count($users) ?> registered users.</p></div>
    <div class="search-box"><input type="text" id="searchUsers" placeholder="Search users..."></div>
</div>

<div class="card">
    <div class="card-body" style="padding:0">
        <table id="usersTable">
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th><th>Role</th><th>Joined</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach($users as $u): ?>
                <tr>
                    <td>#<?= $u['id'] ?></td>
                    <td><strong><?= e($u['username']) ?></strong></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e($u['phone']) ?></td>
                    <td><?= $u['order_count'] ?></td>
                    <td>
                        <?php if($u['is_admin']): ?>
                            <span class="badge badge-primary">Admin</span>
                        <?php else: ?>
                            <span class="badge" style="background:#F1F5F9;color:#94A3B8">Customer</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <?php if(!$u['is_admin']): ?>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Remove user <?= e($u['username']) ?>? This will also delete their orders.')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="delete_user_id" value="<?= $u['id'] ?>">
                                <button type="submit" name="delete_user" class="btn btn-sm btn-danger">Remove</button>
                            </form>
                        <?php else: ?>
                            <span style="color:var(--text-light);font-size:.8rem">Protected</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
