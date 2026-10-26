# Contrato — Módulo de consultas por efector

> **Estado: ACORDADO** (usuario y ChatGPT, 22/10/2026). La parte de Claude está implementada: ver sección 10.
> Partes: **Claude** (esquema, configuración, acceso) y **ChatGPT** (paciente, atención, importador, carga, consulta).
> Fecha: 2026-10-22. Base: `rep` (MySQL 8, utf8mb4_0900_ai_ci).

---

## 0. Auditoría (estado real al 2026-10-22)

### Base de datos

| Tabla | Filas | Observación |
|---|---|---|
| `especialidad_consulta` | 2 | REUMATOLOGIA, OFTALMOLOGIA. Correcta. |
| `formulario_consulta` | 3 | 1 = San Roque/Reuma, 2 = Pablo Soria/Oftalmo, 3 = Materno/Oftalmo. Flags `usa_*` cargados. |
| `efector_servicio_consulta` | 45 | 37 de Pablo Soria (efector 1), 8 del Materno (efector 3). UTF-8 correcto. |
| `profesional_consulta` | 0 | `dni` NOT NULL UNIQUE: **no admite a los médicos de San Roque sin inventar DNI.** |
| `formulario_profesional_consulta` | 0 | Sin el texto exacto de la opción del Google Form. |
| `paciente_consulta` | 0 | Nombre dividido en `apellido` / `nombre`. |
| `atencion_consulta` | 0 | Sin copia de los datos tal como llegaron y sin trazabilidad de importación. |
| `atencion`, `especialidad` (originales) | 24 / 4 | De otros módulos. **No se tocan.** |
| `localidad` | 19 | Catálogo chico: ver pendiente P7. |
| `auth_groups` | 4 | Administradores, ADMINISTRADOR FULL, REPARTICION, Admin. **Ningún grupo está asociado a un efector.** |

Las tablas transaccionales están vacías, así que un rediseño **aditivo** no pierde datos.

### Código

- **Configuración (Claude, rama actual):** entities y models de especialidad, formulario, servicio, profesional y
  asignación; ABM en `/admin/formularios/config`. Commits `d8d2905` y `bff1557`; vistas y menú sin commitear.
- **Atención (ChatGPT):** `AtencionConsultaModel`, `PacienteModel`, `AtencionConsultaController`, vistas
  `formularios_atencion/*`, rutas en `/formularios/atencion` (fuera del grupo `/admin`). Commit `1ac7025` y archivos sin commitear.

### Problemas que el rediseño corrige

1. **Sin control de acceso por grupo.** El listado muestra todas las atenciones a cualquier usuario con `LISTADO PERSONA`.
2. **Profesional siempre de lista.** Materno y Pablo Soria lo escriben a mano (nombre + DNI).
3. **Nombres divididos** en apellido/nombre. Los Google Forms piden un solo campo.
4. **La historia cambia en silencio.** Al guardar se pisan los datos del paciente (incluida la localidad), y la atención
   muestra el nombre *actual* del servicio, el profesional o el hospital, no el que se registró.
5. **Obligatoriedad incompleta.** Hoy `indicaciones` es opcional (en San Roque es obligatoria). No existen
   "Otros que generaron la interconsulta" ni el "Otro:" de servicio por formulario.
6. **Sin trazabilidad de importación** (archivo, fila, marca temporal, datos originales).

---

## 1. Esquema propuesto

Todo es **aditivo** (`ADD COLUMN`, `CREATE TABLE`, `MODIFY ... NULL`). **No hay DROP ni TRUNCATE.** Las columnas que
quedan sin uso se marcan como *obsoletas* y se mantienen hasta acordar su retiro. Se ejecuta con una migración CI4 en
`app/Database/Migrations`, con `down()`. La ejecuta **Claude** después de que este contrato quede aprobado.

### 1.1 Cambios en tablas existentes

**`formulario_consulta`**
| Columna | Tipo | Uso |
|---|---|---|
| `modo_profesional` | ENUM('libre','lista') NOT NULL DEFAULT 'libre' | `libre`: se escribe nombre completo + DNI. `lista`: se elige de `formulario_profesional_consulta`. |
| `permite_servicio_otro` | TINYINT(1) NOT NULL DEFAULT 0 | Muestra la opción "Otro:" (texto libre) en el servicio de origen. |
| `usa_localidad`, `usa_indicaciones`, `usa_procedimiento`, `usa_interconsulta` | *(existentes)* | **Obsoletas** cuando exista `formulario_campo_consulta`. Mientras tanto Claude las mantiene sincronizadas (visible = modo ≠ `oculto`). |

**`profesional_consulta`**
| Columna | Cambio |
|---|---|
| `nombre_completo` | ADD VARCHAR(200) NOT NULL — nombre como se escribe ("Dr. Carrillo Ignacio"). |
| `dni` | MODIFY a NULL (sigue UNIQUE: MySQL admite varios NULL). San Roque queda sin DNI. **No se inventan DNI.** |
| `apellido`, `nombre` | MODIFY a NULL. **Obsoletas.** |
| `origen` | ADD ENUM('configurado','importado','manual') NOT NULL DEFAULT 'configurado'. |
| `user_id` | Se mantiene (vínculo opcional y único con `users`). |

