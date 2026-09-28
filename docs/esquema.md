# Esquema del proyecto — sistemabase

Tablero de gestión e indicadores de salud (emergencias, hospitales, pacientes, salud mental).
CodeIgniter 4 + MySQL (BD `rep`), autenticación y permisos con **Myth\Auth**, Excel con **PhpSpreadsheet**.

---

## 1. Arquitectura general

| Capa | Ubicación | Notas |
|---|---|---|
| Rutas | `app/Config/Routes.php` | Todo bajo `/admin`, filtro `login`. `autoRoute = false`. |
| Controllers | `app/Controllers/Admin/` | Uno por módulo. Los `*VController` son paneles (dashboards). |
| Modelos | `app/Models/` | Uno por tabla; métodos `conRelaciones()` / `conEfector()` / `withNombres()` hacen los JOIN. |
| Entidades | `app/Entities/` | Una por modelo principal. |
| Vistas | `app/Views/<modulo>/` | `<modulo>_list`, `<modulo>_form`, a veces `<modulo>_import`. Paneles en `app/Views/admin/`. |
| Filtro | `app/Filters/Permiso.php` | Alias `permiso`, valida con `has_permission()`. |
| Servicios | `app/Services/` | `GoogleDriveService` (sincroniza Excel con Drive). `DepuracionQueueService` + `ChunkReadFilter` son restos de otro proyecto (usan modelos que no existen acá). |
| Helper | `app/Helpers/estado_helper.php` | Clases CSS de Bootstrap según estado o prioridad. |

### Permisos (Myth\Auth)
Todas las rutas CRUD usan 4 permisos genéricos, iguales para todos los módulos:
- `LISTADO PERSONA` → index, show, export, visto
- `GUARDAR PERSONA` → create, store, import, template
- `EDITAR PERSONA` → update (PUT)
- `ELIMINAR PERSONA` → destroy (DELETE)

### Patrón estándar de un módulo
| Método | Ruta | Qué hace |
|---|---|---|
| `index` | GET `/admin/<mod>` | Listado con filtros y conteo de registros "nuevos" desde la última visita |
| `create` / `store` | GET `<mod>-create` / POST `<mod>` | Alta |
| `show` / `update` | GET `<mod>/(:any)` / PUT `<mod>` | Ver y editar |
| `destroy` | DELETE `<mod>` | Baja (borrado físico; ningún modelo usa soft delete) |
| `export` | GET `<mod>-export` | Descarga a Excel |
| `marcarVisto` | GET `<mod>-visto` | Guarda `ultima_vista` en `user_last_visit` |
| `import` / `processImport` / `template` | `<mod>-import`, `<mod>-template` | Carga masiva desde Excel (solo algunos módulos) |

---

## 2. Tablas de la aplicación

Convenciones: PK `<tabla>_id` (salvo que se indique otra). `(ts)` = tiene `created_at` / `updated_at`.
Los campos de período se repiten en muchas tablas: `ejercicio` (año), `mes` (nombre en mayúsculas: `ENERO`…), `semestre`, y `estado`.

### Catálogos
| Tabla | PK | Campos |
|---|---|---|
| `efector` | efector_id | nombre, nivel_complejidad, departamento, ubicacion, region |
| `departamento` | departamento_id | nombre |
| `localidad` | localidad_id | nombre |
| `obra_social` | obra_social_id | nombre |
| `diagnostico` | diagnostico_id | nombre |
| `especialidad` | especialidad_id | nombre |
| `servicio` | servicio_id | nombre, orden |
| `categoria` | categoria_id | nombre |
| `sub_categoria` | sub_categoria_id | nombre |
| `tipo_operativo` | **tipo_op_id** | nombre |
| `operativo` | operativo_id | nombre, tipo_id |
| `pais` | pais_id | pais_nombre, iso_code (heredada del esquema base) |

