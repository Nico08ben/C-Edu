-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-06-2025 a las 04:10:31
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
-- Estructura de tabla para la tabla `evento`
--

CREATE TABLE `evento` (
  `id_evento` int(11) NOT NULL,
  `fecha_evento` date NOT NULL,
  `hora_evento` time NOT NULL,
  `fecha_fin_evento` date DEFAULT NULL,
  `hora_fin_evento` time DEFAULT NULL,
  `titulo_evento` varchar(100) DEFAULT NULL,
  `descripcion_evento` text DEFAULT NULL,
  `categoria_evento` enum('Reunión','Semillero','Club','Capacitación','Otro') DEFAULT 'Otro',
  `id_responsable` int(11) DEFAULT NULL,
  `color_evento` varchar(20) DEFAULT NULL COMMENT 'Color del evento en formato hexadecimal o nombre de color',
  `enlace_recurso` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `evento`
--

INSERT INTO `evento` (`id_evento`, `fecha_evento`, `hora_evento`, `fecha_fin_evento`, `hora_fin_evento`, `titulo_evento`, `descripcion_evento`, `categoria_evento`, `id_responsable`, `color_evento`, `enlace_recurso`) VALUES
(1, '2025-06-10', '14:00:00', '2025-06-10', '16:00:00', 'Capacitación: Nuevas Herramientas Digitales', 'Capacitación sobre el uso de la plataforma educativa para todos los docentes.', 'Capacitación', 4, '#2a9d8f', NULL),
(2, '2025-06-12', '10:00:00', '2025-06-12', '11:30:00', 'Club de Lectura: Cien Años de Soledad', 'Discusión sobre la obra de Gabriel García Márquez. Abierto a todos los grados.', 'Club', 6, '#e9c46a', NULL),
(3, '2025-06-15', '08:00:00', '2025-06-15', '09:00:00', 'Reunión de Área de Ciencias', 'Reunión para planificar las actividades del laboratorio para el segundo semestre.', 'Reunión', 1, '#f4a261', NULL),
(4, '2025-06-20', '09:00:00', '2025-06-21', '13:00:00', 'Olimpiadas de Matemáticas', 'Competencia de matemáticas para los grados 10 y 11.', 'Otro', 5, '#e76f51', 'https://ejemplo.com/olimpiadas'),
(5, '2025-07-05', '11:00:00', '2025-07-05', '12:30:00', 'Taller de Ética Profesional', 'Taller enfocado en la resolución de conflictos en el aula.', 'Capacitación', 8, '#264653', NULL);

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
(1, 4, 5, 'Hola Carlos, ¿cómo vas con la preparación para las olimpiadas?', '2025-06-08 15:15:00', 1),
(2, 5, 4, '¡Hola Ana! Todo en orden. Un poco estresado pero avanzando. ¿Necesitas ayuda con la capacitación?', '2025-06-08 15:17:21', 1),
(3, 4, 5, 'Por ahora todo bien, gracias! Cualquier cosa te aviso.', '2025-06-08 15:18:05', 0),
(4, 5, 4, 'Perfecto. ¡Éxitos!', '2025-06-08 15:18:45', 0),
(5, 1, 3, 'Julian, por favor no olvides que la tarea \"Prueba Definitiva\" ya fue completada. Excelente trabajo.', '2025-06-09 16:30:10', 1),
(6, 3, 1, 'Entendido, Administrador. Gracias por la confirmación.', '2025-06-09 16:32:45', 1),
(7, 6, 7, 'David, ¿te gustaría participar como invitado en el club de lectura? Podríamos leer algo de divulgación científica.', '2025-06-10 14:05:12', 1),
(8, 7, 6, '¡Suena genial, Beatriz! Me encantaría. Avísame qué libro eligen para prepararme.', '2025-06-10 14:07:33', 1),
(9, 6, 7, 'Excelente, te mantendré informado. Estaba pensando en \"Cosmos\" de Carl Sagan.', '2025-06-10 14:08:15', 0),
(10, 1, 2, 'Maestro Ejemplo, recuerda que tienes pendiente el reporte de asistencia semestral. La fecha límite es el 15 de julio.', '2025-07-02 20:00:00', 1),
(11, 2, 1, 'Recibido, Administrador. Ya estoy trabajando en ello.', '2025-07-02 20:02:11', 1),
(12, 5, 7, 'Colega, ¿me podrías prestar el proyector para la clase de mañana?', '2025-06-11 21:21:54', 1),
(13, 7, 5, 'Claro, pasa por la sala de profesores a primera hora.', '2025-06-11 21:30:02', 0);

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
(1, '2025-06-08', '2025-06-09', 'Preparar material para capacitación de herramientas digitales.', 'Pendiente', 4, 'Alta', 25, 1),
(2, '2025-06-09', '2025-06-18', 'Calificar exámenes del primer corte de grado 11-B.', 'Pendiente', 5, 'Media', 0, 5),
(3, '2025-06-02', '2025-06-05', 'Organizar laboratorio de física para práctica sobre electromagnetismo.', 'Completada', 7, 'Alta', 100, 1),
(4, '2025-06-10', '2025-06-25', 'Revisar ensayos de literatura del grado 8-C.', 'Pendiente', 3, 'Baja', 0, 3),
(5, '2025-05-28', '2025-06-01', 'Seleccionar próxima lectura para el club.', 'Completada', 6, 'Media', 100, 6),
(6, '2025-07-01', '2025-07-15', 'Entregar reporte de asistencia semestral consolidado.', 'Pendiente', 2, 'Alta', 10, 1),
(7, '2025-07-10', '2025-07-30', 'Diseñar el plan de estudios de Ética para el próximo año lectivo.', 'Pendiente', 8, 'Media', 0, 1);

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
(1, 'admin@cedu.com', '$2y$10$AeM0rWoEu8Ma1H/hs8Xw/uEkndmBrOL9zNI0rNHw.gR9bVxe2sJ4S', 'Administrador Principal', '', 1, 0, 1, NULL, 'uploads/profile_pictures/user_3_683b842cc4a4b.png'),
(2, 'maestro@cedu.com', '$2y$10$lLNqY/cEfhJyAuXaxhOJ8OyumVrA434f5Ifp1uzTxbz0nKhkhwNpe', 'Maestro Ejemplo', '', 1, 1, NULL, NULL, 'uploads/profile_pictures/user_4_68264a90e9889.png'),
(3, 'juliancho@gmail.com', '$2y$10$vhMQvQXhjPC8Nn3TKUCS4OHj9TjjYSiZhGfKA3t4U/RxnC0w5LJzS', 'Julian Ospina', '2433232323', 1, 1, 4, NULL, ''),
(4, 'ana.rodriguez@cedu.com', '$2y$10$Goc15f.UFomqxrIPjBCnb.VtQQK2tkNNm3cRqHTw8rP9QUHoJpbbe', 'Ana Rodriguez', '3101234567', 2, 1, 5, 'Grado 10-A', 'uploads/profile_pictures/user_4_6844ef11c35f1.webp'),
(5, 'carlos.sanchez@cedu.com', '$2y$10$4q8oMlkBLY6jqvnkuSc4sOYcDJbPPEe5eqqnbwL5A6yxp0zcN0uFC', 'Carlos Sanchez', '3118901234', 2, 1, 1, 'Grado 11-B', 'uploads/profile_pictures/user_5_6844ee7b2e92b.webp'),
(6, 'beatriz.flores@cedu.com', '$2y$10$uwEHJ1KXGCGsNnNk6BEoDOSFtboO3GrgzblQ6MUr9TWu4WqHDfQue', 'Beatriz Flores', '3125678901', 1, 1, 4, 'Grado 8-C', 'uploads/profile_pictures/user_6_6844eea4dc3c8.webp'),
(7, 'david.mendoza@cedu.com', '$2y$10$ea5jy1Lm6vW8fYxiBiWBAu9C3SWC2z17UDaJbITVC786hVgumUHWm', 'David Mendoza', '3132345678', 2, 1, 2, 'Grado 9-A', 'uploads/profile_pictures/user_7_6844eec1e9129.webp'),
(8, 'elena.gomez@cedu.com', '$2y$10$GDFyBa5aBX446HkTwwMuQ.XyBx8tG1Fzb4ZNCTTCiOztuNZdcuCai', 'Elena Gomez', '3149012345', 1, 1, 9, 'Grado 7-B', 'uploads/profile_pictures/user_8_6844eedf5bea4.webp');

--
-- Índices para tablas volcadas
--

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
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `evento`
--
ALTER TABLE `evento`
  MODIFY `id_evento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id_mensaje` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tarea`
--
ALTER TABLE `tarea`
  MODIFY `id_tarea` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
