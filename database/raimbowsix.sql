-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-10-2025 a las 04:29:52
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
-- Base de datos: `raimbowsix`
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
(1, 'Puño', 10, 5, 1, NULL, '\"C:\\xampp\\htdocs\\rainbowsix\\controller\\img\\puño.jpg\"', 1),
(2, 'Cuchillo', 10, 5, 1, NULL, '\"C:\\xampp\\htdocs\\rainbowsix\\controller\\img\\cuchillo.png\"', 1),
(3, 'Pistola', 20, 10, 1, 15, '\"C:\\xampp\\htdocs\\rainbowsix\\controller\\img\\pistola.png\"', 2),
(4, 'Subfusil', 20, 10, 2, 30, '\"C:\\xampp\\htdocs\\rainbowsix\\controller\\img\\SUBFUSIL.png\"', 2),
(5, 'Fusil de asalto', 25, 15, 3, 30, '\"C:\\xampp\\htdocs\\rainbowsix\\controller\\img\\fusilasalto.png\"', 3),
(6, 'Escopeta', 25, 15, 2, 8, '\"C:\\xampp\\htdocs\\rainbowsix\\controller\\img\\escopeta.png\"', 3),
(7, 'Franco', 40, 25, 3, 5, '\"C:\\xampp\\htdocs\\rainbowsix\\controller\\img\\franco.png\"', 4);

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
(4, 'Ace', 'acepj.webp', 'aceavat.webp');

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
(5, '2025-10-16 10:54:43', '', 2, 1, 1),
(6, '2025-10-17 23:26:19', 'sala_auto_1760761579_290', 2, 1, 1),
(7, '2025-10-17 23:29:01', 'sala_auto_1760761741_617', 2, 1, 1),
(8, '2025-10-17 23:29:06', 'sala_auto_1760761746_749', 2, 1, 1),
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
(3, 'kevin1', '$2y$05$fSw5EsGzaKGmxvFW0U1v4.IoyUmxgLQ2BJhaPtBN69LAPzwMhS0y6', 'prueba1@gmail.com', 200, 5310, '2025-09-29 23:42:11', 3, 2, 4, 1, '2025-10-26 22:13:03'),
(4, 'kevin2', '$2y$05$CAogpD6U6PNmLJ5UyFQBAe7psQ2G04NRw3A2jnHFe4Q7XalCLy/1u', 'prueba@gmail.com', 200, 3980, '2025-09-29 23:49:00', 1, 2, 4, 1, '2025-10-26 22:13:03'),
(5, 'admin1', '$2y$05$tev8R65QSkIbN/Q9mCrqGua9U5ETvGkwm8SYYJLl4ivbCniMjf22G', 'admin1@gmail.com', 200, 0, '2025-09-30 00:10:35', NULL, 1, 1, 2, '2025-10-26 22:13:03'),
(6, 'admin2', '$2y$05$S7rJ1au/Ixbit.d5j/.fq.MPv63ln6fU7hlym56wWGr3F3U4eeKkm', 'admin2@gmail.com', 200, 0, '2025-09-30 00:10:46', NULL, 1, 1, 2, '2025-10-26 22:13:03'),
(7, 'Cronos', '$2y$05$AoCXnC9gW2p3ZnMxZ0p.ku5HAqEaaDQIx/BAai8aScYgs7qIkUbtm', 'cristiancronos123@gmail.com', 200, 510, '2025-09-30 16:55:35', 3, 2, 2, 1, '2025-10-26 22:13:03'),
(8, 'kevin3', '$2y$05$rG8RY0XSWd3m1QC.EkyH7O.KlOMY0RTYw7/eJ2G2J6UR5Wp1HCu7u', 'prueba33@gmail.com', 200, 0, '2025-10-08 22:58:05', 1, 2, 1, 1, '2025-10-26 22:13:03'),
(9, 'daniel1', '$2y$05$7jYnXBc3GnazRSpgiYOdf.zYmYYHvWKufWN375FRklsm02t03lyX6', 'daniel@gmail.com', 200, 0, '2025-10-18 01:02:05', NULL, 2, 1, 2, '2025-10-26 22:13:03');

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
  MODIFY `id_arma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `avatar`
--
ALTER TABLE `avatar`
  MODIFY `id_avatar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `detalle_usuario_partida`
--
ALTER TABLE `detalle_usuario_partida`
  MODIFY `id_usuario_partida` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id_partida` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
