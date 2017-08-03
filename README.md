# modelo_130
Plugin que añade el modelo 130 de la hacienda española a FacturaScripts y permite crear un fichero compatible para su uso en la aplicación
de predeclaración dentro de la web de la agencia tributaria (https://www2.agenciatributaria.gob.es/wlpl/OV15-M130/index.zul).

Se utiliza la contabilidad integrada para el calculo del modelo, por lo que si no utilizas la contabilidad integrada o creas los asientos a mano de los gastos, ingresos y
retenciones no te servirá este plugin.

Actualmente solo se incluyen los datos en el apartado I del modelo, más adelante se podrá configurar el apartado correspondiente para usar el I o II.

Para el calculo se usan las siguientes cuentas:

Ingresos:

    700, 701, 702, 703, 704, 705, 752, 753, 754, 755, 759, 771 y 778

Gastos:

    600, 601, 602, 607, 621, 622, 623, 624, 625, 626, 627, 628, 629, 631, 640, 641, 642, 643, 644, 649, 669, 678, 680 y 681
       
Rappels sobre ventas:
 
    7090, 7091, 7092, 7093 y 7094

Rappels sobre compras:

    6090, 6091 y 6092

Retenciones:
    
    473

En caso de querer incluir una cuenta no especificada arriba, se puede hacer uso de tres cuentas especiales para asignarla.

M130I -> Ingresos

M130G -> Gastos

M130R -> Retenciones

Actualmente se encuentra en fase de prueba por lo que el uso de los datos obtenidos a través de este plugin es responsabilidad exclusiva del usuario, cualquier sugerencia, mejora o consulta se podrá solicitar en el correspondiente apartado en la web de Facturascripts.

https://www.facturascripts.com
