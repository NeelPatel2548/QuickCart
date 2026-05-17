<?php
require_once 'config.php';
require_once 'includes/admin_handlers.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QuickCart | Command Center</title>
    <link rel="icon" type="image/png" href="favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#F8FAFC;--sidebar:#0F172A;--sidebar-hover:#1E293B;--primary:#6366F1;--primary-soft:#EEF2FF;--success:#10B981;--success-soft:#D1FAE5;--warning:#F59E0B;--warning-soft:#FEF3C7;--danger:#EF4444;--danger-soft:#FEE2E2;--text:#1E293B;--text-light:#64748B;--white:#FFF;--border:#E2E8F0;--shadow:0 1px 3px rgba(0,0,0,.06);--radius:12px}
        *{margin:0;padding:0;box-sizing:border-box}
        body{background:var(--bg);font-family:'Inter',sans-serif;display:flex;color:var(--text)}
        .sidebar{width:260px;height:100vh;background:var(--sidebar);padding:32px 16px;position:fixed;overflow-y:auto}
        .sidebar h2{color:var(--primary);font-size:1.5rem;padding:0 12px;margin-bottom:40px}
        .nav-link{display:flex;align-items:center;gap:10px;padding:12px 16px;color:#94A3B8;text-decoration:none;font-weight:500;border-radius:10px;margin-bottom:4px;font-size:.9rem;transition:.2s}
        .nav-link:hover{background:var(--sidebar-hover);color:#CBD5E1}
        .nav-link.active{background:var(--primary);color:var(--white)}
        .nav-link .icon{width:20px;text-align:center}
        .main{margin-left:260px;width:calc(100% - 260px);padding:32px 40px;min-height:100vh}
        .page-header{margin-bottom:32px}
        .page-header h1{font-size:1.75rem;font-weight:800}
        .page-header p{color:var(--text-light);margin-top:4px;font-size:.95rem}
        .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:32px}
        .stat-card{background:var(--white);padding:24px;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border)}
        .stat-card small{color:var(--text-light);font-weight:600;font-size:.75rem;text-transform:uppercase;letter-spacing:.05em}
        .stat-card h2{font-size:1.75rem;margin-top:8px}
        .card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);overflow:hidden;margin-bottom:24px}
        .card-header{padding:20px 24px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center}
        .card-header h3{font-size:1.1rem;font-weight:700}
        .card-body{padding:24px}
        table{width:100%;border-collapse:collapse}
        th{text-align:left;padding:12px 16px;font-size:.75rem;color:var(--text-light);font-weight:700;text-transform:uppercase;letter-spacing:.04em;border-bottom:2px solid var(--border);background:var(--bg)}
        td{padding:14px 16px;border-bottom:1px solid var(--border);font-size:.9rem;vertical-align:middle}
        tr:last-child td{border-bottom:none}
        .badge{padding:4px 12px;border-radius:20px;font-size:.75rem;font-weight:700;display:inline-block}
        .badge-success{background:var(--success-soft);color:var(--success)}
        .badge-warning{background:var(--warning-soft);color:var(--warning)}
        .badge-danger{background:var(--danger-soft);color:var(--danger)}
        .badge-primary{background:var(--primary-soft);color:var(--primary)}
        .btn{padding:8px 16px;border-radius:8px;font-weight:600;font-size:.85rem;cursor:pointer;border:none;transition:.2s;text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-family:inherit}
        .btn-sm{padding:6px 12px;font-size:.8rem}
        .btn-primary{background:var(--primary);color:var(--white)}
        .btn-primary:hover{background:#4F46E5}
        .btn-success{background:var(--success);color:var(--white)}
        .btn-danger{background:var(--danger);color:var(--white)}
        .btn-danger:hover{background:#DC2626}
        .btn-outline{background:transparent;border:1.5px solid var(--border);color:var(--text)}
        .btn-outline:hover{border-color:var(--primary);color:var(--primary)}
        select,input[type=text],input[type=number],input[type=email],textarea{padding:10px 14px;border-radius:8px;border:1.5px solid var(--border);font-family:inherit;font-size:.9rem;outline:none;transition:.2s;width:100%}
        select:focus,input:focus,textarea:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(99,102,241,.1)}
        .form-group{margin-bottom:16px}
        .form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text-light);margin-bottom:6px;text-transform:uppercase;letter-spacing:.03em}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .flash{padding:14px 20px;border-radius:var(--radius);margin-bottom:24px;font-weight:600;font-size:.9rem}
        .flash-success{background:var(--success-soft);color:#065F46}
        .flash-error{background:var(--danger-soft);color:#991B1B}
        .modal-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.4);backdrop-filter:blur(4px);z-index:100;align-items:center;justify-content:center}
        .modal-overlay.active{display:flex}
        .modal{background:var(--white);border-radius:16px;width:90%;max-width:560px;max-height:85vh;overflow-y:auto;padding:32px}
        .modal h3{font-size:1.3rem;margin-bottom:24px}
        .search-box{position:relative}
        .search-box input{padding-left:36px}
        .search-box::before{content:'🔍';position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:.8rem}
        .thumb{width:40px;height:40px;border-radius:8px;object-fit:cover;background:var(--bg)}
        .stock-ok{color:var(--success);font-weight:700}
        .stock-low{color:var(--warning);font-weight:700}
        .stock-out{color:var(--danger);font-weight:700}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:24px}
        .empty-state{text-align:center;padding:60px 20px;color:var(--text-light)}
        .empty-state h3{color:var(--text);margin-bottom:8px}
        .copy-btn{cursor:pointer;background:var(--primary-soft);color:var(--primary);border:none;padding:4px 10px;border-radius:6px;font-weight:700;font-size:.8rem}
        @media(max-width:1024px){.sidebar{width:200px}.main{margin-left:200px;width:calc(100% - 200px);padding:24px}}
    </style>
