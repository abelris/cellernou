<?php
/**
 * Plan de crecimiento de la tienda (aprobado el 15-nov-2026).
 *
 * Fuente única de las tareas: el módulo las pinta desde aquí y guarda en
 * `crecimiento_estado` solo lo que cambia (hecha, quién, cuándo, nota).
 * Para añadir una tarea: un código nuevo (no reutilizar los viejos).
 *
 * Sin cifras de facturación: el repositorio es público. Las cifras salen
 * del TPV al abrir la página (ver indicadores.php).
 */

return [
    'inicio' => '2026-11-16',
    'fases'  => [
        'f1' => ['nombre' => 'Semana 1', 'cuando' => '16-22 nov'],
        'f2' => ['nombre' => 'Campaña de Navidad', 'cuando' => '23 nov - 6 ene'],
        'f3' => ['nombre' => 'Enero', 'cuando' => 'ene 2027'],
        'f4' => ['nombre' => 'Febrero y marzo', 'cuando' => 'feb - mar 2027'],
        'f5' => ['nombre' => 'Abril a junio', 'cuando' => 'abr - jun 2027'],
    ],
    'bloques' => [
        'tienda' => 'Tienda: tiquet y venta cruzada',
        'web'    => 'Web: atraer y convertir',
    ],
    'tareas' => [
        // ---- Semana 1
        ['codigo' => 'T01', 'fase' => 'f1', 'bloque' => 'tienda', 'quien' => 'Victoria',
         'titulo' => 'Kit vermut del domingo junto al grifo',
         'detalle' => 'Litro de vermut, sifón, una lata (berberechos o mejillones), Salsa Espinaler en botellín y patatas, a precio cerrado de 12-15 €. Expuesto viernes, sábado y domingo. Es la palanca más grande: tres de cada cuatro clientes de granel se van solo con el litro.'],
        ['codigo' => 'T02', 'fase' => 'f1', 'bloque' => 'tienda', 'quien' => 'Equipo',
         'titulo' => 'Pregunta fija en caja y producto de impulso',
         'detalle' => 'Siempre la misma: «¿te pongo unas aceitunas, un sifón o una lata para el vermut?». En el mostrador, solo producto de 2-4 €: patatas, aceitunas rellenas, torreznos, botellín de salsa y sobres de ibérico.'],
        ['codigo' => 'T03', 'fase' => 'f1', 'bloque' => 'tienda', 'quien' => 'Victoria',
         'titulo' => 'Stock mínimo de aperitivo para el fin de semana',
         'detalle' => 'Las 6 latas Espinaler que más rotan, salsa y patatas, con reposición cada jueves. Es lo que más se compra en fin de semana y hoy casi todo está a 0.'],
        ['codigo' => 'T04', 'fase' => 'f1', 'bloque' => 'tienda', 'quien' => 'Equipo',
         'titulo' => 'Frío a la vista de viernes a domingo',
         'detalle' => 'Cava De Sota Terra, vermut y cerveza en nevera, para llevar y servir.'],
        ['codigo' => 'T05', 'fase' => 'f1', 'bloque' => 'tienda', 'quien' => 'Victoria',
         'titulo' => 'Carteles de escalera de precio',
         'detalle' => 'Cava: «¿Te gusta el De Sota Terra? Prueba el Anne Marie Reserva». Granel: «¿Te gusta el Priorat a granel? Esta botella es del mismo sitio», con 3 botellas por D.O. junto al grifo.'],
        ['codigo' => 'T06', 'fase' => 'f1', 'bloque' => 'web', 'quien' => 'Dirección',
         'titulo' => 'Fotos reales y QR de reseñas en la ficha de Google',
         'detalle' => '10 fotos reales esta semana (fachada, interior, granel, lotes, jamón, equipo). QR de reseñas en la caja y dentro de los lotes.'],
        ['codigo' => 'T07', 'fase' => 'f1', 'bloque' => 'web', 'quien' => 'Claude',
         'titulo' => 'Kit vermut en el carrusel y en la cinta de avisos',
         'detalle' => 'Cuando el kit esté montado en tienda (T01): diapositiva en la home y aviso en la cinta, con botón de WhatsApp.'],
        ['codigo' => 'T08', 'fase' => 'f1', 'bloque' => 'web', 'quien' => 'Claude',
         'titulo' => 'Panel de seguimiento en el admin',
         'detalle' => 'Esta página: tareas con casillas e indicadores del TPV calculados solos cada mes.'],

        // ---- Navidad
        ['codigo' => 'T09', 'fase' => 'f2', 'bloque' => 'tienda', 'quien' => 'Victoria',
         'titulo' => 'Tres lotes de muestra montados (25, 40 y 60 €)',
         'detalle' => 'A la vista, con el cartel «te lo hacemos a medida». Que el cliente señale uno y se lo llevéis montado.'],
        ['codigo' => 'T10', 'fase' => 'f2', 'bloque' => 'tienda', 'quien' => 'Victoria',
         'titulo' => 'Cava 2 × X en tienda y en la web',
         'detalle' => 'Elegir el cava y el precio de la pareja. Cartel en tienda; Claude lo pone en la cinta de avisos y en la página de cavas.'],
        ['codigo' => 'T11', 'fase' => 'f2', 'bloque' => 'tienda', 'quien' => 'Victoria',
         'titulo' => 'Encargos de jamón y paleta Sierra Morena',
         'detalle' => 'Cartel del I Mundial del Jamón en tienda y hoja de encargos con fecha de recogida.'],
        ['codigo' => 'T12', 'fase' => 'f2', 'bloque' => 'tienda', 'quien' => 'Dirección',
         'titulo' => 'Propuesta de regalos de empresa a los clientes con factura',
         'detalle' => 'Lotes por tramos de precio, con tarjeta de la empresa y reparto en el Maresme. Enviar antes del 1 de diciembre.'],
        ['codigo' => 'T13', 'fase' => 'f2', 'bloque' => 'web', 'quien' => 'Dirección',
         'titulo' => 'Una publicación por semana en la ficha de Google',
         'detalle' => 'Lotes, jamón, Espinaler, festivos. Foto real y botón «Más información» a la página que toque.'],
        ['codigo' => 'T14', 'fase' => 'f2', 'bloque' => 'web', 'quien' => 'Claude',
         'titulo' => '«Encárgalo por WhatsApp y recógelo en tienda»',
         'detalle' => 'En el carrusel y en las fichas, para ganar las ventas de quien no puede venir a buscarlo.'],
        ['codigo' => 'T15', 'fase' => 'f2', 'bloque' => 'web', 'quien' => 'Dirección',
         'titulo' => 'Saber qué ventas vienen de la web',
         'detalle' => 'En caja, «¿cómo nos has conocido?» con una tecla en el TPV, o el código «WEB» en los pedidos por WhatsApp.'],
        ['codigo' => 'T16', 'fase' => 'f2', 'bloque' => 'web', 'quien' => 'Dirección',
         'titulo' => 'Enlaces de las marcas y directorios locales',
         'detalle' => 'Pedir a Espinaler y a Sierra Morena que nos pongan en sus puntos de venta con enlace. Alta en directorios locales y en el comercio de Premià.'],

        // ---- Enero
        ['codigo' => 'T17', 'fase' => 'f3', 'bloque' => 'tienda', 'quien' => 'Dirección y Claude',
         'titulo' => 'Revisión de resultados de Navidad',
         'detalle' => 'Indicadores de esta página, diciembre contra el año pasado y Search Console. Qué repetir y qué cambiar.'],
        ['codigo' => 'T18', 'fase' => 'f3', 'bloque' => 'tienda', 'quien' => 'Victoria',
         'titulo' => 'Tienda por momentos de consumo',
         'detalle' => 'Zona aperitivo (vermut a granel, conservas, salsas, aceitunas, patatas, sifón), zona regalo (lotes, cavas, licores, estuches) y zona del día a día (granel y vino de diario).'],
        ['codigo' => 'T19', 'fase' => 'f3', 'bloque' => 'tienda', 'quien' => 'Dirección',
         'titulo' => 'Garrafa de 5 L con precio por litro para habituales',
         'detalle' => 'Algo menor que el litro suelto. Ya se venden garrafas sin empujarlas.'],
        ['codigo' => 'T20', 'fase' => 'f3', 'bloque' => 'web', 'quien' => 'Claude',
         'titulo' => 'Lista de clientes con consentimiento',
         'detalle' => 'Alta en la web y en caja para recibir la oferta de la semana por WhatsApp o correo. Solo a quien diga que sí expresamente y con baja fácil (LSSI y RGPD).'],

        // ---- Febrero y marzo
        ['codigo' => 'T21', 'fase' => 'f4', 'bloque' => 'tienda', 'quien' => 'Victoria',
         'titulo' => 'Tarjeta de sellos del granel',
         'detalle' => 'Por ejemplo, 10 litros = una lata o un botellín de salsa de regalo (no vino gratis). Fideliza y presenta producto nuevo.'],
        ['codigo' => 'T22', 'fase' => 'f4', 'bloque' => 'tienda', 'quien' => 'Victoria',
         'titulo' => 'Catas mensuales con compra',
         'detalle' => 'La cata termina con un 10 % en lo catado ese día.'],
        ['codigo' => 'T23', 'fase' => 'f4', 'bloque' => 'tienda', 'quien' => 'Claude',
         'titulo' => 'Guion de venta mensual para el equipo',
         'detalle' => 'Una hoja con las 5 recomendaciones del mes: qué ofrecer con el granel, con el cava, con el jamón…'],
        ['codigo' => 'T24', 'fase' => 'f4', 'bloque' => 'web', 'quien' => 'Claude',
         'titulo' => 'Dos páginas de marca nuevas al mes',
         'detalle' => 'Bodegas o licores con tirón, con su permiso. Se suman solas a /marcas y al menú.'],
        ['codigo' => 'T25', 'fase' => 'f4', 'bloque' => 'web', 'quien' => 'Claude',
         'titulo' => 'Un artículo por semana enlazado a productos',
         'detalle' => 'Contenido de temporada (primavera: vermut y rosados) y tandas de fichas cada semana.'],

        // ---- Abril a junio
        ['codigo' => 'T26', 'fase' => 'f5', 'bloque' => 'tienda', 'quien' => 'Victoria y Claude',
         'titulo' => 'Campaña de vermut y verano',
         'detalle' => 'Kit vermut, rosados y frío. En tienda y en la home.'],
        ['codigo' => 'T27', 'fase' => 'f5', 'bloque' => 'tienda', 'quien' => 'Dirección',
         'titulo' => 'Vermut y conservas para la barra de los restaurantes',
         'detalle' => 'Propuesta a los restaurantes a los que ya hacéis la carta de vinos.'],
        ['codigo' => 'T28', 'fase' => 'f5', 'bloque' => 'web', 'quien' => 'Claude',
         'titulo' => 'Revisión mensual de SEO local',
         'detalle' => 'Search Console cada mes. Objetivo: top 5 en «tienda de vinos» y «vino a domicilio» en las poblaciones cercanas.'],
        ['codigo' => 'T29', 'fase' => 'f5', 'bloque' => 'web', 'quien' => 'Dirección y Claude',
         'titulo' => 'Decidir el pedido online',
         'detalle' => 'Carrito ligero con recogida en tienda y reparto en el Maresme, dimensionado con los contactos y las ventas web de diciembre.'],
    ],
];
