-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: rep
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `especialidad_consulta`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `especialidad_consulta` (
  `especialidad_id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`especialidad_id`),
  UNIQUE KEY `uk_especialidad_nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `efector_servicio_consulta`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `efector_servicio_consulta` (
  `efector_servicio_id` int NOT NULL AUTO_INCREMENT,
  `efector_id` int NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `orden` smallint NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`efector_servicio_id`),
  UNIQUE KEY `uk_efector_servicio` (`efector_id`,`nombre`),
  KEY `idx_efector_servicio_activo` (`efector_id`,`activo`,`orden`),
  CONSTRAINT `fk_consulta_efector_servicio_efector` FOREIGN KEY (`efector_id`) REFERENCES `efector` (`efector_id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `profesional_consulta`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `profesional_consulta` (
  `profesional_id` int NOT NULL AUTO_INCREMENT,
  `dni` varchar(20) DEFAULT NULL,
  `nombre_completo` varchar(200) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `matricula` varchar(30) DEFAULT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `origen` enum('configurado','importado','manual') NOT NULL DEFAULT 'configurado',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`profesional_id`),
  UNIQUE KEY `uk_profesional_dni` (`dni`),
  UNIQUE KEY `uk_profesional_user` (`user_id`),
  CONSTRAINT `fk_consulta_profesional_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=786 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paciente_consulta`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `paciente_consulta` (
  `paciente_id` int NOT NULL AUTO_INCREMENT,
  `dni` varchar(20) DEFAULT NULL,
  `documento_tipo` enum('dni','sin_documento','extranjero','historia_clinica') NOT NULL DEFAULT 'dni',
  `documento_numero` varchar(30) DEFAULT NULL,
  `nombre_completo` varchar(200) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `localidad_id` int DEFAULT NULL,
  `localidad_otra` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`paciente_id`),
  UNIQUE KEY `uk_paciente_dni` (`dni`),
  KEY `idx_paciente_apellido` (`apellido`,`nombre`),
  KEY `idx_paciente_localidad` (`localidad_id`),
  KEY `idx_paciente_documento` (`documento_tipo`,`documento_numero`),
  CONSTRAINT `fk_consulta_paciente_localidad` FOREIGN KEY (`localidad_id`) REFERENCES `localidad` (`localidad_id`)
) ENGINE=InnoDB AUTO_INCREMENT=736 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `formulario_consulta`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `formulario_consulta` (
  `formulario_id` int NOT NULL AUTO_INCREMENT,
  `efector_id` int NOT NULL,
  `especialidad_id` int NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `modo_profesional` enum('libre','lista') NOT NULL DEFAULT 'libre',
  `usa_localidad` tinyint(1) NOT NULL DEFAULT '1',
  `usa_indicaciones` tinyint(1) NOT NULL DEFAULT '1',
  `usa_procedimiento` tinyint(1) NOT NULL DEFAULT '1',
  `usa_interconsulta` tinyint(1) NOT NULL DEFAULT '1',
  `permite_servicio_otro` tinyint(1) NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`formulario_id`),
  UNIQUE KEY `uk_formulario_efector_especialidad` (`efector_id`,`especialidad_id`),
  KEY `idx_formulario_especialidad` (`especialidad_id`),
  CONSTRAINT `fk_consulta_formulario_efector` FOREIGN KEY (`efector_id`) REFERENCES `efector` (`efector_id`),
  CONSTRAINT `fk_consulta_formulario_especialidad` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidad_consulta` (`especialidad_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `formulario_profesional_consulta`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `formulario_profesional_consulta` (
  `formulario_id` int NOT NULL,
  `profesional_id` int NOT NULL,
  `etiqueta` varchar(200) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`formulario_id`,`profesional_id`),
  KEY `idx_fp_profesional` (`profesional_id`),
  CONSTRAINT `fk_consulta_fp_formulario` FOREIGN KEY (`formulario_id`) REFERENCES `formulario_consulta` (`formulario_id`),
  CONSTRAINT `fk_consulta_fp_profesional` FOREIGN KEY (`profesional_id`) REFERENCES `profesional_consulta` (`profesional_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `formulario_campo_consulta`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `formulario_campo_consulta` (
  `formulario_id` int NOT NULL,
  `campo` enum('localidad','indicaciones','observaciones','procedimiento','hospital_origen','servicio_origen','otros_interconsulta') NOT NULL,
  `modo` enum('oculto','opcional','obligatorio') NOT NULL DEFAULT 'oculto',
  PRIMARY KEY (`formulario_id`,`campo`),
  CONSTRAINT `fk_consulta_fc_formulario` FOREIGN KEY (`formulario_id`) REFERENCES `formulario_consulta` (`formulario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `grupo_formulario_consulta`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grupo_formulario_consulta` (
  `group_id` int unsigned NOT NULL,
  `formulario_id` int NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`group_id`,`formulario_id`),
  KEY `idx_gfc_formulario` (`formulario_id`),
  CONSTRAINT `fk_consulta_gfc_formulario` FOREIGN KEY (`formulario_id`) REFERENCES `formulario_consulta` (`formulario_id`),
  CONSTRAINT `fk_consulta_gfc_grupo` FOREIGN KEY (`group_id`) REFERENCES `auth_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `importacion_consulta`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `importacion_consulta` (
  `importacion_id` int NOT NULL AUTO_INCREMENT,
  `formulario_id` int NOT NULL,
  `archivo_nombre` varchar(255) NOT NULL,
  `archivo_hash` char(64) NOT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `filas_total` int NOT NULL DEFAULT '0',
  `filas_insertadas` int NOT NULL DEFAULT '0',
  `filas_omitidas` int NOT NULL DEFAULT '0',
  `detalle` json DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`importacion_id`),
  KEY `idx_importacion_formulario` (`formulario_id`,`created_at`),
  KEY `idx_importacion_hash` (`archivo_hash`),
  KEY `fk_consulta_imp_user` (`user_id`),
  CONSTRAINT `fk_consulta_imp_formulario` FOREIGN KEY (`formulario_id`) REFERENCES `formulario_consulta` (`formulario_id`),
  CONSTRAINT `fk_consulta_imp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `atencion_consulta`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `atencion_consulta` (
  `atencion_id` int NOT NULL AUTO_INCREMENT,
  `formulario_id` int NOT NULL,
  `profesional_id` int NOT NULL,
  `profesional_nombre_completo` varchar(200) NOT NULL,
  `profesional_dni` varchar(20) DEFAULT NULL,
  `paciente_id` int NOT NULL,
  `paciente_nombre_completo` varchar(200) NOT NULL,
  `paciente_dni` varchar(20) DEFAULT NULL,
  `paciente_documento_tipo` enum('dni','sin_documento','extranjero','historia_clinica') NOT NULL DEFAULT 'dni',
  `paciente_documento_texto` varchar(50) DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `diagnostico` text NOT NULL,
  `indicaciones` text,
  `observaciones` text,
  `localidad_id` int DEFAULT NULL,
  `localidad_otra` varchar(150) DEFAULT NULL,
  `procedimiento` varchar(255) DEFAULT NULL,
  `fecha_procedimiento` date DEFAULT NULL,
  `hora_procedimiento` time DEFAULT NULL,
  `hospital_origen_texto` varchar(200) DEFAULT NULL,
  `efector_origen_id` int DEFAULT NULL,
  `efector_origen_otro` varchar(150) DEFAULT NULL,
  `efector_servicio_id` int DEFAULT NULL,
  `servicio_origen_texto` varchar(200) DEFAULT NULL,
  `servicio_es_otro` tinyint(1) NOT NULL DEFAULT '0',
  `servicio_origen_otro` varchar(150) DEFAULT NULL,
  `otros_interconsulta` text,
  `origen` enum('manual','importacion') NOT NULL DEFAULT 'manual',
  `importacion_id` int DEFAULT NULL,
  `fila_excel` int DEFAULT NULL,
  `marca_temporal` datetime DEFAULT NULL,
  `datos_originales` json DEFAULT NULL,
  `correcciones` json DEFAULT NULL,
  `created_by` int unsigned DEFAULT NULL,
  `updated_by` int unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`atencion_id`),
  KEY `idx_atencion_formulario_fecha` (`formulario_id`,`fecha`),
  KEY `idx_atencion_paciente` (`paciente_id`,`fecha`),
  KEY `idx_atencion_profesional` (`profesional_id`,`fecha`),
  KEY `idx_atencion_efector_origen` (`efector_origen_id`),
  KEY `idx_atencion_servicio` (`efector_servicio_id`),
  KEY `idx_atencion_created_by` (`created_by`),
  KEY `idx_atencion_localidad` (`localidad_id`),
  KEY `idx_atencion_importacion` (`importacion_id`),
  KEY `idx_atencion_marca` (`formulario_id`,`marca_temporal`),
  KEY `fk_consulta_atencion_updated_by` (`updated_by`),
  CONSTRAINT `fk_consulta_atencion_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_consulta_atencion_efector_origen` FOREIGN KEY (`efector_origen_id`) REFERENCES `efector` (`efector_id`),
  CONSTRAINT `fk_consulta_atencion_formulario` FOREIGN KEY (`formulario_id`) REFERENCES `formulario_consulta` (`formulario_id`),
  CONSTRAINT `fk_consulta_atencion_importacion` FOREIGN KEY (`importacion_id`) REFERENCES `importacion_consulta` (`importacion_id`),
  CONSTRAINT `fk_consulta_atencion_localidad` FOREIGN KEY (`localidad_id`) REFERENCES `localidad` (`localidad_id`),
  CONSTRAINT `fk_consulta_atencion_paciente` FOREIGN KEY (`paciente_id`) REFERENCES `paciente_consulta` (`paciente_id`),
  CONSTRAINT `fk_consulta_atencion_profesional` FOREIGN KEY (`profesional_id`) REFERENCES `profesional_consulta` (`profesional_id`),
  CONSTRAINT `fk_consulta_atencion_servicio` FOREIGN KEY (`efector_servicio_id`) REFERENCES `efector_servicio_consulta` (`efector_servicio_id`),
  CONSTRAINT `fk_consulta_atencion_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=729 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-26  9:56:22