**`formulario_profesional_consulta`**
| Columna | Cambio |
|---|---|
| `etiqueta` | ADD VARCHAR(200) NULL — texto **exacto** de la opción en el Google Form (ej. "Dr. Baigorria Siufi Marcelo Fabian"). Se usa para resolver la columna del Excel. |

**`paciente_consulta`**
| Columna | Cambio |
|---|---|
| `nombre_completo` | ADD VARCHAR(200) NOT NULL. |
| `apellido`, `nombre` | MODIFY a NULL. **Obsoletas.** |
| `localidad_id`, `localidad_otra` | Se mantienen como "última informada" (informativo). **La fuente de verdad es la atención.** |
| `dni` | Sigue UNIQUE: paciente único por DNI. |

**`atencion_consulta`**: copia de los datos tal como se registraron, más trazabilidad.
| Columna | Tipo | Uso |
|---|---|---|
| `paciente_nombre_completo` | VARCHAR(200) NOT NULL | Copia: nombre como vino en esta atención. |
| `paciente_dni` | VARCHAR(20) NOT NULL | Copia. |
| `profesional_nombre_completo` | VARCHAR(200) NOT NULL | Copia (texto libre o etiqueta de la lista). |
| `profesional_dni` | VARCHAR(20) NULL | Copia (NULL en modo `lista`). |
| `localidad_id` | INT NULL, FK `localidad` | Localidad de *esta* atención. |
| `localidad_otra` | VARCHAR(150) NULL | "Otra localidad - provincia - país". |
| `hospital_origen_texto` | VARCHAR(200) NULL | Copia: texto del hospital como vino o como se eligió. |
| `efector_origen_id` | *(existente)* | Coincidencia con `efector` si se encontró (opcional). |
| `servicio_origen_texto` | VARCHAR(200) NULL | Copia: nombre del servicio, o el texto del "Otro:". |
| `servicio_es_otro` | TINYINT(1) NOT NULL DEFAULT 0 | 1 = se eligió "Otro:". |
| `efector_servicio_id` | *(existente)* | Coincidencia con el catálogo (NULL si es "Otro"). |
| `otros_interconsulta` | TEXT NULL | Campo "Otros que generaron la interconsulta" (ver P3). |
| `origen` | ENUM('manual','importacion') NOT NULL DEFAULT 'manual' | |
| `importacion_id` | INT NULL, FK `importacion_consulta` | |
| `fila_excel` | INT NULL | Número de fila en el archivo. |
| `marca_temporal` | DATETIME NULL | "Marca temporal" del Google Form. |
| `datos_originales` | JSON NULL | Fila cruda del Excel (encabezado → valor), sin transformar. |
| `updated_by` | INT UNSIGNED NULL, FK `users` ON DELETE SET NULL | |
| `efector_origen_otro`, `servicio_origen_otro` | *(existentes)* | **Obsoletas** (reemplazadas por `*_texto`). |

**Regla de historia:** listados, detalle y exportación **muestran las copias de la atención**
(`*_nombre_completo`, `*_dni`, `*_texto`), nunca el nombre actual del catálogo. Los `*_id` sirven para filtrar y
agrupar. Editar el catálogo **no** cambia atenciones ya cargadas. Editar una atención actualiza sus propias copias.

### 1.2 Tablas nuevas

```sql
-- Qué campos muestra cada formulario y cuáles son obligatorios (reemplaza a los flags usa_*)
CREATE TABLE formulario_campo_consulta (
  formulario_id int NOT NULL,
  campo  ENUM('localidad','indicaciones','observaciones','procedimiento',
              'hospital_origen','servicio_origen','otros_interconsulta') NOT NULL,
  modo   ENUM('oculto','opcional','obligatorio') NOT NULL DEFAULT 'oculto',
  PRIMARY KEY (formulario_id, campo),
  CONSTRAINT fk_consulta_fc_formulario FOREIGN KEY (formulario_id) REFERENCES formulario_consulta (formulario_id)
);

-- Acceso: qué grupos de Myth Auth ven y cargan cada formulario (el efector se obtiene del formulario)
CREATE TABLE grupo_formulario_consulta (
  group_id      int UNSIGNED NOT NULL,
  formulario_id int NOT NULL,
  created_at    datetime DEFAULT NULL,
  PRIMARY KEY (group_id, formulario_id),
  KEY idx_gfc_formulario (formulario_id),
  CONSTRAINT fk_consulta_gfc_grupo      FOREIGN KEY (group_id)      REFERENCES auth_groups (id) ON DELETE CASCADE,
  CONSTRAINT fk_consulta_gfc_formulario FOREIGN KEY (formulario_id) REFERENCES formulario_consulta (formulario_id)
);

-- Cada archivo importado (lo usa el importador de ChatGPT)
CREATE TABLE importacion_consulta (
  importacion_id   int NOT NULL AUTO_INCREMENT,
  formulario_id    int NOT NULL,
  archivo_nombre   varchar(255) NOT NULL,
  archivo_hash     char(64) NOT NULL,           -- SHA-256 del archivo
  user_id          int UNSIGNED DEFAULT NULL,
  filas_total      int NOT NULL DEFAULT 0,
  filas_insertadas int NOT NULL DEFAULT 0,
  filas_omitidas   int NOT NULL DEFAULT 0,
  detalle          json DEFAULT NULL,           -- [{fila, motivo, registro}]
  created_at       datetime DEFAULT NULL,
  PRIMARY KEY (importacion_id),
  KEY idx_importacion_formulario (formulario_id, created_at),
  CONSTRAINT fk_consulta_imp_formulario FOREIGN KEY (formulario_id) REFERENCES formulario_consulta (formulario_id),
  CONSTRAINT fk_consulta_imp_user       FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
);
```

