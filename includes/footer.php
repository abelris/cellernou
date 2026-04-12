    </main>

    <footer class="site-footer" role="contentinfo">
        <div class="footer-main">
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-col footer-about">
                        <h2 class="footer-logo">
                            <svg class="footer-logo-icon" viewBox="0 0 24 32" aria-hidden="true">
                                <path d="M12 0C12 0 24 12 24 20C24 26.6 18.6 32 12 32C5.4 32 0 26.6 0 20C0 12 12 0 12 0Z"/>
                            </svg>
                            <span class="footer-logo-text">
                                <span class="footer-logo-line1">El Celler</span>
                                <span class="footer-logo-line2">Nou</span>
                            </span>
                        </h2>
                        <p>Tu tienda especializada en vinos de calidad, cavas, licores premium, cervezas artesanales y conservas gourmet. Más de 20 años seleccionando los mejores productos para ti.</p>
                        <div class="footer-social">
                            <a href="#" aria-label="Facebook" rel="noopener noreferrer"><svg class="icon"><use href="#icon-facebook"></use></svg></a>
                            <a href="#" aria-label="Instagram" rel="noopener noreferrer"><svg class="icon"><use href="#icon-instagram"></use></svg></a>
                        </div>
                    </div>

                    <div class="footer-col">
                        <h3>Categorías</h3>
                        <ul class="footer-links">
                            <li><a href="<?php echo Router::url('vinos'); ?>">Vinos</a></li>
                            <li><a href="<?php echo Router::url('vinos.tipo', ['id' => 1, 'slug' => 'vino-tinto']); ?>">Vinos Tintos</a></li>
                            <li><a href="<?php echo Router::url('vinos.tipo', ['id' => 2, 'slug' => 'vino-blanco']); ?>">Vinos Blancos</a></li>
                            <li><a href="<?php echo Router::url('vinos.tipo', ['id' => 3, 'slug' => 'vino-rosado']); ?>">Vinos Rosados</a></li>
                            <li><a href="<?php echo Router::url('cavas'); ?>">Cavas y Espumosos</a></li>
                            <li><a href="<?php echo Router::url('licores'); ?>">Licores</a></li>
                            <li><a href="<?php echo Router::url('cervezas'); ?>">Cervezas</a></li>
                            <li><a href="<?php echo Router::url('conservas'); ?>">Conservas</a></li>
                        </ul>
                    </div>

                    <div class="footer-col">
                        <h3>Denominaciones de Origen</h3>
                        <ul class="footer-links">
                            <li><a href="<?php echo Router::url('vinos.do', ['id' => 1, 'slug' => 'do-la-rioja']); ?>">D.O. La Rioja</a></li>
                            <li><a href="<?php echo Router::url('vinos.do', ['id' => 3, 'slug' => 'do-ribera-de-duero']); ?>">D.O. Ribera del Duero</a></li>
                            <li><a href="<?php echo Router::url('vinos.do', ['id' => 30, 'slug' => 'do-priorat']); ?>">D.O. Priorat</a></li>
                            <li><a href="<?php echo Router::url('vinos.do', ['id' => 15, 'slug' => 'do-penedes']); ?>">D.O. Penedès</a></li>
                            <li><a href="<?php echo Router::url('vinos.do', ['id' => 14, 'slug' => 'do-emporda']); ?>">D.O. Empordà</a></li>
                            <li><a href="<?php echo Router::url('vinos.do', ['id' => 32, 'slug' => 'do-montsant']); ?>">D.O. Montsant</a></li>
                            <li><a href="<?php echo Router::url('vinos.do', ['id' => 68, 'slug' => 'rias-baixas']); ?>">Rías Baixas</a></li>
                        </ul>
                    </div>

                    <div class="footer-col">
                        <h3>Licores</h3>
                        <ul class="footer-links">
                            <li><a href="<?php echo Router::url('licores.tipo', ['id' => 6, 'slug' => 'whisky']); ?>">Whisky</a></li>
                            <li><a href="<?php echo Router::url('licores.tipo', ['id' => 13, 'slug' => 'ron']); ?>">Ron</a></li>
                            <li><a href="<?php echo Router::url('licores.tipo', ['id' => 5, 'slug' => 'ginebra']); ?>">Ginebra</a></li>
                            <li><a href="<?php echo Router::url('licores.tipo', ['id' => 10, 'slug' => 'vodka']); ?>">Vodka</a></li>
                            <li><a href="<?php echo Router::url('licores.tipo', ['id' => 8, 'slug' => 'vermut']); ?>">Vermut</a></li>
                            <li><a href="<?php echo Router::url('licores.tipo', ['id' => 16, 'slug' => 'brandy']); ?>">Brandy</a></li>
                            <li><a href="<?php echo Router::url('licores.tipo', ['id' => 59, 'slug' => 'tequila']); ?>">Tequila</a></li>
                        </ul>
                    </div>

                    <div class="footer-col">
                        <h3>Contacto</h3>
                        <ul class="footer-contact">
                            <li>
                                <svg class="icon"><use href="#icon-phone"></use></svg>
                                <a href="tel:<?php echo preg_replace('/\s+/', '', SITE_PHONE); ?>"><?php echo SITE_PHONE; ?></a>
                            </li>
                            <li>
                                <svg class="icon"><use href="#icon-phone"></use></svg>
                                <a href="tel:<?php echo preg_replace('/\s+/', '', SITE_PHONE_MOBILE); ?>"><?php echo SITE_PHONE_MOBILE; ?></a>
                            </li>
                            <li>
                                <svg class="icon"><use href="#icon-email"></use></svg>
                                <a href="mailto:<?php echo SITE_EMAIL; ?>"><?php echo SITE_EMAIL; ?></a>
                            </li>
                            <li>
                                <svg class="icon"><use href="#icon-location"></use></svg>
                                <span><?php echo SITE_ADDRESS; ?></span>
                            </li>
                        </ul>
                        <a href="<?php echo Router::url('contacto'); ?>" class="btn btn-outline">Contactar</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container">
                <div class="footer-bottom-content">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Todos los derechos reservados.</p>
                    <nav class="footer-legal">
                        <a href="<?php echo Router::url('legal.aviso'); ?>">Aviso Legal</a>
                        <a href="<?php echo Router::url('legal.privacidad'); ?>">Política de Privacidad</a>
                        <a href="<?php echo Router::url('legal.cookies'); ?>">Cookies</a>
                        <a href="<?php echo Router::url('legal.venta'); ?>">Condiciones de Venta</a>
                        <a href="<?php echo Router::url('legal.envios'); ?>">Envíos</a>
                        <a href="<?php echo Router::url('legal.devoluciones'); ?>">Devoluciones</a>
                    </nav>
                </div>
            </div>
        </div>
    </footer>

    <!-- SVG Sprite -->
    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
        <symbol id="icon-search" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.35-4.35"></path>
        </symbol>
        <symbol id="icon-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m6 9 6 6 6-6"></path>
        </symbol>
        <symbol id="icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 6 6 18M6 6l12 12"></path>
        </symbol>
        <symbol id="icon-email" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect width="20" height="16" x="2" y="4" rx="2"></rect>
            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
        </symbol>
        <symbol id="icon-phone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
        </symbol>
        <symbol id="icon-location" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
            <circle cx="12" cy="10" r="3"></circle>
        </symbol>
        <symbol id="icon-facebook" viewBox="0 0 24 24" fill="currentColor">
            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
        </symbol>
        <symbol id="icon-instagram" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect width="20" height="20" x="2" y="2" rx="5" ry="5"></rect>
            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
            <line x1="17.5" x2="17.51" y1="6.5" y2="6.5"></line>
        </symbol>
        <symbol id="icon-wine" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M8 22h8M12 11v11M19.5 8c0 2-2.5 4-7.5 4s-7.5-2-7.5-4c0-2 1.5-7 1.5-7h12s1.5 5 1.5 7z"></path>
        </symbol>
        <symbol id="icon-arrow-right" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M5 12h14M12 5l7 7-7 7"></path>
        </symbol>
        <symbol id="icon-whatsapp" viewBox="0 0 24 24" fill="currentColor">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </symbol>
    </svg>

    <!-- WhatsApp Floating Button -->
    <?php
    $whatsappNumber = preg_replace('/[^0-9]/', '', SITE_PHONE_MOBILE);
    $whatsappMessage = urlencode('Hola, me gustaría recibir información sobre sus productos.');
    ?>
    <a href="https://wa.me/<?php echo $whatsappNumber; ?>?text=<?php echo $whatsappMessage; ?>"
       class="whatsapp-float"
       target="_blank"
       rel="noopener noreferrer"
       aria-label="Contactar por WhatsApp">
        <svg class="icon" aria-hidden="true"><use href="#icon-whatsapp"></use></svg>
        <span class="whatsapp-tooltip">¿Te ayudamos?</span>
    </a>

    <!-- Popup Tienda Online -->
    <div class="popup-overlay" id="popupTiendaOnline" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="popupTitle">
        <div class="popup-card">
            <button class="popup-close" id="popupClose" aria-label="Cerrar">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"></path></svg>
            </button>
            <div class="popup-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 01-8 0"></path>
                </svg>
            </div>
            <h2 class="popup-title" id="popupTitle">Tienda Online</h2>
            <p class="popup-subtitle">Muy pronto</p>
            <div class="popup-divider"></div>
            <p class="popup-text">Estamos preparando nuestra tienda online para que puedas disfrutar de nuestra selección de vinos, cavas, licores y mucho más desde la comodidad de tu hogar.</p>
            <p class="popup-highlight">Compra online, muy pronto disponible.</p>
            <button class="popup-btn" id="popupBtn">Entendido</button>
        </div>
    </div>

</body>
</html>
