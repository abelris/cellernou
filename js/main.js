/**
 * El Celler Nou - Main JavaScript
 */

// Cuenta los clics en WhatsApp y Llamar (sin cookies; ver controllers/ClicController.php).
(function() {
    var script = document.currentScript;
    var url = script && script.getAttribute('data-clic-url');
    if (!url || !navigator.sendBeacon) return;

    document.addEventListener('click', function(e) {
        var enlace = e.target.closest ? e.target.closest('a[href]') : null;
        if (!enlace) return;
        var href = enlace.getAttribute('href');
        var tipo = href.indexOf('tel:') === 0 ? 'telefono'
            : href.indexOf('https://wa.me/') === 0 ? 'whatsapp' : null;
        if (!tipo) return;
        navigator.sendBeacon(url, new URLSearchParams({ tipo: tipo, pagina: location.pathname }));
    }, true);
})();

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

// ============================================
// Hero de la portada: carrusel (includes/hero.php)
// Empieza por la diapositiva que eligió el servidor y pasa cada 7 s. Se para
// con el ratón encima o el foco dentro, con la pestaña oculta y con el botón de
// pausa; no avanza solo si el sistema pide reducir el movimiento.
// ============================================
(function() {
    var hero = document.querySelector('.hero-carrusel');
    if (!hero) return;
    var slides = hero.querySelectorAll('.hero-slide');
    var puntos = hero.querySelectorAll('.hero-punto');
    var total = slides.length;
    if (total < 2) return;

    var INTERVALO = 7000;
    var actual = parseInt(hero.getAttribute('data-inicio'), 10) || 0;
    var temporizador = null;
    var pausadoUsuario = false;
    var pausadoHover = false;
    var reducir = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var btnPausa = hero.querySelector('.hero-pausa');

    function mostrar(i) {
        i = (i + total) % total;
        slides.forEach(function(s, n) {
            var activa = n === i;
            s.classList.toggle('is-active', activa);
            if (activa) {
                s.removeAttribute('aria-hidden');
                s.removeAttribute('inert');
                var img = s.querySelector('img[loading="lazy"]');
                if (img) img.loading = 'eager';
            } else {
                s.setAttribute('aria-hidden', 'true');
                s.setAttribute('inert', '');
            }
        });
        puntos.forEach(function(p, n) {
            p.classList.toggle('is-active', n === i);
            if (n === i) p.setAttribute('aria-current', 'true'); else p.removeAttribute('aria-current');
        });
        actual = i;
        // Precarga la siguiente para que el cambio no muestre la foto a medias.
        var sig = slides[(i + 1) % total].querySelector('img[loading="lazy"]');
        if (sig) sig.loading = 'eager';
    }

    function parar() {
        if (temporizador) clearInterval(temporizador);
        temporizador = null;
    }

    function arrancar() {
        parar();
        if (reducir || pausadoUsuario || pausadoHover || document.hidden) return;
        temporizador = setInterval(function() { mostrar(actual + 1); }, INTERVALO);
    }

    function irA(i) {
        mostrar(i);
        arrancar();
    }

    hero.querySelector('.hero-prev').addEventListener('click', function() { irA(actual - 1); });
    hero.querySelector('.hero-next').addEventListener('click', function() { irA(actual + 1); });
    puntos.forEach(function(p) {
        p.addEventListener('click', function() { irA(parseInt(p.getAttribute('data-ir'), 10)); });
    });

    function pintarPausa() {
        var parado = pausadoUsuario || reducir;
        btnPausa.innerHTML = parado
            ? '<svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path fill="currentColor" d="M7 5l12 7-12 7z"/></svg>'
            : '<svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path fill="currentColor" d="M6 5h4v14H6zM14 5h4v14h-4z"/></svg>';
        btnPausa.setAttribute('aria-label', parado ? 'Reproducir' : 'Pausar');
    }
    btnPausa.addEventListener('click', function() {
        if (reducir) { reducir = false; pausadoUsuario = false; }
        else pausadoUsuario = !pausadoUsuario;
        pintarPausa();
        arrancar();
    });
    pintarPausa();

    var escena = hero.querySelector('.hero-escena');
    escena.addEventListener('mouseenter', function() { pausadoHover = true; parar(); });
    escena.addEventListener('mouseleave', function() { pausadoHover = false; arrancar(); });
    escena.addEventListener('focusin', function() { pausadoHover = true; parar(); });
    escena.addEventListener('focusout', function(e) {
        if (!escena.contains(e.relatedTarget)) { pausadoHover = false; arrancar(); }
    });
    document.addEventListener('visibilitychange', arrancar);

    // Deslizar con el dedo en el móvil.
    var x0 = null;
    escena.addEventListener('touchstart', function(e) { x0 = e.touches[0].clientX; }, { passive: true });
    escena.addEventListener('touchend', function(e) {
        if (x0 === null) return;
        var dx = e.changedTouches[0].clientX - x0;
        x0 = null;
        if (Math.abs(dx) > 40) irA(actual + (dx < 0 ? 1 : -1));
    });

    // Flechas del teclado con el foco en los controles.
    hero.querySelector('.hero-controles').addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') { irA(actual - 1); e.preventDefault(); }
        if (e.key === 'ArrowRight') { irA(actual + 1); e.preventDefault(); }
    });

    mostrar(actual);
    arrancar();
})();


// ============================================
// Barra de avisos de arriba (includes/hero.php, renderAvisosBarra)
// Cinta continua en CSS; aquí solo se ajusta la duración al ancho real para
// que vaya siempre a la misma velocidad (unos 50 px/s), haya más o menos avisos
// y en cualquier pantalla.
// ============================================
(function() {
    var pista = document.querySelector('.avisos-pista');
    if (!pista) return;
    var lista = pista.querySelector('.avisos-lista');
    var VELOCIDAD = 50;

    function ajustar() {
        var ancho = lista.getBoundingClientRect().width;
        if (ancho > 0) pista.style.animationDuration = Math.round(ancho / VELOCIDAD) + 's';
    }

    ajustar();
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(ajustar);
    var t;
    window.addEventListener('resize', function() { clearTimeout(t); t = setTimeout(ajustar, 200); });
})();