### Emergencias / Bases (panel "Base")
| Tabla | Campos principales |
|---|---|
| `base` (ts) | tipo, nro, nombre, coordenadas, ubicacion, region, provincia, estado |
| `movil` (ts) | ejercicio, tipo (ej. `MOVILES OPERATIVOS`), cantidad, total, estado |
| `asistencia` (ts) | ejercicio, mes, operativa, nro, nombre, emergencias_con_medico / sin_medico, urgencias, derivacion_publica / privada, internacion_domiciliaria, total, estado |
| `atencion` (ts) | ejercicio, mes, atenciones_base, asistidos_coberturas, cantidad_coberturas, total, estado |
| `identificacion` (ts) | ejercicio, departamento, adulto, pediatrico, total, estado |

### Servicios
| Tabla | Campos principales |
|---|---|
| `cantidad_operativo` (ts), PK **cantidad_id** | ejercicio, operativo_id, via_publica, via_hospitalaria, total, estado |
| `transfusion` (ts) | ejercicio, efector_id, enero … diciembre (una columna por mes), total, estado |

### Pacientes
| Tabla | Campos principales |
|---|---|
| `consulta_reclamo` (ts), PK **consulta_id** | ejercicio, mes, tipo_llamado, categoria_id, sub_categoria_id, atencion, estado |
| `chat_bot` (ts) | ejercicio, mes, fecha, efector_id, turnos_otorgados, observacion, estado |
| `turno_hospitalario` (ts), PK **turno_id** | ejercicio, mes, efector_id, turnos_atendidos, ausentes, cancelados, sin_codificar, total_otorgados, estado |
| `call_center` (ts) | ejercicio, mes, fecha, atendidos, abandonadas, total, estado |

### Hospitalario / Internación
| Tabla | Campos principales |
|---|---|
| `capacidad_camas` | efector_id, tipo, camas UTI (adulto, coronario, pediátrico, neonatal), UTIN, cuidados básicos, totales y públicos, camas_disponibles(_publicas, _salud_mental) |
| `gestion_cama` (ts) | efector_id, tipo_establecimiento, tipo_gestion, region, cuidados_basicos_adultos / pediatricos, observacion, estado |
| `gestion_cama_hospitales` (ts) | efector_id, departamento_id, zona, cuidados, nivel_riesgo, proceso, tipo_cama, tiempo_estancia, observacion, estado |
| `rendimiento_hospitalario`, PK **rendimiento_id** | efector_id, ejercicio, semestre, altas, defuncion, egresos, dias_estada, paciente_dia, cama_disponible y los indicadores calculados (promedios, % ocupacional, tasa_mortalidad, giro_cama) |
| `rendimiento_hospitalario_uti` | Igual que la anterior, para UTI |
| `rh_materno` (ts) | Igual que rendimiento + servicio, sector, ingresos, pases_de / pases_a, giro_sustitucion, egresos_por_dia |
| `guardia` | efector_id, servicio_id, anio, semestre, mes, cantidad |
| `lista_espera` | efector_id, ejercicio, especialidad_id, cantidad_pacientes, comp_quirurgica_alta / mediana / baja |
| `produccion_quirofano` | efector_id, ejercicio, produccion |
| `produccion_quirofano_hosp`, PK **produccion_id** | efector_id, ejercicio, quirófanos (disponibles, urgencias, programadas), cirugías por complejidad, totales, porcentaje_provincia |
| `carta_servicio` | hospital, nivel_complejidad, region, profesion, especialidad, profesional, dia / horario de atención, turno, cant_turnos (texto, sin FK) |
| `rrhh_carta_servicio`, PK **rrhh_id** | hospital, region, dni, nombre_apellido, profesion, especialidad, revista, consultorio, guardia_cargo, telemedicina, prosane, carnet_sanitario |

### Salud mental
| Tabla | Campos principales |
|---|---|
| `electrodependiente` (ts) | paciente, dni, fecha_nacimiento, edad, tipo, contacto, domicilio, coordenadas, localidad_id, efector_id, obra_social_id, cud, diagnostico_id, dx_complementario, factor_riesgo, seguimiento, estado |
| `electrodependiente_equipamiento` (ts), PK equipamiento_id | electrodependiente_id, equipamiento, marca, serie, modelo, fecha_entrega, tiempo_uso, medico_tratante, titular / nro_servicio, fechas, estado |
| `electrodependiente_historial`, PK historial_id | electrodependiente_id, campo_modificado, valor_anterior, valor_nuevo, modificado_por, created_at |
| `equipamiento_historial`, PK historial_id | equipamiento_id, electrodependiente_id, accion, datos_anteriores, datos_nuevos, modificado_por |
| `salud_mental_camas` | efector_id, modalidad, tipo, cb_adultos, cb_pediatricos, total_basicas |

