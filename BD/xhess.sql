-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Servidor: sql210.infinityfree.com
-- Tiempo de generación: 05-04-2026 a las 20:21:53
-- Versión del servidor: 11.4.10-MariaDB
-- Versión de PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `if0_41208215_xhess`
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
  `verification_token` varchar(100) DEFAULT NULL,
  `email_verificado` tinyint(1) NOT NULL DEFAULT 0,
  `token_verificacion` varchar(64) DEFAULT NULL,
  `token_expiracion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cuentas`
--

INSERT INTO `cuentas` (`id_cuenta`, `usuario`, `contrasena`, `id_rol`, `correo`, `activo`, `fecha_creacion`, `ultimo_login`, `avatar_url`, `created_at`, `updated_at`, `verified`, `verification_token`, `email_verificado`, `token_verificacion`, `token_expiracion`) VALUES
(36, '4x3l_MT', '$2y$10$.kBsAsbrZlbnTMsXxoP74ucdtVbXjnFNPigIn5moeyR4JI3vBX586', 3, 'axelalcala45@gmail.com', 1, '2026-03-31 17:41:46', NULL, NULL, '2026-03-31 17:41:46', '2026-03-31 17:42:00', 0, NULL, 1, NULL, NULL);

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

--
-- Volcado de datos para la tabla `emparejamientos`
--

