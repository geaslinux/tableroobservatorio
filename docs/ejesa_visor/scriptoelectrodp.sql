-- =====================================================================
-- sistemabasec · Electrodependientes / EJESA / Grupo Acción
-- BD/scriptoelectrodp/scriptoelectrodp.sql — SCRIPT ÚNICO de base
--
-- Deja tu base local `rep` con la misma estructura que usa el código
-- subido al repositorio. Reemplaza tener que correr uno por uno:
--   BD/cambios_electrodependientes/01_estructura.sql
--   BD/cambios_electrodependientes/02_grupos_permisos.sql
--   BD/cambios_electrodependientes/03_migracion_servicio.sql
--   docs/electrodependientes/sql/08, 09, 10 y 11
--
-- CAMBIO PRINCIPAL: el Nº de servicio eléctrico y el titular del servicio
-- ya NO van en cada equipo (electrodependiente_equipamiento). Ahora son
-- datos del PACIENTE (tabla electrodependiente): nro_servicio,
-- titular_servicio, titular_servicio_dni, servicio_empresa, etc.
-- Este script copia los números que estaban cargados en los equipos a la
-- ficha del paciente (paso 4).
--
-- CÓMO CORRERLO
--   1) Hacé un respaldo de tu base (phpMyAdmin → Exportar).
--   2) phpMyAdmin → base `rep` → pestaña SQL → pegá TODO el archivo
--      (o "Importar" este archivo) → Continuar.
--      Por consola: mysql -u root rep < BD/scriptoelectrodp/scriptoelectrodp.sql
--   3) Mirá las tablas de resultado del final (paso 6).
--
-- Es IDEMPOTENTE: si ya tenías aplicada una parte, la saltea; se puede
-- correr más de una vez sin romper nada ni duplicar datos.
-- Requiere MySQL 8.0 (el de Laragon) y la base creada desde BD/rep.sql.
-- No borra datos. Las columnas viejas del equipo se conservan (paso 7).
-- =====================================================================

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- PASO 0 · Procedimientos auxiliares (se borran al final)
-- ---------------------------------------------------------------------
DROP PROCEDURE IF EXISTS _ed_agregar_columna;
DROP PROCEDURE IF EXISTS _ed_agregar_indice;
DROP PROCEDURE IF EXISTS _ed_agregar_fk;
DROP PROCEDURE IF EXISTS _ed_quitar_columna;
DROP PROCEDURE IF EXISTS _ed_migrar_servicio;

DELIMITER $$

CREATE PROCEDURE _ed_agregar_columna(IN p_tabla VARCHAR(64), IN p_columna VARCHAR(64), IN p_definicion TEXT)
BEGIN
  IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = p_tabla AND COLUMN_NAME = p_columna) THEN
    SET @ed_sql = CONCAT('ALTER TABLE `', p_tabla, '` ADD COLUMN `', p_columna, '` ', p_definicion);
    PREPARE ed_stmt FROM @ed_sql; EXECUTE ed_stmt; DEALLOCATE PREPARE ed_stmt;
  END IF;
END$$

CREATE PROCEDURE _ed_agregar_indice(IN p_tabla VARCHAR(64), IN p_indice VARCHAR(64), IN p_definicion TEXT)
BEGIN
  IF NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = p_tabla AND INDEX_NAME = p_indice) THEN
    SET @ed_sql = CONCAT('ALTER TABLE `', p_tabla, '` ADD ', p_definicion);
    PREPARE ed_stmt FROM @ed_sql; EXECUTE ed_stmt; DEALLOCATE PREPARE ed_stmt;
  END IF;
END$$

CREATE PROCEDURE _ed_agregar_fk(IN p_tabla VARCHAR(64), IN p_fk VARCHAR(64), IN p_definicion TEXT)
BEGIN
  IF NOT EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
                 WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = p_tabla
                   AND CONSTRAINT_NAME = p_fk AND CONSTRAINT_TYPE = 'FOREIGN KEY') THEN
    SET @ed_sql = CONCAT('ALTER TABLE `', p_tabla, '` ADD CONSTRAINT `', p_fk, '` ', p_definicion);
    PREPARE ed_stmt FROM @ed_sql; EXECUTE ed_stmt; DEALLOCATE PREPARE ed_stmt;
  END IF;
END$$

CREATE PROCEDURE _ed_quitar_columna(IN p_tabla VARCHAR(64), IN p_columna VARCHAR(64))
BEGIN
  IF EXISTS (SELECT 1 FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = p_tabla AND COLUMN_NAME = p_columna) THEN
    SET @ed_sql = CONCAT('ALTER TABLE `', p_tabla, '` DROP COLUMN `', p_columna, '`');
    PREPARE ed_stmt FROM @ed_sql; EXECUTE ed_stmt; DEALLOCATE PREPARE ed_stmt;
  END IF;
END$$

