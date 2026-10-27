# Cómo presentar el primer adelanto

Tiempo orientativo: 8 a 10 minutos. Inicia con `php preparar.php --demo` y `php artisan serve`.

| Paso | Qué mostrar | Qué explica |
|---|---|---|
| 1 | Ingresar como administrador con la cuenta demo. | Autenticación y acceso personal. |
| 2 | Inicio: cantidades reales de clientes, mesas, puestos habilitados y cuentas activas. | Resumen calculado desde la base. |
| 3 | Clientes → Registrar cliente. Usar un documento nuevo, nombres, apellidos y teléfono. | US-01 y validaciones. |
| 4 | Intentar registrar de nuevo el mismo documento. | Rechazo de duplicados sin crear otro registro. |
| 5 | Buscar el documento y abrir su ficha. | US-02, filtros y consulta individual. |
| 6 | Mesas → Crear mesa. Definir un número no usado, capacidad y ubicación. Editar su capacidad y ponerla fuera de servicio. | US-04 y configuración del inventario. |
| 7 | Usuarios y roles → Crear usuario de rol Cliente y asociar la ficha creada. | US-11 y relación entre ficha y cuenta. |
| 8 | Cerrar sesión y entrar como trabajador. Consultar clientes y mesas e intentar abrir `/usuarios` directamente. | US-12: el servidor devuelve 403 aunque se escriba la URL. |
| 9 | Entrar con la cuenta de Cliente creada. Mostrar solo su ficha e intentar abrir `/clientes`. | Separación de información por cuenta. |
| 10 | Cerrar sesión y mostrar en terminal `php artisan test`. | US-14 y evidencia automatizada del incremento. |

Explicación breve del trabajo:

> Este primer incremento prepara la base del sistema. El personal puede registrar y consultar clientes; el administrador organiza las mesas y las cuentas. Cada usuario accede a funciones distintas según su rol y la información queda guardada en una base de datos relacional. Las reservas y la disponibilidad por fecha se desarrollarán en los siguientes sprints.

Para la explicación del código, sigue este orden: `routes/web.php` → middleware de rol → FormRequest → controlador → modelo/migración → vista. Puedes usar ClienteController como recorrido principal, porque conserva la estructura del ejemplo de pacientes.

Las credenciales de demostración están en README.md. No uses datos reales de clientes para la presentación. El seeder no borra lo que creas durante la demostración.
