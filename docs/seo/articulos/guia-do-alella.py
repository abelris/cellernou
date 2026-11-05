# Artículo WineWorld: Guía de los vinos de la D.O. Alella (planner: cellernou-plan-seo-contenidos-14).
# Búsqueda: «vinos de Alella», «pansa blanca». Informa; vende /vinos/do/33 y se enlaza desde los pueblos de la D.O.
# No repite el texto de la categoría (qué es, en breve, y lista de vinos).
# Fuentes: es.ara.cat (contenido patrocinado de la D.O., 2024), femturisme.cat, Wikipedia «Alella (DO)»,
# patrimonicultural.diba.cat, 11onze.cat (Bouquet d'Alella), vinissimus.com (Dolç Mataró).
# Cifras en las que las fuentes no coinciden, redactadas sin dar un número cerrado:
#   año (Consejo 1953, reglamento 1955-56), viñedo (200-314 ha), bodegas (7-9), municipios (18 o 31).
# Pendiente de confirmar con el Consell Regulador: doalella.cat no se pudo leer de forma automática.
# Sin precios a propósito: los datos locales son de julio. Stock y nombres de las fichas: comprobar en producción antes de cargar.
# Foto: Carlos Pino Andújar, Wikimedia Commons, CC BY-SA 4.0 (la licencia obliga a citarla: va al final del texto).

