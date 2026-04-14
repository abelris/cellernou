<?php
/**
 * Script para insertar descripciones y notas de cata de 20 productos
 * Ejecutar una sola vez: php sql/insert_textos_20productos.php
 */
require __DIR__ . '/../distribucio/connexio/connexio.php';

$productos = [
    // === GINEBRAS ===
    [
        'id' => 10,
        'descripcion' => 'Ginebra escocesa de carácter singular, elaborada en la destilería Girvan (Ayrshire, Escocia) mediante una inusual combinación de dos alambiques: un Carter-Head y un Bennett de cobre. Su receta incorpora once botánicos clásicos, pero lo que verdaderamente la distingue es la infusión final de pétalos de rosa búlgara y pepino, que le confiere una personalidad floral y refrescante sin igual. Embotellada a 41,4% vol., se presenta en su icónica botella oscura de estilo farmacéutico victoriano.',
        'nota_cata' => 'Nariz: Aroma delicado y elegante, con notas florales de rosa que se entrelazan con matices frescos de pepino y un sutil fondo de enebro y cítricos. Boca: Entrada sedosa y suave, con un cuerpo medio donde predominan las notas herbáceas y florales; el pepino aporta una frescura cremosa mientras el enebro mantiene el carácter clásico de ginebra. Final: Persistente y equilibrado, con un retrogusto limpio donde la rosa y un toque especiado se desvanecen lentamente.',
    ],
    [
        'id' => 4,
        'descripcion' => 'Ginebra premium de estilo London Dry, elaborada en la destilería de Laverstoke Mill (Hampshire, Inglaterra) mediante el exclusivo proceso de infusión por vapor, en el que los botánicos no se hierven en el alcohol sino que el vapor pasa a través de ellos, extrayendo aromas más delicados y sutiles. Su receta emplea diez botánicos procedentes de distintos rincones del mundo, entre los que destacan el enebro de Italia, las almendras de Marruecos, la corteza de limón de España, el regaliz de China y las bayas de cubeba de Java.',
        'nota_cata' => 'Nariz: Aroma luminoso y complejo, con el enebro en primer plano acompañado de notas cítricas vibrantes de limón, pimienta blanca y un fondo sutil de almendra y regaliz. Boca: Entrada limpia y elegante, con un paladar especiado donde el cilantro y la pimienta cubeba aportan vivacidad; los cítricos y las notas florales le dan amplitud y frescura. Final: Seco y prolongado, con un recuerdo cálido de especias exóticas y un toque resinoso de enebro que perdura.',
    ],
    [
        'id' => 5,
        'descripcion' => 'Clásica ginebra London Dry nacida en 1830 de la mano de Charles Tanqueray en el distrito londinense de Bloomsbury, destilada actualmente en la histórica destilería de Cameronbridge (Escocia). Su fórmula, celosamente guardada durante casi dos siglos, se basa en tan solo cuatro botánicos esenciales: enebro, cilantro, raíz de angélica y regaliz, destilados cuatro veces para alcanzar una pureza y equilibrio excepcionales. La versión Export Strength, embotellada a 43,1% vol., ofrece mayor intensidad y estructura.',
        'nota_cata' => 'Nariz: Potente e inmediato aroma a enebro fresco, con un marcado carácter resinoso acompañado de cilantro y un fondo terroso de angélica. Boca: Entrada firme y con cuerpo, donde el enebro domina con autoridad; las especias aportan profundidad mientras la raíz de angélica proporciona estructura seca y el regaliz una pincelada dulce apenas perceptible. Final: Largo, seco y limpio, con el enebro persistiendo de forma nítida junto a un leve calor especiado.',
    ],
    [
        'id' => 7,
        'descripcion' => 'Ginebra London Dry de espíritu contemporáneo, cuádruple destilada en Inglaterra a partir de una selección de doce botánicos exóticos que incluyen ingredientes poco convencionales como la hoja de loto, el ojo de dragón (longan), la semilla de amapola y la lavanda, además de los clásicos enebro, corteza de limón y raíz de cassia. Su ambición es ofrecer una ginebra equilibrada y versátil que trascienda los límites del estilo clásico.',
        'nota_cata' => 'Nariz: Aroma suave y accesible, con notas cítricas de limón y pomelo que se funden con matices florales de lavanda y un enebro contenido. Boca: Textura redonda y aterciopelada; en el paladar se percibe una elegante combinación de cítricos, notas herbales sutiles y un toque exótico aportado por el longan y la semilla de amapola, que le confieren una dulzura vegetal muy equilibrada. Final: Medio y limpio, con un recuerdo fresco de cítricos y un suave toque floral.',
    ],
    [
        'id' => 9,
        'descripcion' => 'Ginebra francesa de extraordinaria singularidad, elaborada en la región de Cognac a partir de un espíritu base de uva (ugni blanc), lo que la diferencia radicalmente de las ginebras convencionales destiladas con alcohol de cereal. Su botánico estrella es la flor de la vid, recolectada a mano durante el breve período de floración estival y macerada junto a otros nueve botánicos como el enebro, la raíz de jengibre, la nuez moscada, la lima y las bayas de cubeba. El nombre "Floraison" (floración en francés) alude a ese momento efímero de la vid.',
        'nota_cata' => 'Nariz: Delicadísima y envolvente, con un bouquet floral donde la flor de vid se entrelaza con notas de lima fresca, jengibre suave y un enebro discreto; recuerda a un paseo por un viñedo en primavera. Boca: Entrada untuosa y redonda gracias a la base de uva; las notas florales se despliegan con amplitud junto a matices cítricos y un punto especiado de nuez moscada y cardamomo. Final: Largo, sedoso y aromático, con un retrogusto elegante donde la flor de vid y el jengibre se funden en una despedida cálida.',
    ],

    // === WHISKIES ===
    [
        'id' => 17,
        'descripcion' => 'Destilado emblemático de Lynchburg, Tennessee (Estados Unidos), elaborado desde 1866 por la destilería registrada más antigua del país. Su proceso de producción se distingue por el "Lincoln County Process", un filtrado gota a gota a través de carbón de arce sacarino antes del envejecimiento, lo que lo diferencia técnicamente de un bourbon convencional. Se elabora con una mezcla de maíz (80%), cebada malteada y centeno, y envejece en barricas nuevas de roble americano carbonizadas.',
        'nota_cata' => 'Nariz: Notas dulces de caramelo y vainilla, con matices de plátano maduro, un ligero toque ahumado procedente del carbón de arce y un fondo de madera tostada. Boca: Entrada suave y sedosa gracias al filtrado por carbón, con sabores de caramelo, roble dulce y especias suaves como canela y pimienta blanca. Cuerpo medio con una textura notablemente redonda. Final: De longitud media, con un retrogusto cálido donde persisten la vainilla, un sutil ahumado y un punto de dulzor a cereales tostados.',
    ],
    [
        'id' => 19,
        'descripcion' => 'Single malt escocés procedente de la destilería Cardhu, situada en el corazón de la región de Speyside, en las Tierras Altas de Escocia. Fundada en 1824, la destilería tiene una historia singular vinculada a Helen Cumming, pionera en la destilación legal de whisky. El Cardhu 12 años madura exclusivamente en barricas de roble americano ex-bourbon, lo que le confiere un perfil elegante y accesible que además constituye uno de los maltas fundamentales en la composición de Johnnie Walker.',
        'nota_cata' => 'Nariz: Fragante y limpio, con notas de pera madura, malta dulce y miel de flores. Aparecen sutiles toques de almendra tostada y un fondo herbal delicado. Boca: Sedoso y de cuerpo medio-ligero, con sabores de malta con miel, manzana al horno y un toque de nuez moscada. Dulzor equilibrado con una agradable nota de cereales dorados. Final: Medio, limpio y seco, con un recuerdo a malta tostada, un toque de jengibre y un retrogusto suavemente especiado.',
    ],

    // === LICORES ===
    [
        'id' => 31,
        'descripcion' => 'Licor de crema irlandesa creado en 1974, pionero y líder mundial en su categoría. Se elabora combinando whiskey irlandés de triple destilación con crema fresca de leche de vacas irlandesas, cacao, vainilla y azúcar. La crema se obtiene de granjas cercanas a la planta de producción y se integra con el whiskey en las primeras 48 horas tras el ordeño, garantizando frescura máxima. Tiene una graduación de 17% vol.',
        'nota_cata' => 'Nariz: Intensamente cremoso, con notas dominantes de chocolate con leche, vainilla dulce, un toque de café y un sutil fondo del whiskey irlandés que aporta calidez. Boca: Textura aterciopelada y envolvente, con sabores de nata, cacao, caramelo y vainilla, junto a la presencia elegante del whiskey que equilibra el dulzor y aporta estructura. Final: Largo y cremoso, con persistencia del chocolate y la vainilla, un calor suave del whiskey y un ligero amargor del cacao.',
    ],
    [
        'id' => 30,
        'descripcion' => 'Licor de naranja de categoría premium creado en 1875 por Edouard Cointreau en Angers (Francia). Se elabora mediante la destilación en alambiques de cobre de pieles de naranja amarga de origen tropical y naranja dulce de la cuenca mediterránea, combinando cortezas frescas y desecadas para maximizar la complejidad aromática. Cristalino e incoloro, con 40% vol., es considerado la referencia mundial del triple sec y un ingrediente indispensable en coctelería clásica.',
        'nota_cata' => 'Nariz: Explosivo e inmediato, con una intensidad cítrica de piel de naranja fresca, notas florales de azahar y un fondo ligeramente herbal y especiado. Boca: Entrada dulce pero equilibrada por un elegante amargor de la naranja amarga. Textura sedosa, con sabores concentrados de naranja confitada y una frescura casi mentolada. Equilibrio perfecto entre dulzor y amargor. Final: Largo, seco y refrescante, con una persistencia aromática de corteza de naranja que se desvanece limpiamente.',
    ],
    [
        'id' => 116,
        'descripcion' => 'Bitter italiano emblemático creado en 1860 por Gaspare Campari en Novara, y posteriormente asociado a la ciudad de Milán, donde se convirtió en símbolo del aperitivo italiano. Se elabora mediante la infusión y maceración de una mezcla secreta de más de 60 ingredientes que incluyen hierbas aromáticas, especias, cortezas, raíces y frutas, destacando la quinina y la naranja amarga. Con su inconfundible color rojo intenso y 25% vol., es la base del Negroni y el Spritz.',
        'nota_cata' => 'Nariz: Complejo e intrigante, con notas dominantes de naranja amarga, pomelo rojo y hierbas medicinales. Aparecen matices de cereza, especias como clavo y canela, y un fondo balsámico-resinoso. Boca: Entrada amarga franca y directa, que da paso a capas de sabor: corteza de cítricos amargos, notas herbales de genciana y quinina, un toque frutal de cereza y un dulzor subyacente que equilibra la amargura. Final: Largo y persistentemente amargo, con un retrogusto donde permanecen la naranja amarga y las hierbas.',
    ],

    // === BRANDIES ===
    [
        'id' => 35,
        'descripcion' => 'Brandy de Jerez elaborado por la familia Sánchez Romate en la localidad gaditana de Jerez de la Frontera, con una tradición que se remonta a 1887. Se produce a partir de destilados de uva Airén, envejecidos mediante el sistema de solera y criaderas en botas de roble americano que previamente han contenido Oloroso y Pedro Ximénez. Su prolongada crianza, con una edad media superior a los 15 años, le confiere una extraordinaria complejidad y suavidad.',
        'nota_cata' => 'Nariz: Aromas intensos de frutos secos tostados, pasas, vainilla y caramelo, con matices de especias dulces como canela y cacao, envueltos en un elegante fondo de madera noble. Boca: Aterciopelado, untuoso y redondo, con sabores profundos de orejones, higos secos y tofé, sostenidos por una estructura cálida y bien integrada. Final: Largo, persistente y envolvente, con un recuerdo dulce de Pedro Ximénez y un sutil toque de roble tostado.',
    ],
    [
        'id' => 34,
        'descripcion' => 'Brandy Solera Reserva elaborado por Bodegas Torres en Cataluña, una de las casas vinícolas más prestigiosas de España. Se produce a partir de destilados de vinos seleccionados de uvas Parellada y Macabeo del Penedès, envejecidos en barricas de roble americano mediante el tradicional sistema de solera. Con un mínimo de dos años de crianza y 38% de volumen alcohólico, Torres 5 representa la puerta de entrada a la gama de brandies Torres.',
        'nota_cata' => 'Nariz: Aromas frescos y afrutados de manzana madura, melocotón y albaricoque, acompañados de suaves notas de vainilla, almendra y un ligero toque floral. Boca: Suave y equilibrado, con una entrada frutal que da paso a sabores de fruta blanca confitada y madera fina, con una agradable calidez bien integrada. Final: Limpio, de intensidad media, con un recuerdo frutal y ligeramente especiado.',
    ],

    // === VODKA ===
    [
        'id' => 12,
        'descripcion' => 'Vodka sueco de calidad premium producido en la localidad de Åhus, en la región de Escania, al sur de Suecia, donde se elabora ininterrumpidamente desde 1879. Se destila a partir de trigo de invierno cultivado localmente y agua de un pozo profundo protegido, utilizando un proceso de destilación continua desarrollado por Lars Olsson Smith que elimina impurezas sin necesidad de filtración con carbón. A diferencia de muchos vodkas, Absolut no añade azúcar ni otros aditivos.',
        'nota_cata' => 'Nariz: Sutil y limpio, con delicadas notas de grano de trigo fresco, un leve matiz cítrico y un fondo ligeramente mineral. Boca: Sedoso y de textura media, con un perfil neutro pero no exento de carácter: aparecen matices de pan blanco, un toque de anís y una ligera dulzura cereal. Final: Suave, cálido y relativamente corto, con una sensación limpia y un sutil recuerdo a grano tostado que invita a su uso tanto solo como en coctelería.',
    ],

    // === VERMUTS ===
    [
        'id' => 20,
        'descripcion' => 'Vermut rojo italiano creado en 1863 por la destilería Martini & Rossi en la ciudad de Turín, cuna histórica de esta bebida. Se elabora a partir de una base de vino blanco italiano infusionado con una mezcla secreta de más de 40 hierbas, especias y botánicos que incluyen artemisia, genciana, canela y corteza de cítricos. Con 15% de volumen alcohólico, es el vermut rojo más reconocido a nivel mundial y piedra angular del célebre cóctel Negroni y del Manhattan.',
        'nota_cata' => 'Nariz: Aromas complejos de hierbas aromáticas, corteza de naranja amarga, vainilla y especias dulces, con un fondo herbal donde destaca el ajenjo. Boca: Dulce pero equilibrado por un amargor herbal elegante, con sabores de caramelo, raíz de genciana, clavo y cítricos confitados, todo ello envuelto en una textura suave y ligeramente viscosa. Final: De longitud media, amargo-dulce, con un persistente recuerdo de botánicos y un toque de naranja sanguina.',
    ],
    [
        'id' => 21,
        'descripcion' => 'Vermut blanco italiano lanzado en 1910 por Martini & Rossi como una alternativa más suave y aromática al clásico vermut seco. Se elabora sobre una base de vino blanco italiano, aromatizado con una selección de botánicos que incluyen flores de saúco, artemisia, vainilla y cítricos, otorgándole un perfil más dulce y floral que su homólogo seco. Con 15% de volumen alcohólico, es especialmente apreciado servido frío con hielo y una rodaja de limón.',
        'nota_cata' => 'Nariz: Destaca por su carácter floral y aromático, con notas de flores blancas, saúco, vainilla y cítricos frescos como limón y pomelo, acompañados de un sutil fondo herbal. Boca: Suave, dulce y refrescante, con sabores de vainilla, cítricos, frutas blancas y un delicado toque de hierbas aromáticas que aportan equilibrio sin resultar amargo. Final: Limpio, fresco y de longitud media, con un agradable recuerdo floral y cítrico.',
    ],

    // === VINOS ===
    [
        'id' => 105,
        'descripcion' => 'Tinto elaborado por Bodegas Ramón Bilbao, fundadas en 1924 en Haro, en el corazón de la Rioja Alta. Producido principalmente con uva Tempranillo, este crianza envejece durante un mínimo de 12 meses en barricas de roble americano y francés, seguido de un período de afinamiento en botella. Representa fielmente el estilo clásico riojano, conjugando fruta madura con las notas especiadas y tostadas que aporta la madera.',
        'nota_cata' => 'Nariz: Aromas intensos de fruta roja madura — cereza, ciruela — entrelazados con matices de vainilla, coco y un sutil toque de especias dulces procedentes de la crianza en barrica. Boca: Equilibrado, con taninos suaves y bien integrados, cuerpo medio y una acidez fresca que le aporta vivacidad. Final: Persistente y agradable, con recuerdos de fruta confitada y un leve fondo tostado.',
    ],
    [
        'id' => 54,
        'descripcion' => 'Vino tinto singular elaborado por Celler del Roure, bodega situada en la comarca de la Vall dels Alcalans, en el interior de Valencia, reconocida por su labor de recuperación de variedades autóctonas valencianas. Se elabora con uvas como Mandó, Monastrell y otras variedades locales cultivadas en suelos calcáreos y viñedos viejos, fermentadas en tinajas de barro siguiendo métodos tradicionales de la región. Un vino con fuerte identidad territorial y carácter mediterráneo auténtico.',
        'nota_cata' => 'Nariz: Expresión aromática compleja de fruta negra madura, notas florales de violeta, hierbas aromáticas mediterráneas y un fondo mineral y terroso. Boca: Destaca por su frescura sorprendente, con taninos finos y sedosos, buena estructura y una acidez vibrante que equilibra la concentración frutal. Final: Largo y elegante, con un retrogusto mineral y especiado que invita a seguir bebiendo.',
    ],
    [
        'id' => 55,
        'descripcion' => 'Vino español cuyo nombre, de origen latino, hace referencia a los ríos. Se trata de un vino que pone en valor el terroir y las variedades de su zona de producción, elaborado con un enfoque que busca expresar la personalidad del paisaje vitícola local. Su producción se enmarca dentro de la corriente de vinos de autor españoles que priorizan la autenticidad y la expresión varietal por encima de las tendencias internacionales.',
        'nota_cata' => 'Nariz: Aromas de fruta fresca y madura, con apuntes florales y un fondo de hierbas aromáticas. Boca: Franco y expresivo, con buena estructura, taninos amables y una acidez equilibrada que aporta longitud. Final: Limpio y persistente, con un recuerdo frutal y ligeramente mineral.',
    ],
    [
        'id' => 63,
        'descripcion' => 'Vino tinto elaborado en Catalunya, vinculado a la D.O. Montsant. Producido con variedades como Garnacha y Cariñena, uvas emblemáticas de las comarcas del sur de Catalunya, cultivadas en viñedos de suelos pizarrosos y calcáreos con clima mediterráneo continental. Su nombre en catalán significa "soñador" y es un vino de vocación honesta y directa, pensado para disfrutar con naturalidad, que refleja el carácter agreste y mineral de su territorio.',
        'nota_cata' => 'Nariz: Se expresa con aromas de fruta roja y negra del bosque — mora, frambuesa, grosella — acompañados de notas minerales de pizarra, especias suaves y un toque de garriga (romero, tomillo). Boca: Jugoso y goloso, con taninos presentes pero bien pulidos, cuerpo medio-alto y una acidez refrescante. Final: Sabroso y mineral, con una persistencia media-larga y un agradable recuerdo frutal y balsámico.',
    ],

    // === LIMONCELLO ===
    [
        'id' => 40,
        'descripcion' => 'Limoncello artesanal producido en la Península Sorrentina, al sur de Italia, región célebre por sus limoneros centenarios. Se elabora exclusivamente con limones I.G.P. de Sorrento, caracterizados por su gran tamaño, corteza gruesa y extraordinaria riqueza en aceites esenciales. El proceso consiste en la maceración de las cortezas de limón — peladas a mano para evitar la parte blanca amarga — en alcohol puro de grano durante varias semanas, obteniéndose un licor de color amarillo intenso, natural y sin colorantes añadidos.',
        'nota_cata' => 'Nariz: Estalla con un aroma fresco, vibrante e inmediato de limón recién cortado, acompañado de notas florales cítricas y un sutil fondo de corteza confitada. Boca: Suave y envolvente, con un dulzor bien calibrado que no resulta empalagoso, equilibrado por una vivaz acidez cítrica natural. Final: Largo, refrescante y limpio, con un persistente recuerdo a limón maduro mediterráneo que deja el paladar fresco y aromatizado.',
    ],
];

