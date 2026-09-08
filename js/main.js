/**
 * El Celler Nou - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {

    // ============================================
    // Mobile Navigation Toggle
    // ============================================
    const navToggle = document.querySelector('.nav-toggle');
    const mainNav = document.querySelector('.main-nav');
    const body = document.body;

    if (navToggle && mainNav) {
        navToggle.addEventListener('click', function() {
            const isOpen = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isOpen);
            mainNav.classList.toggle('active');
            body.style.overflow = isOpen ? '' : 'hidden';
        });
    }

    // ============================================
    // Mobile Submenu Toggle
    // ============================================
    const hasSubmenu = document.querySelectorAll('.has-submenu');

    hasSubmenu.forEach(function(item) {
        const link = item.querySelector('.nav-link');

        link.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                item.classList.toggle('submenu-open');

                // Close other submenus
                hasSubmenu.forEach(function(other) {
                    if (other !== item) {
                        other.classList.remove('submenu-open');
                    }
                });
            }
        });
    });

    // ============================================
    // Search Overlay
    // ============================================
    const searchToggleMobile = document.querySelector('.search-toggle-mobile');
    const searchOverlay = document.querySelector('.search-overlay');
    const searchClose = document.querySelector('.search-close');

    if (searchToggleMobile && searchOverlay) {
        searchToggleMobile.addEventListener('click', function() {
            searchOverlay.classList.add('active');
            searchOverlay.setAttribute('aria-hidden', 'false');
            const input = searchOverlay.querySelector('input');
            if (input) input.focus();
            body.style.overflow = 'hidden';
        });

        if (searchClose) {
            searchClose.addEventListener('click', function() {
                searchOverlay.classList.remove('active');
                searchOverlay.setAttribute('aria-hidden', 'true');
                body.style.overflow = '';
            });
        }

        // Close on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
                searchOverlay.classList.remove('active');
                searchOverlay.setAttribute('aria-hidden', 'true');
                body.style.overflow = '';
            }
        });

        // Close on overlay click
        searchOverlay.addEventListener('click', function(e) {
            if (e.target === searchOverlay) {
                searchOverlay.classList.remove('active');
                searchOverlay.setAttribute('aria-hidden', 'true');
                body.style.overflow = '';
            }
        });
    }

    // ============================================
    // Smooth Scroll for Anchor Links
    // ============================================
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // ============================================
    // Header Scroll Effect
    // ============================================
    // Throttled via requestAnimationFrame para evitar forced reflow:
    // la lectura de pageYOffset y las mutaciones de clase se agrupan
    // una vez por frame, cuando el navegador ya ha terminado de pintar
    // el frame anterior. Passive listener para no bloquear el scroll nativo.
    const header = document.querySelector('.site-header');
    let lastScroll = 0;
    let scrollTicking = false;

    if (header) {
        window.addEventListener('scroll', function() {
            if (scrollTicking) return;
            scrollTicking = true;

            requestAnimationFrame(function() {
                const currentScroll = window.pageYOffset;

                if (currentScroll <= 0) {
                    header.classList.remove('scroll-up');
                } else if (currentScroll > lastScroll && !header.classList.contains('scroll-down')) {
                    // Scrolling down
                    header.classList.remove('scroll-up');
                    header.classList.add('scroll-down');
                } else if (currentScroll < lastScroll && header.classList.contains('scroll-down')) {
                    // Scrolling up
                    header.classList.remove('scroll-down');
                    header.classList.add('scroll-up');
                }

                lastScroll = currentScroll;
                scrollTicking = false;
            });
        }, { passive: true });
    }

    // ============================================
    // Form Validation
    // ============================================
    const contactForm = document.querySelector('.contact-form');

    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            let valid = true;
            const requiredFields = this.querySelectorAll('[required]');

            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    valid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });

            // Email validation
            const emailField = this.querySelector('input[type="email"]');
            if (emailField && emailField.value) {
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(emailField.value)) {
                    valid = false;
                    emailField.classList.add('error');
                }
            }

            if (!valid) {
                e.preventDefault();
            }
        });
    }

    // ============================================
    // Lazy Loading Images
    // ============================================
    if ('IntersectionObserver' in window) {
        const lazyImages = document.querySelectorAll('img[data-src]');

        const imageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        });

        lazyImages.forEach(function(img) {
            imageObserver.observe(img);
        });
    }

    // ============================================
    // Filter Form Auto-submit
    // ============================================
    const filterSelects = document.querySelectorAll('.filter-select');

    filterSelects.forEach(function(select) {
        // Skip selects that already have inline onchange
        if (select.hasAttribute('onchange')) return;

        select.addEventListener('change', function() {
            const form = this.closest('form');
            if (form) {
                form.submit();
            } else if (this.value) {
                window.location.href = this.value;
            }
        });
    });

    // ============================================
    // Search Autocomplete (Basic)
    // ============================================
    const searchInputs = document.querySelectorAll('input[type="search"]');

    searchInputs.forEach(function(input) {
        let debounceTimer;

        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value.trim();

            if (query.length >= 3) {
                debounceTimer = setTimeout(function() {
                    // Here you could implement AJAX search
                    // For now, we just log
                    console.log('Searching for:', query);
                }, 300);
            }
        });
    });

    // ============================================
    // Accessibility: Focus Management
    // ============================================
    // Trap focus in modal/overlay
    function trapFocus(element) {
        const focusableElements = element.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        const firstFocusable = focusableElements[0];
        const lastFocusable = focusableElements[focusableElements.length - 1];

        element.addEventListener('keydown', function(e) {
            if (e.key !== 'Tab') return;

            if (e.shiftKey) {
                if (document.activeElement === firstFocusable) {
                    lastFocusable.focus();
                    e.preventDefault();
                }
            } else {
                if (document.activeElement === lastFocusable) {
                    firstFocusable.focus();
                    e.preventDefault();
                }
            }
        });
    }

    if (searchOverlay) {
        trapFocus(searchOverlay);
    }

    // ============================================
    // Popup Tienda Online
    // ============================================
    (function() {
        var popup = document.getElementById('popupTiendaOnline');
        var btnClose = document.getElementById('popupClose');
        var btnOk = document.getElementById('popupBtn');
        if (!popup) return;

        var STORAGE_KEY = 'cn_popup_dismissed';
        var SESSION_KEY = 'cn_popup_shown';
        var HOURS_24 = 24 * 60 * 60 * 1000;

        function shouldShow() {
            // Already shown this session
            if (sessionStorage.getItem(SESSION_KEY)) return false;
            // Check 24h cooldown
            var last = localStorage.getItem(STORAGE_KEY);
            if (last && (Date.now() - parseInt(last, 10)) < HOURS_24) return false;
            return true;
        }

        function showPopup() {
            popup.classList.add('active');
            popup.setAttribute('aria-hidden', 'false');
            sessionStorage.setItem(SESSION_KEY, '1');
        }

        function closePopup() {
            popup.classList.remove('active');
            popup.setAttribute('aria-hidden', 'true');
            localStorage.setItem(STORAGE_KEY, Date.now().toString());
        }

        if (shouldShow()) {
            setTimeout(showPopup, 1500);
        }

        btnClose.addEventListener('click', closePopup);
        btnOk.addEventListener('click', closePopup);

        popup.addEventListener('click', function(e) {
            if (e.target === popup) closePopup();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && popup.classList.contains('active')) {
                closePopup();
            }
        });

        trapFocus(popup);
    })();

});
