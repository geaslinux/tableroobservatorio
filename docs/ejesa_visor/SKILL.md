---
name: ejesa-visor
description: Pestaña EJESA de un sistema que solo lee datos (visor / observatorio) — cortes programados de EJESA, horas sin luz, personas electrodependientes afectadas, protocolo activado y auditoría de lo que consulta EJESA. Usar SIEMPRE que se programe, diseñe, calcule o revise cualquier cosa de EJESA, cortes de luz, cortes programados, horas sin luz, afectados por corte, persona-horas o auditoría de consultas de EJESA en el sistema visor, aunque solo se diga "la pestaña de EJESA", "los informes de cortes" o "cuánta gente se quedó sin luz".
---

# EJESA en el sistema visor (solo lectura)

El sistema **sistemabasec** (CodeIgniter 4, base `rep`) es el **dueño** de los datos de EJESA:
EJESA carga los cortes programados y consulta pacientes electrodependientes por número de servicio;
todo queda auditado. El **sistema visor** solo **muestra**: nunca escribe, nunca corrige, nunca
recalcula a mano lo que el dueño ya guardó.

Objetivo de la pestaña: que cualquiera entienda en 10 segundos
**cuándo es el próximo corte, cuántas horas dura, cuántas personas electrodependientes quedan sin luz
(y cuántas de riesgo ALTO), y quién consultó qué**.

---

## 1. Reglas que no se negocian

1. **Solo lectura.** Usuario de base de solo `SELECT` o API `GET`. Ningún `INSERT/UPDATE/DELETE`, ningún botón que cambie un corte.
2. **Solo cantidades de personas.** Nunca nombres, DNI, domicilio, teléfono, coordenadas de pacientes, diagnóstico ni equipos individuales. Ni en HTML, ni en JavaScript, ni en atributos `data-*`, ni en exportaciones. Los nombres solo existen en sistemabasec, donde abrirlos queda auditado.
3. **Número de servicio enmascarado** en la auditoría: mostrar solo los 3 últimos dígitos (`•••••123`).
4. **Riesgo:** `ALTO`, `MEDIANO`, `BAJO`, `PENDIENTE`. `NULL` es **PENDIENTE**, nunca BAJO. Colores fijos con texto siempre visible: ALTO rojo, MEDIANO amarillo, BAJO verde, PENDIENTE gris.
5. **"A revisar" nunca se suma a "afectados".** Son personas de la localidad sin coordenadas cuando el corte tiene zona dibujada: se muestran aparte.
6. **Los afectados son orientativos:** el domicilio no prueba de qué red eléctrica depende la persona. Toda pantalla con afectados lleva esa leyenda corta.
7. **Fechas siempre en hora de Jujuy** (`America/Argentina/Jujuy`, UTC−3, sin horario de verano) y formato `dd/mm/aaaa HH:MM`. Ver la tabla de zonas horarias (§3): hay columnas en hora local y otras en UTC.
8. **Si un dato no se puede saber, se dice.** "Fin real no registrado", "sin protocolo: cálculo al día de hoy". Nunca inventar ni completar con ceros silenciosos.
9. **SQL portable** (la base va a migrar a PostgreSQL): sin comillas invertidas, sin funciones propias de MySQL (`TIMESTAMPDIFF`, `DATE_FORMAT`, `IFNULL`, `GROUP_CONCAT`…). Las restas de fechas y los redondeos se hacen **en código**, no en SQL.
10. **El servicio eléctrico es del PACIENTE, no del equipo.** `electrodependiente.nro_servicio`, `titular_servicio`, `titular_servicio_dni`, `servicio_empresa`, `servicio_verificado` (EJESA solo encuentra por número a los verificados), `servicio_origen`. Las columnas `electrodependiente_equipamiento.nro_servicio` / `titular_servicio` son historia: **no se leen ni se escriben** (las migró `scriptoelectrodp.sql`, paso 4). En `ejesa_corte_afectado` el número queda en `nro_servicio_snapshot`. Normalizar siempre con `normalizar_nro_servicio()` (texto, conserva ceros, no es único).

