# Consultas: auditoría de ChatGPT y revisión pendiente del contrato

Fecha: 22 de octubre de 2026.

## Estado de esta entrega

Auditoría del repositorio y de la estructura real de `rep`. No se modifica la base ni se implementa contra columnas o métodos nuevos todavía.

No existe `docs/consultas/contrato.md` al momento de esta revisión. Claude debe redactarlo y cerrar el esquema y las interfaces antes de continuar el desarrollo dependiente. Este documento contiene observaciones y propuestas, no un contrato aprobado.

## Estado de Git

- Rama observada: `feature/formularios-atencion`. Mantenerla mientras ambos agentes compartan el directorio; no cambiar de rama durante el trabajo del otro.
- El commit que quedó pendiente en la conversación anterior sí terminó: `1ac7025`, entities y models de paciente/atención.
- Commits de configuración presentes: `d8d2905` y `bff1557`.
- El controller, las vistas, el archivo de rutas de atención y sus pruebas siguen sin commit.
- `Routes.php` contiene una línea sin commit que incluye las rutas de atención anteriores. Por tanto, esa implementación anterior está registrada y NO representa todavía el nuevo negocio ni restringe datos por efector.
- Hay cambios de Claude y cambios ajenos al módulo en el directorio. No se revierten ni se incluyen en commits de ChatGPT.

## Base verificada

Existen las siete tablas del módulo: `especialidad_consulta`, `formulario_consulta`, `efector_servicio_consulta`, `profesional_consulta`, `formulario_profesional_consulta`, `paciente_consulta`, `atencion_consulta`.

Los formularios iniciales son San Roque (1, efector 2), Pablo Soria (2, efector 1) y Materno (3, efector 3).

Al revisar: cero pacientes, cero profesionales y cero atenciones. Esto simplifica la adaptación, pero Claude debe volver a contar antes de ejecutar cambios porque el estado puede cambiar.

Los grupos actuales son `Administradores ` (con espacio final), `ADMINISTRADOR FULL`, `REPARTICION` y `Admin`. No hay grupos hospitalarios ni relación grupo → efector en el esquema del módulo revisado. No identificar privilegios por coincidencias parciales de nombres ni eliminar el espacio del grupo existente sin revisar su uso.

`atencion` y `especialidad` sin sufijo pertenecen a otros módulos: se conservan.

## Reglas confirmadas por el usuario

| Regla | Materno | Pablo Soria | San Roque |
| --- | --- | --- | --- |
| Profesional | Nombre completo libre + DNI, obligatorios | Nombre completo libre + DNI, obligatorios | Lista configurada de dos médicos, sin pedir DNI |
| Fecha/hora | Obligatorias | Obligatorias | Obligatorias |
| Paciente | Nombre completo en un campo + DNI | Igual | Igual |
| Diagnóstico | Obligatorio | Obligatorio | Obligatorio |
| Localidad / otra | Oculto | Oculto | Una alternativa obligatoria |
| Indicaciones | Oculto | Oculto | Obligatorio |
| Observaciones | Oculto | Oculto | Opcional |
| Procedimiento y fecha/hora | Opcionales | Opcionales | Oculto |
| Hospital de interconsulta | Obligatorio | Obligatorio | Oculto |
| Servicio | Salas del Materno | Servicios de Pablo Soria + Otro | Oculto |
| Otros que generaron interconsulta | Presente, semántica pendiente | Presente, semántica pendiente | Oculto |

La importación de Excel exportado de Google Forms es el flujo principal. La carga manual usa las mismas reglas. El usuario ve los datos de su efector según sus grupos; no es suficiente ocultar opciones en la vista.

## Qué conservar

- Catálogos iniciales de formularios y servicios, sujetos al ajuste de configuración de Claude.
- Reutilización de `efector`, `localidad`, `users` y autenticación/permisos del proyecto.
- Clave única de DNI de paciente y reutilización entre formularios, con política de consulta/actualización por acordar.
- Soft delete, timestamps y auditoría de creación de atención.
- Patrón transaccional de guardado y manejo de fallos.
- Componentes de filtros, paginación del servidor, escape de HTML y presentación desktop/mobile.
- Biblioteca PhpSpreadsheet ya instalada. `AdminController::leerExcelSubido()` sirve de referencia, pero su lectura actual no debe adoptarse sin revisar celdas de fechas, horas y encabezados reales.
- Ideas de las pruebas de rollback, validación y baja lógica. Las diez pruebas anteriores validaban el contrato viejo; su aprobación no acredita el nuevo diseño.

## Qué hay que reemplazar o adaptar

