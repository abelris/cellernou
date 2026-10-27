<?php
/**
 * El Celler Nou - Admin Header
 */

// Asegurar autenticación
requireAuth();

$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$userRole = getUserRole();

// Definir navegación por marca
$brands = [
    'almabris' => [
        'brand_name' => 'Business Intelligence',
        'brand_class' => 'almabris',
        'sections' => [
            'analisi' => [
                'title' => 'Anàlisi',
                'items' => [
                    [
                        'id' => 'bi',
                        'label' => 'Dashboard BI',
                        'url' => getAdminUrl('modules/bi/'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
                        'children' => [
                            [
                                'id' => 'bi/cellernou',
                                'label' => 'El Celler Nou',
                                'url' => getAdminUrl('modules/bi/cellernou.php'),
                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l4-4 4 4 5-5"/>'
                            ],
                            [
                                'id' => 'bi/ondigitalris',
                                'label' => 'ondigitalris',
                                'url' => getAdminUrl('modules/bi/ondigitalris.php'),
                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l4-4 4 4 5-5"/>'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'gestoria' => [
        'brand_name' => 'Gestoria / Comptabilitat',
        'brand_class' => 'gestoria',
        'sections' => [
            'fiscal' => [
                'title' => 'Fiscal',
                'items' => [
                    [
                        'id' => 'gestoria',
                        'label' => 'Resum Fiscal',
                        'url' => getAdminUrl('modules/gestoria/'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
                        'children' => [
                            [
                                'id' => 'gestoria/tiquets',
                                'label' => 'Tiquets',
                                'url' => getAdminUrl('modules/gestoria/tiquets.php'),
                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>'
                            ],
                            [
                                'id' => 'gestoria/facturas',
                                'label' => 'Factures',
                                'url' => getAdminUrl('modules/gestoria/facturas.php'),
                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'cellernou' => [
        'brand_name' => 'El Celler Nou',
        'brand_class' => 'cellernou',
        'sections' => [
            'principal' => [
                'title' => 'Principal',
                'items' => [
                    [
                        'id' => 'dashboard',
                        'label' => 'Dashboard',
                        'url' => getAdminUrl('dashboard.php'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'
                    ]
                ]
            ],
            'gestio' => [
                'title' => 'Gestió',
                'items' => [
                    [
                        'id' => 'estoc',
                        'label' => 'Estoc',
                        'url' => getAdminUrl('modules/estoc/'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'
                    ],
                    [
                        'id' => 'clients',
                        'label' => 'Clients',
                        'url' => getAdminUrl('modules/clients/'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
                        'children' => [
                            [
                                'id' => 'vendes',
                                'label' => 'Vendes',
                                'url' => getAdminUrl('modules/vendes/'),
                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>'
                            ],
                            [
                                'id' => 'facturas',
                                'label' => 'Factures',
                                'url' => getAdminUrl('modules/facturas/'),
                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'
                            ]
                        ]
                    ],
                    [
                        'id' => 'proveidors',
                        'label' => 'Proveidors',
                        'url' => getAdminUrl('modules/proveidors/'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V2"/>',
                        'children' => [
                            [
                                'id' => 'compres',
                                'label' => 'Compres',
                                'url' => getAdminUrl('modules/compres/'),
                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>'
                            ],
                            [
                                'id' => 'factures_compra',
                                'label' => 'Factures Compra',
                                'url' => getAdminUrl('modules/factures_compra/'),
                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'
                            ]
                        ]
                    ],
                    [
                        'id' => 'tiquets',
                        'label' => 'Tiquets',
                        'url' => getAdminUrl('modules/tiquets/'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>'
                    ],
                    [
                        'id' => 'ocr',
                        'label' => 'OCR Factures',
                        'url' => getAdminUrl('modules/ocr/'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'
                    ],
                    [
                        'id' => 'komkal',
                        'label' => 'Komkal Import',
                        'url' => getAdminUrl('modules/komkal/'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>',
                        'children' => [
                            [
                                'id' => 'komkal/importar',
                                'label' => 'Importar XLSX',
                                'url' => getAdminUrl('modules/komkal/importar.php'),
                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>'
                            ],
                            [
                                'id' => 'komkal/matching',
                                'label' => 'Vinculacio',
                                'url' => getAdminUrl('modules/komkal/matching.php'),
                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>'
                            ]
                        ]
                    ]
                ]
            ],
            'contingut' => [
                'title' => 'Contingut',
                'items' => [
                    [
                        'id' => 'wineworld',
                        'label' => 'WineWorld',
                        'url' => getAdminUrl('modules/wineworld/'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>',
                        'children' => [
                            [
                                'id' => 'wineworld/articulos',
                                'label' => 'Articles',
                                'url' => getAdminUrl('modules/wineworld/articulos.php'),
                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'
                            ],
                            [
                                'id' => 'wineworld/textos',
                                'label' => 'Textos de categoria',
                                'url' => getAdminUrl('modules/wineworld/textos.php'),
                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>'
                            ],
                            [
                                'id' => 'wineworld/seo',
                                'label' => 'Estratègia SEO',
                                'url' => getAdminUrl('modules/wineworld/seo.php'),
                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>'
                            ]
                        ]
                    ],
                    [
                        'id' => 'catas',
                        'label' => 'Catas',
                        'url' => getAdminUrl('modules/catas/'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>'
                    ]
                ]
            ],
            'sistema' => [
                'title' => 'Sistema',
                'items' => [
                    [
                        'id' => 'database',
                        'label' => 'Base de Dades',
                        'url' => getAdminUrl('modules/database/'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>'
                    ],
                    [
                        'id' => 'estadistiques',
                        'label' => 'Estadístiques',
                        'url' => getAdminUrl('modules/estadistiques/'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'
                    ],
                    [
                        'id' => 'configuracio',
                        'label' => 'Configuració Web',
                        'url' => getAdminUrl('modules/configuracio/'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>'
                    ],
                    [
                        'id' => 'control',
                        'label' => 'Control',
                        'url' => getAdminUrl('modules/control/'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'
                    ]
                ]
            ]
        ]
    ],
    'ondigitalris' => [
        'brand_name' => 'ondigitalris',
        'brand_class' => 'ondigitalris',
        'sections' => [
            'principal' => [
                'title' => 'Principal',
                'items' => [
                    [
                        'id' => 'ondigitalris',
                        'label' => 'Dashboard',
                        'url' => getAdminUrl('modules/ondigitalris/'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'
                    ]
                ]
            ],
            'gestio' => [
                'title' => 'Gestió',
                'items' => [
                    [
                        'id' => 'odr_clients',
                        'label' => 'Clients',
                        'url' => getAdminUrl('modules/odr_clients/'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
                        'children' => [
                            [
                                'id' => 'odr_facturas',
                                'label' => 'Factures Venda',
                                'url' => getAdminUrl('modules/odr_facturas/'),
                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'
                            ]
                        ]
                    ],
                    [
                        'id' => 'odr_proveidors',
                        'label' => 'Proveidors',
                        'url' => getAdminUrl('modules/odr_proveidors/'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V2"/>',
                        'children' => [
                            [
                                'id' => 'odr_factures_compra',
                                'label' => 'Factures Compra',
                                'url' => getAdminUrl('modules/odr_factures_compra/'),
                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];

// Función para verificar si el menú está activo
function isMenuActive($itemId, $item = null): bool {
    global $currentPage;
    $currentPath = $_SERVER['REQUEST_URI'];

    if ($itemId === 'dashboard' && $currentPage === 'dashboard') {
        return true;
    }

    if (strpos($currentPath, '/modules/' . $itemId) !== false) {
        return true;
    }

    // Comprobar si algún hijo está activo
    if ($item && !empty($item['children'])) {
        foreach ($item['children'] as $child) {
            if (strpos($currentPath, '/modules/' . $child['id']) !== false) {
                return true;
            }
        }
    }

    return false;
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' - ' : ''; ?>ALMABRIS GROUP Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php if (!empty($useLayeredCss)): ?>
    <style>
    @layer admin, properties, theme, utilities;
    @import url("<?php echo getAdminUrl('assets/css/admin.css'); ?>") layer(admin);
    </style>
    <?php else: ?>
    <link rel="stylesheet" href="<?php echo getAdminUrl('assets/css/admin.css'); ?>">
    <?php endif; ?>
    <?php if (isset($extraCss)): ?>
    <?php echo $extraCss; ?>
    <?php endif; ?>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="<?php echo getAdminUrl('dashboard.php'); ?>" class="sidebar-logo">
                    <div class="sidebar-logo-icon">AG</div>
                    <span>ALMABRIS GROUP</span>
                </a>
            </div>

            <nav class="sidebar-nav">
                <?php foreach ($brands as $brandKey => $brand): ?>
                <?php
                // Filtrar sidebar segons rol d'usuari
                if ($userRole === 'gestoria' && $brandKey !== 'gestoria') continue;
                ?>
                <div class="brand-header <?php echo $brand['brand_class']; ?>">
                    <span class="brand-header-name"><?php echo $brand['brand_name']; ?></span>
                    <?php if (!empty($brand['badge'])): ?>
                    <span class="brand-header-badge"><?php echo $brand['badge']; ?></span>
                    <?php endif; ?>
                </div>
                <div class="brand-<?php echo $brand['brand_class']; ?>">
                <?php foreach ($brand['sections'] as $section): ?>
                <div class="nav-section">
                    <div class="nav-section-title"><?php echo $section['title']; ?></div>
                    <ul class="nav-menu">
                        <?php foreach ($section['items'] as $item): ?>
                        <?php $parentActive = isMenuActive($item['id'], $item); ?>
                        <li class="nav-item">
                            <a href="<?php echo $item['url']; ?>" class="nav-link<?php echo $parentActive ? ' active' : ''; ?>">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <?php echo $item['icon']; ?>
                                </svg>
                                <span><?php echo $item['label']; ?></span>
                                <?php if (!empty($item['children'])): ?>
                                <svg class="nav-arrow<?php echo $parentActive ? ' open' : ''; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px; margin-left: auto; transition: transform 0.2s;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                                <?php endif; ?>
                            </a>
                            <?php if (!empty($item['children'])): ?>
                            <ul class="nav-submenu" style="<?php echo $parentActive ? '' : 'display: none;'; ?>">
                                <?php foreach ($item['children'] as $child): ?>
                                <li class="nav-item">
                                    <a href="<?php echo $child['url']; ?>" class="nav-link nav-link-child<?php echo isMenuActive($child['id']) ? ' active' : ''; ?>">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <?php echo $child['icon']; ?>
                                        </svg>
                                        <span><?php echo $child['label']; ?></span>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </nav>

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($currentUser['nombre'], 0, 2)); ?>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?php echo e($currentUser['nombre']); ?></div>
                        <div class="user-role">ALMABRIS GROUP 2024 S.L.</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <header class="admin-header">
                <div class="header-left">
                    <button class="menu-toggle" onclick="toggleSidebar()" aria-label="Toggle menu">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="page-title"><?php echo isset($pageTitle) ? e($pageTitle) : 'Dashboard'; ?></h1>
                </div>
                <div class="header-right">
                    <a href="<?php echo dirname(BASE_PATH); ?>/" class="header-btn" title="Veure web" target="_blank">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                    <a href="<?php echo getAdminUrl('logout.php'); ?>" class="header-btn" title="Tancar sessió">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </a>
                </div>
            </header>

            <!-- Page Content -->
            <main class="page-content">