### Implementación en sistemabase (tablero/observatorio)
- Panel `/admin/ejesa` (`EjesaVController`), enlazado como pestaña "EJESA · Cortes" desde Salud mental y con botón en el listado de Electrodependientes.
- Lee por la conexión `ejesa` (`app/Config/Database.php`); para que sea de solo `SELECT` configurar `database.ejesa.username/password` en `.env` y comprobar con `php spark ejesa:solo-lectura`.
- Este sistema ya tiene el ABM de pacientes con nombres (permiso `LISTADO PERSONA`), así que **el detalle de cada corte y la pestaña Impacto muestran quiénes son los afectados** (nombre, edad, riesgo, localidad, hospital, equipos, estado) con enlace a la ficha. Nunca DNI, domicilio, teléfono, coordenadas ni diagnóstico, y nunca en el Excel ni en el JavaScript: ahí solo cantidades.

---

## 2. De dónde salen los datos

Dos fuentes, en este orden de preferencia:

| Fuente | Qué da | Estado |
|---|---|---|
| **API de solo lectura de sistemabasec** (GET, token Bearer, paginación por cursor) | Todo, incluida la **cantidad de afectados calculada** de cortes sin protocolo | Contrato acordado (`docs/electrodependientes/AVANCE.md`: "API GET de solo lectura con token y cursor"); endpoints de §7 **a implementar en sistemabasec** |
| **Usuario de base de solo lectura** sobre `rep` | Cortes, historial, protocolo, afectados **guardados**, auditoría | Sirve ya, pero **no** puede dar afectados de cortes sin protocolo |

Por qué importa: los afectados de un corte se calculan en sistemabasec (`CorteModel::clasificar`)
cruzando el padrón activo con la localidad o el polígono del corte. Solo se **guardan** en
`ejesa_corte_afectado` cuando alguien **activa el protocolo**. El visor **no reimplementa** ese
cálculo (geometría, padrón con datos personales): lo pide a la API o muestra "sin protocolo".

---

## 3. Tablas (base `rep`, todas de sistemabasec)

### `ejesa_corte` — un corte programado
| Columna | Uso en el visor |
|---|---|
| `corte_id` | Identificador |
| `empresa` | Siempre filtrar `= 'EJESA'` |
| `referencia` | Nº de aviso de EJESA (mostrar) |
| `motivo` | Motivo operativo breve (mostrar) |
| `inicio_at`, `fin_estimado_at` | **Hora local de Jujuy.** Fecha del corte programado |
| `localidad_id` → `localidad.nombre` | Dónde (LEFT JOIN: puede ser NULL → "Localidad sin indicar") |
| `zona_descripcion` | Barrio / calles (texto) |
| `geometria` | Polígono GeoJSON de la zona. Puede dibujarse en un mapa (no tiene datos de personas) |
| `estado` | `BORRADOR` · `PROGRAMADO` · `EN_CURSO` · `FINALIZADO` · `CANCELADO` |
| `version` | Sube en cada cambio |
| `publicado_at`, `cancelado_at`, `created_at`, `updated_at` | **UTC** |
| `motivo_cancelacion` | Mostrar en cancelados |
| `baja_at` | Si no es NULL, el corte se dio de baja (solo borradores): **excluir** |

**Filtro base de todo el visor:** `empresa = 'EJESA' AND baja_at IS NULL AND estado <> 'BORRADOR'`.
Un borrador no está publicado: no es un corte real.

### `ejesa_corte_historial` — cada cambio del corte
`corte_id`, `version`, `accion`, `cambios` (JSON `{campo: {antes, despues}}`), `actor_id` → `users.username`, `registrado_at` (**UTC**).
Acciones: `CREAR`, `EDITAR`, `PUBLICAR`, `REPROGRAMAR` (cambió inicio o fin de un publicado), `CANCELAR`,
`FINALIZAR` (`cambios.vencido = true` si se finalizó después del fin estimado), `ACTIVAR_PROTOCOLO`
(`cambios.protocolo = {en_zona, a_revisar, revision, incidencia_id}`), `BAJA_BORRADOR`.

### `ejesa_corte_afectado` — afectados guardados al activar el protocolo
`corte_id`, `electrodependiente_id` (para contar y, en sistemabase, unir con la ficha), `nro_servicio_snapshot` (enmascarar), `riesgo_snapshot`
(`ALTO|MEDIANO|BAJO`, NULL = PENDIENTE), `revision_corte`, `calculado_at` (UTC),
`estado_vinculo` (`VIGENTE` | `REEMPLAZADO`). **Contar solo `VIGENTE`.**

### `contingencia_incidencia` — protocolo del corte
`corte_id`, `tipo = 'CORTE_PROGRAMADO'`, `estado` (`ABIERTA` … `CERRADA`), `ambito` JSON
`{en_zona, a_revisar, revision}`, `created_at` (UTC). Si existe para el corte → "Protocolo activado".

