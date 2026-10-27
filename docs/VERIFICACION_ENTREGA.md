# Verificación de esta ampliación

- Código propio PHP revisado mediante parser PHP 8.3 (sin incluir vendor ni plantillas Blade).
- JavaScript del portal revisado con node --check.
- Integridad del ZIP comprobada.
- Casos Laravel añadidos en tests/Feature/RestaurantTest.php.
- Pendiente: ejecución de php artisan test e inspección visual con Laravel levantado. PHP no está disponible en el entorno de entrega.

La versión usa el ZIP Laravel proporcionado por el usuario. No contiene el servidor Python de la entrega previa. Los informes originales del Sprint 1 son antecedentes, no resultados de las nuevas funciones.

## Corrección de CSRF
El inicio de sesión ahora devuelve el token regenerado y el cliente lo actualiza. Ante un 419 se consulta el token actual y se reintenta una vez. Prueba aislada de JavaScript ejecutada con respuesta 419 simulada: correcta. Las pruebas Laravel siguen pendientes de ejecución local.