$inserted = 0;
$errors = 0;

foreach ($productos as $p) {
    $idProducto = (int)$p['id'];

    // Insertar descripcion
    if (!empty($p['descripcion'])) {
        $contenido = mysqli_real_escape_string($bd, $p['descripcion']);
        $sql = "INSERT INTO producto_textos (idProducto, tipo, contenido)
                VALUES ($idProducto, 'descripcion', '$contenido')
                ON DUPLICATE KEY UPDATE contenido = '$contenido'";
        if (mysqli_query($bd, $sql)) {
            $inserted++;
        } else {
            echo "ERROR desc producto $idProducto: " . mysqli_error($bd) . "\n";
            $errors++;
        }
    }

    // Insertar nota de cata
    if (!empty($p['nota_cata'])) {
        $contenido = mysqli_real_escape_string($bd, $p['nota_cata']);
        $sql = "INSERT INTO producto_textos (idProducto, tipo, contenido)
                VALUES ($idProducto, 'nota_cata', '$contenido')
                ON DUPLICATE KEY UPDATE contenido = '$contenido'";
        if (mysqli_query($bd, $sql)) {
            $inserted++;
        } else {
            echo "ERROR nota producto $idProducto: " . mysqli_error($bd) . "\n";
            $errors++;
        }
    }
}

echo "Completado: $inserted registros insertados, $errors errores.\n";
