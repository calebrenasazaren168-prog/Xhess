-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-11-2025 a las 07:05:48
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `xhess`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `activaciones_cuenta`
--

CREATE TABLE `activaciones_cuenta` (
  `id` int(11) NOT NULL,
  `id_cuenta` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `fecha_expiracion` datetime NOT NULL,
  `usado` tinyint(1) DEFAULT 0,
  `tipo` enum('activacion','reset') DEFAULT 'activacion',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clubes`
--

CREATE TABLE `clubes` (
  `id_club` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `escuela` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clubes`
--

INSERT INTO `clubes` (`id_club`, `nombre`, `ciudad`, `escuela`, `created_at`, `updated_at`) VALUES
(1, 'Club CBTis168', 'Aguascalientes', 'CBTis168', '2025-11-26 04:52:50', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentas`
--

CREATE TABLE `cuentas` (
  `id_cuenta` int(11) NOT NULL,
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
  `verification_token` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cuentas`
--

INSERT INTO `cuentas` (`id_cuenta`, `usuario`, `contrasena`, `id_rol`, `correo`, `activo`, `fecha_creacion`, `ultimo_login`, `avatar_url`, `created_at`, `updated_at`, `verified`, `verification_token`) VALUES
(1, 'caleb', '$2y$10$pTOZVrnzZlmKFNseJgOo7.vMfrCqPEF1HowQ0h.mh.VJkHRkPfDvi', 1, 'caleb@gmail.com', 1, '2025-11-26 04:41:59', NULL, NULL, '2025-11-26 04:41:59', '2025-11-26 21:52:34', 0, NULL),
(2, 'yakko', '$2y$10$AKubGymomBiAkCXVtawvh.mcXKsSxlTGFCBVZY/mnUCnKtmBv.oJu', 3, 'yakko@gmail.com', 1, '2025-11-26 04:46:52', NULL, NULL, '2025-11-26 04:46:52', NULL, 0, NULL),
(3, 'wakko', '$2y$10$Nypj.hPc1g2ERZqC3tnwouOmaNslPKguq8uCw3dCCihvnWMfR/EFe', 3, 'wakko@gmail.com', 1, '2025-11-26 04:56:00', NULL, NULL, '2025-11-26 04:56:00', '2025-11-26 04:56:32', 0, NULL),
(4, 'dot', '$2y$10$A9A9mj6hQUAeRcksR/I9MOOCCBkx9Wuiy2udEYI6q./JI425hUafa', 3, 'dot@gmail.com', 1, '2025-11-26 04:57:42', NULL, NULL, '2025-11-26 04:57:42', NULL, 0, NULL),
(5, 'uniqua', '$2y$10$51LAFl8Xa0CBht1Ps1ukDeDA4vWsCCnpCaoT8w2zRWF3s0ZndP7um', 3, 'uniqua@gmail.com', 1, '2025-11-26 05:02:45', NULL, NULL, '2025-11-26 05:02:45', NULL, 0, NULL),
(6, 'tyron', '$2y$10$Jx/1swVyUOhRobUOcY9b0eL1g5BqX37ArGFo6XAOfp5KF4PozIUlO', 3, 'tyron@gmail.com', 1, '2025-11-26 05:05:00', NULL, NULL, '2025-11-26 05:05:00', NULL, 0, NULL),
(7, 'pablo', '$2y$10$kLeccG76mYFYVnB.sXbffOzJ7V8FCLB/7vpTq7i.Jy9NxqCX8cPrK', 3, 'pablo@gmail.com', 1, '2025-11-26 05:06:07', NULL, NULL, '2025-11-26 05:06:07', NULL, 0, NULL),
(8, 'bugs', '$2y$10$dMF1CF8AW0eeiunceRqyOOoLMu4Tcp.CG19bQImx.1aUb.6mBdprS', 3, 'bugs@gmail.com', 1, '2025-11-26 05:07:30', NULL, NULL, '2025-11-26 05:07:30', NULL, 0, NULL),
(9, 'porky', '$2y$10$wmFMsvkWiObQcUgXPFlpduTz888dRg0q2aiWuOE2oDFNk79CSNj3G', 3, 'porky@gmail.com', 1, '2025-11-26 05:09:09', NULL, NULL, '2025-11-26 05:09:09', NULL, 0, NULL),
(10, 'daffy', '$2y$10$9CPgPmXibfOB56.xHqJXDu1JxwzVgBjVoQ5CWahiDYQinIrD2PsBO', 3, 'daffy@gmail.com', 1, '2025-11-26 23:03:20', NULL, NULL, '2025-11-26 23:03:20', NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `emparejamientos`
--

CREATE TABLE `emparejamientos` (
  `id_emparejamiento` int(11) NOT NULL,
  `id_ronda` int(11) DEFAULT NULL,
  `equipo_blancas` int(11) DEFAULT NULL,
  `equipo_negras` int(11) DEFAULT NULL,
  `es_bye` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipos`
--

CREATE TABLE `equipos` (
  `id_equipo` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `id_club` int(11) DEFAULT NULL,
  `id_coach` int(11) DEFAULT NULL,
  `id_creador` int(11) NOT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `es_capitan` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `equipos`
--

INSERT INTO `equipos` (`id_equipo`, `nombre`, `id_club`, `id_coach`, `id_creador`, `avatar_url`, `created_at`, `updated_at`, `es_capitan`) VALUES
(3, 'Animaniacos', 1, 2, 2, NULL, '2025-11-26 04:54:55', '2025-11-26 21:34:48', 1),
(4, 'Backyardigans', 1, 5, 5, NULL, '2025-11-26 05:03:27', '2025-11-26 21:34:48', 1),
(5, 'Los Looney Tunes', 1, 9, 9, NULL, '2025-11-26 05:10:18', '2025-11-26 21:34:48', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estadisticas_equipo`
--

CREATE TABLE `estadisticas_equipo` (
  `id` int(11) NOT NULL,
  `id_equipo` int(11) DEFAULT NULL,
  `id_torneo` int(11) DEFAULT NULL,
  `partidas_ganadas` int(11) DEFAULT 0,
  `partidas_empatadas` int(11) DEFAULT 0,
  `partidas_perdidas` int(11) DEFAULT 0,
  `puntos_tableros` float DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estadisticas_jugador`
--

CREATE TABLE `estadisticas_jugador` (
  `id` int(11) NOT NULL,
  `id_jugador` int(11) DEFAULT NULL,
  `id_torneo` int(11) DEFAULT NULL,
  `id_equipo` int(11) DEFAULT NULL,
  `partidas_ganadas` int(11) DEFAULT 0,
  `partidas_empatadas` int(11) DEFAULT 0,
  `partidas_perdidas` int(11) DEFAULT 0,
  `puntos_totales` float DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jugadores`
--

CREATE TABLE `jugadores` (
  `id_jugador` int(11) NOT NULL,
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
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `jugadores`
--

INSERT INTO `jugadores` (`id_jugador`, `nombre`, `apellido`, `rating`, `id_equipo`, `id_cuenta`, `fecha_nacimiento`, `sexo`, `avatar_url`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'caleb', 'arenas', 1200, NULL, 1, NULL, NULL, NULL, 1, '2025-11-26 04:41:59', NULL),
(2, 'yakko', 'animaniac', 1200, 3, 2, NULL, NULL, NULL, 1, '2025-11-26 04:46:52', '2025-11-26 04:54:55'),
(3, 'wakko', 'animaniac', 1200, 3, 3, NULL, NULL, NULL, 1, '2025-11-26 04:56:00', '2025-11-26 04:56:43'),
(4, 'dot', 'animaniac', 1200, 3, 4, NULL, NULL, NULL, 1, '2025-11-26 04:57:42', '2025-11-26 04:58:10'),
(5, 'uniqua', 'butterfly', 1200, 4, 5, NULL, NULL, NULL, 1, '2025-11-26 05:02:45', '2025-11-26 05:03:27'),
(6, 'Tyron', 'moose', 1200, 4, 6, NULL, NULL, NULL, 1, '2025-11-26 05:05:00', '2025-11-26 05:05:08'),
(7, 'pablo', 'alfonso', 1200, 4, 7, NULL, NULL, NULL, 1, '2025-11-26 05:06:07', '2025-11-26 05:06:18'),
(8, 'Bugs', 'Bunny', 1200, 5, 8, NULL, NULL, NULL, 1, '2025-11-26 05:07:30', '2025-11-26 05:10:54'),
(9, 'porky', 'pig', 1200, 5, 9, NULL, NULL, NULL, 1, '2025-11-26 05:09:09', '2025-11-26 05:10:18'),
(10, 'Daffy', 'duck', 1200, 5, 10, NULL, NULL, NULL, 1, '2025-11-26 23:03:20', '2025-11-27 00:03:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partidas`
--

CREATE TABLE `partidas` (
  `id_partida` int(11) NOT NULL,
  `id_emparejamiento` int(11) DEFAULT NULL,
  `tablero` int(11) DEFAULT NULL,
  `id_jugador_blancas` int(11) DEFAULT NULL,
  `id_jugador_negras` int(11) DEFAULT NULL,
  `fecha_partida` datetime DEFAULT NULL,
  `resultado` enum('1-0','0-1','1/2','*') DEFAULT '*',
  `comentarios` text DEFAULT NULL,
  `pgn` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `puntuaciones_jugador_ronda`
--

CREATE TABLE `puntuaciones_jugador_ronda` (
  `id` int(11) NOT NULL,
  `id_jugador` int(11) NOT NULL,
  `id_ronda` int(11) NOT NULL,
  `id_torneo` int(11) NOT NULL,
  `puntuacion_anterior` decimal(5,2) DEFAULT 0.00,
  `puntuacion_actual` decimal(5,2) DEFAULT 0.00,
  `partidas_jugadas` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre`, `descripcion`) VALUES
(1, 'admin', 'Administrador del sistema'),
(2, 'coach', 'Entrenador / Capitan'),
(3, 'jugador', 'Jugador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rondas`
--

CREATE TABLE `rondas` (
  `id_ronda` int(11) NOT NULL,
  `id_torneo` int(11) DEFAULT NULL,
  `numero_ronda` int(11) DEFAULT NULL,
  `fecha_ronda` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `torneos`
--

CREATE TABLE `torneos` (
  `id_torneo` int(11) NOT NULL,
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
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `torneo_equipos`
--

CREATE TABLE `torneo_equipos` (
  `id_torneo` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `nro_jugadores_equipo` int(11) DEFAULT 4,
  `retirado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `torneo_jugadores`
--

CREATE TABLE `torneo_jugadores` (
  `id_torneo` int(11) NOT NULL,
  `id_jugador` int(11) NOT NULL,
  `id_equipo` int(11) DEFAULT NULL,
  `rating_inicial` int(11) DEFAULT 1200
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `activaciones_cuenta`
--
ALTER TABLE `activaciones_cuenta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_activ_cuenta` (`id_cuenta`);

--
-- Indices de la tabla `clubes`
--
ALTER TABLE `clubes`
  ADD PRIMARY KEY (`id_club`);

--
-- Indices de la tabla `cuentas`
--
ALTER TABLE `cuentas`
  ADD PRIMARY KEY (`id_cuenta`),
  ADD UNIQUE KEY `uk_usuario` (`usuario`),
  ADD UNIQUE KEY `uk_correo` (`correo`),
  ADD KEY `idx_cuentas_rol` (`id_rol`);

--
-- Indices de la tabla `emparejamientos`
--
ALTER TABLE `emparejamientos`
  ADD PRIMARY KEY (`id_emparejamiento`),
  ADD KEY `idx_emparejamientos_ronda` (`id_ronda`),
  ADD KEY `fk_emparejamientos_equipo_blancas` (`equipo_blancas`),
  ADD KEY `fk_emparejamientos_equipo_negras` (`equipo_negras`);

--
-- Indices de la tabla `equipos`
--
ALTER TABLE `equipos`
  ADD PRIMARY KEY (`id_equipo`),
  ADD KEY `idx_equipos_club` (`id_club`),
  ADD KEY `idx_equipos_coach` (`id_coach`),
  ADD KEY `idx_equipos_creador` (`id_creador`);

--
-- Indices de la tabla `estadisticas_equipo`
--
ALTER TABLE `estadisticas_equipo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_est_equipo` (`id_equipo`,`id_torneo`),
  ADD KEY `idx_est_equipo` (`id_torneo`,`id_equipo`);

--
-- Indices de la tabla `estadisticas_jugador`
--
ALTER TABLE `estadisticas_jugador`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_est_jug` (`id_jugador`,`id_torneo`),
  ADD KEY `idx_est_jug` (`id_torneo`,`id_jugador`);

--
-- Indices de la tabla `jugadores`
--
ALTER TABLE `jugadores`
  ADD PRIMARY KEY (`id_jugador`),
  ADD UNIQUE KEY `uk_jugadores_id_cuenta` (`id_cuenta`),
  ADD KEY `idx_jugadores_equipo` (`id_equipo`),
  ADD KEY `idx_jugadores_cuenta` (`id_cuenta`);

--
-- Indices de la tabla `partidas`
--
ALTER TABLE `partidas`
  ADD PRIMARY KEY (`id_partida`),
  ADD KEY `idx_partidas_emparejamiento` (`id_emparejamiento`),
  ADD KEY `idx_partidas_jugadores` (`id_jugador_blancas`,`id_jugador_negras`),
  ADD KEY `idx_partidas_tablero` (`tablero`),
  ADD KEY `fk_partidas_negras` (`id_jugador_negras`);

--
-- Indices de la tabla `puntuaciones_jugador_ronda`
--
ALTER TABLE `puntuaciones_jugador_ronda`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_jugador_ronda` (`id_jugador`,`id_ronda`),
  ADD KEY `id_ronda` (`id_ronda`),
  ADD KEY `id_torneo` (`id_torneo`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `rondas`
--
ALTER TABLE `rondas`
  ADD PRIMARY KEY (`id_ronda`),
  ADD UNIQUE KEY `uk_rondas_torneo_num` (`id_torneo`,`numero_ronda`),
  ADD KEY `idx_rondas_torneo` (`id_torneo`);

--
-- Indices de la tabla `torneos`
--
ALTER TABLE `torneos`
  ADD PRIMARY KEY (`id_torneo`),
  ADD UNIQUE KEY `uk_torneos_nombre` (`nombre`),
  ADD KEY `fk_torneos_cuenta` (`id_cuenta`),
  ADD KEY `fk_torneos_club` (`id_club`);

--
-- Indices de la tabla `torneo_equipos`
--
ALTER TABLE `torneo_equipos`
  ADD PRIMARY KEY (`id_torneo`,`id_equipo`),
  ADD KEY `idx_te_torneo` (`id_torneo`),
  ADD KEY `idx_te_equipo` (`id_equipo`);

--
-- Indices de la tabla `torneo_jugadores`
--
ALTER TABLE `torneo_jugadores`
  ADD PRIMARY KEY (`id_torneo`,`id_jugador`),
  ADD KEY `idx_tj_torneo` (`id_torneo`),
  ADD KEY `idx_tj_jugador` (`id_jugador`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `activaciones_cuenta`
--
ALTER TABLE `activaciones_cuenta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clubes`
--
ALTER TABLE `clubes`
  MODIFY `id_club` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `cuentas`
--
ALTER TABLE `cuentas`
  MODIFY `id_cuenta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `emparejamientos`
--
ALTER TABLE `emparejamientos`
  MODIFY `id_emparejamiento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `equipos`
--
ALTER TABLE `equipos`
  MODIFY `id_equipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `estadisticas_equipo`
--
ALTER TABLE `estadisticas_equipo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estadisticas_jugador`
--
ALTER TABLE `estadisticas_jugador`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `jugadores`
--
ALTER TABLE `jugadores`
  MODIFY `id_jugador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `partidas`
--
ALTER TABLE `partidas`
  MODIFY `id_partida` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `puntuaciones_jugador_ronda`
--
ALTER TABLE `puntuaciones_jugador_ronda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `rondas`
--
ALTER TABLE `rondas`
  MODIFY `id_ronda` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `torneos`
--
ALTER TABLE `torneos`
  MODIFY `id_torneo` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `activaciones_cuenta`
--
ALTER TABLE `activaciones_cuenta`
  ADD CONSTRAINT `fk_activ_cuenta` FOREIGN KEY (`id_cuenta`) REFERENCES `cuentas` (`id_cuenta`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `cuentas`
--
ALTER TABLE `cuentas`
  ADD CONSTRAINT `fk_cuentas_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `emparejamientos`
--
ALTER TABLE `emparejamientos`
  ADD CONSTRAINT `fk_emparejamientos_equipo_blancas` FOREIGN KEY (`equipo_blancas`) REFERENCES `equipos` (`id_equipo`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_emparejamientos_equipo_negras` FOREIGN KEY (`equipo_negras`) REFERENCES `equipos` (`id_equipo`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_emparejamientos_ronda` FOREIGN KEY (`id_ronda`) REFERENCES `rondas` (`id_ronda`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `equipos`
--
ALTER TABLE `equipos`
  ADD CONSTRAINT `fk_equipos_club` FOREIGN KEY (`id_club`) REFERENCES `clubes` (`id_club`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_equipos_coach` FOREIGN KEY (`id_coach`) REFERENCES `cuentas` (`id_cuenta`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_equipos_creador` FOREIGN KEY (`id_creador`) REFERENCES `cuentas` (`id_cuenta`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `estadisticas_equipo`
--
ALTER TABLE `estadisticas_equipo`
  ADD CONSTRAINT `fk_est_eq_equipo` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_est_eq_torneo` FOREIGN KEY (`id_torneo`) REFERENCES `torneos` (`id_torneo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `estadisticas_jugador`
--
ALTER TABLE `estadisticas_jugador`
  ADD CONSTRAINT `fk_est_jug_jugador` FOREIGN KEY (`id_jugador`) REFERENCES `jugadores` (`id_jugador`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_est_jug_torneo` FOREIGN KEY (`id_torneo`) REFERENCES `torneos` (`id_torneo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `jugadores`
--
ALTER TABLE `jugadores`
  ADD CONSTRAINT `fk_jugadores_equipo` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `partidas`
--
ALTER TABLE `partidas`
  ADD CONSTRAINT `fk_partidas_blancas` FOREIGN KEY (`id_jugador_blancas`) REFERENCES `jugadores` (`id_jugador`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_partidas_emparejamiento` FOREIGN KEY (`id_emparejamiento`) REFERENCES `emparejamientos` (`id_emparejamiento`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_partidas_negras` FOREIGN KEY (`id_jugador_negras`) REFERENCES `jugadores` (`id_jugador`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `puntuaciones_jugador_ronda`
--
ALTER TABLE `puntuaciones_jugador_ronda`
  ADD CONSTRAINT `puntuaciones_jugador_ronda_ibfk_1` FOREIGN KEY (`id_jugador`) REFERENCES `jugadores` (`id_jugador`),
  ADD CONSTRAINT `puntuaciones_jugador_ronda_ibfk_2` FOREIGN KEY (`id_ronda`) REFERENCES `rondas` (`id_ronda`),
  ADD CONSTRAINT `puntuaciones_jugador_ronda_ibfk_3` FOREIGN KEY (`id_torneo`) REFERENCES `torneos` (`id_torneo`);

--
-- Filtros para la tabla `rondas`
--
ALTER TABLE `rondas`
  ADD CONSTRAINT `fk_rondas_torneo` FOREIGN KEY (`id_torneo`) REFERENCES `torneos` (`id_torneo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `torneos`
--
ALTER TABLE `torneos`
  ADD CONSTRAINT `fk_torneos_club` FOREIGN KEY (`id_club`) REFERENCES `clubes` (`id_club`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_torneos_cuenta` FOREIGN KEY (`id_cuenta`) REFERENCES `cuentas` (`id_cuenta`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `torneo_equipos`
--
ALTER TABLE `torneo_equipos`
  ADD CONSTRAINT `fk_te_equipo` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_te_torneo` FOREIGN KEY (`id_torneo`) REFERENCES `torneos` (`id_torneo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `torneo_jugadores`
--
ALTER TABLE `torneo_jugadores`
  ADD CONSTRAINT `fk_tj_jugador` FOREIGN KEY (`id_jugador`) REFERENCES `jugadores` (`id_jugador`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tj_torneo` FOREIGN KEY (`id_torneo`) REFERENCES `torneos` (`id_torneo`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
