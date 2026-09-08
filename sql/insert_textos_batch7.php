<?php
/**
 * Batch 7: Insert texts for 50 products
 * Original descriptions and tasting notes
 */
require dirname(__DIR__) . '/distribucio/connexio/connexio.php';
mysqli_query($bd, "SET SESSION sql_mode=''");
mysqli_set_charset($bd, 'utf8mb4');

$textos = [
    // ===== VINOS (18) =====
    [584, 'Sumarroca Muscat',
        'Blanco aromático de Sumarroca en la D.O. Penedès elaborado con moscatel de grano menudo de viñedos ecológicos propios. Esta variedad aporta una intensidad aromática explosiva que Sumarroca preserva mediante una vinificación a baja temperatura en acero inoxidable. Un blanco pensado para disfrutar joven y fresco.',
        "Nariz: Aromas explosivos de uva moscatel, flores de azahar, rosa, lichi y fruta tropical con notas de hierbas frescas.\nBoca: Aromático y glicérico, con sabores de uva fresca, flores, fruta tropical y un toque de miel, buena acidez.\nFinal: Medio-largo y perfumado, con recuerdos florales y frutales que perduran elegantemente."
    ],
    [585, 'Nostrat Sumarroca',
        'Blanco de la D.O. Penedès elaborado por Sumarroca dentro de su gama Nostrat (nuestro, en catalán antiguo), que representa la línea más accesible y cotidiana de esta bodega familiar del Alt Penedès. Coupage de variedades locales vinificadas para expresar frescura y carácter mediterráneo inmediato.',
        "Nariz: Aromas de fruta blanca, manzana, pera y un toque de flores blancas con notas cítricas frescas.\nBoca: Fresco y ligero, con sabores de fruta blanca, cítricos y un carácter frutal directo, buena acidez.\nFinal: Medio y limpio, con un retrogusto de fruta fresca que lo hace ideal para el día a día."
    ],
    [640, 'Valdesantos Joven',
        'Tinto joven de la D.O. Ribera del Duero elaborado con tinta del país (tempranillo) de viñedos de la meseta castellana. Sin crianza en barrica, busca la máxima expresión frutal del tempranillo ribereño en su juventud. Un vino franco y directo pensado para disfrutar sin ceremonia.',
        "Nariz: Aromas frescos de cereza, frambuesa, mora y un toque de violetas con notas de regaliz.\nBoca: Jugoso y frutal, con taninos suaves, buena acidez y sabores de fruta roja y negra fresca.\nFinal: Corto y afrutado, con recuerdos de cereza que lo hacen fácil de beber y versátil en la mesa."
    ],
    [674, 'Ramón Bilbao Crianza Magnum',
        'Crianza de la D.O.Ca. Rioja de Bodegas Ramón Bilbao en formato magnum (1,5 L). Tempranillo de viñedos seleccionados con 14 meses de crianza en barricas de roble americano y francés. El formato magnum favorece una evolución más lenta y armónica. Ramón Bilbao es una de las bodegas más reconocidas de Rioja.',
        "Nariz: Aromas de fruta roja madura, ciruela, vainilla, especias dulces y tostados elegantes de la barrica con un fondo balsámico.\nBoca: Equilibrado y redondo, con taninos maduros, buena estructura y sabores de fruta, especias, cacao y un toque de regaliz.\nFinal: Largo y armonioso, con recuerdos de fruta confitada, vainilla y roble que definen un rioja clásico y fiable."
    ],
    [722, 'Vi de Gel Gewürztraminer',
        'Vino dulce tipo ice wine elaborado en la D.O. Penedès con gewürztraminer. Las uvas se vendimian congeladas para concentrar azúcares y aromas antes del prensado. El gewürztraminer aporta su carácter aromático explosivo de rosas y lichi a un dulce natural de extraordinaria intensidad, complejidad y equilibrio.',
        "Nariz: Aromas intensísimos de rosas, lichi, mango, miel y fruta de la pasión con un fondo especiado y notas de jengibre confitado.\nBoca: Dulce y untuoso, con sabores de fruta tropical, flores, miel y especias exóticas, equilibrado por una acidez vibrante.\nFinal: Muy largo y perfumado, con recuerdos de rosas, lichi y miel que se entrelazan sin empalagar."
    ],
    [739, 'Gaba do Xil',
        'Tinto de Telmo Rodríguez elaborado en Valdeorras, Galicia, con mencía de viñedos en pendiente sobre suelos de pizarra. Gaba do Xil es el nombre del proyecto de este legendario enólogo en la ribera del río Sil, buscando la expresión atlántica de la mencía gallega en un tinto fresco y mineral.',
        "Nariz: Aromas de fruta roja fresca, cereza, frambuesa, flores violetas y un fondo mineral de pizarra con notas herbales.\nBoca: Fresco y elegante, con taninos finos, acidez vibrante y sabores de fruta roja, hierbas y una mineralidad pizarrosa persistente.\nFinal: Largo y mineral, con recuerdos de fruta fresca y pizarra que definen el carácter atlántico de Valdeorras."
    ],
    [760, 'Fuentespinta Crianza',
        'Tinto de la D.O. Ribera del Duero elaborado con tempranillo de viñedos seleccionados en la zona de Peñafiel. Crianza en barricas de roble francés y americano que aporta complejidad y estructura a este tinto ribereño de vocación clásica. Un Ribera del Duero accesible y bien elaborado.',
        "Nariz: Aromas de fruta negra madura, mora, especias, vainilla y notas de tostado con un fondo de cuero y tabaco.\nBoca: Estructurado y potente, con taninos firmes pero maduros, buena acidez y sabores de fruta negra, cacao y especias.\nFinal: Largo y envolvente, con recuerdos de fruta confitada y roble noble que expresan el estilo Ribera."
    ],
    [770, 'Titella',
        'Tinto de la D.O. Montsant elaborado con variedades mediterráneas (garnacha, cariñena, syrah) de viñedos en suelos de pizarra y arcilla roja. Montsant, hermana del Priorat, comparte su carácter mineral y mediterráneo a un precio más accesible. Titella ofrece un tinto con personalidad propia.',
        "Nariz: Aromas de cereza negra, mora, hierbas aromáticas, romero y notas de pizarra con un toque de pimienta negra.\nBoca: Jugoso y mediterráneo, con taninos presentes pero redondos, buena acidez y sabores de fruta negra, garriga y especias.\nFinal: Medio-largo y mineral, con recuerdos de fruta madura y pizarra que reflejan el terroir del Montsant."
    ],
    [780, 'Mes que Paraules Negre',
        'Tinto catalán del proyecto Mes que Paraules (más que palabras) elaborado con garnacha y cariñena de viñedos mediterráneos. Un vino de autor con vocación territorial que combina la expresión frutal de la garnacha con la estructura de la cariñena, ofreciendo un tinto con personalidad y carácter propio.',
        "Nariz: Aromas de fruta roja madura, cereza, frambuesa confitada, hierbas mediterráneas y un toque especiado.\nBoca: Redondo y sabroso, con taninos maduros, buena acidez y sabores de fruta roja, garriga y un punto balsámico.\nFinal: Medio-largo, con recuerdos de fruta y hierbas aromáticas que reflejan el paisaje mediterráneo."
    ],
    [819, 'Saltimbanqui Verdejo',
        'Blanco de la D.O. Rueda elaborado con verdejo de viñedos castellanos sobre suelos pedregosos a más de 700 metros de altitud. Saltimbanqui apuesta por una vinificación moderna que captura toda la intensidad aromática y la frescura del verdejo ruedense en un blanco expresivo y directo.',
        "Nariz: Aromas intensos de hierba fresca, fruta de la pasión, pomelo, hinojo y un fondo mineral con notas florales.\nBoca: Vibrante y expresivo, con acidez marcada, sabores de cítricos, hierba fresca y un toque amargo elegante.\nFinal: Largo y refrescante, con un retrogusto cítrico y herbal que invita a seguir bebiendo."
    ],
    [830, 'Entrelobos',
        'Tinto de la D.O. Ribera del Duero elaborado con tinta del país (tempranillo) de viñedos de altitud en la meseta castellana. El nombre Entrelobos evoca la naturaleza salvaje del Duero castellano. Un tinto de perfil moderno que combina potencia ribereña con accesibilidad y fruta expresiva.',
        "Nariz: Aromas de fruta negra madura, ciruela, mora y un toque de especias con notas de regaliz y cacao.\nBoca: Potente y jugoso, con taninos redondos, buena concentración y sabores de fruta negra, especias y un punto de chocolate.\nFinal: Medio-largo y envolvente, con recuerdos de fruta madura y un final cálido y especiado."
    ],
    [858, 'Glarima Criança Negre',
        'Tinto de la D.O. Somontano elaborado por Bodegas Sommos (antes Pirineos) en el prepirineo aragonés. Glarima es la gama más accesible de esta bodega, con una breve crianza en barrica que aporta complejidad sin perder la fruta. Un Somontano de estilo moderno y fácil de beber.',
        "Nariz: Aromas de fruta roja, cereza, especias suaves y notas de vainilla con un fondo de tostados ligeros.\nBoca: Redondo y equilibrado, con taninos suaves, buena acidez y sabores de fruta, especias dulces y un toque de roble.\nFinal: Medio y agradable, con recuerdos de fruta y un toque cálido que lo hace versátil con la comida."
    ],
    [878, 'Viñas de Anna Blanc de Blancs',
        'Blanco de Bodegas Torres elaborado con chardonnay de viñedos propios en Catalunya. Viñas de Anna lleva el nombre de la madre de Miguel A. Torres, y representa la apuesta de Torres por blancos de carácter con variedades internacionales cultivadas en suelo catalán. Fermentación parcial en barrica.',
        "Nariz: Aromas de manzana golden, pera madura, flores blancas y un toque de mantequilla con notas de avellana y vainilla sutil.\nBoca: Amplio y cremoso, con buena estructura, sabores de fruta blanca, frutos secos y un punto tostado elegante.\nFinal: Medio-largo y refinado, con recuerdos de fruta madura y un retrogusto ligeramente ahumado."
    ],
    [879, 'Veni Vidi Vinum',
        'Blanco catalán con nombre que juega con la célebre frase de Julio César. Elaborado con variedades blancas mediterráneas de viñedos catalanes, busca un perfil fresco y accesible pensado para el consumo cotidiano. Un vino que no se toma demasiado en serio pero cumple con calidad y frescura.',
        "Nariz: Aromas de fruta blanca, manzana verde, cítricos y un toque de flores primaverales con notas herbáceas.\nBoca: Ligero y fresco, con buena acidez, sabores de fruta, cítricos y un carácter directo y refrescante.\nFinal: Corto y limpio, con recuerdos de fruta fresca que lo hacen ideal como aperitivo o con comidas ligeras."
    ],
    [1023, 'Marmellans Negre',
        'Tinto de la D.O. Montsant elaborado con garnacha, cariñena y syrah de viñedos en la zona de Capçanes, pueblo con tradición vinícola milenaria. Marmellans refleja el carácter mineral y mediterráneo de la D.O. Montsant, con suelos de arcilla roja y pizarra que confieren personalidad al vino.',
        "Nariz: Aromas de fruta negra madura, mora silvestre, romero, tomillo y notas de pizarra con un toque especiado.\nBoca: Estructurado y sabroso, con taninos presentes, buena acidez y sabores de fruta negra, hierbas y especias mediterráneas.\nFinal: Medio-largo y mineral, con recuerdos de garriga y pizarra que expresan el terroir del Montsant."
    ],
    [1106, 'Pizarras de Otero',
        'Tinto de la D.O. Bierzo elaborado con mencía de viñedos viejos sobre suelos de pizarra en el noroeste leonés. El Bierzo, a caballo entre Castilla y Galicia, produce tintos de mencía con frescura atlántica y mineralidad. Pizarras de Otero captura ese carácter único del terroir berciano.',
        "Nariz: Aromas de cereza fresca, frambuesa, violetas y un fondo mineral de pizarra con notas de hierbas y tierra húmeda.\nBoca: Fresco y elegante, con taninos finos, acidez vibrante y sabores de fruta roja, hierbas y una mineralidad pizarrosa.\nFinal: Largo y mineral, con recuerdos de fruta roja y pizarra que lo distinguen como un bierzo auténtico."
    ],
    [1157, 'Bouquet d\'Alella Syrah',
        'Tinto monovarietal de syrah elaborado por Alta Alella en la D.O. Alella con uvas de viñedos ecológicos en la Serralada de Marina, a pocos kilómetros del Mediterráneo. La syrah cultivada cerca del mar adquiere un carácter fresco y aromático que la distingue de expresiones más cálidas del interior.',
        "Nariz: Aromas de fruta negra, mora, pimienta negra, violetas y un toque de olivas negras con notas de romero.\nBoca: Fresco y especiado, con taninos maduros, buena acidez y sabores de frutos negros, pimienta y un punto salino.\nFinal: Medio-largo y elegante, con recuerdos de especias y una frescura marítima que define la syrah de Alella."
    ],
    [1232, 'Juan Gil 4 Meses',
        'Tinto de la D.O. Jumilla elaborado por Bodegas Juan Gil con monastrell de viñedos viejos de secano sobre suelos calizos a 700 metros de altitud. Breve paso de 4 meses por barrica que redondea la fruta sin enmascararla. Juan Gil es el referente moderno de Jumilla y ha puesto esta D.O. en el mapa internacional.',
        "Nariz: Aromas intensos de fruta negra madura, mora, ciruela, un toque de chocolate y notas de especias dulces con fondo mineral.\nBoca: Potente y concentrado, con taninos maduros, gran fruta y sabores de mora, regaliz, cacao y especias.\nFinal: Largo y cálido, con recuerdos de fruta confitada y un toque balsámico que definen el estilo Jumilla."
    ],

    // ===== CAVAS / ESPUMOSOS (8) =====
    [1319, 'Cava Mas Oliver Brut Nature Reserva',
        'Cava Brut Nature Reserva elaborado por Mas Oliver en la D.O. Cava con variedades tradicionales (macabeo, xarel·lo y parellada). Crianza mínima de 15 meses sobre lías en método tradicional. Mas Oliver apuesta por un estilo seco y puro, sin dosaje, dejando que el terroir se exprese.',
        "Nariz: Aromas de manzana verde, pera, levadura fina y un fondo mineral con notas de almendra y hierbas secas.\nBoca: Burbuja fina, seco y directo, con sabores de fruta blanca, pan tostado y una acidez vibrante.\nFinal: Largo y mineral, con recuerdos de frutos secos y un retrogusto limpio y elegante."
    ],
    [1324, 'Carbomas Rosé Semi Seco',
        'Cava rosado semi-seco elaborado en la D.O. Penedès con variedades tintas que aportan su color rosado natural. El dosaje semi-seco añade un toque de dulzor que equilibra la acidez del espumoso, haciéndolo ideal para aperitivos dulces, postres y celebraciones informales.',
        "Nariz: Aromas de fresa, frambuesa, pétalos de rosa y un toque de caramelo suave con notas frutales.\nBoca: Burbuja agradable, dulzor elegante equilibrado por la acidez, sabores de frutos rojos y flores.\nFinal: Medio y afrutado, con recuerdos de fresa que lo hacen festivo y fácil de disfrutar."
    ],
    [1461, 'Parxet Brut Nature Magnum',
        'Cava Brut Nature de Parxet en formato magnum (1,5 L), elaborado en la D.O. Cava con variedades autóctonas de viñedos propios en Alella. El formato magnum permite una segunda fermentación más lenta y una evolución más armónica sobre sus lías. Parxet es pionera del cava en la D.O. Alella.',
        "Nariz: Aromas complejos de manzana asada, frutos secos, pan tostado y flores secas con notas minerales salinas.\nBoca: Burbuja finísima y cremosa, seco y estructurado, con sabores de fruta blanca, brioche, almendra y salinidad.\nFinal: Muy largo y complejo, con recuerdos de frutos secos y levadura que reflejan la madurez del magnum."
    ],
    [1477, 'Cava Heretat Mastinell Gran Reserva',
        'Cava Gran Reserva de Heretat Mastinell, bodega con hotel boutique en el corazón del Penedès. Elaborado con macabeo, xarel·lo y parellada de viñedos propios con más de 30 meses de crianza sobre lías. Mastinell combina la tradición del cava con un concepto de enoturismo de primer nivel.',
        "Nariz: Aromas complejos de frutos secos tostados, pan de pueblo, miel de acacia, cítricos maduros y notas de hierbas secas.\nBoca: Burbuja finísima, gran estructura y volumen, sabores de avellana, brioche, fruta confitada y notas yodadas.\nFinal: Muy largo y memorable, con recuerdos de frutos secos, cera de abeja y una mineralidad elegante."
    ],
    [1520, 'Cava Juvé & Camps Gran Reserva',
        'Cava Gran Reserva de Juvé & Camps, bodega familiar centenaria del Penedès. Elaborado con macabeo, xarel·lo y parellada de las mejores parcelas propias, con más de 30 meses de crianza sobre lías. La expresión más selecta y compleja de esta casa, para quienes buscan un cava de alta gastronomía.',
        "Nariz: Aromas complejos de frutos secos, brioche, miel, cítricos confitados y flores secas con un fondo de hierbas y notas minerales.\nBoca: Burbuja finísima y cremosa, gran estructura, sabores de avellana tostada, fruta confitada y pan de especias.\nFinal: Extraordinariamente largo, con oleadas de frutos secos, levadura noble y una salinidad mineral persistente."
    ],
    [1525, 'Champagne Delamotte Blanc de Blancs',
        'Champagne Blanc de Blancs de la Maison Delamotte, fundada en 1760 y hermana de la legendaria Salon. Elaborado íntegramente con chardonnay de los mejores crus de la Côte des Blancs. Delamotte comparte las mismas fuentes de uva que Salon pero ofrece un estilo más accesible y versátil.',
        "Nariz: Aromas refinados de cítricos, tiza, flores blancas, manzana verde y un fondo mineral calcáreo con notas de pan tostado.\nBoca: Burbuja elegante y finísima, fresco y tenso, con sabores de limón, almendra, brioche y una mineralidad calcárea vibrante.\nFinal: Muy largo y preciso, con recuerdos de tiza, cítricos y levadura que definen el gran estilo de la Côte des Blancs."
    ],
    [1526, 'Champagne Delamotte Blanc de Blancs',
        'Champagne Blanc de Blancs de la Maison Delamotte, fundada en 1760 y hermana de la legendaria Salon. Elaborado íntegramente con chardonnay de los mejores crus de la Côte des Blancs. Delamotte comparte las mismas fuentes de uva que Salon pero ofrece un estilo más accesible y versátil.',
        "Nariz: Aromas refinados de cítricos, tiza, flores blancas, manzana verde y un fondo mineral calcáreo con notas de pan tostado.\nBoca: Burbuja elegante y finísima, fresco y tenso, con sabores de limón, almendra, brioche y una mineralidad calcárea vibrante.\nFinal: Muy largo y preciso, con recuerdos de tiza, cítricos y levadura que definen el gran estilo de la Côte des Blancs."
    ],
    [1636, 'Cava Agustí Torelló Mata Brut Nature Gran Reserva',
        'Cava Gran Reserva Brut Nature de Agustí Torelló Mata, bodega de referencia en Sant Sadurní d\'Anoia con más de 600 años de historia. Variedades tradicionales de viñedos propios en la finca Can Martí, con más de 36 meses de crianza sobre lías. Un cava de extraordinaria complejidad y finura.',
        "Nariz: Aromas complejos de frutos secos tostados, pan de pueblo, miel, cítricos maduros y notas de hierbas secas con un fondo mineral.\nBoca: Burbuja finísima y cremosa, gran estructura, sabores de avellana, brioche, fruta confitada y notas minerales.\nFinal: Extraordinariamente largo y sofisticado, con recuerdos de frutos secos, levadura noble y una mineralidad calcárea."
    ],

    // ===== LICORES / DESTILADOS (19) =====
    [1009, 'Afilador Hierbas 1L',
        'Orujo de hierbas gallego de la casa Afilador en formato de litro, pensado para restauración y consumo frecuente. Maceración de hierbas aromáticas autóctonas (menta, manzanilla, hinojo, regaliz, tomillo) en aguardiente de orujo gallego. Un licor digestivo clásico de las sobremesas gallegas.',
        "Nariz: Aromas intensos de hierbas frescas, menta, hinojo, regaliz y anís con un fondo cálido de aguardiente.\nBoca: Dulce y herbáceo, con sabores complejos de hierbas aromáticas, un toque anisado y la calidez del orujo.\nFinal: Largo y refrescante, con recuerdos de hierbas y un calor digestivo agradable."
    ],
    [1030, 'Vermú Martini Dry',
        'Vermut seco italiano de la casa Martini & Rossi, fundada en Turín en 1863. Elaborado con vino blanco seco macerado con una selección de botánicos que incluyen ajenjo, corteza de cítricos y hierbas aromáticas. Martini Dry es más seco y herbal que su hermano rosso, diseñado como ingrediente esencial del Dry Martini.',
        "Nariz: Aromas de hierbas amargas, limón, pomelo y un fondo floral con notas de manzanilla y quina.\nBoca: Seco y herbal, con sabores de cítricos, hierbas amargas y un amargor elegante, textura limpia.\nFinal: Medio y seco, con un retrogusto de hierbas y cítricos que lo hacen perfecto en coctelería clásica."
    ],
    [1107, 'Malibu',
        'Licor de ron y coco elaborado en Barbados, Caribe, con ron de caña de azúcar aromatizado con extracto natural de coco. Creado en 1980, Malibu se ha convertido en un icono de la coctelería tropical y de playa. Su sabor dulce y su baja graduación (21%) lo hacen muy versátil en cócteles como la Piña Colada.',
        "Nariz: Aromas intensos de coco tostado, crema de coco, vainilla y un fondo de caña de azúcar con notas tropicales.\nBoca: Dulce y cremoso, con sabores de coco, vainilla, piña y un toque de ron caribeño, textura sedosa.\nFinal: Medio y tropical, con recuerdos de coco que perduran como un día de playa."
    ],
    [1108, 'Whisky Knockando 12 Años',
        'Single malt de la destilería Knockando, fundada en 1898 en el corazón de Speyside, Escocia. El nombre proviene del gaélico Cnoc-an-dhu (la pequeña colina negra). Envejecido 12 años en barricas de roble ex-bourbon. Cada botella indica la cosecha, práctica inusual que distingue a Knockando de otros single malts.',
        "Nariz: Aromas de miel, fruta seca, malta, vainilla suave y un toque floral con notas de cereales tostados.\nBoca: Suave y elegante, con sabores de miel, fruta madura, vainilla y un punto de especias dulces, textura sedosa.\nFinal: Medio-largo y cálido, con recuerdos de miel, malta y un calor suave que define el estilo Speyside."
    ],
    [1165, 'Whisky Buchanan\'s 12 Años',
        'Blended scotch whisky premium de James Buchanan & Co, fundada en 1884. Ensamblaje de whiskies de malta y grano con un mínimo de 12 años de envejecimiento, con maltas de Dalwhinnie y otras destilerías de las Highlands. Buchanan\'s es uno de los blends más populares de Latinoamérica.',
        "Nariz: Aromas de miel, caramelo, frutas secas, malta y un toque de especias con notas de vainilla y roble.\nBoca: Suave y redondo, con sabores de miel, caramelo, frutas confitadas y un punto de pimienta dulce, textura cremosa.\nFinal: Medio-largo y elegante, con recuerdos de miel, roble y un calor envolvente muy agradable."
    ],
    [1179, 'Gin G\'Vine Nouaison',
        'Ginebra francesa premium elaborada en Cognac con base de alcohol de uva ugni blanc, no de cereales. Nouaison (cuajado de la uva) es la versión más intensa de G\'Vine, con una segunda maceración de botánicos que incluye flor de vid, enebro, raíz de lirio, cilantro, cubeba y nuez moscada.',
        "Nariz: Aromas complejos de enebro, flores de vid, cítricos, raíz de lirio y un fondo especiado con notas de pimienta cubeba.\nBoca: Intensa y aterciopelada, con sabores de enebro, flores, especias y la redondez distintiva del destilado de uva.\nFinal: Largo y elegante, con un retrogusto floral y especiado que la distingue como una ginebra de autor."
    ],
    [1285, 'Crema de Arroz La Bomba del Delta de l\'Ebre',
        'Licor artesanal catalán elaborado en el Delta del Ebro con arroz de la variedad Bomba, autóctona de esta zona. Un producto que nace de la tradición arrocera del Delta transformada en licor cremoso y dulce. Producción artesanal que pone en valor el terroir y la cultura del arroz en el Ebro.',
        "Nariz: Aromas de arroz con leche, vainilla, canela y un fondo cremoso con notas de caramelo suave.\nBoca: Cremoso y dulce, con sabores de arroz, leche, canela y vainilla, textura untuosa y envolvente.\nFinal: Medio y reconfortante, con recuerdos de arroz con leche casero que evocan la tradición."
    ],
    [1333, 'Gin Giró',
        'Ginebra española de la casa Giró, destilería catalana con tradición en destilados. Un gin de perfil clásico mediterráneo elaborado con botánicos seleccionados donde el enebro es protagonista. Pensada tanto para consumo en gin-tonic como para coctelería, ofreciendo un estilo directo y sin artificios.',
        "Nariz: Aromas de enebro fresco, cítricos, hierbas mediterráneas y un toque de especias suaves.\nBoca: Seca y directa, con sabores de enebro, limón, hierbas y un punto de pimienta, textura limpia.\nFinal: Medio y seco, con un retrogusto de enebro y cítricos que lo hace versátil en combinados."
    ],
    [1372, 'Lustau Fino Jarana',
        'Fino de Bodegas Lustau, una de las casas históricas de Jerez fundada en 1896. Jarana es un fino de crianza biológica bajo velo de flor en criaderas y solera de botas de roble americano. Lustau es referencia mundial en vinos de Jerez y Jarana es su fino más accesible y expresivo.',
        "Nariz: Aromas punzantes de almendra, levadura, manzanilla, aceitunas verdes y un fondo salino y yodado.\nBoca: Seco y punzante, con sabores de almendra amarga, masa de pan, cítricos y una salinidad marcada.\nFinal: Largo y seco, con un retrogusto almendrado y yodado que define el gran estilo del fino jerezano."
    ],
    [1403, 'Whisky Wild Turkey',
        'Bourbon americano de la destilería Wild Turkey en Lawrenceburg, Kentucky, fundada en 1855. Destilado con maíz, centeno y cebada malteada, y envejecido en barricas nuevas de roble americano carbonizado. Wild Turkey es conocido por su perfil potente y especiado, con un carácter propio del mejor bourbon.',
        "Nariz: Aromas de caramelo, vainilla, maíz tostado, especias y notas de roble carbonizado con un fondo de fruta seca.\nBoca: Potente y especiado, con sabores de caramelo, centeno, vainilla, canela y un calor intenso del bourbon.\nFinal: Largo y cálido, con recuerdos de roble tostado, especias y un dulzor de maíz que define el estilo Kentucky."
    ],
    [1406, 'Brandy de Jerez Independencia Gran Reserva',
        'Brandy de Jerez de la categoría Gran Reserva, con un mínimo de 3 años de envejecimiento en el sistema tradicional de criaderas y solera en botas de roble americano previamente envinadas con oloroso. Los brandies de Jerez Gran Reserva alcanzan una complejidad y suavidad excepcionales gracias al largo contacto con la madera.',
        "Nariz: Aromas complejos de pasas, higos secos, caramelo tostado, vainilla y roble con notas de especias y café.\nBoca: Sedoso y profundo, con sabores de fruta confitada, toffee, almendra tostada y especias nobles, textura envolvente.\nFinal: Muy largo y elegante, con recuerdos de fruta seca, madera noble y un calor envolvente."
    ],
    [1443, 'Grappa del Contadino',
        'Grappa italiana artesanal cuyo nombre (del campesino) evoca la tradición rural del norte de Italia. Elaborada destilando el bagazo sobrante de la vinificación, siguiendo métodos tradicionales que preservan el carácter de la uva original. Una grappa rústica y auténtica, fiel al estilo campesino.',
        "Nariz: Aromas de uva fresca, fruta de hueso, flores y un toque herbal con la intensidad característica de la grappa.\nBoca: Potente y rústica, con sabores de uva, fruta blanca y una calidez cristalina con un punto de almendra.\nFinal: Largo y cálido, con recuerdos de fruta y un carácter auténtico que refleja la tradición campesina."
    ],
    [1487, 'Whisky Glen Grant',
        'Single malt de la destilería Glen Grant, fundada en 1840 en Rothes, Speyside. Una de las maltas más vendidas del mundo, especialmente en Italia. Destilada en alambiques altos y estrechos que producen un espíritu ligero y afrutado. Glen Grant define el estilo ligero y accesible de Speyside.',
        "Nariz: Aromas de manzana verde, pera, malta, flores blancas y un toque de almendra con notas de vainilla.\nBoca: Ligero y afrutado, con sabores de manzana, pera, malta y un punto de especias suaves, textura limpia.\nFinal: Medio y elegante, con recuerdos de fruta fresca y malta que lo hacen un single malt accesible."
    ],
    [1539, 'Whisky Chivas Regal 21 Años',
        'Blended scotch whisky ultra-premium de Chivas Brothers, también conocido como Royal Salute. Ensamblaje de los mejores whiskies de malta y grano envejecidos un mínimo de 21 años, incluyendo maltas de Strathisla y otras destilerías de Speyside. Presentación en estuche y botella exclusiva.',
        "Nariz: Aromas sofisticados de frutas secas, miel de bosque, naranja confitada, chocolate, cedro y especias nobles con un fondo ahumado sutil.\nBoca: Sedoso y opulento, con capas de sabor: miel, frutas confitadas, chocolate, café, especias y un roble exquisitamente integrado.\nFinal: Extraordinariamente largo y envolvente, con oleadas de fruta seca, especias y una calidez noble que perdura."
    ],
    [1544, 'Kina San Clemente',
        'Licor de quina español elaborado siguiendo una receta tradicional que combina vino con corteza de quina, hierbas aromáticas y especias. La kina o quina es un aperitivo clásico del Mediterráneo español, con raíces en la tradición farmacéutica del siglo XIX. Un licor amargo-dulce ideal como aperitivo.',
        "Nariz: Aromas de hierbas amargas, naranja amarga, corteza de quina, especias y un fondo de caramelo.\nBoca: Amargo-dulce equilibrado, con sabores de quina, cítricos amargos, hierbas y un toque de especias.\nFinal: Medio-largo y estimulante, con recuerdos de quina y hierbas amargas que abren el apetito."
    ],
    [1545, 'Manzanilla Papirusa Lustau',
        'Manzanilla de Sanlúcar de Barrameda elaborada por Bodegas Lustau. Papirusa es una manzanilla de crianza biológica bajo velo de flor en botas de roble americano en las bodegas de Sanlúcar, donde la brisa atlántica confiere un carácter salino único. La manzanilla más fresca e intensa del catálogo de Lustau.',
        "Nariz: Aromas de manzanilla seca, almendra fresca, aceitunas, sal marina y levadura con notas de hierbas y un fondo yodado.\nBoca: Seca, punzante y salina, con sabores de almendra, manzanilla, cítricos y una salinidad marítima persistente.\nFinal: Largo y marítimo, con un retrogusto salino y almendrado que define el estilo de Sanlúcar."
    ],
    [1550, 'Campari 1L',
        'Aperitivo italiano amargo creado por Gaspare Campari en Milán en 1860. Su inconfundible color rojo y su sabor amargo-dulce son resultado de la infusión de hierbas aromáticas y corteza de naranja amarga en alcohol. Campari es el alma del Negroni, el Americano y el Spritz, pilares de la coctelería italiana.',
        "Nariz: Aromas intensos de naranja amarga, hierbas, cereza y un fondo de especias con notas de raíz de genciana.\nBoca: Amargo e intenso, con sabores de naranja amarga, hierbas, cereza y un dulzor sutil que equilibra el amargor.\nFinal: Largo y persistente, con un retrogusto amargo que limpia el paladar y abre el apetito."
    ],
    [1551, 'Jerez Seco Valdespino Sello Azul',
        'Vino de Jerez seco de Bodegas Valdespino, una de las casas más antiguas del Marco de Jerez con orígenes en el siglo XIII. Sello Azul es un fino joven y fresco de crianza biológica bajo velo de flor. Valdespino sigue utilizando fermentación en bota, práctica casi extinta que aporta complejidad.',
        "Nariz: Aromas de almendra, levadura, manzanilla y un fondo salino con notas de masa de pan y cítricos.\nBoca: Seco y fresco, con sabores de almendra, cítricos, levadura y una salinidad marcada, textura limpia.\nFinal: Medio y seco, con un retrogusto almendrado que lo hace perfecto como aperitivo con aceitunas y jamón."
    ],
    [1552, 'Baileys Chocolat Luxe',
        'Licor de crema de whisky irlandés con chocolate belga de la casa Baileys. Versión premium del clásico Baileys, elaborada con crema fresca irlandesa, whiskey triple destilado y chocolate negro belga de primera calidad. Un licor goloso y opulento pensado para los amantes del chocolate y los momentos especiales.',
        "Nariz: Aromas intensos de chocolate negro belga, cacao, crema de leche, vainilla y un toque de café con notas de whiskey.\nBoca: Cremoso y opulento, con sabores de chocolate negro, cacao amargo, crema y un punto de whiskey, textura sedosa.\nFinal: Largo y goloso, con recuerdos de chocolate fundido y crema que perduran como un postre líquido."
    ],

    // ===== CERVEZAS (5) =====
    [1447, 'Cervesa Balate Nil',
        'Cerveza artesanal catalana de la cervecería Balate, micro-cervecera comprometida con la elaboración de cervezas de carácter con ingredientes de proximidad. Nil es un estilo propio de esta cervecera, que apuesta por recetas creativas y sabores diferenciados dentro del movimiento craft catalán.',
        "Nariz: Aromas de malta, lúpulos aromáticos, frutas y un toque de levadura con notas complejas.\nBoca: Equilibrada y sabrosa, con sabores de malta, fruta, un amargor lupulado bien integrado y matices artesanales.\nFinal: Medio y limpio, con recuerdos de malta y lúpulo que reflejan el carácter artesanal de Balate."
    ],
    [1448, 'Cervesa Balate Darro',
        'Cerveza artesanal catalana de la cervecería Balate. Darro es otra expresión de esta micro-cervecera que explora diferentes estilos cerveceros con materias primas de calidad. Elaborada con técnicas artesanales que privilegian el sabor y la complejidad sobre la producción en masa.',
        "Nariz: Aromas de cereales, malta tostada, lúpulos y notas frutales con un toque especiado.\nBoca: Maltosa y equilibrada, con sabores de cereales tostados, un amargor lupulado agradable y matices complejos.\nFinal: Medio y persistente, con recuerdos de malta y un retrogusto que invita al siguiente trago."
    ],
    [1450, 'Cervesa Viakrucis American IPA',
        'Cerveza artesanal catalana estilo American IPA, elaborada con generosa carga de lúpulos americanos que aportan su carácter amargo, cítrico y resinoso. Viakrucis forma parte del vibrante movimiento craft catalán que ha revitalizado la escena cervecera local con estilos importados de la tradición americana.',
        "Nariz: Aromas explosivos de pomelo, mango, piña, resina de pino y flores con un fondo de malta dorada.\nBoca: Amarga e intensa, con sabores de cítricos tropicales, fruta de la pasión, caramelo de malta y un amargor resinoso potente.\nFinal: Largo y amargo, con recuerdos de cítricos tropicales y resina que definen el estilo IPA americano."
    ],
    [1462, 'Cervesa Moritz 33cl Retornable',
        'Cerveza barcelonesa emblemática, elaborada por Moritz desde 1856 en la Ronda de Sant Antoni. Recuperada en 2004, Moritz combina tradición cervecera alsaciana con identidad barcelonesa. Elaborada con agua de su propio pozo, malta de cebada, lúpulo y maíz. Formato retornable comprometido con la sostenibilidad.',
        "Nariz: Aromas suaves de malta, cereales, un toque herbal de lúpulo y notas florales sutiles.\nBoca: Ligera y refrescante, con sabores equilibrados de malta, cereal y un amargor lupulado suave y agradable.\nFinal: Medio y limpio, con un retrogusto fresco que la convierte en el acompañamiento perfecto de la cocina mediterránea."
    ],
    [1490, 'Cervesa Free Damm 25cl',
        'Cerveza sin alcohol de Damm en formato pequeño (25 cl) no retornable. Elaborada siguiendo la tradición cervecera de Damm pero con un proceso que preserva el perfil aromático eliminando el contenido alcohólico. Una alternativa pensada para quienes quieren disfrutar del sabor cervecero sin alcohol.',
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