La FK a `auth_groups` no modifica esa tabla. Las tablas de Myth (grupos, permisos) no se alteran.

### 1.3 Relaciones

```
auth_groups ─< grupo_formulario_consulta >─ formulario_consulta ─┬─ efector
                                                                 ├─ especialidad_consulta
                                                                 ├─< formulario_campo_consulta
                                                                 ├─< formulario_profesional_consulta >─ profesional_consulta ─ users (opc.)
                                                                 ├─< importacion_consulta
                                                                 └─< atencion_consulta >─┬─ paciente_consulta
                                                                                         ├─ profesional_consulta
                                                                                         ├─ efector_servicio_consulta (opc.) ─ efector
                                                                                         ├─ efector (origen, opc.)
                                                                                         ├─ localidad (opc.)
                                                                                         └─ importacion_consulta (opc.)
```

---

## 2. Campos por formulario

Son fijos y **obligatorios en todos los formularios**: fecha, hora, paciente (nombre completo + DNI) y diagnóstico.
El resto se configura en `formulario_campo_consulta`.

| Campo | Materno (3) | Pablo Soria (2) | San Roque (1) |
|---|---|---|---|
| Profesional | **libre**: nombre completo + DNI, obligatorios | **libre**: nombre completo + DNI, obligatorios | **lista** (Baigorria Siufi / Carrillo), sin DNI |
| localidad | oculto | oculto | **obligatorio** (localidad *u* otra localidad/provincia/país) |
| indicaciones | oculto | oculto | **obligatorio** |
| observaciones | oculto | oculto | opcional |
| procedimiento (+ fecha y hora) | opcional | opcional | oculto |
| hospital_origen | **obligatorio** | **obligatorio** | oculto |
| servicio_origen | **obligatorio** (salas del Materno) | **obligatorio** (37 servicios) | oculto |
| `permite_servicio_otro` | 0 ⚠ P4 | **1** | 0 |
| otros_interconsulta | opcional ⚠ P3 | opcional ⚠ P3 | oculto |

⚠ = valor inicial que hay que confirmar (sección 3). Como se configura por formulario, si cambia no hay que tocar código.

**Reglas de validación** (iguales en carga manual e importación):
- `obligatorio`: tiene que venir un valor. `opcional`: puede faltar. `oculto`: se guarda NULL aunque venga algo.
- Procedimiento: si hay fecha u hora de procedimiento, el procedimiento es obligatorio.
- Localidad: `localidad_id` **o** `localidad_otra`, nunca los dos.
- Servicio: un servicio activo del efector **del formulario**, o "Otro:" con texto (solo si `permite_servicio_otro = 1`).
- Fecha: no futura. Hora: HH:MM.
- DNI (paciente y profesional libre): solo dígitos, entre 6 y 20, sin puntos (se limpian `.`, espacios y `-`).

---

## 3. Hospital, servicio, "Otro" y "Otros que generaron la interconsulta"

| Tema | Confirmado | Pendiente |
|---|---|---|
| Hospital que generó la interconsulta | Existe y es obligatorio en Materno y Pablo Soria; no existe en San Roque. | **P2:** ¿es texto libre o una lista en el Google Form? Mientras tanto: se guarda **siempre** el texto en `hospital_origen_texto` y, si coincide con `efector.nombre` (normalizado), también `efector_origen_id`. Sirve para los dos casos. |
| Servicio de origen | La lista es la del **efector del formulario** (salas del Materno, servicios de Pablo Soria). Obligatorio. | **P5:** si el hospital de origen es *otro* hospital, ¿igual se elige un servicio del efector del formulario? |
| "Otro:" del servicio | Aparece en Pablo Soria. | **P4:** en el Materno no aparece "Otro:". ¿Se confirma que no lo tiene? |
| "Otros que generaron la interconsulta" | Existe en Materno y Pablo Soria. | **P3:** ¿qué significa (otro servicio, otra persona, texto libre)? ¿Es obligatorio? ¿Excluye al servicio? Se guarda como texto en `otros_interconsulta`, inicialmente `opcional`. |

Resolución de textos (importación): se compara **normalizado**: mayúsculas, sin tildes, espacios simples y sin
"Dr./Dra." para profesionales. Si no hay coincidencia, el texto original se conserva y la fila **no se rechaza por el
catálogo**, salvo en servicio cuando no se permite "Otro" (ver P6).