### Sistema
| Tabla | Campos |
|---|---|
| `user_last_visit`, PK user_id | user_id, modulo, ultima_vista. Guarda la última visita de cada usuario a cada módulo (badge de "nuevos"). |
| `users`, `auth_*` | Myth\Auth: usuarios, grupos, permisos, logins, tokens |

> Los dumps de `BD/cr (NN).sql` son del **esquema base heredado** (persona, direccion, area_programatica, reparticion, jurídico, transporte, observatorio, etc.) y **no incluyen** la mayoría de las tablas de arriba. En ese esquema, `efector` tiene más columnas con el prefijo `efector_` y FK a `area_programatica` y `efectorcomplejidad`.

---

## 3. Relaciones

Todas son JOIN hechos desde los modelos. No hay FK declaradas en el código.

```
efector ─┬─< capacidad_camas          tipo_operativo ─< operativo ─< cantidad_operativo
         ├─< gestion_cama                                   (tipo_op_id = operativo.tipo_id)
         ├─< gestion_cama_hospitales >── departamento
         ├─< rendimiento_hospitalario / _uti       categoria ─────< consulta_reclamo
         ├─< rh_materno                            sub_categoria ─< consulta_reclamo
         ├─< guardia >── servicio
         ├─< lista_espera >── especialidad         electrodependiente ─┬─< electrodependiente_equipamiento
         ├─< produccion_quirofano / _hosp                              ├─< electrodependiente_historial
         ├─< salud_mental_camas                                        └─< equipamiento_historial
         ├─< transfusion                           electrodependiente >── localidad, efector,
         ├─< chat_bot                                                     obra_social, diagnostico
         ├─< turno_hospitalario
         └─< electrodependiente                    users ─< user_last_visit (por módulo)
```

Tablas **sin relaciones** (datos agregados o texto libre): `base`, `movil`, `asistencia`, `atencion`, `identificacion` (con `departamento` como texto), `call_center`, `carta_servicio`, `rrhh_carta_servicio`.

Auditoría: `ElectrodependienteModel::update()` compara el registro anterior con el nuevo y escribe cada campo cambiado en `electrodependiente_historial`. `EquipamientoModel::sincronizar()` hace lo mismo para los equipos en `equipamiento_historial`.

---

## 4. Navegación y paneles

```
/  (login Myth\Auth) → home → /admin/inicio (IndexController: KPIs de bases, móviles, asistencias, internación)
 ├─ Base            /admin/basev          BaseVController        → base, movil, asistencia, atencion, identificacion
 ├─ Servicios       /admin/serviciov      ServicioVController    → cantidad_operativo, transfusion
 ├─ Pacientes       /admin/pacientev      PacienteVController    → consulta_reclamo, chat_bot, turno_hospitalario, call_center
 ├─ Hospitalario    /admin/hospitalariov  HospitalarioVController
 │    ├─ Carta servicio  (AmbulatorioController → carta_servicio, rrhh_carta_servicio)
 │    ├─ Guardia         (GuardiaVController → guardia)
 │    ├─ Internación     /admin/internacionv → capacidad_camas, gestión camas, rendimiento, rendimiento UTI, rh_materno, salud_mental_camas
 │    └─ Quirófano       (QuirofanoVController → lista_espera, produccion_quirofano, produccion_quirofano_hosp)
 └─ Salud mental    /admin/saludmental    SaludMentalController  → electrodependiente
```

`BaseVController` y `PacienteVController` arman dashboards con agregados por ejercicio y rango de meses (filtros GET `ejercicio`, `mes_desde`, `mes_hasta`).

---

## 5. Módulos: controller, rutas y función

