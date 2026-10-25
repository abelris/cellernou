# El Celler Nou - Project Context

## Overview
Wine/liquor shop management system with a public-facing catalog website and a comprehensive admin panel for inventory, customers, suppliers, POS, and invoice processing.

**Company:** ALMABRIS GROUP 2024 S.L. (CIF: B66318684)
**Location:** C/ Ramón Llull 15, 08330 Premià de Mar, Barcelona
**URL:** https://cellernou.com

## Tech Stack
- **Backend:** PHP 7.4, MySQLi, Apache with mod_rewrite
- **Database:** MariaDB 10.11 (`cellernodb`)
- **Frontend (public):** HTML5, CSS3, vanilla JS, Google Fonts (Playfair Display + Inter)
- **Frontend (admin):** Custom CSS (`admin.css`), jQuery UI 1.11.1
- **OCR Module:** React 19 + TypeScript + Tailwind CSS 4 + Vite 7 (source at `/c/laragon/www/OCR Facturas/`)
- **PDF Libraries:** DomPDF, FPDF, TCPDF8
- **OCR Engine:** Tesseract.js 7 (Spanish)

## Project Structure

```
cellernou.com/
├── index.php                    # Front controller (Router dispatch)
├── .htaccess                    # HTTPS, rewrites, security headers
├── routes/
│   ├── Router.php               # Custom router with regex matching
│   └── web.php                  # Route definitions
├── controllers/                 # 12 public controllers (Home, Vinos, Cavas, etc.)
├── includes/
│   ├── config.php               # Site constants (SITE_NAME, URLs, phones)
│   ├── functions.php            # DB query helpers, product getters
│   ├── header.php / footer.php  # Public layout
│   └── seo.php                  # Meta tags, schema.org
├── css/style.css                # Public stylesheet
├── js/main.js                   # Public JS
├── distribucio/                 # Distribution & Admin system
│   ├── conf.php                 # Session config, company data
│   ├── connexio/connexio.php    # DB connection (mysqli)
│   ├── admin/                   # Modern admin panel
│   │   ├── index.php            # Login page
│   │   ├── dashboard.php        # Dashboard with stats
│   │   ├── includes/
│   │   │   ├── auth.php         # Auth system (bcrypt, CSRF, rate limiting)
│   │   │   ├── config.php       # URL helpers: getBaseUrl(), getAdminUrl()
│   │   │   ├── header.php       # Admin layout (sidebar nav, supports $useLayeredCss)
│   │   │   └── footer.php
│   │   ├── assets/css/admin.css # Admin styles (CSS variables, sidebar, cards, tables)
│   │   └── modules/
│   │       ├── productes/       # Product CRUD + inventari.php (editable grid)
│   │       ├── estoc/           # Stock management dashboard
│   │       ├── clients/         # Customer management
│   │       ├── proveidors/      # Supplier management
│   │       ├── tiquets/         # POS receipts
│   │       ├── ocr/             # OCR invoice processing (React app)
│   │       ├── database/        # Sectors, types, origins CRUD
│   │       ├── estadistiques/   # Reports
│   │       ├── configuracio/    # Web settings
│   │       └── control/         # System monitoring
│   ├── productes/               # Legacy product management
│   │   ├── inventario.php       # Reference inventory (stock_producto based)
│   │   ├── alta.php, modificar.php, borrar.php
│   │   └── stock/               # Stock operations
│   ├── clients/, compres/, vendes/, facturas/, tiquets/  # Legacy modules
│   └── proveidors/, sectores/, tipos/, procedencias/     # Legacy data management
└── sql/cellernodb.sql           # Full database dump
```

## Database

**Connection:** `distribucio/connexio/connexio.php` → global `$bd` variable (MySQLi)

### Key Tables
| Table | Purpose |
|-------|---------|
| `productos` | Product catalog (nombre, precio_medio, precio_venta_dis, iva, codigo, activo) |
| `stock_producto` | Stock units (1 row per unit, vendido=0 means in stock) |
| `precios_producto_tienda` | Per-store pricing (idTienda, idProducto, pvp) |
| `sectores` | Product sectors (Vinos=8, Licores=2, Cervezas=21, Cavas=14...) |
| `tipo_producto` | Product types/categories |
| `procedencia` | Origins / D.O. for wines |
| `clientes` | Customers with fiscal info |
| `proveedores` | Suppliers |
| `tiquets` / `linea_tiquet` | POS receipts |
| `facturas` / `linea_factura` | Customer invoices |
| `compras` / `linea_compra` | Purchase orders |
| `ventas` / `linea_venta` | Sales |
| `usuarios` | Admin users (id, nombre, usuario, password) |
| `admin_logs` | Activity logging |