INSERT INTO `emparejamientos` (`id_emparejamiento`, `id_ronda`, `equipo_blancas`, `equipo_negras`, `es_bye`, `created_at`, `updated_at`) VALUES
(4, 4, NULL, NULL, 0, '2026-03-12 11:22:52', NULL),
(5, 4, NULL, NULL, 0, '2026-03-12 11:22:52', NULL),
(6, 5, NULL, NULL, 0, '2026-03-12 11:26:35', NULL),
(7, 5, NULL, NULL, 0, '2026-03-12 11:26:35', NULL),
(8, 6, NULL, NULL, 0, '2026-03-12 11:28:35', NULL),
(9, 6, NULL, NULL, 0, '2026-03-12 11:28:35', NULL),
(10, 7, NULL, NULL, 0, '2026-03-12 11:39:35', NULL),
(11, 7, NULL, NULL, 0, '2026-03-12 11:39:35', NULL),
(12, 8, NULL, NULL, 0, '2026-03-12 12:28:58', NULL),
(13, 8, NULL, NULL, 0, '2026-03-12 12:28:58', NULL),
(14, 9, NULL, NULL, 0, '2026-03-12 12:31:00', NULL),
(15, 9, NULL, NULL, 0, '2026-03-12 12:31:00', NULL);

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
(14, 'prueba', 1, 36, 36, NULL, '2026-04-05 16:49:08', NULL, 0);

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
(14, 'Axel', 'Alcala', 1200, NULL, 14, NULL, NULL, NULL, 1, '2026-03-12 07:50:19', NULL),
(15, 'Pablo', 'backyardigan', 1213, NULL, 15, NULL, NULL, NULL, 1, '2026-03-12 08:54:07', '2026-03-12 12:31:38'),
(16, 'tiron', 'backyardigan', 1169, NULL, 16, NULL, NULL, NULL, 1, '2026-03-12 08:58:09', '2026-03-12 12:31:38'),
(17, 'yuniqua', 'backyardigan', 1183, NULL, 17, NULL, NULL, NULL, 1, '2026-03-12 08:59:29', '2026-03-12 12:30:11'),
(18, 'bugsbunny', 'looneyToon', 1289, NULL, 18, NULL, NULL, NULL, 1, '2026-03-12 09:03:01', '2026-03-12 12:31:38'),
(19, 'Porky', 'toon', 1268, NULL, 19, NULL, NULL, NULL, 1, '2026-03-12 09:05:45', '2026-03-12 12:31:38'),
(20, 'Lucas', 'Pato', 1200, NULL, 20, NULL, NULL, NULL, 1, '2026-03-12 09:08:53', NULL),
(21, 'Batman', 'Wayne', 1191, NULL, 21, NULL, NULL, NULL, 1, '2026-03-12 09:09:51', '2026-03-12 12:31:38'),
(22, 'DareDevil', 'murdok', 1161, NULL, 22, NULL, NULL, NULL, 1, '2026-03-12 09:11:36', '2026-03-12 12:31:38'),
(23, 'Moon', 'knight', 1172, NULL, 23, NULL, NULL, NULL, 1, '2026-03-12 09:12:40', '2026-03-12 12:30:11'),
(24, 'skipper', 'pinguino', 1159, NULL, 24, NULL, NULL, NULL, 1, '2026-03-12 09:16:55', '2026-03-12 12:31:38'),
(25, 'Cabo', 'pingui', 1200, NULL, 25, NULL, NULL, NULL, 1, '2026-03-12 10:18:03', '2026-03-12 11:19:57'),
(26, 'kobalsky', 'pingui', 1217, NULL, 26, NULL, NULL, NULL, 1, '2026-03-12 10:18:55', '2026-03-12 12:31:38'),
(27, 'Rico', 'pingui', 1127, NULL, 27, NULL, NULL, NULL, 1, '2026-03-12 10:19:33', '2026-03-12 12:31:38'),
(28, 'santiago', 'nuñez', 1200, NULL, 28, NULL, NULL, NULL, 1, '2026-03-12 10:22:15', NULL),
(29, 'Lucas', 'Pato', 1251, NULL, 29, NULL, NULL, NULL, 1, '2026-03-12 11:17:28', '2026-03-12 12:31:38'),
(30, 'Juan', 'Perez', 1200, NULL, 30, NULL, NULL, NULL, 1, '2026-03-12 12:25:17', NULL),
(31, 'Axel', 'Alcala', 1200, NULL, 31, NULL, NULL, NULL, 1, '2026-03-31 16:49:39', NULL),
(32, 'Axel', 'Alcala', 1200, NULL, 32, NULL, NULL, NULL, 1, '2026-03-31 16:53:15', NULL),
(33, 'Axel', 'Alcala', 1200, NULL, 33, NULL, NULL, NULL, 1, '2026-03-31 17:29:58', NULL),
(34, 'Axel', 'Alcala', 1200, NULL, 34, NULL, NULL, NULL, 1, '2026-03-31 17:33:06', NULL),
(35, 'atsel', 'rodriguez', 1200, NULL, 35, NULL, NULL, NULL, 1, '2026-03-31 17:34:55', NULL),
(36, 'Axel', 'Alcala', 1200, 14, 36, NULL, NULL, NULL, 1, '2026-03-31 17:41:46', '2026-04-05 16:49:08'),
(37, 'prueba', 'solicitud', 1200, NULL, 37, NULL, NULL, NULL, 1, '2026-04-05 17:18:13', '2026-04-05 17:19:03');

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

--
-- Volcado de datos para la tabla `partidas`
--

