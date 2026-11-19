-- ============================================================
-- XHESS - Esquema de base de datos (solo estructura, sin datos)
-- MariaDB 10.x / 11.x  ·  utf8mb4
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- roles
-- ------------------------------------------------------------
CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- clubes
-- ------------------------------------------------------------
CREATE TABLE `clubes` (
  `id_club` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `escuela` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_club`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- cuentas
-- ------------------------------------------------------------
CREATE TABLE `cuentas` (
  `id_cuenta` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(80) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `id_rol` int(11) NOT NULL DEFAULT 3,
  `correo` varchar(150) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `ultimo_login` datetime DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(100) DEFAULT NULL,
  `email_verificado` tinyint(1) NOT NULL DEFAULT 0,
  `token_verificacion` varchar(64) DEFAULT NULL,
  `token_expiracion` datetime DEFAULT NULL,
  PRIMARY KEY (`id_cuenta`),
  UNIQUE KEY `uk_usuario` (`usuario`),
  UNIQUE KEY `uk_correo` (`correo`),
  KEY `idx_cuentas_rol` (`id_rol`),
  CONSTRAINT `fk_cuentas_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- activaciones_cuenta
-- ------------------------------------------------------------
CREATE TABLE `activaciones_cuenta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cuenta` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `fecha_expiracion` datetime NOT NULL,
  `usado` tinyint(1) DEFAULT 0,
  `tipo` enum('activacion','reset') DEFAULT 'activacion',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_activ_cuenta` (`id_cuenta`),
  CONSTRAINT `fk_activ_cuenta` FOREIGN KEY (`id_cuenta`) REFERENCES `cuentas` (`id_cuenta`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- equipos
-- ------------------------------------------------------------
CREATE TABLE `equipos` (
  `id_equipo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `id_club` int(11) DEFAULT NULL,
  `id_coach` int(11) DEFAULT NULL,
  `id_creador` int(11) NOT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `es_capitan` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id_equipo`),
  KEY `idx_equipos_club` (`id_club`),
  KEY `idx_equipos_coach` (`id_coach`),
  KEY `idx_equipos_creador` (`id_creador`),
  CONSTRAINT `fk_equipos_club` FOREIGN KEY (`id_club`) REFERENCES `clubes` (`id_club`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_equipos_coach` FOREIGN KEY (`id_coach`) REFERENCES `cuentas` (`id_cuenta`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_equipos_creador` FOREIGN KEY (`id_creador`) REFERENCES `cuentas` (`id_cuenta`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- jugadores
-- ------------------------------------------------------------
CREATE TABLE `jugadores` (
  `id_jugador` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `rating` int(11) DEFAULT 1200,
  `id_equipo` int(11) DEFAULT NULL,
  `id_cuenta` int(11) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `sexo` enum('M','F','O') DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_jugador`),
  UNIQUE KEY `uk_jugadores_id_cuenta` (`id_cuenta`),
  KEY `idx_jugadores_equipo` (`id_equipo`),
  KEY `idx_jugadores_cuenta` (`id_cuenta`),
  CONSTRAINT `fk_jugadores_equipo` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- torneos
-- ------------------------------------------------------------
CREATE TABLE `torneos` (
  `id_torneo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` enum('individual','equipos') DEFAULT 'equipos',
  `sistema` enum('round_robin','suizo','eliminacion','round_robin_ponderado') DEFAULT 'round_robin',
  `condicion` enum('pendiente','en_progreso','finalizado') DEFAULT 'pendiente',
  `nro_tableros` int(11) DEFAULT 4,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `pais` varchar(100) DEFAULT 'México',
  `id_cuenta` int(11) DEFAULT NULL,
  `ritmo_tiempo` varchar(50) DEFAULT NULL,
  `id_club` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_torneo`),
  UNIQUE KEY `uk_torneos_nombre` (`nombre`),
  KEY `fk_torneos_cuenta` (`id_cuenta`),
  KEY `fk_torneos_club` (`id_club`),
  CONSTRAINT `fk_torneos_club` FOREIGN KEY (`id_club`) REFERENCES `clubes` (`id_club`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_torneos_cuenta` FOREIGN KEY (`id_cuenta`) REFERENCES `cuentas` (`id_cuenta`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- torneo_equipos
-- ------------------------------------------------------------
CREATE TABLE `torneo_equipos` (
  `id_torneo` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `nro_jugadores_equipo` int(11) DEFAULT 4,
  `retirado` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id_torneo`,`id_equipo`),
  KEY `idx_te_torneo` (`id_torneo`),
  KEY `idx_te_equipo` (`id_equipo`),
  CONSTRAINT `fk_te_equipo` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_te_torneo` FOREIGN KEY (`id_torneo`) REFERENCES `torneos` (`id_torneo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- rondas
-- ------------------------------------------------------------
CREATE TABLE `rondas` (
  `id_ronda` int(11) NOT NULL AUTO_INCREMENT,
  `id_torneo` int(11) DEFAULT NULL,
  `numero_ronda` int(11) DEFAULT NULL,
  `fecha_ronda` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_ronda`),
  UNIQUE KEY `uk_rondas_torneo_num` (`id_torneo`,`numero_ronda`),
  KEY `idx_rondas_torneo` (`id_torneo`),
  CONSTRAINT `fk_rondas_torneo` FOREIGN KEY (`id_torneo`) REFERENCES `torneos` (`id_torneo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- emparejamientos
-- ------------------------------------------------------------
CREATE TABLE `emparejamientos` (
  `id_emparejamiento` int(11) NOT NULL AUTO_INCREMENT,
  `id_ronda` int(11) DEFAULT NULL,
  `equipo_blancas` int(11) DEFAULT NULL,
  `equipo_negras` int(11) DEFAULT NULL,
  `es_bye` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_emparejamiento`),
  KEY `idx_emparejamientos_ronda` (`id_ronda`),
  KEY `fk_emparejamientos_equipo_blancas` (`equipo_blancas`),
  KEY `fk_emparejamientos_equipo_negras` (`equipo_negras`),
  CONSTRAINT `fk_emparejamientos_equipo_blancas` FOREIGN KEY (`equipo_blancas`) REFERENCES `equipos` (`id_equipo`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_emparejamientos_equipo_negras` FOREIGN KEY (`equipo_negras`) REFERENCES `equipos` (`id_equipo`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_emparejamientos_ronda` FOREIGN KEY (`id_ronda`) REFERENCES `rondas` (`id_ronda`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- partidas
-- ------------------------------------------------------------
CREATE TABLE `partidas` (
  `id_partida` int(11) NOT NULL AUTO_INCREMENT,
  `id_emparejamiento` int(11) DEFAULT NULL,
  `tablero` int(11) DEFAULT NULL,
  `id_jugador_blancas` int(11) DEFAULT NULL,
  `id_jugador_negras` int(11) DEFAULT NULL,
  `fecha_partida` datetime DEFAULT NULL,
  `resultado` enum('1-0','0-1','1/2','*') DEFAULT '*',
  `comentarios` text DEFAULT NULL,
  `pgn` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_partida`),
  KEY `idx_partidas_emparejamiento` (`id_emparejamiento`),
  KEY `idx_partidas_jugadores` (`id_jugador_blancas`,`id_jugador_negras`),
  KEY `idx_partidas_tablero` (`tablero`),
  KEY `fk_partidas_negras` (`id_jugador_negras`),
  CONSTRAINT `fk_partidas_blancas` FOREIGN KEY (`id_jugador_blancas`) REFERENCES `jugadores` (`id_jugador`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_partidas_emparejamiento` FOREIGN KEY (`id_emparejamiento`) REFERENCES `emparejamientos` (`id_emparejamiento`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_partidas_negras` FOREIGN KEY (`id_jugador_negras`) REFERENCES `jugadores` (`id_jugador`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- estadisticas_equipo
-- ------------------------------------------------------------
CREATE TABLE `estadisticas_equipo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_equipo` int(11) DEFAULT NULL,
  `id_torneo` int(11) DEFAULT NULL,
  `partidas_ganadas` int(11) DEFAULT 0,
  `partidas_empatadas` int(11) DEFAULT 0,
  `partidas_perdidas` int(11) DEFAULT 0,
  `puntos_tableros` float DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_est_equipo` (`id_equipo`,`id_torneo`),
  KEY `idx_est_equipo` (`id_torneo`,`id_equipo`),
  CONSTRAINT `fk_est_eq_equipo` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_est_eq_torneo` FOREIGN KEY (`id_torneo`) REFERENCES `torneos` (`id_torneo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- estadisticas_jugador
-- ------------------------------------------------------------
CREATE TABLE `estadisticas_jugador` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_jugador` int(11) DEFAULT NULL,
  `id_torneo` int(11) DEFAULT NULL,
  `id_equipo` int(11) DEFAULT NULL,
  `partidas_ganadas` int(11) DEFAULT 0,
  `partidas_empatadas` int(11) DEFAULT 0,
  `partidas_perdidas` int(11) DEFAULT 0,
  `puntos_totales` float DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_est_jug` (`id_jugador`,`id_torneo`),
  KEY `idx_est_jug` (`id_torneo`,`id_jugador`),
  CONSTRAINT `fk_est_jug_jugador` FOREIGN KEY (`id_jugador`) REFERENCES `jugadores` (`id_jugador`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_est_jug_torneo` FOREIGN KEY (`id_torneo`) REFERENCES `torneos` (`id_torneo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- puntuaciones_jugador_ronda
-- ------------------------------------------------------------
CREATE TABLE `puntuaciones_jugador_ronda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_jugador` int(11) NOT NULL,
  `id_ronda` int(11) NOT NULL,
  `id_torneo` int(11) NOT NULL,
  `puntuacion_anterior` decimal(5,2) DEFAULT 0.00,
  `puntuacion_actual` decimal(5,2) DEFAULT 0.00,
  `partidas_jugadas` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_jugador_ronda` (`id_jugador`,`id_ronda`),
  KEY `id_ronda` (`id_ronda`),
  KEY `id_torneo` (`id_torneo`),
  CONSTRAINT `puntuaciones_jugador_ronda_ibfk_1` FOREIGN KEY (`id_jugador`) REFERENCES `jugadores` (`id_jugador`),
  CONSTRAINT `puntuaciones_jugador_ronda_ibfk_2` FOREIGN KEY (`id_ronda`) REFERENCES `rondas` (`id_ronda`),
  CONSTRAINT `puntuaciones_jugador_ronda_ibfk_3` FOREIGN KEY (`id_torneo`) REFERENCES `torneos` (`id_torneo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- solicitudes_equipo
-- ------------------------------------------------------------
CREATE TABLE `solicitudes_equipo` (
  `id_solicitud` int(11) NOT NULL AUTO_INCREMENT,
  `id_jugador` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `estado` enum('pendiente','aceptada','rechazada') DEFAULT 'pendiente',
  `mensaje` varchar(255) DEFAULT NULL,
  `fecha_solicitud` datetime DEFAULT current_timestamp(),
  `fecha_respuesta` datetime DEFAULT NULL,
  PRIMARY KEY (`id_solicitud`),
  UNIQUE KEY `uk_solicitud_jugador_equipo` (`id_jugador`,`id_equipo`,`estado`),
  KEY `idx_sol_equipo` (`id_equipo`),
  KEY `idx_sol_jugador` (`id_jugador`),
  CONSTRAINT `fk_sol_equipo` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sol_jugador` FOREIGN KEY (`id_jugador`) REFERENCES `jugadores` (`id_jugador`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- solicitudes_torneo
-- ------------------------------------------------------------
CREATE TABLE `solicitudes_torneo` (
  `id_solicitud` int(11) NOT NULL AUTO_INCREMENT,
  `id_torneo` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `id_cuenta` int(11) NOT NULL,
  `estado` enum('pendiente','aceptada','rechazada') DEFAULT 'pendiente',
  `mensaje` varchar(255) DEFAULT NULL,
  `fecha_solicitud` datetime DEFAULT current_timestamp(),
  `fecha_respuesta` datetime DEFAULT NULL,
  PRIMARY KEY (`id_solicitud`),
  UNIQUE KEY `uk_solicitud_torneo_equipo` (`id_torneo`,`id_equipo`,`estado`),
  KEY `idx_st_torneo` (`id_torneo`),
  KEY `idx_st_equipo` (`id_equipo`),
  KEY `idx_st_cuenta` (`id_cuenta`),
  CONSTRAINT `fk_st_cuenta` FOREIGN KEY (`id_cuenta`) REFERENCES `cuentas` (`id_cuenta`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_st_equipo` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_st_torneo` FOREIGN KEY (`id_torneo`) REFERENCES `torneos` (`id_torneo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;
