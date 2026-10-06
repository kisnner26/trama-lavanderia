# producto y alcance

## problema a validar

una lavandería necesita relacionar lo que recibe, lo que procesa y lo que devuelve. tickets en papel o mensajes pueden dejar instrucciones dispersas, prendas sin identificar y entregas difíciles de comprobar. es una hipótesis de producto; todavía no se han entrevistado negocios ni validado ventas.

## cliente inicial

lavandería pequeña atendida por su propietario, con recepción y operación en un mismo local. el piloto empieza con una sucursal y un negocio; el diseño de datos reserva la separación entre negocios desde el principio.

## promesa

abrir una orden y saber qué unidades se recibieron, cuáles están listas, qué instrucciones tienen y cuáles ya se entregaron. el sistema registra decisiones del personal; no decide cómo tratar una tela ni opera máquinas.

## alcance del piloto

| función | resultado observable |
|---|---|
| recepción | orden con cliente, servicio, cantidades, fecha prometida y observaciones |
| identificación | cada pieza o bulto recibe un código único y una etiqueta reimprimible |
| operación | estado por unidad, actor, hora e incidencia asociada |
| cobros | anticipos y pagos manuales; saldo calculado sin alterar el estado de las prendas |
| entrega | seleccionar y confirmar únicamente unidades listas y no entregadas |
| consulta | buscar por código, nombre o teléfono dentro del negocio autorizado |
| reportes | órdenes abiertas, listas, vencidas y con entrega parcial |

la unidad puede ser una pieza individual o un bulto declarado. una bolsa de diez prendas sin etiquetas individuales se sigue como un bulto; la interfaz nunca debe presentarla como diez piezas rastreadas.

## fuera del piloto

pasarelas de pago, facturación fiscal, contabilidad, rutas a domicilio, app nativa, lectura de chips, conexión a máquinas y clasificación automática de telas. los comprobantes iniciales son operativos; no se promete facturación fiscal.

no se implementará un inventario general de todo el negocio antes de validar la recepción y entrega. insumos y compras pueden entrar en una fase posterior.

## venta a probar

ofrecer instalación y configuración de catálogo, etiquetas y usuarios, más alojamiento y soporte mensual. el precio se acuerda después de comprobar el flujo del local y sus costos; no hay clientes ni ingresos confirmados.

## validación previa al backend

1. observar la recepción y entrega de una lavandería con autorización del propietario.
2. registrar tipos de servicio, unidades de cobro, excepciones y hardware disponible.
3. probar la demo sin cargar datos personales reales.
4. acordar alcance, presupuesto y aceptación de un piloto pagado.
5. elegir una primera configuración: cobro por pieza, por peso o ambos.

medir tiempo de recepción, órdenes que requieren buscar instrucciones y entregas con diferencias. establecer una línea base y objetivos junto al negocio; no inventar métricas de ahorro.