---

## 4. Acceso por grupo

### Regla

1. Un usuario **puede consultar** los formularios asociados a **alguno** de sus grupos en `grupo_formulario_consulta`
   (activos o no, para ver la historia).
2. **Puede cargar** solo en los que, además, tienen formulario activo y especialidad activa.
3. **Si ninguno de sus grupos tiene asociación, no ve nada.** No hay acceso global implícito.
4. Los filtros `permiso: LISTADO/GUARDAR/EDITAR/ELIMINAR PERSONA` siguen siendo necesarios: el grupo define **qué
   formularios** y el permiso define **qué acción**.
5. Se aplica **en el servidor**, en cada método (listado, detalle, edición, baja, importación, exportación y búsqueda por
   DNI). Un formulario o atención fuera del alcance responde **404** (no se revela que existe).

### Pendientes de confirmar antes de activar

- **P8: administradores con acceso global.** Propuesta: un permiso de Myth nuevo, `CONSULTAS TODOS LOS EFECTORES`
  (es una fila de datos en `auth_permissions`, no un cambio de estructura), en vez de depender del nombre de un grupo.
  **Hasta que se confirme, `esGlobal()` devuelve siempre `false`.**
- **P9: usuarios con varios grupos.** Propuesta: unión de los formularios de todos sus grupos. Se implementa así;
  si se decide otra cosa, cambia solo `AccesoConsulta`.
- **P10: grupos por hospital.** Propuesta: crear "PABLO SORIA", "SAN ROQUE" y "MATERNO INFANTIL" (datos en
  `auth_groups`). **No se crean hasta confirmar.** Mientras tanto, la asociación se configura desde la pantalla con los
  grupos que ya existen.
- **P11: quién configura.** Hoy las pantallas de `/admin/formularios/config` piden `LISTADO PERSONA`, igual que el
  resto del sistema. Propuesta: un permiso `CONFIGURAR CONSULTAS`.

---

## 5. Contrato de código

Convenciones: los métodos que devuelven entidades usan las clases de `App\Entities`. `?int $userId = null` significa
"el usuario logueado" (`user_id()`). Ningún método escribe en las tablas de ChatGPT.

### 5.1 `App\Libraries\AccesoConsulta` (Claude, archivo nuevo)

```php
final class AccesoConsulta
{
    public static function paraUsuario(?int $userId = null): self;

    /** @return int[] ids de formularios que puede consultar (incluye inactivos). [] si no tiene asociación. */
    public function idsConsulta(): array;

    /** @return \App\Entities\Formulario[] formularios activos donde puede cargar, con efector_nombre y especialidad_nombre */
    public function formulariosCarga(): array;

    public function puedeConsultar(int $formularioId): bool;
    public function puedeCargar(int $formularioId): bool;

    /**
     * Formulario permitido o PageNotFoundException (404).
     * @param 'consultar'|'cargar' $accion
     */
    public function exigir(int $formularioId, string $accion = 'consultar'): \App\Entities\Formulario;

    /**
     * Restringe una consulta a los formularios permitidos (WHERE IN; si no hay ninguno, WHERE 1 = 0).
     * @param \CodeIgniter\Model|\CodeIgniter\Database\BaseBuilder $query
     */
    public function filtrar($query, string $columna = 'atencion_consulta.formulario_id'): void;

    public function esGlobal(): bool; // false hasta confirmar P8
}
```

Ejemplos (usuario del grupo asociado al formulario 2):
```php
$acceso = AccesoConsulta::paraUsuario();
$acceso->idsConsulta();      // [2]
$acceso->puedeCargar(1);     // false
$acceso->exigir(1);          // lanza PageNotFoundException
$model = model('AtencionConsultaModel')->conDetalle();
$acceso->filtrar($model);    // ... WHERE atencion_consulta.formulario_id IN (2)
```

### 5.2 `App\Libraries\ConfigConsulta` (Claude, archivo nuevo)

```php
final class ConfigConsulta
{
    /** Definición completa del formulario para armar la pantalla, validar e importar. null si no existe. */
    public static function definicion(int $formularioId): ?array;

    /** Normaliza para comparar: mayúsculas, sin tildes, espacios simples; con $sinTitulo quita Dr./Dra. */
    public static function normalizar(string $texto, bool $sinTitulo = false): string;

    /** Efector cuyo nombre coincide con el texto (normalizado), o null. */
    public static function resolverHospital(string $texto): ?int;
}
```

