-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 01-06-2025 a las 02:45:23
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
-- Base de datos: `cedu`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignacion_academica`
--

CREATE TABLE `asignacion_academica` (
  `id_asignacion` int(11) NOT NULL,
  `id_institucion` int(11) DEFAULT NULL,
  `nivel_educativo` enum('Preescolar','Primaria','Secundaria','Media') DEFAULT NULL,
  `jornada` enum('Única','Contraria') DEFAULT NULL,
  `periodo_duracion` int(11) DEFAULT NULL,
  `total_periodos` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asignacion_academica`
--

INSERT INTO `asignacion_academica` (`id_asignacion`, `id_institucion`, `nivel_educativo`, `jornada`, `periodo_duracion`, `total_periodos`) VALUES
(1, 2, 'Primaria', 'Contraria', 50, 32);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evento`
--

CREATE TABLE `evento` (
  `id_evento` int(11) NOT NULL,
  `fecha_evento` date NOT NULL,
  `hora_evento` time NOT NULL,
  `tipo_evento` varchar(100) DEFAULT NULL,
  `asignacion_evento` varchar(255) DEFAULT NULL,
  `categoria_evento` enum('Reunión','Semillero','Club','Capacitación','Otro') DEFAULT 'Otro',
  `id_responsable` int(11) DEFAULT NULL,
  `enlace_recurso` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `institucion`
--

CREATE TABLE `institucion` (
  `id_institucion` int(11) NOT NULL,
  `nombre_institucion` varchar(255) NOT NULL,
  `asignacion_institucion` varchar(255) DEFAULT NULL,
  `direccion_institucion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `institucion`
--

INSERT INTO `institucion` (`id_institucion`, `nombre_institucion`, `asignacion_institucion`, `direccion_institucion`) VALUES
(1, 'Institución de Prueba', NULL, NULL),
(2, 'Colegio Comfandi Calipso', NULL, 'Cali, Colombia');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materia`
--

CREATE TABLE `materia` (
  `id_materia` int(11) NOT NULL,
  `nombre_materia` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `materia`
--

INSERT INTO `materia` (`id_materia`, `nombre_materia`, `descripcion`, `activo`, `fecha_creacion`) VALUES
(1, 'Matemáticas', 'Estudio de números y operaciones', 1, '2025-04-03 20:33:18'),
(2, 'Física', 'Estudio de la materia y energía', 1, '2025-04-03 20:33:18'),
(3, 'Química', 'Estudio de la composición molecular', 1, '2025-04-03 20:33:18'),
(4, 'Literatura', 'Estudio de obras literarias', 1, '2025-04-03 20:33:18'),
(5, 'Tecnología e informática', 'Área de tecnología y herramientas digitales', 1, '2025-04-16 23:23:42'),
(6, 'Cátedra de Paz', 'Formación en resolución de conflictos', 1, '2025-04-16 23:23:42'),
(7, 'Competencia ciudadana', 'Desarrollo de habilidades sociales', 1, '2025-04-16 23:23:42'),
(8, 'Emprendimiento', 'Gestión de proyectos innovadores', 1, '2025-04-16 23:23:42'),
(9, 'Ética y valores', 'Educación en principios morales', 1, '2025-04-16 23:23:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensaje`
--