</head>
<body>

<div class="sidebar">
    <h2>QuickCart.</h2>
    <nav>
        <a class="nav-link <?= $tab==='dashboard'?'active':'' ?>" href="admin.php?tab=dashboard"><span class="icon">📊</span> Dashboard</a>
        <a class="nav-link <?= $tab==='products'?'active':'' ?>" href="admin.php?tab=products"><span class="icon">📦</span> Products</a>
        <a class="nav-link <?= $tab==='orders'?'active':'' ?>" href="admin.php?tab=orders"><span class="icon">🛒</span> Orders</a>
        <a class="nav-link <?= $tab==='users'?'active':'' ?>" href="admin.php?tab=users"><span class="icon">👥</span> Customers</a>
        <a class="nav-link <?= $tab==='offers'?'active':'' ?>" href="admin.php?tab=offers"><span class="icon">🏷️</span> Offers</a>
        <a class="nav-link <?= $tab==='categories'?'active':'' ?>" href="admin.php?tab=categories"><span class="icon">📁</span> Categories</a>
        <a class="nav-link" href="index.php" style="margin-top:24px;color:#94A3B8"><span class="icon">🏠</span> View Store</a>
        <a class="nav-link" href="logout.php" style="color:#F87171"><span class="icon">🚪</span> Logout</a>
    </nav>
</div>

<div class="main">
    <?php if($flash): ?>
        <div class="flash flash-<?= $flash['type'] ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <?php
    $tab_file = __DIR__ . '/includes/admin_tabs/' . basename($tab) . '.php';
    if (file_exists($tab_file)) {
        include $tab_file;
    } else {
        include __DIR__ . '/includes/admin_tabs/dashboard.php';
    }
    ?>
</div>

<script>
// Modal system
function openModal(id){document.getElementById(id).classList.add('active')}
function closeModal(id){document.getElementById(id).classList.remove('active')}
document.querySelectorAll('.modal-overlay').forEach(m=>{m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('active')})});

// Live table search
function tableSearch(inputId,tableId){
    const input=document.getElementById(inputId);
    if(!input)return;
    input.addEventListener('input',function(){
        const val=this.value.toLowerCase();
        document.querySelectorAll('#'+tableId+' tbody tr').forEach(row=>{
            row.style.display=row.textContent.toLowerCase().includes(val)?'':'none';
        });
    });
}

// Copy to clipboard
function copyCode(text,btn){
    navigator.clipboard.writeText(text).then(()=>{btn.textContent='Copied!';setTimeout(()=>btn.textContent='Copy',1500)});
}

// Init search on all tables
document.addEventListener('DOMContentLoaded',()=>{
    tableSearch('searchProducts','productsTable');
    tableSearch('searchOrders','ordersTable');
    tableSearch('searchUsers','usersTable');
});
</script>
</body>
</html>
