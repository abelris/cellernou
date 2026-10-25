# Textos de categoría, tanda 3 (planner: cellernou-plan-seo-contenidos-11).
# 16 páginas: los tipos de licor indexables (pacharán, orujo, hierbas, ginebra,
# ron, brandy, coñac, anís, licor de naranja, vodka, tequila, bourbon y grappa)
# y los orígenes de cerveza Cataluña, España y Bélgica (ámbito 'origen').
#
# Fuera, con motivo: absenta y ponche (tras reclasificar, 1-2 productos con
# stock: noindex), limoncello (2), aperitivos y «Licor» (cajones de sastre),
# sangría (verano).
#
# Mismas reglas que las tandas 1 y 2 (docs/seo/mapa-contenidos.html).
# Productos citados: con stock el 26-oct-2026, ya reclasificados.
#
# Carga: python docs/seo/textos-categoria/cargar.py tanda3  (genera SQL y la página de revisión)

TEXTOS = [

# ---------------------------------------------------------------- Licores
{
    'ambito': 'tipo', 'id_ref': 17, 'pagina': '/licores/tipo/17/pacharan',
    'titulo_seo': None,
    'intro': 'Pacharán navarro: endrinas maceradas en anís, el digestivo de sobremesa por excelencia. '
             'Baines, Etxeko, Beloki y Belasco en la tienda, de 9,50 a 25 €.',
    'cuerpo': '''<h2>Qué es el pacharán</h2>
<p>El pacharán (patxaran en euskera) es un licor de <strong>endrinas</strong>, el fruto del endrino, maceradas durante meses en anís. Es tradicional de Navarra, donde tiene indicación geográfica propia: el Pacharán Navarro. Sale un licor de color rojo intenso, con fruta, un punto ácido y el anís de fondo.</p>
<p>Se toma muy frío o con hielo, después de comer. En Navidad, cuando la sobremesa se alarga, es de los que más salen.</p>
<h2>Los pacharanes que tenemos</h2>
<ul>
<li><strong>Para casa:</strong> Beloki en litro y Patxaran Etxeko.</li>
<li><strong>Navarros con más recorrido:</strong> Etxeco Patxarana y Baines, en litro.</li>
<li><strong>Para regalar:</strong> Baines Oro y Belasco Oro, sus versiones más cuidadas.</li>
</ul>
<p>Un pacharán y unos turrones hacen un regalo de Navidad sencillo que siempre se agradece. Te lo preparamos en la tienda y te lo llevamos a casa en el Maresme. Si buscas otros licores de sobremesa, mira la <a href="/licores/tipo/55/ratafia">ratafía</a> o el <a href="/licores/tipo/7/orujo">orujo</a>.</p>''',
    'faqs': [
        ('¿Cómo se toma el pacharán?',
         'Muy frío, de la nevera o con un par de cubitos, en vaso corto y después de comer. Algunos lo prefieren a temperatura ambiente, pero el frío suaviza el anís y realza la fruta.'),
        ('¿Qué diferencia hay entre pacharán y ratafía?',
         'El pacharán se hace con endrinas en anís y es navarro; la ratafía, con nueces verdes y hierbas en aguardiente, y es catalana. Los dos son licores de sobremesa.'),
        ('¿Cuánto dura una botella de pacharán abierta?',
         'Mucho: por su graduación aguanta meses bien cerrada y lejos del sol. Con el tiempo puede perder algo de color y de fruta.'),
    ],
},
{
    'ambito': 'tipo', 'id_ref': 7, 'pagina': '/licores/tipo/7/orujo',
    'titulo_seo': None,
    'intro': 'Orujo gallego: aguardiente blanco, de hierbas, de café, de miel y crema de orujo, también en garrafa de 3 litros para la queimada. '
             'Desde 9 € en la tienda.',
    'cuerpo': '''<h2>Del aguardiente al licor</h2>
<p>El orujo es el aguardiente que se obtiene al destilar los hollejos de la uva después de prensarla. Galicia es su tierra, con indicación geográfica propia. De ahí salen toda una familia de licores:</p>
<ul>
<li><strong>Orujo blanco:</strong> el aguardiente puro, seco y potente. La base de la queimada.</li>
<li><strong>Licor de hierbas:</strong> orujo macerado con hierbas, de color amarillo, más suave y aromático.</li>
<li><strong>Licor de café:</strong> con café, oscuro y algo dulce.</li>
<li><strong>Crema de orujo:</strong> con nata, dulce y cremosa, para servir muy fría.</li>
</ul>
<h2>Los que tenemos</h2>
<p>De Orujo Casero Galicia tenemos blanco, de hierbas, de café, de miel y crema; además, El Afilador blanco y de hierbas, el orujo de hierbas de Panizo y el licor de hierbas Fonte do Frade. Para fiestas y para la queimada, formatos de 3 litros: Camí del Nord blanco y de hierbas, Orujo Casero Galicia blanco y crema de orujo Dos Cruceiros.</p>
<p>Si vas a hacer una queimada, cuéntanos para cuántos y te decimos cuánto orujo llevar. También preparamos lotes de orujos para regalar. Si lo que buscas es el orujo italiano, mira la <a href="/licores/tipo/159/grappa">grappa</a>.</p>''',
    'faqs': [
        ('¿Cuánto orujo hace falta para una queimada?',
         'Como orientación, un litro de orujo blanco da para unas 8-10 personas, con azúcar, piel de limón y granos de café. Por eso tenemos garrafas de 3 litros para las fiestas grandes.'),
        ('¿Qué diferencia hay entre orujo de hierbas y crema de orujo?',
         'El de hierbas es orujo macerado con hierbas: amarillo, aromático y con bastante graduación. La crema lleva nata y azúcar: es dulce, suave y de menos grado. Las dos se sirven frías.'),
        ('¿El orujo es lo mismo que la grappa?',
         'Se elaboran igual, destilando los hollejos de la uva. El orujo es gallego y la grappa, italiana; cada uno tiene su estilo y sus uvas.'),
    ],
},
{
    'ambito': 'tipo', 'id_ref': 105, 'pagina': '/licores/tipo/105/licor-hierbas',
    'titulo_seo': None,
    'intro': 'Licores de hierbas y digestivos: Chartreuse, Aromes de Montserrat, Calisay, Estomacal Bonet, Fernet Branca y Jägermeister. '
             'Recetas de monasterio y clásicos de sobremesa.',
    'cuerpo': '''<h2>Hierbas, raíces y siglos de receta</h2>
<p>Los licores de hierbas nacieron en monasterios y boticas como remedio digestivo, y muchos guardan aún su receta en secreto. Se elaboran macerando o destilando decenas de plantas, raíces y especias en alcohol, y se toman después de comer, solos o con hielo.</p>
<h2>Los que tenemos</h2>
<ul>
<li><strong>De Cataluña:</strong> Aromes de Montserrat, ligado a la montaña y su monasterio; Calisay, el clásico licor de quina, y Estomacal Bonet.</li>
<li><strong>De monasterio:</strong> Chartreuse Amarillo, de los monjes cartujos franceses. Durante buena parte del siglo XX se elaboró en Tarragona.</li>
<li><strong>Amargos de Europa:</strong> Fernet Branca, italiano y muy amargo, y Jägermeister, alemán, que también se toma muy frío como chupito.</li>
<li><strong>De Mallorca y Galicia:</strong> Palo Túnel y el licor de hierbas Mos Pas, en garrafa de 3 litros.</li>
</ul>
<p>Para quien lo tiene todo, una botella de Chartreuse o de Aromes de Montserrat es un regalo con historia. Te ayudamos a elegir y te lo preparamos en la tienda. El licor de hierbas gallego lo encontrarás en <a href="/licores/tipo/7/orujo">orujo</a>.</p>''',
    'faqs': [
        ('¿Para qué sirve un licor de hierbas?',
         'Tradicionalmente, como digestivo después de comer. Hoy se toma también por gusto, solo, con hielo o en cócteles.'),
        ('¿Cómo se sirve el Chartreuse?',
         'Solo y a temperatura ambiente, o muy frío. El amarillo es más suave y dulce que el verde. También se usa en coctelería.'),
        ('¿Qué licores de hierbas catalanes tenéis?',
         'Aromes de Montserrat, Calisay y Estomacal Bonet. Si buscas una ratafía, que también lleva hierbas, la encontrarás en su propia sección.'),
    ],
},
{
    'ambito': 'tipo', 'id_ref': 5, 'pagina': '/licores/tipo/5/ginebra',
    'titulo_seo': None,
    'intro': 'Ginebras London Dry, Gin de Mahón y ginebras catalanas y premium para el gin-tonic de casa o para regalar. '
             'Una veintena en la tienda, de 15 a 34 €.',
    'cuerpo': '''<h2>Estilos de ginebra</h2>
<p>La ginebra es un destilado de cereal aromatizado con <strong>enebro</strong> y otros botánicos: cítricos, cilantro, angélica, especias. Tres estilos para orientarte:</p>
<ul>
<li><strong>London Dry:</strong> seca, con el enebro marcado. Beefeater 24, Tanqueray Ten, Bombay, Martin Miller's, Brockmans, London Nº1 o Mombasa Club.</li>
<li><strong>Gin de Mahón:</strong> la ginebra de Menorca, con indicación geográfica propia, destilada de alcohol de vino. Xoriguer, en 70 cl y en litro.</li>
<li><strong>Nuevas ginebras:</strong> con botánicos propios y perfiles más florales o afrutados: MG, Akori, Cadaqués Quevall, Puerto de Indias Melón o Cabecita Loca. Y Seagram's en litro, para las fiestas.</li>
</ul>
<h2>Cómo elegir</h2>
<p>Si te gusta el gin-tonic clásico, una London Dry. Si lo quieres más aromático, una de las nuevas, con su tónica y su guarnición: cítrico para las secas, frutos rojos o pepino para las florales. Tenemos también tónicas premium para completarlo.</p>
<p>Una ginebra con su tónica y una copa es un regalo que funciona. Te lo preparamos en la tienda o te lo llevamos a casa en el Maresme. ¿No sabes cuál? Dinos qué gin-tonic te gusta y te recomendamos una.</p>''',
    'faqs': [
        ('¿Qué es una ginebra London Dry?',
         'Un estilo, no un origen: ginebra seca, sin azúcar añadido y con todos los botánicos destilados juntos. El enebro manda. Puede elaborarse en cualquier país.'),
        ('¿Qué tiene de especial la Gin de Mahón?',
         'Es la ginebra de Menorca, con indicación geográfica propia. Se destila de alcohol de vino con enebro en alambiques tradicionales; Xoriguer es la más conocida. En Menorca se toma con limonada, la «pomada».'),
        ('¿Qué tónica pongo en el gin-tonic?',
         'Con una London Dry, una tónica clásica y seca. Con una ginebra floral o afrutada, una tónica suave o aromática. Pregúntanos y te recomendamos la pareja.'),
    ],
},
{
    'ambito': 'tipo', 'id_ref': 13, 'pagina': '/licores/tipo/13/ron',
    'titulo_seo': None,
    'intro': 'Ron de Cuba, República Dominicana, Venezuela, Nicaragua, Guatemala y Filipinas: blancos para cócteles y añejos para tomar solos. '
             'De 11 a 59 €.',
    'cuerpo': '''<h2>Blanco, añejo o de solera</h2>
<p>El ron se destila de la caña de azúcar o de su melaza y se envejece en barrica. Cada país tiene su estilo: los de tradición española (Cuba, República Dominicana, Venezuela, Nicaragua, Guatemala) suelen ser ligeros y suaves, y muchos se crían por sistema de solera.</p>
<ul>
<li><strong>Para cócteles (mojito, daiquiri, cuba libre):</strong> Havana Club 3 Años, Brugal Blanco o Havana Añejo Especial.</li>
<li><strong>Añejos para tomar solos (16-30 €):</strong> Matusalem Extra Añejo, Legendario 7 Años, Flor de Caña 7 y Centenario 12, Roble Viejo Maestro Añejo, Pampero Aniversario y Barceló Imperial.</li>
<li><strong>Para regalar (35-59 €):</strong> Kirk and Sweeney Reserva, Don Papa 7 Años de Filipinas, Ron Zacapa Centenario 23 y Plantation XO.</li>
<li><strong>Para cocinar y para el ponche:</strong> Stroh 80, el ron austriaco de alta graduación.</li>
</ul>
<p>Un buen ron añejo se toma como un coñac: en copa, solo o con un cubito, a ser posible con un poco de chocolate negro. Si quieres regalar uno, dinos el presupuesto y te preparamos la botella.</p>''',
    'faqs': [
        ('¿Qué ron es mejor para un mojito?',
         'Un ron blanco o ligeramente añejo, como Havana Club 3 Años o Brugal Blanco: aportan sabor sin tapar la menta y la lima.'),
        ('¿Qué significa «sistema solera» en un ron?',
         'Que el ron se cría en hileras de barricas y se van mezclando rones de distintas edades, como en el jerez. El número de la etiqueta (por ejemplo, 23 en Zacapa) indica el ron más viejo de la mezcla, no la edad de todo el ron.'),
        ('¿Cómo se toma un ron añejo?',
         'Solo, en copa o vaso bajo, a temperatura ambiente o con un cubito grande. Va muy bien con chocolate negro, café o un puro.'),
    ],
},
{
    'ambito': 'tipo', 'id_ref': 16, 'pagina': '/licores/tipo/16/brandy',
    'titulo_seo': None,
    'intro': 'Brandy de Jerez, del Penedès y de Mallorca: de Soberano y Veterano a Carlos I, Gran Duque de Alba y Jaime I. '
             'Para la sobremesa y para regalar en Navidad.',
    'cuerpo': '''<h2>Qué es un brandy</h2>
<p>El brandy es un aguardiente de vino envejecido en barrica. En España tiene dos grandes tradiciones: el <strong>Brandy de Jerez</strong>, criado por criaderas y soleras en botas que antes contuvieron jerez, y el brandy del Penedès, con casas históricas como Torres y Mascaró.</p>
<p>En el Brandy de Jerez, la edad marca la categoría: Solera, Solera Reserva y Solera Gran Reserva, la más vieja.</p>
<h2>Los brandies que tenemos</h2>
<ul>
<li><strong>De diario y para cocinar (10-13 €):</strong> Soberano, Veterano en litro, Terry Centenario, Brandy 103 y Magno.</li>
<li><strong>Gran Reserva y premium (18-30 €):</strong> Lustau Solera Reserva, Independencia Gran Reserva, Carlos I, Gran Duque de Alba y Torres 15 Imperial.</li>
<li><strong>Del Penedès y de Mallorca:</strong> Mascaró Narciso y Suau.</li>
<li><strong>Para un regalo especial:</strong> Ysabel Regina, Conde de Osborne en su botella de Dalí, Luis Felipe y Jaime I de Torres.</li>
</ul>
<p>Un brandy gran reserva es uno de los regalos de Navidad más clásicos. Te ayudamos a elegirlo y te lo preparamos en la tienda. Si buscas un coñac francés, lo tienes en <a href="/licores/tipo/74/conac">coñac</a>.</p>''',
    'faqs': [
        ('¿Qué diferencia hay entre brandy y coñac?',
         'El coñac es un brandy de la región de Cognac, en Francia, con sus propias normas de uva, destilación y crianza. Brandy es el nombre general del aguardiente de vino envejecido.'),
        ('¿Qué significa Solera Gran Reserva?',
         'Es la categoría más alta del Brandy de Jerez: el brandy ha pasado al menos tres años en botas por el sistema de criaderas y soleras, aunque muchos llevan bastante más.'),
        ('¿Cómo se toma un brandy?',
         'Solo, en copa de balón o de tulipa, a temperatura ambiente; calentar la copa con la mano ayuda a abrir los aromas. Los de diario se usan también en cocina y en carajillos.'),
    ],
},
{
    'ambito': 'tipo', 'id_ref': 74, 'pagina': '/licores/tipo/74/conac',
    'titulo_seo': None,
    'intro': 'Coñac francés: Martell, Courvoisier y Rémy Martin en VS, VSOP y Napoleón, el regalo de sobremesa por excelencia. '
             'De 26 a 59 €.',
    'cuerpo': '''<h2>VS, VSOP, XO: qué significa cada sigla</h2>
<p>El coñac (cognac) es el brandy de la región de Cognac, al suroeste de Francia. Se hace sobre todo con uva ugni blanc, se destila dos veces en alambique de cobre y envejece en roble francés. Las siglas de la etiqueta indican la edad mínima del aguardiente más joven de la mezcla:</p>
<ul>
<li><strong>VS (Very Special):</strong> al menos dos años. Fresco y afrutado, bueno también en cócteles.</li>
<li><strong>VSOP (Very Superior Old Pale):</strong> al menos cuatro años. Más redondo, con vainilla y frutos secos.</li>
<li><strong>Napoleón y XO:</strong> seis y diez años o más. Complejos, para tomar despacio.</li>
</ul>
<h2>Los coñacs que tenemos</h2>
<p>De <strong>Martell</strong>, el VS en dos presentaciones; de <strong>Courvoisier</strong>, VS, VSOP y Napoleón; y <strong>Rémy Martin VSOP</strong>, uno de los más reconocidos de su categoría.</p>
<p>Un VSOP es el punto ideal para regalar: elegante y sin un precio desorbitado. Si dudas entre coñac y un buen <a href="/licores/tipo/16/brandy">brandy de Jerez</a>, te ayudamos a decidir en la tienda y te lo preparamos para regalo.</p>''',
    'faqs': [
        ('¿Qué coñac es mejor para regalar?',
         'Un VSOP, como Rémy Martin VSOP o Courvoisier VSOP: tiene la crianza suficiente para tomarse solo y es un regalo elegante. Para algo más especial, un Napoleón.'),
        ('¿El coñac se toma con hielo?',
         'Los VS admiten hielo o se usan en cócteles. Los VSOP y más viejos se disfrutan mejor solos, en copa, a temperatura ambiente.'),
        ('¿Coñac y brandy son lo mismo?',
         'Todo coñac es brandy, pero no al revés: solo se llama coñac el que viene de la región de Cognac y sigue sus normas.'),
    ],
},
{
    'ambito': 'tipo', 'id_ref': 12, 'pagina': '/licores/tipo/12/anis',
    'titulo_seo': None,
    'intro': 'Anís seco y dulce: Anís del Mono, Chinchón y Marie Brizard, el licor de las sobremesas y de la Navidad de siempre. '
             'De 10 a 16 €.',
    'cuerpo': '''<h2>Seco o dulce</h2>
<p>El anís se elabora destilando alcohol con semillas de anís verde, a veces con anís estrellado y otras plantas. Hay dos estilos: el <strong>seco</strong>, potente y sin azúcar, para tomar solo, con agua o en el café; y el <strong>dulce</strong>, más suave, de sobremesa.</p>
<p>En España tiene una gran tradición: el <strong>Anís del Mono</strong>, de Badalona, con su botella labrada que en Navidad se rasca con una cuchara para acompañar los villancicos, y el <strong>anís de Chinchón</strong>, de Madrid, con nombre propio.</p>
<h2>Los que tenemos</h2>
<ul>
<li><strong>Anís del Mono Seco</strong>, el clásico de casa.</li>
<li><strong>Chinchón Seco</strong>, potente y aromático.</li>
<li><strong>Marie Brizard</strong>, el anís francés de Burdeos, más suave.</li>
</ul>
<p>Con agua fría el anís se vuelve blanco y lechoso: es el efecto «louche» de los aceites del anís. Además de la copa, va muy bien en repostería: rosquillas, buñuelos y pestiños. Si buscas otro licor de sobremesa, mira el <a href="/licores/tipo/17/pacharan">pacharán</a>, que se hace con anís.</p>''',
    'faqs': [
        ('¿Qué diferencia hay entre anís seco y dulce?',
         'El seco no lleva azúcar añadido y tiene más graduación; el dulce lleva azúcar y es más suave. El seco se toma solo, con agua o en el café; el dulce, en la sobremesa.'),
        ('¿Por qué el anís se vuelve blanco con agua?',
         'Porque los aceites esenciales del anís se disuelven en alcohol pero no en agua: al añadirla, forman pequeñas gotas que enturbian la bebida.'),
        ('¿Qué anís se usa en repostería?',
         'Un anís dulce o seco suave, según la receta: en rosquillas, buñuelos, pestiños y masas fritas se usa desde siempre.'),
    ],
},
{
    'ambito': 'tipo', 'id_ref': 14, 'pagina': '/licores/tipo/14/licor-naranja-piel-naranja',
    'titulo_seo': None,
    'intro': 'Licor de naranja y triple sec: Cointreau, Magdala, Larios y Porthos, para cócteles, repostería o para tomar con hielo. '
             'De 12 a 19 €.',
    'cuerpo': '''<h2>Triple sec y licores de naranja</h2>
<p>El licor de naranja se elabora con la piel de naranjas dulces y amargas maceradas o destiladas en alcohol. El <strong>triple sec</strong> es su versión incolora y más seca, imprescindible en coctelería.</p>
<ul>
<li><strong>Cointreau:</strong> el triple sec francés de referencia. Solo con hielo o en margarita, cosmopolitan y sidecar.</li>
<li><strong>Magdala:</strong> licor de naranja español, de color ámbar, más redondo, para tomar solo.</li>
<li><strong>Triple Sec Larios y Porthos:</strong> los de batalla para cócteles en casa y para cocinar.</li>
</ul>
<h2>En la cocina</h2>
<p>Un chorrito de licor de naranja transforma un postre: crepes suzette, macedonia, bizcochos, trufas de chocolate o una salsa para el pato. También para flambear.</p>
<p>¿Te animas con los cócteles en casa estas fiestas? Pregúntanos y te ayudamos a montar el kit: <a href="/licores/tipo/59/tequila">tequila</a> y triple sec para la margarita, o vodka y licor de naranja para el cosmopolitan.</p>''',
    'faqs': [
        ('¿Qué es el triple sec?',
         'Un licor de naranja incoloro y seco, hecho con pieles de naranja dulce y amarga. Es la base de cócteles como la margarita, el cosmopolitan o el sidecar.'),
        ('¿Se puede sustituir el Cointreau por otro triple sec?',
         'Sí, en cócteles y en cocina un triple sec como Larios o Porthos funciona bien. El Cointreau tiene más intensidad aromática; se nota más si se toma solo.'),
        ('¿Cómo se toma el licor de naranja solo?',
         'Con mucho hielo, o frío de la nevera, como digestivo. El Magdala y el Cointreau son los que mejor se toman así.'),
    ],
},
{
    'ambito': 'tipo', 'id_ref': 10, 'pagina': '/licores/tipo/10/vodka',
    'titulo_seo': None,
    'intro': 'Vodka ruso, polaco y sueco: Smirnoff, Stolichnaya, Belvedere, Beluga y Absolut, del cóctel de diario al vodka premium para regalar. '
             'De 12 a 38 €.',
    'cuerpo': '''<h2>Un destilado limpio</h2>
<p>El vodka es un destilado neutro, normalmente de cereal (trigo, centeno) o de patata, que se filtra para dejarlo limpio y suave. Las diferencias entre vodkas están en la materia prima, el número de destilaciones y el filtrado: los premium tienen una textura más sedosa y matices de cereal.</p>
<h2>Los vodkas que tenemos</h2>
<ul>
<li><strong>Para cócteles:</strong> Smirnoff y Stolichnaya, los clásicos para el bloody mary, el moscow mule o el vodka con naranja.</li>
<li><strong>Aromatizado:</strong> Absolut Peach, sueco, con melocotón, en litro.</li>
<li><strong>Premium:</strong> Belvedere Pure, polaco de centeno, y Beluga, ruso, para tomar muy frío y solo.</li>
</ul>
<h2>Cómo servirlo</h2>
<p>Un vodka premium se toma helado, en vaso pequeño, guardando la botella en el congelador. Con caviar, salmón ahumado o arenques es la combinación clásica.</p>
<p>Si preparas cócteles para una fiesta, dinos cuántos sois y te ayudamos a calcular las botellas. Para el cosmopolitan, añade un <a href="/licores/tipo/14/licor-naranja-piel-naranja">licor de naranja</a>.</p>''',
    'faqs': [
        ('¿De qué se hace el vodka?',
         'Normalmente de cereal (trigo, centeno, cebada) o de patata, aunque puede hacerse de casi cualquier materia con almidón o azúcar. Se destila varias veces y se filtra.'),
        ('¿El vodka se guarda en el congelador?',
         'Se puede: por su graduación no se congela, y muy frío gana textura. Es la forma clásica de servir un vodka premium solo.'),
        ('¿Qué vodka es mejor para regalar?',
         'Un premium como Belvedere o Beluga: botella elegante y un vodka para tomar solo. Te lo preparamos para regalo.'),
    ],
},
{
    'ambito': 'tipo', 'id_ref': 59, 'pagina': '/licores/tipo/59/tequila',
    'titulo_seo': None,
    'intro': 'Tequila blanco y reposado de agave azul de Jalisco: José Cuervo, 1800, Calle 23 y Sierra, para margaritas o para tomar solo. '
             'De 17 a 44 €.',
    'cuerpo': '''<h2>Agave azul, Jalisco y tres edades</h2>
<p>El tequila se destila del corazón cocido del <strong>agave azul</strong> y solo puede elaborarse en Jalisco y algunas zonas de otros cuatro estados mexicanos. Lo mejor es que sea 100 % agave; los «mixtos» llevan también otros azúcares. Por la crianza:</p>
<ul>
<li><strong>Blanco o plata:</strong> sin crianza o muy corta. Agave puro, cítrico y herbáceo. El de la margarita.</li>
<li><strong>Reposado:</strong> de 2 a 12 meses en barrica. Más suave, con vainilla y madera.</li>
<li><strong>Añejo:</strong> más de un año. Para tomar solo, como un buen destilado.</li>
</ul>
<h2>Los tequilas que tenemos</h2>
<p>José Cuervo Especial en litro y el tequila Sal y Limón Oro, para cócteles y chupitos; y tres reposados para tomar con calma: <strong>Sierra Reposado</strong>, <strong>1800 Reposado</strong> y <strong>Calle 23 Reposado</strong>.</p>
<p>Un reposado se toma en copa pequeña, solo, para apreciar el agave: no hace falta sal ni limón. Para la margarita, tequila, <a href="/licores/tipo/14/licor-naranja-piel-naranja">triple sec</a> y lima. Te ayudamos a montar el kit.</p>''',
    'faqs': [
        ('¿Qué diferencia hay entre tequila blanco y reposado?',
         'El blanco no tiene crianza o muy poca: sabe a agave, es cítrico y herbáceo. El reposado pasa de 2 a 12 meses en barrica y es más suave, con notas de vainilla y madera.'),
        ('¿Qué significa «100 % agave»?',
         'Que todo el azúcar fermentado viene del agave azul. Los tequilas «mixtos» pueden llevar hasta un 49 % de otros azúcares. Los 100 % agave suelen ser más aromáticos.'),
        ('¿El tequila se toma con sal y limón?',
         'Es la costumbre con los blancos y los chupitos. Un reposado bueno se disfruta más solo, a sorbos, como un whisky.'),
    ],
},
{
    'ambito': 'tipo', 'id_ref': 11, 'pagina': '/licores/tipo/11/bourbon',
    'titulo_seo': None,
    'intro': 'Bourbon y whiskey americano: Wild Turkey, Bulleit y Jack Daniel\'s Honey, de maíz y barrica nueva tostada. '
             'Para tomar solo, con hielo o en un old fashioned.',
    'cuerpo': '''<h2>Qué hace que un whiskey sea bourbon</h2>
<p>El bourbon es el whiskey de Estados Unidos por excelencia, con Kentucky como cuna. Para llamarse así tiene que cumplir unas normas: al menos un 51 % de <strong>maíz</strong> en el grano y crianza en <strong>barricas nuevas de roble tostado</strong> por dentro. Por eso es más dulce y redondo que un whisky escocés, con vainilla, caramelo y especias.</p>
<h2>Los que tenemos</h2>
<ul>
<li><strong>Wild Turkey:</strong> un bourbon de Kentucky de carácter, con bastante centeno y especia.</li>
<li><strong>Bulleit:</strong> bourbon con alto contenido de centeno, seco y especiado, muy usado en coctelería.</li>
<li><strong>Jack Daniel's Honey:</strong> licor a base de whiskey de Tennessee con miel, suave y dulce, para tomar con hielo.</li>
</ul>
<h2>Cómo tomarlo</h2>
<p>Solo o con un cubito grande, o en cócteles clásicos: old fashioned, manhattan, whiskey sour. Va muy bien con barbacoa y chocolate.</p>
<p>Si buscas escoceses, irlandeses o japoneses, los tienes en <a href="/licores/tipo/6/whisky">whisky</a>. Te ayudamos a elegir y te preparamos la botella para regalo.</p>''',
    'faqs': [
        ('¿Qué diferencia hay entre bourbon y whisky escocés?',
         'El bourbon se hace sobre todo con maíz y se cría en barrica nueva tostada: es más dulce, con vainilla y caramelo. El escocés suele ser de cebada malteada y se cría en barricas usadas; puede ser ahumado.'),
        ('¿El Jack Daniel\'s es bourbon?',
         'No: es whiskey de Tennessee, que se filtra con carbón de arce antes de criarse. El Jack Daniel\'s Honey, además, es un licor de whiskey con miel.'),
        ('¿Qué bourbon uso para un old fashioned?',
         'Uno con carácter y algo de centeno, como Bulleit o Wild Turkey: aguantan bien el azúcar y el amargo del cóctel.'),
    ],
},
{
    'ambito': 'tipo', 'id_ref': 159, 'pagina': '/licores/tipo/159/grappa',
    'titulo_seo': None,
    'intro': 'Grappa italiana: el aguardiente de orujo de uva del norte de Italia, joven o afinado en barrica. '
             'Sanmarco, Grappa del Contadino y Sibona Riserva en la tienda.',
    'cuerpo': '''<h2>Qué es la grappa</h2>
<p>La grappa es el aguardiente que se obtiene destilando los hollejos de la uva que quedan después de hacer el vino. Es la hermana italiana del <a href="/licores/tipo/7/orujo">orujo gallego</a>, con denominación protegida: solo se llama grappa la que se elabora en Italia con uva italiana. Las regiones con más tradición están en el norte: Piamonte, Véneto, Trentino y Friuli.</p>
<ul>
<li><strong>Joven o blanca:</strong> sin crianza, transparente, intensa y aromática.</li>
<li><strong>Afinada o reserva:</strong> criada en barrica, de color dorado, más suave, con notas de madera y frutos secos.</li>
</ul>
<h2>Las que tenemos</h2>
<p><strong>Grappa Sanmarco</strong> y <strong>Grappa del Contadino</strong>, jóvenes, para empezar; y <strong>Sibona Riserva</strong>, en media botella de 50 cl, afinada en botas de jerez: una grappa para tomar despacio.</p>
<h2>Cómo tomarla</h2>
<p>En copa pequeña de tulipa, las jóvenes algo frescas y las reservas a temperatura ambiente, después de comer. En Italia también se añade al café: el «caffè corretto». Pregúntanos y te ayudamos a elegir.</p>''',
    'faqs': [
        ('¿La grappa es lo mismo que el orujo?',
         'Se elaboran igual, destilando los hollejos de la uva, pero la grappa es italiana, con sus propias uvas y estilos, y tiene denominación protegida.'),
        ('¿Cómo se sirve la grappa?',
         'En copa pequeña de tulipa, después de comer. Las jóvenes, a 9-13 °C; las reservas, a temperatura ambiente.'),
        ('¿Qué es un caffè corretto?',
         'Un café espresso «corregido» con un chorrito de grappa, muy típico en Italia después de comer.'),
    ],
},

# ---------------------------------------------------------------- Orígenes de cerveza
{
    'ambito': 'origen', 'id_ref': 10, 'pagina': '/cervezas/origen/10/cataluna',
    'titulo_seo': None,
    'intro': 'Cervezas artesanas catalanas: IPA, pale ale, torradas y lagers de pequeñas cerveceras de Cataluña. '
             'Catorce en la tienda, de 2,30 a 6 €.',
    'cuerpo': '''<h2>La cerveza artesana en Cataluña</h2>
<p>Cataluña es una de las zonas de España con más cerveceras artesanas: pequeñas fábricas, muchas sin pasteurizar, con estilos muy distintos, de las lagers ligeras a las IPA cargadas de lúpulo. En el Celler vamos cambiando la selección para que siempre haya algo nuevo por probar.</p>
<h2>Las catalanas que tenemos</h2>
<ul>
<li><strong>IPA y pale ale, con lúpulo:</strong> Montseny IPA, Guineu IPA, St Cebrià IPA, Kibus Pale Ale con quinoa y Rafega American Amber Ale.</li>
<li><strong>Rubias y de trigo:</strong> St Pol Blonde, Guineu Blat de Sac, Cor de Blat y La Bella Lola.</li>
<li><strong>Tostadas:</strong> St Iscle Torrada y Font de Ferro St Roch.</li>
<li><strong>Para descubrir:</strong> Misericòrdia Canetenca, Cuca Sant Jordi y Mataró Grape Lager.</li>
</ul>
<h2>Cómo servirlas</h2>
<p>Las artesanas no se sirven heladas: a 6-8 °C las rubias y las IPA, y a 8-12 °C las tostadas y las más intensas, en vaso ancho para que suelten el aroma. Sírvelas despacio, dejando en la botella el poso de levadura si no te gusta turbia.</p>
<p>Una caja con cuatro o seis catalanas distintas es un buen regalo para quien le gusta la cerveza. Te la preparamos en la tienda. Más estilos en <a href="/cervezas">cervezas</a>.</p>''',
    'faqs': [
        ('¿Qué diferencia una cerveza artesana de una industrial?',
         'Normalmente, la escala y el proceso: las artesanas se elaboran en pequeños lotes, sin pasteurizar ni filtrar en exceso, y con más variedad de estilos y lúpulos. Por eso cambian más de una tanda a otra.'),
        ('¿A qué temperatura se sirve una cerveza artesana?',
         'Las rubias e IPA, a 6-8 °C; las tostadas, negras y de alta graduación, a 8-12 °C. Si están demasiado frías, pierden aroma.'),
        ('¿Preparáis lotes de cervezas catalanas?',
         'Sí: elegimos cuatro o seis cervezas de estilos distintos según el gusto de la persona y te las preparamos para regalar.'),
    ],
},
{
    'ambito': 'origen', 'id_ref': 24, 'pagina': '/cervezas/origen/24/espana',
    'titulo_seo': None,
    'intro': 'Cervezas españolas: Estrella Damm, Voll Damm, Inedit, Daura, Estrella Galicia y artesanas como Dougall\'s, por unidades. '
             'Desde 0,60 € la lata.',
    'cuerpo': '''<h2>De la caña de siempre a las especiales</h2>
<p>En el Celler tenemos las cervezas de casa de toda la vida, por unidades, para que te lleves justo las que necesitas, y algunas especiales para cuando apetece otra cosa.</p>
<ul>
<li><strong>Lagers de diario:</strong> Estrella Damm en lata y en botellín de 25 cl, AK Damm y Estrella Galicia en lata, y La Estrella del Camino.</li>
<li><strong>Con más cuerpo:</strong> Voll Damm, la doble malta, en 25 cl y en tercio.</li>
<li><strong>Especiales de Damm:</strong> Inedit, la cerveza de trigo creada con Ferran Adrià para acompañar la comida, y Daura, sin gluten.</li>
<li><strong>Con limón y sin alcohol:</strong> Damm Lemon y Free Damm.</li>
<li><strong>Artesanas:</strong> Dougall's 942 APA, de Cantabria, Cerdos Voladores, y la Irish Stout y la Abadía de Sanfrutos.</li>
</ul>
<h2>Para fiestas y comidas grandes</h2>
<p>Si organizas una comida, un cumpleaños o una fiesta, dinos cuántos sois y te ayudamos a calcular. Te lo llevamos a casa en el Maresme con nuestro transporte, junto con el vino y los refrescos.</p>
<p>Si buscas cervezas de pequeñas cerveceras catalanas, mira las <a href="/cervezas/origen/10/cataluna">cervezas artesanas de Cataluña</a>.</p>''',
    'faqs': [
        ('¿Vendéis cerveza por unidades?',
         'Sí: todas las cervezas se pueden comprar por unidades, en lata, botellín o tercio, para que te lleves justo las que necesitas.'),
        ('¿Qué es la Inedit de Damm?',
         'Una cerveza de trigo y cebada, con cilantro, piel de naranja y regaliz, creada con Ferran Adrià y el equipo de elBulli para acompañar la comida. Se sirve en copa de vino.'),
        ('¿Tenéis cerveza sin gluten?',
         'Sí, Daura, de Damm, apta para celíacos. Pregúntanos si buscas otra.'),
    ],
},
{
    'ambito': 'origen', 'id_ref': 38, 'pagina': '/cervezas/origen/38/belgica',
    'titulo_seo': None,
    'intro': 'Cervezas belgas: de abadía, strong ale y tripel, con Leffe, Duvel, Gulden Draak y Piraat. '
             'Cervezas de mucho carácter y graduación, de 1,80 a 3,20 €.',
    'cuerpo': '''<h2>La gran tradición cervecera</h2>
<p>Bélgica tiene una de las culturas cerveceras más ricas del mundo: cervezas de abadía, trapenses, strong ales, tripels, lambics… Muchas son de alta fermentación y refermentan en la botella, con levaduras que aportan aromas frutales y especiados, y bastante graduación.</p>
<h2>Las belgas que tenemos</h2>
<ul>
<li><strong>De abadía:</strong> Leffe rubia, Leffe negra y Leffe Royale. La rubia, suave y especiada; la negra, con caramelo y tostados.</li>
<li><strong>Strong golden ale:</strong> Duvel, rubia, seca y muy aromática, con una espuma enorme. Su nombre significa «diablo».</li>
<li><strong>Para los que buscan intensidad:</strong> Gulden Draak, oscura y potente, y Piraat, una strong ale ámbar.</li>
</ul>
<h2>Cómo servirlas</h2>
<p>Las belgas se sirven a 8-12 °C, nunca heladas, en copa o cáliz para que se forme la espuma y suelten el aroma. Sírvelas inclinando la copa y deja en la botella el último dedo, donde queda la levadura. Por su graduación, se toman despacio: van muy bien con quesos curados, guisos y chocolate.</p>
<p>Un lote de belgas es un regalo perfecto para quien le gusta la cerveza. Te lo preparamos en la tienda. Más estilos en <a href="/cervezas">cervezas</a>.</p>''',
    'faqs': [
        ('¿Qué es una cerveza de abadía?',
         'Una cerveza de estilo monástico, elaborada hoy por cerveceras con licencia de una abadía, como Leffe. Las trapenses, en cambio, se elaboran dentro de monasterios trapenses.'),
        ('¿A qué temperatura se sirve una cerveza belga?',
         'Entre 8 y 12 °C, según el estilo: las rubias, más frescas; las oscuras y fuertes, más templadas. Demasiado frías pierden aroma.'),
        ('¿Por qué las cervezas belgas tienen tanta graduación?',
         'Muchos estilos belgas usan más malta y azúcar y refermentan en botella. Duvel, Gulden Draak o Piraat superan el 8 %: se toman despacio, como un vino.'),
    ],
},

]
