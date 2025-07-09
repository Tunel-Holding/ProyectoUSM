-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 09-07-2025 a las 23:38:40
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `proyectousm`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivos`
--

DROP TABLE IF EXISTS `archivos`;
CREATE TABLE IF NOT EXISTS `archivos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `materia_id` int NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `fecha_subida` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `parcial` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `materia_id` (`materia_id`)
) ENGINE=MyISAM AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `archivos`
--

INSERT INTO `archivos` (`id`, `usuario_id`, `materia_id`, `nombre_archivo`, `ruta_archivo`, `fecha_subida`, `parcial`) VALUES
(71, 6, 2, 'cuaderno-de-ejercicios-y-practicas-php.pdf', 'uploads/cuaderno-de-ejercicios-y-practicas-php.pdf', '2025-01-19 09:42:34', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `datos_usuario`
--

DROP TABLE IF EXISTS `datos_usuario`;
CREATE TABLE IF NOT EXISTS `datos_usuario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `cedula` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `nombres` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `apellidos` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `sexo` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `telefono` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `correo` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `direccion` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

--
-- Volcado de datos para la tabla `datos_usuario`
--

INSERT INTO `datos_usuario` (`id`, `usuario_id`, `cedula`, `nombres`, `apellidos`, `sexo`, `telefono`, `correo`, `direccion`) VALUES
(1, 1, '29989547', 'Tomas Alejandro', 'Reveron Lopez', 'Masculino', '04122884386', 'tomyreveroncito@gmail.com', 'Caricuao'),
(2, 6, '31661441', 'Sebastian Aaron', 'Sanchez Ramirez', 'Masculino', '04122001161', 'sebastiansanchezar3@gmail.com', 'bucare'),
(3, 24, '30395202', 'María Victoria', 'García García', 'Femenino', '04129610038', 'mvggarcia05@gmail.com', 'Baruta');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

DROP TABLE IF EXISTS `estudiantes`;
CREATE TABLE IF NOT EXISTS `estudiantes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `carrera` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `semestre` int NOT NULL,
  `creditosdisponibles` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

--
-- Volcado de datos para la tabla `estudiantes`
--

INSERT INTO `estudiantes` (`id`, `id_usuario`, `carrera`, `semestre`, `creditosdisponibles`) VALUES
(1, 1, 'Ingeniería de Sistemas', 1, 5),
(2, 6, 'Ingeniería de Sistemas', 1, 20),
(3, 24, 'Ingenieria en Sistemas', 1, 1),
(4, 25, 'Ingenieria en Sistemas', 1, 15),
(5, 26, 'Ingenieria en Sistemas', 1, 20),
(6, 27, 'Ingenieria en Sistemas', 1, 20),
(7, 28, 'Ingenieria en Sistemas', 1, 20);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fotousuario`
--

DROP TABLE IF EXISTS `fotousuario`;
CREATE TABLE IF NOT EXISTS `fotousuario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `foto` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `fotousuario`
--

INSERT INTO `fotousuario` (`id`, `id_usuario`, `foto`) VALUES
(4, 1, 'fotoperfil/WhatsApp Image 2025-01-20 at 3.49.33 PM.jpeg'),
(5, 24, 'fotoperfil/1734223601310.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historicoacademico`
--

DROP TABLE IF EXISTS `historicoacademico`;
CREATE TABLE IF NOT EXISTS `historicoacademico` (
  `HistoricoID` int NOT NULL AUTO_INCREMENT,
  `EstudianteID` int DEFAULT NULL,
  `MateriaID` int DEFAULT NULL,
  `Calificacion` decimal(10,0) DEFAULT NULL,
  PRIMARY KEY (`HistoricoID`),
  KEY `EstudianteID` (`EstudianteID`),
  KEY `MateriaID` (`MateriaID`)
) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

--
-- Volcado de datos para la tabla `historicoacademico`
--

INSERT INTO `historicoacademico` (`HistoricoID`, `EstudianteID`, `MateriaID`, `Calificacion`) VALUES
(27, 27, 78, NULL),
(42, 1, 89, 18),
(41, 1, 88, 20),
(40, 1, 86, 16),
(39, 1, 84, 18),
(38, 1, 80, 19),
(37, 1, 89, 18),
(36, 1, 88, 20),
(35, 1, 86, 16),
(34, 1, 84, 18),
(33, 1, 80, 19),
(32, 1, 78, NULL),
(31, 1, 78, NULL),
(30, 1, 78, NULL),
(29, 1, 78, NULL),
(28, 27, 78, NULL),
(43, 24, 80, NULL),
(44, 24, 88, NULL),
(45, 24, 89, 13),
(46, 24, 86, NULL),
(47, 24, 80, NULL),
(48, 24, 88, NULL),
(49, 24, 89, 13),
(50, 24, 86, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

DROP TABLE IF EXISTS `horarios`;
CREATE TABLE IF NOT EXISTS `horarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_estudiante` int DEFAULT NULL,
  `id_materia` int DEFAULT NULL,
  `dia` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_estudiante` (`id_estudiante`),
  KEY `id_materia` (`id_materia`)
) ENGINE=MyISAM AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

--
-- Volcado de datos para la tabla `horarios`
--

INSERT INTO `horarios` (`id`, `id_estudiante`, `id_materia`, `dia`, `hora_inicio`, `hora_fin`) VALUES
(85, 1, 78, 'Jueves', '07:00:00', '08:30:00'),
(84, 1, 78, 'Martes', '07:00:00', '09:15:00'),
(75, 25, 78, 'Jueves', '07:00:00', '08:30:00'),
(74, 25, 78, 'Martes', '07:00:00', '09:15:00'),
(73, 24, 78, 'Jueves', '07:00:00', '08:30:00'),
(72, 24, 78, 'Martes', '07:00:00', '09:15:00'),
(92, 1, 84, 'Martes', '07:00:00', '08:30:00'),
(93, 1, 84, 'Jueves', '07:00:00', '09:15:00'),
(94, 1, 86, 'Miércoles', '10:00:00', '11:30:00'),
(95, 1, 88, 'Viernes', '08:30:00', '10:00:00'),
(96, 1, 89, 'Viernes', '07:00:00', '08:30:00'),
(97, 24, 80, 'Lunes', '07:00:00', '08:30:00'),
(98, 24, 80, 'Miércoles', '07:00:00', '08:30:00'),
(99, 24, 88, 'Viernes', '08:30:00', '10:00:00'),
(102, 24, 89, 'Viernes', '07:00:00', '08:30:00'),
(101, 24, 86, 'Miércoles', '10:00:00', '11:30:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horariosmateria`
--

DROP TABLE IF EXISTS `horariosmateria`;
CREATE TABLE IF NOT EXISTS `horariosmateria` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_materia` int DEFAULT NULL,
  `dia` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_materia` (`id_materia`)
) ENGINE=MyISAM AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

--
-- Volcado de datos para la tabla `horariosmateria`
--

INSERT INTO `horariosmateria` (`id`, `id_materia`, `dia`, `hora_inicio`, `hora_fin`) VALUES
(34, 7, 'Lunes', '14:30:00', '15:15:00'),
(33, 6, 'Viernes', '08:30:00', '10:00:00'),
(29, 5, 'Miércoles', '07:00:00', '08:30:00'),
(28, 5, 'Lunes', '07:00:00', '08:30:00'),
(27, 4, 'Jueves', '08:30:00', '10:00:00'),
(26, 4, 'Martes', '08:30:00', '10:00:00'),
(25, 3, 'Jueves', '07:00:00', '08:30:00'),
(24, 3, 'Martes', '07:00:00', '08:30:00'),
(23, 2, 'Viernes', '08:30:00', '10:00:00'),
(22, 2, 'Miércoles', '08:30:00', '10:00:00'),
(32, 6, 'Jueves', '08:30:00', '10:00:00'),
(31, 6, 'Martes', '08:30:00', '10:00:00'),
(30, 5, 'Viernes', '07:00:00', '08:30:00'),
(21, 2, 'Lunes', '08:30:00', '10:00:00'),
(20, 1, 'Miércoles', '07:00:00', '08:30:00'),
(19, 1, 'Lunes', '07:00:00', '08:30:00'),
(35, 7, 'Miércoles', '14:30:00', '15:15:00'),
(36, 7, 'Viernes', '14:30:00', '15:15:00'),
(37, 78, 'Martes', '07:00:00', '09:15:00'),
(38, 78, 'Jueves', '07:00:00', '08:30:00'),
(39, 79, 'Martes', '07:00:00', '08:30:00'),
(40, 79, 'Viernes', '07:00:00', '09:15:00'),
(41, 80, 'Lunes', '07:00:00', '08:30:00'),
(42, 80, 'Miércoles', '07:00:00', '08:30:00'),
(43, 81, 'Lunes', '07:00:00', '08:30:00'),
(44, 81, 'Jueves', '07:00:00', '08:30:00'),
(45, 82, 'Lunes', '07:00:00', '08:30:00'),
(46, 82, 'Martes', '07:00:00', '08:30:00'),
(47, 83, 'Martes', '08:30:00', '10:00:00'),
(48, 83, 'Miércoles', '08:30:00', '10:00:00'),
(57, 85, 'Lunes', '00:00:00', '00:00:00'),
(56, 84, '', '00:00:00', '00:00:00'),
(54, 88, 'Viernes', '08:30:00', '10:00:00'),
(53, 86, 'Miércoles', '10:00:00', '11:30:00'),
(55, 89, 'Viernes', '07:00:00', '08:30:00'),
(58, 85, 'Lunes', '00:00:00', '00:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripciones`
--

DROP TABLE IF EXISTS `inscripciones`;
CREATE TABLE IF NOT EXISTS `inscripciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_estudiante` int NOT NULL,
  `id_materia` int NOT NULL,
  `fecha_inscripcion` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_estudiante` (`id_estudiante`),
  KEY `id_materia` (`id_materia`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

--
-- Volcado de datos para la tabla `inscripciones`
--

INSERT INTO `inscripciones` (`id`, `id_estudiante`, `id_materia`, `fecha_inscripcion`) VALUES
(30, 25, 78, '2025-01-16'),
(35, 1, 78, '2025-01-17'),
(29, 24, 78, '2025-01-16'),
(39, 1, 84, '2025-01-18'),
(40, 1, 86, '2025-01-18'),
(41, 1, 88, '2025-01-18'),
(42, 1, 89, '2025-01-18'),
(43, 24, 80, '2025-01-20'),
(44, 24, 88, '2025-01-20'),
(47, 24, 89, '2025-01-20'),
(46, 24, 86, '2025-01-20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias`
--

DROP TABLE IF EXISTS `materias`;
CREATE TABLE IF NOT EXISTS `materias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `salon` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `id_profesor` int DEFAULT NULL,
  `creditos` int NOT NULL,
  `semestre` int NOT NULL,
  `seccion` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_profesor` (`id_profesor`)
) ENGINE=MyISAM AUTO_INCREMENT=90 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

--
-- Volcado de datos para la tabla `materias`
--

INSERT INTO `materias` (`id`, `nombre`, `salon`, `id_profesor`, `creditos`, `semestre`, `seccion`) VALUES
(84, 'Herramientas de Apoyo', '902', 2, 2, 1, 'A'),
(83, 'Matematicas 1', '702', 3, 5, 1, 'D'),
(80, 'Matematicas 1', '902', 1, 5, 1, 'A'),
(81, 'Matematicas 1', '902', 4, 5, 1, 'B'),
(82, 'Matematicas 1', '901', 2, 5, 1, 'C'),
(85, 'Herramientas de Apoyo', '902', NULL, 2, 1, 'B'),
(86, 'Fisica 1', '901', 1, 5, 1, 'A'),
(87, 'Fisica 1', '901', NULL, 5, 1, 'B'),
(88, 'Lenguaje y Comunicacion', '702', 1, 2, 1, 'A'),
(89, 'Ingles 1', '702', 1, 2, 1, 'A');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `message` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `group_id` int DEFAULT NULL,
  `tipo` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `reply_to` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=150 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

--
-- Volcado de datos para la tabla `messages`
--

INSERT INTO `messages` (`id`, `user_id`, `message`, `created_at`, `group_id`, `tipo`, `reply_to`) VALUES
(148, 5, 'opa', '2025-01-19 03:44:57', 80, 'texto', '0'),
(147, 1, 'Gracias profe', '2025-01-19 02:50:58', 80, 'texto', '146'),
(146, 5, '9:00am, sean puntuales', '2025-01-19 02:50:47', 80, 'texto', '144'),
(145, 5, 'buenas noches', '2025-01-19 02:50:26', 80, 'texto', '0'),
(144, 1, 'A que hora seria el examen mañana?', '2025-01-19 02:49:53', 80, 'texto', '0'),
(143, 1, 'Hola profe, buenas noches', '2025-01-19 02:49:35', 80, 'texto', '0'),
(149, 1, 'hola', '2025-07-09 19:18:01', 84, 'texto', '0');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas`
--

DROP TABLE IF EXISTS `notas`;
CREATE TABLE IF NOT EXISTS `notas` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `Parcial1` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci DEFAULT NULL,
  `Parcial2` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci DEFAULT NULL,
  `Parcial3` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci DEFAULT NULL,
  `Parcial4` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci DEFAULT NULL,
  `Final` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci DEFAULT NULL,
  `materia_id` int DEFAULT NULL,
  `semestre` int DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=203 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

--
-- Volcado de datos para la tabla `notas`
--

INSERT INTO `notas` (`Id`, `usuario_id`, `Parcial1`, `Parcial2`, `Parcial3`, `Parcial4`, `Final`, `materia_id`, `semestre`) VALUES
(190, 1, '18', '19', '18', '18', '18', 78, 1),
(184, 24, NULL, NULL, NULL, NULL, NULL, 78, 1),
(185, 25, NULL, NULL, NULL, NULL, NULL, 78, 1),
(194, 1, '18', '18', '18', '18', '18', 84, 1),
(195, 1, '15', '17', '15', '16', '16', 86, 1),
(196, 1, '20', '07', '19', '19', '20', 88, 1),
(197, 1, '18', '17', '19', '17', '18', 89, 1),
(198, 24, '20', '20', '20', '20', '20', 80, 1),
(199, 24, NULL, NULL, NULL, NULL, NULL, 88, 1),
(202, 24, '12', '5', '18', '18', '13', 89, 1),
(201, 24, NULL, NULL, NULL, NULL, NULL, 86, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesores`
--

DROP TABLE IF EXISTS `profesores`;
CREATE TABLE IF NOT EXISTS `profesores` (
  `id` int NOT NULL,
  `id_usuario` int DEFAULT NULL,
  `nombre` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

--
-- Volcado de datos para la tabla `profesores`
--

INSERT INTO `profesores` (`id`, `id_usuario`, `nombre`) VALUES
(1, 5, 'Angel Cepeda'),
(2, 4, 'Hector Hurtado'),
(3, 6, 'Sebastian Sanchez'),
(4, 7, 'Saul Mendoza'),
(0, 29, 'Tomas Reveron');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre_usuario` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `contrasena` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `nivel_usuario` enum('administrador','profesor','usuario') CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_usuario`, `email`, `contrasena`, `nivel_usuario`) VALUES
(1, 'TReveron', 'tomyreveroncito@gmail.com', '$2y$10$MjHASuz/bqzOFoh5Jqe2M.rqKfUmTSf9L//6YRLTVFaholnfZ9MKm', 'usuario'),
(25, 'Daniela', 'daniela.aleja2021@gmail.com', '$2y$10$PhXZBCya4WmmRuC4JrTex.p5hmhh5Zi9o25EiWgdb7x/K2cCU4/kq', 'usuario'),
(24, 'Mvicky0505', 'mvggarcia05@gmail.com', '$2y$10$8JCIh4b6v825FwFfeLHrjumhmgMDsS4V7Eo2pVGwyXIjQRNNQtwl6', 'administrador'),
(5, 'ACepeda', 'angelcepeda@gmail.com', '$2y$10$G8fS6qQCpqXyfJ/US95Sl.MGqtwSD4jQ1oymMoYrLcqkEfvDiNEKm', 'profesor'),
(6, 'Sebastian', 'sebastiansanchezar3@gmail.com', '$2y$10$pKa.Q0FKgIjEDG/lHHeaz./Al8.FD0h5iT9ZekPugh5S37rNobuDO', 'usuario'),
(27, 'Marivgc19', 'mvgc1133@gmail.com', '$2y$10$vtm/d2SqSwxSIiS.p00hPej7UBUBg3SCQwKS47KtzbzV5RDq.I32O', 'usuario'),
(28, 'Jpipi', 'janpipi@gmail.com', '$2y$10$dfQJGv63KFl9irf8NC04ZeiMddn/buoDgFHGW0lnTduyhJ9nRmfI.', 'administrador'),
(29, 'Tprofesor', 'reveron29989547@gmail.com', '$2y$10$so7NSqdekje5ACdgRJa5P.n3v2FhqwTsFYnLZnH72RJtyO/uVy1dm', 'profesor');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