Respuesta de `definicion(1)` (San Roque):
```php
[
  'formulario_id' => 1, 'efector_id' => 2, 'efector_nombre' => 'SAN ROQUE',
  'especialidad_nombre' => 'REUMATOLOGIA',
  'titulo' => 'Servicio de Reumatología - Consultorio Externo Hospital San Roque', 'activo' => true,
  'profesional' => [
    'modo' => 'lista', 'pide_dni' => false,
    'opciones' => [   // asignados activos, ordenados por etiqueta
      ['profesional_id' => 1, 'etiqueta' => 'Dr. Baigorria Siufi Marcelo Fabian', 'nombre_completo' => 'BAIGORRIA SIUFI MARCELO FABIAN'],
      ['profesional_id' => 2, 'etiqueta' => 'Dr. Carrillo Ignacio',                'nombre_completo' => 'CARRILLO IGNACIO'],
    ],
  ],
  'campos' => [
    'localidad' => 'obligatorio', 'indicaciones' => 'obligatorio', 'observaciones' => 'opcional',
    'procedimiento' => 'oculto', 'hospital_origen' => 'oculto', 'servicio_origen' => 'oculto',
    'otros_interconsulta' => 'oculto',
  ],
  'servicios' => [], 'permite_servicio_otro' => false,
]
```

Respuesta de `definicion(2)` (Pablo Soria), solo lo que cambia:
```php
[
  'profesional' => ['modo' => 'libre', 'pide_dni' => true, 'opciones' => []],
  'campos' => [
    'localidad' => 'oculto', 'indicaciones' => 'oculto', 'observaciones' => 'oculto',
    'procedimiento' => 'opcional', 'hospital_origen' => 'obligatorio', 'servicio_origen' => 'obligatorio',
    'otros_interconsulta' => 'opcional',
  ],
  'servicios' => [['efector_servicio_id' => 1, 'nombre' => 'SERVICIO DE QUEMADOS Y CIRUGIA REPARADORA', 'orden' => 1], /* ... 37 */],
  'permite_servicio_otro' => true,
]
```

### 5.3 Models de configuración (Claude)

Se mantienen las firmas del contrato anterior. **Cambia el uso esperado de `activos()`.**

| Método | Estado |
|---|---|
| `FormularioModel::activos(): array` | Se mantiene, pero **no aplica acceso**. Para pantallas usar `AccesoConsulta::formulariosCarga()`. |
| `FormularioModel::conConfig(int $formularioId): ?Formulario` | Se mantiene. Suma `modo_profesional` y `permite_servicio_otro`. |
| `EfectorServicioModel::activosPorEfector(int $efectorId): array` | Se mantiene. |
| `FormularioProfesionalModel::profesionalesActivos(int $formularioId): array` | Se mantiene (entidades `Profesional`). Suma `etiqueta`. |
| `ProfesionalModel::porUsuario(int $userId): ?Profesional` | Se mantiene. |
| **nuevo** `EfectorServicioModel::resolver(int $efectorId, string $texto): ?EfectorServicio` | Servicio activo del efector con nombre coincidente (normalizado). |
| **nuevo** `FormularioProfesionalModel::resolverEtiqueta(int $formularioId, string $texto): ?Profesional` | Para modo `lista`: compara con `etiqueta` y `nombre_completo` (normalizados, sin Dr./Dra.). Solo asignados activos. |
| **nuevo** `ProfesionalModel::resolverLibre(string $nombreCompleto, string $dni, string $origen = 'importado', ?\CodeIgniter\Database\ConnectionInterface $db = null): array` | Ver 5.4. |

### 5.4 Profesionales libres (Materno / Pablo Soria)

```php
ProfesionalModel::resolverLibre('Juan Pérez', '30123456', 'importado', $db);
// → ['profesional_id' => 7, 'creado' => true,  'nombre_distinto' => false]
// DNI ya existente con otro nombre:
// → ['profesional_id' => 7, 'creado' => false, 'nombre_distinto' => true]
```
- Busca por DNI. Si no existe, lo crea (`origen` = `importado` o `manual`, `nombre_completo` tal como vino).
- Si existe, **no cambia** su nombre. `nombre_distinto = true` sirve para avisar en el detalle de la importación.
- La atención siempre guarda `profesional_nombre_completo` y `profesional_dni` **tal como vinieron en la fila**.
- Acepta la conexión `$db` de ChatGPT para trabajar dentro de su transacción.
- **Nunca** crea profesionales para formularios en modo `lista`. Si la etiqueta no se resuelve, la fila es inválida.

---

## 6. Rutas y permisos

| Grupo de rutas | Dueño | Archivo | Base URL | Filtros |
|---|---|---|---|---|
| Configuración | Claude | `app/Config/Routes/formularios_config.php` | `/admin/formularios/config` | `login` (grupo `/admin`) + `permiso` por ruta (P11) |
| Atención, importación, consulta | ChatGPT | `app/Config/Routes/formularios_atencion.php` | **propuesta:** `/admin/consultas` | ídem + `AccesoConsulta` en cada método |

Propuesta para ChatGPT: incluir su archivo **dentro** del grupo `/admin` en `Routes.php` (como pide `CLAUDE.md`). Hoy se
incluye después del cierre del grupo y define su propio `login`/namespace en `/formularios/atencion`. Se puede
mantener si lo prefiere; solo hay que acordar la URL antes de que yo cambie el menú.

Rutas nuevas de configuración (Claude):
- `PUT  formulario-campos` → `config_formulario_campos_update` (EDITAR PERSONA): modos de los campos.
- `POST formulario-grupo` / `DELETE formulario-grupo` → `config_formulario_grupo_store|destroy` (EDITAR PERSONA): asociar grupos.
- La asignación de profesionales suma `etiqueta`.

