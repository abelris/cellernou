<?php
if (!isset($pageTitle)) $pageTitle = SITE_NAME;
if (!isset($pageDescription)) $pageDescription = SITE_DESCRIPTION;
if (!isset($pageType)) $pageType = 'website';
if (!isset($pageImage)) $pageImage = getBaseUrl() . '/images/og-default.jpg';
$currentUrl = getBaseUrl() . $_SERVER['REQUEST_URI'];

// Obtener tipos para submenús
$tiposVinos = getTiposParaMenu('vinos');
$tiposLicores = getTiposParaMenu('licores');
$tiposCavas = getTiposParaMenu('cavas');

// D.O. para vinos
$procedenciasVinos = getProcedencias(8);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <?php echo generarMetaTags([
        'title' => $pageTitle,
        'description' => $pageDescription,
        'url' => $currentUrl,
        'image' => $pageImage,
        'type' => $pageType
    ]); ?>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?php echo getBaseUrl(); ?>/favicon.svg">

    <!-- Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo getBaseUrl(); ?>/css/style.css">

    <!-- Schema.org -->
    <?php echo schemaOrganizacion(); ?>
    <?php echo schemaWebSite(); ?>
    <?php if (isset($schemaExtra)) echo $schemaExtra; ?>
    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-MQS7YVS98L"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-MQS7YVS98L');
