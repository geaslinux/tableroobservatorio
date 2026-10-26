# Contrato del tablero estadístico de atenciones de consulta

Implementado el 23/10/2026 en `sistemabase`, tras la autorización del usuario para completar también consultas, cálculos y permisos. Complementa `contrato.md`, que describe el módulo de carga en el otro sistema. No modifica su esquema ni requiere trasladar el módulo de carga.

## Acceso y universo

- Login y permiso Myth `LISTADO PERSONA` en cada ruta; el controlador vuelve a comprobarlos y devuelve 403 si faltan.
- Alcance: unión de `grupo_formulario_consulta` para todos los grupos del usuario en `auth_groups_users`. Ninguna asociación equivale a ningún dato. No hay privilegio global por nombre de grupo.
- Incluye formularios inactivos para consultar su historia. Excluye `atencion_consulta.deleted_at IS NOT NULL`.
- Efector y especialidad de atención se obtienen de `formulario_consulta`. Los campos de origen no se usan como servicio de atención.
- Formulario, efector o especialidad fuera del alcance: 404. Atención inexistente, fuera del alcance o fuera de los filtros: 404.
- Los registros clínicos sin identificables y su exportación CSV usan el mismo permiso de consulta y alcance por formulario, siguiendo la sección 4 del contrato original. No se habilita acceso a nombres o DNI de pacientes. No se crean permisos, grupos, usuarios o asociaciones.
- Respuestas del módulo con datos: `Cache-Control: private, no-store`.

## Rutas GET

| Ruta | Nombre | Función |
| --- | --- | --- |
| `/admin/consulta_tablero` | `consulta_tablero` | Filtros, agregados, gráficos, tablas y página de atenciones |
| `/admin/consulta_tablero/atencion/{id}` | `consulta_tablero_detalle` | Detalle histórico de una atención del mismo universo |
| `/admin/consulta_tablero/exportar` | `consulta_tablero_exportar` | CSV completo de registros filtrados, sin identificables de pacientes |

No hay endpoints JSON públicos ni carga de toda la base en el navegador. La interfaz se entrega renderizada en el servidor. Los filtros viajan por URL; no hay peticiones asíncronas que puedan reemplazar la selección con respuestas antiguas.

## Parámetros

| Parámetro | Contenido |
| --- | --- |
| `efector` | ID de efector de atención |
| `formulario` | ID de formulario de atención |
| `especialidad` | ID de especialidad de consulta |
| `medico` | ID de profesional de consulta |
| `hospital` | Texto histórico exacto del hospital que generó interconsulta |
| `servicio` | Texto histórico exacto del servicio que generó interconsulta |
| `desde`, `hasta` | Fechas ISO YYYY-MM-DD inclusivas sobre `a.fecha`; vacías = sin límite |
| `con_procedimiento` | Vacío o `1`; exige procedimiento no vacío tras TRIM |
| `pagina` | Entero desde 1, solo para listado, 25 registros por página |

Las páginas superiores a la última se ajustan a la última. Parámetros de tipo incorrecto, fechas inexistentes, rangos invertidos y formulario incompatible con efector/especialidad devuelven 422. El cambio de efector limpia formulario, especialidad, profesional y orígenes y recarga catálogos; cambiar formulario o especialidad limpia profesional y orígenes. Los filtros temporales y de procedimiento se conservan. Restablecer elimina todos los filtros.

## Interfaz PHP de lectura

`App\Libraries\EstadisticaConsulta(int $usuario, array $entrada)` utiliza la conexión `default` del sistema y normaliza/valida los filtros. Todos los métodos parten de un constructor de consulta compartido con alcance y exclusión de bajas. Los nombres de columnas SQL provienen de listas fijas y los valores se envían mediante Query Builder.

- `filtros`: valores normalizados para los enlaces y formularios.
- `catalogos()`: `formularios` autorizados con IDs, título, efector y especialidad; `medico`, `hospital`, `servicio` con ID/texto y nombre histórico, restringidos a efector/formulario seleccionados.
- `resumen()`: `kpi`, `serie`, `medicos`.
- `distribuciones()`: mapa de título a filas `nombre`, `cantidad`, con todas las categorías agregadas.
- `detalle(int $pagina = 1, ?int $id = null)`: hasta 25 registros, o un registro por ID, con columnas explícitas sin identificables de pacientes.

### Cálculos

- `kpi.atenciones`: COUNT de registros vigentes del universo filtrado.
- `kpi.pacientes`: COUNT DISTINCT de `paciente_id`, calculado directamente sobre ese universo.
- `kpi.medicos`: COUNT DISTINCT de `profesional_id`.
- `kpi.procedimientos`: número de atenciones con procedimiento no vacío tras TRIM. No equivale a cantidad de procedimientos realizados.
- `kpi.desde/hasta`: mínimo/máximo de fecha realmente encontrada; separado del rango solicitado.
- `serie`: cantidad por mes, con meses sin actividad en cero entre el primer y último mes con registros. Sin registros: serie vacía.
- `medicos`: ID, denominación histórica representativa (MAX de los nombres conservados para ese ID), atenciones, pacientes distintos y atenciones con procedimiento. Los nombres exactos de cada registro se conservan en el detalle.
- Distribuciones: efector, formulario, especialidad, hospital/servicio de origen, diagnóstico, procedimiento, localidad, día de semana y hora. Textos registrados completos, sin dividirlos ni convertirlos en categorías clínicas normalizadas.
- Cada distribución asigna una categoría a cada atención; sus cantidades suman el total filtrado. Vacío: «Sin informar»; si el campo está configurado oculto en el formulario: «No aplica». Localidad usa `localidad_otra` y luego el nombre del catálogo vinculado: no existe una columna histórica del nombre de localidad en este esquema.
- Porcentajes: cantidad de la categoría / total de atenciones filtradas; denominador visible. Si el total es cero, no se calcula porcentaje.
- No se suman pacientes únicos por profesional para obtener el total general. No se inventan comparación temporal, tendencias, sexo, edad o resultados clínicos.

## Presentación y detalle

Chart.js 4.4.1 y paleta `dashboard-colors.js`, conforme a los paneles existentes. Evolución mensual y barras horizontales. Médico y distribuciones muestran hasta 10 barras, con cantidad de categorías y tabla completa. Nombres largos abreviados solo en el eje; tooltip y tablas conservan texto completo.

Tabla de atenciones ordenada por fecha/hora/ID descendentes, paginada en el servidor. Detalle con profesional, diagnóstico, procedimiento, hospital/servicio de origen, indicaciones y observaciones históricos. Formulario usa el título del catálogo porque no hay copia histórica de ese título en la atención. No incluye nombre/DNI de paciente, DNI profesional ni datos originales de importación.

CSV UTF-8 con BOM y separador punto y coma: ID, fecha, hora, formulario, profesional, diagnóstico, procedimiento, hospital y servicio de origen. Conserva todos los filtros, omite `pagina`, obtiene registros en lotes de 25 y protege valores que puedan interpretarse como fórmulas de planilla.

## Límites

La configuración actual de campos permite distinguir «No aplica» en agregados; no existe una copia histórica de esa configuración. El detalle vacío se presenta como «Sin informar» porque no se infiere retrospectivamente si ese campo estuvo oculto.

La comparación entre períodos equivalentes no se implementa: no se había acordado un cálculo. No hay edición clínica ni importación en este tablero. Las consultas de varias peticiones pueden reflejar cambios concurrentes en la base compartida; los filtros son idénticos, pero no se conserva un snapshot entre navegaciones.
