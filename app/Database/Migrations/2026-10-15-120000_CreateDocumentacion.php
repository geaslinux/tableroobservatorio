<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Manual de usuario: una fila = una página que documenta una pantalla de un módulo para un rol.
 * Misma estructura que docs/sql/documentacion.sql (para cargarla a mano en phpMyAdmin).
 */
class CreateDocumentacion extends Migration
{
    protected $DBGroup = 'default';

    public function up()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `documentacion` (
              `documentacion_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `modulo`           VARCHAR(60)  NOT NULL COMMENT 'Clave del módulo: prehospitalario, paciente, internacion…',
              `modulo_nombre`    VARCHAR(100) NOT NULL COMMENT 'Nombre visible del módulo',
              `vista`            VARCHAR(80)  NOT NULL COMMENT 'Clave de la pantalla: base_list, movil_list…',
              `vista_nombre`     VARCHAR(120) NOT NULL COMMENT 'Nombre visible de la pantalla',
              `tipo_vista`       VARCHAR(20)  NOT NULL COMMENT 'listado | formulario | panel | importacion | reporte',
              `rol`              VARCHAR(20)  NOT NULL COMMENT 'administrativo | funcionario | ambos',
              `titulo`           VARCHAR(150) NOT NULL,
              `resumen`          VARCHAR(300) DEFAULT NULL COMMENT 'Una o dos líneas para el índice y el buscador',
              `descripcion`      MEDIUMTEXT   DEFAULT NULL COMMENT 'HTML con formato (editor tipo Word)',
              `validaciones`     TEXT         DEFAULT NULL COMMENT 'JSON: [{\"campo\":\"…\",\"regla\":\"…\",\"mensaje\":\"…\"}]',
              `imagen`           VARCHAR(255) DEFAULT NULL COMMENT 'Ruta relativa a public/: uploads/documentacion/archivo.png',
              `orden`            INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Orden dentro del módulo',
              `version`          VARCHAR(20)  DEFAULT NULL,
              `estado`           VARCHAR(15)  NOT NULL DEFAULT 'activo' COMMENT 'activo (publicada) | desactivado (borrador) | eliminado',
              `created_by`       INT UNSIGNED DEFAULT NULL COMMENT 'users.id',
              `updated_by`       INT UNSIGNED DEFAULT NULL COMMENT 'users.id',
              `created_at`       DATETIME     DEFAULT NULL,
              `updated_at`       DATETIME     DEFAULT NULL,
              PRIMARY KEY (`documentacion_id`),
              KEY `idx_documentacion_rol_estado` (`rol`, `estado`),
              KEY `idx_documentacion_modulo_vista` (`modulo`, `vista`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
              COMMENT='Manual de usuario: una página por pantalla y rol'
        ");
    }

    public function down()
    {
        $this->forge->dropTable('documentacion', true);
    }
}