ARTICULO = {
    'slug': 'guia-vinos-do-alella',
    'id_categoria': 1,  # Guías de Vino
    'titulo': 'Vinos de la D.O. Alella: guía de la denominación del Maresme',
    'extracto': 'Pansa blanca, sauló y viñas entre el mar y la Serralada de Marina: qué hace distintos a los vinos de Alella, sus bodegas y por cuál empezar.',
    'autor': 'Victoria Ris',
    'imagen': 'art-do-alella-hero.jpg',
    'relaciones': [('procedencia', 33)],
    'consejo': 'Si no conoces los vinos de Alella, empieza por un blanco joven de pansa blanca y tómalo con algo de mar. Y si buscas un regalo de aquí, cuéntanos para quién es y te ayudamos a elegir la botella.',
    'faqs': [
        ('¿Qué uva es la pansa blanca?',
         'Es el nombre que recibe en Alella el xarel·lo, la misma variedad que se usa en el Penedès. Es la uva más característica de la D.O. y da blancos frescos, con fruta blanca y un punto salino.'),
        ('¿Qué pueblos forman parte de la D.O. Alella?',
         'En el Maresme, Alella, Argentona, Cabrils, El Masnou, Montgat, Òrrius, Premià de Dalt, Premià de Mar, Teià, Tiana y Vilassar de Dalt. La denominación también tiene viñedo en el Vallès.'),
        ('¿Qué es el sauló?',
         'Es el suelo típico de la zona: una arena blanca de origen granítico, muy porosa, que drena bien el agua y guarda el calor.'),
        ('¿Alella solo hace vino blanco?',
         'No. El blanco es lo más conocido, pero también hay tintos de garnacha y syrah, rosados, espumosos y vinos dulces, algunos de monastrell, la uva que aquí llaman mataró.'),
        ('¿Con qué se come un blanco de Alella?',
         'Con pescado, marisco, arroces y platos de verdura. Los que han pasado por barrica o vienen de viñas viejas aguantan también carnes blancas y quesos no muy curados.'),
    ],
    'contenido': '''<p>La D.O. Alella es una de las denominaciones más pequeñas de España y la más cercana a Barcelona. También es la nuestra: Premià de Mar, donde tenemos la tienda, es uno de sus municipios. Muchas veces la viña está a pocos minutos de la mesa donde se abre la botella, y aun así hay quien vive en el Maresme sin haber probado estos vinos. Te contamos qué los hace distintos, quién los hace y por cuál empezar.</p>

<h2>Dónde está la D.O. Alella</h2>
<p>La denominación ocupa las dos caras de la Serralada de Marina, la pequeña cordillera que separa el Maresme del Vallès. En el lado del mar están <a href="/vino-a-domicilio/alella">Alella</a>, <a href="/vino-a-domicilio/argentona">Argentona</a>, <a href="/vino-a-domicilio/cabrils">Cabrils</a>, <a href="/vino-a-domicilio/el-masnou">El Masnou</a>, <a href="/vino-a-domicilio/montgat">Montgat</a>, <a href="/vino-a-domicilio/orrius">Òrrius</a>, <a href="/vino-a-domicilio/premia-de-dalt">Premià de Dalt</a>, <a href="/vino-a-domicilio/premia-de-mar">Premià de Mar</a>, <a href="/vino-a-domicilio/teia">Teià</a>, <a href="/vino-a-domicilio/tiana">Tiana</a> y <a href="/vino-a-domicilio/vilassar-de-dalt">Vilassar de Dalt</a>. Al otro lado, la viña sigue por pueblos del Vallès como Martorelles, Sant Fost de Campsentelles o Vallromanes.</p>
<p>Esa montaña marca el clima. Junto a la costa es plenamente mediterráneo, y hacia el interior se vuelve algo más continental, con más contraste de temperaturas. Por eso dos vinos de la misma denominación pueden ser bastante diferentes según el lado de la sierra del que vengan.</p>
<p>Hoy quedan poco más de 200 hectáreas de viñedo, con unas pocas bodegas y alrededor de medio centenar de viticultores. Es una cifra muy pequeña, y no siempre fue así: la presión urbanística ha dejado la superficie en una tercera parte de la que tenía a mediados del siglo XX. Basta con mirar la foto de este artículo, con las casas rodeando las cepas, para entender por qué beber vino de Alella es también una forma de mantener viva esta viña.</p>

<h2>Dos mil años de vino en el Maresme</h2>
<p>Aquí se hace vino desde época romana. Los vinos de la antigua Laietania, la comarca romana que incluía esta costa, ya aparecen citados por autores como Plinio y Marcial. Quien quiera verlo con sus propios ojos puede visitar en Teià el Centro Enoturístico y Arqueológico de Vallmora, levantado sobre los restos de una bodega romana.</p>
<p>A finales del siglo XIX, la filoxera arrasó la viña, como en casi toda Europa, y hubo que replantarla sobre pies resistentes llegados de América. En 1906 nació la cooperativa <strong>Alella Vinícola</strong>, con su bodega modernista y su marca Marfil, todo un clásico de la zona. A mediados de los años cincuenta llegó el reconocimiento oficial como denominación de origen, y a partir de 1991 una nueva generación de bodegas, con Alta Alella entre las primeras, apostó por la viña ecológica.</p>

<h2>Sauló y pansa blanca: lo que hace distinto a este vino</h2>
<p>Si hay una palabra que hay que aprender para entender Alella es <strong>sauló</strong>. Es una arena blanca que viene de la descomposición del granito: muy porosa, pobre y capaz de guardar el calor. El agua se escurre rápido, la cepa tiene que esforzarse y la uva madura bien, sin perder finura.</p>
<p>La otra palabra es <strong>pansa blanca</strong>, el nombre que recibe aquí el xarel·lo. Es la misma variedad que se usa en el Penedès para el cava y para blancos tranquilos, pero en el sauló y cerca del mar da un carácter propio: fruta blanca, recuerdos de hierbas y un final salino.</p>
<p>No es la única uva de la zona. En blanco se trabajan también la garnacha blanca y el chardonnay; en tinto, la garnacha, la syrah, el merlot, el cabernet o la monastrell, a la que aquí llaman <strong>mataró</strong>, como la ciudad vecina.</p>

<h2>Las bodegas que conviene conocer</h2>
<ul>
<li><strong>Alta Alella.</strong> Proyecto familiar nacido en 1991 en la finca de Can Genís, entre el mar y el parque de la Serralada de Marina. Cultiva en ecológico y es también referencia en espumosos y en vinos sin sulfitos añadidos.</li>
<li><strong>Bouquet d'Alella.</strong> La fundaron en 2010 los hermanos Cerdà, herederos de la tradición viticultora de su padre. Trabaja en ecológico, con variedades de la zona como la pansa blanca, la garnacha y la monastrell.</li>
<li><strong>Raventós d'Alella.</strong> Marca heredera de la antigua Marqués de Alella, uno de los nombres clásicos de la denominación, con la pansa blanca como protagonista.</li>
<li><strong>Can Roda.</strong> Bodega ecológica en una masía de 1867, con viñas en la vertiente del Vallès, en pueblos como Martorelles y Sant Fost. Una buena forma de comprobar cómo cambia el vino al otro lado de la sierra.</li>
<li><strong>Roura.</strong> Otra bodega de la denominación, con un xarel·lo monovarietal que permite probar la uva sin mezclas.</li>
</ul>

<h2>Qué vinos de Alella probar y en qué orden</h2>
<p>En nuestra <a href="/vinos/do/33/d-o-alella">selección de vinos de la D.O. Alella</a> tienes más opciones y te ayudamos a elegir, pero si quieres un pequeño recorrido, este funciona muy bien:</p>
<ul>
<li><strong>Para empezar:</strong> <a href="/producto/2209/raventos-pansa-blanca">Raventós Pansa Blanca</a> o el <a href="/producto/219/roura-xarel-lo">Roura Xarel·lo</a>. Son dos buenas formas de conocer la uva.</li>
<li><strong>Un paso más:</strong> el <a href="/producto/5517/bouquet-d-a-pansa-blanca-vinyes-velles">Bouquet d'A Pansa Blanca Vinyes Velles</a>, de viñas viejas, para ver hasta dónde llega esta variedad.</li>
<li><strong>Un blanco distinto:</strong> el <a href="/producto/284/parvus-chardonnay">Parvus Chardonnay</a> de Alta Alella, para comprobar cómo se comporta una uva internacional cerca del mar.</li>
<li><strong>En tinto:</strong> el <a href="/producto/4990/bouquet-garnatxa-negre">Bouquet Garnatxa Negre</a> si quieres probar la garnacha de la zona; el <a href="/producto/3947/can-roda-syrah-garnacha">Can Roda Syrah Garnacha</a> para probar la vertiente del Vallès; y el <a href="/producto/387/parvus-syrah">Parvus Syrah</a> si buscas algo con paso por barrica.</li>
</ul>

<h2>Cómo servirlos y con qué comerlos</h2>
<p>Los blancos jóvenes de pansa blanca van bien a unos 8-10 °C, y los que tienen más crianza agradecen un par de grados más. Saca los tintos ligeros de garnacha a unos 14-16 °C: un rato en la nevera en verano les sienta muy bien.</p>
<p>En la mesa, juegan en casa con la cocina de esta costa:</p>
<ul>
<li><strong>Blancos jóvenes:</strong> pescado a la plancha, gambas, mejillones, ensaladas y escalivada.</li>
<li><strong>Blancos con crianza:</strong> arroces de pescado, suquet, pollo de corral y quesos semicurados.</li>
<li><strong>Tintos de garnacha y syrah:</strong> embutidos, butifarra a la brasa, setas, conejo y cordero.</li>
<li><strong>Dulces:</strong> postres de chocolate, frutos secos y quesos azules.</li>
</ul>
<p>Si estás pensando ya en las fiestas, en nuestra guía de <a href="/wineworld/maridajes/que-vino-servir-comidas-navidad">qué vino servir en las comidas de Navidad</a> encontrarás más ideas, plato a plato.</p>

<h2>Un regalo de aquí</h2>
<p>Una botella de Alella es un buen detalle para quien vive fuera del Maresme, o para quien vive aquí y no sabe lo que tiene al lado. Queda muy bien en un <a href="/lotes-y-regalos">lote de regalo</a>, junto a una <a href="/licores/tipo/55/ratafia">ratafía</a> o unas conservas, y también podemos llevártela a casa con nuestro <a href="/vino-a-domicilio">servicio de vino a domicilio</a>.</p>
<p>Y si te apetece conocerlos con calma, pásate por la tienda de Premià de Mar o apúntate a una de nuestras <a href="/catas">catas</a>.</p>

<p><small>Foto: viñas en Alella, de Carlos Pino Andújar (<a href="https://commons.wikimedia.org/wiki/File:Vinyes_d%27Alella.jpg" rel="nofollow noopener" target="_blank">Wikimedia Commons</a>), licencia <a href="https://creativecommons.org/licenses/by-sa/4.0/deed.es" rel="nofollow noopener" target="_blank">CC BY-SA 4.0</a>; recortada.</small></p>
''',
}