### Stock System
Stock is tracked as **individual rows** in `stock_producto`. Each unit of a product is one row:
- `vendido=0` → unit is in stock
- To count stock: `SELECT COUNT(*) FROM stock_producto WHERE vendido=0 AND idProducto=X`
- To add N units: INSERT N rows with `vendido=0`
- To remove N units: DELETE N rows `WHERE vendido=0 ORDER BY fecha_entrada DESC LIMIT N`
- **Do NOT use `productos.unidades`** — it's a stale field. Always count from `stock_producto`.

## Authentication (Admin)

**File:** `distribucio/admin/includes/auth.php`
- BCrypt password hashing (cost 12)
- CSRF token generation/validation: `generateCsrfToken()`, `verifyCsrfToken()`
- Session timeout: 2 hours
- Rate limiting: 5 login attempts, 15-min lockout
- Activity logging: `logActivity($action, $details)`
- HTML escaping: `e($string)` (alias for htmlspecialchars)
- Session vars: `admin_user_id`, `admin_user_name`, `admin_usuario`

## Public Routing

**Router:** `routes/Router.php` — regex-based, named routes
```php
Router::get('/vinos', 'VinosController', 'vinos');
Router::get('/producto/{id}/{slug}', 'ProductoController', 'producto');
Router::url('producto', ['id' => 1, 'slug' => 'rioja']);  // Generate URL
```

**Categories:** / (home), /vinos, /cavas, /licores, /cervezas, /conservas
**Product pages:** /producto/{id}/{slug}
**Other:** /buscar, /contacto, /aviso-legal, /sitemap.xml

## OCR Module (React)

**Source:** `C:\laragon\www\OCR Facturas\`
**Build output:** `distribucio/admin/modules/ocr/dist/assets/`
**Build command:** `cd "C:\laragon\www\OCR Facturas" && npm run build`

Wizard-based invoice processing:
1. Upload PDF → 2. OCR + data extraction → 3. Accounting entry → 4. Export (Excel/CSV)

**CSS Layer Fix:** The OCR page sets `$useLayeredCss = true` so admin.css loads inside `@layer admin`, preventing it from overriding Tailwind v4's `@layer utilities`. This is necessary because unlayered CSS always beats layered CSS in the cascade.

## SEO: mapa de contenidos (obligatorio)

**Copia maestra:** `docs/seo/mapa-contenidos.html` (se abre en `http://localhost/cellernou/docs/seo/mapa-contenidos.html` o con doble clic). Dice qué búsqueda ataca cada página, qué vende cada artículo del blog, las reglas para que dos páginas no compitan y los cambios pendientes.

- **Antes** de tocar títulos, textos de categoría o D.O., fichas, blog, enlazado, sitemap o indexación: consultar el mapa y respetar sus reglas.
- **Después** de cada cambio: actualizar el bloque `<script id="datos">` del mapa (estado de la fila, cambio marcado como hecho) y añadir una línea arriba en `historial` (fecha, qué, dónde).
- Objetivo del usuario: ser **la tienda de vinos de referencia del Maresme, por catálogo y por servicio** (asesoramiento, reparto, regalos, catas). Cada contenido nuevo tiene que reforzar las dos cosas.
- Indexación (`includes/functions.php`, sección INDEXACIÓN): ficha indexable = con stock, con texto o con tráfico de Google (tabla `seo_trafico`, refrescar con `scripts/importar_search_console.py` y cada exportación mensual de Search Console). Nunca quitar del índice páginas con tráfico sin cruzarlo antes con Search Console.
- Plan de trabajo: planner de Vanesa `cellernou-plan-seo-contenidos`; acciones diarias del usuario en el plan SEO `/seo` (plan `cellernou-2026-10`). Fichas por tandas: `scripts/cargar_tanda.py`.

## Conventions

- **Languages:** Mix of Catalan (UI labels, variable names) and Spanish (data, some code)
- **DB field naming:** Spanish (nombre, precio_medio, fecha_entrada, vendido)
- **Multilingual fields:** Some tables have `nombre_es` and `nombre_cat` columns
- **Legacy vs new:** Legacy code in `distribucio/productes/`, new admin code in `distribucio/admin/modules/`
- **Default store:** `idTienda = 1` is the main/default store
- **Session user:** Legacy uses `$_SESSION["idUsuario"]`, admin uses `$_SESSION["admin_user_id"]`
- **No ORM:** Direct SQL queries everywhere via global `$bd` (MySQLi)
- **PDF generation:** Multiple libraries available (DomPDF, FPDF, TCPDF8)
