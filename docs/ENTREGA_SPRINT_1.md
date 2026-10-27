# Entrega del Sprint 1 · Base segura

**Proyecto:** Sistema de Mesas y Reservas, restaurante La Embajada Paisa, Pasto, Nariño.
**Referencia de alcance:** PDF de planificación suministrado y la selección de historias indicada por el usuario.
**Tecnología:** PHP, Laravel 12, Eloquent, Blade y base relacional.
**Estimación de planificación:** 26 puntos agrupados para seis historias; no se inventa un desglose individual.

## Relación con el ejemplo recibido

El ejemplo implementa pacientes mediante un modelo Eloquent, un controlador, rutas, migraciones y vistas Blade. Este trabajo conserva ese patrón y lo adapta a Clientes, Mesas y Usuarios. No contiene los pacientes, la base SQLite, la clave `.env`, los registros ni las sesiones del ejemplo. Se conserva el conjunto de dependencias PHP bloqueado en Composer para facilitar la reproducción.

La operación de registro sigue este recorrido:

1. La ruta aplica `auth`, cuenta activa, verificación de sesión y rol.
2. Un FormRequest autoriza y valida la entrada.
3. El controlador usa únicamente los datos validados.
4. Eloquent escribe en la base de datos.
5. El controlador redirige y muestra el resultado en una vista Blade.

Los recursos React del kit inicial no intervenían en el CRUD de pacientes. Se utiliza Blade para las pantallas de este incremento, de modo que la instalación no requiere Node ni compilación. Las dependencias PHP del kit se mantienen en el archivo de bloqueo; no se ejecuta un frontend React.

## Criterios de aceptación propuestos y realizados

Los criterios siguientes se derivan del alcance resumido del PDF. No reemplazan los criterios originales de ClickUp, cuyo contenido no estuvo disponible, ni una validación del restaurante.

| Historia | Criterios comprobables en el incremento | Evidencia |
|---|---|---|
| US-01 | Administrador y trabajador registran un cliente; se exigen identificación, nombres, apellidos y teléfono; el correo es opcional; no se duplican documento o correo; se informa el resultado. | Formulario, FormRequest, índices únicos y suite funcional. |
| US-02 | El personal consulta fichas, busca por datos del cliente, ve resultados paginados y recibe un mensaje cuando no hay coincidencias. | Listado, ficha y pruebas de búsqueda/paginación. |
| US-04 | El administrador crea, consulta, edita y elimina mesas; número único entre 1 y 999, capacidad entre 1 y 30, ubicación obligatoria y habilitación; el trabajador solo consulta. | Pantallas, reglas y pruebas de CRUD/permisos. |
| US-11 | Solo el administrador crea y edita cuentas; controla rol y activación; no hay contraseñas en texto plano; puede conservar o restablecer la contraseña; puede desactivar cuentas conservando sus registros. | Gestión de usuarios y pruebas. |
| US-12 | El servidor bloquea peticiones directas fuera del rol; el cliente solo ve la ficha vinculada; una ficha no puede pertenecer a dos cuentas. | Middleware, relación única y pruebas negativas. |
| US-14 | Credenciales válidas de cuenta activa permiten ingresar; las inválidas fallan; cinco fallos provocan espera de hasta 60 segundos por combinación correo/IP; el cierre invalida la sesión. | LoginRequest, AuthController y pruebas. |

## Decisiones de implementación que debe validar el Product Owner

- Los trabajadores registran/consultan clientes y consultan mesas. La modificación de mesas y usuarios se reserva al administrador.
- La baja de cuentas se hace por desactivación reversible. No existe eliminación física de usuarios en este incremento.
- No se permite que un administrador cambie su propio rol ni se desactive. También existe una comprobación transaccional para conservar al menos un administrador activo.
- Las fichas usan documento único global y correo opcional único. El documento se normaliza a mayúsculas y el correo a minúsculas antes de validar.
- La capacidad máxima de 30 personas y el rango de números de mesa son límites operativos propuestos; pueden ajustarse en MesaRequest.
- “Habilitada” es una propiedad del inventario, distinta de la disponibilidad por fecha, reserva u ocupación.
- El alta pública y el restablecimiento por correo no forman parte de este Sprint 1. La cuenta la crea y administra un administrador.

## Modelo implementado

**User:** cuenta de acceso, nombre, correo, hash de contraseña, rol y activación. El rol puede ser administrador, trabajador o cliente.

**Cliente:** ficha de identificación y contacto. Una ficha puede existir sin cuenta. `clientes.user_id` es una clave foránea opcional y única hacia `users.id`; establece la relación uno a uno y permite limitar el perfil por cuenta.

**Mesa:** número, capacidad, ubicación, descripción opcional y habilitación. No contiene reservas ni afirma disponibilidad temporal.

Laravel conserva las tablas de infraestructura del ejemplo (`sessions`, `cache`, `jobs` y relacionadas). La configuración local usa archivos para sesiones y caché, y cola síncrona; no necesita procesos adicionales.

## Controles incluidos

- Contraseñas con hash mediante Laravel; los formularios no vuelven a mostrar contraseñas.
- Sesión regenerada al entrar; invalidación y token CSRF nuevo al salir; cookie HttpOnly, SameSite Lax y sesión cifrada de 30 minutos en la configuración local.
- Estado activo comprobado en cada petición protegida. Una cuenta desactivada pierde acceso en su siguiente solicitud.
- `auth.session` compara el hash de la sesión con el de la cuenta para invalidar sesiones anteriores después de un restablecimiento de contraseña.
- Formularios con CSRF y métodos HTTP correspondientes; el cierre de sesión usa POST.
- Validaciones de servidor y persistencia limitada a campos validados.
- Consultas Eloquent con parámetros; vistas con salida escapada; búsquedas de longitud limitada.
- Restricciones únicas y clave foránea en la base; transacción al gestionar cuentas y vinculación de clientes.
- Errores 403, 404, 419 y 500 con mensajes de uso y sin mostrar trazas en la configuración entregada.

Estos controles y pruebas cubren el incremento académico; no representan una auditoría de seguridad ni la validación del entorno de producción.

## Funciones reservadas para los próximos sprints

US-03 (edición/eliminación de clientes), US-05 (disponibilidad por fecha), US-06 a US-09 (reservas), US-10 (estados dinámicos) y US-13 (información pública). Se debe añadir una entidad Reserva y sus relaciones, reglas de solapamiento, capacidad y estados. Antes de integrar reservas, la eliminación de mesas debe restringirse para preservar los registros relacionados.

## Estado de la entrega

El incremento tiene código, migraciones, demostración, documentación y pruebas reproducibles. Quedan pendientes la revisión por otra persona del equipo, la comparación con los criterios originales de ClickUp, la validación del restaurante y las comprobaciones de MySQL/despliegue en el entorno elegido. No se han marcado historias como terminadas en ClickUp ni se ha publicado un sitio.

## Referencias técnicas utilizadas

- Laravel 12, autenticación: https://laravel.com/docs/12.x/authentication
- Laravel 12, autorización: https://laravel.com/docs/12.x/authorization
- Laravel 12, validación: https://laravel.com/docs/12.x/validation
