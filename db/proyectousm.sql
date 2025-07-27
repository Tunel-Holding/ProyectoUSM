-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 27-07-2025 a las 13:55:37
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
  `descripcion` text,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `materia_id` (`materia_id`)
) ENGINE=MyISAM AUTO_INCREMENT=119 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `archivos`
--

INSERT INTO `archivos` (`id`, `usuario_id`, `materia_id`, `nombre_archivo`, `ruta_archivo`, `fecha_subida`, `parcial`, `descripcion`) VALUES
(71, 6, 2, 'cuaderno-de-ejercicios-y-practicas-php.pdf', 'uploads/cuaderno-de-ejercicios-y-practicas-php.pdf', '2025-01-19 09:42:34', 1, NULL),
(117, 33, 90, 'uploads/68862604d883a_material.png', '', '2025-07-27 13:13:40', NULL, 'hola como andan tonotos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios`
--

DROP TABLE IF EXISTS `comentarios`;
CREATE TABLE IF NOT EXISTS `comentarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `archivo_id` int NOT NULL,
  `id_publicacion` int NOT NULL,
  `id_usuario` int NOT NULL,
  `comentario` text COLLATE utf8mb4_general_ci NOT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_comentario_padre` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_publicacion` (`id_publicacion`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=MyISAM AUTO_INCREMENT=138 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `comentarios`
--

INSERT INTO `comentarios` (`id`, `archivo_id`, `id_publicacion`, `id_usuario`, `comentario`, `fecha`, `id_comentario_padre`) VALUES
(137, 117, 0, 33, 'ojal ser feliz', '2025-07-27 09:46:51', 132),
(132, 117, 0, 33, 'hola soy la profe', '2025-07-27 09:13:53', NULL),
(133, 117, 0, 33, 'puedo responderme', '2025-07-27 09:14:00', 132);

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
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

--
-- Volcado de datos para la tabla `datos_usuario`
--

INSERT INTO `datos_usuario` (`id`, `usuario_id`, `cedula`, `nombres`, `apellidos`, `sexo`, `telefono`, `correo`, `direccion`) VALUES
(1, 1, '29989547', 'Tomas Alejandro', 'Reveron Lopez', 'Masculino', '04122884386', 'tomyreveroncito@gmail.com', 'Caricuao'),
(2, 6, '31661441', 'Sebastian Aaron', 'Sanchez Ramirez', 'Masculino', '04122001161', 'sebastiansanchezar3@gmail.com', 'bucare'),
(3, 24, '30395202', 'María Victoria', 'García García', 'Femenino', '04129610038', 'mvggarcia05@gmail.com', 'Baruta'),
(6, 33, '299990008', 'Jade', 'Moya', 'Masculino', '4142145277', 'jademoya0101@gmail.com', 'name'),
(5, 31, '30542565', 'Andrea', 'Mejia', 'Femenino', '0412701095', 'avictoria1501@gmail.com', 'fff'),
(7, 34, '29990008', 'Jade', 'Moya', 'Masculino', '01621626218278217821', 'jademoya0101@gmail.com', 'name'),
(8, 35, '29990008', 'Jade', 'Moya', 'Femenino', '04142145277', 'jademoya04@gmail.com', 'el paraiso');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entregas_tareas`
--

DROP TABLE IF EXISTS `entregas_tareas`;
CREATE TABLE IF NOT EXISTS `entregas_tareas` (
  `id_entrega` int NOT NULL AUTO_INCREMENT,
  `id_tarea` int NOT NULL,
  `id_alumno` int NOT NULL,
  `archivo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_entrega` datetime NOT NULL,
  `calificacion` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `retroalimentacion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `estado` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'entregado',
  PRIMARY KEY (`id_entrega`),
  KEY `id_tarea` (`id_tarea`),
  KEY `id_alumno` (`id_alumno`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

DROP TABLE IF EXISTS `estudiantes`;
CREATE TABLE IF NOT EXISTS `estudiantes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `cedula` int DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `carrera` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `semestre` int NOT NULL,
  `creditosdisponibles` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

--
-- Volcado de datos para la tabla `estudiantes`
--

INSERT INTO `estudiantes` (`id`, `id_usuario`, `cedula`, `email`, `carrera`, `semestre`, `creditosdisponibles`) VALUES
(1, 1, 165712321, '', 'Ingeniería de Sistemas', 1, 20),
(2, 6, NULL, '', 'Ingeniería de Sistemas', 1, 20),
(3, 24, NULL, '', 'Ingenieria en Sistemas', 1, 1),
(4, 25, NULL, '', 'Ingenieria en Sistemas', 1, 15),
(5, 26, NULL, '', 'Ingenieria en Sistemas', 1, 20),
(6, 27, NULL, '', 'Ingenieria en Sistemas', 1, 20),
(7, 28, NULL, '', 'Ingenieria en Sistemas', 1, 20),
(8, 31, 30542565, 'avictoria1501@gmail.com', 'Ingenieria en Sistemas', 1, 18),
(9, 34, NULL, '', 'Ingenieria en Sistemas', 1, 0),
(10, 35, NULL, '', 'Ingenieria en Sistemas', 1, 20);

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
) ENGINE=MyISAM AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

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
(50, 24, 86, NULL),
(51, 1, 84, 18),
(52, 1, 86, 16),
(53, 1, 88, 20),
(54, 1, 89, 18),
(55, 1, 84, 18),
(56, 1, 86, 16),
(57, 1, 88, 20),
(58, 1, 89, 18),
(59, 1, 84, 18),
(60, 1, 86, 16),
(61, 1, 88, 20),
(62, 1, 89, 18),
(63, 1, 84, 18),
(64, 1, 86, 16),
(65, 1, 88, 20),
(66, 1, 89, 18),
(67, 1, 90, NULL),
(68, 1, 84, 18),
(69, 1, 86, 16),
(70, 1, 88, 20),
(71, 1, 89, 18),
(72, 1, 90, NULL),
(73, 1, 84, 18),
(74, 1, 86, 16),
(75, 1, 88, 20),
(76, 1, 89, 18),
(77, 1, 90, NULL),
(78, 1, 84, 18),
(79, 1, 86, 16),
(80, 1, 88, 20),
(81, 1, 89, 18);

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
) ENGINE=MyISAM AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

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
(103, 1, 90, 'Lunes', '07:00:00', '14:00:00'),
(92, 1, 84, 'Martes', '07:00:00', '08:30:00'),
(93, 1, 84, 'Jueves', '07:00:00', '09:15:00'),
(94, 1, 86, 'Miércoles', '10:00:00', '11:30:00'),
(95, 1, 88, 'Viernes', '08:30:00', '10:00:00'),
(96, 1, 89, 'Viernes', '07:00:00', '08:30:00'),
(97, 24, 80, 'Lunes', '07:00:00', '08:30:00'),
(98, 24, 80, 'Miércoles', '07:00:00', '08:30:00'),
(99, 24, 88, 'Viernes', '08:30:00', '10:00:00'),
(102, 24, 89, 'Viernes', '07:00:00', '08:30:00'),
(101, 24, 86, 'Miércoles', '10:00:00', '11:30:00'),
(104, 31, 84, '', '00:00:00', '00:00:00'),
(105, 34, 90, 'Jueves', '01:45:00', '21:00:00');

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
) ENGINE=MyISAM AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

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
(54, 88, 'Viernes', '08:30:00', '10:00:00'),
(53, 86, 'Miércoles', '10:00:00', '11:30:00'),
(55, 89, 'Viernes', '07:00:00', '08:30:00'),
(59, 90, 'Jueves', '01:45:00', '21:00:00'),
(66, 84, 'Lunes', '11:00:00', '12:00:00'),
(68, 84, 'Miércoles', '09:41:00', '11:00:00');

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
) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

