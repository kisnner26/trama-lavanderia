# plan de construcción

cada hito termina con una función revisable y sus pruebas relevantes. los commits agrupan cambios coherentes; no se separan para incrementar el contador.

## 0. validar el negocio

- observar recepción, operación y entrega con autorización.
- confirmar cobro por pieza/peso, servicios, etiquetas, impresora y política de saldos.
- acordar qué constituye una unidad rastreable y qué datos conservar.

**salida:** flujo acordado y piloto presupuestado. pendiente; no hay un cliente confirmado.

## 1. base y acceso

- proyecto laravel, mysql, configuración local y migraciones.
- negocio, sucursal, usuario y roles.
- aislamiento por negocio y autorización por operación.

**aceptación:** un empleado no puede consultar ni modificar datos ajenos cambiando un identificador. probar acceso cruzado, sesión vencida y roles restringidos.

## 2. recibir una orden

- clientes, servicios y precios configurados por el negocio.
- líneas por pieza o peso, unidades e instrucciones.
- comprobante, identificación y reimpresión.

**aceptación:** una solicitud repetida no duplica la orden; un fallo deja cero registros parciales. una actualización del catálogo mantiene condiciones de órdenes confirmadas.

## 3. operar prendas y bultos

- tablero por etapa, búsqueda por código y lectura con cámara o lector.
- rutas según servicio, historial y registro de incidencias.

**aceptación:** no se avanza una unidad bloqueada o desde un estado obsoleto. las instrucciones siguen visibles durante todo el trabajo.

## 4. cobrar y entregar

- pagos manuales y saldo; reversión autorizada con motivo.
- entrega completa o parcial y comprobante.

**aceptación:** dos solicitudes simultáneas no entregan dos veces la misma unidad. pago y entrega tienen estados independientes; el permiso de entrega con saldo pendiente se aplica en servidor.

## 5. probar en el local

- evaluar pantalla pequeña, impresora, escáner y operación con el personal.
- recopilar problemas sin publicar datos reales en github.
- configurar copias y ensayar recuperación.

**aceptación:** recepción, proceso, cobro y entrega de órdenes de prueba completas; restauración demostrada y aprobación del propietario antes de uso real.

## entregado en este repositorio

documentación del producto, demo interactiva, reglas simples del ejemplo y pruebas de entrega parcial. el sitio y sus capturas sirven para discutir el piloto. la base laravel, mysql, negocios, sucursales, roles y acceso por sesión ya están implementados; las asignaciones se comprueban en cada petición. el alta inicial se hace mediante un comando local, sin contraseña predeterminada. falta administración visual del equipo y recuperación de acceso. también están implementados el registro y búsqueda paginada de clientes, y el tarifario por negocio con precios enteros, cobro por pieza/peso y ruta con o sin acabado. solo propietario y recepción consultan estos datos; solo el propietario configura servicios. los hitos de órdenes, operación, pagos y entrega siguen pendientes.

## preguntas abiertas

¿piezas, bultos o ambos? ¿cuándo se pesa? ¿qué servicios omiten acabado? ¿qué impresora usa el negocio? ¿cómo autoriza un retiro por otra persona? ¿qué hacer con piezas dañadas, extraviadas o no retiradas? ¿qué política aplica a descuentos, devoluciones y saldos? estas decisiones requieren información del negocio, no valores inventados.