-- Traslada Nº de servicio y titular de los equipos a la ficha del paciente.
-- Solo si los equipos todavía tienen esas columnas y solo para pacientes
-- que aún no tienen número en la ficha y cuyos equipos concuerdan (un
-- único número y como mucho un titular). Los que no concuerdan se listan
-- en el paso 6 para cargarlos a mano desde la ficha.
CREATE PROCEDURE _ed_migrar_servicio()
BEGIN
  IF EXISTS (SELECT 1 FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'electrodependiente_equipamiento'
               AND COLUMN_NAME = 'nro_servicio')
     AND EXISTS (SELECT 1 FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'electrodependiente_equipamiento'
               AND COLUMN_NAME = 'titular_servicio') THEN

    UPDATE electrodependiente e
    JOIN (
      SELECT electrodependiente_id,
             MAX(NULLIF(TRIM(nro_servicio), ''))     AS nro,
             MAX(NULLIF(TRIM(titular_servicio), '')) AS tit
      FROM electrodependiente_equipamiento
      GROUP BY electrodependiente_id
      HAVING COUNT(DISTINCT NULLIF(TRIM(nro_servicio), '')) = 1
         AND COUNT(DISTINCT NULLIF(TRIM(titular_servicio), '')) <= 1
    ) x ON x.electrodependiente_id = e.electrodependiente_id
    SET e.nro_servicio        = x.nro,
        e.titular_servicio    = COALESCE(e.titular_servicio, x.tit),
        e.servicio_empresa    = COALESCE(e.servicio_empresa, 'EJESA'),
        e.servicio_origen     = 'MIGRACION_EQUIPO',
        e.servicio_verificado = 0
    WHERE e.nro_servicio IS NULL OR TRIM(e.nro_servicio) = '';

    SELECT ROW_COUNT() AS pacientes_con_servicio_migrado_desde_equipo;

    -- Pacientes que hay que revisar a mano (equipos con números o titulares distintos)
    SELECT eq.electrodependiente_id,
           COUNT(*) AS equipos,
           GROUP_CONCAT(DISTINCT NULLIF(TRIM(eq.nro_servicio), '')     SEPARATOR ' | ') AS nros_en_equipos,
           GROUP_CONCAT(DISTINCT NULLIF(TRIM(eq.titular_servicio), '') SEPARATOR ' | ') AS titulares_en_equipos
    FROM electrodependiente_equipamiento eq
    JOIN electrodependiente e ON e.electrodependiente_id = eq.electrodependiente_id
    WHERE e.nro_servicio IS NULL OR TRIM(e.nro_servicio) = ''
    GROUP BY eq.electrodependiente_id
    HAVING COUNT(DISTINCT NULLIF(TRIM(eq.nro_servicio), '')) > 1
        OR COUNT(DISTINCT NULLIF(TRIM(eq.titular_servicio), '')) > 1
        OR (COUNT(DISTINCT NULLIF(TRIM(eq.nro_servicio), '')) = 0
            AND COUNT(DISTINCT NULLIF(TRIM(eq.titular_servicio), '')) > 0);
  ELSE
    SELECT 'Los equipos ya no tienen nro_servicio/titular_servicio: no hay nada que migrar' AS aviso;
  END IF;
END$$

DELIMITER ;


-- ---------------------------------------------------------------------
-- PASO 1 · electrodependiente: datos del servicio eléctrico en el PACIENTE,
--          responsable/tutor y evaluación de riesgo
-- ---------------------------------------------------------------------
CALL _ed_agregar_columna('electrodependiente', 'servicio_empresa',        "VARCHAR(50) NULL DEFAULT 'EJESA' AFTER `updated_at`");
CALL _ed_agregar_columna('electrodependiente', 'nro_servicio',            "VARCHAR(50) NULL COMMENT 'Texto: conserva ceros iniciales. NO es único (suministro compartido)' AFTER `servicio_empresa`");
CALL _ed_agregar_columna('electrodependiente', 'titular_servicio',        "VARCHAR(200) NULL COMMENT 'Titular del suministro; puede diferir del paciente' AFTER `nro_servicio`");
CALL _ed_agregar_columna('electrodependiente', 'titular_servicio_dni',    "VARCHAR(20) NULL AFTER `titular_servicio`");
CALL _ed_agregar_columna('electrodependiente', 'servicio_verificado',     "TINYINT(1) NOT NULL DEFAULT 0 AFTER `titular_servicio_dni`");
CALL _ed_agregar_columna('electrodependiente', 'servicio_verificado_at',  "DATETIME NULL AFTER `servicio_verificado`");
CALL _ed_agregar_columna('electrodependiente', 'servicio_verificado_por', "INT UNSIGNED NULL AFTER `servicio_verificado_at`");
CALL _ed_agregar_columna('electrodependiente', 'servicio_origen',         "VARCHAR(30) NULL COMMENT 'MIGRACION_EQUIPO | CARGA_FICHA' AFTER `servicio_verificado_por`");
CALL _ed_agregar_columna('electrodependiente', 'domicilio_referencia',    "VARCHAR(255) NULL COMMENT 'Referencia de acceso al domicilio' AFTER `servicio_origen`");
CALL _ed_agregar_columna('electrodependiente', 'responsable_nombre',      "VARCHAR(200) NULL COMMENT 'Tutor/responsable (no es el titular eléctrico)' AFTER `domicilio_referencia`");
CALL _ed_agregar_columna('electrodependiente', 'responsable_vinculo',     "VARCHAR(100) NULL AFTER `responsable_nombre`");
CALL _ed_agregar_columna('electrodependiente', 'responsable_contacto',    "VARCHAR(200) NULL AFTER `responsable_vinculo`");
CALL _ed_agregar_columna('electrodependiente', 'riesgo_evaluado_at',      "DATETIME NULL AFTER `responsable_contacto`");
CALL _ed_agregar_columna('electrodependiente', 'riesgo_evaluado_por',     "INT UNSIGNED NULL AFTER `riesgo_evaluado_at`");