</script>
</head>
<body>
    <a href="#main-content" class="skip-link">Saltar al contenido principal</a>

    <header class="site-header" role="banner">
        <div class="header-top">
            <div class="container">
                <div class="header-top-content">
                    <div class="contact-info contact-phones">
                        <a href="tel:<?php echo preg_replace('/\s+/', '', SITE_PHONE); ?>">
                            <svg class="icon" aria-hidden="true"><use href="#icon-phone"></use></svg>
                            <span class="hide-mobile"><?php echo SITE_PHONE; ?></span>
                        </a>
                        <a href="tel:<?php echo preg_replace('/\s+/', '', SITE_PHONE_MOBILE); ?>">
                            <svg class="icon" aria-hidden="true"><use href="#icon-phone"></use></svg>
                            <span class="hide-mobile"><?php echo SITE_PHONE_MOBILE; ?></span>
                        </a>
                    </div>
                    <div class="contact-info contact-email">
                        <a href="mailto:<?php echo SITE_EMAIL; ?>">
                            <svg class="icon" aria-hidden="true"><use href="#icon-email"></use></svg>
                            <span class="hide-mobile"><?php echo SITE_EMAIL; ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="header-main">
            <div class="container">
                <div class="header-content">
                    <a href="<?php echo Router::url('home'); ?>" class="logo" aria-label="<?php echo SITE_NAME; ?> - Inicio">
                        <svg class="logo-icon" viewBox="0 0 24 32" aria-hidden="true">
                            <path d="M12 0C12 0 24 12 24 20C24 26.6 18.6 32 12 32C5.4 32 0 26.6 0 20C0 12 12 0 12 0Z"/>
                        </svg>
                        <span class="logo-text">
                            <span class="logo-line1">El Celler</span>
                            <span class="logo-line2">Nou</span>
                        </span>
                    </a>

                    <button class="search-toggle-mobile" aria-label="Abrir buscador" aria-expanded="false">
                        <svg class="icon" aria-hidden="true"><use href="#icon-search"></use></svg>
                    </button>

                    <button class="nav-toggle" aria-label="Abrir menú" aria-expanded="false" aria-controls="main-nav">
                        <span class="hamburger"></span>
                    </button>

                    <nav class="main-nav" id="main-nav" role="navigation" aria-label="Navegación principal">
                        <ul class="nav-menu">
                            <li class="nav-item">
                                <a href="<?php echo Router::url('home'); ?>" class="nav-link<?php echo Router::is('home') ? ' active' : ''; ?>">Inicio</a>
                            </li>

                            <li class="nav-item has-submenu">
                                <a href="<?php echo Router::url('vinos'); ?>" class="nav-link<?php echo strpos(Router::currentRoute() ?? '', 'vinos') !== false ? ' active' : ''; ?>" aria-haspopup="true">
                                    Vinos
                                    <svg class="icon chevron" aria-hidden="true"><use href="#icon-chevron"></use></svg>
                                </a>
                                <div class="submenu mega-menu">
                                    <div class="submenu-inner">
                                        <div class="submenu-column">
                                            <h3 class="submenu-title">Por Tipo</h3>
                                            <ul class="submenu-list">
                                                <?php foreach ($tiposVinos as $tipo): ?>
                                                <li><a href="<?php echo Router::url('vinos.tipo', ['id' => $tipo['id'], 'slug' => createSlug($tipo['nombre_es'])]); ?>"><?php echo htmlspecialchars($tipo['nombre_es']); ?></a></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <div class="submenu-column">
                                            <h3 class="submenu-title">Por D.O.</h3>
                                            <ul class="submenu-list">
                                                <?php
                                                $countDO = 0;
                                                foreach ($procedenciasVinos as $proc):
                                                    if ($countDO >= 10) break;
                                                    if (strpos($proc['nombre_es'], 'D.O.') !== false):
                                                ?>
                                                <li><a href="<?php echo Router::url('vinos.do', ['id' => $proc['id'], 'slug' => createSlug($proc['nombre_es'])]); ?>"><?php echo htmlspecialchars($proc['nombre_es']); ?></a></li>
                                                <?php
                                                    $countDO++;
                                                    endif;
                                                endforeach;
                                                ?>
                                                <li><a href="<?php echo Router::url('vinos'); ?>" class="see-all">Ver todos los vinos →</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            <li class="nav-item has-submenu">
                                <a href="<?php echo Router::url('cavas'); ?>" class="nav-link<?php echo strpos(Router::currentRoute() ?? '', 'cavas') !== false ? ' active' : ''; ?>" aria-haspopup="true">
                                    Cavas y Espumosos
                                    <svg class="icon chevron" aria-hidden="true"><use href="#icon-chevron"></use></svg>
                                </a>
                                <div class="submenu">
                                    <ul class="submenu-list">
                                        <?php foreach ($tiposCavas as $tipo): ?>
                                        <li><a href="<?php echo Router::url('cavas.tipo', ['id' => $tipo['id'], 'slug' => createSlug($tipo['nombre_es'])]); ?>"><?php echo htmlspecialchars($tipo['nombre_es']); ?></a></li>
                                        <?php endforeach; ?>
                                        <li><a href="<?php echo Router::url('cavas'); ?>" class="see-all">Ver todo →</a></li>
                                    </ul>
                                </div>
                            </li>

                            <li class="nav-item has-submenu">
                                <a href="<?php echo Router::url('licores'); ?>" class="nav-link<?php echo strpos(Router::currentRoute() ?? '', 'licores') !== false ? ' active' : ''; ?>" aria-haspopup="true">
                                    Licores
                                    <svg class="icon chevron" aria-hidden="true"><use href="#icon-chevron"></use></svg>
                                </a>
                                <div class="submenu mega-menu">
                                    <div class="submenu-inner">
                                        <div class="submenu-column">
                                            <h3 class="submenu-title">Destilados</h3>
                                            <ul class="submenu-list">
                                                <?php
                                                $destilados = ['Whisky', 'Ron', 'Ginebra', 'Vodka', 'Tequila', 'Brandy'];
                                                foreach ($tiposLicores as $tipo):
                                                    if (in_array($tipo['nombre_es'], $destilados)):
                                                ?>
                                                <li><a href="<?php echo Router::url('licores.tipo', ['id' => $tipo['id'], 'slug' => createSlug($tipo['nombre_es'])]); ?>"><?php echo htmlspecialchars($tipo['nombre_es']); ?></a></li>
                                                <?php
                                                    endif;
                                                endforeach;
                                                ?>
                                            </ul>
                                        </div>
                                        <div class="submenu-column">
                                            <h3 class="submenu-title">Licores y Otros</h3>
                                            <ul class="submenu-list">
                                                <?php
                                                $otros = ['Vermut', 'Orujo', 'Anís', 'Ratafia', 'Pacharán'];
                                                foreach ($tiposLicores as $tipo):
                                                    if (in_array($tipo['nombre_es'], $otros) || strpos($tipo['nombre_es'], 'Licor') !== false):
                                                ?>
                                                <li><a href="<?php echo Router::url('licores.tipo', ['id' => $tipo['id'], 'slug' => createSlug($tipo['nombre_es'])]); ?>"><?php echo htmlspecialchars($tipo['nombre_es']); ?></a></li>
                                                <?php
                                                    endif;
                                                endforeach;
                                                ?>
                                                <li><a href="<?php echo Router::url('licores'); ?>" class="see-all">Ver todos los licores →</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            <li class="nav-item">
                                <a href="<?php echo Router::url('cervezas'); ?>" class="nav-link<?php echo Router::is('cervezas') ? ' active' : ''; ?>">Cervezas</a>
                            </li>

                            <li class="nav-item">
                                <a href="<?php echo Router::url('conservas'); ?>" class="nav-link<?php echo strpos(Router::currentRoute() ?? '', 'conservas') !== false ? ' active' : ''; ?>">Conservas</a>
                            </li>

                            <li class="nav-item has-submenu">
                                <a href="<?php echo Router::url('wineworld'); ?>" class="nav-link<?php echo strpos(Router::currentRoute() ?? '', 'wineworld') !== false ? ' active' : ''; ?>" aria-haspopup="true">
                                    WineWorld
                                    <svg class="icon chevron" aria-hidden="true"><use href="#icon-chevron"></use></svg>
                                </a>
                                <div class="submenu">
                                    <ul class="submenu-list">
                                        <?php
                                        $wwCategorias = getWWCategorias();
                                        foreach ($wwCategorias as $wwCat):
                                        ?>
                                        <li><a href="<?php echo Router::url('wineworld.categoria', ['cat_slug' => $wwCat['slug']]); ?>"><?php echo htmlspecialchars($wwCat['nombre']); ?></a></li>
                                        <?php endforeach; ?>
                                        <li><a href="<?php echo Router::url('wineworld'); ?>" class="see-all">Ver todo WineWorld →</a></li>
                                    </ul>
                                </div>
                            </li>

                            <li class="nav-item">
                                <a href="<?php echo Router::url('contacto'); ?>" class="nav-link<?php echo Router::is('contacto') ? ' active' : ''; ?>">Contacto</a>
                            </li>
                        </ul>
                    </nav>

                    <div class="header-search">
                        <form action="<?php echo Router::url('buscar'); ?>" method="GET" class="search-form" role="search">
                            <label for="search-input" class="sr-only">Buscar productos</label>
                            <input type="search" id="search-input" name="q" placeholder="Buscar vinos, licores..." autocomplete="off" required>
                            <button type="submit" aria-label="Buscar">
                                <svg class="icon" aria-hidden="true"><use href="#icon-search"></use></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="search-overlay" aria-hidden="true">
        <div class="search-overlay-content">
            <form action="<?php echo Router::url('buscar'); ?>" method="GET" class="search-form-mobile" role="search">
                <label for="search-input-mobile" class="sr-only">Buscar productos</label>
                <input type="search" id="search-input-mobile" name="q" placeholder="Buscar vinos, licores..." autocomplete="off" required>
                <button type="submit" aria-label="Buscar">
                    <svg class="icon" aria-hidden="true"><use href="#icon-search"></use></svg>
                </button>
            </form>
            <button class="search-close" aria-label="Cerrar buscador">
                <svg class="icon" aria-hidden="true"><use href="#icon-close"></use></svg>
            </button>
        </div>
    </div>

    <main id="main-content" role="main">
