# sistemabase

Tablero de gestión e indicadores de salud: emergencias y bases, servicios, pacientes, hospitalario/internación y salud mental.
Los usuarios cargan datos por módulo (ABM e importación de Excel) y los consultan en paneles con gráficos y exportación a Excel.

## Entorno

- **CodeIgniter 4** (PHP 8.1, Laragon en Windows: `C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64`)
- **MySQL** con driver MySQLi. La BD activa se define en `.env` / `app/Config/Database.php`.
- Servidor: vhost de Laragon o `php spark serve` (`baseURL` = `http://localhost:8080/`)
- Dependencias: `myth/auth`, `phpoffice/phpspreadsheet`, `dompdf/dompdf`, `google/apiclient` (Drive)

El mapa completo de tablas, relaciones, controllers y rutas está en docs/esquema.md. Leelo solo si la tarea lo necesita.

## Estructura

- `app/Config/Routes.php`: todas las rutas van en el grupo `/admin` (filtro `login`, namespace `App\Controllers\Admin`). `autoRoute` está desactivado, así que toda ruta nueva hay que declararla.
- `app/Controllers/Admin/`: un controller por módulo. Los `*VController` son paneles (dashboards).
- `app/Models/`: un modelo por tabla. Los JOIN van en métodos del modelo (`conRelaciones()`, `conEfector()`).
- `app/Entities/`: una entidad por modelo principal.
- `app/Views/<modulo>/`: `<modulo>_list.php`, `<modulo>_form.php` y, si hay importación, `<modulo>_import.php`. Layout en `app/Views/layout/main.php`.
- `app/Filters/Permiso.php`: filtro `permiso: <NOMBRE PERMISO>`.

## Convenciones

### Nombres
- Todo en **español** y **snake_case**: tablas, campos, rutas, nombres de ruta y carpetas de vistas.
- Tabla `movil` → PK `movil_id`, modelo `MovilModel`, entidad `Movil`, controller `MovilController`, vistas `movil/movil_list.php`.
- Nombres de ruta: `<modulo>_list`, `_create`, `_store`, `_show`, `_update`, `_destroy`, `_export`, `_visto`.
- Campos de período: `ejercicio` (año), `mes` (en mayúsculas: `ENERO`…), `semestre`, `estado`.
- Timestamps: `created_at` / `updated_at` con `useTimestamps = true`.

### Rutas CRUD estándar
```
GET    <mod>            index     permiso: LISTADO PERSONA
GET    <mod>-create     create    permiso: GUARDAR PERSONA
POST   <mod>            store     permiso: GUARDAR PERSONA
GET    <mod>/(:any)     show      permiso: LISTADO PERSONA
PUT    <mod>            update    permiso: EDITAR PERSONA
DELETE <mod>            destroy   permiso: ELIMINAR PERSONA
GET    <mod>-export     export    permiso: LISTADO PERSONA
GET    <mod>-visto      marcarVisto (guarda la visita en user_last_visit)
```
Update y delete van por POST con `_method` oculto. El id viaja en el body, no en la URL.

### Autenticación (Myth\Auth)
- Login en `/`. Usar `helper('auth')`, `user()`, `user_id()` y `has_permission()`.
- Toda ruta nueva de `/admin` lleva su filtro `permiso`.

### Entidades
Cada entidad de un módulo CRUD implementa:
- `getEditLink()`: link a `route_to('<mod>_show', $this-><pk>)`
- `getDeleteLink()`: form inline con `_method=DELETE`, el id oculto y `csrf_field()`
- `setCampoOculto()` / `getCampoOculto()`: inputs ocultos con la PK y `_method=PUT` para el form de edición

### Frontend
- **Bulma 0.9.3** (no Bootstrap), Font Awesome y jQuery / jQuery UI, cargados en el layout.
- **Chart.js** para gráficos en paneles y listados.
- **Mobile-first**: las tablas van en contenedores con scroll horizontal y las columnas se adaptan con los modificadores responsive de Bulma.
- Mensajes flash: `session()->setFlashdata('msg', ['type' => ..., 'body' => ...])`, que muestra `app/Views/_messages.php`.
- Todos los forms POST llevan `csrf_field()` (CSRF está activo como filtro global).

### Excel
- Exportar e importar con PhpSpreadsheet. La importación tiene tres pasos: `import` (formulario), `template` (descarga la plantilla) y `processImport`.

## Reglas

- **Entregar archivos completos**: nunca truncar ni usar `// ... resto igual`. Si se modifica un archivo, el resultado tiene que quedar completo y funcional.
- **Los cambios de BD se hacen con migraciones** en `app/Database/Migrations/` (`php spark make:migration`). No alterar tablas a mano ni solo con SQL suelto. Incluir `down()`.
- **No tocar `.env`** (credenciales, BD, entorno) sin avisar antes y explicar el cambio.
- No modificar `vendor/`.
- Si un módulo nuevo necesita rutas, agregarlas en `Routes.php` siguiendo el bloque estándar y con permisos.
- Mantener el idioma: textos de UI, comentarios y mensajes en español.

## Comandos útiles

```bash
php spark serve                          # servidor de desarrollo en :8080
php spark routes                         # listar rutas y filtros
php spark make:migration NombreMigracion # nueva migración
php spark migrate                        # aplicar migraciones
php spark migrate:rollback               # revertir el último batch
php spark migrate:status                 # estado de migraciones
php spark make:model NombreModel         # generadores (también make:controller, make:entity)
php spark db:table <tabla>               # ver estructura y datos de una tabla
php spark cache:clear                    # limpiar caché
composer install                         # dependencias
vendor/bin/phpunit                       # tests
```