### `ejesa_consulta` — auditoría de lo que consulta EJESA
`consulta_id`, `evento_uuid`, `usuario_id`, `usuario_nombre_snapshot`, `perfil_snapshot`,
`consultado_at` (**UTC**), `nro_servicio_consultado` (enmascarar), `modalidad`
(`SERVICIO_EXACTO` = buscó un servicio · `CORTE` = abrió afectados de un corte · `RIESGO_AMBITO` = listado operativo),
`resultado` (`CON_RESULTADOS` · `SIN_RESULTADOS` · `DENEGADA` · `ERROR`), `cantidad_personas`,
`corte_id`, `ambito_snapshot` (JSON), `ip`.

### `ejesa_consulta_persona` — **no se usa en el visor**
Tiene nombres de pacientes. Si hace falta "personas distintas consultadas", pedirlo a la API como número.

### Zonas horarias (resumen)
| Hora local Jujuy | UTC (convertir restando 3 h) |
|---|---|
| `ejesa_corte.inicio_at`, `fin_estimado_at` | `created_at`, `updated_at`, `publicado_at`, `cancelado_at`, `baja_at`, `historial.registrado_at`, `afectado.calculado_at`, `consulta.consultado_at`, `incidencia.created_at` |

---

## 4. Cálculos (definiciones únicas: usar estas y no otras)

Sea `ahora` = fecha y hora actual de Jujuy. Todas las restas, en código.

### 4.1 Estado que se muestra
| Condición | Etiqueta |
|---|---|
| `PROGRAMADO` y `ahora < inicio_at` | **Programado** (azul) + "faltan 2 d 5 h" |
| `EN_CURSO`, o `PROGRAMADO` con `inicio_at ≤ ahora < fin_estimado_at` | **En curso** (rojo) |
| `PROGRAMADO`/`EN_CURSO` con `fin_estimado_at < ahora` | **Vencido sin finalizar** (ámbar): EJESA no lo cerró |
| `FINALIZADO` | **Finalizado** (verde) |
| `CANCELADO` | **Cancelado** (gris) |

### 4.2 Horas sin luz de un corte
- **Horas programadas** = `fin_estimado_at − inicio_at`.
- **Fin efectivo**:
  - `FINALIZADO` y la hora de `FINALIZAR` (historial, pasada a hora local) es **anterior** a `fin_estimado_at` → esa hora (se restableció antes).
  - `FINALIZADO` con `cambios.vencido = true` (se cerró tarde) → `fin_estimado_at` y la marca **"fin real no registrado"**.
  - En curso → `ahora` (parcial, marcar "en curso").
  - Vencido sin finalizar → `fin_estimado_at` y la marca **"sin finalizar"**.
  - `CANCELADO`: si `cancelado_at` (local) ≤ `inicio_at` → **0 h, no se cuenta**; si se canceló durante el corte → `cancelado_at`.
  - Programado futuro → 0 h ejecutadas (sus horas programadas van a "próximos").
- **Horas sin luz** = `max(0, fin efectivo − inicio_at)`, en horas con 1 decimal (`3,5 h`). Mostrar también `HH:MM`.
- No existe "inicio real": se usa `inicio_at`. Decirlo en la ayuda de la pantalla.

### 4.3 Personas afectadas de un corte
| Caso | Fuente | Etiqueta |
|---|---|---|
| Protocolo activado | `ejesa_corte_afectado` `VIGENTE` (o la API) | "Al activar el protocolo (dd/mm HH:MM)" |
| Sin protocolo | API (cálculo de sistemabasec con el padrón de hoy) | "Cálculo al día de hoy" |
| Sin protocolo y sin API | — | "Sin dato: activar protocolo en sistemabasec" (no mostrar 0) |

Desglose siempre: **total**, ALTO, MEDIANO, BAJO, PENDIENTE y, aparte, **a revisar**.

### 4.4 Indicadores de impacto
- **Persona-horas sin luz** = afectados × horas sin luz (por corte; se suman). Es el número que mejor resume el daño.
- **Persona-horas de riesgo ALTO** = afectados ALTO × horas sin luz.
- **Personas distintas afectadas en el período**: solo con API o con `ejesa_corte_afectado` (`COUNT(DISTINCT electrodependiente_id)`); nunca sumando totales de cortes (una persona puede estar en varios).
- **Duración media** y **corte más largo** del período (sobre cortes con horas > 0).
- **Cumplimiento de cierre**: % de cortes publicados que EJESA finalizó a tiempo (no vencidos).
- **Reprogramaciones**: cantidad de `REPROGRAMAR` por corte y en el período.