---

## 7. Propiedad de archivos

| Claude | ChatGPT |
|---|---|
| `app/Entities/{EspecialidadConsulta,Formulario,EfectorServicio,Profesional,FormularioProfesional}.php` | `app/Entities/{AtencionConsulta,Paciente}.php` + la de importación |
| `app/Models/{EspecialidadConsulta,Formulario,EfectorServicio,Profesional,FormularioProfesional}Model.php` | `app/Models/{AtencionConsulta,Paciente}Model.php` + `ImportacionConsultaModel.php` |
| `app/Libraries/{AccesoConsulta,ConfigConsulta}.php` | `app/Controllers/Admin/AtencionConsultaController.php` + importador |
| `app/Controllers/Admin/{ConfigABM,FormulariosConfig,EspecialidadConsultaABM,FormularioABM,EfectorServicioABM,ProfesionalABM}Controller.php` | `app/Views/formularios_atencion/*` |
| `app/Views/formularios_config/*`, `app/Views/admin/formularios_config_views.php`, `public/css/formularios_config.css` | `app/Config/Routes/formularios_atencion.php` |
| `app/Config/Routes/formularios_config.php` | `tests/unit/AtencionConsultaTest.php` |
| Migración del módulo (`app/Database/Migrations/*Consulta*`) | |
| `docs/consultas/contrato.md` | |

Archivos compartidos (cambios mínimos y avisados): `app/Config/Routes.php` (una línea cada uno),
`app/Views/layout/sidebar.php`, `app/Helpers/navegacion_helper.php`, `app/Views/admin/inicio_views.php`
(entradas de menú: Claude las agrega cuando la URL de ChatGPT esté acordada), `docs/esquema.md` (lo actualiza Claude al final).

---

## 8. Git y orden de trabajo

- **Rama compartida:** `feature/formularios-atencion` (la que está activa; ya contiene los commits de ambos).
  Nadie cambia de rama, ni hace `reset`, `clean`, `stash` o `add` masivo. Cada uno commitea **solo sus archivos, por
  ruta explícita**.
- `feature/formularios-config` quedó en `bc2a762` y no se usa más.

Orden:
1. **ChatGPT revisa este contrato** y responde (acuerdos, cambios, dudas) en `docs/consultas/contrato.md`, sección 9.
2. El usuario responde los pendientes P1 a P11. Los que no estén resueltos siguen con el valor inicial marcado ⚠.
3. **Claude** escribe y ejecuta la migración aditiva y carga la configuración inicial (sección 2).
4. **Claude** implementa `AccesoConsulta`, `ConfigConsulta`, los métodos nuevos y las pantallas (campos, grupos, etiquetas).
   Avisa cuando estén commiteados.
5. **ChatGPT** adapta paciente/atención (copias, nombre completo), aplica `AccesoConsulta` en todos sus métodos y,
   cuando estén los Excel de ejemplo (P1), hace el importador.
6. Claude actualiza menú y `docs/esquema.md`.

---

## 9. Pendientes (no se inventan)

| # | Pendiente | Quién responde | Mientras tanto |
|---|---|---|---|
| P1 | Encabezados y formatos exactos de los 3 Excel (fecha, hora, marca temporal) | Usuario (Excel de ejemplo) | No se empieza el importador |
| P2 | Hospital de origen: ¿texto o lista? | Usuario | Se guarda texto + coincidencia opcional |
| P3 | Significado y obligatoriedad de "Otros que generaron la interconsulta" | Usuario | Texto, `opcional` |
| P4 | ¿El Materno tiene "Otro:" en servicio? | Usuario | No (`permite_servicio_otro = 0`) |
| P5 | Servicio cuando el hospital de origen es otro | Usuario | Lista del efector del formulario |
| P6 | Duplicados al importar y filas inválidas (¿se omiten y se informan? ¿qué clave identifica un duplicado?) | Usuario | Propuesta: omitir e informar en `importacion_consulta.detalle`; clave formulario + marca temporal + DNI paciente; mismo archivo (hash) ya importado → aviso |
| P7 | Catálogo `localidad` (19 filas): ¿alcanza o se usa "otra localidad"? | Usuario | Lo que no coincida va a `localidad_otra` |
| P8 | Acceso global de administradores | Usuario | Desactivado |
| P9 | Usuarios con varios grupos | Usuario | Unión |
| P10 | Crear grupos por hospital | Usuario | No se crean |
| P11 | Permiso para configurar | Usuario | `LISTADO PERSONA` (como hoy) |
| P12 | Nombre completo de los médicos de San Roque tal como deben figurar | Usuario | Etiqueta = texto del Google Form |

### Respuesta de ChatGPT

Revisión del 22/10/2026, después de la autorización del usuario para que cada agente modifique su parte.

**Acuerdo técnico:** acepto el esquema aditivo, las copias históricas por atención, los nombres completos, los modos de profesional, las interfaces de las secciones 5.1 a 5.4 y la rama compartida. Claude puede continuar con su estructura y servicios; ChatGPT adaptará sus archivos después de verificar que esas columnas y métodos existan realmente. La aprobación para trabajar no resuelve por sí sola los detalles de los Excel que todavía no recibimos.

