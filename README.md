# La Bandeja Paisa — Laravel + SQLite

Ampliación del proyecto original Embajada Paisa Sprint 1. Conserva Laravel 12, usuarios, clientes, mesas y panel de administración; añade el portal público y los 22 requisitos funcionales de pedidos y reservas. No usa AWS ni Python.

## Requisitos para ejecutarlo
PHP 8.2 o superior con pdo_sqlite, mbstring, openssl, tokenizer, xml, ctype, fileinfo y dom. Composer si necesitas restaurar dependencias. No requiere npm, Vite ni MySQL. CSS, JavaScript e ilustración están incluidos en public.

## Instalación local en Windows
1. Extrae el ZIP completo.
2. Abre una terminal dentro de la carpeta del proyecto, donde está `artisan`.
3. Ejecuta `php preparar.php --demo`. Crea .env, clave, SQLite, migraciones y datos de ejemplo sin vaciar una base existente.
4. Ejecuta `php artisan serve`.
5. Abre http://127.0.0.1:8000.

Si falta `vendor/autoload.php`, ejecuta primero `composer install`.

Demo local: **admin@embajada.test / Embajada2026!**. El nombre del correo se conserva para compatibilidad con el proyecto original. Puedes registrar clientes desde Ingresar → Crear cuenta. El administrador cambia una cuenta a Personal desde Gestión → Usuarios. Trabajador es el nombre interno del rol Personal.

Para instalar sin cuentas de ejemplo: `php preparar.php`; configura EMB_ADMIN_EMAIL y EMB_ADMIN_PASSWORD en .env y ejecuta `php artisan db:seed`. Retira luego la contraseña inicial de .env.

Si ya trabajabas con la base anterior, conserva tu .env y database/database.sqlite; ejecuta `php preparar.php`. No ejecutes migrate:fresh: borraría tus datos.

## Recorrido
- Menú público: categorías colombianas, detalles, precios e ilustraciones. Agregar, cambiar cantidades o quitar productos en Mi pedido.
- Cuenta: registro de nombre, correo, teléfono y contraseña; acceso y cierre de sesión.
- Pedido: resumen, total, método de pago simulado y número de seguimiento. Mis pedidos contiene activos e historial de entregados; cada cliente solo obtiene sus propios datos.
- Reservas: fecha, hora en intervalos de 15 minutos, comensales y mesa asignada según disponibilidad real.
- Personal: consulta pedidos/reservas y avanza estados Recibido → En preparación → Listo → Entregado.
- Administrador: registro, edición, desactivación y reactivación de platos; gestión de usuarios, contacto, ubicación y configuración de mesas/horarios/duración.
- El panel original sigue disponible en `/inicio`, para clientes y mesas. Las mesas con reservas vigentes no pueden modificarse o eliminarse desde ese panel. Eliminar una mesa con historial la desactiva para conservar las reservas.

## Disponibilidad y configuración
Gestión → Configuración permite definir mesas con capacidad uniforme, horario de apertura/cierre y duración. Esta configuración sustituye la disponibilidad de mesas habilitadas cuando cambia capacidad u horario. No puede cambiarse mientras hay reservas vigentes; contacto y coordenadas sí pueden editarse.

El panel original conserva también capacidades individuales por mesa: las reservas seleccionan una mesa habilitada cuya capacidad real sea suficiente. Por eso, si tienes mesas de capacidades diferentes, revisa los límites de comensales del formulario o configura una capacidad uniforme desde Gestión.

## Alcance acordado
- Pagos exclusivamente simulados: no se cobran tarjetas ni se solicitan datos bancarios.
- No se añaden domicilios, envío ni recogida: los requisitos no especifican esas modalidades.
- El estado se consulta automáticamente cada cinco segundos mientras la sección está visible. No usa WebSocket.
- RF-21 contenía criterios contradictorios. Se implementó historial propio para el cliente y consulta general para personal/administrador.
- Se conservan nombres y precios históricos en cada pedido aunque cambie el menú.
- El carrito se conserva al iniciar sesión; se vacía al cerrar sesión o recargar la página.
- Contacto y ubicación son datos de ejemplo editables. Mapa OpenStreetMap y fuente web requieren internet; hay fuentes locales alternativas.
- El servidor se inicia para uso local. Una publicación pública requiere HTTPS y configuración de servidor Laravel de producción.

## Pruebas y verificación
`php artisan test --filter=RestaurantTest` ejecuta los casos nuevos de registro/acceso, permisos, precios del servidor, historial privado, estados, reservas, productos y usuarios inactivos.
`php artisan test` incluye también las pruebas originales del Sprint 1.

En el entorno de preparación no hubo PHP ejecutable. Se revisaron sintaxis PHP con parser y sintaxis JavaScript con Node. Las pruebas Laravel incluidas NO se ejecutaron aquí y tampoco se completó una inspección visual en navegador real. No se presentan los resultados del proyecto Python anterior como evidencia de esta entrega.

Consulta `docs/REQUERIMIENTOS_22.md` y `docs/VERIFICACION_ENTREGA.md`. Los otros documentos del Sprint 1 se conservan como antecedentes del proyecto original; sus resultados históricos no certifican esta ampliación.