CREATE TABLE `mensaje` (
  `id_mensaje` int(11) NOT NULL,
  `id_emisor` int(11) NOT NULL,
  `id_receptor` int(11) NOT NULL,
  `contenido_mensaje` text DEFAULT NULL,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  `leido` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mensaje`
--

INSERT INTO `mensaje` (`id_mensaje`, `id_emisor`, `id_receptor`, `contenido_mensaje`, `fecha_envio`, `leido`) VALUES
(2, 3, 4, 'Hola', '2025-05-28 02:31:32', 1),
(4, 3, 4, 'uploads/img_68367783e52737.26627023.webp', '2025-05-28 02:40:03', 1),
(5, 3, 4, 'Hola', '2025-05-28 02:57:16', 1),
(6, 3, 4, 'Hola', '2025-05-28 03:01:16', 1),
(7, 3, 4, 'Hpña', '2025-05-28 03:09:26', 1),
(8, 4, 3, 'Hola', '2025-05-28 03:26:51', 1),
(9, 3, 6, 'Hola', '2025-05-28 03:27:27', 1),
(10, 4, 6, 'Hola', '2025-06-01 00:24:59', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion`
--

CREATE TABLE `notificacion` (
  `id_notificacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo_notificacion` varchar(100) DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `enlace` varchar(255) DEFAULT NULL,
  `fecha_notificacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado_notificacion` enum('leída','no leída') NOT NULL DEFAULT 'no leída'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificacion`
--

INSERT INTO `notificacion` (`id_notificacion`, `id_usuario`, `tipo_notificacion`, `mensaje`, `enlace`, `fecha_notificacion`, `estado_notificacion`) VALUES
(1, 4, 'nueva_tarea_personal', 'Has creado una nueva tarea para ti: PruebaFinal', '/C-Edu/Docente/Tareas asignadas/TareasDetalles.php?id_tarea=7', '2025-05-14 15:55:34', 'leída'),
(2, 6, 'nueva_tarea_asignada', 'Un administrador te ha asignado una nueva tarea: Prueba Definitiva', '/C-Edu/Docente/Tareas asignadas/TareasDetalles.php?id_tarea=8', '2025-05-14 16:00:39', 'leída');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id_rol` int(11) NOT NULL,
  `tipo_rol` enum('Administrador','Maestro') NOT NULL,
  `permisos_rol` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_rol`, `tipo_rol`, `permisos_rol`) VALUES
(0, 'Administrador', NULL),
(1, 'Maestro', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarea`
--

CREATE TABLE `tarea` (
  `id_tarea` int(11) NOT NULL,
  `fecha_inicio_tarea` date DEFAULT NULL,
  `fecha_fin_tarea` date DEFAULT NULL,
  `instruccion_tarea` text DEFAULT NULL,
  `estado_tarea` enum('Pendiente','Completada','Cancelada') NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `prioridad` enum('Alta','Media','Baja') DEFAULT 'Media',
  `porcentaje_avance` int(11) DEFAULT 0,
  `id_asignador` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tarea`
--

INSERT INTO `tarea` (`id_tarea`, `fecha_inicio_tarea`, `fecha_fin_tarea`, `instruccion_tarea`, `estado_tarea`, `id_usuario`, `prioridad`, `porcentaje_avance`, `id_asignador`) VALUES
(2, '2025-05-06', '2025-05-08', 'Tienes que jugar', 'Cancelada', 4, 'Alta', 0, 3),
(3, '2025-05-06', '2025-05-07', 'Revisar horario 9-2', 'Completada', 4, 'Media', 0, 3),
(4, '2025-05-06', '2025-05-15', 'Tengo que Trabajar', 'Pendiente', 4, 'Baja', 0, 4),
(5, '2025-05-06', '2025-05-14', 'Subir notas finales de alumnos', 'Pendiente', 6, 'Alta', 0, 4),
(6, '2025-05-06', '2025-05-17', 'XD', 'Pendiente', 4, 'Alta', 0, 6),
(7, '2025-05-14', '2025-05-15', 'PruebaFinal', 'Completada', 4, 'Media', 0, 4),
(8, '2025-05-14', '2025-05-17', 'Prueba Definitiva', 'Completada', 6, 'Alta', 0, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `email_usuario` varchar(100) NOT NULL,
  `contrasena_usuario` varchar(255) NOT NULL,
  `nombre_usuario` varchar(100) NOT NULL,
  `telefono_usuario` varchar(15) DEFAULT NULL,
  `id_institucion` int(11) DEFAULT NULL,
  `id_rol` int(11) NOT NULL,
  `id_materia` int(11) DEFAULT NULL,
  `grupo_cargo_usuario` varchar(11) DEFAULT NULL,
  `foto_perfil_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `email_usuario`, `contrasena_usuario`, `nombre_usuario`, `telefono_usuario`, `id_institucion`, `id_rol`, `id_materia`, `grupo_cargo_usuario`, `foto_perfil_url`) VALUES
(3, 'admin@cedu.com', '$2y$10$AeM0rWoEu8Ma1H/hs8Xw/uEkndmBrOL9zNI0rNHw.gR9bVxe2sJ4S', 'Administrador Principal', '', 1, 0, NULL, NULL, 'uploads/profile_pictures/user_3_683b842cc4a4b.png'),
(4, 'maestro@cedu.com', '$2y$10$lLNqY/cEfhJyAuXaxhOJ8OyumVrA434f5Ifp1uzTxbz0nKhkhwNpe', 'Maestro Ejemplo', '', 1, 1, NULL, NULL, 'uploads/profile_pictures/user_4_68264a90e9889.png'),
(6, 'juliancho@gmail.com', '$2y$10$vhMQvQXhjPC8Nn3TKUCS4OHj9TjjYSiZhGfKA3t4U/RxnC0w5LJzS', 'Julian Ospina', '2433232323', 1, 1, 4, NULL, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_evento`
--

CREATE TABLE `usuario_evento` (
  `id_usuario` int(11) NOT NULL,
  `id_evento` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asignacion_academica`
--
ALTER TABLE `asignacion_academica`
  ADD PRIMARY KEY (`id_asignacion`),
  ADD KEY `id_institucion` (`id_institucion`);

--
-- Indices de la tabla `evento`
--
ALTER TABLE `evento`
  ADD PRIMARY KEY (`id_evento`),
  ADD KEY `id_responsable` (`id_responsable`);

--
-- Indices de la tabla `institucion`
--
ALTER TABLE `institucion`
  ADD PRIMARY KEY (`id_institucion`);

--
-- Indices de la tabla `materia`
--
ALTER TABLE `materia`
  ADD PRIMARY KEY (`id_materia`);

--
-- Indices de la tabla `mensaje`
--
ALTER TABLE `mensaje`
  ADD PRIMARY KEY (`id_mensaje`),
  ADD KEY `id_emisor` (`id_emisor`),
  ADD KEY `id_receptor` (`id_receptor`);

--
-- Indices de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `idx_id_usuario_estado` (`id_usuario`,`estado_notificacion`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `tarea`
--
ALTER TABLE `tarea`
  ADD PRIMARY KEY (`id_tarea`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_asignador` (`id_asignador`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email_usuario` (`email_usuario`),
  ADD KEY `id_institucion` (`id_institucion`),
  ADD KEY `id_rol` (`id_rol`),
  ADD KEY `id_materia` (`id_materia`);

--
-- Indices de la tabla `usuario_evento`
--
ALTER TABLE `usuario_evento`
  ADD PRIMARY KEY (`id_usuario`,`id_evento`),
  ADD KEY `id_evento` (`id_evento`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asignacion_academica`
--
ALTER TABLE `asignacion_academica`
  MODIFY `id_asignacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `evento`
--
ALTER TABLE `evento`
  MODIFY `id_evento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `institucion`
--
ALTER TABLE `institucion`
  MODIFY `id_institucion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `materia`
--
ALTER TABLE `materia`
  MODIFY `id_materia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `mensaje`
--
ALTER TABLE `mensaje`
  MODIFY `id_mensaje` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tarea`
--
ALTER TABLE `tarea`
  MODIFY `id_tarea` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asignacion_academica`
--
ALTER TABLE `asignacion_academica`
  ADD CONSTRAINT `asignacion_academica_ibfk_1` FOREIGN KEY (`id_institucion`) REFERENCES `institucion` (`id_institucion`);

--
-- Filtros para la tabla `evento`
--
ALTER TABLE `evento`
  ADD CONSTRAINT `evento_ibfk_1` FOREIGN KEY (`id_responsable`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `mensaje`
--
ALTER TABLE `mensaje`
  ADD CONSTRAINT `mensaje_ibfk_1` FOREIGN KEY (`id_emisor`) REFERENCES `usuario` (`id_usuario`),
  ADD CONSTRAINT `mensaje_ibfk_2` FOREIGN KEY (`id_receptor`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD CONSTRAINT `notificacion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tarea`
--
ALTER TABLE `tarea`
  ADD CONSTRAINT `tarea_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`),
  ADD CONSTRAINT `tarea_ibfk_2` FOREIGN KEY (`id_asignador`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_institucion`) REFERENCES `institucion` (`id_institucion`),
  ADD CONSTRAINT `usuario_ibfk_2` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`),
  ADD CONSTRAINT `usuario_ibfk_3` FOREIGN KEY (`id_materia`) REFERENCES `materia` (`id_materia`);

--
-- Filtros para la tabla `usuario_evento`
--
ALTER TABLE `usuario_evento`
  ADD CONSTRAINT `usuario_evento_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`),
  ADD CONSTRAINT `usuario_evento_ibfk_2` FOREIGN KEY (`id_evento`) REFERENCES `evento` (`id_evento`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