CALL _ed_agregar_indice('electrodependiente', 'idx_ed_nro_servicio',     'INDEX `idx_ed_nro_servicio` (`nro_servicio`)');
CALL _ed_agregar_indice('electrodependiente', 'idx_ed_empresa_servicio', 'INDEX `idx_ed_empresa_servicio` (`servicio_empresa`, `nro_servicio`)');

CALL _ed_agregar_fk('electrodependiente', 'fk_ed_serv_verif_por',  'FOREIGN KEY (`servicio_verificado_por`) REFERENCES `users` (`id`)');
CALL _ed_agregar_fk('electrodependiente', 'fk_ed_riesgo_eval_por', 'FOREIGN KEY (`riesgo_evaluado_por`) REFERENCES `users` (`id`)');


-- ---------------------------------------------------------------------
-- PASO 2 · Tablas nuevas (en orden de dependencias)
-- ---------------------------------------------------------------------

-- Imágenes de equipamiento (archivos privados en WRITEPATH)
CREATE TABLE IF NOT EXISTS `equipamiento_imagen` (
  `imagen_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `equipamiento_id` int NOT NULL,
  `ruta_privada` varchar(500) NOT NULL COMMENT 'Ruta dentro de WRITEPATH, nunca pública',
  `ruta_miniatura` varchar(500) DEFAULT NULL,
  `nombre_original` varchar(255) DEFAULT NULL,
  `mime` varchar(100) NOT NULL,
  `bytes` bigint unsigned NOT NULL,
  `hash_sha256` char(64) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `es_principal` tinyint(1) NOT NULL DEFAULT '0',
  `estado` varchar(20) NOT NULL DEFAULT 'ACTIVA' COMMENT 'ACTIVA | BAJA',
  `creado_por` int unsigned NOT NULL,
  `creado_at` datetime NOT NULL,
  `baja_por` int unsigned DEFAULT NULL,
  `baja_at` datetime DEFAULT NULL,
  `motivo_baja` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`imagen_id`),
  KEY `idx_img_equipo_estado` (`equipamiento_id`,`estado`),
  KEY `fk_img_creado` (`creado_por`),
  KEY `fk_img_baja` (`baja_por`),
  CONSTRAINT `fk_img_baja` FOREIGN KEY (`baja_por`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_img_creado` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_img_equipo` FOREIGN KEY (`equipamiento_id`) REFERENCES `electrodependiente_equipamiento` (`equipamiento_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Cortes programados EJESA
CREATE TABLE IF NOT EXISTS `ejesa_corte` (
  `corte_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa` varchar(50) NOT NULL DEFAULT 'EJESA',
  `referencia` varchar(100) DEFAULT NULL COMMENT 'Nº de aviso de EJESA',
  `motivo` varchar(255) DEFAULT NULL COMMENT 'Motivo operativo breve, sin datos clínicos',
  `inicio_at` datetime NOT NULL COMMENT 'Hora local',
  `fin_estimado_at` datetime NOT NULL COMMENT 'Hora local',
  `zona_horaria` varchar(64) NOT NULL DEFAULT 'America/Argentina/Jujuy',
  `localidad_id` int DEFAULT NULL,
  `zona_descripcion` text,
  `geometria` json DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'BORRADOR' COMMENT 'BORRADOR | PROGRAMADO | EN_CURSO | FINALIZADO | CANCELADO',
  `version` int unsigned NOT NULL DEFAULT '1',
  `creado_por` int unsigned NOT NULL,
  `actualizado_por` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `publicado_at` datetime DEFAULT NULL,
  `cancelado_at` datetime DEFAULT NULL,
  `cancelado_por` int unsigned DEFAULT NULL,
  `motivo_cancelacion` varchar(255) DEFAULT NULL,
  `baja_at` datetime DEFAULT NULL COMMENT 'Baja auditada, solo borradores',
  `baja_por` int unsigned DEFAULT NULL,
  PRIMARY KEY (`corte_id`),
  KEY `idx_corte_estado_fechas` (`estado`,`inicio_at`,`fin_estimado_at`),
  KEY `idx_corte_localidad` (`localidad_id`),
  KEY `fk_corte_creado` (`creado_por`),
  KEY `fk_corte_actualiz` (`actualizado_por`),
  KEY `fk_corte_cancel` (`cancelado_por`),
  KEY `fk_corte_baja` (`baja_por`),
  CONSTRAINT `fk_corte_actualiz` FOREIGN KEY (`actualizado_por`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_corte_baja` FOREIGN KEY (`baja_por`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_corte_cancel` FOREIGN KEY (`cancelado_por`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_corte_creado` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`),
  CONSTRAINT `chk_corte_fechas` CHECK ((`fin_estimado_at` > `inicio_at`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `ejesa_corte_servicio` (
  `corte_servicio_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `corte_id` bigint unsigned NOT NULL,
  `nro_servicio` varchar(50) NOT NULL,
  `origen` varchar(30) NOT NULL DEFAULT 'CARGA_MANUAL' COMMENT 'CARGA_MANUAL | IMPORTACION | POLIGONO',
  PRIMARY KEY (`corte_servicio_id`),
  UNIQUE KEY `uq_corte_servicio` (`corte_id`,`nro_servicio`),
  KEY `idx_cs_nro` (`nro_servicio`),
  CONSTRAINT `fk_cs_corte` FOREIGN KEY (`corte_id`) REFERENCES `ejesa_corte` (`corte_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `ejesa_corte_afectado` (
  `afectado_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `corte_id` bigint unsigned NOT NULL,
  `electrodependiente_id` int NOT NULL,
  `nro_servicio_snapshot` varchar(50) DEFAULT NULL,
  `riesgo_snapshot` varchar(20) DEFAULT NULL COMMENT 'ALTO | MEDIANO | BAJO | NULL = PENDIENTE',
  `revision_corte` int unsigned NOT NULL,
  `calculado_at` datetime NOT NULL,
  `estado_vinculo` varchar(20) NOT NULL DEFAULT 'VIGENTE' COMMENT 'VIGENTE | REEMPLAZADO',
  PRIMARY KEY (`afectado_id`),
  UNIQUE KEY `uq_afectado_rev` (`corte_id`,`revision_corte`,`electrodependiente_id`),
  KEY `idx_af_paciente` (`electrodependiente_id`),
  CONSTRAINT `fk_af_corte` FOREIGN KEY (`corte_id`) REFERENCES `ejesa_corte` (`corte_id`),
  CONSTRAINT `fk_af_paciente` FOREIGN KEY (`electrodependiente_id`) REFERENCES `electrodependiente` (`electrodependiente_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `ejesa_corte_historial` (
  `historial_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `corte_id` bigint unsigned NOT NULL,
  `version` int unsigned NOT NULL,
  `accion` varchar(30) NOT NULL COMMENT 'CREAR | EDITAR | PUBLICAR | REPROGRAMAR | CANCELAR | BAJA_BORRADOR',
  `cambios` json DEFAULT NULL,
  `actor_id` int unsigned NOT NULL,
  `registrado_at` datetime NOT NULL COMMENT 'UTC',
  PRIMARY KEY (`historial_id`),
  KEY `idx_ch_corte` (`corte_id`,`version`),
  KEY `fk_ch_actor` (`actor_id`),
  CONSTRAINT `fk_ch_actor` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_ch_corte` FOREIGN KEY (`corte_id`) REFERENCES `ejesa_corte` (`corte_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Auditoría de consultas de EJESA por Nº de servicio
CREATE TABLE IF NOT EXISTS `ejesa_consulta` (
  `consulta_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `evento_uuid` char(36) NOT NULL,
  `usuario_id` int unsigned NOT NULL,
  `usuario_nombre_snapshot` varchar(255) DEFAULT NULL,
  `perfil_snapshot` varchar(50) DEFAULT NULL,
  `consultado_at` datetime(6) NOT NULL COMMENT 'UTC',
  `nro_servicio_consultado` varchar(50) DEFAULT NULL COMMENT 'NULL solo en consultas por corte/ámbito',
  `modalidad` varchar(30) NOT NULL COMMENT 'SERVICIO_EXACTO | CORTE | RIESGO_AMBITO',
  `resultado` varchar(30) NOT NULL COMMENT 'CON_RESULTADOS | SIN_RESULTADOS | DENEGADA | ERROR',
  `cantidad_personas` int unsigned NOT NULL DEFAULT '0',
  `corte_id` bigint unsigned DEFAULT NULL,
  `filtro_riesgo` varchar(20) DEFAULT NULL,
  `ambito_snapshot` json DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`consulta_id`),
  UNIQUE KEY `uq_consulta_uuid` (`evento_uuid`),
  KEY `idx_cons_fecha` (`consultado_at`,`consulta_id`),
  KEY `idx_cons_usuario` (`usuario_id`,`consultado_at`),
  KEY `idx_cons_servicio` (`nro_servicio_consultado`,`consultado_at`),
  KEY `fk_cons_corte` (`corte_id`),
  CONSTRAINT `fk_cons_corte` FOREIGN KEY (`corte_id`) REFERENCES `ejesa_corte` (`corte_id`),
  CONSTRAINT `fk_cons_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `ejesa_consulta_persona` (
  `consulta_persona_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `consulta_id` bigint unsigned NOT NULL,
  `electrodependiente_id` int NOT NULL,
  `paciente_nombre_snapshot` varchar(200) NOT NULL,
  `nro_servicio_snapshot` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`consulta_persona_id`),
  UNIQUE KEY `uq_cons_persona` (`consulta_id`,`electrodependiente_id`),
  KEY `idx_cp_paciente` (`electrodependiente_id`,`consulta_id`),
  CONSTRAINT `fk_cp_consulta` FOREIGN KEY (`consulta_id`) REFERENCES `ejesa_consulta` (`consulta_id`),
  CONSTRAINT `fk_cp_paciente` FOREIGN KEY (`electrodependiente_id`) REFERENCES `electrodependiente` (`electrodependiente_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Grupo Acción: plan de contingencia, incidencias y actuaciones
CREATE TABLE IF NOT EXISTS `contingencia_plan` (
  `plan_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `electrodependiente_id` int NOT NULL,
  `responsable_id` int unsigned DEFAULT NULL,
  `canal_contacto` varchar(100) DEFAULT NULL,
  `pauta_operativa` text,
  `respaldo_energetico` varchar(200) DEFAULT NULL,
  `autonomia_minutos` int unsigned DEFAULT NULL COMMENT 'NULL = PENDIENTE; nunca calcular desde diagnóstico',
  `verificado_at` datetime DEFAULT NULL,
  `traslado_indicado` tinyint(1) DEFAULT NULL COMMENT 'NULL = PENDIENTE',
  `destino` varchar(255) DEFAULT NULL,
  `validado_por` int unsigned DEFAULT NULL,
  `validado_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`plan_id`),
  UNIQUE KEY `uq_plan_paciente` (`electrodependiente_id`),
  KEY `fk_plan_responsable` (`responsable_id`),
  KEY `fk_plan_validado` (`validado_por`),
  CONSTRAINT `fk_plan_paciente` FOREIGN KEY (`electrodependiente_id`) REFERENCES `electrodependiente` (`electrodependiente_id`),
  CONSTRAINT `fk_plan_responsable` FOREIGN KEY (`responsable_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_plan_validado` FOREIGN KEY (`validado_por`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `contingencia_incidencia` (
  `incidencia_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `corte_id` bigint unsigned DEFAULT NULL COMMENT 'NULL = corte imprevisto',
  `tipo` varchar(30) NOT NULL COMMENT 'CORTE_PROGRAMADO | CORTE_IMPREVISTO | OTRO',
  `inicio_at` datetime NOT NULL,
  `ambito` json DEFAULT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'ABIERTA' COMMENT 'ABIERTA | EN_SEGUIMIENTO | CERRADA',
  `creado_por` int unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `cerrado_at` datetime DEFAULT NULL,
  PRIMARY KEY (`incidencia_id`),
  KEY `idx_inc_estado` (`estado`,`inicio_at`),
  KEY `fk_inc_corte` (`corte_id`),
  KEY `fk_inc_creado` (`creado_por`),
  CONSTRAINT `fk_inc_corte` FOREIGN KEY (`corte_id`) REFERENCES `ejesa_corte` (`corte_id`),
  CONSTRAINT `fk_inc_creado` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `contingencia_actuacion` (
  `actuacion_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `evento_uuid` char(36) NOT NULL COMMENT 'Para conciliar fuera de línea sin duplicar',
  `incidencia_id` bigint unsigned DEFAULT NULL,
  `electrodependiente_id` int NOT NULL,
  `actor_id` int unsigned NOT NULL,
  `estado` varchar(30) NOT NULL COMMENT 'PENDIENTE | ASIGNADO | CONTACTO_INTENTADO | CONTACTADO | ASISTENCIA_EN_CURSO | ASISTENCIA_COMPLETADA | NO_LOCALIZADO',
  `nota_operativa` text,
  `ocurrido_at` datetime NOT NULL,
  `registrado_at` datetime NOT NULL COMMENT 'UTC',
  `origen` varchar(20) NOT NULL DEFAULT 'EN_LINEA' COMMENT 'EN_LINEA | FUERA_DE_LINEA',
  PRIMARY KEY (`actuacion_id`),
  UNIQUE KEY `uq_act_uuid` (`evento_uuid`),
  KEY `idx_act_paciente` (`electrodependiente_id`,`ocurrido_at`),
  KEY `idx_act_incidencia` (`incidencia_id`),
  KEY `fk_act_actor` (`actor_id`),
  CONSTRAINT `fk_act_actor` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_act_incidencia` FOREIGN KEY (`incidencia_id`) REFERENCES `contingencia_incidencia` (`incidencia_id`),
  CONSTRAINT `fk_act_paciente` FOREIGN KEY (`electrodependiente_id`) REFERENCES `electrodependiente` (`electrodependiente_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copia de emergencia (paquete de contingencia) y sus entregas
CREATE TABLE IF NOT EXISTS `contingencia_paquete` (
  `paquete_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(50) NOT NULL,
  `periodo_desde` date NOT NULL,
  `periodo_hasta` date NOT NULL,
  `filtros` json DEFAULT NULL,
  `generado_at` datetime NOT NULL COMMENT 'UTC',
  `datos_actualizados_hasta` datetime DEFAULT NULL,
  `ruta_privada` varchar(500) DEFAULT NULL,
  `hash_sha256` char(64) DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'GENERANDO' COMMENT 'GENERANDO | DISPONIBLE | ERROR | RETIRADO',
  `generado_por` int unsigned DEFAULT NULL COMMENT 'NULL = generación automática',
  PRIMARY KEY (`paquete_id`),
  UNIQUE KEY `uq_paquete_version` (`version`),
  KEY `idx_paq_estado` (`estado`,`generado_at`),
  KEY `fk_paq_generado` (`generado_por`),
  CONSTRAINT `fk_paq_generado` FOREIGN KEY (`generado_por`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `contingencia_paquete_entrega` (
  `entrega_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `paquete_id` bigint unsigned NOT NULL,
  `destinatario_id` int unsigned NOT NULL,
  `entregado_at` datetime DEFAULT NULL,
  `descargado_at` datetime DEFAULT NULL,
  `disponibilidad_confirmada_at` datetime DEFAULT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'PENDIENTE' COMMENT 'PENDIENTE | DESCARGADO | CONFIRMADO',
  PRIMARY KEY (`entrega_id`),
  UNIQUE KEY `uq_entrega` (`paquete_id`,`destinatario_id`),
  KEY `idx_ent_dest` (`destinatario_id`),
  CONSTRAINT `fk_ent_dest` FOREIGN KEY (`destinatario_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_ent_paquete` FOREIGN KEY (`paquete_id`) REFERENCES `contingencia_paquete` (`paquete_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Destinatarios de la copia de emergencia: cada uno es un usuario del sistema
CREATE TABLE IF NOT EXISTS `contingencia_funcion` (
  `funcion_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`funcion_id`),
  UNIQUE KEY `uq_contingencia_funcion_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `contingencia_destinatario` (
  `destinatario_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) NOT NULL,
  `funcion_id` bigint unsigned NOT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`destinatario_id`),
  UNIQUE KEY `uq_contingencia_destinatario_usuario` (`user_id`),
  KEY `idx_contingencia_destinatario_funcion` (`funcion_id`),
  CONSTRAINT `fk_contingencia_destinatario_funcion` FOREIGN KEY (`funcion_id`) REFERENCES `contingencia_funcion` (`funcion_id`),
  CONSTRAINT `fk_contingencia_destinatario_usuario` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Si ya tenías contingencia_destinatario de una versión anterior (con
-- telefono/correo), se corrige: se agrega user_id y se quitan telefono y
-- correo (WhatsApp y correo se descartaron el 30/10/2026).
CALL _ed_agregar_columna('contingencia_destinatario', 'user_id', 'INT UNSIGNED NULL AFTER `funcion_id`');
CALL _ed_agregar_indice('contingencia_destinatario', 'uq_contingencia_destinatario_usuario', 'UNIQUE KEY `uq_contingencia_destinatario_usuario` (`user_id`)');
CALL _ed_agregar_fk('contingencia_destinatario', 'fk_contingencia_destinatario_usuario', 'FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)');
CALL _ed_quitar_columna('contingencia_destinatario', 'correo');
CALL _ed_quitar_columna('contingencia_destinatario', 'telefono');

-- Avisos operativos y eventos de seguridad (auditoría)
CREATE TABLE IF NOT EXISTS `operacion_aviso` (
  `aviso_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `corte_id` bigint unsigned DEFAULT NULL,
  `incidencia_id` bigint unsigned DEFAULT NULL,
  `destinatario_id` int unsigned NOT NULL,
  `canal` varchar(30) NOT NULL COMMENT 'SISTEMA | EMAIL | WHATSAPP | SMS | TELEFONO',
  `estado` varchar(30) NOT NULL DEFAULT 'CREADO' COMMENT 'CREADO | ENVIADO | ENTREGADO | CONFIRMADO | FALLIDO | ESCALADO',
  `intentos` int unsigned NOT NULL DEFAULT '0',
  `creado_at` datetime NOT NULL,
  `enviado_at` datetime DEFAULT NULL,
  `entregado_at` datetime DEFAULT NULL,
  `confirmado_at` datetime DEFAULT NULL,
  `proximo_intento_at` datetime DEFAULT NULL,
  PRIMARY KEY (`aviso_id`),
  KEY `idx_aviso_estado` (`estado`,`proximo_intento_at`),
  KEY `idx_aviso_dest` (`destinatario_id`),
  KEY `fk_aviso_corte` (`corte_id`),
  KEY `fk_aviso_incidencia` (`incidencia_id`),
  CONSTRAINT `fk_aviso_corte` FOREIGN KEY (`corte_id`) REFERENCES `ejesa_corte` (`corte_id`),
  CONSTRAINT `fk_aviso_dest` FOREIGN KEY (`destinatario_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_aviso_incidencia` FOREIGN KEY (`incidencia_id`) REFERENCES `contingencia_incidencia` (`incidencia_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `seguridad_evento` (
  `evento_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `actor_id` int unsigned DEFAULT NULL COMMENT 'NULL = proceso automático',
  `accion` varchar(60) NOT NULL,
  `recurso_tipo` varchar(60) NOT NULL,
  `recurso_id` varchar(100) DEFAULT NULL,
  `registrado_at` datetime NOT NULL COMMENT 'UTC',
  `resultado` varchar(30) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `metadatos_minimos` json DEFAULT NULL,
  PRIMARY KEY (`evento_id`),
  KEY `idx_seg_actor` (`actor_id`,`registrado_at`),
  KEY `idx_seg_recurso` (`recurso_tipo`,`recurso_id`),
  KEY `idx_seg_fecha` (`registrado_at`),
  CONSTRAINT `fk_seg_actor` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- ---------------------------------------------------------------------
-- PASO 3 · Catálogo de funciones de los destinatarios
-- ---------------------------------------------------------------------
INSERT INTO contingencia_funcion (nombre)
SELECT x.nombre FROM (
            SELECT 'Operativo' AS nombre
  UNION ALL SELECT 'Funcionario'
  UNION ALL SELECT 'Administrativo'
) x
WHERE NOT EXISTS (SELECT 1 FROM contingencia_funcion f WHERE f.nombre = x.nombre);


-- ---------------------------------------------------------------------
-- PASO 4 · Nº de servicio: de los EQUIPOS a la ficha del PACIENTE
-- Muestra cuántos pacientes se migraron y cuáles hay que revisar a mano.
-- Los migrados quedan con servicio_origen = 'MIGRACION_EQUIPO' y
-- servicio_verificado = 0: EJESA no los ve hasta verificarlos en la ficha.
-- ---------------------------------------------------------------------
CALL _ed_migrar_servicio();


-- ---------------------------------------------------------------------
-- PASO 5 · Permisos y grupos (Myth Auth). No asigna grupos a usuarios.
-- auth_groups_permissions no tiene clave primaria en esta base: por eso
-- se usa NOT EXISTS (INSERT IGNORE duplicaría al correr dos veces).
-- ---------------------------------------------------------------------
INSERT INTO auth_permissions (name, description)
SELECT CONCAT(m.modulo, '.', a.accion), CONCAT(a.descr, ' - ', m.nombre)
FROM (
            SELECT 'gestion_paciente' AS modulo, 'Gestión Paciente' AS nombre
  UNION ALL SELECT 'prehospitalario',         'Prehospitalario'
  UNION ALL SELECT 'hospitalario',            'Hospitalario'
  UNION ALL SELECT 'laboratorio',             'Laboratorio'
  UNION ALL SELECT 'salud_mental',            'Salud Mental'
  UNION ALL SELECT 'servicios_transversales', 'Servicios Transversales'
  UNION ALL SELECT 'ambulatorio',             'Ambulatorio'
  UNION ALL SELECT 'electrodependientes',     'Electrodependientes'
) m
CROSS JOIN (
            SELECT 'ver' AS accion, 'Ver' AS descr
  UNION ALL SELECT 'crear',     'Crear'
  UNION ALL SELECT 'editar',    'Editar'
  UNION ALL SELECT 'eliminar',  'Eliminar / dar de baja'
  UNION ALL SELECT 'importar',  'Importar'
  UNION ALL SELECT 'exportar',  'Exportar'
  UNION ALL SELECT 'historial', 'Ver historial'
) a
WHERE NOT EXISTS (
  SELECT 1 FROM auth_permissions p WHERE p.name = CONCAT(m.modulo, '.', a.accion)
);

INSERT INTO auth_permissions (name, description)
SELECT x.name, x.descr FROM (
            SELECT 'electrodependientes.imagenes.ver' AS name, 'Ver imágenes de equipamiento' AS descr
  UNION ALL SELECT 'electrodependientes.imagenes.subir',            'Subir imágenes de equipamiento'
  UNION ALL SELECT 'electrodependientes.imagenes.eliminar',         'Dar de baja imágenes de equipamiento'
  UNION ALL SELECT 'electrodependientes.auditoria_ejesa.ver',       'Ver consultas realizadas por EJESA'
  UNION ALL SELECT 'electrodependientes.auditoria_ejesa.exportar',  'Exportar consultas realizadas por EJESA'
  UNION ALL SELECT 'ejesa.consultar',                               'EJESA - Consultar por número de servicio'
  UNION ALL SELECT 'ejesa.cortes.ver',                              'EJESA - Ver cortes programados'
  UNION ALL SELECT 'ejesa.cortes.crear',                            'EJESA - Crear cortes programados'
  UNION ALL SELECT 'ejesa.cortes.editar',                           'EJESA - Editar / publicar / reprogramar cortes'
  UNION ALL SELECT 'ejesa.cortes.cancelar',                         'EJESA - Cancelar cortes / baja de borradores'
  UNION ALL SELECT 'accion.contingencia.ver',                       'Acción - Ver plan de contingencia'
  UNION ALL SELECT 'accion.actuacion.registrar',                    'Acción - Registrar actuaciones'
  UNION ALL SELECT 'accion.incidencia.crear',                       'Acción - Abrir incidencia (corte imprevisto)'
  UNION ALL SELECT 'accion.paquete.descargar',                      'Acción - Descargar paquete de contingencia'
  UNION ALL SELECT 'accion.paquete.generar',                        'Acción - Generar paquete de contingencia'
  UNION ALL SELECT 'accion.plan.editar',                            'Acción - Editar datos del plan por paciente'
  UNION ALL SELECT 'sistema.accesos.administrar',                   'Administrar usuarios, grupos y permisos'
) x
WHERE NOT EXISTS (SELECT 1 FROM auth_permissions p WHERE p.name = x.name);

INSERT INTO auth_groups (name, description)
SELECT x.name, x.descr FROM (
            SELECT 'funcionario_alto_rango' AS name, 'Todos los módulos: ver, crear, editar. Nunca eliminar.' AS descr
  UNION ALL SELECT 'funcionario_prehosp',             'Prehospitalario completo (CRUD)'
  UNION ALL SELECT 'funcionario_electrodependientes', 'Electrodependientes completo (CRUD) + auditoría EJESA'
  UNION ALL SELECT 'administrativo_carga',            'Crear y consultar en módulos asignados. Nunca eliminar.'
  UNION ALL SELECT 'ejesa',                           'Espacio EJESA: consulta por servicio y cortes. Cuenta exclusiva.'
  UNION ALL SELECT 'grupo_accion',                    'Plan de contingencia y actuaciones'
) x
WHERE NOT EXISTS (SELECT 1 FROM auth_groups g WHERE g.name = x.name);

-- Alto rango: ver/crear/editar/importar/exportar/historial en todos los módulos. Sin eliminar.
INSERT INTO auth_groups_permissions (group_id, permission_id)
SELECT g.id, p.id
FROM auth_groups g
JOIN auth_permissions p
WHERE NOT EXISTS (SELECT 1 FROM auth_groups_permissions gp
                  WHERE gp.group_id = g.id AND gp.permission_id = p.id)
  AND g.name = 'funcionario_alto_rango'
  AND (
       p.name REGEXP '^(gestion_paciente|prehospitalario|hospitalario|laboratorio|salud_mental|servicios_transversales|ambulatorio|electrodependientes)\\.(ver|crear|editar|importar|exportar|historial)$'
    OR p.name IN ('electrodependientes.imagenes.ver', 'electrodependientes.imagenes.subir',
                  'electrodependientes.auditoria_ejesa.ver', 'ejesa.cortes.ver', 'ejesa.cortes.editar',
                  'accion.contingencia.ver', 'accion.paquete.descargar')
  );

INSERT INTO auth_groups_permissions (group_id, permission_id)
SELECT g.id, p.id
FROM auth_groups g
JOIN auth_permissions p
WHERE NOT EXISTS (SELECT 1 FROM auth_groups_permissions gp
                  WHERE gp.group_id = g.id AND gp.permission_id = p.id)
  AND g.name = 'funcionario_prehosp'
  AND p.name LIKE 'prehospitalario.%';

INSERT INTO auth_groups_permissions (group_id, permission_id)
SELECT g.id, p.id
FROM auth_groups g
JOIN auth_permissions p
WHERE NOT EXISTS (SELECT 1 FROM auth_groups_permissions gp
                  WHERE gp.group_id = g.id AND gp.permission_id = p.id)
  AND g.name = 'funcionario_electrodependientes'
  AND (
       p.name LIKE 'electrodependientes.%'
    OR p.name IN ('ejesa.cortes.ver', 'accion.contingencia.ver', 'accion.plan.editar',
                  'accion.paquete.generar', 'accion.paquete.descargar')
  );

INSERT INTO auth_groups_permissions (group_id, permission_id)
SELECT g.id, p.id
FROM auth_groups g
JOIN auth_permissions p
WHERE NOT EXISTS (SELECT 1 FROM auth_groups_permissions gp
                  WHERE gp.group_id = g.id AND gp.permission_id = p.id)
  AND g.name = 'ejesa'
  AND p.name IN ('ejesa.consultar', 'ejesa.cortes.ver', 'ejesa.cortes.crear',
                 'ejesa.cortes.editar', 'ejesa.cortes.cancelar');

INSERT INTO auth_groups_permissions (group_id, permission_id)
SELECT g.id, p.id
FROM auth_groups g
JOIN auth_permissions p
WHERE NOT EXISTS (SELECT 1 FROM auth_groups_permissions gp
                  WHERE gp.group_id = g.id AND gp.permission_id = p.id)
  AND g.name = 'grupo_accion'
  AND p.name IN ('accion.contingencia.ver', 'accion.actuacion.registrar',
                 'accion.incidencia.crear', 'accion.paquete.descargar');


-- ---------------------------------------------------------------------
-- Limpieza de procedimientos auxiliares
-- ---------------------------------------------------------------------
DROP PROCEDURE IF EXISTS _ed_agregar_columna;
DROP PROCEDURE IF EXISTS _ed_agregar_indice;
DROP PROCEDURE IF EXISTS _ed_agregar_fk;
DROP PROCEDURE IF EXISTS _ed_quitar_columna;
DROP PROCEDURE IF EXISTS _ed_migrar_servicio;


-- ---------------------------------------------------------------------
-- PASO 6 · Control final
-- ---------------------------------------------------------------------
SELECT
  (SELECT COUNT(*) FROM electrodependiente)                                              AS total_pacientes,
  (SELECT COUNT(*) FROM electrodependiente WHERE nro_servicio IS NOT NULL AND TRIM(nro_servicio) <> '') AS con_nro_servicio,
  (SELECT COUNT(*) FROM electrodependiente WHERE servicio_origen = 'MIGRACION_EQUIPO')   AS migrados_desde_equipo,
  (SELECT COUNT(*) FROM electrodependiente_equipamiento)                                 AS total_equipos;

-- Debe dar 17 filas, todas con existe = 1
SELECT t.tabla, (SELECT COUNT(*) FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = t.tabla) AS existe
FROM (
            SELECT 'electrodependiente' AS tabla
  UNION ALL SELECT 'equipamiento_imagen'
  UNION ALL SELECT 'ejesa_corte'
  UNION ALL SELECT 'ejesa_corte_servicio'
  UNION ALL SELECT 'ejesa_corte_afectado'
  UNION ALL SELECT 'ejesa_corte_historial'
  UNION ALL SELECT 'ejesa_consulta'
  UNION ALL SELECT 'ejesa_consulta_persona'
  UNION ALL SELECT 'contingencia_plan'
  UNION ALL SELECT 'contingencia_incidencia'
  UNION ALL SELECT 'contingencia_actuacion'
  UNION ALL SELECT 'contingencia_paquete'
  UNION ALL SELECT 'contingencia_paquete_entrega'
  UNION ALL SELECT 'contingencia_funcion'
  UNION ALL SELECT 'contingencia_destinatario'
  UNION ALL SELECT 'operacion_aviso'
  UNION ALL SELECT 'seguridad_evento'
) t;


-- ---------------------------------------------------------------------
-- PASO 7 · OPCIONAL, más adelante: borrar las columnas viejas del equipo
-- El código ya no lee ni escribe electrodependiente_equipamiento.nro_servicio
-- ni .titular_servicio, así que dejarlas no molesta. Borrarlas recién cuando
-- el paso 4 no liste pacientes para revisar y tengas respaldo. Para hacerlo,
-- sacale el "-- " a las dos líneas siguientes y corrélas aparte:
-- ---------------------------------------------------------------------
-- ALTER TABLE electrodependiente_equipamiento
--   DROP COLUMN nro_servicio, DROP COLUMN titular_servicio;

-- Fin scriptoelectrodp.sql
