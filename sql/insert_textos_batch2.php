<?php
require __DIR__ . '/../distribucio/connexio/connexio.php';
mysqli_set_charset($bd, 'utf8mb4');

$productos = [
    // === GINEBRAS ===
    [
        'id' => 2,
        'descripcion' => 'Ginebra London Dry destilada en el corazón de Londres desde 1863, en la histórica destilería de Kennington. Elaborada mediante una cuidadosa maceración de nueve botánicos seleccionados, entre los que destacan el enebro, la raíz de angélica, las semillas de cilantro y la piel de limón y naranja de Sevilla. Con 40% vol., es una ginebra de carácter firme y equilibrado que se ha convertido en referencia indiscutible del estilo London Dry en todo el mundo.',
        'nota_cata' => "Nariz: Intensa y fresca, con un marcado protagonismo del enebro acompañado de notas cítricas de limón y naranja amarga. Aparecen matices herbáceos sutiles de angélica y un fondo especiado de cilantro y pimienta blanca.\n\nBoca: Entrada limpia y seca con cuerpo medio. El enebro domina el paladar, seguido de una oleada cítrica vibrante y un toque de raíz de regaliz que aporta redondez. Textura sedosa con un punto de pimienta que activa el paladar.\n\nFinal: Medio-largo, seco y refrescante, con recuerdos persistentes de enebro y cáscara de cítricos. Cierre limpio y nítido."
    ],
    [
        'id' => 8,
        'descripcion' => 'Ginebra inglesa de carácter singular que combina la tradición destilatoria británica con la pureza del agua glacial de Islandia. Los botánicos se destilan en dos grupos separados — cítricos por un lado, especias y raíces por otro — para después ser mezclados con agua procedente de un manantial islandés, lo que le confiere una suavidad y frescura excepcionales. Entre sus botánicos más distintivos se encuentran la piel de pepino y la lima, que le otorgan un perfil aromático elegante y contemporáneo.',
        'nota_cata' => "Nariz: Delicada y aromática, con notas frescas de pepino y lima que se entrelazan con el enebro clásico. Se perciben toques florales y un sutil fondo terroso de raíz de angélica y casia.\n\nBoca: Entrada suave y aterciopelada, con una textura notablemente sedosa gracias a la calidad del agua. Sabores de cítricos frescos, pepino y un toque de corteza de canela se despliegan con elegancia. Equilibrio perfecto entre frescura y complejidad botánica.\n\nFinal: Largo y refrescante, con un regusto limpio de pepino, lima y enebro que invita a seguir degustando. Acabado suave sin asperezas."
    ],
    [
        'id' => 11,
        'descripcion' => 'Ginebra premium de estilo London Dry que destaca por su característico color azul turquesa natural, obtenido mediante la maceración de flores de gardenia. Destilada en alambique de cobre con una selección de doce botánicos de primera calidad, se embotella a 47% vol., una graduación que intensifica su complejidad aromática y permite apreciar plenamente la riqueza de sus ingredientes. Es una ginebra concebida para paladares exigentes que buscan distinción y profundidad.',
        'nota_cata' => "Nariz: Elegante y compleja, con un enebro refinado que se funde con notas florales de gardenia, lirio y violeta. Emergen aromas de bergamota, almendra y un delicado fondo especiado de canela y raíz de iris.\n\nBoca: Potente y envolvente, con un cuerpo generoso que refleja su graduación de 47%. Capas sucesivas de enebro, flores, cítricos y especias se revelan progresivamente. Textura oleosa y rica, con toques de almendra amarga y raíz de angélica que aportan estructura.\n\nFinal: Muy largo y persistente, con un recuerdo floral distintivo y un cierre especiado y ligeramente amargo que aporta sofisticación. Retrogusto de enebro y gardenia."
    ],

    // === WHISKIES ===
    [
        'id' => 13,
        'descripcion' => 'Blended Scotch Whisky que representa el legado de George Ballantine, quien fundó su negocio en Edimburgo en 1827. Esta expresión Finest es el resultado de ensamblar más de cincuenta whiskies de malta procedentes de las principales regiones escocesas junto con selectos whiskies de grano, creando un blend de notable complejidad y accesibilidad. Es un whisky versátil y bien equilibrado que ha sido galardonado en numerosas competiciones internacionales.',
        'nota_cata' => "Nariz: Suave y acogedora, con aromas de miel de brezo, manzana roja madura y vainilla. Se aprecian notas de roble tostado, un toque de turba delicada y matices florales que recuerdan a la lavanda. Fondo de cereales malteados y caramelo.\n\nBoca: Entrada redonda y cremosa, con sabores de miel, chocolate con leche y frutas de hueso. Se desarrollan notas de especias suaves — canela y nuez moscada — junto con un toque ahumado muy sutil. Cuerpo medio con una textura sedosa y envolvente.\n\nFinal: Medio, cálido y agradable, con recuerdos de vainilla, roble y un ligero toque de turba que se desvanece suavemente. Cierre dulce y equilibrado."
    ],
    [
        'id' => 16,
        'descripcion' => 'Bourbon de Kentucky elaborado en la destilería de Lawrenceburg, célebre por su método único de producción que combina dos recetas de macerado y cinco cepas de levadura propias, dando lugar a diez recetas distintas de bourbon que se ensamblan para crear su perfil final. Su marcado carácter floral, poco habitual en el mundo del bourbon, lo distingue del resto de destilados de su categoría. Es un bourbon suave y aromático que refleja décadas de maestría destiladora.',
        'nota_cata' => "Nariz: Fragante y floral, con notas de pétalos de rosa, fruta madura de albaricoque y ciruela, y un fondo dulce de miel y caramelo. Se perciben aromas de vainilla cremosa, cereza confitada y un sutil toque de roble especiado.\n\nBoca: Entrada suave y afrutada, con un cuerpo medio que despliega sabores de frutas rojas maduras, vainilla bourbon y caramelo de mantequilla. Aparecen matices de canela, clavo y un punto de centeno especiado que aporta vivacidad. Textura sedosa y bien integrada.\n\nFinal: Medio-largo, cálido y dulce, con un regusto persistente de frutas secas, vainilla y roble ligeramente tostado. Cierre elegante con un recuerdo floral característico de la marca."
    ],

    // === RONES ===
    [
        'id' => 24,
        'descripcion' => 'Ron blanco de carácter ligero y limpio, elaborado en Puerto Rico siguiendo la tradición fundada por Don Facundo Bacardí Massó en Santiago de Cuba en 1862. Su proceso de destilación en alambiques de columna y posterior filtrado a través de carbón de coco le confiere una suavidad y transparencia excepcionales. Es el ron de referencia mundial para coctelería, base indispensable del mojito, daiquirí y Cuba libre.',
        'nota_cata' => "Nariz: Aromas sutiles y frescos de vainilla, almendra tierna y un ligero toque cítrico con notas de lima y coco rallado.\n\nBoca: Entrada suave y ligera, con sabores delicados de caña de azúcar, frutas tropicales blancas y un matiz especiado apenas perceptible. Textura sedosa y muy limpia.\n\nFinal: Corto y refrescante, con un recuerdo dulce de vainilla y un acabado seco que invita a la mezcla."
    ],
    [
        'id' => 229,
        'descripcion' => 'Ron cubano de añejamiento prolongado, resultado de una cuidadosa selección de aguardientes madurados durante un mínimo de siete años en barricas de roble blanco bajo el clima tropical de la isla. Elaborado por los maestros roneros de Cuba siguiendo métodos artesanales transmitidos durante generaciones, representa la esencia de la tradición ronera caribeña. Su complejidad y redondez lo convierten en un ron idóneo para degustar solo o con un toque de hielo.',
        'nota_cata' => "Nariz: Intensa y envolvente, con aromas profundos de tabaco habano, cacao amargo, caramelo tostado y frutos secos. Notas de fondo de cuero noble, vainilla madura y un sutil toque de plátano asado.\n\nBoca: Entrada aterciopelada y con cuerpo, desplegando sabores de melaza, café expreso, especias dulces como canela y nuez moscada, y un elegante amargor de chocolate negro. Gran equilibrio entre dulzor y complejidad.\n\nFinal: Largo y cálido, con persistencia de madera noble, tabaco y un retrogusto especiado que perdura con gran armonía."
    ],

    // === BRANDIS ===
    [
        'id' => 193,
        'descripcion' => 'Brandy de Jerez Solera Gran Reserva elaborado por la prestigiosa bodega Williams & Humbert en el Marco de Jerez. Envejecido durante largos años mediante el tradicional sistema de criaderas y soleras en botas de roble americano que previamente albergaron vinos olorosos y Pedro Ximénez, lo que le aporta una riqueza y profundidad extraordinarias. Es considerado uno de los grandes brandis españoles, expresión máxima de la nobleza y el carácter del brandy jerezano.',
        'nota_cata' => "Nariz: Opulenta y compleja, con aromas de pasas de Corinto, higos secos, toffee y frutos de cáscara tostados. Notas elegantes de vainilla bourbon, cuero repujado, especias orientales y un fondo de madera vieja con toques de incienso.\n\nBoca: Entrada untuosa y envolvente, con gran cuerpo y una textura casi oleosa. Sabores intensos de fruta pasa, dátil, crema de cacao, nueces caramelizadas y una sutil nota de naranja amarga. Extraordinario equilibrio entre dulzor natural y complejidad.\n\nFinal: Muy largo y persistente, con recuerdos de madera noble, especias cálidas, Pedro Ximénez y un retrogusto elegante que se extiende con distinción durante varios minutos."
    ],
    [
        'id' => 32,
        'descripcion' => 'Brandy de Jerez Solera elaborado por González Byass, una de las bodegas más emblemáticas del Marco de Jerez, con una trayectoria que se remonta a 1835. Destilado a partir de vinos blancos de la región y envejecido en el sistema tradicional de soleras y criaderas en barricas que anteriormente contuvieron vino de Jerez, adquiere un carácter afable y reconocible. Es uno de los brandis más populares de España, apreciado por su accesibilidad y su perfil cálido.',
        'nota_cata' => "Nariz: Franca y directa, con aromas de uva madura, caramelo, vainilla sencilla y un ligero toque de madera joven. Notas de fondo de almendra tostada y un matiz floral discreto.\n\nBoca: Entrada cálida y amable, con cuerpo medio y sabores de fruta confitada, miel de romero, canela y un toque de regaliz. Buena redondez con un dulzor comedido y un punto de calidez alcohólica bien integrada.\n\nFinal: Medio, cálido y agradable, con recuerdos de madera, fruta seca y un acabado ligeramente especiado que deja una sensación reconfortante."
    ],

    // === TEQUILA ===
    [
        'id' => 126,
        'descripcion' => 'Tequila de estilo gold elaborado en la localidad de Tequila, Jalisco, por la casa José Cuervo, la destilería de tequila más antigua del mundo, fundada en 1795. Producido a partir de agave azul Weber cultivado en las tierras altas y valles jaliscienses, combina tequila joven con tequila reposado envejecido en barricas de roble, lo que le otorga su característico color dorado y su perfil suave. Es un referente universal del tequila mexicano.',
        'nota_cata' => "Nariz: Aromas característicos de agave cocido, con notas de pimienta blanca, cítricos maduros y un toque de caramelo suave. Fondo herbáceo con matices de tierra húmeda y vainilla aportada por la madera.\n\nBoca: Entrada con carácter, mostrando sabores de agave dulce, pimienta negra, toques de roble joven, miel y un sutil picante que recorre el paladar. Cuerpo medio con una textura ligeramente oleosa y buena presencia.\n\nFinal: Medio, con una calidez especiada agradable, recuerdos de agave asado y pimienta, y un acabado limpio con un punto dulce que equilibra el conjunto."
    ],

    // === JEREZ ===
    [
        'id' => 224,
        'descripcion' => 'Tío Pepe es el fino por excelencia de la D.O. Jerez-Xérès-Sherry, elaborado por la casa González Byass en sus históricas bodegas de Jerez de la Frontera desde mediados del siglo XIX. Producido íntegramente con uva Palomino Fino procedente de los suelos albarizas del pago de Macharnudo, se cría bajo un velo de flor viva mediante el sistema de criaderas y solera. Es un vino de una sequedad impecable y una pureza que lo han convertido en referencia mundial de su categoría.',
        'nota_cata' => "Nariz: Punzante y expresiva, con aromas de almendra cruda, levadura fresca de panadería y manzanilla de campo. Se aprecian notas salinas y yodadas que evocan la cercanía del Atlántico, junto con matices de aceitunas verdes, heno seco y un fondo de pedernal.\n\nBoca: Extremadamente seca y cortante, con una acidez vibrante que tensa el paladar. Sabores de almendra amarga, champiñón fresco y una marcada salinidad que le confiere una personalidad inconfundible. Ligero de cuerpo pero con una concentración y un nervio sorprendentes.\n\nFinal: Largo, amargo y salino, con un regusto de aceitunas y almendra tostada que persiste y deja el paladar limpio, seco y con una sensación casi etérea."
    ],

    // === VINOS ===
    [
        'id' => 221,
        'descripcion' => 'Elaborado con uva Tinta del País (Tempranillo) procedente de viñedos seleccionados en la Ribera del Duero, este tinto joven pasa aproximadamente cuatro meses en barricas de roble francés y americano. La bodega Protos, situada a los pies del castillo de Peñafiel, lleva décadas siendo referente de la denominación. Un vino que conjuga la expresión frutal de la variedad con un toque sutil de madera, resultando accesible y muy gastronómico.',
        'nota_cata' => "Nariz: Aromas intensos de fruta roja madura, como cereza y frambuesa, con notas de vainilla y un ligero fondo especiado aportado por la crianza en barrica.\n\nBoca: Entrada jugosa y de cuerpo medio, con taninos redondos y bien integrados. Predominan los sabores de fruta fresca acompañados de matices tostados y un punto de regaliz.\n\nFinal: Persistente y limpio, con recuerdos de fruta negra y un agradable toque balsámico que invita a seguir bebiendo."
    ],
    [
        'id' => 230,
        'descripcion' => 'Crianza clásica de la Rioja elaborada mayoritariamente con uva Tempranillo, sometida a doce meses de envejecimiento en barricas de roble americano y posterior afinamiento en botella. Bodegas El Coto de Rioja produce este vino como fiel representante del estilo tradicional riojano, buscando el equilibrio entre fruta y madera. Es un tinto versátil, de gran relación calidad-precio, ideal para el día a día.',
        'nota_cata' => "Nariz: Aroma complejo donde conviven la fruta madura —ciruela y mora— con notas de vainilla, coco y un sutil toque de cuero fino procedente de la crianza.\n\nBoca: Entrada suave y envolvente, de cuerpo medio con taninos pulidos por el tiempo en barrica. Sabores de fruta confitada, especias dulces y un fondo ligeramente ahumado que le confieren elegancia.\n\nFinal: De longitud media-alta, equilibrado y grato, con un retrogusto donde persisten la vainilla y la fruta madura."
    ],
    [
        'id' => 228,
        'descripcion' => 'Concebido por Álvaro Palacios como la puerta de entrada a los extraordinarios vinos del Priorat, este coupage combina Garnacha, Cariñena, Cabernet Sauvignon y Syrah cultivadas sobre los característicos suelos de pizarra (llicorella) de la denominación. Las cepas, repartidas en distintas parcelas de la comarca, aportan una expresión mineral y mediterránea inconfundible. Un vino que refleja el carácter salvaje y la profundidad del Priorat en un formato accesible y seductor.',
        'nota_cata' => "Nariz: Explosiva y aromática, con notas de garriga mediterránea, romero y tomillo, sobre un fondo de frutas negras maduras como la mora y la zarzamora. Aparecen matices minerales de piedra caliente y pizarra que delatan su origen.\n\nBoca: Potente pero sorprendentemente fresco, con una acidez bien definida que estructura el vino. Taninos firmes y de grano fino, con sabores de cereza negra, grafito y hierbas aromáticas. La mineralidad se hace presente de forma persistente.\n\nFinal: Largo y profundo, con un recuerdo mineral y balsámico que permanece en el paladar. Deja una sensación de frescor y complejidad notable para un vino de su gama."
    ],
    [
        'id' => 208,
        'descripcion' => 'Vino blanco fresco y desenfadado elaborado por Perelada bajo la D.O. Catalunya, pensado para acompañar los sabores del Mediterráneo. Ligeramente aguja en su versión tradicional, es un blanco ligero que transmite alegría y frescura en cada copa. Su nombre evoca las tradiciones marineras de la Costa Brava y se ha convertido en todo un clásico de las mesas veraniegas españolas.',
        'nota_cata' => "Nariz: Delicada y frutal, con aromas de manzana verde, cítricos como el limón y la lima, y un sutil fondo floral de azahar. Notas herbáceas frescas que recuerdan al heno recién cortado.\n\nBoca: Ligera y vivaz, con una acidez refrescante y una agradable sensación de aguja que le aporta dinamismo. Sabores de frutas blancas y un punto salino que evoca la brisa marina.\n\nFinal: Breve y limpio, con un cierre fresco y ligeramente amargo que resulta muy apetecible. Perfecto para beber bien frío como aperitivo o junto a pescados y mariscos."
    ],
    [
        'id' => 203,
        'descripcion' => 'Blanco histórico de Rioja elaborado por la Compañía Vinícola del Norte de España (CVNE), una de las bodegas más emblemáticas de la denominación, fundada en 1879. Producido fundamentalmente con uva Viura, Monopole es un referente del estilo de blanco riojano clásico, fermentado en depósito de acero inoxidable para preservar toda su frescura y pureza varietal. Es un vino que ha sabido mantener su identidad a lo largo de más de un siglo de historia.',
        'nota_cata' => "Nariz: Limpia y elegante, con aromas de fruta blanca como la pera y la manzana, acompañados de notas de flores blancas y un toque de hinojo. Fondo ligeramente anisado con matices cítricos.\n\nBoca: Seca y bien estructurada, con una acidez vibrante que le confiere nervio y frescura. Sabores de fruta de hueso, cítricos maduros y un leve punto almendrado que aporta complejidad. Textura sedosa y equilibrada.\n\nFinal: De longitud media, limpio y refrescante, con un agradable recuerdo de manzana verde y un toque mineral que le otorga distinción. Muy gastronómico."
    ],

    // === CAVAS ===
    [
        'id' => 202,
        'descripcion' => 'Elaborado por la familia Gramona en el corazón del Penedès desde 1881, este Gran Reserva es fruto de un largo proceso de crianza en botella siguiendo el método tradicional. El coupage de Xarel·lo, Macabeo y Parellada, variedades autóctonas de la región, aporta una complejidad excepcional que solo se alcanza con paciencia y saber hacer artesanal. Un cava que refleja la filosofía de una de las casas más respetadas de la denominación, donde el tiempo es el ingrediente principal.',
        'nota_cata' => "Nariz: Aromas elegantes de fruta blanca madura, corteza de pan tostado y notas de frutos secos como la avellana, con un sutil fondo de miel de azahar y matices minerales que revelan su prolongada crianza.\n\nBoca: Entrada cremosa y envolvente, con una burbuja fina y perfectamente integrada. Se despliegan sabores de manzana asada, brioche y un toque cítrico que aporta frescura. La acidez está magistralmente equilibrada con la untuosidad que otorga el tiempo sobre lías.\n\nFinal: Largo, persistente y sofisticado, con un recuerdo de almendra tostada y un delicado amargor mineral que invita a seguir bebiendo."
    ],
    [
        'id' => 210,
        'descripcion' => 'Procedente de la pequeña y privilegiada D.O. Alella, en las colinas litorales al norte de Barcelona, Parxet elabora este brut con un ensamblaje de Macabeo, Parellada y Pansa Blanca, esta última variedad emblemática de la zona. Los suelos graníticos y la influencia mediterránea confieren al cava un carácter mineral y una frescura singulares. Es la expresión de un territorio íntimo y costero trasladada a la copa con elegancia.',
        'nota_cata' => "Nariz: Delicado y expresivo, con aromas de flor blanca, pera de agua y cítricos como el pomelo rosa, acompañados de una nota mineral calcárea que recuerda la brisa marina y un ligero toque herbáceo.\n\nBoca: Fresca y vivaz, con una burbuja persistente y de grano fino. Predominan los sabores de manzana verde, lima y un fondo salino que le otorga personalidad. De cuerpo ligero a medio, con una acidez vibrante que lo hace especialmente gastronómico.\n\nFinal: Limpio, seco y refrescante, con un agradable recuerdo de cítricos maduros y ese sello mineral tan característico de los vinos de Alella."
    ],

    // === CERVEZA ===
    [
        'id' => 110,
        'descripcion' => 'Xibeca es una cerveza tipo lager elaborada por la histórica cervecera Damm en Barcelona, una marca profundamente arraigada en la cultura popular catalana. Ligera y de carácter sencillo, fue concebida como la cerveza del día a día, pensada para acompañar los momentos cotidianos con naturalidad. Su perfil accesible y refrescante la ha convertido en un clásico de las mesas y terrazas del litoral mediterráneo.',
        'nota_cata' => "Nariz: Aromas suaves y discretos de cereal malteado, con notas de pan blanco recién horneado y un leve apunte floral procedente del lúpulo, sin estridencias.\n\nBoca: Ligera y muy refrescante, con una carbonatación moderada que la hace fácil de beber. Sabor limpio a malta clara con un punto dulce equilibrado por un amargor suave y contenido. Textura acuosa y desenfadada, ideal para climas cálidos.\n\nFinal: Corto y seco, con un cierre limpio y un sutil recuerdo a grano tostado que deja el paladar preparado para el siguiente trago."
    ],

    // === RATAFIA ===
    [
        'id' => 113,
        'descripcion' => 'La Ratafia Russet es un licor artesanal catalán elaborado según una receta tradicional que hunde sus raíces en la cultura rural de Cataluña. Su base parte de la maceración de nueces verdes recolectadas por San Juan junto con una cuidada selección de hierbas aromáticas y especias autóctonas del territorio. El resultado es un licor de carácter único, complejo y reconfortante, que representa una de las tradiciones gastronómicas más singulares del Mediterráneo occidental.',
        'nota_cata' => "Nariz: Intensa y envolvente, con aromas profundos de nuez verde, canela en rama, clavo de olor y hierbas de montaña como el romero y el tomillo. Se perciben también notas de anís, menta y un fondo balsámico que evoca el paisaje de los bosques catalanes.\n\nBoca: Dulce pero equilibrada, con una textura untuosa y densa. Estallan sabores de fruta seca, miel de bosque, especias cálidas y un amargor herbáceo noble que aporta complejidad. El alcohol está bien integrado y se funde con los matices botánicos de forma armoniosa.\n\nFinal: Largo y especiado, con un persistente recuerdo de nuez, regaliz y hierbas medicinales que deja una sensación cálida y reconfortante en el paladar."
    ],
];

$ok = 0;
$errors = 0;

foreach ($productos as $p) {
    $id = $p['id'];

    // Descripción
    $desc = mysqli_real_escape_string($bd, $p['descripcion']);
    $sql = "INSERT INTO producto_textos (idProducto, tipo, contenido) VALUES ($id, 'descripcion', '$desc')
            ON DUPLICATE KEY UPDATE contenido = '$desc'";
    if (mysqli_query($bd, $sql)) {
        $ok++;
    } else {
        echo "ERROR desc ID $id: " . mysqli_error($bd) . "\n";
        $errors++;
    }

    // Nota de cata
    $nota = mysqli_real_escape_string($bd, $p['nota_cata']);
    $sql = "INSERT INTO producto_textos (idProducto, tipo, contenido) VALUES ($id, 'nota_cata', '$nota')
            ON DUPLICATE KEY UPDATE contenido = '$nota'";
    if (mysqli_query($bd, $sql)) {
        $ok++;
    } else {
        echo "ERROR nota ID $id: " . mysqli_error($bd) . "\n";
        $errors++;
    }
}

echo "Insertados: $ok OK, $errors errores\n";
