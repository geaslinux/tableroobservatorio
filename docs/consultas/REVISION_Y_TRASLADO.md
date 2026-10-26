# Revisión del módulo de consultas

Fecha: 23/10/2026. Alcance confirmado: carga, importación, edición, consulta, permisos y trazabilidad. Los análisis estadísticos se realizan en otro sistema; este módulo no contiene panel ni exportación de estadísticas.

## Correcciones y verificaciones

- Carga y edición adaptadas a nombres completos y a ConfigConsulta, usando las mismas validaciones que la importación.
- Médicos y pacientes identificados por DNI; se crean los que faltan, sin generar cuentas de usuario.
- Cada atención conserva una copia de nombres, DNI, localidad y textos de origen. Editarla no modifica las otras atenciones. Se registra updated_by y se conserva la trazabilidad del archivo de origen.
- Preparación y confirmación comprueban usuario, grupo y formulario en el servidor. Consulta, edición, baja, historial y detalle de lote respetan AccesoConsulta.
- Guardado transaccional; importación serializada por formulario; control de duplicados; errores de guardado revierten el lote.
- Historial de importaciones con acceso al detalle de filas omitidas.
- Lectura de archivos originales con columnas contiguas sin encabezado para servicio; rechazo de columnas que contienen valores contradictorios para el mismo campo.

## Datos existentes

375 atenciones de Pablo Soria: 374 importadas y una manual previa; sin bajas ni pacientes/profesionales huérfanos. Lote 1: 379 filas, 374 insertadas, 5 omitidas. Las importadas conservan datos_originales, fila_excel e importacion_id.

La migración `2026-10-23-160000_CompletarCopiasConsulta.php` completó únicamente copias vacías del registro manual anterior utilizando sus datos vinculados existentes. No reemplaza copias ya informadas. Resultado: cero atenciones con copias obligatorias vacías.

Pendientes del Excel original: fila 66 (fecha 8/13/0023), fila 191 (fecha 4/15/0224), filas 186, 231 y 232 (DNI profesional inválido). Requieren corrección en origen antes de reimportar; no se inventan fechas ni DNI. Los motivos se consultan en el historial.

## Pruebas

PHP 8.3.36, CodeIgniter 4 y MySQL; pruebas de integración con escrituras revertidas:

```powershell
$env:RUN_CONSULTA_DB_TESTS='1'
php vendor/phpunit/phpunit/phpunit tests/unit --filter 'AtencionConsultaTest|ImportadorConsultaTest'
php spark routes
```

Cubren carga, edición, copias históricas, campos ocultos, profesional de lista sin DNI, validación sin altas parciales, baja lógica, alcance, consulta por DNI, renderizado, confirmación real de importación, duplicados, omisiones y selección de grupo.

## Traslado futuro

No se instaló en otro sistema. Copiar el módulo de configuración, entidades/modelos de consultas, AccesoConsulta, ConfigConsulta, ImportadorConsulta, RegistroConsulta, AtencionConsultaController, ImportacionConsultaController, vistas formularios_atencion y rutas formularios_atencion. Conservar la dependencia PhpSpreadsheet del composer.lock, las entradas del menú y los permisos Myth Auth.

Para conservar datos, trasladar un respaldo SQL del módulo, incluida importacion_consulta y sus referencias; reconciliar usuarios, grupos, efectores y localidades con el destino. No asumir IDs idénticos. El esquema base está en docs/sql/formularios_consulta.sql y el rediseño en 2026-10-22-220000_RedisenoConsulta.php. Aplicar la migración de copias después del rediseño cuando corresponda. No ejecutar todas las migraciones indiscriminadamente: existen tablas de otros módulos creadas a mano. Comparar el esquema y ejecutar solo las migraciones necesarias.

Comprobar las dependencias PHP, permisos de writable/cache y sesiones, límites de carga (Excel hasta 10 MB y 10.000 filas), rutas sin duplicar y acceso por grupo. Comparar totales origen/destino, detalle de registros e importación repetida antes de habilitar cargas reales. La verificación del destino queda pendiente hasta disponer de acceso.