### 4.5 Período
Un corte pertenece al período si `inicio_at` cae dentro. Filtros: desde/hasta (por defecto el mes
actual), localidad, estado. Agrupar por mes con el año (`ejercicio` + `mes`, convención del sistema).

---

## 5. La pestaña EJESA (mobile-first)

Una pestaña **EJESA** dentro del panel del visor, con 5 sub-pestañas. En celular: tarjetas apiladas
y tablas que se convierten en tarjetas; en escritorio: grilla.

1. **Resumen**
   - Banda superior si hay corte **en curso** o **vencido sin finalizar** (texto, no solo color).
   - **Próximo corte**: fecha y hora (`Mar 18/11 08:00 a 13:00`), cuenta regresiva, localidad, zona, horas, afectados (total y ALTO).
   - KPIs del período: cortes publicados · horas sin luz · personas afectadas (distintas) · persona-horas · afectados ALTO · cancelados · vencidos sin finalizar.
2. **Próximos cortes**
   - Lista por fecha (y vista calendario mensual en escritorio). Cada uno: fecha/hora inicio y fin, duración, localidad, zona, referencia, afectados por riesgo, protocolo sí/no, cantidad de reprogramaciones.
   - Mapa opcional con el polígono de la zona (sin puntos de personas).
3. **Historial de cortes**
   - Tabla filtrable: fecha, localidad, estado (§4.1), horas programadas, horas sin luz (+ marcas), afectados (total / ALTO / a revisar), persona-horas, protocolo.
   - Detalle del corte: datos, línea de tiempo del historial (quién, cuándo, qué cambió: "reprogramado de 08:00 a 10:00"), motivo de cancelación.
   - Exportar a Excel **con las mismas columnas** (solo cantidades) y el período/filtros en el encabezado.
4. **Impacto** (gráficos)
   - Barras por mes: horas sin luz.
   - Barras apiladas por mes: personas afectadas por riesgo (ALTO/MEDIANO/BAJO/PENDIENTE).
   - Barras horizontales: top 10 localidades por persona-horas.
   - Línea: persona-horas por mes.
   - Cada gráfico con su tabla de datos debajo (accesible y verificable).
5. **Auditoría**
   - **Consultas de EJESA**: por día (barras), por usuario, por modalidad, por resultado. `DENEGADA` y `ERROR` destacados.
   - Tabla: fecha/hora local, usuario (`usuario_nombre_snapshot`), modalidad, resultado, cantidad de personas, servicio enmascarado, corte (enlace a su detalle), IP.
   - **Cambios de cortes**: tabla del historial (CREAR, PUBLICAR, REPROGRAMAR, CANCELAR, FINALIZAR, ACTIVAR_PROTOCOLO) con actor y hora.
   - Alertas: consultas fuera de horario (22–06 h), más de N consultas por usuario y día, picos de `SIN_RESULTADOS` (posible búsqueda al azar).

Todas las sub-pestañas muestran **"Datos al dd/mm/aaaa HH:MM"** (hora de la consulta o del último cache).

---

## 6. Consultas de referencia (portables)

Traen filas; los cálculos de §4 se hacen en código.

