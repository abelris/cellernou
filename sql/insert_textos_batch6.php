<?php
/**
 * Batch 6: Insert texts for 50 products
 * Original descriptions and tasting notes
 */
require dirname(__DIR__) . '/distribucio/connexio/connexio.php';
mysqli_query($bd, "SET SESSION sql_mode=''");
mysqli_set_charset($bd, 'utf8mb4');

$textos = [
    // ===== VINOS (18) =====
    [349, 'Rose Vimiere Bordeaux',
        'Rosado francés de Burdeos elaborado con las variedades bordelesas clásicas (merlot y cabernet sauvignon) vinificadas en rosado por prensado directo. Un rosado ligero y elegante que refleja la tradición vinícola del suroeste francés, pensado como compañero versátil de mesa.',
        "Nariz: Aromas de fresa, frambuesa, pomelo rosado y un toque floral de flor de acacia con un sutil fondo especiado.\nBoca: Ligero y fresco, con buena acidez, sabores de frutos rojos, cítricos y un punto mineral elegante.\nFinal: Medio y limpio, con un retrogusto de fruta roja que lo hace ideal como aperitivo o para comidas ligeras."
    ],
    [351, 'Casa Mariol Ull de Llebre Crianza',
        'Tinto de la D.O. Terra Alta elaborado por Casa Mariol, bodega familiar de Batea fundada en 1944. Ull de llebre (tempranillo en catalán) de viñedos propios sobre suelos calizos, con crianza en barrica de roble. Expresión del terroir más occidental de Catalunya, de clima continental seco.',
        "Nariz: Aromas de fruta negra madura, ciruela, especias dulces y notas de tostado con un fondo de hierbas mediterráneas.\nBoca: Estructurado y redondo, con taninos maduros, buena acidez y sabores de mora, cereza, vainilla y un punto balsámico.\nFinal: Medio-largo, con recuerdos de fruta confitada, roble noble y un toque de garriga."
    ],
    [352, 'Casa Mariol Merlot Crianza',
        'Tinto de la D.O. Terra Alta elaborado por Casa Mariol con merlot de viñedos propios en Batea. La crianza en barricas de roble aporta complejidad a esta variedad bordelesa aclimatada al clima continental seco de la Terra Alta, produciendo un merlot de perfil mediterráneo con estructura y fruta.',
        "Nariz: Aromas de ciruela, mora, chocolate negro y especias dulces con un fondo de vainilla y tostados elegantes.\nBoca: Redondo y goloso, con taninos maduros y sedosos, cuerpo medio-alto y sabores de fruta negra, cacao y un punto de cedro.\nFinal: Largo y aterciopelado, con recuerdos de fruta madura y roble noble que perduran."
    ],
    [355, 'Casa Mariol Samsó Crianza',
        'Tinto de la D.O. Terra Alta elaborado con samsó (cariñena) por Casa Mariol en Batea. La samsó, variedad autóctona recuperada en esta zona, encuentra en los suelos calizos de la Terra Alta un carácter propio. La crianza en barrica aporta estructura y refinamiento a su vocación mediterránea.',
        "Nariz: Aromas de cereza madura, fruta roja confitada, especias y notas balsámicas con un fondo de hierbas aromáticas.\nBoca: Jugoso y especiado, con taninos presentes pero maduros, buena acidez y sabores de frutos rojos, regaliz y garriga.\nFinal: Medio-largo, con recuerdos de fruta y un toque mineral calizo persistente."
    ],
    [365, 'Vi de Gel Riesling',
        'Vino dulce tipo ice wine elaborado en la D.O. Penedès con riesling. Las uvas se vendimian congeladas para concentrar los azúcares y aromas antes del prensado. Este método, heredado de la tradición vinícola alemana y canadiense, produce un dulce natural de extraordinaria concentración aromática y equilibrio acidez-dulzor.',
        "Nariz: Aromas intensos de miel, albaricoque confitado, piña madura, mandarina y flores blancas con un fondo de hidrocarburos característico del riesling.\nBoca: Dulce, untuoso y vibrante, con sabores de fruta tropical, melocotón en almíbar y cítricos, equilibrado por una acidez viva.\nFinal: Muy largo y meloso, con recuerdos de fruta confitada y una frescura cítrica que evita empalagar."
    ],
    [366, 'Santa Digna Rosé',
        'Rosado de la gama Santa Digna de Miguel Torres Chile, elaborado con cabernet sauvignon de viñedos en el Valle Central chileno. Vinificado por sangrado breve para preservar color y fruta. Un rosado cuidadosamente elaborado con la filosofía de sostenibilidad que caracteriza a Torres en sus bodegas de los Andes.',
        "Nariz: Aromas de fresa madura, cereza, pétalos de rosa y un toque de pomelo rosado con notas de caramelo suave.\nBoca: Fresco y jugoso, con sabores de frutos rojos, cítricos y un punto especiado, cuerpo medio y buena acidez.\nFinal: Medio, afrutado y limpio, con un retrogusto de fruta roja y una frescura que invita al aperitivo."
    ],
    [396, 'Sumarroca Temps de Flors',
        'Blanco ecológico de Sumarroca en la D.O. Penedès, coupage de variedades aromáticas mediterráneas. Esta bodega familiar del Alt Penedès cultiva sus viñedos siguiendo prácticas ecológicas desde hace años. El nombre Temps de Flors evoca la primavera y se refleja en el perfil aromático floral del vino.',
        "Nariz: Aromas intensos y florales de azahar, rosa, jazmín y fruta blanca con notas cítricas frescas y un fondo de hierba recién cortada.\nBoca: Fresco y expresivo, con buena acidez, sabores de manzana, pera, flores y un carácter aromático marcado.\nFinal: Medio-largo y floral, con recuerdos de azahar y cítricos que lo hacen muy refrescante."
    ],
    [397, 'Sumarroca Riesling',
        'Blanco monovarietal de riesling elaborado por Sumarroca en la D.O. Penedès, uno de los pocos productores catalanes de esta variedad germánica. Viñedos plantados en zonas frescas del Alt Penedès donde esta uva encuentra el clima adecuado para expresar toda su finura aromática y acidez.',
        "Nariz: Aromas clásicos de riesling: lima, pomelo, manzana verde, flores blancas y un fondo mineral con notas petrolíferas incipientes.\nBoca: Fresco y vibrante, con acidez marcada, sabores de cítricos, fruta verde y una mineralidad que define la variedad.\nFinal: Largo y cítrico, con recuerdos de lima y una frescura eléctrica muy característica del riesling."
    ],
    [398, 'Sumarroca Gewürztraminer',
        'Blanco aromático monovarietal de gewürztraminer elaborado por Sumarroca en la D.O. Penedès. Esta variedad originaria de Alsacia encuentra en el Alt Penedès una expresión propia, más fresca que la alsaciana. Vinificación cuidadosa para preservar toda la intensidad aromática característica de esta uva tan expresiva.',
        "Nariz: Aromas explosivos de rosas, lichi, fruta de la pasión, mango y jengibre con un fondo especiado muy característico.\nBoca: Glicérico y untuoso, con sabores de fruta tropical, flores blancas y un toque exótico, acidez moderada.\nFinal: Medio-largo y aromático, con recuerdos de rosas, lichi y especias dulces que perduran."
    ],
    [399, 'Viña del Oja',
        'Tinto de la D.O.Ca. Rioja elaborado con tempranillo de viñedos de la subzona de Rioja Alta, área que da origen al nombre del vino (río Oja). Vinificación tradicional con una breve crianza en barrica que respeta la expresión frutal del tempranillo joven riojano, para disfrutar en su juventud.',
        "Nariz: Aromas de fruta roja fresca, cereza, frambuesa y un sutil toque de vainilla con un fondo especiado ligero.\nBoca: Jugoso y equilibrado, con taninos suaves, buena acidez y sabores de fruta roja, especias dulces y un punto de regaliz.\nFinal: Medio y afrutado, con recuerdos de cereza y un toque cálido de roble que cierra con elegancia."
    ],
    [429, 'Viñas del Vero Macabeo Chardonnay',
        'Blanco de la D.O. Somontano elaborado por Viñas del Vero, una de las bodegas de referencia del prepirineo aragonés. Coupage de macabeo (variedad mediterránea autóctona) y chardonnay que combina frescura y estructura. Vinificación en depósitos de acero inoxidable para preservar los aromas frutales.',
        "Nariz: Aromas de manzana, pera, melón y un toque de flores blancas con notas de fruta tropical del chardonnay.\nBoca: Fresco y equilibrado, con sabores de fruta blanca, cítricos y un sutil toque cremoso, buena acidez.\nFinal: Medio y frutal, con recuerdos de manzana y un retrogusto limpio y refrescante."
    ],
    [432, 'Marqués de Burgos',
        'Tinto de la D.O. Ribera del Duero elaborado con tinta del país (tempranillo) de viñedos seleccionados de la zona burgalesa. Una elaboración de perfil clásico ribereño, con crianza en barricas de roble que realza la expresión del tempranillo criado a gran altitud sobre suelos calizos de la meseta castellana.',
        "Nariz: Aromas de fruta negra madura, especias, tabaco y notas de vainilla con un fondo de cuero y tostados.\nBoca: Estructurado y potente, con taninos maduros, buena acidez y sabores de mora, cereza, cacao y especias.\nFinal: Largo y envolvente, con recuerdos de fruta confitada y roble que definen el estilo Ribera."
    ],
    [433, 'Raimat Clamor Tinto Roble',
        'Tinto de la D.O. Costers del Segre elaborado por Raimat, histórica finca familiar de Codorníu en las tierras leridanas. Coupage de variedades tintas con paso por barrica de roble durante algunos meses. Clamor es la gama más accesible de Raimat, manteniendo el estilo cuidado de esta casa centenaria.',
        "Nariz: Aromas de fruta roja, ciruela, vainilla y notas tostadas con un fondo ligero de especias dulces.\nBoca: Redondo y amable, con taninos suaves, sabores de fruta madura, vainilla y un punto de regaliz.\nFinal: Medio y agradable, con recuerdos de fruta y un sutil toque de roble que aporta complejidad."
    ],
    [482, 'Mim Giró Ribot',
        'Tinto de la D.O. Penedès elaborado por Giró Ribot, bodega familiar en Santa Fe del Penedès con más de 400 años de historia vinícola. Coupage mediterráneo con variedades tintas cuidadosamente seleccionadas de viñedos propios en el Alt Penedès, zona de altitud y clima fresco.',
        "Nariz: Aromas de fruta negra, mora, grosella y un toque floral de violetas con notas de hierbas mediterráneas.\nBoca: Fresco y jugoso, con taninos suaves, buena acidez y sabores de frutos negros, regaliz y garriga.\nFinal: Medio y elegante, con recuerdos de fruta y un toque mineral que refleja el terroir del Alt Penedès."
    ],
    [483, 'Finca 10 Joven',
        'Tinto joven de la D.O.Ca. Rioja elaborado con tempranillo de viñedos seleccionados. Vinificación moderna sin crianza en barrica o con paso muy breve, buscando la máxima expresión frutal del tempranillo riojano en su juventud. Un vino franco y directo pensado para beber pronto.',
        "Nariz: Aromas frescos de cereza, frambuesa, fresas y un toque de regaliz con notas florales.\nBoca: Jugoso y ligero, con taninos suaves, buena acidez y sabores de fruta roja fresca y un punto especiado.\nFinal: Corto y frutal, con recuerdos de cereza que lo hacen fácil de beber y versátil con la cocina diaria."
    ],
    [545, 'Hema Magnum',
        'Tinto de la D.O. Montsant elaborado con variedades mediterráneas cultivadas en suelos de pizarra y arcilla roja característicos de esta D.O. colindante con el Priorat. La presentación en magnum favorece una evolución más lenta y armónica del vino en botella, realzando su potencial de guarda.',
        "Nariz: Aromas intensos de fruta negra madura, mora, regaliz, especias y un fondo mineral con notas balsámicas.\nBoca: Potente y estructurado, con taninos firmes, gran concentración y sabores de frutos negros, cacao y hierbas aromáticas.\nFinal: Muy largo y mineral, con recuerdos de pizarra, fruta confitada y un calor mediterráneo."
    ],
    [560, 'Pata Negra Rioja',
        'Tinto de la D.O.Ca. Rioja de la gama Pata Negra, una de las marcas más reconocidas de Bodegas García Carrión. Elaborado con tempranillo riojano, con una crianza cuidada que aporta equilibrio entre fruta y madera. Un vino accesible que refleja el estilo tradicional rioja al alcance del consumidor cotidiano.',
        "Nariz: Aromas de fruta roja madura, ciruela, vainilla y especias dulces con un fondo de tostados ligeros.\nBoca: Redondo y suave, con taninos maduros, cuerpo medio y sabores de fruta confitada, especias y un toque de regaliz.\nFinal: Medio y cálido, con recuerdos de fruta y roble que lo hacen un riojas clásico y fiable."
    ],
    [578, 'Can Feixes',
        'Tinto de la D.O. Penedès elaborado por Huguet de Can Feixes, bodega histórica del Alt Penedès con viñedos en la finca familiar. Coupage mediterráneo con variedades autóctonas e internacionales cuidadosamente elegidas. Can Feixes practica una viticultura respetuosa con el entorno y elabora vinos con carácter propio.',
        "Nariz: Aromas de fruta roja y negra madura, especias, vainilla y un fondo de hierbas mediterráneas con notas de cedro.\nBoca: Estructurado y sabroso, con taninos maduros, buena acidez y sabores de frutos rojos, especias dulces y un toque balsámico.\nFinal: Medio-largo, con recuerdos de fruta, roble noble y la frescura de un Alt Penedès bien elaborado."
    ],

    // ===== CAVAS / ESPUMOSOS (8) =====
    [473, 'Cava Olivia Reserva',
        'Cava Reserva elaborado con el método tradicional con las variedades autóctonas macabeo, xarel·lo y parellada. Crianza mínima de 15 meses sobre lías en botella que aporta complejidad y una burbuja fina. Un cava Reserva de estilo clásico catalán, pensado para celebraciones y como aperitivo.',
        "Nariz: Aromas de manzana golden, pera madura, flores blancas y levadura fina con notas de almendra y un toque de brioche.\nBoca: Burbuja fina y cremosa, con sabores de fruta blanca, frutos secos y pan tostado, buena acidez.\nFinal: Medio-largo, con recuerdos de levadura y un retrogusto limpio de fruta y almendra."
    ],
    [587, 'Cava Parxet Semi-Sec',
        'Cava semi-seco de Parxet, bodega pionera del cava en la D.O. Alella elaborado siguiendo el método tradicional. Variedades locales con un licor de expedición más dulce para obtener un estilo semi-seco ideal para postres y momentos dulces. Parxet es una referencia histórica en los espumosos catalanes.',
        "Nariz: Aromas de fruta madura, melocotón, miel y flores blancas con notas de levadura y un fondo ligeramente cítrico.\nBoca: Burbuja fina y cremosa, dulzor elegante equilibrado por la acidez, sabores de fruta blanca, miel y un toque de almendra.\nFinal: Medio y meloso, con recuerdos de fruta confitada que lo hacen ideal para postres."
    ],
    [793, 'Cava Bertha Lounge',
        'Cava de la familia Cusiné con la marca Bertha, elaborado en la D.O. Cava con variedades tradicionales (macabeo, xarel·lo, parellada). La gama Lounge apuesta por un estilo fresco y contemporáneo pensado para el consumo como aperitivo o para maridar con cocina moderna.',
        "Nariz: Aromas frescos de manzana, pera, cítricos y flores blancas con un sutil fondo de levadura fina.\nBoca: Burbuja fina y ligera, fresco y equilibrado, con sabores de fruta blanca, lima y un toque de pan tostado.\nFinal: Medio y nítido, con recuerdos cítricos y una acidez refrescante que invita al siguiente sorbo."
    ],
    [794, 'Cava Mirgin Reserva',
        'Cava Reserva de Alta Alella elaborado con variedades ecológicas de viñedos propios en la Serralada de Marina, a pocos kilómetros del Mediterráneo. Crianza prolongada sobre lías en método tradicional que aporta complejidad. La cercanía al mar imprime una salinidad característica a los cavas de esta D.O. Alella.',
        "Nariz: Aromas complejos de fruta blanca madura, frutos secos, pan tostado y flores secas con notas minerales salinas.\nBoca: Burbuja finísima y cremosa, con sabores de manzana asada, almendra, brioche y una salinidad marina distintiva.\nFinal: Largo y mineral, con recuerdos de frutos secos, levadura y una frescura atlántica elegante."
    ],
    [796, 'Lambrusco Violetta de Giorgi Bianco',
        'Lambrusco blanco italiano de la casa De Giorgi, elaborado en Emilia-Romaña. Versión blanca del tradicional lambrusco, vinificada sin el color de los hollejos para obtener un espumoso ligero y afrutado. Un vino desenfadado y festivo ideal para aperitivos informales y momentos compartidos.',
        "Nariz: Aromas de manzana verde, pera, cítricos y flores blancas con un sutil toque floral.\nBoca: Ligero y efervescente, dulzor moderado equilibrado por la frescura, sabores de fruta blanca y un carácter fresco y alegre.\nFinal: Corto y agradable, con recuerdos de manzana y un retrogusto limpio que invita al aperitivo."
    ],
    [797, 'Lambrusco Violetta de Giorgi Rosso',
        'Lambrusco tinto italiano clásico de la casa De Giorgi en Emilia-Romaña, cuna histórica de esta variedad. Vino espumoso ligero y afrutado elaborado con uvas lambrusco, ofreciendo ese carácter festivo y refrescante que ha convertido al lambrusco en un icono del aperitivo italiano informal.',
        "Nariz: Aromas de cereza, frambuesa, violetas y un toque de caramelo con notas frutales frescas.\nBoca: Ligero y efervescente, con dulzor moderado, sabores de frutos rojos y una acidez refrescante muy agradable.\nFinal: Corto y frutal, con recuerdos de cereza y frambuesa que lo convierten en un aperitivo festivo."
    ],
    [1164, 'Cava Torelló Brut Reserva',
        'Cava Brut Reserva de Torelló, bodega familiar con más de 600 años de historia en Sant Sadurní d\'Anoia. Elaborado con las variedades tradicionales macabeo, xarel·lo y parellada de viñedos propios en la finca Can Martí. Crianza prolongada sobre lías que aporta complejidad y una burbuja finísima.',
        "Nariz: Aromas elegantes de manzana asada, pera madura, frutos secos y pan tostado con notas de flores blancas y levadura fina.\nBoca: Burbuja cremosa y finísima, con sabores de fruta blanca, brioche, almendra y una acidez equilibrada.\nFinal: Largo y complejo, con recuerdos de frutos secos, pan tostado y un retrogusto de fruta confitada."
    ],
    [1260, 'Cava Castell de Perelada Brut Reserva',
        'Cava Brut Reserva de Castell de Perelada, elaborado en el Empordà con variedades tradicionales del Penedès. La bodega, ubicada en el histórico castillo medieval de Perelada en Girona, combina tradición y modernidad. Crianza sobre lías que aporta complejidad a este cava de estilo elegante.',
        "Nariz: Aromas de fruta blanca, manzana, pera, levadura y un fondo de almendra con notas florales.\nBoca: Burbuja fina y persistente, fresco y estructurado, con sabores de fruta madura, pan tostado y un toque de cítricos.\nFinal: Medio-largo, con recuerdos de fruta blanca y una mineralidad elegante que refleja el terroir ampurdanés."
    ],

    // ===== LICORES / DESTILADOS (19) =====
    [114, 'Triple Sec Larios',
        'Licor de naranja de la casa Larios, destilería malagueña con más de 150 años de historia. Triple Sec es un licor cristalino elaborado mediante la maceración y destilación de pieles de naranjas amargas y dulces. Ingrediente imprescindible en cócteles clásicos como el Margarita o el Cosmopolitan.',
        "Nariz: Aromas intensos de naranja fresca, piel de cítricos y un toque floral de azahar.\nBoca: Dulce y cítrico, con sabores limpios de naranja amarga y dulce, textura ligera y buena intensidad.\nFinal: Medio y cítrico, con un retrogusto refrescante de naranja que lo hace versátil en coctelería."
    ],
    [123, 'Orujo Casero Galicia Blanco 70cl',
        'Aguardiente gallego de orujo blanco elaborado de forma artesanal destilando los hollejos (bagazo) sobrantes de la vinificación del albariño y otras variedades gallegas. Destilación lenta en alambique tradicional que preserva el carácter auténtico del orujo, bebida ancestral de las sobremesas gallegas.',
        "Nariz: Aromas potentes de uva fresca, flores blancas, fruta de hueso y un toque herbal con notas cálidas alcohólicas.\nBoca: Fuerte y limpio, con sabores de uva, pera y cítricos, con el característico calor del aguardiente bien destilado.\nFinal: Largo y cálido, con recuerdos de fruta fresca y el toque pétreo de un orujo artesanal."
    ],
    [124, 'Orujo Casero Galicia Hierbas 70cl',
        'Licor gallego tradicional elaborado macerando hierbas autóctonas en aguardiente de orujo. La receta incluye hierbas como manzanilla, tomillo, menta, regaliz, hinojo y otras plantas aromáticas de la tierra gallega. El color verde-amarillento y el aroma herbáceo son su sello distintivo, ideal como digestivo.',
        "Nariz: Aromas intensos de hierbas aromáticas, menta, regaliz, hinojo y anís con un fondo de aguardiente.\nBoca: Dulce y herbáceo, con sabores complejos de hierbas, un toque anisado y la calidez del orujo, textura sedosa.\nFinal: Largo y refrescante, con recuerdos de hierbas frescas y un calor digestivo agradable."
    ],
    [329, 'Whisky Glend Winner',
        'Blended scotch whisky escocés de perfil accesible, resultado del ensamblaje de whiskies de malta y grano envejecidos en barricas de roble. Un whisky pensado para el consumo cotidiano, que mantiene el estilo clásico escocés sin complicaciones, ideal para coctelería o para tomar con hielo y soda.',
        "Nariz: Aromas de malta, cereales y un sutil toque de roble con notas de vainilla y fruta seca.\nBoca: Ligero y suave, con sabores de malta tostada, caramelo y un punto de especias, textura fluida.\nFinal: Corto y limpio, con recuerdos de cereales y un toque dulzón que facilita su mezcla en cócteles."
    ],
    [376, 'Whisky Cutty Sark 1L',
        'Blended scotch clásico creado en 1923 por la casa Berry Bros & Rudd. Nombrado en honor al legendario clíper Cutty Sark, es conocido por su botella de cristal verde y su etiqueta amarilla inconfundibles. Ensamblaje ligero y afrutado pensado originalmente para el mercado americano, fácil de beber y versátil.',
        "Nariz: Aromas de manzana verde, pera, vainilla y cereales con un sutil toque cítrico y floral.\nBoca: Ligero y suave, con sabores de fruta fresca, malta, vainilla y un punto de miel, textura agradable.\nFinal: Medio y limpio, con recuerdos de fruta y vainilla que lo hacen un blend muy amable."
    ],
    [430, 'Valdespino Fino Seco',
        'Fino de la D.O. Jerez elaborado por Bodegas Valdespino, una de las casas históricas más antiguas de Jerez con orígenes en el siglo XIII. Crianza biológica bajo velo de flor en botas de roble americano siguiendo el sistema de criaderas y solera. Un fino seco de estilo clásico, pensado para el aperitivo.',
        "Nariz: Aromas punzantes de almendra amarga, levadura, manzanilla y un fondo salino característico del velo de flor.\nBoca: Seco y punzante, con sabores de almendra, masa de pan, cítricos y una salinidad marcada.\nFinal: Medio y seco, con un retrogusto almendrado y yodado muy característico del fino jerezano."
    ],
    [462, 'Orujo Casero Galicia Blanco 3L',
        'Formato grande (3 litros) del orujo gallego casero blanco, elaborado de forma artesanal a partir del bagazo de la vinificación. Destilación tradicional en alambique que preserva toda la expresión de la uva gallega. Presentación ideal para restauración o para rellenar botellas más pequeñas en casa.',
        "Nariz: Aromas de uva fresca, frutas blancas y un toque herbal con el característico calor del aguardiente.\nBoca: Potente y limpio, con sabores de fruta y un punto pétreo que identifica al orujo artesanal gallego.\nFinal: Largo y cálido, con recuerdos de uva y una calidez que se disfruta como digestivo."
    ],
    [487, 'Ron Pujol 1L',
        'Ron elaborado por la destilería catalana Pujol, con una tradición ronera sorprendentemente asentada en Catalunya desde mediados del siglo XX. Un ron de estilo clásico con envejecimiento en barrica de roble que aporta color y redondez, pensado como ron de consumo cotidiano y para mezclar en cócteles.',
        "Nariz: Aromas de caña de azúcar, vainilla, caramelo y un toque de fruta tropical con notas de roble.\nBoca: Suave y dulce, con sabores de melaza, vainilla, coco y un punto especiado, textura ligera.\nFinal: Medio y limpio, con recuerdos de caramelo y madera que lo hacen versátil en coctelería."
    ],
    [631, 'Vermú Blanco Espinaler',
        'Vermut blanco elaborado por Espinaler, casa histórica de Vilassar de Mar fundada en 1896 y famosa por sus aperitivos tradicionales catalanes. Maceración de hierbas y especias en vino blanco seco siguiendo una receta propia. Un vermut blanco con marcada identidad catalana, ideal como aperitivo con salazones.',
        "Nariz: Aromas de hierbas amargas, manzanilla, cítricos y un fondo de flores blancas con notas especiadas.\nBoca: Fresco y herbáceo, con amargor equilibrado por un dulzor discreto, sabores de hierbas, cítricos y un toque salino.\nFinal: Medio y aromático, con recuerdos de hierbas amargas y cítricos que limpian el paladar."
    ],
    [689, 'Whisky Peche Yachting',
        'Blended scotch whisky de perfil ligero y accesible, resultado del ensamblaje de whiskies de malta y grano escoceses. Un whisky de consumo cotidiano, sin pretensiones de premium, pensado para disfrutarse solo, con hielo o como base de combinados clásicos. Ofrece el estilo escocés clásico a buen precio.',
        "Nariz: Aromas de malta, cereales tostados y un sutil fondo de vainilla con notas de caramelo suave.\nBoca: Ligero y suave, con sabores de cereales, caramelo, vainilla y un toque de madera, textura fluida.\nFinal: Corto y limpio, con recuerdos de malta y un dulzor discreto que lo hace fácil de beber."
    ],
    [727, 'Sangría Lolea White Frizzante',
        'Sangría blanca espumosa elaborada por Lolea, la marca española que ha modernizado el concepto tradicional de la sangría. Elaborada con vino blanco, frutas y un suave burbujeo natural. Su botella de lunares es icónica. Una sangría contemporánea refrescante, pensada para momentos festivos.',
        "Nariz: Aromas de melocotón, cítricos, flores blancas y un toque herbal con notas frutales frescas.\nBoca: Ligera y efervescente, con dulzor moderado, sabores de fruta blanca, cítricos y un carácter fresco.\nFinal: Corto y refrescante, con recuerdos de fruta que lo hacen ideal como aperitivo veraniego."
    ],
    [728, 'Sangría Lolea Tinta',
        'Sangría tinta de Lolea, la marca española que elabora sangrías premium con vinos de calidad y frutas naturales. Elaborada con vino tinto, especias y cítricos, siguiendo una receta cuidada que se aleja de las sangrías industriales. Su botella de lunares roja es reconocible internacionalmente.',
        "Nariz: Aromas de frutos rojos, naranja, canela y especias con un fondo de fruta macerada.\nBoca: Afrutada y especiada, con sabores de cereza, naranja, canela y un dulzor equilibrado, textura agradable.\nFinal: Medio y frutal, con recuerdos de fruta y especias que la hacen perfecta para el aperitivo festivo."
    ],
    [741, 'Orujo Afilador Hierbas 70cl',
        'Orujo de hierbas gallego elaborado por la casa Afilador, marca tradicional dedicada a los destilados gallegos de calidad. Maceración de hierbas aromáticas autóctonas (menta, manzanilla, hinojo, regaliz, tomillo) en aguardiente de orujo, obteniendo un licor digestivo de color dorado y perfil herbáceo complejo.',
        "Nariz: Aromas intensos de hierbas frescas, menta, hinojo, regaliz y anís con un fondo cálido de aguardiente.\nBoca: Dulce y herbáceo, con sabores complejos de hierbas aromáticas, un toque anisado y la fuerza del orujo.\nFinal: Largo y refrescante, con recuerdos de hierbas y un calor digestivo agradable."
    ],
    [834, 'Vodka Sildka Caramel',
        'Vodka saborizado a caramelo elaborado por la casa Sildka. Destilado neutro aromatizado con esencia natural de caramelo tostado, que aporta dulzor y un perfil goloso. Ideal para cócteles dulces, chupitos festivos o mezclado con refrescos cola y café, una opción versátil para momentos informales.',
        "Nariz: Aromas intensos de caramelo tostado, toffee y vainilla con un fondo de azúcar quemado.\nBoca: Dulce y aterciopelado, con sabores de caramelo, toffee y un toque amantequillado, textura sedosa.\nFinal: Medio y meloso, con recuerdos de caramelo que persisten agradablemente en boca."
    ],
    [835, 'Vodka Sildka Chocolate',
        'Vodka saborizado a chocolate elaborado por la casa Sildka. Destilado neutro aromatizado con cacao y chocolate negro que aporta un perfil goloso y envolvente. Pensado para coctelería dulce, chupitos festivos o para mezclar en creaciones modernas donde el chocolate aporta profundidad al destilado.',
        "Nariz: Aromas intensos de chocolate negro, cacao, vainilla y un toque dulce con notas tostadas.\nBoca: Dulce y cremoso, con sabores de chocolate, cacao amargo y un punto de vainilla, textura sedosa.\nFinal: Medio y goloso, con recuerdos de chocolate que perduran como un bombón líquido."
    ],
    [836, 'Vodka Sildka Chocolate',
        'Vodka saborizado a chocolate de Sildka en presentación alternativa. Destilado neutro aromatizado con cacao y chocolate, versión popular en chupitos festivos y coctelería dulce. Su perfil goloso lo convierte en un licor de consumo informal más que en un destilado de degustación tradicional.',
        "Nariz: Aromas de chocolate negro, cacao tostado, vainilla y un toque dulzón.\nBoca: Dulce y envolvente, con sabores de chocolate y cacao, textura cremosa y sedosa.\nFinal: Medio y goloso, con un retrogusto de chocolate que perdura como postre líquido."
    ],
    [888, 'Moscatell Sardà',
        'Vino dulce de moscatel elaborado en Catalunya con uvas moscatel cuidadosamente seleccionadas. Sardà es un clásico del repertorio de vinos dulces catalanes, con el perfil aromático explosivo y el dulzor equilibrado característicos del moscatel mediterráneo. Ideal como vino de postre o para maridar con quesos.',
        "Nariz: Aromas explosivos de uva moscatel, flores de azahar, miel y un toque de fruta confitada con notas cítricas.\nBoca: Dulce y aromático, con sabores de uva fresca, miel, cítricos y un equilibrio acidez-dulzor agradable.\nFinal: Largo y meloso, con recuerdos de uva y flores que lo hacen perfecto para postres."
    ],
    [984, 'Vodka Smirnoff 70cl',
        'Vodka clásico de la casa Smirnoff, marca fundada en Moscú en 1864 y hoy una de las más vendidas del mundo. Destilado tres veces y filtrado siete veces a través de carbón activo, siguiendo un proceso que garantiza su pureza característica. Un vodka suave y limpio, pilar de la coctelería internacional.',
        "Nariz: Aromas limpios y neutros, con sutiles notas de cereales y un fondo mineral muy discreto.\nBoca: Suave y sedoso, con textura limpia, sabores muy sutiles de cereal y un punto dulzón, sin aristas.\nFinal: Corto y limpio, con un retrogusto neutro que lo hace perfecto para coctelería clásica."
    ],
    [1001, 'Grappa Li Barna',
        'Grappa italiana elaborada por la casa Li Barna mediante la destilación del bagazo (orujo) sobrante de la vinificación. La grappa, aguardiente tradicional del norte de Italia, es un destilado con carácter propio que en esta versión ofrece un perfil frutal y limpio, ideal como digestivo después de las comidas.',
        "Nariz: Aromas de uva fresca, fruta de hueso, flores y un toque herbal con la intensidad del destilado.\nBoca: Potente y limpio, con sabores de uva, fruta blanca y una calidez cristalina característica de la grappa.\nFinal: Largo y cálido, con recuerdos de fruta fresca y un toque floral persistente."
    ],

    // ===== CERVEZAS (5) =====
    [535, 'Cerveza Brutus Malta 33cl',
        'Cerveza de malta holandesa de la casa Brutus, elaborada con un perfil maltoso marcado que destaca los aromas y sabores de los cereales tostados. Un estilo cervecero continental clásico con una graduación moderada y un carácter maltoso equilibrado, pensada como cerveza de consumo diario y fácil de beber.',
        "Nariz: Aromas de malta tostada, pan, caramelo y un sutil fondo de lúpulo herbal.\nBoca: Redonda y maltosa, con sabores de cereales tostados, caramelo y un amargor lupulado discreto.\nFinal: Medio y limpio, con recuerdos de malta y un retrogusto que invita al siguiente trago."
    ],
    [536, 'Cervesa Simone La Calavera',
        'Cerveza artesanal catalana de la cervecera La Calavera, con sede en Solsona y elaboraciones que combinan tradición cervecera europea con experimentación moderna. Simone es una cerveza de autor con carácter propio, resultado del movimiento craft catalán que ha revitalizado la escena cervecera local en los últimos años.',
        "Nariz: Aromas complejos de malta, lúpulos aromáticos, frutas y un toque especiado que invita al descubrimiento.\nBoca: Estructurada y sabrosa, con sabores de malta, fruta, un amargor lupulado bien integrado y matices complejos.\nFinal: Largo y complejo, con recuerdos de malta y lúpulo que reflejan el cuidado de una cerveza artesanal.",
    ],
    [641, 'Cerveza Estrella Damm 1/3 Retornable',
        'Cerveza mediterránea elaborada por Estrella Damm en Barcelona desde 1876, utilizando cebada, arroz y lúpulo siguiendo la receta original. Formato 1/3 retornable que apuesta por la sostenibilidad mediante el retorno y reutilización del envase de vidrio. La cerveza más emblemática de Catalunya y uno de los iconos de la dieta mediterránea.',
        "Nariz: Aromas suaves de malta, cereales, un toque herbáceo de lúpulo y sutiles notas florales.\nBoca: Ligera y refrescante, con sabores equilibrados de malta, cereal y un amargor lupulado muy agradable.\nFinal: Medio y limpio, con un retrogusto fresco que la convierte en el acompañamiento perfecto de las tapas."
    ],
    [811, 'Cerveza Guineu Montcogul',
        'Cerveza artesanal catalana de la cervecería Guineu, ubicada en Valls de Torroella (Bages) y una de las pioneras del movimiento craft en Catalunya. Montcogul es una cerveza de carácter propio con el estilo distintivo de Guineu, que combina técnicas modernas con materias primas de calidad.',
        "Nariz: Aromas de malta, lúpulos aromáticos, frutas y un toque de levadura con notas complejas.\nBoca: Estructurada y sabrosa, con sabores de malta tostada, fruta, un amargor equilibrado y una carbonatación agradable.\nFinal: Medio-largo, con recuerdos de lúpulo y malta que reflejan la calidad artesanal de Guineu."
    ],
    [1365, 'Cerveza Free Damm Sin Alcohol 33cl',
        'Cerveza sin alcohol elaborada por Damm en Barcelona, ofreciendo el sabor mediterráneo característico de la casa con menos del 1% de alcohol. Formato retornable que apuesta por la sostenibilidad. Elaborada siguiendo la tradición cervecera de Damm pero con un proceso que preserva el perfil aromático sin el contenido alcohólico.',
        "Nariz: Aromas suaves de malta, cereales y un toque herbal de lúpulo con notas frescas.\nBoca: Ligera y refrescante, con sabores de malta, cereal y un amargor lupulado discreto, muy bebible.\nFinal: Corto y limpio, con recuerdos de cereales que la hacen una buena alternativa sin alcohol."
    ],
];

$ok = 0;
$errors_list = [];

foreach ($textos as $t) {
    list($id, $nombre, $desc, $nota) = $t;

    $descEsc = mysqli_real_escape_string($bd, $desc);
    $sql = "INSERT INTO producto_textos (idProducto, tipo, contenido)
            VALUES ($id, 'descripcion', '$descEsc')
            ON DUPLICATE KEY UPDATE contenido = '$descEsc'";
    if (!mysqli_query($bd, $sql)) {
        $errors_list[] = "ID $id desc: " . mysqli_error($bd);
    }

    $notaEsc = mysqli_real_escape_string($bd, $nota);
    $sql = "INSERT INTO producto_textos (idProducto, tipo, contenido)
            VALUES ($id, 'nota_cata', '$notaEsc')
            ON DUPLICATE KEY UPDATE contenido = '$notaEsc'";
    if (!mysqli_query($bd, $sql)) {
        $errors_list[] = "ID $id nota: " . mysqli_error($bd);
    }

    $ok++;
    echo "OK: $id - $nombre\n";
}

echo "\n=== Done: $ok products with texts ===\n";
if ($errors_list) {
    echo "Errors:\n";
    foreach ($errors_list as $e) echo "  - $e\n";
}
