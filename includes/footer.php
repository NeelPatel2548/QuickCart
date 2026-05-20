<footer>
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="index.php" class="logo" style="margin-bottom: 1.5rem; display: block;"><?= SITE_NAME ?></a>
                <p style="color: var(--text-secondary); max-width: 300px;">
                    Crafting the next generation of electronics with a focus on performance, design, and user experience.
                </p>
            </div>
            <div>
                <h4 style="margin-bottom: 1.5rem;">Shop</h4>
                <ul style="list-style: none; display: grid; gap: 0.8rem; color: var(--text-secondary); font-size: 0.9rem;">
                    <li><a href="products.php">All Products</a></li>
                    <li><a href="products.php?cat=Laptops">Laptops</a></li>
                    <li><a href="products.php?cat=Watches">Watches</a></li>
                    <li><a href="deals.php">Special Deals</a></li>
                </ul>
            </div>
            <div>
                <h4 style="margin-bottom: 1.5rem;">Support</h4>
                <ul style="list-style: none; display: grid; gap: 0.8rem; color: var(--text-secondary); font-size: 0.9rem;">
                    <li><a href="profile.php">Order Status</a></li>
                    <li><a href="products.php">New Collections</a></li>
                    <li><a href="profile.php">My Account</a></li>
                    <li><a href="cart.php">Your Bag</a></li>
                </ul>
            </div>
            <div>
                <h4 style="margin-bottom: 1.5rem;">Company</h4>
                <ul style="list-style: none; display: grid; gap: 0.8rem; color: var(--text-secondary); font-size: 0.9rem;">
                    <li><a href="index.php">About Us</a></li>
                    <li><a href="products.php">Explore Tech</a></li>
                    <li><a href="signup.php">Join Community</a></li>
                </ul>
            </div>
        </div>
        
        <div style="margin-top: 5rem; padding-top: 2rem; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; color: var(--text-secondary);">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
            <div style="display: flex; gap: 2rem;">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

<script src="<?= BASE_URL ?>js/script.js"></script>
</body>
</html>
