# Integración del tablero de atenciones de consulta

Estado al 23/10/2026: conectado a la base compartida mediante consultas de solo lectura. El usuario autorizó completar también la capa estadística. Contrato vigente: `docs/consultas/contrato_tablero.md`; esquema y acceso originales: `docs/consultas/contrato.md`.

## Archivos

- `app/Libraries/EstadisticaConsulta.php`: alcance por grupos, validación, catálogos, agregados y detalle paginado.
- `app/Controllers/Admin/ConsultaTableroController.php`: tablero, detalle, CSV y comprobaciones de permisos.
- `app/Views/admin/consulta_tablero.php`: filtros, tarjetas compartidas, gráficos, tablas y paginación.
- `app/Views/admin/consulta_detalle.php`: detalle de la atención sin identificables del paciente.
- `public/js/consulta-tablero.js`: dependencias entre filtros, validación de fechas, carga y gráficos Chart.js.
- `public/css/consulta-tablero.css`: ajustes del módulo sobre Bulma, paneles y tarjetas existentes.
- `app/Config/Routes.php`: tres rutas de lectura protegidas dentro de `/admin`.
- `app/Views/layout/sidebar.php`: acceso al tablero, visible con `LISTADO PERSONA`.
- `app/Config/UltimaActualizacion.php`: fecha de actualización del módulo basada en `atencion_consulta`, evitando consultas del footer a módulos ajenos.
- `tests/unit/EstadisticaConsultaTest.php`: integración optativa de solo lectura.
- `docs/consultas/contrato_tablero.md` y este documento.

No se modificaron la base de datos, .env, vendor, grupos, usuarios, pacientes, profesionales, importaciones ni archivos de arranque de la actualización. No se copiaron los módulos de carga del proyecto vecino.

## Verificación

Ejecutar:

```powershell
$env:RUN_CONSULTA_DB_TESTS='1'
php vendor/phpunit/phpunit/phpunit tests/unit/EstadisticaConsultaTest.php --no-coverage
php spark routes
```

Sin la variable, las pruebas de base quedan omitidas. No crean fixtures ni realizan INSERT/UPDATE/DELETE: usan registros existentes y autenticación simulada en memoria para renderizar y verificar controladores.

Resultado: 8 pruebas, 458 aserciones correctas. Cobertura:

- Reconciliación directa del total con la tabla y con todas las distribuciones, serie y médicos.
- Pacientes únicos calculados por universo y filtro de atenciones con procedimiento.
- Filtro por profesional, fechas inclusivas, resultados vacíos, rango invertido y fecha inexistente.
- Sin asociación: cero registros y catálogos vacíos; intento de formulario fuera de alcance: 404.
- Páginas de 25 sin solapamiento y detalle que coincide con la fila del listado.
- Respuesta renderizada real con filtros, gráficos y exportación habilitados.
- CSV completo filtrado, cantidad reconciliada y nueve columnas sin identificables del paciente.
- Denegación 403 del controlador para tablero, detalle y exportación sin permiso.

`php -l` correcto para biblioteca, controlador y vistas. `php spark routes` confirmó las tres rutas con login y permiso.

Prueba visual con Chrome headless sobre HTML renderizado por el controlador, autenticación de prueba en memoria y datos reales sin identificables de pacientes. Assets y Chart.js cargados. Se verificaron 12 gráficos y seis selectores en los tres tamaños:

| Tamaño | Ancho CSS | Ancho del documento | Gráficos |
| --- | --- | --- | --- |
| Celular | 390 | 390 | 12 |
| Tablet | 820 | 820 | 12 |
| Escritorio | 1440 | 1425 (scroll vertical) | 12 |

Sin desbordamiento horizontal de la página. Las tablas mantienen scroll local. Capturas locales en `writable/consulta-mobile.png`, `writable/consulta-tablet.png` y `writable/consulta-desktop.png`. Esta prueba verifica renderizado y adaptación; no reemplaza una sesión manual con las credenciales del usuario ni constituye una auditoría completa de accesibilidad.

Referencia reconciliada de solo lectura el 23/10/2026: Pablo Soria, 375 atenciones vigentes, 359 pacientes únicos, ocho médicos y 342 atenciones con procedimiento, entre 14/04/2023 y 22/10/2026. Estos números no están codificados en la aplicación ni como resultados esperados de las pruebas. El total de cada usuario depende de sus grupos y filtros.
