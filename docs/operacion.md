# operación de una orden

## recepción

1. buscar o registrar al cliente dentro del negocio.
2. registrar servicio, fecha prometida, precio acordado y unidad de cobro.
3. contar piezas o identificar bultos; registrar daños visibles e instrucciones.
4. crear la orden y sus unidades en una sola transacción.
5. emitir comprobante y etiquetas, sin incluir el teléfono del cliente en la etiqueta.

el identificador visible permite buscar; no funciona como credencial pública. una reimpresión conserva el código original y queda registrada. si cambia el conteo después de confirmar recepción, se corrige con motivo e historial.

## recorrido por unidad

```mermaid
flowchart LR
    A[recibida] --> B[clasificada]
    B --> C[en lavado]
    C --> D[en secado]
    D --> E[en acabado]
    E --> F[lista]
    F --> G[entregada]
```

este recorrido es el ejemplo de la demo. el backend deberá permitir omitir acabado para un servicio que no lo incluya mediante una ruta configurada; no se permitirá saltar etapas arbitrariamente. las rutas se copian a la orden para que un cambio posterior de catálogo no modifique trabajos abiertos.

clasificar significa confirmar el servicio y las instrucciones con el personal. registrar un estado no acciona una máquina. una incidencia se asocia a la unidad y puede bloquear el avance o la entrega; no se elimina el historial para ocultarla.

## entrega

1. identificar la orden y seleccionar las unidades listas.
2. comprobar códigos y cantidades con el cliente o persona autorizada.
3. mostrar el saldo; aplicar la política del negocio sobre entrega con saldo pendiente.
4. confirmar entrega, registrar responsable y emitir comprobante.

la política de saldo pendiente será una opción explícita, inicialmente desactivada. una entrega parcial incluye solo las unidades confirmadas. la orden se marca entregada únicamente cuando no quedan unidades pendientes; su estado de pago se calcula por separado.

## reglas de consistencia

- una unidad no puede entregarse dos veces ni pertenecer a dos órdenes.
- no se entrega una unidad en proceso, anulada o bloqueada por una incidencia.
- dos empleados intentando entregar la misma unidad: solo una operación confirma.
- repetir una petición por problemas de conexión no duplica orden, pago ni entrega.
- cancelar exige motivo; no borra órdenes, pagos o eventos anteriores.
- devoluciones de dinero y reaperturas son movimientos explícitos con autorización.

## criterios de aceptación del piloto

| caso | resultado esperado |
|---|---|
| dos prendas; una lista y una en secado | orden en proceso, nunca lista completa |
| entregar una de dos prendas | entrega parcial y una unidad pendiente |
| segunda entrega sobre la misma unidad | rechazo sin nuevo comprobante |
| pago completo antes del lavado | pagada, pero prendas recibidas |
| búsqueda desde otro negocio | no revela cliente ni orden |
| reimprimir etiqueta | mismo identificador y evento de reimpresión |
| cambio de precio del catálogo | mantiene el precio acordado de la orden |
