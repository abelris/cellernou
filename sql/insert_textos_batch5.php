<?php
/**
 * Batch 5: Insert texts for 50 products
 * Original descriptions and tasting notes
 */
require dirname(__DIR__) . '/distribucio/connexio/connexio.php';
mysqli_query($bd, "SET SESSION sql_mode=''");
mysqli_set_charset($bd, 'utf8mb4');

$textos = [
    // ===== WINES (18) =====
    [220, 'Albariño Marieta',
        'Albariño gallego fresco y expresivo, elaborado con uvas de viñedos en las Rías Baixas. Marieta apuesta por una vinificación limpia en acero inoxidable a baja temperatura, buscando la máxima expresión aromática de la variedad albariño. Un blanco atlántico que transmite la frescura y salinidad del noroeste peninsular.',
        "Nariz: Aromas intensos de fruta blanca, melocotón y albaricoques, con notas cítricas de pomelo y un fondo floral de azahar.\nBoca: Fresco y vibrante, con una acidez bien marcada que enmarca sabores de fruta de hueso, lima y un toque salino atlántico.\nFinal: Medio-largo y refrescante, con recuerdos cítricos y una salinidad mineral que define su origen gallego."
    ],
    [302, 'Bouquet Blanc',
        'Blanco de la D.O. Alella elaborado por Alta Alella con variedades mediterráneas de viñedos ecológicos. Las viñas crecen en la Serralada de Marina, a pocos kilómetros del mar, sobre suelos de sauló granítico que confieren mineralidad y frescura al vino.',
        "Nariz: Aromas de manzana verde, pera y flores blancas primaverales, con un elegante fondo mineral y notas de hierbas frescas.\nBoca: Ligero y refrescante, con buena acidez, sabores de cítricos, fruta blanca y un carácter salino mediterráneo.\nFinal: Medio y mineral, con un recuerdo limpio de almendra verde y piel de limón."
    ],
    [361, 'Duquesa de Valladolid',
        'Vino blanco de la D.O. Rueda elaborado con verdejo de viñedos castellanos. Vinificación moderna en depósitos de acero inoxidable a temperatura controlada para preservar los aromas varietales. Un blanco que refleja el potencial de la meseta para producir blancos de carácter.',
        "Nariz: Aromas de hierba fresca, pomelo, manzana verde y un toque de hinojo con notas minerales de fondo.\nBoca: Seco y estructurado, con buena acidez y sabores de fruta cítrica, manzana y un agradable amargor herbal.\nFinal: Largo y limpio, con un retrogusto de cítricos y hierbas que permanece con frescura."
    ],
    [444, 'Albariño Terras Gauda',
        'Albariño premium de Bodegas Terras Gauda, ubicada en O Rosal, Rías Baixas. Coupage de albariño, caiño blanco y loureiro que aportan complejidad al vino. Viñedos en terrazas orientadas al río Miño con suelos graníticos y clima atlántico que definen el carácter único de esta zona.',
        "Nariz: Complejo y elegante, con aromas de fruta de hueso madura, cítricos, flores blancas y un fondo mineral pétreo con notas de hierbas aromáticas.\nBoca: Amplio y estructurado para un blanco, con acidez equilibrada, sabores de melocotón, lima, hinojo y una mineralidad granítica persistente.\nFinal: Largo y salino, con recuerdos de fruta blanca y una frescura atlántica que define su carácter."
    ],
    [446, 'Parés Baltà Ros de Pacs',
        'Rosado ecológico de Parés Baltà, bodega familiar en el Alt Penedès comprometida con la biodinámica. Elaborado con merlot y cabernet sauvignon de viñedos propios. Un rosado con personalidad mediterránea que combina frescura y estructura.',
        "Nariz: Aromas de fresas frescas, pétalos de rosa, pomelo rosado y un toque de hierba con notas de caramelo suave.\nBoca: Fresco y sabroso, con buena estructura, sabores de frutos rojos, cítricos y un punto especiado.\nFinal: Medio-largo, con un retrogusto afrutado y una acidez refrescante que invita a repetir."
    ],
    [456, 'Heus Blanc',
        'Blanco de la D.O. Empordà elaborado por La Vinyeta con variedades mediterráneas de viñedos orgánicos azotados por la tramontana. Los suelos de pizarra y esquisto de la zona del Cap de Creus aportan mineralidad y carácter al vino.',
        "Nariz: Aromas de fruta blanca, flores de almendro, hierbas aromáticas como romero y un fondo mineral de piedra caliente.\nBoca: Fresco y mediterráneo, con sabores de pera, manzana, almendra y una salinidad que recuerda la cercanía al mar.\nFinal: Medio y mineral, con recuerdos de garriga y un toque almendrado persistente."
    ],
    [531, 'Pago de los Capellanes Reserva',
        'Reserva de Bodegas Pago de los Capellanes en Pedrosa de Duero, D.O. Ribera del Duero. Tempranillo de viñedos viejos seleccionados con 16 meses de crianza en barricas de roble francés nuevas. Un tinto de vocación clásica que combina potencia ribereña con finura y elegancia.',
        "Nariz: Aromas profundos de fruta negra madura, cedro, tabaco, especias finas y notas de grafito con un fondo tostado complejo.\nBoca: Concentrado y estructurado, con taninos firmes pero de grano fino, gran acidez y sabores de mora, cacao, café y especias nobles.\nFinal: Muy largo y envolvente, con recuerdos de fruta confitada, tabaco y una mineralidad que perdura."
    ],
    [537, 'Mas Donis',
        'Tinto de la D.O. Montsant elaborado por Celler de Capçanes con garnacha y syrah de viñedos en suelos de pizarra y arcilla roja. Montsant, hermana del Priorat, comparte su carácter mineral mediterráneo a un precio más accesible. Un vino franco y directo.',
        "Nariz: Aromas de cereza madura, frambuesa, violetas y hierbas de monte bajo con un toque de pimienta negra.\nBoca: Jugoso y mediterráneo, con taninos suaves, buena frescura y sabores de frutos rojos, regaliz y especias.\nFinal: Medio y sabroso, con recuerdos de fruta madura y un toque mineral pizarroso."
    ],
    [576, 'Matsu Viejo',
        'La expresión más ambiciosa de la trilogía Matsu en la D.O. Toro. Tinta de toro de viñas centenarias de más de 100 años cultivadas en secano sobre suelos arenosos. Fermentación en tinas de roble francés y crianza de 20 meses en barricas nuevas. La etiqueta rinde homenaje al viticultor más anciano.',
        "Nariz: Aromas intensísimos de fruta negra confitada, chocolate negro, tabaco, cuero y notas de tierra húmeda con un fondo balsámico.\nBoca: Potente y profundo, con una concentración extraordinaria, taninos maduros y sedosos, y capas de sabor que incluyen mora, ciruela pasa, cacao y especias oscuras.\nFinal: Extraordinariamente largo, con oleadas de fruta confitada, tostados nobles y una calidez envolvente."
    ],
    [577, 'Castell del Remei Oda Negre',
        'Tinto de la D.O. Costers del Segre elaborado por Castell del Remei, bodega histórica de Lleida. Coupage de tempranillo, cabernet sauvignon y merlot con crianza en barricas de roble. Un vino que expresa el carácter continental de las tierras de Lleida con elegancia.',
        "Nariz: Aromas de fruta negra madura, ciruela, notas de vainilla y tostados de la barrica con un fondo de especias y chocolate con leche.\nBoca: Estructurado y redondo, con taninos maduros, buena acidez y sabores de mora, cereza negra, cacao y un punto balsámico.\nFinal: Largo y armonioso, con recuerdos de fruta confitada, vainilla y especias dulces."
    ],
    [612, 'Alta Alella Garnatxa Negre',
        'Tinto monovarietal de garnacha de Alta Alella, elaborado con uvas de viñedos ecológicos en la D.O. Alella. La garnacha cultivada cerca del mar Mediterráneo, en suelos de sauló granítico, produce un tinto fresco y aromático con la expresión más pura de esta variedad.',
        "Nariz: Aromas de frambuesa, cereza fresca, flores rojas y hierbas mediterráneas con un elegante fondo mineral de granito.\nBoca: Ligero y sedoso, con taninos suaves, acidez fresca y sabores de frutos rojos, granada y un punto especiado.\nFinal: Medio y elegante, con recuerdos de fruta roja y una mineralidad salina persistente."
    ],
    [628, 'José Pariente Verdejo',
        'Verdejo de referencia de la D.O. Rueda, elaborado por Victoria Pariente en Rueda, Valladolid. Viñedos propios sobre suelos de cascajo pedregoso a más de 700 metros de altitud. Fermentación a baja temperatura en depósitos de acero para capturar toda la intensidad aromática del verdejo.',
        "Nariz: Intensos aromas de hierba fresca, fruta de la pasión, pomelo y hinojo, con un fondo mineral y notas de box.\nBoca: Vibrante y expresivo, con acidez bien marcada, sabores de manzana verde, lima, hierbas frescas y un toque amargo elegante.\nFinal: Largo y refrescante, con un retrogusto de cítricos y un amargor herbal que define al gran verdejo."
    ],
    [680, 'Sangre de Toro',
        'Tinto clásico de Bodegas Torres, uno de los vinos españoles más reconocidos internacionalmente. Elaborado con garnacha y cariñena de la D.O. Catalunya. Su icónico toro de plástico que acompaña cada botella es símbolo de la marca desde los años 50.',
        "Nariz: Aromas de cereza madura, ciruela, especias dulces como canela y clavo, con un fondo de regaliz y un toque herbal.\nBoca: Redondo y amable, con taninos suaves, cuerpo medio y sabores de fruta roja madura, especias y un punto de garriga.\nFinal: Medio, cálido y agradable, con recuerdos de fruta y especias que se desvanecen suavemente."
    ],
    [748, 'Ramón Bilbao Verdejo',
        'Blanco de Bodegas Ramón Bilbao en la D.O. Rueda, elaborado íntegramente con verdejo de viñedos seleccionados en suelos pedregosos de Valladolid. Vinificación en acero inoxidable a baja temperatura, con contacto con sus lías finas durante varios meses para aportar volumen.',
        "Nariz: Aromas frescos de hierba cortada, fruta tropical, lima y hinojo, con notas florales y un fondo mineral.\nBoca: Amplio y fresco, con buena acidez, sabores de pomelo, manzana verde, hierbas aromáticas y un toque cremoso de las lías.\nFinal: Largo y expresivo, con un retrogusto cítrico y herbal que invita a seguir bebiendo."
    ],
    [932, 'Mauro Crianza',
        'Tinto de culto de Bodegas Mauro, fundada por Mariano García, exenólogo de Vega Sicilia. Aunque no pertenece a ninguna D.O., sus viñedos en Tudela de Duero producen tempranillo de calidad excepcional. Crianza en barricas de roble francés que aporta complejidad sin ocultar la expresión del terroir.',
        "Nariz: Aromas complejos de fruta negra madura, regaliz, cedro, especias finas y notas de grafito con un fondo tostado elegante.\nBoca: Sedoso y profundo, con taninos de textura fina, gran estructura y sabores de mora, cereza negra, cacao, café y especias nobles.\nFinal: Muy largo y sofisticado, con recuerdos de fruta, madera noble y una mineralidad que lo distingue."
    ],
    [992, 'Martín Códax',
        'Albariño de referencia de la D.O. Rías Baixas, elaborado por Bodegas Martín Códax en Cambados, Pontevedra. Nombrado en honor al trovador medieval gallego, este blanco captura la esencia atlántica de las Rías Baixas con albariño de viñedos en emparrado sobre suelos graníticos.',
        "Nariz: Aromas limpios de melocotón blanco, manzana, cítricos y flores blancas, con un fondo mineral de piedra mojada y notas de hierba fresca.\nBoca: Fresco y equilibrado, con acidez vibrante, sabores de fruta de hueso, lima, hinojo y una salinidad atlántica persistente.\nFinal: Largo y mineral, con recuerdos de cítricos y una frescura marina que define el estilo Rías Baixas."
    ],
    [1050, 'Llavors',
        'Tinto de la D.O. Empordà elaborado por La Vinyeta con garnacha, cariñena y syrah de viñedos orgánicos. El nombre Llavors (semillas en catalán) evoca el compromiso de esta bodega familiar con la tierra y las variedades autóctonas del Empordà. Crianza en barrica que redondea su carácter mediterráneo.',
        "Nariz: Aromas de cereza negra, mora silvestre, romero, tomillo y notas de pizarra con un toque de pimienta y chocolate amargo.\nBoca: Estructurado y mediterráneo, con taninos firmes pero maduros, buena acidez y sabores de fruta negra, hierbas aromáticas y especias.\nFinal: Largo y mineral, con recuerdos de garriga, pizarra y fruta confitada."
    ],
    [1183, 'Paco & Lola',
        'Albariño de las Rías Baixas elaborado por la cooperativa Paco & Lola en Meaño, Pontevedra. Reconocible por su botella de lunares blancos que evoca las conchas de vieira gallegas. Albariño fresco y aromático de viñedos atlánticos sobre suelos graníticos.',
        "Nariz: Aromas de melocotón blanco, albaricoque, flores de azahar y cítricos, con notas de hierba húmeda y un fondo mineral.\nBoca: Fresco y jugoso, con acidez equilibrada, sabores de fruta de hueso, lima, manzana y un toque salino.\nFinal: Medio-largo y refrescante, con recuerdos de fruta y una salinidad atlántica agradable."
    ],

    // ===== CAVAS / ESPUMOSOS (8) =====
    [223, 'Lambrusco Violetta de Giorgi Rosato',
        'Lambrusco rosado italiano de la casa De Giorgi, elaborado en la región de Emilia-Romaña. Este espumoso ligero y afrutado se produce con la variedad lambrusco, ofreciendo un vino desenfadado y festivo con su característico color rosado brillante y su dulzor contenido.',
        "Nariz: Aromas de fresas, frambuesas y pétalos de rosa, con notas de caramelo suave y un toque floral delicado.\nBoca: Ligero y efervescente, con dulzor moderado equilibrado por una acidez refrescante, sabores de frutos rojos y un carácter festivo.\nFinal: Corto y agradable, con recuerdos de fresa y un retrogusto ligeramente dulce."
    ],
    [589, 'Anna de Codorníu Blanc de Blancs',
        'Cava de Codorníu elaborado exclusivamente con variedades blancas: chardonnay, macabeo, xarel·lo y parellada. Crianza sobre lías en las históricas cavas subterráneas de Sant Sadurní d\'Anoia. Un espumoso fresco y elegante que refleja la tradición centenaria de la casa.',
        "Nariz: Aromas de manzana verde, pera, cítricos y flores blancas, con notas de levadura fina y un toque de pan tostado.\nBoca: Burbuja fina y persistente, fresco y equilibrado, con sabores de fruta blanca, almendra y una acidez viva.\nFinal: Medio-largo y limpio, con recuerdos de cítricos y un retrogusto de levadura elegante."
    ],
    [630, 'Cava Llopart Brut Nature Reserva Integral',
        'Cava ecológico de Llopart, elaborado sin dosaje y sin filtrar, preservando toda la pureza del vino. Macabeo, xarel·lo y parellada de viñedos propios en agricultura ecológica. La versión más auténtica de Llopart, con toda su turbidez natural y complejidad.',
        "Nariz: Aromas de manzana asada, flores de almendro, masa madre y notas minerales de calcáreo con un fondo de hierbas secas.\nBoca: Burbuja fina, textura cremosa con partículas en suspensión, sabores de fruta blanca, pan integral y una acidez vibrante.\nFinal: Largo y auténtico, con recuerdos de frutos secos, pan de pueblo y mineralidad calcárea."
    ],
    [676, 'Cava Juvé & Camps Cinta Púrpura',
        'Cava Reserva de Juvé & Camps, bodega familiar del Penedès con más de 200 años de historia. Elaborado con macabeo, xarel·lo y parellada de viñedos propios. Su nombre hace referencia a la cinta púrpura que envuelve cada botella artesanalmente.',
        "Nariz: Aromas de manzana golden, pera madura, flores blancas y levadura fina con notas de almendra y un toque de miel.\nBoca: Burbuja fina y cremosa, con sabores de fruta blanca, brioche, almendra y una acidez equilibrada.\nFinal: Medio-largo, con recuerdos de pan tostado, frutos secos y un retrogusto limpio y elegante."
    ],
    [720, 'Cava Codorníu Pinot Noir',
        'Cava rosado de Codorníu elaborado con pinot noir vinificada como rosado, aportando color y estructura al espumoso. Método tradicional con crianza sobre lías en las cavas centenarias de Sant Sadurní d\'Anoia. Un cava con personalidad propia y carácter frutal.',
        "Nariz: Aromas de fresa, cereza, pomelo rosado y un toque floral de rosa con notas de levadura y brioche.\nBoca: Burbuja fina y agradable, con sabores de frutos rojos, cítricos y un toque de pan tostado, buena frescura.\nFinal: Medio y refrescante, con recuerdos de frambuesa y una acidez viva que limpia el paladar."
    ],
    [743, 'Cava Juvé & Camps Reserva de la Familia',
        'Gran Reserva de Juvé & Camps, la expresión más selecta de esta casa centenaria del Penedès. Macabeo, xarel·lo y parellada de las mejores parcelas, con más de 30 meses de crianza sobre lías. Degüelle manual y producción limitada para un cava de extraordinaria complejidad.',
        "Nariz: Aromas complejos de frutos secos tostados, brioche, miel de acacia y cítricos maduros con notas de flores secas y un fondo mineral.\nBoca: Burbuja finísima y cremosa, gran estructura y volumen, sabores de avellana, pan tostado, fruta confitada y notas yodadas.\nFinal: Muy largo y memorable, con recuerdos de frutos secos, cera de abeja y una salinidad mineral elegante."
    ],
    [856, 'Anna de Codorníu Brut Nature',
        'Cava Brut Nature de la gama Anna de Codorníu, sin adición de azúcar en el licor de expedición. Elaborado con chardonnay y variedades autóctonas del Penedès. Un espumoso seco y expresivo que muestra la pureza del cava catalán.',
        "Nariz: Aromas de manzana verde, lima, flores blancas y notas de pan tostado con un fondo mineral fresco.\nBoca: Burbuja fina, seco y directo, con sabores de cítricos, fruta blanca, almendra y una acidez vibrante.\nFinal: Largo y limpio, con un retrogusto mineral y cítrico que invita a otro sorbo."
    ],
    [457, 'Cava Mirgin Reserva Rosé',
        'Cava rosado de Alta Alella elaborado con garnacha de viñedos ecológicos en la D.O. Alella. Método tradicional con crianza sobre lías que aporta complejidad al carácter frutal del rosado. La cercanía al mar Mediterráneo imprime una salinidad única.',
        "Nariz: Aromas de fresas frescas, frambuesa, pomelo rosado y flores silvestres con un sutil toque de levadura.\nBoca: Burbuja fina y delicada, sabores de frutos rojos, cítricos y un carácter salino mediterráneo, buena acidez.\nFinal: Medio-largo, fresco y afrutado, con recuerdos de fresa y una mineralidad marina persistente."
    ],

    // ===== LICORES / DESTILADOS (19) =====
    [22, 'Anís del Mono Dulce',
        'Anís español histórico elaborado por la destilería del Mono en Badalona desde 1870. Su botella facetada de cristal tallado y la etiqueta con el mono darwiniano son iconos del diseño industrial español. Destilado de grano de anís verde matalahúga, con un perfil dulce y envolvente.',
        "Nariz: Aromas intensos de anís verde, hierbas mediterráneas y un toque de regaliz con notas de almendra.\nBoca: Dulce y envolvente, con sabores de anís estrellado, regaliz, miel y un punto de hierbas frescas.\nFinal: Largo y anisado, con un calor agradable y recuerdos de matalahúga que permanecen."
    ],
    [109, 'Veterano',
        'Brandy de Jerez elaborado por Osborne en El Puerto de Santa María, Cádiz. Envejecido mediante el sistema de criaderas y solera en botas de roble americano previamente envinadas con fino y oloroso. Veterano es uno de los brandies más populares de España desde su creación.',
        "Nariz: Aromas de pasas, caramelo tostado, vainilla y un fondo de roble con notas de frutos secos y canela.\nBoca: Cálido y suave, con sabores de uva pasa, toffee, vainilla y un punto de especias dulces.\nFinal: Medio y reconfortante, con recuerdos de madera noble y caramelo que se desvanecen con calidez."
    ],
    [192, 'Gin Gordon\'s',
        'Ginebra London Dry creada por Alexander Gordon en 1769 en Southwark, Londres. Destilada con una receta secreta de botánicos que incluye enebro, cilantro, angélica y limón. Gordon\'s es la ginebra más vendida del mundo y referencia del estilo London Dry.',
        "Nariz: Aromas claros de enebro fresco, cítricos y un toque de especias con notas de raíz de angélica.\nBoca: Seca y directa, con sabores de enebro, limón, cilantro y un punto de pimienta, textura limpia.\nFinal: Medio y seco, con un retrogusto de enebro y cítricos que perdura con elegancia."
    ],
    [197, 'Whisky J&B',
        'Blended scotch whisky de Justerini & Brooks, cuya tradición se remonta al Londres del siglo XVIII. Mezcla de más de 40 whiskies de malta y grano de Speyside y otras regiones escocesas. Concebido como un whisky deliberadamente ligero y versátil, es uno de los blends más vendidos en Europa.',
        "Nariz: Aromas delicados de hierba fresca, manzana verde y un sutil fondo de cereales malteados con notas florales.\nBoca: Ligero y suave, con sabores de pera, vainilla discreta y un toque de pimienta blanca, textura fluida.\nFinal: Breve y nítido, con recuerdos de malta tostada y hierbas aromáticas que se desvanecen limpiamente."
    ],
    [211, 'Terry',
        'Brandy de Jerez elaborado por la bodega Terry en El Puerto de Santa María, Cádiz. Envejecido en el sistema tradicional de soleras y criaderas en botas de roble americano. Un brandy clásico español de carácter suave y accesible, pensado para el consumo diario.',
        "Nariz: Aromas de caramelo, pasas, vainilla y un toque de roble con notas de fruta seca y canela.\nBoca: Suave y dulce, con sabores de uva pasa, toffee, almendra y un punto de especias.\nFinal: Medio y cálido, con recuerdos de madera y caramelo que proporcionan una sensación reconfortante."
    ],
    [232, 'Ponche Caballero',
        'Licor español único elaborado en El Puerto de Santa María, Cádiz, desde 1830. Combinación de brandy de Jerez, naranja, vainilla y hierbas aromáticas, con una receta que se ha mantenido prácticamente inalterada. Su botella plateada y su sabor inconfundible lo convierten en un clásico español.',
        "Nariz: Aromas cálidos de naranja confitada, vainilla, canela y brandy, con notas de caramelo y especias dulces.\nBoca: Dulce y envolvente, con sabores de naranja, vainilla, canela y brandy, textura sedosa y cálida.\nFinal: Largo y especiado, con recuerdos de cítricos confitados, vainilla y un calor agradable."
    ],
    [309, 'Pacharán Etxeko',
        'Pacharán navarro artesanal elaborado mediante la maceración lenta de endrinas silvestres recolectadas a mano en aguardiente de anís. Etxeko sigue la tradición ancestral navarra de este licor, donde las endrinas ceden su color, taninos y carácter frutal al destilado durante meses.',
        "Nariz: Aromas de endrina madura, ciruela roja, frutos del bosque y un fondo anisado con notas de almendra amarga.\nBoca: Dulce y frutal, con sabores de endrina, cereza negra, regaliz y anís, con una astringencia frutal agradable.\nFinal: Largo y reconfortante, con recuerdos de fruta macerada, anís y un calor suave."
    ],
    [360, 'Ron Negrita',
        'Ron de tradición caribeña embotellado en España, con presencia en el mercado desde hace décadas. Negrita es un ron de perfil suave y versátil, envejecido en barricas de roble que aportan color y redondez al destilado.',
        "Nariz: Aromas de caña de azúcar, vainilla, caramelo suave y un toque de coco con notas de fruta tropical.\nBoca: Ligero y suave, con sabores de melaza, vainilla, fruta tropical y un punto de especias dulces.\nFinal: Medio y limpio, con recuerdos de caramelo y roble que se desvanecen suavemente."
    ],
    [401, 'Ricard',
        'Pastís provenzal elaborado por la casa Pernod Ricard, fundada en Marsella en 1932. Infusión de anís estrellado y regaliz natural con extractos de hierbas provenzales. Ricard es el pastís más vendido del mundo, inseparable de la cultura del aperitivo mediterráneo francés.',
        "Nariz: Aromas intensos de anís estrellado, regaliz natural y hierbas provenzales con notas de hinojo y menta.\nBoca: Anisado y herbáceo, con sabores de regaliz, anís, hierbas aromáticas y un dulzor contenido. Se transforma al añadir agua.\nFinal: Largo y anisado, con un retrogusto de regaliz y hierbas que persiste refrescante en el paladar."
    ],
    [447, 'Licor 43',
        'Licor español elaborado en Cartagena, Murcia, con una receta familiar que combina 43 ingredientes incluyendo cítricos, frutas, hierbas y especias. Su color dorado y su sabor dulce y aromático lo han convertido en uno de los licores españoles más exportados, popular tanto solo como en cócteles.',
        "Nariz: Aromas de vainilla, cítricos, canela y frutas tropicales con notas de caramelo y un toque de especias.\nBoca: Dulce y cremoso, con sabores de vainilla, naranja, canela y hierbas dulces, textura sedosa.\nFinal: Largo y aromático, con recuerdos de vainilla, cítricos y especias que permanecen agradablemente."
    ],
    [582, 'Gin Xoriguer',
        'Ginebra de Mahón, elaborada en Menorca desde el siglo XVIII cuando la isla estaba bajo dominio británico. Destilada en alambiques de cobre con bayas de enebro y alcohol de uva (no de grano), lo que la diferencia de las ginebras convencionales. Denominación geográfica protegida.',
        "Nariz: Aromas frescos de enebro, hierbas mediterráneas, anís y un fondo vinoso distintivo de la base de uva.\nBoca: Suave y aromática, con sabores de enebro, hierbas, un punto anisado y la redondez característica del alcohol de uva.\nFinal: Medio y herbáceo, con recuerdos de enebro y un carácter vinoso que la hace única."
    ],
    [620, 'Vermú Cinzano',
        'Vermú italiano clásico de la casa Cinzano, fundada en Turín en 1757. Elaborado con vino blanco macerado con una mezcla de hierbas, especias y botánicos que incluyen ajenjo, genciana y cortezas de cítricos. Uno de los vermús más reconocidos del mundo.',
        "Nariz: Aromas de hierbas amargas, naranja amarga, especias dulces y un fondo de caramelo con notas de regaliz.\nBoca: Amargo-dulce equilibrado, con sabores de hierbas, cítricos, quina y especias, textura untuosa.\nFinal: Medio-largo y aromático, con recuerdos de hierbas amargas y cítricos que limpian el paladar."
    ],
    [633, 'Chartreuse Amarillo',
        'Licor herbáceo elaborado por los monjes cartujos en Voiron, Francia, siguiendo una receta secreta de 1605 con 130 plantas. Con 40% de graduación, la Chartreuse Amarilla es más dulce y suave que su hermana verde, con un perfil más meloso y accesible.',
        "Nariz: Aromas de miel, azafrán, anís, hierbas aromáticas y flores con un fondo de especias dulces y cítricos.\nBoca: Dulce y herbáceo, con sabores de miel, hierbas alpinas, especias dulces y un toque de regaliz, textura sedosa.\nFinal: Largo y envolvente, con recuerdos de miel, hierbas y especias que se entrelazan armoniosamente."
    ],
    [637, 'Gin Fifty Pounds',
        'Ginebra London Dry premium elaborada en Londres en lotes pequeños. Su nombre hace referencia a la ley británica de 1736 que imponía una tasa de 50 libras a quienes vendieran ginebra. Destilada con botánicos selectos que incluyen enebro, cilantro, angélica y piel de cítricos.',
        "Nariz: Aromas elegantes de enebro, lima, flores y un toque de pimienta con notas de angélica y cilantro.\nBoca: Suave y equilibrada, con sabores de enebro, cítricos frescos, flores y especias sutiles, textura aterciopelada.\nFinal: Largo y refinado, con un retrogusto de enebro y flores que persiste con delicadeza."
    ],
    [881, 'Whisky Oban 14 años',
        'Single malt de la destilería Oban, una de las más pequeñas y antiguas de Escocia, fundada en 1794 en la costa oeste de las Highlands. Envejecido 14 años en barricas de roble americano ex-bourbon. Oban se sitúa entre el estilo costero y el continental, ofreciendo un whisky equilibrado.',
        "Nariz: Aromas de naranja confitada, miel, sal marina y humo suave, con notas de malta tostada y un toque de turba.\nBoca: Sedoso y equilibrado, con sabores de caramelo, sal, fruta seca y un toque ahumado costero, textura oleosa.\nFinal: Largo y marítimo, con recuerdos de sal, humo dulce y fruta confitada que se entrelazan elegantemente."
    ],
    [986, 'Whisky Isle of Jura 10 años',
        'Single malt de la destilería Isle of Jura, la única destilería de esta isla remota de las Hébridas escocesas donde los ciervos superan en número a los habitantes. Envejecido 10 años en barricas de roble americano, ofrece un whisky ligero y costero.',
        "Nariz: Aromas de miel, aceite de oliva, sal marina y hierbas, con notas de cebada tostada y un toque de café.\nBoca: Ligero y costero, con sabores de miel, malta, especias suaves y un toque de sal con notas de roble.\nFinal: Medio y cálido, con recuerdos de miel, aceite y un sutil carácter marítimo."
    ],
    [988, 'Ron Flor de Caña 7 años Gran Reserva',
        'Ron nicaragüense premium de la familia Pellas, destilado a los pies del volcán San Cristóbal en Chichigalpa. Envejecido 7 años en barricas de bourbon de roble blanco americano. Flor de Caña se distingue por su compromiso con el envejecimiento natural sin aceleración artificial.',
        "Nariz: Aromas de vainilla, caramelo, coco tostado y frutas tropicales con notas de roble y un toque de especias dulces.\nBoca: Suave y redondo, con sabores de miel, vainilla, cacao, fruta tropical y un punto de pimienta blanca.\nFinal: Largo y elegante, con recuerdos de vainilla, madera noble y un calor envolvente."
    ],
    [990, 'Cognac Courvoisier Napoléon',
        'Cognac de la prestigiosa Maison Courvoisier, fundada en 1809 en Jarnac, Francia. El Napoléon es un ensamblaje de eaux-de-vie envejecidas entre 6 y 12 años en barricas de roble del Limousin. Courvoisier fue el cognac preferido de Napoleón Bonaparte, de ahí su apelativo histórico.',
        "Nariz: Aromas complejos de fruta seca, nuez, flores secas, vainilla y un fondo de roble con notas de canela y café.\nBoca: Elegante y redondo, con sabores de pasa, almendra tostada, caramelo, especias y un punto floral, textura sedosa.\nFinal: Muy largo y sofisticado, con recuerdos de frutos secos, roble noble y especias que perduran con calidez."
    ],
    [1177, 'Whisky Glenfiddich',
        'Single malt de Glenfiddich, la destilería más vendida del mundo fundada en 1887 por William Grant en Dufftown, Speyside. Envejecido 12 años en barricas de roble americano y europeo. Su icónico ciervo en la etiqueta simboliza el espíritu independiente de la marca.',
        "Nariz: Aromas de pera madura, miel, malta y un toque de roble con notas florales y un sutil fondo de nuez.\nBoca: Suave y afrutado, con sabores de manzana, miel, vainilla, malta y un punto de canela, textura cremosa.\nFinal: Medio-largo y elegante, con recuerdos de fruta madura, miel y un calor suave de roble."
    ],

    // ===== CERVEZAS (5) =====
    [809, 'Cerveza Piraat',
        'Cerveza belga strong ale de la cervecería Van Steenberge, en Ertvelde, Bélgica. Con 10.5% de graduación, Piraat es una cerveza de carácter potente con triple fermentación. Su etiqueta pirata refleja el espíritu aventurero de esta cerveza belga de alta graduación.',
        "Nariz: Aromas de malta dulce, plátano, levadura belga y especias, con notas de caramelo, miel y un toque alcohólico.\nBoca: Potente y compleja, con sabores de malta, frutas maduras, especias belgas y un dulzor contrarrestado por el lúpulo.\nFinal: Largo y cálido, con recuerdos de especias, malta tostada y un calor alcohólico que permanece."
    ],
    [808, 'Cerveza Guineu Jack the Ripa',
        'Double IPA de la cervecería artesanal catalana Guineu, en Valls de Torroella. Estilo americano con doble carga de lúpulos que aportan un carácter lupulado intenso. Guineu es una de las cerveceras craft de referencia en Catalunya.',
        "Nariz: Aromas explosivos de pomelo, mango, piña, resina de pino y flores con un fondo de malta tostada.\nBoca: Amarga e intensa, con sabores de cítricos tropicales, fruta de la pasión, caramelo de malta y un amargor resinoso potente.\nFinal: Largo y amargo, con recuerdos de cítricos tropicales, resina y un calor alcohólico bien integrado."
    ],
    [904, 'Cerveza BrewDog Five AM Red Ale',
        'Red ale de la cervecería escocesa BrewDog, elaborada con cinco maltas distintas y lúpulos del Pacífico Noroeste americano. Su color rojizo profundo y su perfil maltoso con carácter lupulado la convierten en una cerveza de gran equilibrio y complejidad.',
        "Nariz: Aromas de caramelo tostado, galleta, frutos rojos y lúpulos cítricos con notas de toffee y un toque herbal.\nBoca: Maltosa y equilibrada, con sabores de caramelo, frutos secos, cítricos y un amargor lupulado que compensa el dulzor.\nFinal: Medio-largo, con recuerdos de malta tostada, caramelo y un amargor seco agradable."
    ],
    [1095, 'Cerveza Cerdos Voladores',
        'Cerveza artesanal española de estilo Belgian Tripel, con su característico nombre irreverente. Elaborada con malta pálida y especias belgas siguiendo las técnicas tradicionales de fermentación belga. Cerveza de alta graduación con un perfil afrutado y especiado.',
        "Nariz: Aromas de plátano, clavo, levadura belga y miel, con notas de cítricos y especias dulces.\nBoca: Efervescente y compleja, con sabores de miel, frutas tropicales, especias belgas y un dulzor equilibrado.\nFinal: Largo y especiado, con recuerdos de miel, levadura y un calor alcohólico elegante."
    ],
    [1174, 'Cerveza Grevensteiner',
        'Cerveza alemana estilo Landbier (cerveza rural) de la cervecería Veltins, en Sauerland, Alemania. Elaborada según la Reinheitsgebot (ley de pureza alemana de 1516) con cebada, lúpulo, levadura y agua de manantial. Una cerveza artesanal que evoca la tradición cervecera rural germana.',
        "Nariz: Aromas de malta, pan recién horneado, miel y lúpulo herbal con un toque de hierba fresca.\nBoca: Redonda y maltosa, con sabores de cereales, miel, pan y un amargor lupulado suave y equilibrado.\nFinal: Medio y limpio, con recuerdos de malta, pan y un retrogusto herbal refrescante."
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
