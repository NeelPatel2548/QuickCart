/**
 * QuickCart - Premium E-Commerce UI Engine
 * Centralized logic for Cart, Navigation, and Animations
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Mobile Navigation Toggle
    window.toggleMobileMenu = function() {
        const header = document.querySelector('header');
        header.classList.toggle('mobile-open'); // Assuming CSS handles this transition
    };

    // 2. Global AJAX Add To Cart
    window.addToCart = function(productId, event) {
        let btn = null;
        if (event && event.target) {
            btn = event.target;
        }

        const originalText = btn ? btn.innerHTML : "Add to Bag";
        if (btn) {
            btn.innerHTML = "Processing...";
            btn.disabled = true;
        }

        const params = new URLSearchParams();
        params.append('product_id', productId);
        params.append('quantity', 1);

        fetch('add_to_cart.php', {
            method: 'POST',
            body: params
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                if (btn) {
                    btn.innerHTML = "✓ Added";
                    btn.style.background = "#34C759"; // Success Green
                    btn.style.borderColor = "#34C759";
                }
                // Refresh to update header cart count
                setTimeout(() => {
                    location.reload();
                }, 800);
            } else {
                alert('Could not add item: ' + (data.message || 'Unknown error'));
                if (btn) {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            }
        })
        .catch(err => {
            console.error('Cart Error:', err);
            if (btn) {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    };

    // 3. Smooth Fade-In on Scroll
    const faders = document.querySelectorAll('.fade-in');
    const appearOptions = {
        threshold: 0.1,
        rootMargin: "0px 0px -50px 0px"
    };

    const appearOnScroll = new IntersectionObserver(function(entries, appearOnScroll) {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('appear');
            appearOnScroll.unobserve(entry.target);
        });
    }, appearOptions);

    faders.forEach(fader => {
        appearOnScroll.observe(fader);
    });

    // 4. Quantity Input Auto-Update (Cart)
    const qtyInputs = document.querySelectorAll('input[name^="qty"]');
    qtyInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Find the closest update button or form
            const form = this.closest('form');
            if (form) {
                // We could AJAX this, but for now, the UI expects a reload
                // Let's just submit the specific update form if it exists
                const updateBtn = form.querySelector('button[name="update_cart"]');
                if (updateBtn) updateBtn.click();
            }
        });
    });
});
