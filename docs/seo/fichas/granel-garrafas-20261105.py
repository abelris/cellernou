import json, re
CONS = ("Una vez abierta, el vino entra en contacto con el aire: guarda la garrafa bien cerrada, en un lugar fresco y sin luz, "
        "y si no la vas a acabar pronto, trasvasa parte a botellas pequeñas llenas hasta arriba.")
G = [
 (161, 139, "La garrafa de 5 litros de Oporto a granel es el formato para quien lo usa a menudo, en la cocina o en la copa de sobremesa. ",
  "Es el mismo vino que vendemos por litros en la tienda: un vino generoso del valle del Duero, en Portugal, encabezado con aguardiente durante la fermentación para que conserve parte del dulzor de la uva. "
  "Tiene un perfil dulce, de fruta negra madura y frutos secos, y funciona igual de bien en una copa con los postres o con quesos curados que en la cocina, para salsas de carne, reducciones o un bizcocho. "
  "Al ser un vino fortificado aguanta abierto más que un vino de mesa. "),
 (181, 136, "La garrafa de 5 litros de Gandesa Daurat es el vino blanco dorado de la Terra Alta, a granel, en el formato más práctico para casa. ",
  "Es el mismo vino que servimos por litros en la tienda: un blanco de garnacha blanca de Gandesa, de color dorado intenso y con el punto oxidativo de los blancos tradicionales de la zona, que le da recuerdos de almendra, miel y frutos secos. "
  "Tiene cuerpo y se toma bien en la mesa con arroces, pescados en salsa o embutidos, y también como vino de cocina. "),
 (183, 135, "La garrafa de 5 litros de Alella semidulce es el blanco semidulce de la D.O. Alella a granel, para el día a día o una comida con muchos invitados. ",
  "Es el mismo vino que vendemos por litros en la tienda: un blanco de pansa blanca, la uva de Alella, con un dulzor ligero equilibrado por la acidez y aromas de fruta de hueso y flores blancas. "
  "Se toma fresco, de aperitivo, con postres suaves o con cocina especiada. "),
 (170, 130, "La garrafa de 2 litros de vino tinto de Falset es el tinto a granel de la tienda en un formato manejable, para el consumo de cada día. ",
  "Es el mismo vino que vendemos por litros: un tinto de Falset, la capital del Priorat histórico, hecho con garnacha y cariñena, con fruta roja y negra madura, un fondo de hierbas secas y taninos rústicos pero agradables. "
  "Es un vino de mesa para comidas de diario: legumbres, carnes a la brasa, embutidos o un arroz de montaña. "
  "Si te gusta y lo bebes a menudo, también lo tenemos en garrafa de 5 litros. "),
 (495, 493, "La garrafa de 5 litros de Jerez a granel es el formato para quien usa el jerez a menudo, de aperitivo o en la cocina. ",
  "Es el mismo vino que vendemos por litros en la tienda: un vino generoso del Marco de Jerez, seco, con recuerdos de almendra tostada y un carácter salino. "
  "Se sirve frío como aperitivo, con aceitunas, almendras, jamón o pescaíto frito, y en la cocina da mucho juego en guisos, riñones al jerez o salsas. "
  "Como vino generoso, aguanta abierto más que un vino de mesa. "),
 (162, 498, "La garrafa de 5 litros de vi ranci a granel es el vino rancio catalán en el formato más práctico para quien cocina con él o lo toma de sobremesa. ",
  "Es el mismo vino que vendemos por litros en la tienda: un vino de crianza oxidativa larga, con aromas de nuez, avellana tostada, caramelo y especias, y una boca untuosa con un final muy largo. "
  "En Cataluña es un clásico de la cocina, en guisos, estofados, salsas de caza o el pollo al vino rancio, y también se toma solo, en copa pequeña, con frutos secos o turrón. "
  "Al ser un vino oxidativo aguanta bien una vez abierto. "),
]
out = []
for gid, litro, intro, cuerpo in G:
    d = intro + cuerpo + CONS
    first = intro.strip()
    out.append({"id": gid, "litro": litro, "descripcion": d})
    print(gid, len(d.split()), 'palabras; 1a frase', len(first))
json.dump(out, open('granel.json', 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