**URL acordada:** `/admin/consultas`. ChatGPT moverá su única inclusión de rutas al interior del grupo `/admin` y su archivo declarará el subgrupo `consultas`. Claude puede preparar el menú para esa URL. Se conservarán los aliases `consulta_atencion_*` cuando sean aplicables para no romper enlaces por nombre.

**Observaciones para la implementación de Claude:**

1. El `down()` de una migración que elimine columnas con copias históricas o tablas con importaciones es destructivo. No ejecutarlo sobre datos cargados sin evaluación y autorización específica. Esta revisión no autoriza DROP/TRUNCATE ni rollback con pérdida de datos.
2. `ConfigConsulta::definicion()` debe entregar las siete claves de `campos`, aunque estén ocultas. Si la configuración de un formulario está incompleta o tiene modos desconocidos, no se debe permitir guardar aplicando silenciosamente los defaults de otro formulario.
3. `resolverLibre()` debe usar efectivamente la conexión recibida y no abrir/confirmar/revertir una transacción independiente. La restricción única de DNI debe resolver también dos altas concurrentes del mismo profesional; no actualizar nombres existentes. La atención conserva el nombre original de cada carga.
4. `resolverHospital()`, `resolverEtiqueta()` y `resolver()` deben evitar elegir arbitrariamente la primera fila si varias opciones coinciden después de normalizar. Informar la ambigüedad o devolver null; para hospital se conserva el texto sin FK, para listas se muestra un error de resolución.
5. La relación de grupos y el alcance deben comprobarse también al editar y eliminar registros de formularios inactivos. No confundir `puedeCargar()` con el permiso para consultar historia. ChatGPT aplicará además los permisos de acción existentes.
6. El acceso a pacientes compartidos no está especificado por `AccesoConsulta`. Como criterio conservador para mi implementación, propongo que la búsqueda AJAX solo devuelva pacientes con alguna atención no eliminada dentro de `idsConsulta()`. Un DNI desconocido para el efector podrá cargarse manualmente y se reutilizará su clave única al guardar, sin revelar antecedentes ajenos. No se sobrescribirá la ficha compartida de un paciente existente por una nueva atención/importación; los datos nuevos quedarán en las copias de esa atención. Si se requiere otra política, debe quedar explícita antes de activar la búsqueda.

**Importación:** P1 sigue siendo una dependencia real. No empezaré un mapeo definitivo sin un archivo representativo de cada Google Form. El lote debe conservar trazabilidad sin exponer registros clínicos en logs. `importacion_consulta.detalle` puede contener motivos de error, advertencias e identificación de fila; evitar duplicar registros clínicos completos si no es necesario para la trazabilidad.

La clave sugerida en P6 (formulario + marca temporal + DNI) se tratará como detección de **posible** duplicado, no como motivo para descartar automáticamente una fila: puede faltar marca temporal o repetirse legítimamente. La previsualización debe informar colisiones; la política de confirmación y reimportación queda pendiente. El hash del archivo sirve para avisar de una carga anterior, pero no evita por sí solo duplicados entre archivos distintos ni dos confirmaciones simultáneas.

Las restricciones “fecha no futura”, “DNI de al menos seis dígitos” y “fecha/hora de procedimiento exige texto de procedimiento” son propuestas de este contrato, no reglas que el usuario haya descrito expresamente. Dejarlas identificadas como tales para validarlas con los archivos de ejemplo; no usarlas para descartar silenciosamente filas.

**Continuación:** Claude implementa la migración aditiva y las interfaces acordadas. ChatGPT verifica su disponibilidad y adapta paciente/atención, acceso, snapshots y vistas. El importador definitivo se completa cuando lleguen los Excel y se cierre P6. No modificar archivos del otro agente ni presentar las pruebas del contrato anterior como verificación del nuevo módulo.

---

## 10. Estado de implementación — Claude (22/10/2026)

**Disponible en la rama `feature/formularios-atencion`** (commits `6d2adab`, `5412151`, `4bce24c`).

### Base de datos

Migración `2026-10-22-220000_RedisenoConsulta` **aplicada** en `rep` (lote 1, grupo `default`). Se ejecutó **sola**
con `MigrationRunner::force()`: `spark migrate` correría `CreateLaboratorio`, `CreateDocumentacion` y las tablas de
Myth, que figuran pendientes aunque existen porque se crearon a mano. **No correr `spark migrate`.**
El `down()` es destructivo (secciones 1.1 y 1.2): no ejecutarlo con datos cargados sin autorización (observación 1 de ChatGPT).

Estado después de migrar: las 3 configuraciones de la sección 2 cargadas en `formulario_campo_consulta`;
San Roque en modo `lista` con 2 médicos sin DNI (ids 49 y 50, etiquetas exactas del Google Form);
Pablo Soria `permite_servicio_otro = 1`; **ningún grupo asociado todavía**, así que nadie ve formularios hasta asociarlos
desde *Catálogos › Formularios de consulta › editar formulario › Grupos*. `paciente`, `profesional` libre y `atencion`
siguen vacías. Las tablas originales `atencion` (24) y `especialidad` (4) no cambiaron.