1. `PacienteModel` y vistas: hoy exigen apellido y nombre separados. El nuevo esquema debe aceptar nombre completo sin adivinar su división.
2. Profesional: hoy siempre se exige selección de profesional asignado. Se necesita distinguir modo libre y modo lista desde configuración. El DNI obligatorio y único actual impide cargar los médicos de San Roque sin inventarlo.
3. Acceso: listado, filtros históricos, detalle, edición, eliminación y búsqueda de DNI hoy no tienen alcance por efector. Todos requieren el servicio central de acceso antes de considerarse terminados.
4. Entrada al módulo: hoy hay un selector global de formularios. Para un usuario con uno permitido debe resolverse automáticamente. La selección para varios efectores queda pendiente de decisión.
5. Campos: indicaciones hoy son opcionales; deben ser obligatorias en San Roque. Observaciones hoy se muestran siempre; deben aparecer solo donde esté configurado.
6. “Otros que generaron la interconsulta” no debe confundirse automáticamente con “Otro servicio” ni con “Otro hospital”. Falta aclarar si es un campo independiente y qué representa.
7. Importación: todavía no existe en este módulo. No hay archivos Excel de muestra revisados.
8. Historia: los joins actuales muestran nombres vigentes de paciente/profesional/servicio. Cambiar esos catálogos puede cambiar la visualización de atenciones anteriores. El esquema debe definir qué valores originales se conservan por atención/importación.
9. Auditoría: falta acordar quién editó/importó, procedencia y trazabilidad del lote/fila. No agregar columnas por cuenta de ChatGPT.

## Interfaces que ChatGPT necesita de Claude

Claude definirá las firmas definitivas en el contrato. Requisitos para esas interfaces:

- Resolver los formularios permitidos para un usuario, considerando todos sus grupos y negando acceso cuando no haya asociación.
- Autorizar una operación concreta sobre un formulario, sin confiar en IDs enviados por el navegador. El acceso a registros históricos debe estar definido aparte de la disponibilidad para nuevas cargas.
- Obtener configuración explícita de campos visibles y obligatorios, modo de profesional, efector y listas habilitadas.
- Obtener profesionales de lista y servicios válidos para el formulario autorizado, incluyendo cómo mostrar referencias históricas desactivadas.
- Resolver o registrar profesionales libres por DNI dentro de la misma conexión/transacción usada al guardar la atención. Definir qué pasa si el mismo DNI llega con otro nombre y cómo manejar concurrencia.
- Definir el alcance de búsqueda de pacientes compartidos: qué puede ver un usuario cuando el DNI solo tiene antecedentes de otro efector y qué datos puede actualizar.
- Definir permisos para importar, previsualizar, confirmar, cargar manualmente, consultar, editar y eliminar. Los permisos genéricos actuales no sustituyen el alcance por hospital.
- Evitar que los catálogos o la validación del importador creen asignaciones de acceso implícitas.

Cada método deberá documentar firma, respuesta real, errores, responsabilidad transaccional y un ejemplo. No se programará contra estas descripciones como si ya fueran métodos disponibles.

## Importador: diseño a cerrar antes de implementarlo

1. Leer un Excel real de cada origen y registrar sus encabezados exactos, hoja utilizada, formatos de fecha/hora y columnas adicionales, incluida marca temporal si existe.
2. Mapear columnas originales al esquema acordado; no dividir nombres completos ni descartar columnas sin informarlo.
3. Usar el formulario autorizado del usuario como contexto. No autorizar por el nombre del archivo o una columna del Excel.
4. Compartir normalización, validación y persistencia con la carga manual.
5. Previsualizar filas válidas, errores por fila y posibles duplicados antes de confirmar.
6. Revalidar permisos y contenido al confirmar. La previsualización no habilita un acceso permanente ni permite alterar filas o cambiar de efector en el navegador.
7. Acordar la estrategia de duplicados/reimportación. DNI + fecha no basta como clave: puede haber varias atenciones válidas del mismo paciente el mismo día.
8. Acordar guardado de lotes, identidad de fila y conservación de valores de origen. Separar trazabilidad del archivo de las claves del catálogo.
9. Acordar si se rechaza todo el lote o se permite importar solo filas válidas mediante confirmación explícita. Nunca hacer carga parcial silenciosa.
10. No registrar diagnósticos u otros datos clínicos en logs técnicos.

## Decisiones y material pendientes

- Un Excel de muestra por Google Form, preferentemente con datos ficticios y conservando encabezados/tipos reales. Incluir alguna fila con procedimiento u opciones “Otro” cuando existan.
- Hospital de interconsulta: texto libre o lista en el formulario original.
- Significado y obligatoriedad de “Otros que generaron la interconsulta”, y relación con la opción Otro del servicio.
- Grupos hospitalarios, acceso global administrativo y usuarios con múltiples grupos/efectores.
- Política de edición de la ficha compartida del paciente y de los nombres de profesionales.
- Política de duplicados, reimportación y filas inválidas.

## Orden de continuación

1. Claude redacta el contrato y propone cambios concretos de esquema con impacto sobre datos.
2. ChatGPT revisa compatibilidad con importación, acceso y conservación histórica; las diferencias se resuelven antes de programar dependencias.
3. Claude ejecuta cambios estructurales y publica interfaces acordadas.
4. ChatGPT adapta entities/models y crea una única validación/persistencia para importación y carga manual.
5. Pruebas con los tres formatos y pruebas de acceso cruzado, duplicados, transacciones y bajas lógicas. Ningún dato de prueba queda persistido.

En esta auditoría no se eliminaron archivos, no se modificaron tablas y no se completó el módulo nuevo.
