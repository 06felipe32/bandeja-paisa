# Verificación de la entrega

## Resultados

| Comprobación | Resultado |
|---|---|
| Instalación limpia y repetición de preparar.php --demo | Aprobadas; migraciones reproducibles y demostración sin duplicados. |
| Suite funcional Laravel/PHPUnit | 47 pruebas y 186 aserciones aprobadas. |
| Estilo de PHP con Laravel Pint | Aprobado. |
| Sintaxis de los archivos PHP propios | Aprobada. |
| Compilación de vistas Blade | Aprobada. |
| Consistencia de composer.json y composer.lock | Aprobada; versiones PHP conservadas del ejemplo. |
| Navegador de escritorio, 1440 px | Login, inicio, clientes, mesas y formulario de usuarios revisados. |
| Navegador móvil, 390 px | Inicio, clientes, mesas, formularios y perfil sin desbordamiento de página. Las tablas conservan desplazamiento horizontal interno. |
| Flujo real en navegador | Inicio/cierre de sesión, creación de cliente y creación/edición/eliminación de mesa completados. |
| Petición POST sin token CSRF por HTTP real | Rechazada con 419; no se crea la mesa. |
| Trabajador abre /usuarios por URL | Rechazado con 403. |
| Cliente autenticado | Ve su ficha asociada; no tiene acceso al listado interno. |
| JavaScript de las pantallas probadas | Sin errores de ejecución. |

El archivo RESULTADO_PRUEBAS.txt conserva la salida de la suite funcional. La prueba del token CSRF se realizó por HTTP real, porque Laravel omite su comprobación durante las pruebas funcionales convencionales.

## Entorno y límites

La ejecución se realizó con PHP 8.3.6 y SQLite. Las pruebas automatizadas usan una base SQLite en memoria separada de la demostración. La revisión de interfaz se hizo con Chromium; no se ejecutó una matriz de navegadores, una auditoría de accesibilidad completa, pruebas de carga ni pruebas contra un servidor MySQL.

La correspondencia con las seis historias se basa en el PDF y en el alcance indicado por el usuario. Faltan la validación del Product Owner y la comparación con los criterios completos de ClickUp, que no estuvieron disponibles. La entrega es un incremento local y no un despliegue de producción.