```sql
-- Cortes publicados del período (inicio dentro del rango, hora local)
SELECT c.corte_id, c.referencia, c.motivo, c.inicio_at, c.fin_estimado_at, c.estado, c.version,
       c.zona_descripcion, c.publicado_at, c.cancelado_at, c.motivo_cancelacion,
       c.localidad_id, l.nombre AS localidad
FROM ejesa_corte c
LEFT JOIN localidad l ON l.localidad_id = c.localidad_id
WHERE c.empresa = 'EJESA' AND c.baja_at IS NULL AND c.estado <> 'BORRADOR'
  AND c.inicio_at >= :desde AND c.inicio_at < :hasta
ORDER BY c.inicio_at;

-- Próximos (ahora = hora local de Jujuy, calculada en código)
SELECT ... FROM ejesa_corte c ...
WHERE c.empresa = 'EJESA' AND c.baja_at IS NULL
  AND c.estado IN ('PROGRAMADO', 'EN_CURSO') AND c.fin_estimado_at >= :ahora
ORDER BY c.inicio_at;

-- Hora de finalización y si fue tarde (cambios es JSON: leer "vencido" en código)
SELECT h.corte_id, h.registrado_at, h.cambios
FROM ejesa_corte_historial h
WHERE h.accion = 'FINALIZAR' AND h.corte_id IN (:ids);

-- Afectados guardados por riesgo (solo cantidades)
SELECT a.corte_id, a.riesgo_snapshot, COUNT(*) AS cantidad
FROM ejesa_corte_afectado a
WHERE a.estado_vinculo = 'VIGENTE' AND a.corte_id IN (:ids)
GROUP BY a.corte_id, a.riesgo_snapshot;

-- Protocolo por corte
SELECT i.corte_id, i.estado, i.created_at, i.ambito
FROM contingencia_incidencia i
WHERE i.tipo = 'CORTE_PROGRAMADO' AND i.corte_id IN (:ids);

-- Auditoría de consultas (rango en UTC: convertir desde/hasta locales sumando 3 h)
SELECT q.consultado_at, q.usuario_nombre_snapshot, q.modalidad, q.resultado,
       q.cantidad_personas, q.nro_servicio_consultado, q.corte_id, q.ip
FROM ejesa_consulta q
WHERE q.consultado_at >= :desde_utc AND q.consultado_at < :hasta_utc
ORDER BY q.consultado_at DESC, q.consulta_id DESC;

-- Historial de cambios con actor
SELECT h.corte_id, h.version, h.accion, h.cambios, h.registrado_at, u.username
FROM ejesa_corte_historial h
LEFT JOIN users u ON u.id = h.actor_id
WHERE h.registrado_at >= :desde_utc AND h.registrado_at < :hasta_utc
ORDER BY h.historial_id DESC;
```

Paginar la auditoría por cursor (`consultado_at, consulta_id`), no por `OFFSET`.

---

## 7. Contrato de la API (a implementar del lado de sistemabasec)

Solo `GET`, JSON, `Authorization: Bearer <token>` de un usuario de servicio con permiso de lectura
(no una cuenta de persona), sin datos personales de pacientes, horas en local Jujuy con offset
(`2026-11-18T08:00:00-03:00`). Cada llamada queda registrada en sistemabasec.

| Endpoint | Devuelve |
|---|---|
| `GET /api/ejesa/v1/cortes?desde=&hasta=&estado=&localidad_id=&cursor=` | Cortes con `horas_programadas`, `horas_sin_luz`, `marca_fin` (`REAL`, `NO_REGISTRADO`, `SIN_FINALIZAR`, `EN_CURSO`), `afectados {total, alto, mediano, bajo, pendiente, a_revisar, fuente: PROTOCOLO|CALCULO_ACTUAL, calculado_at}`, `protocolo`, `reprogramaciones`, `next_cursor` |
| `GET /api/ejesa/v1/cortes/{id}` | Lo anterior + `geometria` + historial (acción, actor, hora, cambios) |
| `GET /api/ejesa/v1/resumen?desde=&hasta=` | KPIs de §4.4, próximo corte y serie mensual |
| `GET /api/ejesa/v1/auditoria/consultas?desde=&hasta=&cursor=` | Filas de `ejesa_consulta` con servicio ya enmascarado |

Mientras la API no exista, el visor usa la base de solo lectura y en cortes sin protocolo muestra
"Sin dato: activar protocolo en sistemabasec". Si la API falla, el visor muestra el último dato
cacheado con su fecha y un aviso; nunca una pantalla vacía sin explicación.

---

## 8. Checklist antes de dar algo por terminado

- [ ] Ninguna respuesta, vista, JS o export contiene nombre, DNI, domicilio, coordenadas de paciente o número de servicio completo.
- [ ] Borradores y dados de baja excluidos; `empresa = 'EJESA'`.
- [ ] Horas en local Jujuy; columnas UTC convertidas.
- [ ] `NULL` de riesgo = PENDIENTE; "a revisar" separado del total.
- [ ] Horas sin luz con sus marcas (fin no registrado / sin finalizar / en curso); cancelados antes del inicio = 0 y fuera de totales.
- [ ] Personas distintas del período sin sumar totales de cortes.
- [ ] Cada gráfico con tabla debajo; KPIs con "Datos al …".
- [ ] Se ve bien en un celular de 360 px.
- [ ] Usuario de base o token sin permisos de escritura (probarlo: un `UPDATE` debe fallar).
- [ ] Tests de cálculos con casos: finalizado antes, finalizado tarde, en curso, vencido, cancelado antes y durante, sin localidad, sin protocolo.
