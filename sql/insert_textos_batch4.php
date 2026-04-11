<?php
/**
 * Batch 4: Insert texts for 50 products
 * Original descriptions and tasting notes
 */
require dirname(__DIR__) . '/distribucio/connexio/connexio.php';
mysqli_query($bd, "SET SESSION sql_mode=''");
mysqli_set_charset($bd, 'utf8mb4');

$textos = [
    // ===== WINES (20) =====
    [204, 'Protos Verdejo',
        'Verdejo de referencia elaborado por Bodegas Protos en la D.O. Rueda. Procedente de viñedos seleccionados en suelos pedregosos de Valladolid, este blanco captura la esencia más fresca y aromática de la variedad verdejo. Vinificado con control de temperatura para preservar toda su expresión frutal.',
        "Nariz: Intensos aromas de hierba fresca, pomelo y fruta de hueso, con un fondo de hinojo y notas minerales.\nBoca: Entrada fresca y vibrante, con buena acidez que enmarca sabores de manzana verde, lima y un toque amargo elegante.\nFinal: Largo y refrescante, con recuerdos cítricos y un agradable amargor que invita a otro sorbo."
    ],
    [217, 'Castell del Remei Gotim Bru',
        'Tinto joven de Castell del Remei, una de las bodegas con más historia de la D.O. Costers del Segre. Elaborado con tempranillo, garnacha, cabernet sauvignon y merlot, este vino refleja el carácter continental de la zona de Lleida. Crianza breve en barrica que aporta estructura sin ocultar la fruta.',
        "Nariz: Aromas de frutas rojas maduras como cereza y frambuesa, acompañados de notas especiadas suaves y un toque de regaliz.\nBoca: Entrada jugosa con taninos sedosos, buena acidez y sabores de frutos del bosque con matices de pimienta negra.\nFinal: Medio, frutal y especiado, con un recuerdo agradable de fruta madura."
    ],
    [218, 'Raimat Clos Abadia',
        'Tinto de guarda de la bodega Raimat, en pleno corazón de Costers del Segre. Coupage de cabernet sauvignon, tempranillo y merlot procedentes de viñedos propios cultivados en régimen de agricultura sostenible. Envejecido en barricas de roble francés y americano durante 12 meses, ofrece complejidad y elegancia.',
        "Nariz: Complejo bouquet de fruta negra madura, cedro, vainilla y notas tostadas con un fondo de tabaco dulce.\nBoca: Estructurado y carnoso, con taninos firmes pero pulidos, sabores de mora, ciruela pasa y especias nobles.\nFinal: Largo y persistente, con recuerdos de cacao, vainilla y fruta confitada."
    ],
    [219, 'Roura Xarel·lo',
        'Blanco monovarietal de xarel·lo elaborado por la bodega Roura en la D.O. Alella, la denominación de origen más pequeña de Catalunya. Los viñedos situados en terrazas con suelo de sauló (granito descompuesto) confieren al vino una marcada mineralidad mediterránea.',
        "Nariz: Aromas frescos de manzana golden, pera y flores blancas, con un elegante fondo mineral y notas de almendra.\nBoca: Sedoso y equilibrado, con acidez bien integrada y sabores de fruta blanca, cítricos y un toque salino muy característico.\nFinal: Persistente y mineral, con un agradable recuerdo de almendras tostadas y piel de limón."
    ],
    [284, 'Parvus Chardonnay',
        'Chardonnay de altura elaborado en la D.O. Alella por Alta Alella. Procedente de viñedos ecológicos situados en la vertiente marítima del Parc de la Serralada de Marina, este blanco combina la cremosidad del chardonnay con la frescura de la brisa mediterránea. Fermentado parcialmente en barrica de roble francés.',
        "Nariz: Elegantes aromas de melocotón, piña madura y mantequilla fresca, con notas de vainilla y un toque floral de acacia.\nBoca: Untuoso y envolvente, con buena acidez que equilibra la cremosidad. Sabores de fruta tropical, avellana y un toque ahumado.\nFinal: Largo y persistente, con notas tostadas y un recuerdo frutal que permanece."
    ],
    [346, 'Heus Negre',
        'Tinto de la D.O. Empordà elaborado por La Vinyeta, una bodega familiar comprometida con la viticultura orgánica. Coupage de garnacha y cariñena cultivadas en suelos de pizarra y esquisto, azotadas por la tramontana, que aporta concentración natural a las uvas.',
        "Nariz: Intensos aromas de cereza negra, mora silvestre y violetas, con matices de romero, tomillo y un fondo mineral de pizarra.\nBoca: Fresco y mediterráneo, con taninos suaves y sabores de frutas rojas, hierbas aromáticas y un punto especiado.\nFinal: Medio-largo, con una agradable sensación mineral y recuerdos de garriga mediterránea."
    ],
    [387, 'Parvus Syrah',
        'Tinto monovarietal de syrah de Alta Alella, elaborado con uvas de viñedos ecológicos en la D.O. Alella. Las viñas crecen cerca del mar Mediterráneo, dando lugar a un syrah expresivo y aromático. Crianza en barricas de roble francés durante varios meses que redondean su carácter.',
        "Nariz: Exuberantes aromas de fruta negra, pimienta negra, violetas y notas de chocolate amargo con un fondo ahumado.\nBoca: Potente pero elegante, con taninos maduros, buena acidez y sabores de ciruela, mora y especias orientales.\nFinal: Largo y especiado, con recuerdos de pimienta y fruta negra que persisten en el paladar."
    ],
    [388, 'Sedó Barceló',
        'Tinto de la D.O. Priorat, una de las zonas vinícolas más prestigiosas de España. Elaborado con garnacha y cariñena de viñas viejas cultivadas en las características terrazas de llicorella (pizarra). Un vino que refleja la intensidad y el carácter mineral único de esta comarca tarraconense.',
        "Nariz: Concentrados aromas de fruta negra madura, grafito, pizarra mojada y notas balsámicas de romero y eucalipto.\nBoca: Denso y mineral, con taninos firmes y estructurados, sabores de cereza negra, regaliz y notas terrosas complejas.\nFinal: Muy largo y persistente, con un marcado carácter mineral y recuerdos de fruta confitada y especias."
    ],
    [529, 'Pago de los Capellanes Roble',
        'Tinto joven con crianza en roble de Bodegas Pago de los Capellanes, situada en Pedrosa de Duero, corazón de la D.O. Ribera del Duero. Elaborado íntegramente con tempranillo de viñedos propios y envejecido durante 4 meses en barricas de roble francés, buscando el equilibrio entre fruta y madera.',
        "Nariz: Aromas limpios de fruta roja y negra, con notas de vainilla, coco y un toque de regaliz procedente de la barrica.\nBoca: Jugoso y goloso, con taninos suaves y redondos, sabores de cereza madura, mora y un punto tostado elegante.\nFinal: Medio-largo, frutal y ligeramente especiado, con un retrogusto limpio y agradable."
    ],
    [574, 'Viña Tondonia Reserva',
        'Reserva icónico de R. López de Heredia, una de las bodegas más emblemáticas de la D.O. Rioja. Viña Tondonia encarna el estilo clásico riojano con largas crianzas en barrica de roble americano y posterior afinamiento en botella. Elaborado con tempranillo, garnacha, graciano y mazuelo de viñedos centenarios.',
        "Nariz: Complejo y evolutivo, con aromas de fruta roja madura, cuero, tabaco, especias dulces y un elegante fondo oxidativo de frutos secos.\nBoca: Sedoso y aterciopelado, con taninos perfectamente integrados, acidez vibrante y sabores de cereza confitada, especias y notas balsámicas.\nFinal: Extraordinariamente largo, con recuerdos de piel de naranja, tabaco y una mineralidad sutil que lo hace inolvidable."
    ],
    [575, 'Matsu El Recio',
        'Tinto de la D.O. Toro elaborado por Matsu con tinta de toro (tempranillo) de viñas de más de 60 años cultivadas en secano sobre suelos arenosos. Filosofía de mínima intervención en bodega, fermentación con levaduras autóctonas y crianza en barrica de roble francés. La etiqueta, obra de un fotógrafo español, rinde homenaje a los viticultores mayores.',
        "Nariz: Intensos aromas de fruta negra muy madura, notas de chocolate negro, pimienta y un toque de tierra húmeda.\nBoca: Potente y carnoso, con taninos robustos pero maduros, buena frescura y sabores de mora, ciruela y cacao.\nFinal: Largo y cálido, con recuerdos de fruta confitada, especias y notas torrefactas."
    ],
    [607, 'Venta las Vacas',
        'Tinto de la D.O. Ribera del Duero elaborado por Vizcarra en Roa de Duero. Tempranillo de viñedos viejos con crianza de 14 meses en barricas de roble francés. Un vino que combina la potencia de la Ribera con una elegancia y frescura poco habituales, reflejo del terroir de Roa.',
        "Nariz: Aromas profundos de grosella negra, ciruela madura, cedro y vainilla, con matices de violetas y grafito.\nBoca: Concentrado pero equilibrado, con taninos firmes y sedosos, buena acidez y sabores de fruta negra, especias y notas minerales.\nFinal: Largo y complejo, con un retrogusto elegante de fruta madura, tostados nobles y un punto balsámico."
    ],
    [672, 'Finca Resalso',
        'Tinto joven de Emilio Moro, elaborado en la D.O. Ribera del Duero con tempranillo de viñedos jóvenes de la finca familiar. Crianza de 3 meses en barricas de roble francés y americano que complementa su carácter frutal. Ideal como introducción al estilo Emilio Moro.',
        "Nariz: Aromas frescos y directos de cereza, frambuesa y un toque floral, con sutiles notas de vainilla y regaliz.\nBoca: Ligero y frutal, con taninos suaves y buena acidez, sabores de frutos rojos y un punto goloso.\nFinal: Medio, limpio y agradable, con recuerdos de fruta fresca y un toque especiado."
    ],
    [854, 'Vivanco Reserva',
        'Reserva de Dinastía Vivanco, bodega con museo del vino en Briones, D.O. Rioja. Elaborado con tempranillo, graciano y garnacha de viñedos seleccionados, con 24 meses de crianza en barricas de roble francés y americano. Un reserva clásico que equilibra tradición e innovación.',
        "Nariz: Aromas complejos de fruta roja madura, cuero fino, vainilla, clavo y un elegante fondo de cacao.\nBoca: Estructurado y elegante, con taninos finos y bien integrados, buena acidez y sabores de cereza confitada, especias dulces y notas tostadas.\nFinal: Largo y armonioso, con recuerdos de tabaco, vainilla y fruta madura que perduran."
    ],
    [855, 'Vivanco Crianza',
        'Crianza de Dinastía Vivanco procedente de viñedos en Briones, D.O. Rioja. Tempranillo como variedad principal con 12 meses en barricas de roble americano y francés. Un tinto versátil que muestra la expresión más accesible del tempranillo riojano.',
        "Nariz: Aromas de cereza madura, fresa y notas de vainilla con un fondo de especias dulces y cuero.\nBoca: Redondo y equilibrado, con taninos suaves, acidez fresca y sabores de fruta roja, regaliz y un toque de coco.\nFinal: Medio y agradable, con un retrogusto frutal y ligeramente especiado."
    ],
    [1103, 'Viña Ardanza Reserva',
        'Reserva emblemático de La Rioja Alta S.A., una de las bodegas históricas de Haro. Coupage de tempranillo y garnacha con 36 meses de crianza en barricas de roble americano. Viña Ardanza es uno de los reservas más reconocidos de España, paradigma del estilo clásico riojano.',
        "Nariz: Bouquet evolucionado de fruta roja confitada, especias dulces, cuero noble, café y notas balsámicas de incienso.\nBoca: Aterciopelado y elegante, con taninos perfectamente fundidos, gran equilibrio y sabores de cereza madura, tabaco, vainilla y un toque mineral.\nFinal: Muy largo y memorable, con recuerdos de especias, frutos secos y una sutil nota oxidativa que le aporta complejidad."
    ],
    [1104, 'Pago de Carraovejas Crianza',
        'Tinto de culto de la D.O. Ribera del Duero, elaborado en Peñafiel con tempranillo, cabernet sauvignon y merlot de viñedos propios. Crianza de 12 meses en barricas de roble francés. Pago de Carraovejas se ha convertido en una de las referencias más deseadas de la Ribera.',
        "Nariz: Elegante y complejo, con aromas de grosella, mora, cedro, especias finas y un sutil toque mineral.\nBoca: Sedoso y profundo, con taninos de grano fino, acidez equilibrada y capas de sabor que incluyen fruta negra, cacao, café y notas herbáceas nobles.\nFinal: Muy largo y envolvente, con un retrogusto que combina fruta, especias y mineralidad de forma armoniosa."
    ],
    [872, 'Gran Viña Sol',
        'Blanco premium de Bodegas Torres, referencia histórica de la D.O. Penedès. Elaborado con chardonnay y parellada de viñedos de altura, con fermentación parcial en barricas de roble. Un clásico catalán que demostró el potencial del chardonnay en el Mediterráneo.',
        "Nariz: Aromas de manzana golden, melocotón blanco y flores de azahar, con elegantes notas de mantequilla y tostados de la barrica.\nBoca: Amplio y cremoso, con buena frescura, sabores de fruta de hueso, avellana y un punto de miel.\nFinal: Largo y elegante, con recuerdos de fruta madura y notas tostadas que aportan complejidad."
    ],
    [1251, 'Viña Esmeralda Torres',
        'Blanco aromático de Bodegas Torres elaborado con moscatel de Alejandría y gewürztraminer en la D.O. Penedès. Un vino que captura la esencia de las variedades aromáticas mediterráneas, fresco y perfumado, ideal para disfrutar en su juventud.',
        "Nariz: Explosión de aromas florales como rosa y jazmín, con notas de lichi, melocotón y uva moscatel fresca.\nBoca: Ligero y delicado, con dulzor apenas perceptible, buena acidez y sabores de frutas tropicales y pétalos de rosa.\nFinal: Medio y perfumado, con un agradable recuerdo floral y afrutado que se desvanece suavemente."
    ],
    [1265, 'Azpilicueta Crianza',
        'Crianza de Bodegas Azpilicueta en la D.O. Rioja, elaborado con tempranillo, garnacha y mazuelo. Envejecido 14 meses en barricas de roble americano y francés, este tinto ofrece un estilo clásico riojano con personalidad propia y gran relación calidad-precio.',
        "Nariz: Aromas de cereza madura, fresas, vainilla y notas especiadas de clavo y canela.\nBoca: Suave y equilibrado, con taninos finos, acidez integrada y sabores de fruta roja, coco y especias dulces.\nFinal: Medio-largo y armonioso, con un retrogusto limpio de vainilla y fruta madura."
    ],

    // ===== CAVAS / ESPUMOSOS (10) =====
    [99, 'Amanima Brut Nature Reserva',
        'Cava Brut Nature Reserva elaborado en el corazón del Penedès con las variedades autóctonas macabeo, xarel·lo y parellada. Larga crianza sobre lías que aporta cremosidad y complejidad al vino espumoso. Método tradicional con segunda fermentación en botella.',
        "Nariz: Aromas de manzana verde, cítricos y flores blancas, con elegantes notas de levadura fina y pan tostado.\nBoca: Burbuja fina y persistente, entrada fresca con sabores de pera, lima y almendra, buena acidez y cremosidad.\nFinal: Largo y mineral, con recuerdos de pan brioche y un agradable amargor cítrico."
    ],
    [106, 'Cava Jaume Serra',
        'Cava elaborado por Jaume Serra en la D.O. Cava con método tradicional. Coupage de variedades mediterráneas que da lugar a un espumoso fresco y versátil. Fermentación en botella y crianza sobre lías que aporta finura de burbuja y complejidad aromática.',
        "Nariz: Aromas frescos de manzana, pera y un toque de cítricos, con sutiles notas de levadura.\nBoca: Burbuja fina y agradable, con sabores de fruta blanca, buena acidez y un carácter limpio y directo.\nFinal: Medio y refrescante, con un recuerdo frutal y ligeramente tostado."
    ],
    [534, 'Anna de Codorníu Blanc de Noirs',
        'Cava singular de Codorníu elaborado exclusivamente con uvas tintas pinot noir, vinificadas como blanco. Una propuesta innovadora de la casa más antigua de cavas que aporta cuerpo y complejidad inusuales para un espumoso. Largo envejecimiento sobre lías en las cavas centenarias de Sant Sadurní.',
        "Nariz: Delicados aromas de frutos rojos como fresa y frambuesa, con notas de brioche, almendra y un toque floral.\nBoca: Cremoso y envolvente, con burbuja fina y persistente, sabores de frutas rojas, pan tostado y un punto goloso.\nFinal: Largo y elegante, con recuerdos de fresa, levadura fina y una sutil nota de galleta."
    ],
    [579, 'Cava Llopart Brut Nature Reserva',
        'Cava Brut Nature de la familia Llopart, una de las casas más antiguas del Penedès con más de 130 años elaborando espumosos. Macabeo, xarel·lo y parellada de viñedos propios en las faldas del Montserrat. Mínimo 18 meses de crianza sobre lías en sus cavas excavadas en la roca.',
        "Nariz: Aromas complejos de manzana asada, pan tostado, avellana y flores blancas, con un elegante fondo mineral.\nBoca: Burbuja fina y cremosa, gran equilibrio entre acidez y untuosidad, sabores de fruta blanca madura, almendra y notas de panadería.\nFinal: Largo y mineral, con recuerdos de frutos secos y un final seco y persistente."
    ],
    [580, 'Cava Llopart Imperial Brut Gran Reserva',
        'Gran Reserva de Llopart con más de 30 meses de crianza sobre lías, representa la máxima expresión de esta bodega familiar del Alt Penedès. Coupage de macabeo, xarel·lo, parellada y chardonnay. Degüelle manual y dosaje mínimo para un espumoso de extraordinaria complejidad.',
        "Nariz: Muy complejo, con aromas de frutos secos tostados, brioche, miel de azahar, cítricos maduros y notas de toffee.\nBoca: Burbuja finísima y cremosa, textura sedosa, gran estructura y sabores de avellana, pan tostado, fruta confitada y notas yodadas.\nFinal: Extraordinariamente largo, con recuerdos de frutos secos, cera de abeja y una mineralidad envolvente."
    ],
    [581, 'Cava Agustí Torelló Mata Trepat Rosat',
        'Cava rosado singular elaborado por Agustí Torelló Mata con la variedad autóctona trepat, una uva roja exclusiva del Penedès y la Conca de Barberà. Método tradicional con crianza mínima de 15 meses sobre lías. Un espumoso con personalidad propia y carácter mediterráneo.',
        "Nariz: Aromas de fresas frescas, pétalos de rosa, pomelo rosado y un sutil toque de galleta.\nBoca: Burbuja fina y delicada, sabores de frambuesa, grosella y cítricos, con buena acidez y un carácter frutal encantador.\nFinal: Medio-largo, fresco y afrutado, con un recuerdo de frutos rojos y pétalos de rosa."
    ],
    [588, 'Cava Privat Laietà Gran Reserva Brut Nature',
        'Gran Reserva de Alta Alella elaborado con chardonnay, macabeo y pinot noir de viñedos ecológicos en la D.O. Alella. Más de 30 meses de crianza sobre lías en las cavas de Tiana, junto al mar. La cercanía al Mediterráneo imprime una salinidad única a este espumoso.',
        "Nariz: Elegantes aromas de frutos secos, brioche, manzana asada y flores blancas, con notas de miel y un fondo salino.\nBoca: Burbuja fina y persistente, textura cremosa, sabores de almendra tostada, pera Williams y cítricos maduros, con mineralidad marina.\nFinal: Largo y complejo, con recuerdos de pan tostado, frutos secos y una salinidad mediterránea característica."
    ],
    [595, 'Cava Bertha Brut Nature Reserva',
        'Cava Brut Nature de Cava Bertha, bodega familiar en Subirats, D.O. Penedès. Elaborado con macabeo, xarel·lo y parellada de viñedos propios. Crianza mínima de 15 meses sobre lías siguiendo el método tradicional más riguroso.',
        "Nariz: Aromas de fruta blanca, flores de almendro y notas de levadura fina con un toque de pan recién horneado.\nBoca: Burbuja fina y constante, fresco y directo, con sabores de manzana verde, pera y almendra, buena acidez y equilibrio.\nFinal: Medio-largo, limpio y mineral, con un agradable recuerdo de frutos secos."
    ],
    [1090, 'Champagne Moët & Chandon Rosé Impérial',
        'Champagne rosado de la prestigiosa Maison Moët & Chandon en Épernay. Coupage de pinot noir, pinot meunier y chardonnay, con una proporción de vino tinto de Bouzy que aporta el color rosado y la intensidad frutal. Una de las referencias más reconocidas del champagne rosado a nivel mundial.',
        "Nariz: Aromas vibrantes de fresa silvestre, frambuesa, rosa y un toque de pimienta rosa, con notas de brioche y caramelo.\nBoca: Elegante y festivo, con burbuja fina y persistente, sabores de frutos rojos, pomelo rosado y un punto de jengibre.\nFinal: Largo y sofisticado, con recuerdos de frambuesa, pan tostado y una frescura envolvente."
    ],
    [644, 'Champagne d\'Aiglemont Brut',
        'Champagne Brut de la casa d\'Aiglemont, elaborado en la región de Champagne con el método tradicional. Coupage clásico de pinot noir, chardonnay y pinot meunier. Un champagne que ofrece la elegancia francesa en cada burbuja, con una relación calidad-precio destacable.',
        "Nariz: Aromas de manzana golden, pera, brioche y notas de almendra tostada con un sutil toque de miel.\nBoca: Burbuja fina y elegante, cremoso en boca con sabores de cítricos maduros, pan tostado y un punto mineral.\nFinal: Largo y refinado, con recuerdos de frutos secos, levadura fina y una acidez vibrante."
    ],

    // ===== SPIRITS / LIQUORS (15) =====
    [107, 'Whisky Johnnie Walker Red Label',
        'Blended scotch whisky de la destilería Johnnie Walker, el whisky más vendido del mundo. Combinación de hasta 35 whiskies de malta y grano de toda Escocia, envejecidos en barricas de roble. Red Label es la expresión más versátil de la familia Walker, creada para ser disfrutada sola o en combinados.',
        "Nariz: Aromas vivos de especias, canela y pimienta, con notas de vainilla, manzana fresca y un toque ahumado.\nBoca: Vibrante y especiado, con sabores de caramelo, vainilla, malta y un punto de humo de turba.\nFinal: Medio y cálido, con un retrogusto especiado y ligeramente ahumado."
    ],
    [196, 'Whisky Ballantine\'s',
        'Blended scotch whisky elaborado por Ballantine\'s desde 1827. Mezcla de más de 40 whiskies de malta y grano de las principales regiones escocesas. Destaca por su suavidad y equilibrio, resultado de una selección meticulosa de barricas por parte del Master Blender.',
        "Nariz: Aromas dulces de miel, vainilla y toffee, con notas de manzana, pera y un sutil fondo floral.\nBoca: Suave y cremoso, con sabores de chocolate con leche, caramelo, malta y un toque de roble.\nFinal: Medio y elegante, con un agradable calor y recuerdos de vainilla y miel."
    ],
    [200, 'Gin Beefeater',
        'Ginebra London Dry destilada en la destilería de Kennington, Londres, desde 1863. La receta original combina nueve botánicos incluyendo enebro, raíz de angélica, semillas de cilantro, regaliz y piel de naranja de Sevilla. Un clásico indiscutible de la ginebra británica.',
        "Nariz: Aromas nítidos de enebro, cítricos frescos, especias y un toque floral de violeta con notas de almendra.\nBoca: Seca y equilibrada, con sabores de enebro, lima, cilantro y un punto de regaliz, textura sedosa.\nFinal: Largo y limpio, con un retrogusto de enebro y cítricos que perdura."
    ],
    [332, 'Ron Brugal',
        'Ron dominicano de la familia Brugal, con más de 130 años de tradición ronera en Puerto Plata, República Dominicana. Elaborado con melazas seleccionadas y envejecido en barricas de roble americano blanco. Un ron seco y ligero que define el estilo caribeño dominicano.',
        "Nariz: Aromas de caramelo, vainilla y coco, con notas de plátano maduro y un toque de especias dulces.\nBoca: Ligero y seco, con sabores de caña de azúcar, nuez, vainilla y un punto de pimienta blanca.\nFinal: Medio y limpio, con un agradable calor y recuerdos de roble y caramelo."
    ],
    [463, 'Whisky Talisker 10 años',
        'Single malt de la destilería Talisker, la única destilería de la isla de Skye en las Highlands escocesas. Envejecido 10 años en barricas de roble americano, este whisky captura la esencia salvaje de su origen marítimo. Reconocido por su carácter ahumado con la explosiva pimienta que lo distingue.',
        "Nariz: Intensos aromas de humo de turba, algas marinas, pimienta negra y sal, con notas dulces de malta, fruta seca y miel.\nBoca: Potente y complejo, con oleadas de humo, pimienta, malta dulce y sal marina, textura aceitosa y envolvente.\nFinal: Extraordinariamente largo, con la característica explosión de pimienta de Talisker y recuerdos ahumados y salinos."
    ],
    [464, 'Gin Citadelle',
        'Ginebra francesa artesanal destilada en alambique de cobre en el Château de Bonbonnet, en la región de Cognac. Elaborada con 19 botánicos procedentes de todo el mundo, destilados individualmente y combinados después. Citadelle recupera una receta del siglo XVIII.',
        "Nariz: Aromas elegantes de enebro, violeta, anís estrellado y cardamomo, con notas de cítricos y un fondo especiado sutil.\nBoca: Suave y sedosa, con sabores de enebro, regaliz, canela y flores, complejidad que se despliega en capas.\nFinal: Largo y aromático, con recuerdos de especias exóticas y un toque floral persistente."
    ],
    [636, 'Gin Mare',
        'Ginebra mediterránea premium destilada en una antigua capilla del siglo XIII en Vilanova i la Geltrú, Barcelona. Su perfil botánico se inspira en la cocina mediterránea con arbequina, tomillo, romero y albahaca como botánicos principales, junto al enebro, cítricos y cardamomo.',
        "Nariz: Aromas herbáceos de romero y albahaca, con notas de oliva, cítricos y un toque de enebro fresco.\nBoca: Untuosa y compleja, con sabores de hierbas mediterráneas, aceituna, cardamomo y cítricos, textura sedosa.\nFinal: Largo y aromático, con recuerdos de romero, tomillo y un sutil toque salino mediterráneo."
    ],
    [648, 'Gin Brockmans',
        'Ginebra premium de estilo intensamente suave, destilada en Inglaterra con una combinación inusual de botánicos que incluye arándanos, moras, almendras y cilantro además del clásico enebro. Brockmans rompe con la ginebra tradicional apostando por un perfil más afrutado y suave.',
        "Nariz: Aromas de arándanos, moras y bayas oscuras, con un fondo de enebro suave, cilantro y notas de almendra.\nBoca: Sedosa y afrutada, con sabores de bayas, cítricos y un enebro presente pero contenido, textura aterciopelada.\nFinal: Medio-largo y suave, con recuerdos de frutos del bosque y almendras que persisten elegantemente."
    ],
    [632, 'Chartreuse Verde',
        'Licor herbáceo elaborado por los monjes cartujos en Voiron, Francia, siguiendo una receta secreta de 1605 que emplea 130 plantas, flores y raíces. Con 55% de graduación alcohólica, la Chartreuse Verde es más potente y herbácea que su hermana amarilla. Un destilado legendario e irreplicable.',
        "Nariz: Complejísimos aromas de hierbas alpinas, menta, anís, eucalipto y un mosaico de plantas aromáticas difícil de descifrar.\nBoca: Potente e intenso, con una cascada de sabores herbáceos, mentolados, anisados y balsámicos de extraordinaria complejidad.\nFinal: Muy largo y envolvente, con oleadas de hierbas que van apareciendo y un calor alcohólico bien integrado."
    ],
    [638, 'Fernet Branca',
        'Amaro italiano creado en Milán en 1845 por la familia Branca. Elaborado con una mezcla secreta de 27 hierbas y especias de cuatro continentes, incluyendo mirra, ruibarbo, manzanilla, azafrán y áloe. Macerado durante un año en barricas de roble. Digestivo por excelencia.',
        "Nariz: Aromas intensos de mentol, hierbas medicinales, regaliz y especias oscuras, con notas de eucalipto y café.\nBoca: Amargo e intenso, con sabores de mentol, hierbas amargas, raíces y un punto de chocolate oscuro.\nFinal: Largo y mentolado, con un amargor persistente que limpia el paladar y un calor reconfortante."
    ],
    [653, 'Frangelico',
        'Licor italiano de avellana elaborado en la región del Piamonte. Inspirado en la tradición de los monjes ermitaños del siglo XVII que infusionaban avellanas tostadas, cacao, vainilla y café en alcohol. Su botella en forma de fraile es una de las más reconocibles del mundo de los licores.',
        "Nariz: Aromas envolventes de avellana tostada, cacao, vainilla y un toque de café con un fondo de caramelo.\nBoca: Dulce y aterciopelado, con sabores de avellana, chocolate, vainilla y especias suaves, textura cremosa.\nFinal: Medio-largo y reconfortante, con recuerdos de frutos secos tostados y cacao que persisten."
    ],
    [907, 'Whisky Jack Daniel\'s',
        'Tennessee whiskey elaborado en Lynchburg, Tennessee, desde 1866. Lo que distingue a Jack Daniel\'s es su proceso de filtrado gota a gota a través de carbón de arce (Lincoln County Process) antes del envejecimiento en barricas de roble americano nuevas y carbonizadas.',
        "Nariz: Aromas de caramelo, plátano, vainilla y carbón de arce, con notas de especias dulces y un toque ahumado suave.\nBoca: Suave y dulce, con sabores de caramelo, vainilla, roble y un punto de humo característico del carbón de arce.\nFinal: Medio-largo y cálido, con recuerdos de caramelo, madera tostada y una suavidad envolvente."
    ],
    [927, 'Whisky Jameson',
        'Irish whiskey triple destilado de la destilería Midleton en Cork, Irlanda. Mezcla de whiskey de malta y grano envejecidos en barricas de roble ex-bourbon y ex-jerez. Jameson es el whiskey irlandés más vendido del mundo, símbolo de la suavidad que define el estilo irlandés.',
        "Nariz: Aromas suaves de cebada malteada, miel, vainilla y flores, con notas de frutos secos y un toque de jerez.\nBoca: Excepcionalmente suave y cremoso, con sabores de miel, nuez, vainilla y un toque de especias dulces.\nFinal: Medio y sedoso, con un retrogusto cálido de malta, miel y un punto de jerez que se desvanece suavemente."
    ],
    [865, 'Vodka Smirnoff',
        'Vodka ruso fundado en 1864 por Piotr Arseniévich Smirnov en Moscú. Triple destilado y filtrado diez veces a través de carbón de abedul, lo que le confiere una pureza y limpieza excepcionales. Es el vodka más vendido del mundo y referencia de la categoría.',
        "Nariz: Limpio y neutro, con sutiles notas de grano, un toque de pimienta blanca y una frescura cristalina.\nBoca: Suave y limpio, con un ligero dulzor de grano, textura sedosa y un punto de pimienta.\nFinal: Corto y limpio, con un calor suave y controlado que se disipa rápidamente."
    ],
    [718, 'Tequila José Cuervo Silver',
        'Tequila blanco de la casa José Cuervo, la destilería más antigua de México fundada en 1758 en Tequila, Jalisco. Elaborado con agave azul Weber y embotellado directamente tras la destilación, sin paso por barrica. Captura la esencia fresca y pura del agave.',
        "Nariz: Aromas de agave fresco, cítricos, pimienta verde y un toque herbáceo con notas de tierra húmeda.\nBoca: Fresco y directo, con sabores de agave, lima, pimienta y un punto dulce de agave cocido.\nFinal: Medio y picante, con un calor agradable y recuerdos de agave y cítricos."
    ],

    // ===== CERVEZAS (5) =====
    [805, 'Cerveza Delirium Tremens',
        'Cerveza belga strong blonde ale de la cervecería Huyghe, en Melle, Bélgica. Reconocible por su botella de cerámica blanca decorada con elefantes rosas. Con 8.5% de graduación y triple fermentación con tres levaduras diferentes, es una de las cervezas belgas más premiadas del mundo.',
        "Nariz: Aromas de malta dulce, plátano, clavo y especias, con notas de miel, cítricos y un sutil toque alcohólico.\nBoca: Cremosa y efervescente, con sabores de malta, frutas tropicales, especias belgas y un dulzor equilibrado por la carbonatación.\nFinal: Largo y cálido, con recuerdos de especias, malta tostada y un calor alcohólico bien integrado."
    ],
    [806, 'Cerveza Delirium Nocturnum',
        'Strong dark ale belga de la cervecería Huyghe, la hermana oscura de la famosa Delirium Tremens. Con 8.5% de graduación, emplea maltas tostadas y oscuras que le confieren su color granate profundo y sus aromas de chocolate y frutas oscuras. Triple fermentación para máxima complejidad.',
        "Nariz: Aromas de chocolate negro, café, frutas pasas, higos y especias oscuras como clavo y regaliz.\nBoca: Densa y aterciopelada, con sabores de cacao, caramelo oscuro, ciruela pasa y un punto de pimienta.\nFinal: Largo y envolvente, con recuerdos de chocolate amargo, frutas confitadas y un calor reconfortante."
    ],
    [817, 'Cerveza BrewDog Punk IPA',
        'India Pale Ale icónica de la cervecería escocesa BrewDog, fundada en 2007. Punk IPA revolucionó la escena cervecera artesanal europea con su carácter lupulado y su actitud irreverente. Emplea lúpulos americanos como Simcoe, Citra y Chinook para un perfil aromático explosivo.',
        "Nariz: Aromas intensos de pomelo, mango, piña y resina de pino, con notas florales y un fondo de galleta.\nBoca: Amarga y refrescante, con sabores de cítricos tropicales, pomelo, caramelo de malta y un amargor seco persistente.\nFinal: Largo y amargo, con recuerdos de cítricos tropicales y resina que limpian el paladar."
    ],
    [1098, 'Cerveza La Chouffe',
        'Belgian strong golden ale de la Brasserie d\'Achouffe en las Ardenas belgas. Reconocible por su gnomo rojo (chouffe) en la etiqueta. Con 8% de graduación, emplea cilantro y lúpulo como ingredientes principales. Una cerveza artesanal belga de referencia que ha conquistado paladares en todo el mundo.',
        "Nariz: Aromas de cítricos, cilantro fresco, miel y levadura belga, con notas de manzana verde y especias.\nBoca: Efervescente y ligera pese a su graduación, con sabores de miel, especias, cítricos y un toque herbáceo de cilantro.\nFinal: Largo y especiado, con recuerdos de cilantro, miel y un calor alcohólico elegante."
    ],
    [1096, 'Cerveza La Bella Lola',
        'Cerveza artesanal catalana de la cervecería Barcelona Beer Company. Estilo Mediterranean Blonde Ale inspirada en la escultura de La Bella Lola del Passeig Marítim de Barcelona. Elaborada con maltas pálidas y lúpulos aromáticos, es una cerveza refrescante pensada para el clima mediterráneo.',
        "Nariz: Aromas suaves de malta, pan blanco, cítricos y un toque de hierba fresca con notas florales.\nBoca: Ligera y refrescante, con sabores de malta, trigo, un toque cítrico y una carbonatación viva.\nFinal: Corto y limpio, con un retrogusto seco y refrescante perfecto para el clima cálido."
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
