    const heroSection = document.querySelector('.sg-hero-section');
    if (heroSection) {
        const petalIcons = ['🌸', '🌺', '💮', '🍃', '✨'];
        const petalCount = 10;

        for (let i = 0; i < petalCount; i++) {
            const petal = document.createElement('span');
            petal.className = 'sg-petal-particle';
            petal.textContent = petalIcons[Math.floor(Math.random() * petalIcons.length)];
            
            const startLeft = Math.random() * 92;
            const animDuration = 6 + Math.random() * 7;
            const animDelay = Math.random() * 5;
            const size = 14 + Math.random() * 12;
            const opacity = 0.35 + Math.random() * 0.45;

            petal.style.left = startLeft + '%';
            petal.style.top = '-20px';
            petal.style.fontSize = size + 'px';
            petal.style.animationDuration = animDuration + 's';
            petal.style.animationDelay = animDelay + 's';
            petal.style.opacity = opacity;

            heroSection.appendChild(petal);
        }
    }

document.addEventListener('DOMContentLoaded', function () {
    const siteHeader = document.querySelector('.site-header');
    if (siteHeader) {
        const handleHeaderScroll = () => {
            if (window.scrollY > 20) {
                siteHeader.classList.add('scrolled');
            } else {
                siteHeader.classList.remove('scrolled');
            }
        };
        window.addEventListener('scroll', handleHeaderScroll, { passive: true });
        handleHeaderScroll();
    }

    const revealElements = document.querySelectorAll('[data-sg-reveal]');
    if (revealElements.length > 0 && 'IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('sg-revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.12,
            rootMargin: '0px 0px -40px 0px'
        });

        revealElements.forEach(el => revealObserver.observe(el));
    } else {
        revealElements.forEach(el => el.classList.add('sg-revealed'));
    }

    window.triggerCartJiggle = function () {
        const cartBtn = document.querySelector('.nav-cart-btn') || document.querySelector('.cart-icon-wrap');
        const cartBadge = document.getElementById('cart-count');

        if (cartBtn) {
            cartBtn.classList.remove('cart-jiggle-anim');
            void cartBtn.offsetWidth;
            cartBtn.classList.add('cart-jiggle-anim');
            setTimeout(() => cartBtn.classList.remove('cart-jiggle-anim'), 700);
        }

        if (cartBadge) {
            cartBadge.style.transform = 'scale(1.4)';
            cartBadge.style.transition = 'transform 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
            setTimeout(() => {
                cartBadge.style.transform = 'scale(1)';
            }, 300);
        }
    };

    if (!window.__sgRippleInitialized) {
        window.__sgRippleInitialized = true;
        document.addEventListener('pointerdown', function (e) {
            const target = e.target.closest(
                '.main-nav a, .nav-cart-btn, .user-dropdown-toggle, .dropdown-menu a, .logo-link, ' +
                '.btn-search, .btn-reset, .custom-select-trigger, .custom-option, .product-card, .btn-cart, .btn-card-wishlist, ' +
                '.pagination a, #btnBackToTop, .chat-widget-btn, button, .btn, a.btn-detail, ' +
                '.admin-btn, .action-btn, .admin-tab-btn, .admin-nav-item, .admin-shop-link, .kpi-card'
            );
            if (!target) return;

            const rect = target.getBoundingClientRect();
            const ripple = document.createElement('span');
            ripple.className = 'sg-click-ripple';
            
            const size = Math.max(rect.width, rect.height) * 2;
            ripple.style.width = ripple.style.height = `${size}px`;
            ripple.style.left = `${e.clientX - rect.left - size / 2}px`;
            ripple.style.top = `${e.clientY - rect.top - size / 2}px`;

            const computed = window.getComputedStyle(target);
            if (computed.position === 'static') {
                target.style.position = 'relative';
            }
            target.style.overflow = 'hidden';

            target.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
    }
});
