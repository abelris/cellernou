# Textos de categoría, tanda 1 (planner: cellernou-plan-seo-contenidos-09).
# 22 páginas: las D.O. con impresiones en Search Console, cavas y champagne
# antes de Navidad, tipos de vino principales, ratafía, vermut y whisky.
#
# Reglas (docs/seo/mapa-contenidos.html): la primera frase de la intro es la
# meta description (155 caracteres como máximo); la categoría vende y el
# artículo explica; cada texto dice cómo aconsejamos y servimos; solo
# servicios confirmados (docs/seo/datos-productos-usuario.md).
# Productos citados: con stock el 25-oct-2026.
#
# Carga: python docs/seo/textos-categoria/cargar.py tanda1  (genera SQL y la página de revisión)

TEXTOS = [

# ---------------------------------------------------------------- D.O. de vinos
{
    'ambito': 'procedencia', 'id_ref': 63, 'pagina': '/vinos/do/63/d-o-bierzo',
    'titulo_seo': None,
    'intro': 'Vinos del Bierzo, la D.O. de la mencía: tintos frescos y con fruta del noroeste de León. '
             'Los que tenemos ahora en la tienda, de 9 a 23 €, y cómo elegir entre ellos.',
    'cuerpo': '''<h2>Mencía, pizarra y clima atlántico</h2>
<p>El Bierzo es un valle de montaña entre León y Galicia. La uva que lo define es la <strong>mencía</strong>: da tintos de color intenso pero ligeros de cuerpo, con fruta roja, notas florales y un punto mineral que viene de los suelos de pizarra. En blanco manda la godello.</p>
<p>Dentro de la D.O. hay dos estilos que conviene distinguir: los tintos jóvenes y frutales de los valles, fáciles de beber y buenos con la comida de diario, y los vinos de viñas viejas en ladera, con más estructura y más guarda.</p>
<h2>Los del Bierzo que tenemos</h2>
<p>Una selección corta y elegida: el <strong>Viernes</strong> y <strong>La Tellería</strong> para empezar, en torno a 10 €; <strong>Pétalos</strong>, de Descendientes de J. Palacios, que es la referencia para entender la zona; y <strong>Arena y Pizarra</strong> o <strong>Hombros</strong> si buscas un mencía con más profundidad. Si te gusta el pinot noir o los tintos del Ribeira Sacra, el Bierzo es tu zona.</p>
<p>¿Con qué? Con carnes a la brasa, embutidos, setas y platos de cuchara. Sírvelo algo fresco, a 15-16 °C. Si dudas entre dos botellas, escríbenos y te decimos cuál encaja con tu plato; te lo podemos llevar a casa en el Maresme.</p>''',
    'faqs': [
        ('¿A qué sabe un vino de mencía?',
         'A fruta roja y negra (cereza, frambuesa, mora), con notas florales y a veces un punto mineral o de hierbas. Suele ser un tinto de acidez fresca y taninos suaves, más ligero que un Ribera del Duero o un Priorat.'),
        ('¿El Bierzo tiene vinos blancos?',
         'Sí: la uva blanca de la zona es la godello, que da blancos con cuerpo, fruta blanca y algo de volumen en boca. Pregúntanos qué godellos tenemos en ese momento, del Bierzo o de la vecina Valdeorras.'),
        ('¿Con qué comida va bien un Bierzo?',
         'Con carnes a la brasa, cordero, embutidos, setas, legumbres y quesos curados. Los mencías jóvenes también van bien con pescados grasos como el atún.'),
    ],
},
{
    'ambito': 'procedencia', 'id_ref': 32, 'pagina': '/vinos/do/32/d-o-montsant',
    'titulo_seo': None,
    'intro': 'Vinos del Montsant: garnacha y cariñena de la comarca del Priorat, con carácter y a mejor precio que sus vecinos. '
             'Tenemos más de treinta, desde 6 € hasta los más especiales de la zona.',
    'cuerpo': '''<h2>La D.O. que rodea al Priorat</h2>
<p>El Montsant envuelve como un anillo a la D.O.Q. Priorat, en la comarca del Priorat (Tarragona). Comparte uvas con su vecina, <strong>garnacha y cariñena (samsó)</strong>, pero sus suelos son más variados: arcilla, calcáreo, granito y algo de pizarra. El resultado son tintos mediterráneos con fruta madura, especias y frescor, normalmente más amables y asequibles que un Priorat.</p>
<p>Es una de las zonas con mejor relación calidad-precio de Cataluña.</p>
<h2>Cómo elegir un Montsant</h2>
<ul>
<li><strong>Para el día a día (6-12 €):</strong> Olla, Joana, Lo Petito de Pascona, El Barret de la Nina o Blau.</li>
<li><strong>Para una comida especial (13-20 €):</strong> Pascona Criança, Brunus, Mirador, Dido o La Mare de Pascona.</li>
<li><strong>Para guardar o regalar:</strong> Lo Pare, El Tracte, Trossos Vells, Adicció, o un mágnum de Pascona o Jaspi.</li>
<li><strong>En blanco y rosado:</strong> Cor de Granit, Mar d'Estels, Rojalet, Brunus Rosat o Trenca-closques.</li>
</ul>
<p>Si quieres comparar, prueba un Montsant y un <a href="/vinos/do/30/d-o-q-priorat">Priorat</a> de la misma bodega o de la misma uva: se entiende enseguida qué aporta cada zona. Te ayudamos a montar la pareja en la tienda o por WhatsApp.</p>''',
    'faqs': [
        ('¿Qué diferencia hay entre Montsant y Priorat?',
         'Comparten comarca y uvas (garnacha y cariñena), pero el Priorat crece sobre llicorella, una pizarra que da vinos más concentrados y minerales, con rendimientos muy bajos. El Montsant tiene suelos más variados y suele dar tintos más frutales y fáciles de beber jóvenes, a mejor precio.'),
        ('¿Hay buenos Montsant por menos de 10 €?',
         'Sí, es una de sus virtudes. Tenemos tintos de garnacha y cariñena entre 6 y 10 € que funcionan muy bien en la mesa de diario; pregúntanos por los de esta semana.'),
        ('¿Con qué se toma un Montsant?',
         'Con carnes asadas, embutidos, arroces de montaña, cordero, setas y quesos semicurados. Los blancos de garnacha blanca van bien con pescado al horno y arroces.'),
        ('¿Me lo podéis llevar a casa?',
         'Sí. En Premià de Mar y alrededores repartimos el mismo día o al siguiente, y en el resto del Maresme en 24-48 horas, con nuestro propio transporte.'),
    ],
},
{
    'ambito': 'procedencia', 'id_ref': 33, 'pagina': '/vinos/do/33/d-o-alella',
    'titulo_seo': None,
    'intro': 'Vinos de la D.O. Alella, la denominación del Maresme: pansa blanca, garnacha y syrah a pocos kilómetros de la tienda. '
             'Blancos, tintos, rosados y dulces de las bodegas de la zona.',
    'cuerpo': '''<h2>La D.O. de casa</h2>
<p>Alella es una de las denominaciones más pequeñas de España y la más cercana a Barcelona: sus viñas están entre el mar y la sierra de Marina, en el Maresme y el Vallès. Los suelos son de <strong>sauló</strong>, un granito descompuesto, arenoso y pobre, que da vinos finos y con buena acidez.</p>
<p>La uva emblemática es la <strong>pansa blanca</strong>, el nombre que aquí recibe el xarel·lo: blancos frescos, con fruta blanca, hierbas y un punto salino. En tinto se trabajan la garnacha, la syrah y el cabernet, y hay una tradición de vinos dulces que vale la pena descubrir.</p>
<h2>Lo que tenemos de Alella</h2>
<ul>
<li><strong>Blancos de pansa blanca:</strong> Raventós d'Alella, Roura Xarel·lo, Bouquet d'Alella (joven, fermentado en bota o de viñas viejas) y Can Reon.</li>
<li><strong>Tintos:</strong> Bouquet d'Alella Garnatxa y Syrah, Parvus Syrah, Can Roda y El Pas de les Bruixes.</li>
<li><strong>Rosados y espumosos:</strong> Parvus Cabernet Rosé y el Ancestral Rosat.</li>
<li><strong>Dulce:</strong> Dolç Mataró, para acabar una comida o acompañar quesos azules.</li>
</ul>
<p>Para quien vive en el Maresme, un vino de Alella es también un buen regalo: es de aquí. Si quieres un lote de vinos de la zona, te lo preparamos.</p>''',
    'faqs': [
        ('¿Qué es la pansa blanca?',
         'Es el nombre que recibe en Alella la uva xarel·lo. En los suelos de sauló de la zona da blancos frescos y aromáticos, con notas de fruta blanca, hinojo y un toque salino, que aguantan bien uno o dos años en botella.'),
        ('¿Dónde está la D.O. Alella?',
         'Entre Barcelona y el Maresme, en las laderas de la sierra de Marina que miran al mar y en parte del Vallès. Es una de las denominaciones más pequeñas de España.'),
        ('¿Qué vino de Alella me recomendáis para empezar?',
         'Un blanco de pansa blanca joven, como el Raventós d\'Alella o el Bouquet d\'Alella. Si te gusta, el siguiente paso es uno fermentado en barrica o de viñas viejas.'),
        ('¿Preparáis lotes con vinos de Alella?',
         'Sí, preparamos regalos y lotes. Dinos para quién es y cuánto quieres gastar, y lo dejamos listo para recoger o te lo llevamos.'),
    ],
},
{
    'ambito': 'procedencia', 'id_ref': 59, 'pagina': '/vinos/do/59/d-o-catalunya',
    'titulo_seo': None,
    'intro': 'Vinos de la D.O. Catalunya: tintos y blancos de diario bien hechos, de 5 a 12 €, que mezclan uvas de toda Cataluña. '
             'Los que tenemos y para qué ocasión va cada uno.',
    'cuerpo': '''<h2>Una D.O. para toda Cataluña</h2>
<p>La D.O. Catalunya existe para permitir que una bodega elabore vino con uvas de distintas zonas catalanas, sin quedar atada a una sola comarca. Por eso aquí se encuentran muchos vinos de diario de bodegas grandes y medianas, y también algún proyecto más personal que busca uvas en varios sitios.</p>
<p>Es la etiqueta ideal para la mesa de cada día: tintos de tempranillo, garnacha, merlot o cabernet, y blancos de macabeo, xarel·lo o garnacha blanca, con precios ajustados.</p>
<h2>Los que tenemos ahora</h2>
<p>Para diario, el <strong>Castell d'Or Jove</strong>, el <strong>Desig</strong>, el <strong>Roca Xica</strong> o el <strong>Compta Ovelles</strong>, todos en torno a 5 €. En blanco, el <strong>Extrísimo</strong> de Bach, un clásico semidulce. Si quieres subir un escalón sin pasar de 12 €, el <strong>Mistik 6 mesos</strong> o el <strong>Terra Remota Ales Negres</strong>.</p>
<p>¿Buscas vino para una comida con mucha gente? Con los vinos de la D.O. Catalunya se queda bien sin disparar el presupuesto; dinos cuántos sois y te calculamos las botellas. También tenemos <a href="/vinos">vino a granel y en box</a> para fiestas.</p>''',
    'faqs': [
        ('¿Un vino D.O. Catalunya es peor que uno de otra D.O.?',
         'No necesariamente. La D.O. Catalunya indica que las uvas pueden venir de varias zonas de Cataluña; muchas bodegas la usan para sus vinos de diario y otras para proyectos que mezclan viñas de distintos sitios.'),
        ('¿Qué es el Extrísimo de Bach?',
         'Un blanco semidulce de Masia Bach, uno de los vinos más conocidos de Cataluña. Se sirve muy frío y gusta como aperitivo o con postres poco dulces.'),
        ('¿Cuántas botellas necesito para una comida de 20 personas?',
         'Como orientación, media botella de vino por persona en una comida larga, más el cava del brindis. Para 20 personas, unas 10-12 botellas de vino. Cuéntanos el menú y te lo ajustamos.'),
    ],
},
{
    'ambito': 'procedencia', 'id_ref': 15, 'pagina': '/vinos/do/15/d-o-penedes',
    'titulo_seo': None,
    'intro': 'Vinos del Penedès: blancos de xarel·lo, tintos mediterráneos y espumosos de la gran zona vinícola de Barcelona. '
             'Más de sesenta referencias en la tienda, de 4 a 76 €.',
    'cuerpo': '''<h2>Del mar a Montserrat</h2>
<p>El Penedès va desde la costa del Garraf hasta las montañas que miran a Montserrat, y esa variedad de altitudes explica la variedad de sus vinos. Es tierra de <strong>blancos</strong>: el <strong>xarel·lo</strong> es la uva que más carácter da a la zona, con cuerpo, fruta y capacidad de envejecer; el macabeo y la parellada aportan frescor. En tinto hay garnacha, sumoll, merlot, cabernet o syrah.</p>
<p>También es la cuna del espumoso catalán: aquí se elaboran cavas, Clàssic Penedès y vinos ancestrales. Los encontrarás en <a href="/cavas">cavas y espumosos</a>.</p>
<h2>Algunos Penedès que tenemos</h2>
<ul>
<li><strong>Blancos:</strong> Gran Viña Sol, Gramona Mustillant, Can Vich, Cora y Gaia de Loxarel, Durona de Mont-Rubí, La Descarada o Jean Leon.</li>
<li><strong>Tintos:</strong> Can Feixes, Black de Mont-Rubí, 77 Veremes Garnatxa, Amaltea y 790 Pecats de Loxarel.</li>
<li><strong>Rosados:</strong> Mart de Gramona, Freye, Petit Arnau o L'Amic.</li>
</ul>
<p>Con tanta oferta, lo mejor es decirnos qué te gusta: si buscas un blanco con cuerpo para un arroz, uno ligero para el aperitivo o un tinto para la carne, te proponemos dos o tres opciones. En la tienda, por WhatsApp o por teléfono.</p>''',
    'faqs': [
        ('¿Qué uva es típica del Penedès?',
         'El xarel·lo, una uva blanca autóctona que da vinos con cuerpo, fruta y notas de hierbas, y que aguanta bien el paso del tiempo. Es también una de las tres uvas clásicas del cava, junto al macabeo y la parellada.'),
        ('¿Todos los cavas son del Penedès?',
         'La mayoría se elaboran en el Penedès, sobre todo alrededor de Sant Sadurní d\'Anoia, pero la D.O. Cava abarca zonas de varias comunidades. Además, algunas bodegas del Penedès han salido del cava y elaboran sus espumosos como Corpinnat o Clàssic Penedès.'),
        ('¿Qué blanco del Penedès va con un arroz o una paella?',
         'Un xarel·lo con algo de cuerpo, como un Gramona, un Loxarel o un Mont-Rubí. Si el arroz es de marisco, también funciona un cava brut nature.'),
    ],
},
{
    'ambito': 'procedencia', 'id_ref': 30, 'pagina': '/vinos/do/30/d-o-q-priorat',
    'titulo_seo': None,
    'intro': 'Vinos del Priorat: garnacha y cariñena sobre llicorella, en una de las dos denominaciones calificadas de España. '
             'Una treintena de referencias, de 6 € a vinos de colección.',
    'cuerpo': '''<h2>Llicorella, laderas y poca producción</h2>
<p>El Priorat es una comarca de montaña del interior de Tarragona donde la viña crece en terrazas y laderas muy empinadas, sobre <strong>llicorella</strong>, una pizarra oscura que obliga a las raíces a ir muy hondo. Las cepas, muchas viejas, dan poca uva y muy concentrada: de ahí tintos potentes, minerales y de larga vida. Es D.O.Q. (Denominación de Origen Calificada), un rango que en España solo comparte con Rioja.</p>
<p>Las uvas son <strong>garnacha y cariñena</strong>, a menudo con algo de syrah o cabernet. También hay blancos de garnacha blanca y macabeo, cada vez más valorados.</p>
<h2>Priorats para cada ocasión</h2>
<ul>
<li><strong>Para descubrir la zona (6-18 €):</strong> Sedó Barceló, Mas d'en Bernat, Class Priorat, Viatge al Priorat, Nona o Terrestre.</li>
<li><strong>Para una cena especial (20-40 €):</strong> Martinet Bru, Camins del Priorat, Pedrabona, Iugiter o Mas Mallola.</li>
<li><strong>Para regalar o guardar:</strong> Les Terrasses, Voltons de La Conreria d'Scala Dei, Clos Abella, el Vi de Vila de Porrera de Álvarez Duran o Roca Grisa.</li>
<li><strong>Blancos:</strong> Consulat Petit, Loidana, Formiga Seda Blanca, Les Brugueres u Olbia.</li>
</ul>
<p>Un Priorat de gama alta es de los mejores regalos que se pueden hacer con vino. Si es para regalar, dinos el presupuesto y a quién va: te ayudamos a elegir y te lo preparamos.</p>''',
    'faqs': [
        ('¿Qué es la llicorella?',
         'Una pizarra oscura y laminada, típica del Priorat, que retiene poca agua y obliga a las cepas a buscar humedad en profundidad. Da vinos concentrados y con un perfil mineral característico.'),
        ('¿Qué significa D.O.Q.?',
         'Denominación de Origen Calificada (en catalán, Qualificada). Es el rango más alto de las denominaciones españolas, con controles más estrictos; solo lo tienen el Priorat y Rioja.'),
        ('¿Cuánto se puede guardar un Priorat?',
         'Los de gama media, 5-8 años sin problema; los grandes Priorats de viñas viejas pueden evolucionar bien durante 15 años o más si se guardan tumbados, a oscuras y a temperatura estable.'),
        ('¿El Priorat siempre es caro?',
         'No. Hay Priorats de entrada entre 6 y 15 € que muestran bien la zona. Los precios suben en los vinos de viñas viejas y de finca por su producción muy baja.'),
    ],
},
{
    'ambito': 'procedencia', 'id_ref': 14, 'pagina': '/vinos/do/14/d-o-emporda',
    'titulo_seo': None,
    'intro': 'Vinos del Empordà: garnacha, cariñena y tramontana en el norte de Girona, entre el Cap de Creus y la Albera. '
             'Más de treinta tintos, blancos, rosados y dulces en la tienda.',
    'cuerpo': '''<h2>Viña entre el mar y los Pirineos</h2>
<p>El Empordà es la zona vinícola del norte de Cataluña, junto a la frontera francesa. La tramontana, el viento del norte, mantiene las viñas sanas y secas, y los suelos van del granito y la pizarra de la Albera a las arcillas de la llanura. La <strong>garnacha (lledoner)</strong> y la <strong>cariñena (samsó)</strong> son las uvas de la casa, junto con la garnacha blanca y la gris para los blancos.</p>
<p>Además de tintos y blancos mediterráneos, el Empordà tiene un vino dulce propio, la <strong>Garnatxa de l'Empordà</strong>, perfecta para los postres y los turrones.</p>
<h2>Algunos Empordà que tenemos</h2>
<ul>
<li><strong>Tintos:</strong> Bufanuvols, Cigonya, Maragda, Empordàlia Balmeta, Perelada 5 Fincas, Mas Pòlit o Clot d'Illa.</li>
<li><strong>Blancos:</strong> Només Garnatxa Blanca, Oriol, Tramuntanart, Clarinet de Mas Pòlit, Finca La Sureda o Finca Masdeneres.</li>
<li><strong>Rosado:</strong> Terra Indika Rosat de Mas Pòlit.</li>
<li><strong>Dulce:</strong> Empordàlia Sinols Garnatxa.</li>
</ul>
<p>Si buscas un vino con personalidad que no sea de las zonas más conocidas, el Empordà es una apuesta segura. Pregúntanos y te recomendamos según el plato.</p>''',
    'faqs': [
        ('¿Qué es la Garnatxa de l\'Empordà?',
         'Un vino dulce tradicional del Empordà, elaborado con garnacha y envejecido a menudo al sol o en barricas viejas. Tiene notas de fruta pasa, frutos secos y caramelo, y acompaña muy bien postres, turrones y quesos.'),
        ('¿Cómo son los tintos del Empordà?',
         'Mediterráneos, con fruta madura, notas de hierbas de monte y una frescura que les da la tramontana. Los jóvenes son muy fáciles de beber; los de finca tienen más estructura.'),
        ('¿Con qué comida va un blanco del Empordà?',
         'Con pescados, arroces, mar y montaña y cocina de la Costa Brava. Los de garnacha blanca tienen volumen y aguantan platos con salsa.'),
    ],
},
{
    'ambito': 'procedencia', 'id_ref': 1, 'pagina': '/vinos/do/1/d-o-ca-rioja',
    'titulo_seo': None,
    'intro': 'Vinos de Rioja: crianzas, reservas y gran reservas de tempranillo, de las bodegas clásicas y de las nuevas. '
             'Casi cuarenta referencias en la tienda, desde 5 € hasta Viña Tondonia.',
    'cuerpo': '''<h2>La D.O. más conocida de España</h2>
<p>Rioja se extiende por La Rioja, Álava y Navarra, a lo largo del Ebro, y se divide en tres zonas: <strong>Rioja Alta</strong>, <strong>Rioja Alavesa</strong> y <strong>Rioja Oriental</strong>. La uva principal es el <strong>tempranillo</strong>, acompañado de garnacha, graciano y mazuelo. Fue la primera D.O. calificada de España.</p>
<p>Lo que más caracteriza a Rioja es su tradición de crianza en barrica. Las etiquetas dicen cuánto tiempo ha envejecido el vino: <strong>crianza</strong> (al menos dos años, uno en barrica), <strong>reserva</strong> (tres años, uno en barrica) y <strong>gran reserva</strong> (cinco años, dos en barrica). Hoy también hay muchos vinos de pueblo y de finca que se fijan más en el origen que en el tiempo de barrica.</p>
<h2>Riojas que tenemos</h2>
<ul>
<li><strong>Para diario:</strong> Monólogo, Ugarte, Coto Crianza, Lan, Ramón Bilbao Crianza o Finca de Landaluce.</li>
<li><strong>Reservas clásicos:</strong> Viña Pomal Reserva, Lealtanza, Hacienda López de Haro, Luis Cañas o Marqués de Riscal.</li>
<li><strong>Para una ocasión especial:</strong> Viña Tondonia Reserva, Félix Martínez La Cuesta Gran Reserva o La Cueva del Monge.</li>
<li><strong>Blancos y rosados:</strong> Monopole de Cune, Azpilicueta Blanco y Ramón Bilbao Rosat.</li>
</ul>
<p>¿Dudas entre un Rioja y un <a href="/vinos/do/3/d-o-ribera-del-duero">Ribera del Duero</a>? Es la pregunta que más nos hacen en la tienda. Cuéntanos qué vas a comer y te lo resolvemos.</p>''',
    'faqs': [
        ('¿Qué diferencia hay entre Rioja Alta, Alavesa y Oriental?',
         'Rioja Alta y Alavesa, más altas y frescas, dan vinos más finos y de buena acidez, con el tempranillo como protagonista. Rioja Oriental (antes Rioja Baja) es más cálida y seca, y en ella la garnacha tiene más peso.'),
        ('¿Qué Rioja elijo para regalar?',
         'Un reserva o gran reserva de bodega clásica siempre queda bien: Viña Tondonia, Viña Pomal o Marqués de Riscal. Si la persona conoce Rioja, un vino de finca o de pueblo puede sorprenderle más.'),
        ('¿Con qué se toma un Rioja reserva?',
         'Con cordero asado, carnes rojas, caza menor, guisos y quesos curados. Ábrelo media hora antes y sírvelo a 16-18 °C.'),
    ],
},
{
    'ambito': 'procedencia', 'id_ref': 3, 'pagina': '/vinos/do/3/d-o-ribera-del-duero',
    'titulo_seo': None,
    'intro': 'Vinos de Ribera del Duero: tempranillo de altura, potente y con fruta, de Pesquera, Emilio Moro o Pago de los Capellanes. '
             'Cuarenta referencias, de robles de diario a reservas.',
    'cuerpo': '''<h2>Tempranillo a 800 metros</h2>
<p>La Ribera del Duero sigue el río Duero por Burgos, Valladolid, Soria y Segovia, en una meseta de entre 750 y 1.000 metros. Los inviernos fríos y los veranos con mucha diferencia entre el día y la noche dan uvas con color, fruta y buena acidez. La uva es el <strong>tempranillo</strong>, que aquí llaman tinto fino o tinta del país.</p>
<p>Los Ribera suelen ser tintos de más cuerpo y fruta que los Rioja, con más presencia de la madera nueva. En la etiqueta verás <strong>roble</strong> (unos meses de barrica), <strong>crianza</strong>, <strong>reserva</strong> y <strong>gran reserva</strong>.</p>
<h2>Riberas que tenemos</h2>
<ul>
<li><strong>Robles y jóvenes (5-13 €):</strong> Silentium, Viña Arnáiz, Coto de Caleruega, Melior, Figuero 4 meses, Rudeles 23 o Protos 9 meses.</li>
<li><strong>Crianzas (14-25 €):</strong> Pesquera Crianza, Emilio Moro, Figuero 12 meses, Finca Villacreces, Legaris o Vizcarra.</li>
<li><strong>Para regalar:</strong> Pago de los Capellanes Reserva, Pesquera Reserva, Malleolus de Emilio Moro o el 8000 de Marqués de Burgos.</li>
</ul>
<p>Para una comida de Navidad con carne, un buen Ribera es una apuesta segura. Si lo quieres para un lote o un regalo de empresa, pregúntanos.</p>''',
    'faqs': [
        ('¿Qué significa «roble» en un Ribera del Duero?',
         'Que el vino ha pasado unos meses en barrica de roble, normalmente entre 3 y 6, menos que un crianza. Son tintos con la fruta muy presente y un toque de madera, ideales para diario.'),
        ('¿Es mejor un Ribera o un Rioja?',
         'Ninguno es mejor: son estilos distintos. El Ribera suele ser más potente y frutal; el Rioja clásico, más fino y con más notas de crianza. Depende del plato y de tu gusto; te ayudamos a elegir.'),
        ('¿Con qué comida va un Ribera del Duero?',
         'Con carnes rojas, lechazo asado, chuletón, caza y quesos curados. Los robles también acompañan bien hamburguesas, pasta con carne y embutidos.'),
    ],
},

# ---------------------------------------------------------------- Cavas y espumosos
{
    'ambito': 'sector', 'id_ref': 14, 'pagina': '/cavas',
    'titulo_seo': None,
    'intro': 'Cavas, champagnes y espumosos para brindar y para comer, del brut nature de diario a los grandes reservas. '
             'Más de setenta en la tienda de Premià de Mar y con reparto propio en el Maresme.',
    'cuerpo': '''<h2>Todos los espumosos, en un mismo sitio</h2>
<p>En esta sección encontrarás los <a href="/cavas/tipo/22/cava">cavas</a>, los <a href="/cavas/tipo/135/champagne">champagnes</a> y <a href="/cavas/tipo/166/otros-espumosos">otros espumosos</a>: Corpinnat, Clàssic Penedès, ancestrales, prosecco y moscato. También los <a href="/cavas/tipo/77/lambruscos">lambruscos</a>, para quien busca algo dulce y ligero.</p>
<p>La mayoría de lo que vendemos es cava catalán de productor: Carles Andreu, Maria Casanovas, Mestres, Loxarel, Parxet o Agustí Torelló Mata, además de las marcas que todo el mundo conoce. Para Navidad tenemos también formatos mágnum y cavas de larga crianza para las grandes ocasiones.</p>
<h2>Cómo te ayudamos a elegir</h2>
<p>Dinos para qué es (un brindis, una comida entera, un regalo) y cuántos sois, y te decimos qué espumoso y cuántas botellas. Si es para una celebración o para empresa, también preparamos lotes. Te lo llevamos a casa con nuestro reparto: el mismo día o al siguiente en Premià de Mar y alrededores.</p>''',
    'faqs': [
        ('¿Cuántas botellas de cava necesito para un brindis?',
         'De una botella de 75 cl salen unas 6-7 copas. Para un brindis cuenta una copa por persona; si el cava acompaña también el aperitivo o la comida, calcula media botella por persona.'),
        ('¿Preparáis lotes de cava para Navidad o para empresas?',
         'Preparamos regalos y lotes con cava, vino y algún capricho gourmet. Dinos para quién es, cuántos necesitas y el presupuesto, y te proponemos opciones.'),
    ],
},
{
    'ambito': 'tipo', 'id_ref': 22, 'pagina': '/cavas/tipo/22/cava',
    'titulo_seo': 'Comprar cava: brut nature, reserva y gran reserva | El Celler Nou',
    'intro': 'Cava de productor y de las grandes casas: brut nature, brut, rosado, reserva y gran reserva para brindar y para comer. '
             'Más de sesenta cavas en la tienda, de 3 a 76 €.',
    'cuerpo': '''<h2>Qué cava elegir</h2>
<p>El cava se elabora por el método tradicional: la segunda fermentación, la que hace la burbuja, ocurre dentro de la propia botella. Cuanto más tiempo pasa el vino con sus lías antes del degüelle, más fina es la burbuja y más notas de pan tostado y fruta seca gana. Por eso la crianza es lo primero que miramos al recomendar un cava:</p>
<ul>
<li><strong>Cava de guarda (9 meses o más):</strong> fresco y frutal, para el aperitivo y los cócteles. Por ejemplo, Josep Masachs, Nina, Trias Batlle o Castell d'Or.</li>
<li><strong>Reserva (18 meses o más):</strong> el más versátil, para brindar y para comer. Carles Andreu Reserva, Bertha, Maria Casanovas Brut de Brut o Loxarel Garnatxa Blanca.</li>
<li><strong>Gran reserva (30 meses o más):</strong> burbuja fina y complejidad, para la comida de Navidad o para regalar. Carles Andreu Gran Reserva, Mestres Coquet y Visol, Maria Casanovas Gran Reserva o Agustí Torelló Mata Kripta.</li>
</ul>
<p>El otro dato es el azúcar: el <strong>brut nature</strong> es el más seco, el que mejor acompaña la comida; el <strong>brut</strong>, algo más amable; el <strong>semiseco y el dulce</strong>, para postres. Y en <strong>rosado</strong> tenemos Anne Marie, Castell d'Or, Anna de Codorníu o Il·lusió.</p>
<h2>Cava para Navidad, con consejo</h2>
<p>Es la época en que más cava se abre, y la que más preguntas nos hacéis. Si nos dices el menú, cuántos sois y el presupuesto, te proponemos un cava para el aperitivo y otro para la comida, y te lo llevamos a casa en el Maresme. Si prefieres champagne, mira nuestros <a href="/cavas/tipo/135/champagne">champagnes</a>; y si dudas entre los dos, lo explicamos en la <a href="/wineworld/guias-de-vino/guia-definitiva-regalar-vino-cava-licores">guía para regalar vino, cava y licores</a>.</p>''',
    'faqs': [
        ('¿Qué cava elijo para la comida de Navidad?',
         'Un brut nature reserva o gran reserva: es seco, tiene burbuja fina y aguanta desde los entrantes de marisco hasta las carnes blancas. Para los postres, mejor un semiseco o un dulce.'),
        ('¿Cuántas botellas de cava hacen falta por persona?',
         'Para el brindis, una copa por persona (de una botella salen 6-7). Si el cava acompaña el aperitivo o toda la comida, calcula media botella por persona.'),
        ('¿Un cava caro es siempre mejor?',
         'No siempre, pero el precio suele reflejar el tiempo de crianza: un gran reserva pasa más de dos años y medio en la bodega. Para cócteles o una fiesta, un cava joven bien hecho es la mejor opción.'),
        ('¿Me lo podéis llevar frío?',
         'Te lo llevamos a casa con nuestro reparto en el Maresme. Si lo necesitas para servir enseguida, coméntanoslo al hacer el pedido.'),
    ],
},
{
    'ambito': 'tipo', 'id_ref': 135, 'pagina': '/cavas/tipo/135/champagne',
    'titulo_seo': 'Comprar champagne | Cavas y espumosos de El Celler Nou',
    'intro': 'Champagne francés para las grandes ocasiones: Moët & Chandon, Abelé 1757 y otras casas de la región de Champagne. '
             'Una selección corta, y te conseguimos la que busques.',
    'cuerpo': '''<h2>Champagne: lo que hay que saber</h2>
<p>Solo se puede llamar champagne al espumoso elaborado en la región de Champagne, en el noreste de Francia. Se hace por el mismo método que el cava (segunda fermentación en botella), pero con otras uvas, <strong>chardonnay, pinot noir y pinot meunier</strong>, y en un clima mucho más frío, que da vinos de acidez alta, burbuja muy fina y notas de bollería y fruta.</p>
<p>En la etiqueta, <strong>brut</strong> es el estilo más habitual; <strong>blanc de blancs</strong> indica que solo lleva chardonnay, y <strong>rosé</strong>, que lleva vino tinto de pinot noir. Los champagnes sin añada mezclan varios años para mantener el estilo de cada casa.</p>
<h2>Los que tenemos</h2>
<p>Ahora mismo tenemos <strong>Moët & Chandon Rosé Impérial</strong>, <strong>Abelé 1757</strong>, una de las casas más antiguas de Champagne, y <strong>Pierre Mignon Brut</strong>, una opción de productor con muy buena relación calidad-precio. Si buscas una marca o una añada concretas, pregúntanos: si no la tenemos, te decimos si podemos conseguirla.</p>
<p>¿Champagne o cava? Depende de la ocasión y del presupuesto: un buen <a href="/cavas/tipo/22/cava">cava gran reserva</a> compite con muchos champagnes por la mitad de precio. Te lo explicamos en la tienda o por WhatsApp.</p>''',
    'faqs': [
        ('¿Qué uvas lleva el champagne?',
         'Principalmente chardonnay, pinot noir y pinot meunier. Un blanc de blancs es solo de chardonnay; un blanc de noirs, solo de uvas tintas.'),
        ('¿Podéis conseguirme un champagne que no tenéis?',
         'Pregúntanos por la marca o la añada que buscas y te decimos si podemos conseguirla y en qué plazo.'),
        ('¿A qué temperatura se sirve el champagne?',
         'Entre 7 y 9 °C. Los más jóvenes, algo más fríos; los de añada o gran crianza, a 9-10 °C para que se aprecien sus aromas.'),
    ],
},
{
    'ambito': 'tipo', 'id_ref': 166, 'pagina': '/cavas/tipo/166/otros-espumosos',
    'titulo_seo': 'Corpinnat, Clàssic Penedès, prosecco y otros espumosos | El Celler Nou',
    'intro': 'Espumosos que no son cava ni champagne: Corpinnat, Clàssic Penedès, ancestrales, prosecco y moscato d\'Asti. '
             'Burbujas distintas para quien quiere salir de lo de siempre.',
    'cuerpo': '''<h2>Más allá del cava</h2>
<p>En los últimos años, varias bodegas del Penedès han dejado la D.O. Cava para agruparse en marcas propias con reglas más estrictas. <strong>Corpinnat</strong> exige uva ecológica, vendimiada a mano en el Penedès central y largas crianzas; aquí tenemos, por ejemplo, <strong>Gramona Brut Cuvée</strong> y <strong>Castellroig</strong>. <strong>Clàssic Penedès</strong> es el espumoso de la D.O. Penedès, también ecológico y con 15 meses de crianza mínima, como el <strong>Loxarel Vintage</strong>.</p>
<p>Los <strong>ancestrales</strong> se embotellan antes de terminar la primera fermentación: burbuja más suave, fruta y a veces algo de turbidez. Prueba el <strong>Ida &amp; Peter</strong> del Montsant.</p>
<h2>Prosecco y moscato</h2>
<p>De Italia tenemos <strong>prosecco</strong> (Mionetto, Romeo &amp; Giulietta), fresco y aromático, ideal para el aperitivo o el spritz, y <strong>Tosti Moscato d'Asti</strong>, dulce, con poca graduación y perfecto con los postres. Para algo dulce y tinto, mira los <a href="/cavas/tipo/77/lambruscos">lambruscos</a>.</p>''',
    'faqs': [
        ('¿Qué es Corpinnat?',
         'Una marca colectiva de bodegas del Penedès que salieron de la D.O. Cava. Sus espumosos son de uva ecológica del Penedès central, vendimiada a mano, con un mínimo de 18 meses de crianza.'),
        ('¿El prosecco se hace como el cava?',
         'No. El prosecco hace la segunda fermentación en grandes depósitos (método Charmat), no en la botella. Por eso es más fresco y aromático, con menos notas de crianza.'),
        ('¿Qué espumoso elijo para un spritz?',
         'Un prosecco seco como el Mionetto. Con Aperol o Campari y un poco de soda queda el spritz clásico.'),
    ],
},
{
    'ambito': 'tipo', 'id_ref': 77, 'pagina': '/cavas/tipo/77/lambruscos',
    'titulo_seo': 'Comprar lambrusco | Cavas y espumosos de El Celler Nou',
    'intro': 'Lambrusco italiano, tinto, rosado y blanco: espumoso, ligero y algo dulce, a menos de 5 €. '
             'Para quien busca un vino fácil, fresco y con poca graduación.',
    'cuerpo': '''<h2>Qué es el lambrusco</h2>
<p>El lambrusco es un vino de aguja de Emilia-Romaña, en el norte de Italia, hecho con la uva del mismo nombre. Tiene burbuja suave, poca graduación (entre 7 y 11 % en muchos casos) y suele ser <strong>amabile</strong>, es decir, semidulce. Se sirve muy frío y gusta a quien no suele beber vino.</p>
<p>Es un vino para no complicarse: va bien en una fiesta, en una barbacoa o con una pizza, y a ese precio se puede comprar por cajas.</p>
<p>Tenemos <strong>Decordi</strong> en blanco y rosado, <strong>Borgo Imperial</strong> rosado y <strong>Dellemilia Amabile</strong>, todos a muy buen precio para fiestas y comidas informales.</p>
<p>Si buscas burbuja con algo más de nivel, mira el <a href="/cavas/tipo/166/otros-espumosos">prosecco y el moscato d'Asti</a>, o los <a href="/cavas/tipo/22/cava">cavas</a> semisecos.</p>''',
    'faqs': [
        ('¿El lambrusco es dulce?',
         'Los más habituales son amabile (semidulces), aunque también hay lambruscos secos. Los que tenemos son de estilo amable y fresco.'),
        ('¿Con qué se toma el lambrusco?',
         'En Italia, con embutidos, pizza y pasta con salsa de tomate. También funciona como aperitivo frío o con postres de fruta.'),
    ],
},

# ---------------------------------------------------------------- Vinos
{
    'ambito': 'sector', 'id_ref': 8, 'pagina': '/vinos',
    'titulo_seo': None,
    'intro': 'Más de seiscientos vinos con stock en la tienda: tintos, blancos, rosados y dulces de las principales D.O. de España y de fuera. '
             'Elegidos uno a uno y con consejo para cada ocasión.',
    'cuerpo': '''<h2>Cómo está organizado</h2>
<p>Puedes buscar por tipo (<a href="/vinos/tipo/1/vino-tinto">tinto</a>, <a href="/vinos/tipo/2/vino-blanco">blanco</a>, <a href="/vinos/tipo/3/vino-rosado">rosado</a>) o por denominación de origen. Tenemos sobre todo las catalanas, <a href="/vinos/do/32/d-o-montsant">Montsant</a>, <a href="/vinos/do/30/d-o-q-priorat">Priorat</a>, <a href="/vinos/do/15/d-o-penedes">Penedès</a>, <a href="/vinos/do/14/d-o-emporda">Empordà</a> y <a href="/vinos/do/33/d-o-alella">Alella</a>, la D.O. de aquí, y las clásicas <a href="/vinos/do/1/d-o-ca-rioja">Rioja</a> y <a href="/vinos/do/3/d-o-ribera-del-duero">Ribera del Duero</a>.</p>
<p>Hay vino para cada día desde 3-5 €, y botellas de colección para regalar o guardar. Y si buscas algo concreto que no ves, pregúntanos: el catálogo cambia cada semana.</p>
<h2>Lo que no está en la web: el consejo</h2>
<p>Una tienda de vinos de barrio sirve para eso: para que nos cuentes qué vas a comer, para cuántos y cuánto quieres gastar, y salir con la botella adecuada. Lo hacemos en la tienda, por WhatsApp o por teléfono, y te lo llevamos a casa con nuestro reparto en el Maresme.</p>''',
    'faqs': [
        ('¿Tenéis vinos baratos para diario?',
         'Sí, muchos entre 3 y 8 €, y también vino a granel y en box. Pregúntanos y te recomendamos los que mejor están de esta semana.'),
        ('¿Puedo encargar un vino que no tenéis?',
         'Sí. Dinos qué buscas y te decimos si podemos conseguirlo y cuándo.'),
    ],
},
{
    'ambito': 'tipo', 'id_ref': 1, 'pagina': '/vinos/tipo/1/vino-tinto',
    'titulo_seo': None,
    'intro': 'Vino tinto de toda España y de fuera: más de 350 tintos con stock, de 3 € a grandes reservas. '
             'Te ayudamos a elegir por zona, por uva, por plato o por presupuesto.',
    'cuerpo': '''<h2>Cómo elegir un vino tinto</h2>
<p>Con tantos tintos, lo más útil es empezar por el estilo que te gusta:</p>
<ul>
<li><strong>Ligeros y frutales:</strong> mencía del <a href="/vinos/do/63/d-o-bierzo">Bierzo</a>, garnachas jóvenes, pinot noir. Para pasta, pizza, embutidos o pescados grasos.</li>
<li><strong>Mediterráneos, con fruta madura:</strong> <a href="/vinos/do/32/d-o-montsant">Montsant</a>, <a href="/vinos/do/14/d-o-emporda">Empordà</a> o Terra Alta. Para arroces de montaña, carnes a la brasa y cocina de diario.</li>
<li><strong>Clásicos con crianza:</strong> <a href="/vinos/do/1/d-o-ca-rioja">Rioja</a> reserva y gran reserva. Para cordero, guisos y caza.</li>
<li><strong>Potentes y con cuerpo:</strong> <a href="/vinos/do/3/d-o-ribera-del-duero">Ribera del Duero</a>, <a href="/vinos/do/30/d-o-q-priorat">Priorat</a> o Toro. Para carnes rojas y quesos curados.</li>
</ul>
<p>En la etiqueta, «joven» o «roble» quiere decir poca o ninguna barrica; «crianza», «reserva» y «gran reserva», cada vez más tiempo de envejecimiento. Más madera no es mejor: es otro estilo.</p>
<h2>Con consejo, en la tienda o por WhatsApp</h2>
<p>Cuéntanos el plato, cuántos sois y cuánto quieres gastar, y te proponemos dos o tres tintos. Sirve también para regalar: un buen tinto con caja es de los regalos que nunca fallan, y te lo preparamos.</p>''',
    'faqs': [
        ('¿A qué temperatura se sirve el vino tinto?',
         'Los jóvenes y ligeros, a 14-16 °C; los de crianza y con cuerpo, a 16-18 °C. «Temperatura ambiente» en un piso con calefacción suele ser demasiado: mejor 15 minutos en la nevera antes de abrirlo.'),
        ('¿Qué tinto elijo para regalar?',
         'Un clásico reconocible (un Rioja reserva, un Ribera crianza de bodega conocida) o algo de la zona que sorprenda, como un Priorat o un Montsant de viñas viejas. Dinos el presupuesto y te proponemos opciones.'),
        ('¿Tengo que decantar el vino tinto?',
         'No es imprescindible. Los tintos jóvenes no lo necesitan; los potentes y de crianza ganan si se abren o decantan media hora antes. Los vinos muy viejos se decantan solo para separar los posos.'),
    ],
},
{
    'ambito': 'tipo', 'id_ref': 2, 'pagina': '/vinos/tipo/2/vino-blanco',
    'titulo_seo': None,
    'intro': 'Vino blanco para todos los gustos: casi doscientos blancos con stock, de xarel·lo, pansa blanca, albariño, verdejo, godello o garnacha blanca. '
             'Frescos, con cuerpo, con barrica o dulces.',
    'cuerpo': '''<h2>Cómo elegir un vino blanco</h2>
<ul>
<li><strong>Frescos y aromáticos:</strong> verdejo de Rueda, albariño de Rías Baixas, sauvignon blanc. Para el aperitivo, ensaladas, marisco y pescado a la plancha.</li>
<li><strong>Mediterráneos y con carácter:</strong> xarel·lo del <a href="/vinos/do/15/d-o-penedes">Penedès</a>, pansa blanca de <a href="/vinos/do/33/d-o-alella">Alella</a>, garnacha blanca del <a href="/vinos/do/14/d-o-emporda">Empordà</a> y Terra Alta. Para arroces, pescado al horno y cocina catalana.</li>
<li><strong>Con cuerpo o con barrica:</strong> godello, chardonnay, blancos fermentados en bota. Para carnes blancas, pescados con salsa y quesos.</li>
<li><strong>Dulces y semidulces:</strong> moscatel, vinos de vendimia tardía o el Extrísimo de Bach. Para postres y foie.</li>
</ul>
<p>Un blanco se sirve frío, pero no helado: a 8-10 °C los ligeros y a 10-12 °C los que tienen cuerpo o barrica.</p>
<h2>El blanco de aquí</h2>
<p>Estamos en tierra de blancos: el Penedès y Alella tienen algunos de los mejores de España. Si nunca has probado una pansa blanca de Alella, pregúntanos: es el vino de la zona. Y si es para una comida de marisco o para regalar, te ayudamos a elegir y te lo llevamos a casa.</p>''',
    'faqs': [
        ('¿Qué vino blanco va con marisco?',
         'Un blanco fresco y con buena acidez: albariño, xarel·lo joven, pansa blanca o un cava brut nature. Para marisco a la plancha o hervido, mejor sin barrica.'),
        ('¿Cuánto dura un vino blanco abierto?',
         'Bien tapado y en la nevera, dos o tres días. Los blancos con cuerpo aguantan algo más que los muy ligeros.'),
        ('¿Hay vinos blancos para guardar?',
         'Sí. Los xarel·los con crianza, los godellos y los blancos de garnacha blanca con barrica pueden mejorar varios años en botella.'),
    ],
},
{
    'ambito': 'tipo', 'id_ref': 3, 'pagina': '/vinos/tipo/3/vino-rosado',
    'titulo_seo': None,
    'intro': 'Vino rosado para el aperitivo y para la mesa: rosados de Penedès, Montsant, Alella, Rioja o Navarra. '
             'Del pálido de estilo provenzal al rosado con cuerpo, de 5 a 20 €.',
    'cuerpo': '''<h2>Dos estilos de rosado</h2>
<p>Hoy conviven dos maneras de hacer rosado. Los <strong>pálidos</strong>, de estilo provenzal, con poco contacto con la piel de la uva: delicados, frescos, con fruta roja suave. Y los <strong>rosados de color intenso</strong>, de garnacha o tempranillo, más frutales y con más cuerpo, que aguantan bien la comida.</p>
<p>Tenemos rosados del <a href="/vinos/do/15/d-o-penedes">Penedès</a> (Mart de Gramona, Freye, Petit Arnau, L'Amic), del <a href="/vinos/do/32/d-o-montsant">Montsant</a> (Brunus, Trenca-closques), de <a href="/vinos/do/33/d-o-alella">Alella</a> (Parvus Cabernet Rosé), de Terra Alta, Navarra, Somontano y <a href="/vinos/do/1/d-o-ca-rioja">Rioja</a>, y también espumosos rosados en <a href="/cavas/tipo/22/cava">cavas</a>.</p>
<p>¿Con qué? Con el aperitivo, arroces, pasta, ensaladas, cocina asiática o un pescado azul. Sírvelo a 8-10 °C. Si no sabes cuál, dinos el plato y te lo recomendamos.</p>''',
    'faqs': [
        ('¿El rosado es solo para el verano?',
         'No. Los rosados con cuerpo acompañan muy bien arroces, pasta y carnes blancas todo el año, y un rosado espumoso es una buena opción para las fiestas.'),
        ('¿Cómo se hace un vino rosado?',
         'Con uvas tintas que se dejan poco tiempo en contacto con su piel, de unas horas a un día: el mosto toma algo de color y de fruta, pero no la estructura de un tinto.'),
    ],
},

# ---------------------------------------------------------------- Licores
{
    'ambito': 'sector', 'id_ref': 2, 'pagina': '/licores',
    'titulo_seo': None,
    'intro': 'Whisky, ginebra, ron, vermut, ratafía y licores: más de 250 referencias con stock, de las marcas de siempre a destilados de pequeño productor. '
             'Para la copa de casa y para regalar.',
    'cuerpo': '''<h2>Qué encontrarás</h2>
<p>Una sección de destilados amplia y elegida: <a href="/licores/tipo/6/whisky">whisky</a> escocés, irlandés, japonés y americano; ginebras de todo el mundo; rones de añejo; <a href="/licores/tipo/8/vermut">vermut</a> de Reus y de la casa; <a href="/licores/tipo/55/ratafia">ratafía</a> catalana; y licores, pacharán, orujos y aguardientes.</p>
<p>En ginebra hay London Dry clásicas, ginebras mediterráneas y de autor; en ron, desde los blancos para cóctel hasta añejos de Venezuela, Guatemala o el Caribe para tomar solos. Y para la sobremesa, pacharán, orujos, licores de hierbas y aguardientes.</p>
<p>Muchas de las botellas vienen en estuche o en formatos especiales, y son de los regalos más agradecidos en Navidad. Si no sabes por dónde empezar, te ayudamos a elegir según a quién va y lo que le gusta, en la tienda, por WhatsApp o por teléfono, y te lo llevamos a casa en el Maresme.</p>''',
    'faqs': [
        ('¿Qué licor es un buen regalo?',
         'Un whisky single malt, un ron añejo o una ginebra premium en estuche son apuestas seguras. Si la persona es de aquí, una ratafía artesana o un vermut de Reus también sorprenden. Dinos el presupuesto y te proponemos opciones.'),
        ('¿Preparáis lotes con licores?',
         'Sí, preparamos regalos y lotes. Podemos combinar un licor con vino, cava o algún producto gourmet.'),
    ],
},
{
    'ambito': 'tipo', 'id_ref': 55, 'pagina': '/licores/tipo/55/ratafia',
    'titulo_seo': 'Comprar ratafía catalana | Licores de El Celler Nou',
    'intro': 'Ratafía catalana artesana: Quevall, Raiers, Russet, Moliné, Les Bruixes y otras. '
             'El licor de nueces verdes y hierbas de las casas de pagès, de 11 a 21 €, también en petaca y en bag in box.',
    'cuerpo': '''<h2>Qué es la ratafía</h2>
<p>La ratafía es un licor tradicional catalán que se elabora macerando <strong>nueces verdes</strong> en aguardiente, con hierbas, especias y azúcar. Las nueces se recogen tiernas a principios del verano, y cada casa tenía su receta: hinojo, canela, clavo, hierba luisa, manzanilla… Después de semanas al sol y de reposar, sale un licor oscuro, aromático, entre dulce y amargo, que se toma solo y fresco después de comer.</p>
<p>Las ratafías de hoy siguen ese espíritu artesano, cada una con su equilibrio entre la nuez, las hierbas y el dulzor.</p>
<h2>Las ratafías que tenemos</h2>
<ul>
<li><strong>Quevall</strong>, de nueces, de hierbas, de membrillo (codony) y la Clandestina.</li>
<li><strong>Raiers</strong> en botella y en petaca, <strong>Russet</strong>, <strong>Moliné</strong>, <strong>Bosch</strong> y <strong>Melody</strong>.</li>
<li><strong>Les Bruixes</strong>, <strong>Malhivern</strong>, <strong>Maus</strong> y <strong>Mina de Magnolies</strong> de Bellmunt.</li>
<li>Y <strong>ratafía en bag in box de 3 litros</strong>, para restaurantes o para quien la toma a menudo.</li>
</ul>
<p>Una ratafía es un regalo muy de aquí, y en un lote con vino de Alella o un vermut queda redondo. Si quieres probar estilos distintos, pregúntanos: te explicamos en qué se diferencian.</p>''',
    'faqs': [
        ('¿Cómo se toma la ratafía?',
         'Sola, en copa pequeña, fresca o con un cubito, después de comer. También se usa en cócteles y para dar sabor a postres y helados.'),
        ('¿Qué graduación tiene la ratafía?',
         'Normalmente entre 23 y 30 grados, según el productor.'),
        ('¿Cuánto dura una ratafía abierta?',
         'Mucho: al ser un licor con alcohol y azúcar, aguanta meses bien cerrada y lejos del calor. Con el tiempo puede suavizarse, pero no se estropea.'),
    ],
},
{
    'ambito': 'tipo', 'id_ref': 8, 'pagina': '/licores/tipo/8/vermut',
    'titulo_seo': 'Comprar vermut negre y blanco | Licores de El Celler Nou',
    'intro': 'Vermut negre, blanco y rosado: de Reus, del Maresme, de Italia y de Jerez, y también a granel. '
             'Más de veinte vermuts para la hora del vermut del domingo.',
    'cuerpo': '''<h2>El vermut en Cataluña</h2>
<p>El vermut es un vino aromatizado con hierbas, raíces y especias, con el ajenjo como planta principal. Llegó de Italia en el siglo XIX y en Cataluña echó raíces, sobre todo en <strong>Reus</strong>, que llegó a tener decenas de elaboradores. El <strong>vermut negre</strong>, dulce y especiado, es el más tradicional; el <strong>blanco</strong>, más fresco y floral; y cada vez hay más vermuts de reserva y de autor.</p>
<h2>Los vermuts que tenemos</h2>
<ul>
<li><strong>Del Camp de Tarragona:</strong> Padró y Dos Deus (Origins, Estrelles, Dip Smoked).</li>
<li><strong>Del Maresme y alrededores:</strong> Espinaler, de Vilassar de Mar, y Perucchi, de Badalona.</li>
<li><strong>De autor:</strong> Casa Mariol (Terra Alta), Macarrilla, Can Roda Clarea, Petroni o Cabecita Loca.</li>
<li><strong>Clásicos:</strong> Martini Rosso y Rosato, Cisa, y el Lustau de Jerez.</li>
<li><strong>A granel</strong>, en litro y en botella, y Donzell en formato de 5 litros.</li>
</ul>
<p>Para servirlo: en vaso bajo con hielo, una rodaja de naranja y una aceituna, y si quieres, un chorrito de sifón. Con unas <a href="/conservas">conservas</a> de berberechos o mejillones, ya tienes el vermut del domingo.</p>''',
    'faqs': [
        ('¿Qué diferencia hay entre vermut negre y blanco?',
         'El negre (rojo) lleva caramelo y especias dulces, y es más intenso y amargo. El blanco es más floral, fresco y suave. Los dos se toman con hielo y naranja.'),
        ('¿Cuánto dura un vermut abierto?',
         'Es un vino: una vez abierto, mejor en la nevera y en unas semanas. Los de mayor graduación aguantan algo más, pero pierden aroma con el tiempo.'),
        ('¿Vendéis vermut a granel?',
         'Sí, tenemos vermut a granel en litro y en botella, además de formatos grandes para bares o fiestas. Pregúntanos por disponibilidad.'),
    ],
},
{
    'ambito': 'tipo', 'id_ref': 6, 'pagina': '/licores/tipo/6/whisky',
    'titulo_seo': 'Comprar whisky: single malt, blended, bourbon y japonés | El Celler Nou',
    'intro': 'Whisky escocés, irlandés, japonés y americano: más de setenta referencias, de blends de diario a single malts de 15 años. '
             'Para la copa de casa, para regalar y para coleccionar.',
    'cuerpo': '''<h2>Por dónde empezar</h2>
<ul>
<li><strong>Blended escocés:</strong> el estilo más fácil y versátil. Chivas 12, The Famous Grouse o Johnnie Walker.</li>
<li><strong>Single malt de Speyside, las Highlands y las islas:</strong> elegantes, con fruta y vainilla. Glenfiddich, Glenrothes 10 y 12, Speyburn 10, Glengoyne 12, Tomatin 12 o Arran 10.</li>
<li><strong>De Islay, ahumados:</strong> Laphroaig 10, Bowmore, Smokehead o Islay Mist. Para quien busca turba.</li>
<li><strong>Irlandés:</strong> suave y afrutado. Jameson, Bushmills, Black Bush o Natterjack.</li>
<li><strong>Japonés:</strong> Nikka, Akashi o Asaka, cada vez más buscados.</li>
<li><strong>Bourbon y americano:</strong> Maker's Mark, Redemption o Chicken Cock.</li>
</ul>
<p>Y para ocasiones especiales, single malts de larga crianza como <strong>Macallan 15</strong> o <strong>Tamdhu 15</strong>.</p>
<h2>Whisky para regalar</h2>
<p>Un whisky es de los regalos de Navidad más pedidos. Si nos dices qué suele beber la persona y el presupuesto, te recomendamos uno que le sorprenda. Si quieres saber más, lee nuestra <a href="/wineworld/destilados/guia-whisky-single-malt-blended-como-elegir">guía de whisky: single malt, blended y cómo elegir</a>.</p>''',
    'faqs': [
        ('¿Qué whisky regalo a alguien que empieza?',
         'Un single malt suave de Speyside o de las Highlands, como Glenrothes, Glengoyne o Speyburn, o un irlandés como Bushmills. Evita para empezar los muy ahumados de Islay.'),
        ('¿Qué es un whisky de turba?',
         'Uno elaborado con cebada secada con humo de turba, típico de la isla de Islay. Tiene aromas ahumados, medicinales y marinos: Laphroaig o Smokehead son buenos ejemplos.'),
        ('¿El whisky caduca?',
         'No. Cerrado se conserva indefinidamente de pie y lejos de la luz. Abierto, si la botella está por debajo de la mitad, conviene tomarlo en uno o dos años para que no pierda aroma.'),
    ],
},
]