--
-- Volcado de datos para la tabla `inscripciones`
--

INSERT INTO `inscripciones` (`id`, `id_estudiante`, `id_materia`, `fecha_inscripcion`) VALUES
(30, 25, 78, '2025-01-16'),
(35, 1, 78, '2025-01-17'),
(29, 24, 78, '2025-01-16'),
(48, 1, 90, '2025-07-09'),
(39, 1, 85, '2025-01-18'),
(40, 1, 87, '2025-01-18'),
(41, 1, 88, '2025-01-18'),
(42, 1, 89, '2025-01-18'),
(43, 24, 80, '2025-01-20'),
(44, 24, 88, '2025-01-20'),
(47, 24, 89, '2025-01-20'),
(46, 24, 86, '2025-01-20'),
(49, 31, 84, '2025-07-12'),
(50, 34, 90, '2025-07-15');

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
) ENGINE=MyISAM AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

--
-- Volcado de datos para la tabla `materias`
--

INSERT INTO `materias` (`id`, `nombre`, `salon`, `id_profesor`, `creditos`, `semestre`, `seccion`) VALUES
(84, 'Herramientas de Apoyo', '902', 1, 2, 1, 'A'),
(83, 'Matematicas 1', '702', NULL, 5, 1, 'D'),
(80, 'Matematicas 1', '902', 8, 5, 1, 'A'),
(81, 'Matematicas 1', '902', 4, 5, 1, 'B'),
(82, 'Matematicas 1', '901', 8, 5, 1, 'C'),
(85, 'Herramientas de Apoyo', '902', 2, 2, 1, 'B'),
(86, 'Fisica 1', '901', 1, 5, 1, 'A'),
(87, 'Fisica 1', '901', 6, 5, 1, 'B'),
(88, 'Lenguaje y Comunicacion', '702', NULL, 2, 1, 'A'),
(89, 'Ingles 1', '702', 5, 2, 1, 'A'),
(90, 'Daniela Garcia', '902', 8, 20, 1, 'A'),
(91, 'Daniela Garcia', '902', 3, 20, 1, 'B');

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
) ENGINE=MyISAM AUTO_INCREMENT=165 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

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
(149, 1, 'hola', '2025-07-09 19:18:01', 84, 'texto', '0'),
(150, 1, 'dasdas', '2025-07-10 02:27:02', 88, 'texto', '0'),
(151, 29, 'hola', '2025-07-10 02:56:15', 87, 'texto', '0'),
(152, 29, 'dasdasdasda', '2025-07-10 17:49:10', 87, 'texto', '0'),
(153, 29, 'adsdasd', '2025-07-10 17:49:11', 87, 'texto', '0'),
(154, 29, 'asda', '2025-07-10 17:49:11', 87, 'texto', '0'),
(155, 29, 'sd', '2025-07-10 17:49:12', 87, 'texto', '0'),
(156, 29, 'asd', '2025-07-10 17:49:13', 87, 'texto', '0'),
(157, 29, 'asdas', '2025-07-10 18:33:55', 87, 'texto', '0'),
(162, 31, 'km', '2025-07-13 18:16:33', 84, 'texto', '149'),
(161, 31, 'uploads/6872a5595fc63_1752343897.jpg', '2025-07-12 18:11:37', 84, 'imagen', '0'),
(163, 34, 'hola', '2025-07-22 15:50:45', 90, 'texto', '0'),
(164, 34, 'uploads/menu.png', '2025-07-22 16:36:23', 90, 'imagen', '0');

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
) ENGINE=MyISAM AUTO_INCREMENT=206 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

