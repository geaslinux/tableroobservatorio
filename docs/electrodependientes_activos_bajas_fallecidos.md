# Tablero · Electrodependientes: separar Activos, Bajas y Fallecidos

**Para:** equipo del tablero (`tableroobservatorio` / `sistemabase`)
**Desde:** sistema de carga (`sistemabaseobservatorio` / `sistemabasec`), noviembre 2026

## Qué pasa hoy

El tablero cuenta **539 pacientes** electrodependientes. Ese número suma activos más bajas y fallecidos
(`estado IN ('activo','desactivado')`). El Registro Provincial tiene **226 activos**, así que el total confunde.

El sistema de carga ya muestra las tres cifras por separado. El tablero tiene que mostrar lo mismo.

## Regla (igual que en el sistema de carga)

Columnas de la tabla `electrodependiente`: `estado` y `estado_vital`.

| Categoría | Condición SQL |
|---|---|
| **Fallecidos** | `estado_vital = 'fallecido'`, sin importar `estado` |
| **Activos** | `estado = 'activo' AND (estado_vital IS NULL OR estado_vital <> 'fallecido')` |
| **Bajas** | `estado = 'desactivado' AND (estado_vital IS NULL OR estado_vital <> 'fallecido')` |
| (no contar) | `estado = 'eliminado'` |

Un fallecido cuenta **solo** como fallecido, aunque esté desactivado. Las tres categorías no se superponen
y su suma da el total de hoy.

```sql
SELECT
  SUM(estado = 'activo'      AND COALESCE(estado_vital, '') <> 'fallecido') AS activos,
  SUM(estado = 'desactivado' AND COALESCE(estado_vital, '') <> 'fallecido') AS bajas,
  SUM(estado_vital = 'fallecido')                                           AS fallecidos,
  COUNT(*)                                                                  AS total
FROM electrodependiente
WHERE estado IN ('activo', 'desactivado');
```

Valores esperados con la base actual: **activos 228 · bajas 119 · fallecidos 192 · total 539**.
De los 228 activos, 3 son registros de prueba que se van a depurar, así que van a quedar 225.

## Qué se cambió en el tablero (ya implementado en este repositorio)

Los porcentajes (riesgo alto, con CUD, etc.) se dejaron como estaban, sobre el total del padrón.
Si se quiere calcularlos solo sobre activos, es un cambio aparte.

### 1. Inicio: bloque Salud mental

[app/Controllers/Admin/IndexController.php](../app/Controllers/Admin/IndexController.php), sección `SALUD MENTAL` (≈ línea 102).
Agregar al `select` de `$sm`:

```php
SUM(estado = 'activo'      AND COALESCE(estado_vital, '') <> 'fallecido') AS activos,
SUM(estado = 'desactivado' AND COALESCE(estado_vital, '') <> 'fallecido') AS bajas,
SUM(estado_vital = 'fallecido')                                           AS fallecidos
```

En [app/Views/admin/inicio_views.php](../app/Views/admin/inicio_views.php) (≈ línea 232), cambiar
"Pacientes electrodep." (`$sm['pacientes']`) por **Activos** (`$sm['activos']`). Si hay lugar, sumar Bajas y Fallecidos.

### 2. Panel Salud mental (KPI y estadísticas)

[app/Controllers/Admin/SaludMentalController.php](../app/Controllers/Admin/SaludMentalController.php), `salud()` (≈ línea 60):
agregar las mismas tres sumas al `select` de `$kpi` (con el prefijo `electrodependiente.`).

En [app/Views/admin/saludmental_views.php](../app/Views/admin/saludmental_views.php) (≈ línea 460), reemplazar la tarjeta
**Pacientes** por tres tarjetas: **Activos** (verde), **Bajas** (ámbar) y **Fallecidos** (gris).

Hay que decidir y avisar qué base usan los porcentajes, por ejemplo "Riesgo alto (x %)" o "Con CUD (x %)".
Hoy se calculan sobre activos + bajas + fallecidos. Recomendado: calcularlos solo sobre **activos**, porque un paciente
fallecido no tiene riesgo operativo vigente.

### 3. Listado de Electrodependientes del tablero

[app/Controllers/Admin/ElectrodependienteController.php](../app/Controllers/Admin/ElectrodependienteController.php) (≈ línea 145):
agregar `bajas` y `fallecidos` al `select` de `$kpis`. El `activos` que ya existe hay que corregirlo con la regla
de arriba, porque hoy cuenta como activo a un fallecido que siga en `estado = 'activo'`.

En [app/Views/electrodependiente/electrodependiente_list.php](../app/Views/electrodependiente/electrodependiente_list.php) (≈ línea 214),
la tarjeta "Pacientes" con el texto "X activos · Y desactivados" pasa a ser tres tarjetas: Activos, Bajas y Fallecidos.

## Requisito en la base

La columna `estado_vital` la crea la migración `2026-11-06-110000_EstadoVitalElectrodependiente` del sistema de carga.
Antes de desplegar, confirmar en la base que lee el tablero:

```sql
SHOW COLUMNS FROM electrodependiente LIKE 'estado_vital';
```

## Otros cambios en los datos (ya los hace el sistema de carga, el tablero no tiene que hacer nada)

- **Coordenadas:** script `docs/migracion_coordenadas/coordenadas_electrodp.sql` del sistema de carga. Completa por DNI
  la ubicación de 111 pacientes del Registro Provincial. Tras correrlo, "Ubicados en mapa" va a subir.
- **Nº de servicio verificado:** script `docs/migracion_coordenadas/verificar_servicio_electrodp.sql`. Marca como verificados
  los 523 pacientes que tienen número de servicio.

## Cómo comprobarlo

1. Correr la consulta SQL de arriba y anotar los cuatro números.
2. Abrir el Inicio, el panel Salud mental y el listado de Electrodependientes del tablero.
   Los tres tienen que mostrar los mismos Activos, Bajas y Fallecidos que la consulta.
3. Comparar con el sistema de carga (Salud mental › Electrodependientes): tiene que mostrar las mismas tres cifras.
