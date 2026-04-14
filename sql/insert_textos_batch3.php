<?php
require __DIR__ . '/../distribucio/connexio/connexio.php';
mysqli_set_charset($bd, 'utf8mb4');

$productos = [
    // === GINEBRAS ===
    [
        'id' => 1,
        'descripcion' => 'Ginebra española elaborada en Málaga desde mediados del siglo XX, Larios se ha convertido en un referente indiscutible del estilo mediterráneo. Su proceso de destilación emplea una cuidadosa selección de botánicos entre los que destacan el enebro, la semilla de cilantro y la piel de naranja y limón. El resultado es una ginebra suave, luminosa y versátil, concebida para armonizar con los sabores cítricos de la costa sur peninsular.',
        'nota_cata' => "Nariz: Aromas frescos y limpios de enebro maduro, acompañados de notas cítricas de limón y mandarina. Se perciben matices herbáceos sutiles y un ligero toque floral que aporta delicadeza al conjunto.\n\nBoca: Entrada suave y sedosa con un cuerpo ligero. El enebro aparece bien integrado junto a sabores de piel de naranja amarga y un punto especiado de cilantro. Textura amable y refrescante, con una acidez cítrica que mantiene la vivacidad.\n\nFinal: Acabado corto a medio, limpio y agradable, con un recuerdo persistente de cítricos mediterráneos y un sutil amargor herbal que invita al siguiente sorbo."
    ],
    [
        'id' => 3,
        'descripcion' => 'Master\'s es una ginebra premium española elaborada siguiendo el riguroso método London Dry, donde todos los botánicos se destilan conjuntamente sin adición posterior de aromas o azúcares. Producida mediante triple destilación en alambiques de cobre tradicionales, su receta incorpora enebro de alta montaña, raíz de angélica, cardamomo y cáscara de naranja entre sus ingredientes principales. Su perfil seco y estructurado la convierte en una base excepcional tanto para un gin-tonic sofisticado como para coctelería clásica.',
        'nota_cata' => "Nariz: Intenso aroma de enebro fresco que domina la primera impresión, seguido de notas especiadas de cardamomo verde y pimienta blanca. En un segundo plano emergen matices de raíz de angélica, piel de cítricos secos y un sutil recuerdo a regaliz.\n\nBoca: Ataque directo y seco, con cuerpo medio y una textura untuosa que recubre el paladar. Los sabores de enebro y especias se entrelazan con toques de naranja amarga y un fondo terroso de raíces. La sequedad característica del estilo London Dry se manifiesta con nitidez.\n\nFinal: Largo y persistente, con un retrogusto donde el enebro y el cardamomo se funden en un amargor noble. Deja una sensación cálida y especiada que se prolonga con elegancia."
    ],
    [
        'id' => 120,
        'descripcion' => 'Bombay Sapphire es una ginebra de renombre mundial, reconocible al instante por su emblemática botella azul. Lo que la distingue fundamentalmente es su método de infusión por vapor: en lugar de hervir los botánicos directamente en el alcohol, los vapores de la destilación ascienden a través de una cesta de cobre que contiene los diez ingredientes seleccionados, extrayendo sus esencias de forma delicada y precisa. El proceso garantiza una ginebra luminosa, aromática y extraordinariamente equilibrada.',
        'nota_cata' => "Nariz: Bouquet elegante y complejo donde el enebro se presenta contenido y armonioso. Destacan las notas florales de iris y lavanda, entrelazadas con matices de piel de limón fresca, almendra amarga y un sutil toque de pimienta exótica. Conforme se abre en la copa, aparecen capas de cilantro y casia.\n\nBoca: Entrada sedosa y envolvente, con un cuerpo medio que transmite ligereza sin perder presencia. Los sabores cítricos y las especias orientales se despliegan en capas sucesivas sobre un fondo de enebro contenido. La textura es casi cremosa, con una frescura vibrante.\n\nFinal: Medio a largo, elegante y perfumado, con un recuerdo persistente de especias cálidas y cítricos que se desvanecen gradualmente dejando una estela floral delicada y un punto de sequedad mineral."
    ],

    // === VODKA ===
    [
        'id' => 227,
        'descripcion' => 'Absolut es un vodka sueco con raíces profundas en la localidad de Åhus, en la región de Escania, donde se elabora de manera ininterrumpida desde 1879. Su método de producción se basa en la destilación continua, un proceso revolucionario introducido por Lars Olsson Smith que permite alcanzar una pureza excepcional. La materia prima es trigo de invierno cultivado en los campos circundantes y agua de un pozo profundo protegido de cualquier contaminación externa.',
        'nota_cata' => "Nariz: Aroma limpio y transparente con sutiles notas de grano de trigo tostado. Se perciben matices delicados de pan blanco recién horneado, un leve toque de cítricos y una frescura etérea casi imperceptible que denota su elevada pureza.\n\nBoca: Entrada suave y cristalina, con un cuerpo medio y una textura sedosa que se desliza por el paladar. Se detectan sabores sutiles de cereal, un punto de dulzor contenido y notas de fruta blanca madura. El carácter neutro y equilibrado se manifiesta sin aristas.\n\nFinal: Limpio, moderadamente cálido y de longitud media. El retrogusto deja un recuerdo tenue de grano y una ligera nota de pimienta blanca que se desvanece con suavidad."
    ],

    // === PACHARAN ===
    [
        'id' => 272,
        'descripcion' => 'Zoco es el pacharán de referencia en Navarra y probablemente el más reconocido de toda España. Este licor ancestral se obtiene mediante la maceración lenta de endrinas silvestres en aguardiente de anís durante un periodo prolongado de varios meses. La marca Zoco mantiene un proceso artesanal en el que las endrinas, recolectadas a mano en otoño cuando alcanzan su madurez óptima, ceden paulatinamente su color rubí intenso, sus taninos y su carácter frutal al destilado.',
        'nota_cata' => "Nariz: Aromas intensos y golosos de endrina madura, ciruela roja y frutos del bosque, envueltos en un fondo anisado característico. Se aprecian notas de almendra amarga procedentes del hueso de la fruta, un toque de hierbas silvestres y matices de mermelada casera.\n\nBoca: Entrada dulce y frutal, con un cuerpo medio-alto y textura envolvente. El sabor de endrina domina con generosidad, acompañado de notas de cereza negra, regaliz y anís estrellado. El dulzor está bien compensado por una astringencia frutal y una acidez sutil.\n\nFinal: Largo y reconfortante, con un retrogusto donde la endrina y el anís se entrelazan en una despedida cálida. Persisten las notas de fruta macerada y una ligera sensación especiada."
    ],

    // === WHISKIES ===
    [
        'id' => 14,
        'descripcion' => 'J&B Rare es un blended Scotch whisky elaborado por la casa Justerini & Brooks, cuya tradición se remonta al Londres del siglo XVIII. Su mezcla combina más de cuarenta whiskies de malta y grano procedentes de las principales regiones escocesas, con especial protagonismo de destilerías de Speyside. El resultado es un whisky deliberadamente ligero y versátil, concebido para ofrecer suavidad sin sacrificar complejidad.',
        'nota_cata' => "Nariz: Aroma delicado con notas herbáceas frescas, toques de manzana verde y un sutil fondo de cereales malteados. Se perciben ligeros matices florales y un punto de ralladura de limón que aporta vivacidad al conjunto.\n\nBoca: Entrada suave y limpia en boca, con un cuerpo ligero que revela sabores de pera madura, vainilla discreta y un toque especiado de pimienta blanca. La textura es sedosa y fluida, sin aristas.\n\nFinal: Acabado breve y nítido, con un recuerdo agradable a malta tostada y hierbas aromáticas que se desvanece con elegancia, dejando el paladar fresco y preparado."
    ],
    [
        'id' => 15,
        'descripcion' => 'Cutty Sark es un blended Scotch whisky que nació en 1923 de la mano del comerciante de vinos Berry Bros. & Rudd, en plena era de la Ley Seca americana. Debe su nombre al célebre clipper escocés del siglo XIX, símbolo de velocidad y aventura marítima. Su formulación privilegia whiskies de malta jóvenes y vibrantes, lo que le confiere un color dorado pálido natural y un carácter marcadamente fresco.',
        'nota_cata' => "Nariz: Aromas luminosos de cereal dulce, mantequilla fresca y un soplo de naranja confitada. En segundo plano emergen notas de heno seco y un delicado toque a galleta de avena recién horneada.\n\nBoca: Paladar ágil y desenvuelto, con sabores de miel de acacia, malta dorada y un matiz cítrico que recuerda al pomelo maduro. El cuerpo es esbelto y la textura resulta lisa, casi acuosa en su fluidez.\n\nFinal: Desenlace corto y vivaz, con un rastro dulzón de caramelo suave y grano tostado que se apaga rápidamente sin dejar pesadez alguna."
    ],
    [
        'id' => 293,
        'descripcion' => 'Dewar\'s White Label es el buque insignia de la destilería fundada por John Dewar en Perth, Escocia, en 1846, y representa una de las tradiciones de blending más respetadas de las Highlands. Lo distingue su exclusivo proceso de doble envejecimiento: tras mezclar los whiskies de malta y grano, el conjunto retorna a barricas de roble para un periodo adicional de maduración que permite la integración armónica de todos sus componentes.',
        'nota_cata' => "Nariz: Bouquet envolvente de miel de brezo, frutos secos tostados y un fondo dulce de toffee. Aparecen notas de canela en rama y un sutil aroma ahumado que recuerda a leña de roble recién cortada.\n\nBoca: Entrada cremosa y generosa, con sabores bien ensamblados de vainilla bourbon, almendra garrapiñada y malta caramelizada. El cuerpo medio se sostiene con una textura aterciopelada que envuelve el paladar.\n\nFinal: Persistencia media con ecos cálidos de especias dulces, un toque de cacao con leche y un recuerdo limpio a madera noble que invita a dar el siguiente sorbo."
    ],
    [
        'id' => 400,
        'descripcion' => 'Johnnie Walker Black Label es un blended Scotch whisky con un mínimo de doce años de envejecimiento, piedra angular de la legendaria casa fundada en Kilmarnock en 1820. Su elaboración reúne whiskies de malta y grano procedentes de hasta veintinueve destilerías repartidas por toda Escocia, desde las costas de Islay hasta los valles de Speyside. Esta diversidad de orígenes permite al maestro mezclador construir un perfil de extraordinaria complejidad y profundidad.',
        'nota_cata' => "Nariz: Rica y estratificada, con capas de humo de turba, pasas sultanas, cáscara de naranja seca y un fondo de cuero bien curtido. Se aprecian destellos de café expreso, regaliz negro y un matiz yodado que delata la influencia costera.\n\nBoca: Entrada firme y envolvente con un cuerpo medio-alto que despliega sabores de fruta oscura confitada, chocolate amargo, jengibre fresco y madera de sándalo. La turba aparece como un hilo conductor elegante, nunca dominante.\n\nFinal: Largo y memorable, con un abrazo cálido de humo dulce, pimienta negra molida y un eco persistente de malta tostada con cacao que se prolonga generosamente en el paladar."
    ],

    // === BRANDY ===
    [
        'id' => 33,
        'descripcion' => 'Magno es un Brandy de Jerez Solera Reserva elaborado por la casa Osborne en sus históricas bodegas de El Puerto de Santa María, Cádiz, donde la brisa atlántica imprime un carácter singular a cada barrica. Se produce mediante el tradicional sistema de criaderas y solera, envejeciendo en botas de roble americano que previamente albergaron vinos de Jerez. Desde su creación a mediados del siglo XX, Magno se ha consolidado como uno de los brandies españoles más emblemáticos.',
        'nota_cata' => "Nariz: Aromas generosos de vainilla, orejones de albaricoque y un envolvente fondo de roble con recuerdos a frutos secos garrapiñados. Se distinguen notas de caramelo de azúcar moreno, clavo de olor y un delicado perfume de flor de azahar.\n\nBoca: Entrada cálida y redonda, con un cuerpo medio que ofrece sabores de uva pasa, higo seco, nuez moscada y una vainilla mantecosa procedente de la madera. La textura es sedosa y amable, con un dulzor bien integrado.\n\nFinal: Persistencia media-larga, con un final reconfortante de madera tostada, dátil maduro y un sutil recuerdo a miel de romero que se funde con las especias dulces de la crianza en solera."
    ],

    // === RONES ===
    [
        'id' => 26,
        'descripcion' => 'Destilado emblemático de Venezuela, elaborado por Destilerías Unidas S.A. en el estado de Lara, donde el clima tropical acelera la interacción entre el aguardiente de caña y la madera. Su proceso de envejecimiento en barricas de roble blanco americano le confiere una personalidad suave y envolvente, con matices que recuerdan la tradición ronera caribeña. Es un ron de cuerpo medio que equilibra dulzura natural y carácter amaderado.',
        'nota_cata' => "Nariz: Aroma cálido de caramelo tostado y vainilla, con un fondo sutil de coco rallado y melaza. Se perciben ligeras notas de plátano maduro y un toque especiado de canela que aporta complejidad sin resultar agresivo.\n\nBoca: Entrada sedosa y amable, con sabores de azúcar moreno y frutos tropicales confitados. En el desarrollo aparecen matices de roble tostado y una pizca de nuez moscada que le dan estructura. El cuerpo es medio, con una textura agradable y redonda.\n\nFinal: Persistencia moderada, con un recuerdo dulce de miel oscura y madera suave. Deja un rastro cálido y limpio en el paladar."
    ],
    [
        'id' => 112,
        'descripcion' => 'Ron cubano de referencia, elaborado en La Habana siguiendo la tradición de los maestros roneros de la isla. Tras un proceso de destilación en columna y un envejecimiento mínimo de tres años en barricas de roble blanco, el aguardiente se somete a un filtrado en carbón activo que le otorga su característica transparencia y ligereza. El resultado es un destilado fresco y versátil que captura la esencia del ron ligero caribeño.',
        'nota_cata' => "Nariz: Aromas limpios y frescos de caña de azúcar recién cortada, acompañados de notas de pera blanca y un toque floral delicado. En segundo plano se intuyen matices de vainilla suave y una ligera brisa cítrica que recuerda a la lima.\n\nBoca: Entrada ligera y vivaz, con una textura casi cristalina. Predominan los sabores de fruta blanca y caramelo claro, con un punto de acidez que aporta frescura. El paso por roble se manifiesta de forma sutil.\n\nFinal: Corto y refrescante, con un cierre limpio donde persisten notas de cítricos y una leve dulzura de almendra."
    ],
    [
        'id' => 278,
        'descripcion' => 'Producido en San Pedro de Macorís, República Dominicana, por la casa Barceló, fundada en 1930 y convertida en uno de los pilares de la industria ronera dominicana. Este añejo se elabora a partir de mieles de caña locales y reposa en barricas de roble americano previamente usadas para bourbon, en bodegas donde el calor caribeño intensifica el intercambio entre destilado y madera. El resultado es un ron de cuerpo medio-alto, con una marcada personalidad amaderada.',
        'nota_cata' => "Nariz: Intenso aroma de toffee y madera tostada, con capas de cacao en polvo y un fondo de frutos secos como la almendra garrapiñada. Aparecen toques de tabaco rubio y una nota balsámica que recuerda a resina de pino.\n\nBoca: Ataque generoso y envolvente, con sabores de caramelo oscuro, café con leche y dátil. La madera se expresa con claridad, aportando taninos suaves y un punto de amargor noble que equilibra la dulzura natural del destilado.\n\nFinal: Largo y gratificante, con una estela de chocolate negro y especias dulces como el clavo. El retrogusto alterna entre la calidez del roble y un recuerdo meloso."
    ],

    // === VERMUT ===
    [
        'id' => 310,
        'descripcion' => 'Vermut artesanal elaborado en Vilassar de Mar, localidad costera del Maresme barcelonés, por la casa Espinaler, cuya tradición vermutista se remonta a mediados del siglo XX. Se produce a partir de una base de vino blanco macerado con una fórmula propia de más de treinta botánicos, entre los que destacan el ajenjo, la genciana y diversas cortezas de cítricos mediterráneos. Su color oscuro intenso y su perfil aromático complejo lo convierten en un referente del vermut negro catalán.',
        'nota_cata' => "Nariz: Aromas profundos de hierbas amargas y raíces, con la presencia inconfundible del ajenjo y la genciana. Se entrelazan notas de piel de naranja amarga, regaliz negro y un matiz torrefacto que recuerda al café.\n\nBoca: Entrada amarga y aromática, con una dulzura contenida que actúa como contrapunto. Se perciben sabores de quina, corteza de pomelo y hierbas de monte bajo mediterráneo. El cuerpo resulta denso y untuoso, casi masticable.\n\nFinal: Persistente y balsámico, con un regusto donde conviven el amargor elegante de la genciana y la frescura mentolada de las hierbas. Deja la boca limpia y estimulada."
    ],

    // === MOSCATEL ===
    [
        'id' => 315,
        'descripcion' => 'Vino dulce natural elaborado por Bodegas Torres en la comarca del Penedès, a partir de uvas moscatel de Alejandría vendimiadas en su punto óptimo de maduración y sobremaduración parcial en la cepa. La fermentación se interrumpe mediante la adición de alcohol vínico para preservar los azúcares naturales de la uva, obteniendo un vino generoso de color dorado brillante. Es un clásico de la sobremesa mediterránea.',
        'nota_cata' => "Nariz: Explosión aromática de flores blancas como el azahar y el jazmín, seguida de una cascada de fruta madura: melocotón en almíbar, albaricoque seco y uva pasa dorada. En el fondo aparecen notas de miel de romero, pétalos de rosa y un sutil toque de piel de mandarina confitada.\n\nBoca: Entrada generosamente dulce pero sin empalagar, gracias a una acidez bien calibrada que equilibra el conjunto. Los sabores reproducen fielmente la nariz, con predominio de fruta escarchada, membrillo y miel de azahar. La textura es sedosa y envolvente.\n\nFinal: Largo y perfumado, con una persistencia notable de notas florales y frutales. El recuerdo final es de uva moscatel fresca, con un eco de miel y cítricos confitados."
    ],

    // === VINOS TINTOS TOP ===
    [
        'id' => 206,
        'descripcion' => 'Bodega fundada en 1924 en Haro, en el corazón de la Rioja Alta, Ramón Bilbao representa la tradición vinícola riojana con una visión contemporánea. Este Reserva está elaborado íntegramente con uva Tempranillo procedente de viñedos seleccionados en suelos calcáreo-arcillosos. Tras una cuidadosa vinificación, el vino reposa durante 18 meses en barricas de roble americano y francés, seguidos de un prolongado afinamiento en botella.',
        'nota_cata' => "Nariz: Aroma envolvente donde destacan las frutas rojas maduras como cereza y ciruela, entrelazadas con notas de vainilla, coco tostado y un sutil fondo de tabaco rubio. Con el paso del tiempo en copa aparecen matices especiados de clavo y canela.\n\nBoca: Entrada sedosa y amplia, con un tanino bien pulido que viste el paladar sin aspereza. La fruta madura se funde con tonos balsámicos y de regaliz, sostenidos por una acidez fresca que aporta vivacidad. Cuerpo medio-alto con una textura aterciopelada.\n\nFinal: Persistente y armonioso, con un retrogusto donde conviven la fruta confitada, las especias dulces de la barrica y un elegante toque mineral."
    ],
    [
        'id' => 242,
        'descripcion' => 'Nacida del sueño del cantautor catalán Lluís Llach por recuperar los antiguos viñedos de Porrera, Vall Llach es una de las bodegas emblemáticas del Priorat. Embruix, cuyo nombre evoca un hechizo, combina las variedades autóctonas Garnacha y Cariñena con aportaciones de Cabernet Sauvignon, Merlot y Syrah, reflejando la diversidad del terruño prioratino. Las cepas crecen en los característicos suelos de licorella en laderas empinadas de escaso rendimiento.',
        'nota_cata' => "Nariz: Intenso y profundo, despliega aromas de frutos negros silvestres como mora y arándano, acompañados de notas de garriga mediterránea, romero y tomillo. Se perciben matices de pizarra húmeda y grafito, junto con pinceladas de pimienta negra y cacao amargo.\n\nBoca: Potente y concentrado, con una entrada golosa de fruta negra que da paso a un tanino firme pero de grano fino. La mineralidad pizarrosa marca el carácter del vino, aportando una salinidad que equilibra la riqueza frutal.\n\nFinal: Largo e intenso, con una persistencia notable donde la mineralidad de la licorella se funde con recuerdos de fruta madura y especias ahumadas."
    ],
    [
        'id' => 308,
        'descripcion' => 'Alejandro Fernández revolucionó la Ribera del Duero cuando en 1972 apostó por elaborar grandes vinos a partir de la Tinto Fino, la expresión local de la Tempranillo adaptada al clima extremo de la meseta castellana. Pesquera Crianza procede de viñedos propios situados en torno a la localidad de Pesquera de Duero, a más de 750 metros de altitud, donde las drásticas oscilaciones térmicas favorecen una maduración lenta y equilibrada.',
        'nota_cata' => "Nariz: Expresivo e inmediato, ofrece un bouquet dominado por la cereza negra madura y la frambuesa confitada, sobre un fondo de madera noble bien integrada. Aparecen notas de vainilla, toffee y un recuerdo herbáceo sutil que evoca el tomillo de los páramos castellanos.\n\nBoca: Carnoso y generoso, con un ataque frutal que llena el paladar. Los taninos son robustos pero maduros, proporcionando estructura sin rusticidad. La acidez justa permite que los sabores de fruta negra, regaliz y vainilla se expresen con nitidez.\n\nFinal: De recorrido amplio, despide sabores de fruta concentrada y especias dulces con un fondo ligeramente tostado. Un final cálido, sabroso y persistente."
    ],
    [
        'id' => 314,
        'descripcion' => 'Fundada en 1858 en Elciego (Álava), Marqués de Riscal es una de las bodegas más antiguas y prestigiosas de la Rioja, pionera en introducir las técnicas bordelesas de vinificación en la región. Su Reserva se elabora principalmente con Tempranillo, complementada con pequeñas proporciones de Graciano y Mazuelo. Los viñedos se asientan en la Rioja Alavesa, donde los suelos arcillo-calcáreos y el microclima de influencia atlántica producen uvas de notable finura.',
        'nota_cata' => "Nariz: Complejo y refinado, presenta una primera capa de fruta roja madura que evoluciona hacia notas de cuero repujado, habano y especias finas como la nuez moscada. El roble aporta recuerdos de vainilla discreta y cedro, mientras un sutil matiz balsámico revela el buen envejecimiento.\n\nBoca: Elegante y equilibrado, con una estructura clásica que combina taninos sedosos con una acidez vibrante que le aporta nervio. Los sabores de fruta roja se entrelazan con notas de tabaco, especias y un toque terroso que recuerda a la trufa.\n\nFinal: Prolongado y delicado, se despide con un elegante recuerdo de fruta en compota, madera noble y un ligero toque mentolado."
    ],
    [
        'id' => 374,
        'descripcion' => 'Scala Dei toma su nombre de la antigua cartuja fundada en el siglo XII en el corazón del Priorat, y fue una de las primeras bodegas en recuperar el potencial vitícola de esta comarca. Su vino Negre se elabora con Garnacha y Cariñena, las dos variedades históricas de la zona, cultivadas en viñedos viejos plantados sobre los característicos suelos de llicorella. Las cepas, muchas con décadas de antigüedad, producen rendimientos muy bajos que se traducen en una concentración natural extraordinaria.',
        'nota_cata' => "Nariz: Aromático y expresivo, combina la frescura de la Garnacha con la profundidad terrosa de la Cariñena. Se distinguen matices de hierbas aromáticas del monte bajo mediterráneo, piedra caliente y un fondo mineral de pizarra. Aparecen notas de violeta y pimienta blanca.\n\nBoca: Jugoso y directo, con una fruta vibrante que se despliega sobre un armazón tánico de grano medio, firme pero sin dureza. La mineralidad pizarrosa aporta una salinidad estimulante que tensa el vino y le da verticalidad.\n\nFinal: De buena longitud, termina con una nota mineral limpia y un recuerdo frutal que se va apagando entre matices de roca y especias suaves."
    ],

    // === VINOS Y BLANCOS ===
    [
        'id' => 222,
        'descripcion' => 'Legaris es un proyecto enológico ubicado en el corazón de la Ribera del Duero, donde la altitud y los contrastes térmicos entre el día y la noche forjan vinos de carácter singular. Elaborado íntegramente con uva Tempranillo procedente de viñedos seleccionados en suelos calcáreos y pedregosos, este roble pasa aproximadamente cuatro meses en barricas de roble francés y americano. El resultado es un tinto moderno y accesible que conjuga la fruta fresca con un sutil aporte de madera.',
        'nota_cata' => "Nariz: Intenso y limpio, con predominio de fruta roja madura como cereza y frambuesa, acompañadas de ligeros matices de vainilla, coco tostado y un fondo especiado que recuerda al clavo y la pimienta blanca.\n\nBoca: Entrada jugosa y de cuerpo medio, con taninos suaves y bien pulidos que aportan estructura sin aspereza. La fruta se muestra generosa en el paladar, con notas de mora y ciruela roja, y una acidez equilibrada que mantiene la frescura.\n\nFinal: Persistencia media con recuerdos de fruta confitada y un delicado toque ahumado. Cierre limpio y agradable."
    ],
    [
        'id' => 237,
        'descripcion' => 'Matsu es una bodega que rinde homenaje a los viticultores veteranos de la denominación de origen Toro, tierra de vinos potentes y con alma. El Pícaro, el más joven de la trilogía, se elabora con Tinta de Toro procedente de viñedos cultivados en agricultura ecológica sobre suelos arenosos y de cantos rodados. Las cepas jóvenes aportan una expresión frutal vibrante y desenfadada, vinificada sin crianza en madera para preservar toda la pureza varietal.',
        'nota_cata' => "Nariz: Explosivo e inmediato, despliega aromas de fruta negra silvestre como zarzamora y arándano, con pinceladas de violeta fresca, regaliz y un sutil fondo mineral que evoca la tierra arcillosa de Toro.\n\nBoca: Potente y carnoso a pesar de su juventud, con una concentración notable y taninos firmes pero de grano fino. La fruta negra domina el paladar con generosidad, equilibrada por una acidez vibrante que aporta nervio y dinamismo.\n\nFinal: Largo y envolvente, con un retrogusto donde confluyen la mora madura, notas de pimienta negra y un recuerdo terroso que delata su origen zamorano."
    ],
    [
        'id' => 282,
        'descripcion' => 'La familia Gramona, con más de un siglo de tradición vitivinícola en el Alt Penedès, elabora este blanco aromático como una oda a la frescura mediterránea. Gessamí nace del ensamblaje de Moscatel de Alejandría, Sauvignon Blanc y Gewürztraminer, tres variedades de marcado perfil aromático que se complementan con elegancia. La vinificación se realiza a baja temperatura en depósitos de acero inoxidable para capturar toda la intensidad floral y frutal de las uvas.',
        'nota_cata' => "Nariz: Exuberante y seductor, con un ramillete de flores blancas como jazmín y azahar, seguido de notas de melocotón blanco, lichi, pomelo rosa y un delicado toque de hierba fresca que recuerda a la albahaca.\n\nBoca: Entrada sedosa y envolvente, con un cuerpo ligero a medio que sorprende por su amplitud en boca. La acidez es vivaz y refrescante, con sabores que recorren la fruta de hueso madura, los cítricos y un carácter floral persistente.\n\nFinal: De recorrido medio-largo, cierra con un agradable amargor cítrico que estimula el paladar y notas de piel de mandarina y pétalos de rosa que permanecen con delicadeza."
    ],
    [
        'id' => 304,
        'descripcion' => 'Bodegas Luis Cañas, asentada en la localidad alavesa de Villabuena, representa la esencia de la Rioja Alavesa con vinos que combinan tradición y precisión enológica. Este crianza se elabora principalmente con Tempranillo complementado con un porcentaje de Graciano, variedad autóctona que aporta color, acidez y complejidad aromática. El vino reposa durante catorce meses en barricas de roble francés y americano.',
        'nota_cata' => "Nariz: Compleja y bien definida, con capas de cereza negra madura, ciruelo y grosella que se entrelazan con aromas de vainilla, canela en rama, tabaco rubio y un sutil toque balsámico de romero y tomillo.\n\nBoca: Entrada amplia y estructurada, con un cuerpo medio-pleno y taninos maduros de textura aterciopelada. Fruta concentrada pero elegante, arropada por notas de cacao, café tostado y especias dulces, vertebrado por una acidez justa.\n\nFinal: Largo y memorable, con un retrogusto donde se funden la fruta en compota, el cuero noble y un fino recuerdo de roble bien integrado."
    ],
    [
        'id' => 311,
        'descripcion' => 'CVNE, fundada en 1879 en el barrio de la Estación de Haro, es una de las casas históricas de la Rioja y referencia ineludible del estilo clásico riojano. Su Cune Crianza se elabora mayoritariamente con Tempranillo seleccionado de viñedos propios en Rioja Alta, donde la influencia atlántica modera las temperaturas y alarga el ciclo de maduración. La crianza se lleva a cabo en barricas de roble americano y francés.',
        'nota_cata' => "Nariz: Elegante y clásica, abre con aromas de fruta roja madura como fresa y cereza, que dan paso a notas de toffee, vainilla cremosa, cuero suave y un matiz de hoja de laurel.\n\nBoca: De entrada amable y cuerpo medio, con taninos finos y bien integrados que revelan una textura sedosa. El paladar muestra un equilibrio admirable entre la fruta roja confitada, las especias suaves y una acidez bien calibrada.\n\nFinal: Persistencia media-larga con un cierre limpio y armonioso, donde resuenan la frambuesa madura, un toque de nuez moscada y la calidez amable de la madera bien trabajada."
    ],

    // === CAVAS ===
    [
        'id' => 281,
        'descripcion' => 'Gramona es una de las casas elaboradoras más emblemáticas del Penedès, con una tradición familiar que se remonta a finales del siglo XIX. El III Lustros se elabora a partir de Xarel·lo y Macabeo cultivados en viñedos viejos sobre suelos calcáreos. Tras una segunda fermentación en botella, reposa un mínimo de ocho años en contacto con sus lías, un periodo de crianza excepcional que confiere una complejidad y profundidad difíciles de encontrar en el mundo del espumoso.',
        'nota_cata' => "Nariz: Aroma complejo y envolvente donde se entrelazan notas de frutos secos tostados, corteza de pan horneado y matices de miel de azahar. Con la oxigenación aparecen recuerdos de hierbas secas mediterráneas, cera de abeja y un sutil fondo mineral con toques ahumados.\n\nBoca: Entrada cremosa y untuosa, con una burbuja finísima e integrada que recorre el paladar con delicadeza. El cuerpo es amplio y estructurado, con capas de sabor desde la fruta blanca madura hasta las notas de panadería. La acidez es vibrante y precisa.\n\nFinal: Prolongado e impactante, con un retrogusto donde persisten los matices tostados, el recuerdo salino y una elegante nota amarga que invita a cada nuevo sorbo."
    ],
    [
        'id' => 303,
        'descripcion' => 'Recaredo, bodega familiar fundada en 1924 en Sant Sadurní d\'Anoia, es referencia ineludible del cava de máxima calidad y pionera en la aplicación de principios biodinámicos a la viticultura en el Penedès. El Terrers nace de la combinación de Xarel·lo, Macabeo y Parellada procedentes de parcelas propias sobre suelos de terraza fluvial. La vinificación se realiza con levaduras autóctonas y la crianza sobre lías se prolonga muy por encima de los mínimos legales.',
        'nota_cata' => "Nariz: Primer impacto fresco y luminoso, con aromas de manzana golden, flor de almendro y cítricos maduros. En segundo plano emergen notas de masa fermentada, almendra cruda y un fondo calcáreo. La copa evoluciona mostrando toques de hierbas frescas como el tomillo.\n\nBoca: Ataque limpio y directo, con una mousse de burbuja fina y persistente que envuelve el paladar. Tenso y vertical, con una acidez vibrante que vertebra un cuerpo medio de gran precisión. La salinidad marca el ritmo del trago.\n\nFinal: Largo y mineral, con una estela de cítricos blancos y un regusto ligeramente almendrado que aporta distinción."
    ],
    [
        'id' => 305,
        'descripcion' => 'Agustí Torelló Mata es una bodega familiar arraigada en el corazón de Sant Sadurní d\'Anoia, cuna del cava, que elabora sus espumosos con rigor artesanal y un profundo respeto por la tradición del método clásico. Este Brut Reserva se compone de Macabeo, Xarel·lo y Parellada vendimiado a mano y prensado con suavidad para obtener mostos de máxima delicadeza. La crianza en cava subterránea supera con creces el periodo exigido para la categoría Reserva.',
        'nota_cata' => "Nariz: Aromas francos y limpios de pera de agua, manzana verde y flores blancas primaverales. Se perciben apuntes de corteza de limón, un ligero toque de levadura fresca y destellos anisados.\n\nBoca: Entrada frutal y jugosa, con una burbuja bien integrada que aporta textura sin agredir. De cuerpo medio y estructura ágil, muestra un equilibrio notable entre la acidez cítrica y la suave redondez de la crianza.\n\nFinal: De recorrido medio-largo, cierra con un agradable amargor de piel de pomelo y un recuerdo frutal limpio. La burbuja se desvanece dejando una sensación refrescante y un apetecible toque salino."
    ],

    // === MÁS VINOS ===
    [
        'id' => 382,
        'descripcion' => 'Brunus es la expresión más franca y accesible de Portal del Montsant, proyecto enológico ubicado en la D.O. Montsant, comarca vinícola que rodea el legendario Priorat y comparte con él un paisaje agreste de pizarras y pendientes escarpadas. Elaborado con un ensamblaje mediterráneo de Garnacha, Cariñena y Syrah, las uvas proceden de viñedos asentados sobre suelos de licorella y arcilla roja a distintas altitudes. Un tinto generoso que captura la esencia del Montsant.',
        'nota_cata' => "Nariz: Intenso y expresivo, despliega aromas de cereza negra madura, mora silvestre y ciruela confitada. Sobre el fondo frutal asoman notas de garriga mediterránea — romero, jara, lavanda — junto con matices de pimienta negra, regaliz y un sutil recuerdo mineral de roca caliente.\n\nBoca: Entrada amplia y carnosa, con una fruta generosa y envolvente que llena el paladar. El tanino es presente pero de grano fino, bien pulido, y la acidez justa mantiene el vino fresco y digestivo.\n\nFinal: Persistente y cálido, con un retrogusto donde confluyen la fruta madura, el mineral pizarroso y un toque especiado que recuerda al clavo y la canela."
    ],
    [
        'id' => 384,
        'descripcion' => 'Bodegas Vizcarra, situada en Roa de Duero, es un proyecto familiar liderado por Juan Carlos Vizcarra que ha sabido ganarse un lugar destacado en la D.O. Ribera del Duero. Senda del Oro es su vino joven con crianza en roble, concebido para ofrecer una lectura accesible del terruño ribereño sin renunciar a la estructura. Las uvas provienen de viñedos propios sobre suelos calcáreos a más de 800 metros de altitud. Una breve estancia en barrica de roble aporta complejidad sin ocultar la vibrante fruta varietal.',
        'nota_cata' => "Nariz: Aroma fresco y goloso dominado por frutos rojos y negros — frambuesa, grosella negra, cereza picota — con un fondo de violetas y un elegante toque de roble que aporta matices de vainilla suave y coco tostado. Un ligero apunte de grafito y tierra húmeda completa el conjunto.\n\nBoca: Entrada jugosa y frutal, con un cuerpo medio que se muestra ágil y sabroso. El tanino es joven pero maduro, de textura sedosa, y la acidez se integra con naturalidad aportando frescura.\n\nFinal: Medio-largo, limpio y gratificante, con un cierre donde la fruta madura se funde con recuerdos tostados y un ligero amargor tánico que aporta carácter."
    ],
];

$ok = 0;
$errors = 0;

foreach ($productos as $p) {
    $id = $p['id'];
    $desc = mysqli_real_escape_string($bd, $p['descripcion']);
    $sql = "INSERT INTO producto_textos (idProducto, tipo, contenido) VALUES ($id, 'descripcion', '$desc')
            ON DUPLICATE KEY UPDATE contenido = '$desc'";
    if (mysqli_query($bd, $sql)) { $ok++; } else { echo "ERROR desc ID $id: " . mysqli_error($bd) . "\n"; $errors++; }

    $nota = mysqli_real_escape_string($bd, $p['nota_cata']);
    $sql = "INSERT INTO producto_textos (idProducto, tipo, contenido) VALUES ($id, 'nota_cata', '$nota')
            ON DUPLICATE KEY UPDATE contenido = '$nota'";
    if (mysqli_query($bd, $sql)) { $ok++; } else { echo "ERROR nota ID $id: " . mysqli_error($bd) . "\n"; $errors++; }
}

echo "Insertados: $ok OK, $errors errores\n";
