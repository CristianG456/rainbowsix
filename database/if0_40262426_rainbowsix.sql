-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Servidor: sql113.infinityfree.com
-- Tiempo de generación: 05-11-2025 a las 12:01:17
-- Versión del servidor: 11.4.7-MariaDB
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
-- Base de datos: `if0_40262426_rainbowsix`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `armas`
--

CREATE TABLE `armas` (
  `id_arma` int(11) NOT NULL,
  `nomb_arma` text NOT NULL,
  `dano_cabeza` int(11) NOT NULL,
  `dano_torso` int(11) NOT NULL,
  `id_tipo_arma` int(11) NOT NULL,
  `cant_balas` int(11) DEFAULT NULL,
  `img_arma` varchar(500) NOT NULL,
  `id_nivel_arma` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `armas`
--

INSERT INTO `armas` (`id_arma`, `nomb_arma`, `dano_cabeza`, `dano_torso`, `id_tipo_arma`, `cant_balas`, `img_arma`, `id_nivel_arma`) VALUES
(1, 'Puño', 10, 5, 1, NULL, 'puño.jpg', 1),
(2, 'Cuchillo', 10, 5, 1, NULL, 'cuchillo.png', 1),
(3, 'Pistola', 20, 10, 1, 15, 'pistola.png', 2),
(4, 'Subfusil', 20, 10, 2, 30, 'SUBFUSIL.png', 2),
(5, 'Fusil de asalto', 25, 15, 3, 30, 'fusilasalto.png', 3),
(6, 'Escopeta', 25, 15, 2, 8, 'escopeta.png', 3),
(7, 'Franco', 40, 25, 3, 5, 'franco.png', 4),
(9, 'Ballesta', 70, 50, 3, 5, '6907a71980295.png', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `avatar`
--

CREATE TABLE `avatar` (
  `id_avatar` int(11) NOT NULL,
  `nomb_avat` text NOT NULL,
  `url_personaje` varchar(500) NOT NULL,
  `url_avatar` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `avatar`
--

INSERT INTO `avatar` (`id_avatar`, `nomb_avat`, `url_personaje`, `url_avatar`) VALUES
(1, 'Skopos', 'skopospj.webp', 'skoposavat.webp'),
(2, 'Deimos', 'deimospj.webp', 'deimosavat.webp'),
(3, 'Nokk', 'nokkpj.webp', 'nokkavat.webp'),
(4, 'Ace', 'acepj.webp', 'aceavat.webp'),
(6, 'Blackbeard', 'personaje_69082e54767ba.webp', 'avatar_69082e54767bd.webp');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_usuario_partida`
--

CREATE TABLE `detalle_usuario_partida` (
  `id_usuario_partida` int(11) NOT NULL,
  `puntos_total` int(11) NOT NULL,
  `id_usuario1` int(11) NOT NULL,
  `id_usuario2` int(11) DEFAULT NULL,
  `id_partida` int(11) NOT NULL,
  `id_arma` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_usuario_partida`
--

INSERT INTO `detalle_usuario_partida` (`id_usuario_partida`, `puntos_total`, `id_usuario1`, `id_usuario2`, `id_partida`, `id_arma`) VALUES
(2834, 0, 11, NULL, 145, 1),
(2835, 0, 11, NULL, 147, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado`
--

CREATE TABLE `estado` (
  `id_estado` int(11) NOT NULL,
  `estado` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado`
--

INSERT INTO `estado` (`id_estado`, `estado`) VALUES
(1, 'ACTIVO'),
(2, 'BLOQUEADO'),
(3, 'ABIERTO'),
(4, 'CERRADO'),
(5, 'EN JUEGO'),
(6, 'GANADOR'),
(7, 'PERDEDOR'),
(8, 'ELIMINADO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mundo`
--

CREATE TABLE `mundo` (
  `id_mundo` int(11) NOT NULL,
  `nomb_mundo` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mundo`
--

INSERT INTO `mundo` (`id_mundo`, `nomb_mundo`) VALUES
(1, 'MUNDO PRINCIPIANTE'),
(2, 'MUNDO AVANZADO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nivel`
--

CREATE TABLE `nivel` (
  `id_nivel` int(11) NOT NULL,
  `nomb_nivel` varchar(500) NOT NULL,
  `puntos_nivel` int(11) NOT NULL,
  `url_nivel` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `nivel`
--

INSERT INTO `nivel` (`id_nivel`, `nomb_nivel`, `puntos_nivel`, `url_nivel`) VALUES
(1, 'Bronce', 0, 'broncee.png'),
(2, 'Plata', 250, 'plataa.png'),
(3, 'Oro', 500, 'oroo.png'),
(4, 'Diamante', 750, 'diamantee.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partida`
--

CREATE TABLE `partida` (
  `id_partida` int(11) NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `cantidad_jug` int(11) NOT NULL,
  `id_estado_part` int(11) NOT NULL,
  `id_sala` int(11) NOT NULL,
  `id_ganador` int(10) DEFAULT NULL,
  `inicio_cuenta_regresiva` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `partida`
--

INSERT INTO `partida` (`id_partida`, `fecha_inicio`, `fecha_fin`, `cantidad_jug`, `id_estado_part`, `id_sala`, `id_ganador`, `inicio_cuenta_regresiva`) VALUES
(126, '2025-11-03 21:02:20', '2025-11-03 21:24:12', 0, 4, 1, NULL, NULL),
(127, '2025-11-03 21:36:40', '0000-00-00 00:00:00', 1, 3, 1, NULL, '2025-11-03 21:36:40'),
(128, '2025-11-03 21:02:22', '2025-11-03 21:32:22', 0, 4, 1, NULL, NULL),
(129, '2025-11-03 21:34:09', '2025-11-03 21:35:15', 1, 4, 1, NULL, NULL),
(130, '2025-11-03 21:03:54', '2025-11-03 21:08:52', 1, 4, 1, NULL, NULL),
(131, '2025-11-03 21:36:45', '0000-00-00 00:00:00', 1, 3, 1, NULL, '2025-11-03 21:36:45'),
(132, '2025-11-03 21:09:01', '2025-11-03 21:09:41', 1, 4, 1, NULL, NULL),
(133, '2025-11-04 07:13:05', '0000-00-00 00:00:00', 1, 3, 1, NULL, '2025-11-04 07:13:06'),
(134, '2025-11-03 21:09:46', '2025-11-03 21:12:59', 1, 4, 1, NULL, NULL),
(135, '2025-11-03 21:14:43', '2025-11-03 21:16:12', 1, 6, 1, 3, '2025-11-03 21:14:51'),
(136, '2025-11-03 21:14:51', '2025-11-03 21:16:24', 1, 4, 1, NULL, NULL),
(137, '2025-11-03 21:16:26', '2025-11-03 21:17:09', 1, 6, 1, 3, '2025-11-03 21:16:29'),
(138, '2025-11-04 07:13:13', '0000-00-00 00:00:00', 1, 5, 1, NULL, '2025-11-04 07:13:13'),
(139, '2025-11-04 07:13:32', '2025-11-04 07:14:33', 0, 4, 1, NULL, '2025-11-04 07:13:33'),
(140, '2025-11-03 21:17:23', '2025-11-03 21:21:12', 1, 4, 1, NULL, NULL),
(141, '2025-11-04 09:14:46', '0000-00-00 00:00:00', 1, 3, 1, NULL, '2025-11-04 09:14:46'),
(142, '2025-11-03 21:21:21', '2025-11-03 21:22:30', 1, 4, 1, NULL, NULL),
(143, '2025-11-04 09:24:26', '2025-11-04 09:24:31', 1, 4, 1, NULL, '2025-11-04 09:24:26'),
(144, '2025-11-03 21:23:22', '2025-11-03 21:24:07', 0, 6, 1, 3, '2025-11-03 21:23:22'),
(145, '2025-11-04 20:13:44', '0000-00-00 00:00:00', 1, 3, 1, NULL, '2025-11-04 20:13:44'),
(146, '2025-11-04 20:14:14', '0000-00-00 00:00:00', 1, 5, 1, NULL, '2025-11-04 20:14:14'),
(147, '2025-11-04 20:43:16', '0000-00-00 00:00:00', 0, 3, 1, NULL, '2025-11-04 20:43:16'),
(148, '2025-11-03 21:32:54', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:32:54'),
(149, '2025-11-03 21:32:56', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:32:56'),
(150, '2025-11-03 21:32:58', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:32:58'),
(151, '2025-11-03 21:33:00', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:00'),
(152, '2025-11-03 21:33:01', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:01'),
(153, '2025-11-03 21:33:02', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:03'),
(154, '2025-11-03 21:33:04', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:05'),
(155, '2025-11-03 21:33:06', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:07'),
(156, '2025-11-03 21:33:08', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:09'),
(157, '2025-11-03 21:33:10', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:11'),
(158, '2025-11-03 21:33:11', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:12'),
(159, '2025-11-03 21:33:13', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:14'),
(160, '2025-11-03 21:33:15', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:16'),
(161, '2025-11-03 21:33:18', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:19'),
(162, '2025-11-03 21:33:18', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:22'),
(163, '2025-11-03 21:33:23', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:24'),
(164, '2025-11-03 21:33:25', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:26'),
(165, '2025-11-03 21:33:28', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:28'),
(166, '2025-11-03 21:33:30', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:30'),
(167, '2025-11-03 21:33:32', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:32'),
(168, '2025-11-03 21:33:34', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:35'),
(169, '2025-11-03 21:33:34', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:37'),
(170, '2025-11-03 21:33:39', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:39'),
(171, '2025-11-03 21:33:41', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:41'),
(172, '2025-11-03 21:33:43', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:43'),
(173, '2025-11-03 21:33:45', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:45'),
(174, '2025-11-03 21:33:47', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:47'),
(175, '2025-11-03 21:33:49', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:49'),
(176, '2025-11-03 21:33:51', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:51'),
(177, '2025-11-03 21:33:53', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:53'),
(178, '2025-11-03 21:33:54', '0000-00-00 00:00:00', 1, 1, 1, NULL, '2025-11-03 21:33:55'),
(179, '2025-11-03 21:33:56', '0000-00-00 00:00:00', 0, 1, 1, NULL, '2025-11-03 21:33:57'),
(180, '2025-11-03 21:33:58', '0000-00-00 00:00:00', 1, 1, 1, NULL, '2025-11-03 21:34:00'),
(181, '2025-11-03 21:34:02', '2025-11-03 21:37:37', 0, 4, 1, NULL, '2025-11-03 21:36:46'),
(182, '2025-11-03 21:37:39', '0000-00-00 00:00:00', 1, 3, 2, NULL, NULL),
(183, '2025-11-03 21:37:39', '0000-00-00 00:00:00', 0, 3, 2, NULL, '2025-11-03 21:37:39'),
(184, '2025-11-03 21:37:41', '2025-11-03 21:38:35', 0, 4, 2, NULL, '2025-11-03 21:37:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id_rol` int(11) NOT NULL,
  `nom_rol` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_rol`, `nom_rol`) VALUES
(1, 'ADMINISTRADOR'),
(2, 'JUGADOR');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sala`
--

CREATE TABLE `sala` (
  `id_sala` int(11) NOT NULL,
  `fecha_creacion` datetime NOT NULL,
  `url_sala` varchar(500) NOT NULL,
  `id_estado_sala` int(11) NOT NULL,
  `id_mundo` int(11) NOT NULL,
  `id_nivel` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sala`
--

INSERT INTO `sala` (`id_sala`, `fecha_creacion`, `url_sala`, `id_estado_sala`, `id_mundo`, `id_nivel`) VALUES
(1, '2025-10-06 23:47:29', '', 3, 1, 1),
(2, '2025-10-16 10:54:43', '', 3, 1, 1),
(3, '2025-10-16 10:54:43', '', 3, 1, 1),
(4, '2025-10-16 10:54:43', '', 3, 1, 1),
(5, '2025-10-16 10:54:43', '', 3, 1, 1),
(6, '2025-10-17 23:26:19', 'sala_auto_1760761579_290', 3, 1, 1),
(7, '2025-10-17 23:29:01', 'sala_auto_1760761741_617', 3, 1, 1),
(8, '2025-10-17 23:29:06', 'sala_auto_1760761746_749', 3, 1, 1),
(9, '2025-10-17 23:32:51', 'sala_auto_1760761971_342', 2, 1, 1),
(10, '2025-10-17 23:34:28', 'sala_auto_1760762068_806', 2, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_arma`
--

CREATE TABLE `tipo_arma` (
  `id_tipo_arma` int(11) NOT NULL,
  `tipo_arma` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_arma`
--

INSERT INTO `tipo_arma` (`id_tipo_arma`, `tipo_arma`) VALUES
(1, 'Corto Alcance'),
(2, 'Mediano Alcance'),
(3, 'Largo Alcance');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nomb_usu` text NOT NULL,
  `contra_usu` varchar(500) NOT NULL,
  `correo` varchar(250) NOT NULL,
  `vida` int(11) NOT NULL,
  `puntos` int(11) NOT NULL,
  `ultimo_ingreso` datetime NOT NULL,
  `id_avatar` int(11) DEFAULT NULL,
  `id_rol` int(11) NOT NULL,
  `id_nivel` int(11) NOT NULL,
  `id_estado_usu` int(11) NOT NULL,
  `ultima_actividad` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nomb_usu`, `contra_usu`, `correo`, `vida`, `puntos`, `ultimo_ingreso`, `id_avatar`, `id_rol`, `id_nivel`, `id_estado_usu`, `ultima_actividad`) VALUES
(0, 'kev1', '$2y$10$OLDP2GH4vyK66Yx7L5zkC.FMP92KdVxmGgW99Fdc.HwxoXxoicxxG', 'kevin@gmail.com', 200, 0, '2025-10-31 13:38:23', NULL, 2, 1, 1, '2025-10-31 13:38:23'),
(3, 'kevin1', '$2y$05$fSw5EsGzaKGmxvFW0U1v4.IoyUmxgLQ2BJhaPtBN69LAPzwMhS0y6', 'prueba1@gmail.com', 200, 8110, '2025-09-29 23:42:11', 2, 2, 4, 1, '2025-11-04 20:15:08'),
(4, 'kevin2', '$2y$05$CAogpD6U6PNmLJ5UyFQBAe7psQ2G04NRw3A2jnHFe4Q7XalCLy/1u', 'prueba@gmail.com', 200, 5385, '2025-09-29 23:49:00', 6, 2, 4, 1, '2025-11-04 09:24:33'),
(5, 'admin1', '$2y$05$tev8R65QSkIbN/Q9mCrqGua9U5ETvGkwm8SYYJLl4ivbCniMjf22G', 'admin1@gmail.com', 200, 0, '2025-09-30 00:10:35', NULL, 1, 1, 1, '2025-10-26 22:13:03'),
(6, 'admin2', '$2y$05$S7rJ1au/Ixbit.d5j/.fq.MPv63ln6fU7hlym56wWGr3F3U4eeKkm', 'admin2@gmail.com', 200, 0, '2025-09-30 00:10:46', NULL, 1, 1, 1, '2025-10-26 22:13:03'),
(7, 'Cronos', '$2y$05$AoCXnC9gW2p3ZnMxZ0p.ku5HAqEaaDQIx/BAai8aScYgs7qIkUbtm', 'cristiancronos123@gmail.com', 200, 3535, '2025-09-30 16:55:35', 2, 2, 4, 1, '2025-11-04 07:14:28'),
(8, 'kevin3', '$2y$05$rG8RY0XSWd3m1QC.EkyH7O.KlOMY0RTYw7/eJ2G2J6UR5Wp1HCu7u', 'prueba33@gmail.com', 200, 0, '2025-10-08 22:58:05', 1, 2, 1, 1, '2025-10-27 09:48:39'),
(9, 'daniel1', '$2y$05$7jYnXBc3GnazRSpgiYOdf.zYmYYHvWKufWN375FRklsm02t03lyX6', 'daniel@gmail.com', 200, 0, '2025-10-18 01:02:05', NULL, 2, 1, 1, '2025-10-26 22:13:03'),
(10, 'daniel22', '$2y$10$whrjXoXl9uysuWb6IJHq5eZfBD9lU3HUpDMHYV2CRu2qocMvNT3TO', 'kevindany2209@gmail.com', 0, 2400, '2025-10-27 08:26:43', 1, 2, 4, 8, '2025-11-04 20:15:02'),
(11, 'dires123', '$2y$12$ka.cz.BERHZ03txkXN.T1uczSk7tvrqci8GKz3pVJAwWDPVkcYsTe', 'didierreyes003@gmail.com', 200, 1810, '2025-10-27 11:25:50', 6, 2, 4, 1, '2025-11-04 20:45:40'),
(12, 'dilan123', '$2y$10$gFauJ5mKIIJWc0kASx4AiORi.SofrZMFBWcaVe1sxUfMVYsd1FaI2', 'dilansantiortizm@gmail.com', 200, 0, '2025-10-27 11:38:15', 3, 2, 1, 1, '2025-10-29 20:41:39'),
(13, 'sebas1', '$2y$10$z/MzdHQBG89UIYF.yprIl.QyfzcIeDv5TPxYHf7bbwtoUX8wJr7jK', 'sebastiangarciaalvarez123@gmail.com', 0, 530, '2025-10-27 12:55:59', 2, 2, 3, 8, '2025-10-27 15:27:31'),
(14, 'david', '$2y$10$DDtODI0/uj5V58QZlcyVHe83dGiHHlDGJhqRL5.DRdG0rzft4jzPS', 'madarazeduchiha@gmail.com', 0, 180, '2025-10-27 13:34:59', 2, 2, 1, 7, '2025-10-27 13:38:45'),
(16, 'Mono', '$2y$10$dGs.15tkBei6addytcjSU.EwV.DjnTybcExY3mPmP5nLIOEeIVvEK', 'juanestebank7@gmail.com', 200, 0, '2025-10-27 14:00:17', 3, 2, 1, 1, '2025-10-27 14:00:17'),
(17, 'Rompeanos', '$2y$10$NLXJDTz2r7Dk2PXeIeO1ZOjRKCetU2UwYn6OWdS0B8wK20AaxNSzC', 'lucho3112007@gmail.com', 0, 300, '2025-10-27 15:18:12', 1, 2, 2, 7, '2025-10-27 15:24:41'),
(18, 'yubergas', '$2y$10$aqMcPJ3axi6/LmpncRFaO.Mkz.TlWtBs0R/M1LDltnf9ageQLjNyO', 'santiagopc333@gmail.com', 150, 340, '2025-10-27 15:21:13', 2, 2, 2, 6, '2025-10-27 15:24:32'),
(19, 'Stevan', '$2y$10$DQqp7l.a0DV5wesCXDUh7e76.6OC.mfuinyQcRFUGB9vu6x50LTt2', 'bastobrayan246@gmail.com', 200, 870, '2025-10-27 15:24:35', 3, 2, 4, 1, '2025-10-27 15:31:33'),
(20, 'jose365', '$2y$10$S2hyuLfgtqY1QHM4w1vMOumPMQsZB8oI2stxicWgCcnbrhfw1P3fW', 'joseluis1409rodriguez@gmail.com', 0, 0, '2025-10-27 15:24:44', 4, 2, 1, 8, '2025-10-27 15:28:39'),
(21, 'alex921', '$2y$10$1hPJGEwwIPN77N6wMNeN9uOnTePliKdqCAjbqmc22oKv1IyuwmRcu', 'alex@gmail.com', 200, 210, '2025-10-27 15:26:14', 3, 2, 1, 1, '2025-10-27 15:30:02'),
(22, 'pro', '$2y$10$UqUyFrZ9/mAMsTja7h5gQ.2fFHMlA9VADnP7KvSjxpGhs/g7SfVam', 'pro@gmail.com', 0, 600, '2025-10-29 11:56:26', 4, 2, 3, 1, '2025-10-29 15:13:25'),
(23, 'dan', '$2y$10$80YuNn3NLzpPNUcoV08AeeLzRXxEu2w246ve3lIYa8B9fZgjyuzNy', 'asdas@gmail.com', 200, 0, '2025-10-29 15:17:11', 4, 2, 1, 1, '2025-10-29 15:17:11'),
(24, 'villa', '$2y$10$pFpn2BB8znAKdrwbVVqbcOTUiJh0WcII46OgVPZGoU.VLPtCXL2q.', 'villa@gmail.com', 0, 0, '2025-10-31 13:01:50', 3, 2, 1, 8, '2025-10-31 13:25:25'),
(25, 'pepe', '$2y$10$cV71JKAEDeH3VBJsoK/K5.YyhPl/jKsqYbzBbk.sDH2RQq6MHkHsu', 'pe@gmail.com', 200, 0, '2025-10-31 14:48:08', NULL, 2, 1, 1, '2025-10-31 14:48:08'),
(26, 'kevin2209', '$2y$10$MDaA8Gkw7hvcj6ZXFqSBQu7fmwGd9T97FtJQe2gRjPU6wFjQiJ9iy', 'kevindany1993838@gmail.com', 200, 0, '2025-11-01 21:56:13', NULL, 2, 1, 1, '2025-11-01 21:56:13'),
(27, 'paola', '$2y$10$84.1e99ANlMOPwuU.jjJ3OpcwRsanaiL3q3cdrhbBP/X3Oz0UvNXm', 'adrianaperez071@gmail.com', 200, 0, '2025-11-01 22:42:40', NULL, 2, 1, 1, '2025-11-01 22:42:40'),
(28, 'adri', '$2y$10$U3/tlPD3QdsN7OZu.pse8ensa2N1QwHyTfqZqb.zw0d.FQULheJsa', 'adrianaperezd071@gmail.com', 200, 0, '2025-11-01 22:44:27', NULL, 2, 1, 1, '2025-11-01 22:44:27'),
(29, 'humber', '$2y$10$TOsDekLIjLxW7HuW5mswxeXWDCiMYRhdQeEqiWbqPyTV2xuwgDBpe', 'humber@gmail.com', 200, 0, '2025-11-01 23:03:17', NULL, 2, 1, 2, '2025-11-01 23:03:17'),
(30, 'bross', '$2y$10$1LYMutwDV9881Dd/Pv07FOajnXIQjK0UYwKdGrHxxRxcdYq0OymeW', 'kevinreal2209@gmail.com', 200, 0, '2025-11-04 09:28:08', NULL, 2, 1, 1, '2025-11-04 09:28:08');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `armas`
--
ALTER TABLE `armas`
  ADD PRIMARY KEY (`id_arma`),
  ADD KEY `id_nivel` (`id_nivel_arma`),
  ADD KEY `id_tipo_arma` (`id_tipo_arma`);

--
-- Indices de la tabla `avatar`
--
ALTER TABLE `avatar`
  ADD PRIMARY KEY (`id_avatar`);

--
-- Indices de la tabla `detalle_usuario_partida`
--
ALTER TABLE `detalle_usuario_partida`
  ADD PRIMARY KEY (`id_usuario_partida`),
  ADD UNIQUE KEY `uniq_jugador_partida` (`id_usuario1`,`id_usuario2`,`id_partida`),
  ADD KEY `id_usuario` (`id_usuario1`),
  ADD KEY `id_usuario2` (`id_usuario2`),
  ADD KEY `id_partida` (`id_partida`),
  ADD KEY `id_arma` (`id_arma`);

--
-- Indices de la tabla `estado`
--
ALTER TABLE `estado`
  ADD PRIMARY KEY (`id_estado`);

--
-- Indices de la tabla `mundo`
--
ALTER TABLE `mundo`
  ADD PRIMARY KEY (`id_mundo`);

--
-- Indices de la tabla `nivel`
--
ALTER TABLE `nivel`
  ADD PRIMARY KEY (`id_nivel`);

--
-- Indices de la tabla `partida`
--
ALTER TABLE `partida`
  ADD PRIMARY KEY (`id_partida`),
  ADD KEY `id_sala` (`id_sala`),
  ADD KEY `id_estado_part` (`id_estado_part`),
  ADD KEY `id_ganador` (`id_ganador`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `sala`
--
ALTER TABLE `sala`
  ADD PRIMARY KEY (`id_sala`),
  ADD KEY `id_mundo` (`id_mundo`),
  ADD KEY `id_nivel` (`id_nivel`),
  ADD KEY `id_estado` (`id_estado_sala`);

--
-- Indices de la tabla `tipo_arma`
--
ALTER TABLE `tipo_arma`
  ADD PRIMARY KEY (`id_tipo_arma`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD KEY `id_rol` (`id_rol`),
  ADD KEY `id_nivel` (`id_nivel`),
  ADD KEY `id_avatar` (`id_avatar`),
  ADD KEY `id_estado_usu` (`id_estado_usu`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `armas`
--
ALTER TABLE `armas`
  MODIFY `id_arma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `avatar`
--
ALTER TABLE `avatar`
  MODIFY `id_avatar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `detalle_usuario_partida`
--
ALTER TABLE `detalle_usuario_partida`
  MODIFY `id_usuario_partida` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2836;

--
-- AUTO_INCREMENT de la tabla `estado`
--
ALTER TABLE `estado`
  MODIFY `id_estado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `mundo`
--
ALTER TABLE `mundo`
  MODIFY `id_mundo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `nivel`
--
ALTER TABLE `nivel`
  MODIFY `id_nivel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `partida`
--
ALTER TABLE `partida`
  MODIFY `id_partida` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=185;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `sala`
--
ALTER TABLE `sala`
  MODIFY `id_sala` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `tipo_arma`
--
ALTER TABLE `tipo_arma`
  MODIFY `id_tipo_arma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `armas`
--
ALTER TABLE `armas`
  ADD CONSTRAINT `armas_ibfk_1` FOREIGN KEY (`id_nivel_arma`) REFERENCES `nivel` (`id_nivel`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `armas_ibfk_2` FOREIGN KEY (`id_tipo_arma`) REFERENCES `tipo_arma` (`id_tipo_arma`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_usuario_partida`
--
ALTER TABLE `detalle_usuario_partida`
  ADD CONSTRAINT `detalle_usuario_partida_ibfk_2` FOREIGN KEY (`id_usuario1`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detalle_usuario_partida_ibfk_3` FOREIGN KEY (`id_partida`) REFERENCES `partida` (`id_partida`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detalle_usuario_partida_ibfk_4` FOREIGN KEY (`id_usuario2`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detalle_usuario_partida_ibfk_5` FOREIGN KEY (`id_arma`) REFERENCES `armas` (`id_arma`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `partida`
--
ALTER TABLE `partida`
  ADD CONSTRAINT `partida_ibfk_1` FOREIGN KEY (`id_sala`) REFERENCES `sala` (`id_sala`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `partida_ibfk_2` FOREIGN KEY (`id_estado_part`) REFERENCES `estado` (`id_estado`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `partida_ibfk_3` FOREIGN KEY (`id_ganador`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `sala`
--
ALTER TABLE `sala`
  ADD CONSTRAINT `sala_ibfk_1` FOREIGN KEY (`id_mundo`) REFERENCES `mundo` (`id_mundo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sala_ibfk_2` FOREIGN KEY (`id_nivel`) REFERENCES `nivel` (`id_nivel`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sala_ibfk_3` FOREIGN KEY (`id_estado_sala`) REFERENCES `estado` (`id_estado`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usuario_ibfk_2` FOREIGN KEY (`id_nivel`) REFERENCES `nivel` (`id_nivel`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usuario_ibfk_3` FOREIGN KEY (`id_avatar`) REFERENCES `avatar` (`id_avatar`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usuario_ibfk_4` FOREIGN KEY (`id_estado_usu`) REFERENCES `estado` (`id_estado`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
