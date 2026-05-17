<?php 
require_once 'config.php'; 
include 'includes/header.php'; 
?>

<main class="section-padding" style="background: var(--bg-soft); min-height: calc(100vh - 80px); display: flex; align-items: center; justify-content: center;">
    <div style="width: 100%; max-width: 600px; background: var(--white); padding: 4rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-medium); text-align: center;" class="fade-in">
        <div style="width: 90px; height: 90px; background: var(--success); color: var(--white); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 2.5rem; box-shadow: 0 15px 35px rgba(52, 199, 89, 0.25);">
            ✓
        </div>
        <h1 style="font-size: 3rem; margin-bottom: 1.5rem;">Order <span style="color: var(--accent);">Confirmed.</span></h1>
        <p style="color: var(--text-secondary); font-size: 1.15rem; line-height: 1.6; margin-bottom: 3.5rem;">
            Thank you for choosing QuickCart. Your transaction was successful, and our team is now preparing your premium tech for delivery.
        </p>
        
        <div style="display: flex; gap: 1.5rem; justify-content: center;">
            <a href="profile.php" class="btn btn-primary" style="padding: 1.25rem 2.5rem; border-radius: var(--radius-md);">Track Your Order</a>
            <a href="products.php" class="btn btn-outline" style="padding: 1.25rem 2.5rem; border-radius: var(--radius-md);">Back to Store</a>
        </div>
        
        <p style="margin-top: 3rem; font-size: 0.85rem; color: var(--text-secondary);">
            A confirmation email has been sent to your registered address.
        </p>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