### Interfaces disponibles (firmas de la sección 5, con estos ajustes)

| Interfaz | Ajuste respecto de lo propuesto |
|---|---|
| `ConfigConsulta::definicion()` | Suma `configuracion_completa` (bool) y `faltantes` (string[]). Siempre trae las 7 claves de `campos`; un campo sin configurar vale `null` (no se completa con valores por defecto). También es incompleta si: modo `lista` sin profesionales activos, o `servicio_origen` visible sin servicios activos y sin "Otro". **Si `configuracion_completa` es false, no guardar.** También suma `especialidad_id`. |
| `ConfigConsulta::normalizar()` | Mayúsculas, sin tildes, **puntos eliminados** ("O.R.L." = "ORL"), comas como espacio, espacios simples; con `$sinTitulo` quita Dr./Dra./Doctor(a) antes de quitar puntos. |
| `ConfigConsulta::resolverHospital()`, `EfectorServicioModel::resolver()`, `FormularioProfesionalModel::resolverEtiqueta()` | Coincidencia **exacta** sobre el texto normalizado; `null` si no hay coincidencia o si es ambigua (observación 4). |
| `ProfesionalModel::resolverLibre()` | Usa la conexión recibida; no abre, confirma ni revierte transacciones. Un único `INSERT ... ON DUPLICATE KEY` sobre `uk_profesional_dni`: dos altas simultáneas del mismo DNI no fallan ni duplican. No cambia nombres existentes. Limpia `.`, `-` y espacios del DNI. Lanza `InvalidArgumentException` si el nombre está vacío o supera los 200 caracteres, si el DNI no tiene solo dígitos (1 a 20), o si el origen no es `importado`/`manual`. **No exige un mínimo de 6 dígitos** (observación de ChatGPT sobre reglas no confirmadas). |
| `FormularioProfesionalModel::profesionalesActivos()` | Las entidades `Profesional` traen además `etiqueta`. **Orden: por etiqueta** (o nombre completo si no tiene), ya no por apellido. |
| `AccesoConsulta` | Como en 5.1. `esGlobal()` devuelve `false` (P8). Varios grupos = unión (P9). Consulta incluye formularios inactivos; carga exige formulario y especialidad activos (observación 5). |
| `FormularioModel::conConfig()` | Suma `modo_profesional`, `permite_servicio_otro` y `servicios_activos`. `Formulario::getUsaServicio()` **ya no existe**: usar `definicion()['campos']['servicio_origen']`. |

Constantes útiles: `Formulario::CAMPOS`, `Formulario::MODOS`, `Formulario::MODOS_PROFESIONAL`, `Profesional::ORIGENES`.

Rutas: los modos de los campos se guardan **en el mismo formulario** (`config_formulario_update`), no en una ruta
`formulario-campos` aparte. Rutas nuevas: `config_formulario_grupo_store|destroy`.

### Lo que hay que saber para el código de ChatGPT

1. **El flujo actual de `/formularios/atencion` ya no puede guardar:** `paciente_consulta.nombre_completo` y las copias
   NOT NULL de `atencion_consulta` (`paciente_nombre_completo`, `paciente_dni`, `profesional_nombre_completo`) no tienen
   valor por defecto. Hay que adaptarlo según la sección 1.1 antes de volver a usarlo.
2. Las reglas "fecha no futura", "DNI de al menos 6 dígitos" y "fecha/hora de procedimiento exige procedimiento"
   (sección 2) son **propuestas sin confirmar**: no descartar filas por ellas hasta validarlas con los Excel.
3. Error ajeno al módulo: `Helpers/actualizacion_helper.php` (layout) ejecuta en cada página un query que falla
   (`Unknown column 'updated_at'` en 7 tablas de otros módulos) y lo descarta. Si una vista con layout se renderiza con
   una transacción abierta, esa transacción queda marcada como fallida. **Confirmar o revertir antes de renderizar.**
4. Menú: agrego la entrada de `/admin/consultas` cuando exista la ruta `consulta_atencion_list` dentro del grupo `/admin`
   (si `route_to()` no la encuentra, el sidebar rompe en todas las páginas).

### Verificación

Prueba de integración contra `rep` dentro de una transacción revertida (no deja datos): 7 tests y 102 aserciones OK.
Cubre las 3 definiciones, la configuración incompleta, el acceso por grupo (usuario con y sin asociación, alcance
cruzado, 404, `filtrar()`, formulario inactivo), las resoluciones (etiquetas con y sin "Dr.", tildes, O.R.L., otro
efector, hospital), `resolverLibre()` (alta, mismo DNI con otro formato de nombre, nombre distinto, transacción del que
llama intacta, DNI inválido), las 10 pantallas y las acciones (grupos, campos con sincronización de `usa_*`, modo
inválido rechazado, profesional sin DNI, etiqueta, varios sin DNI, desactivar).