| Módulo | Controller | Prefijo de ruta | Qué hace |
|---|---|---|---|
| Bases | BaseCController | `base` | ABM de bases de emergencia (ubicación, coordenadas, región) |
| Móviles | MovilController | `movil` | Cantidad de móviles por tipo y ejercicio |
| Asistencias | AsistenciaController | `asistencia` | Asistencias mensuales por base operativa. CRUD e importación desde Excel |
| Atenciones | AtencionController | `atencion` | Atenciones y coberturas mensuales |
| Identificación | IdentificacionController | `identificacion` | Internaciones por departamento (adulto / pediátrico). Importación desde Excel |
| Operativos | CantidadOperativoController | `cantidad-operativo` | Operativos por vía pública u hospitalaria. **Sincroniza el Excel `cantidad_operativos.xlsx` con Google Drive** |
| ABM operativo | OperativoABMController | `operativo`, `tipo-operativo` | Catálogos de operativo y tipo |
| Transfusión | TransfusionController | `transfusion`, `transfusion-resumen` | Transfusiones mensuales por efector y un resumen anual exportable |
| Consultas y reclamos | ConsultaReclamoController | `consulta-reclamo` | Llamados por categoría y subcategoría |
| ABM categoría | CategoriaABMController | `categoria`, `sub-categoria` | Catálogos de consulta_reclamo |
| Chat bot | ChatBotController | `chat-bot` | Turnos otorgados por chatbot, por efector |
| Turnos | TurnoHospitalarioController | `turno-hospitalario` | Turnos atendidos, ausentes y cancelados por efector |
| Call center | CallCenterController | `call-center` | Llamadas atendidas y abandonadas |
| Gestión de camas | GestionCamaController | `gestion_cama` | Camas de cuidados básicos por efector |
| Gestión de pacientes hospitalarios | GestionPacienteHospitalController | `gestion_paciente_hospital` | Pacientes por zona, riesgo y tipo de cama |
| Efectores | EfectorABMController | `efector` | ABM de efectores (hospitales y centros) |
| Electrodependientes | ElectrodependienteController | `electrodependiente` | Padrón de pacientes electrodependientes con sus equipos e historial de cambios (`/(:num)/historial`) |
| Capacidad de camas | CapacidadCamasController | — | Capacidad por tipo de cama y efector |
| Rendimiento hospitalario | RendimientoHospitalarioController (+ `Uti`) | — | Indicadores semestrales. Valida que no se repita efector + ejercicio + semestre |
| RH materno | RhMaternoController | — | Rendimiento materno por servicio y sector |
| Salud mental (camas) | SaludMentalCamasController | — | Camas de salud mental por modalidad |
| Guardia | GuardiaController | — | Atenciones de guardia por servicio y mes |
| Lista de espera | ListaEsperaController | — | Pacientes en espera quirúrgica por especialidad (permite dar de alta una especialidad desde el mismo formulario) |
| Producción de quirófano | ProduccionQuirofanoController / ProduccionQuirofanoHospController | — | Producción quirúrgica anual y detalle por hospital |
| Carta de servicio | CartaServicioController / RrhhCartaServicioController | — | Oferta de servicios y RRHH por hospital. Solo importación y exportación Excel |

---

## 6. Inconsistencias detectadas

- **Controllers sin rutas** (la columna "—" de la tabla anterior): Capacidad de camas, Rendimiento (y UTI), RH materno, Salud mental camas, Guardia, Lista de espera, Producción de quirófano (ambos), Carta de servicio y RRHH, y los paneles `AmbulatorioController`, `GuardiaVController` y `QuirofanoVController`. Las vistas los enlazan con `route_to('…')` (`guardia_views`, `quirofano_views`, `ambulatorio_list`, `rh_materno_list`, etc.), pero esos nombres no existen en `Routes.php`. Con `autoRoute` desactivado, esos módulos no son accesibles.
- Las vistas usan `cama_hospitalaria_list`, `salud_mental_cama_list`, `list_users` y `groups_list`, que tampoco están definidas.
- `AtencionController` solo implementa `index`, `export` y `destroy`, pero hay rutas a `import`, `processImport`, `template`, `create`, `store` y `marcarVisto`.
- `base-visto` y `asistencia-visto` no tienen filtro de permiso.
- Los permisos son todos `* PERSONA` (heredados), así que no hay separación de permisos por módulo.
- `DepuracionQueueService` depende de modelos inexistentes (`RepositoriosModel`, etc.).