INSERT INTO `partidas` (`id_partida`, `id_emparejamiento`, `tablero`, `id_jugador_blancas`, `id_jugador_negras`, `fecha_partida`, `resultado`, `comentarios`, `pgn`, `created_at`, `updated_at`) VALUES
(1, NULL, 1, NULL, NULL, NULL, '1-0', NULL, NULL, '2026-01-22 19:24:47', '2026-01-22 19:24:55'),
(2, NULL, 2, NULL, NULL, NULL, '1-0', NULL, NULL, '2026-01-22 19:24:47', '2026-01-22 19:24:55'),
(3, NULL, 3, NULL, NULL, NULL, '1-0', NULL, NULL, '2026-01-22 19:24:47', '2026-01-22 19:24:55'),
(4, NULL, 1, NULL, NULL, NULL, '1-0', NULL, NULL, '2026-01-22 19:25:18', '2026-01-22 19:25:26'),
(5, NULL, 2, NULL, NULL, NULL, '1-0', NULL, NULL, '2026-01-22 19:25:18', '2026-01-22 19:25:26'),
(6, NULL, 3, NULL, NULL, NULL, '1-0', NULL, NULL, '2026-01-22 19:25:18', '2026-01-22 19:25:26'),
(7, NULL, 1, NULL, NULL, NULL, '1-0', NULL, NULL, '2026-01-22 19:25:49', '2026-01-22 19:26:52'),
(8, NULL, 2, NULL, NULL, NULL, '1-0', NULL, NULL, '2026-01-22 19:25:49', '2026-01-22 19:26:53'),
(9, NULL, 3, NULL, NULL, NULL, '1-0', NULL, NULL, '2026-01-22 19:25:49', '2026-01-22 19:26:53'),
(10, 4, 1, 24, 15, NULL, '1/2', NULL, NULL, '2026-03-12 11:22:52', '2026-03-12 11:25:41'),
(11, 4, 2, 26, 16, NULL, '1-0', NULL, NULL, '2026-03-12 11:22:52', '2026-03-12 11:25:41'),
(12, 4, 3, 27, 17, NULL, '0-1', NULL, NULL, '2026-03-12 11:22:52', '2026-03-12 11:25:41'),
(13, 5, 1, 21, 18, NULL, '0-1', NULL, NULL, '2026-03-12 11:22:52', '2026-03-12 11:25:41'),
(14, 5, 2, 22, 19, NULL, '0-1', NULL, NULL, '2026-03-12 11:22:52', '2026-03-12 11:25:41'),
(15, 5, 3, 23, 29, NULL, '0-1', NULL, NULL, '2026-03-12 11:22:52', '2026-03-12 11:25:41'),
(16, 6, 1, 17, 21, NULL, '1-0', NULL, NULL, '2026-03-12 11:26:35', '2026-03-12 11:27:24'),
(17, 6, 2, 15, 22, NULL, '1/2', NULL, NULL, '2026-03-12 11:26:35', '2026-03-12 11:27:24'),
(18, 6, 3, 16, 23, NULL, '1/2', NULL, NULL, '2026-03-12 11:26:35', '2026-03-12 11:27:24'),
(19, 7, 1, 26, 18, NULL, '0-1', NULL, NULL, '2026-03-12 11:26:35', '2026-03-12 11:27:24'),
(20, 7, 2, 24, 19, NULL, '0-1', NULL, NULL, '2026-03-12 11:26:35', '2026-03-12 11:27:24'),
(21, 7, 3, 27, 29, NULL, '0-1', NULL, NULL, '2026-03-12 11:26:35', '2026-03-12 11:27:24'),
(22, 8, 1, 18, 17, NULL, '1-0', NULL, NULL, '2026-03-12 11:28:35', '2026-03-12 11:29:20'),
(23, 8, 2, 19, 15, NULL, '1-0', NULL, NULL, '2026-03-12 11:28:35', '2026-03-12 11:29:20'),
(24, 8, 3, 29, 16, NULL, '1-0', NULL, NULL, '2026-03-12 11:28:35', '2026-03-12 11:29:20'),
(25, 9, 1, 26, 22, NULL, '1/2', NULL, NULL, '2026-03-12 11:28:35', '2026-03-12 11:29:20'),
(26, 9, 2, 24, 23, NULL, '0-1', NULL, NULL, '2026-03-12 11:28:35', '2026-03-12 11:29:20'),
(27, 9, 3, 27, 21, NULL, '1/2', NULL, NULL, '2026-03-12 11:28:35', '2026-03-12 11:29:20'),
(28, 10, 1, 26, 17, NULL, '1-0', NULL, NULL, '2026-03-12 11:39:35', '2026-03-12 11:41:00'),
(29, 10, 2, 24, 15, NULL, '1/2', NULL, NULL, '2026-03-12 11:39:35', '2026-03-12 11:41:00'),
(30, 10, 3, 27, 16, NULL, '0-1', NULL, NULL, '2026-03-12 11:39:35', '2026-03-12 11:41:00'),
(31, 11, 1, 23, 18, NULL, '0-1', NULL, NULL, '2026-03-12 11:39:35', '2026-03-12 11:41:00'),
(32, 11, 2, 22, 19, NULL, '1/2', NULL, NULL, '2026-03-12 11:39:35', '2026-03-12 11:41:00'),
(33, 11, 3, 21, 29, NULL, '1-0', NULL, NULL, '2026-03-12 11:39:35', '2026-03-12 11:41:00'),
(34, 12, 1, 26, 17, NULL, '1-0', NULL, NULL, '2026-03-12 12:28:58', '2026-03-12 12:30:11'),
(35, 12, 2, 24, 16, NULL, '1/2', NULL, NULL, '2026-03-12 12:28:58', '2026-03-12 12:30:11'),
(36, 12, 3, 27, 15, NULL, '0-1', NULL, NULL, '2026-03-12 12:28:58', '2026-03-12 12:30:11'),
(37, 13, 1, 22, 18, NULL, '0-1', NULL, NULL, '2026-03-12 12:28:58', '2026-03-12 12:30:11'),
(38, 13, 2, 21, 19, NULL, '0-1', NULL, NULL, '2026-03-12 12:28:58', '2026-03-12 12:30:11'),
(39, 13, 3, 23, 29, NULL, '0-1', NULL, NULL, '2026-03-12 12:28:58', '2026-03-12 12:30:11'),
(40, 14, 1, 15, 22, NULL, '1-0', NULL, NULL, '2026-03-12 12:31:00', '2026-03-12 12:31:38'),
(41, 14, 2, 16, 21, NULL, '0-1', NULL, NULL, '2026-03-12 12:31:00', '2026-03-12 12:31:38'),
(42, 14, 3, 17, 23, NULL, '*', NULL, NULL, '2026-03-12 12:31:00', NULL),
(43, 15, 1, 26, 18, NULL, '0-1', NULL, NULL, '2026-03-12 12:31:00', '2026-03-12 12:31:38'),
(44, 15, 2, 24, 19, NULL, '0-1', NULL, NULL, '2026-03-12 12:31:00', '2026-03-12 12:31:38'),
(45, 15, 3, 27, 29, NULL, '0-1', NULL, NULL, '2026-03-12 12:31:00', '2026-03-12 12:31:38');

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