--
-- Volcado de datos para la tabla `notas`
--

INSERT INTO `notas` (`Id`, `usuario_id`, `Parcial1`, `Parcial2`, `Parcial3`, `Parcial4`, `Final`, `materia_id`, `semestre`) VALUES
(190, 1, '18', '19', '18', '18', '18', 78, 1),
(184, 24, NULL, NULL, NULL, NULL, NULL, 78, 1),
(185, 25, NULL, NULL, NULL, NULL, NULL, 78, 1),
(203, 1, NULL, NULL, NULL, NULL, NULL, 90, 1),
(194, 1, '18', '18', '18', '18', '18', 84, 1),
(195, 1, '15', '17', '15', '16', '16', 86, 1),
(196, 1, '20', '07', '19', '19', '20', 88, 1),
(197, 1, '18', '17', '19', '17', '18', 89, 1),
(198, 24, '20', '20', '20', '20', '20', 80, 1),
(199, 24, NULL, NULL, NULL, NULL, NULL, 88, 1),
(202, 24, '12', '5', '18', '18', '13', 89, 1),
(201, 24, NULL, NULL, NULL, NULL, NULL, 86, 1),
(204, 31, NULL, NULL, NULL, NULL, NULL, 84, 1),
(205, 34, NULL, NULL, NULL, NULL, NULL, 90, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesores`
--

DROP TABLE IF EXISTS `profesores`;
CREATE TABLE IF NOT EXISTS `profesores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int DEFAULT NULL,
  `cedula` int NOT NULL,
  `nombre` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `material` varchar(255) COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

--
-- Volcado de datos para la tabla `profesores`
--

INSERT INTO `profesores` (`id`, `id_usuario`, `cedula`, `nombre`, `material`) VALUES
(1, 5, 0, 'Angel Cepeda', NULL),
(2, 4, 0, 'Hector Hurtado', NULL),
(3, 6, 0, 'Sebastian Sanchez', NULL),
(4, 7, 0, 'Saul Mendoza', NULL),
(5, 29, 29989547, 'Tomas Reveron', NULL),
(6, 30, 0, 'Daniela Garcia', NULL),
(8, 33, 0, 'Jade', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `publicaciones`
--

DROP TABLE IF EXISTS `publicaciones`;
CREATE TABLE IF NOT EXISTS `publicaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_profesor` int NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `contenido` text COLLATE utf8mb4_general_ci,
  `archivo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_profesor` (`id_profesor`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas`
--

DROP TABLE IF EXISTS `tareas`;
CREATE TABLE IF NOT EXISTS `tareas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_materia` int NOT NULL,
  `titulo_tarea` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `categoria` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_entrega` date NOT NULL,
  `hora_entrega` time NOT NULL,
  `descripcion` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tareas`
--

INSERT INTO `tareas` (`id`, `id_materia`, `titulo_tarea`, `categoria`, `fecha_entrega`, `hora_entrega`, `descripcion`) VALUES
(2, 87, 'cacota', 'examen', '2025-07-10', '17:15:00', 'cacota mucha'),
(3, 87, 'fortnite', 'examen', '2025-07-11', '02:00:00', 'modo construccion solo para pros nada de noobs dribling pared'),
(5, 87, 'TOCAR A LIVIO', 'examen', '2025-07-12', '03:33:00', 'tocar a livio mucho mucho mucho mucho mucho mucho mucho mucho mucho mucho mucho mucho mucho demasiado');

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
  `session` tinyint(1) NOT NULL DEFAULT '0',
  `codigo_recuperacion` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_usuario`, `email`, `contrasena`, `nivel_usuario`, `session`, `codigo_recuperacion`) VALUES
(1, 'TReveron', 'tomyreveroncito@gmail.com', '$2y$10$MjHASuz/bqzOFoh5Jqe2M.rqKfUmTSf9L//6YRLTVFaholnfZ9MKm', 'usuario', 0, ''),
(25, 'Daniela', 'daniela.aleja2021@gmail.com', '$2y$10$PhXZBCya4WmmRuC4JrTex.p5hmhh5Zi9o25EiWgdb7x/K2cCU4/kq', 'usuario', 0, ''),
(24, 'Mvicky0505', 'mvggarcia05@gmail.com', '$2y$10$8JCIh4b6v825FwFfeLHrjumhmgMDsS4V7Eo2pVGwyXIjQRNNQtwl6', 'administrador', 0, ''),
(5, 'ACepeda', 'angelcepeda@gmail.com', '$2y$10$G8fS6qQCpqXyfJ/US95Sl.MGqtwSD4jQ1oymMoYrLcqkEfvDiNEKm', 'profesor', 0, ''),
(6, 'Sebastian', 'sebastiansanchezar3@gmail.com', '$2y$10$pKa.Q0FKgIjEDG/lHHeaz./Al8.FD0h5iT9ZekPugh5S37rNobuDO', 'usuario', 0, ''),
(27, 'Marivgc19', 'mvgc1133@gmail.com', '$2y$10$vtm/d2SqSwxSIiS.p00hPej7UBUBg3SCQwKS47KtzbzV5RDq.I32O', 'usuario', 0, ''),
(28, 'Jpipi', 'janpipi@gmail.com', '$2y$10$dfQJGv63KFl9irf8NC04ZeiMddn/buoDgFHGW0lnTduyhJ9nRmfI.', 'administrador', 0, ''),
(29, 'Tprofesor', 'reveron29989547@usm.edu.ve', '$2y$10$WMsYx2.0SweZ9QFFhCvFuOVkHOT//2wfMQNnv/alJSVqvSmPa8SDm', 'profesor', 0, ''),
(30, 'DGarcia', 'danielagarciaprof@gmail.com', '$2y$10$l2QfBJE/NbuwJJ3VYqR6DuWlbiaEeze0oTaM7fQqoH0LF66GsSN.u', 'profesor', 0, ''),
(31, 'andreamejia', 'avictoria1501@gmail.com', '$2y$10$qhyCJhavTPoEvfcYTYI19ubu0z8Bpb4HP8DwuXX.BGkEZD92ZZzPy', 'administrador', 0, ''),
(33, 'Jade3', 'jademoya06@gmail.com', '$2y$10$2WCgq/ksLjKZ6AQ0gbAYVuvTOHmLK/Z/xR2EHOJ7xhuTGh2S1FIjm', 'profesor', 0, ''),
(34, 'jade', 'jademoya0606@gmail.com', '$2y$10$TWbPllt3ZlK9MFoCDnsjTOwQbqz2BtXwEOXziAvhFMOrUQz5.7bYS', 'administrador', 0, ''),
(35, 'jadepro', 'jademoya04@gmail.com', '$2y$10$eVTEgum4zhDNU4k/9NtVUO.xBbxBYeMGE8ydkK1OdcqtPbDQuJ2sW', 'usuario', 0, '');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
