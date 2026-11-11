# Prompt para construir la pestaña EJESA en el sistema visor

Cómo usarlo:
1. Copiá `SKILL.md` (esta misma carpeta) al otro sistema, en `.claude/skills/ejesa-visor/SKILL.md`.
2. Abrí Claude Code en el otro sistema y pegá el prompt de abajo, completando lo que está entre `<...>`.
3. Si la API de sistemabasec todavía no existe, usá la "Etapa 0" del final en **este** repo (sistemabasec) antes o en paralelo.

---

## Prompt (para el sistema visor)

```
Usá el skill ejesa-visor y seguilo al pie de la letra. Leelo completo antes de escribir código.

Contexto: este sistema SOLO MUESTRA datos. Los datos de EJESA los genera sistemabasec
(CodeIgniter 4, base MySQL "rep", hora de Jujuy). Necesito una pestaña "EJESA" dentro del panel
de este sistema que muestre de forma clara:
  - la fecha y hora de cada corte programado (y cuánto falta para el próximo),
  - cuántas horas se quedan sin luz las personas en cada corte y en el período,
  - cuántas personas electrodependientes quedan afectadas por cada corte (total y por riesgo:
    ALTO, MEDIANO, BAJO, PENDIENTE; "a revisar" aparte) y las persona-horas sin luz,
  - la auditoría: qué consultó EJESA (quién, cuándo, cómo, con qué resultado) y el historial de
    cambios de cada corte (publicaciones, reprogramaciones, cancelaciones, finalizaciones, protocolo).

Fuente de datos: <API de sistemabasec en https://... con token en .env | usuario MySQL de solo lectura "...">
Stack de este sistema: <framework, versión, motor de base, librería de gráficos, CSS>
Dónde va la pestaña: <ruta / archivo del panel / menú>
Quién la puede ver: <rol o permiso>

Cómo trabajar:
1. Primero explorá este repo y decime en 10 líneas: stack, cómo se arma una pestaña del panel,
   cómo se manejan permisos, dónde iría la configuración de la conexión. No escribas código todavía.
2. Proponé el plan (archivos a crear/tocar, sub-pestañas, consultas o llamadas a la API, cache)
   y esperá mi OK.
3. Implementá en este orden, mostrando avance al terminar cada paso:
   a. Capa de datos de solo lectura (cliente de API o repositorio SQL) + verificación de que no puede
      escribir.
   b. Servicio de cálculos (§4 del skill) con tests unitarios de todos los casos del checklist
      (finalizado antes, finalizado tarde, en curso, vencido, cancelado antes y durante el corte,
      sin localidad, sin protocolo, riesgo NULL).
   c. Sub-pestañas en este orden: Resumen, Próximos cortes, Historial de cortes, Impacto, Auditoría.
   d. Exportación a Excel del historial (solo cantidades, con período y filtros en el encabezado).
4. Mobile-first: probalo a 360 px de ancho.
5. Al final, recorré el checklist §8 del skill punto por punto y decime cuál se cumple y cómo lo
   verificaste. Lo que no se pudo verificar, decilo.

Ojo: el número de servicio y el titular son datos del PACIENTE (tabla electrodependiente),
no de cada equipo (regla 10 del skill). No leer electrodependiente_equipamiento.nro_servicio.

Prohibido: escribir en la base de sistemabasec, mostrar nombres/DNI/domicilios/coordenadas de
pacientes o el número de servicio completo, leer ejesa_consulta_persona, recalcular los afectados
con geometría propia, inventar el fin real de un corte, sumar "a revisar" a los afectados.
Entregá archivos completos, sin "// ...resto igual".
```

---

## Etapa 0 (en sistemabasec): la API de solo lectura

Hace falta para mostrar afectados de cortes **sin protocolo activado** (el cálculo vive en
`CorteModel::clasificar`). Pegar en Claude Code dentro de sistemabasec:

```
Implementá la API de solo lectura de EJESA descripta en docs/ejesa_visor/SKILL.md §7
(contrato acordado en docs/electrodependientes/AVANCE.md: "API GET de solo lectura con token y cursor").

- Rutas en app/Config/Routes.php (o en el archivo de rutas de EJESA si así está la convención),
  con nombre, solo GET, fuera de la sesión web: autenticación por token Bearer de un usuario de servicio
  (token guardado hasheado; migración nueva, portable a PostgreSQL según docs/postgresql/SKILL.md).
- Reutilizá CorteModel (vigentes, afectados/clasificar, historial) y no dupliques reglas.
  Afectados: si hay protocolo, ejesa_corte_afectado VIGENTE (fuente PROTOCOLO); si no, clasificar
  con el padrón actual (fuente CALCULO_ACTUAL). Solo cantidades.
- Horas sin luz y marcas exactamente como §4.2 del skill; la lógica en una clase con tests.
- Cada llamada queda registrada (quién, cuándo, endpoint, filtros) sin datos de pacientes.
- Nada de nombres, DNI, domicilios, coordenadas de pacientes ni número de servicio completo.
- Tests de: token inválido/ausente (401), método distinto de GET (405), respuesta sin datos personales,
  cálculo de horas en todos los casos, paginación por cursor.
- Actualizá docs/esquema.md. php spark postgres:inventario no debe sumar hallazgos.
```