--
-- Volcado de datos para la tabla `rondas`
--

INSERT INTO `rondas` (`id_ronda`, `id_torneo`, `numero_ronda`, `fecha_ronda`, `created_at`, `updated_at`) VALUES
(4, 4, 1, '2026-03-12', '2026-03-12 11:22:52', NULL),
(5, 4, 2, '2026-03-12', '2026-03-12 11:26:35', NULL),
(6, 4, 3, '2026-03-13', '2026-03-12 11:28:35', NULL),
(7, 5, 1, '2026-03-12', '2026-03-12 11:39:35', NULL),
(8, 6, 1, '2026-03-12', '2026-03-12 12:28:58', NULL),
(9, 6, 2, '2026-03-12', '2026-03-12 12:31:00', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_equipo`
--

CREATE TABLE `solicitudes_equipo` (
  `id_solicitud` int(11) NOT NULL,
  `id_jugador` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `estado` enum('pendiente','aceptada','rechazada') DEFAULT 'pendiente',
  `mensaje` varchar(255) DEFAULT NULL,
  `fecha_solicitud` datetime DEFAULT current_timestamp(),
  `fecha_respuesta` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_torneo`
--

CREATE TABLE `solicitudes_torneo` (
  `id_solicitud` int(11) NOT NULL,
  `id_torneo` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `id_cuenta` int(11) NOT NULL,
  `estado` enum('pendiente','aceptada','rechazada') DEFAULT 'pendiente',
  `mensaje` varchar(255) DEFAULT NULL,
  `fecha_solicitud` datetime DEFAULT current_timestamp(),
  `fecha_respuesta` datetime DEFAULT NULL
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

--
-- Volcado de datos para la tabla `torneos`
--

INSERT INTO `torneos` (`id_torneo`, `nombre`, `descripcion`, `tipo`, `sistema`, `condicion`, `nro_tableros`, `fecha_inicio`, `fecha_fin`, `ubicacion`, `ciudad`, `provincia`, `pais`, `id_cuenta`, `ritmo_tiempo`, `id_club`, `created_at`, `updated_at`) VALUES
(4, 'expo', '', 'equipos', 'round_robin_ponderado', 'finalizado', 4, '2026-03-12', '2026-03-13', 'Cbtis 168 Biblioteca', NULL, NULL, 'México', NULL, '60+30', 1, '2026-03-12 11:21:22', '2026-03-16 09:46:55'),
(5, 'prueba', '', 'equipos', 'round_robin_ponderado', 'finalizado', 4, '2026-03-12', '2026-03-12', 'Cbtis 168 Biblioteca', NULL, NULL, 'México', NULL, '60+30', 1, '2026-03-12 11:39:00', '2026-03-12 22:53:29'),
(6, 'expo2', '', 'equipos', 'round_robin_ponderado', 'finalizado', 4, '2026-03-12', '2026-03-13', 'Cbtis 168 Biblioteca', NULL, NULL, 'México', NULL, '60+30', 1, '2026-03-12 12:27:43', '2026-03-16 09:46:55'),
(7, 'pruebasolicitud', '', 'equipos', 'round_robin_ponderado', 'en_progreso', 4, '2026-04-05', '2026-04-07', 'biblioteca', NULL, NULL, 'México', 36, '60+30', 1, '2026-04-05 17:15:20', '2026-04-05 17:15:20');

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
-- Indices de la tabla `solicitudes_equipo`
--
ALTER TABLE `solicitudes_equipo`
  ADD PRIMARY KEY (`id_solicitud`),
  ADD UNIQUE KEY `uk_solicitud_jugador_equipo` (`id_jugador`,`id_equipo`,`estado`),
  ADD KEY `idx_sol_equipo` (`id_equipo`),
  ADD KEY `idx_sol_jugador` (`id_jugador`);

--
-- Indices de la tabla `solicitudes_torneo`
--
ALTER TABLE `solicitudes_torneo`
  ADD PRIMARY KEY (`id_solicitud`),
  ADD UNIQUE KEY `uk_solicitud_torneo_equipo` (`id_torneo`,`id_equipo`,`estado`),
  ADD KEY `idx_st_torneo` (`id_torneo`),
  ADD KEY `idx_st_equipo` (`id_equipo`),
  ADD KEY `idx_st_cuenta` (`id_cuenta`);

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
  MODIFY `id_cuenta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `emparejamientos`
--
ALTER TABLE `emparejamientos`
  MODIFY `id_emparejamiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `equipos`
--
ALTER TABLE `equipos`
  MODIFY `id_equipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
  MODIFY `id_jugador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `partidas`
--
ALTER TABLE `partidas`
  MODIFY `id_partida` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

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
  MODIFY `id_ronda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `solicitudes_equipo`
--
ALTER TABLE `solicitudes_equipo`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `solicitudes_torneo`
--
ALTER TABLE `solicitudes_torneo`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `torneos`
--
ALTER TABLE `torneos`
  MODIFY `id_torneo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
-- Filtros para la tabla `solicitudes_equipo`
--
ALTER TABLE `solicitudes_equipo`
  ADD CONSTRAINT `fk_sol_equipo` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sol_jugador` FOREIGN KEY (`id_jugador`) REFERENCES `jugadores` (`id_jugador`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `solicitudes_torneo`
--
ALTER TABLE `solicitudes_torneo`
  ADD CONSTRAINT `fk_st_cuenta` FOREIGN KEY (`id_cuenta`) REFERENCES `cuentas` (`id_cuenta`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_st_equipo` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_st_torneo` FOREIGN KEY (`id_torneo`) REFERENCES `torneos` (`id_torneo`) ON DELETE CASCADE ON UPDATE CASCADE;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
