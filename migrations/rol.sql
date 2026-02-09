-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-02-2026 a las 18:39:51
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
-- Base de datos: `rol`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habilidades`
--

CREATE TABLE `habilidades` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `habilidades`
--

INSERT INTO `habilidades` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Estática', 'Puede paralizar al atacante.'),
(2, 'Sintonía', 'Copia el tipo del ataque recibido.'),
(3, 'Torrente', 'Aumenta ataque de tipo agua cuando tiene bajo HP.'),
(4, 'Sobrecarga', 'Aumenta ataque especial y velocidad en lluvia.'),
(5, 'Marcha acuática', 'Aumenta velocidad en lluvia.'),
(6, 'Intimidación', 'Reduce el ataque del enemigo al entrar.'),
(7, 'Competencia', 'Iguala el ataque especial del rival.'),
(8, 'Absorción', 'Absorbe HP del enemigo.'),
(9, 'Rivalidad', 'Hace más daño al mismo género.'),
(10, 'Premonición', 'Siente el movimiento del rival.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inventario`
--

INSERT INTO `inventario` (`user_id`, `item_id`, `cantidad`) VALUES
(4, 1, 10),
(4, 2, 3),
(4, 3, 10),
(4, 4, 1),
(4, 5, 4),
(4, 6, 5),
(11, 4, 4),
(68, 1, 7),
(68, 2, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `clave` varchar(100) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `icono` varchar(100) DEFAULT NULL,
  `effect_type` varchar(50) DEFAULT NULL,
  `effect_value` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `items`
--

INSERT INTO `items` (`id`, `clave`, `nombre`, `descripcion`, `icono`, `effect_type`, `effect_value`, `price`) VALUES
(1, 'potion', 'Poción', 'Restaura 20 HP', 'potion.png', 'Curación', 20, 300.00),
(2, 'superpotion', 'Super Poción', 'Restaura 50 HP', 'superpotion.png', 'Curación', 50, 600.00),
(3, 'pokeball', 'Poké Ball', 'Captura un Pokémon x1', 'pokeball.png', 'Captura', NULL, 200.00),
(4, 'superball', 'Super Ball', 'Captura un Pokémon x1.5', 'superball.png', 'Captura', NULL, 600.00),
(5, 'revive', 'Revivir', 'Revive y restaura HP parcial', 'revivir.png', 'Revivir', 50, 1500.00),
(6, 'antidote', 'Antídoto', 'Quita el estado de veneno', 'antidoto.png', 'Curacion de estados', NULL, 100.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `money_transactions`
--

CREATE TABLE `money_transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` varchar(50) NOT NULL,
  `meta` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `money_transactions`
--

INSERT INTO `money_transactions` (`id`, `user_id`, `amount`, `type`, `meta`, `created_at`) VALUES
(22, 3, 1500.00, 'purchase', '{\"item\":\"revive\",\"qty\":1,\"unit_price\":1500}', '2025-12-03 02:34:07'),
(23, 3, 1500.00, 'purchase', '{\"item\":\"revive\",\"qty\":1,\"unit_price\":1500}', '2025-12-03 02:34:07'),
(24, 3, 1500.00, 'purchase', '{\"item\":\"revive\",\"qty\":1,\"unit_price\":1500}', '2025-12-03 02:34:07'),
(25, 3, 1500.00, 'purchase', '{\"item\":\"revive\",\"qty\":1,\"unit_price\":1500}', '2025-12-03 02:34:07'),
(26, 3, 1500.00, 'purchase', '{\"item\":\"revive\",\"qty\":1,\"unit_price\":1500}', '2025-12-03 02:34:07'),
(27, 3, 1500.00, 'purchase', '{\"item\":\"revive\",\"qty\":1,\"unit_price\":1500}', '2025-12-03 02:34:08'),
(28, 4, 1500.00, 'purchase', '{\"item\":\"revive\",\"qty\":1,\"unit_price\":1500}', '2025-12-03 02:34:09'),
(29, 4, 600.00, 'purchase', '{\"item\":\"superball\",\"qty\":1,\"unit_price\":600}', '2025-12-03 02:34:14'),
(30, 4, 100.00, 'purchase', '{\"item\":\"antidote\",\"qty\":1,\"unit_price\":100}', '2025-12-03 02:34:31'),
(31, 4, 200.00, 'purchase', '{\"item\":\"pokeball\",\"qty\":1,\"unit_price\":200}', '2025-12-03 02:34:35'),
(32, 4, 600.00, 'purchase', '{\"item\":\"superpotion\",\"qty\":1,\"unit_price\":600}', '2025-12-03 02:34:36'),
(33, 4, 300.00, 'purchase', '{\"item\":\"potion\",\"qty\":1,\"unit_price\":300}', '2025-12-03 02:34:36'),
(34, 4, 200.00, 'purchase', '{\"item\":\"pokeball\",\"qty\":1,\"unit_price\":200}', '2025-12-03 02:35:09'),
(35, 4, 200.00, 'purchase', '{\"item\":\"pokeball\",\"qty\":1,\"unit_price\":200}', '2025-12-03 02:35:09'),
(36, 4, 600.00, 'purchase', '{\"item\":\"superpotion\",\"qty\":1,\"unit_price\":600}', '2025-12-03 02:35:10'),
(37, 4, 600.00, 'purchase', '{\"item\":\"superpotion\",\"qty\":1,\"unit_price\":600}', '2025-12-03 02:35:10'),
(38, 4, 300.00, 'purchase', '{\"item\":\"potion\",\"qty\":1,\"unit_price\":300}', '2025-12-03 02:35:11'),
(39, 4, 300.00, 'purchase', '{\"item\":\"potion\",\"qty\":1,\"unit_price\":300}', '2025-12-03 02:35:11'),
(40, 4, 300.00, 'purchase', '{\"item\":\"potion\",\"qty\":1,\"unit_price\":300}', '2025-12-03 02:35:11'),
(41, 4, 300.00, 'purchase', '{\"item\":\"potion\",\"qty\":1,\"unit_price\":300}', '2025-12-03 02:35:11'),
(42, 4, 200.00, 'purchase', '{\"item\":\"pokeball\",\"qty\":1,\"unit_price\":200}', '2025-12-03 02:35:12'),
(43, 4, 200.00, 'purchase', '{\"item\":\"pokeball\",\"qty\":1,\"unit_price\":200}', '2025-12-03 02:35:12'),
(44, 4, 200.00, 'purchase', '{\"item\":\"pokeball\",\"qty\":1,\"unit_price\":200}', '2025-12-03 02:35:12'),
(45, 4, 100.00, 'purchase', '{\"item\":\"antidote\",\"qty\":1,\"unit_price\":100}', '2025-12-03 02:35:13'),
(46, 3, 600.00, 'purchase', '{\"item\":\"superball\",\"qty\":1,\"unit_price\":600}', '2025-12-03 02:35:17'),
(47, 4, 1500.00, 'purchase', '{\"item\":\"revive\",\"qty\":1,\"unit_price\":1500}', '2025-12-03 02:35:28'),
(48, 4, 1500.00, 'purchase', '{\"item\":\"revive\",\"qty\":1,\"unit_price\":1500}', '2025-12-03 02:35:29'),
(49, 4, 1500.00, 'purchase', '{\"item\":\"revive\",\"qty\":1,\"unit_price\":1500}', '2025-12-03 02:35:31'),
(50, 4, 100.00, 'purchase', '{\"item\":\"antidote\",\"qty\":1,\"unit_price\":100}', '2025-12-03 02:36:33'),
(51, 4, 200.00, 'purchase', '{\"item\":\"pokeball\",\"qty\":1,\"unit_price\":200}', '2025-12-03 02:38:04'),
(52, 4, 200.00, 'purchase', '{\"item\":\"pokeball\",\"qty\":1,\"unit_price\":200}', '2025-12-03 02:38:05'),
(53, 4, 200.00, 'purchase', '{\"item\":\"pokeball\",\"qty\":1,\"unit_price\":200}', '2025-12-03 02:38:05'),
(54, 4, 200.00, 'purchase', '{\"item\":\"pokeball\",\"qty\":1,\"unit_price\":200}', '2025-12-03 02:38:06'),
(55, 4, 200.00, 'purchase', '{\"item\":\"pokeball\",\"qty\":1,\"unit_price\":200}', '2025-12-03 02:38:06'),
(56, 4, 200.00, 'purchase', '{\"item\":\"pokeball\",\"qty\":1,\"unit_price\":200}', '2025-12-03 02:38:06'),
(57, 4, 300.00, 'purchase', '{\"item\":\"potion\",\"qty\":1,\"unit_price\":300}', '2025-12-03 02:39:03'),
(58, 4, 300.00, 'purchase', '{\"item\":\"potion\",\"qty\":1,\"unit_price\":300}', '2025-12-03 02:39:04'),
(59, 4, 300.00, 'purchase', '{\"item\":\"potion\",\"qty\":1,\"unit_price\":300}', '2025-12-03 02:39:06'),
(60, 4, 300.00, 'purchase', '{\"item\":\"potion\",\"qty\":1,\"unit_price\":300}', '2025-12-03 02:39:07'),
(61, 4, 300.00, 'purchase', '{\"item\":\"potion\",\"qty\":1,\"unit_price\":300}', '2025-12-03 02:39:07'),
(284, 68, 300.00, 'purchase', '{\"item\":\"potion\",\"qty\":1,\"unit_price\":300}', '2026-02-04 23:04:09'),
(285, 68, 300.00, 'purchase', '{\"item\":\"potion\",\"qty\":1,\"unit_price\":300}', '2026-02-04 23:04:09'),
(286, 68, 300.00, 'purchase', '{\"item\":\"potion\",\"qty\":1,\"unit_price\":300}', '2026-02-04 23:04:09'),
(287, 68, 300.00, 'purchase', '{\"item\":\"potion\",\"qty\":1,\"unit_price\":300}', '2026-02-04 23:04:10'),
(288, 68, 600.00, 'purchase', '{\"item\":\"superpotion\",\"qty\":1,\"unit_price\":600}', '2026-02-04 23:08:05'),
(289, 68, 600.00, 'purchase', '{\"item\":\"superpotion\",\"qty\":1,\"unit_price\":600}', '2026-02-04 23:08:06'),
(290, 68, 600.00, 'purchase', '{\"item\":\"superpotion\",\"qty\":1,\"unit_price\":600}', '2026-02-04 23:08:06'),
(291, 68, 300.00, 'purchase', '{\"item\":\"potion\",\"qty\":1,\"unit_price\":300}', '2026-02-04 23:08:07'),
(292, 68, 300.00, 'purchase', '{\"item\":\"potion\",\"qty\":1,\"unit_price\":300}', '2026-02-04 23:08:08'),
(293, 68, 300.00, 'purchase', '{\"item\":\"potion\",\"qty\":1,\"unit_price\":300}', '2026-02-04 23:08:08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos`
--

CREATE TABLE `movimientos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo_id` int(11) DEFAULT NULL,
  `categoria` enum('fisico','especial','estado') DEFAULT 'fisico',
  `potencia` int(11) DEFAULT 0,
  `precision` int(11) DEFAULT 100,
  `nivel_requerido` int(11) NOT NULL DEFAULT 1,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `movimientos`
--

INSERT INTO `movimientos` (`id`, `nombre`, `tipo_id`, `categoria`, `potencia`, `precision`, `nivel_requerido`, `descripcion`) VALUES
(1, 'Ataque Rápido', 1, 'fisico', 40, 100, 1, 'Ataque rápido, con prioridad (+1)'),
(2, 'Bofetón Lodo', 3, 'fisico', 20, 100, 1, 'Golpe con lodo de baja potencia'),
(3, 'Placaje', 1, 'fisico', 40, 100, 1, 'Ataque de carga al rival'),
(4, 'Puño Fuego', 2, 'fisico', 75, 100, 1, 'Puño envuelto en fuego'),
(5, 'Rayo Hielo', 6, 'especial', 90, 100, 10, 'Rayo de hielo que congela'),
(6, 'Rayo', 5, 'especial', 90, 100, 10, 'Potente descarga eléctrica'),
(7, 'Poder Psíquico', 11, 'especial', 90, 100, 1, 'Ataque psíquico devastador'),
(8, 'Terremoto', 9, 'fisico', 100, 100, 30, 'Terremoto que afecta a todos'),
(9, 'Destello Espectral', 14, 'especial', 80, 100, 20, 'Ataque fantasmal que ignora defensas'),
(10, 'Danza Espada', 1, 'estado', 0, 100, 7, 'Aumenta el ataque del usuario'),
(11, 'Defensa Férrea', 17, 'estado', 0, 100, 1, 'Aumenta la defensa del usuario'),
(12, 'Síntesis', 4, 'estado', 0, 100, 1, 'Restaura HP'),
(13, 'Recuperación', 1, 'estado', 0, 100, 1, 'Restaura la mitad del HP máximo'),
(14, 'Protección', 1, 'estado', 0, 100, 1, 'Se protege del siguiente ataque'),
(15, 'Rueda de Fuego', 2, 'fisico', 60, 100, 1, 'Giro envuelto en fuego');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `naturalezas`
--

CREATE TABLE `naturalezas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `stat_aumentado` varchar(20) DEFAULT NULL,
  `stat_reducido` varchar(20) DEFAULT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `naturalezas`
--

INSERT INTO `naturalezas` (`id`, `nombre`, `stat_aumentado`, `stat_reducido`, `descripcion`) VALUES
(1, 'Adamantina', 'defensa', 'sp_ataque', 'Aumenta defensa, reduce ataque especial'),
(2, 'Arisca', 'velocidad', 'defensa', 'Aumenta velocidad, reduce defensa'),
(3, 'Audaz', 'ataque', 'sp_ataque', 'Aumenta ataque, reduce ataque especial'),
(4, 'Auspiciosa', 'sp_ataque', 'sp_defensa', 'Aumenta ataque especial, reduce defensa especial'),
(5, 'Calmada', 'sp_defensa', 'velocidad', 'Aumenta defensa especial, reduce velocidad'),
(6, 'Cauta', 'defensa', 'ataque', 'Aumenta defensa, reduce ataque'),
(7, 'Comedida', 'sp_defensa', 'sp_ataque', 'Aumenta defensa especial, reduce ataque especial'),
(8, 'Desenfadada', 'velocidad', 'sp_ataque', 'Aumenta velocidad, reduce ataque especial'),
(9, 'Docil', NULL, NULL, 'Neutra, sin cambios de stats'),
(10, 'Dura', 'defensa', 'sp_defensa', 'Aumenta defensa, reduce defensa especial'),
(11, 'Espigada', 'velocidad', 'sp_defensa', 'Aumenta velocidad, reduce defensa especial'),
(12, 'Estable', NULL, NULL, 'Neutra, sin cambios de stats'),
(13, 'Firme', 'ataque', 'velocidad', 'Aumenta ataque, reduce velocidad'),
(14, 'Floja', 'sp_ataque', 'defensa', 'Aumenta ataque especial, reduce defensa'),
(15, 'Grosera', 'ataque', 'sp_defensa', 'Aumenta ataque, reduce defensa especial'),
(16, 'Huraña', 'sp_ataque', 'ataque', 'Aumenta ataque especial, reduce ataque'),
(17, 'Ingenua', 'velocidad', 'sp_defensa', 'Aumenta velocidad, reduce defensa especial'),
(18, 'Leal', 'sp_defensa', 'ataque', 'Aumenta defensa especial, reduce ataque'),
(19, 'Miedosa', 'sp_defensa', 'ataque', 'Aumenta defensa especial, reduce ataque'),
(20, 'Mansa', NULL, NULL, 'Neutra, sin cambios de stats'),
(21, 'Modesta', 'sp_ataque', 'ataque', 'Aumenta ataque especial, reduce ataque'),
(22, 'Parca', 'velocidad', 'ataque', 'Aumenta velocidad, reduce ataque'),
(23, 'Plácida', 'sp_defensa', 'velocidad', 'Aumenta defensa especial, reduce velocidad'),
(24, 'Recia', 'ataque', 'sp_defensa', 'Aumenta ataque, reduce defensa especial'),
(25, 'Tímida', 'velocidad', 'ataque', 'Aumenta velocidad, reduce ataque');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pokedex`
--

CREATE TABLE `pokedex` (
  `user_id` int(11) NOT NULL,
  `species_id` int(11) NOT NULL,
  `visto` tinyint(4) DEFAULT 0,
  `capturado` tinyint(4) DEFAULT 0,
  `veces_visto` int(11) DEFAULT 0,
  `first_seen_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pokedex`
--

INSERT INTO `pokedex` (`user_id`, `species_id`, `visto`, `capturado`, `veces_visto`, `first_seen_at`) VALUES
(68, 1, 1, 0, 0, NULL),
(68, 2, 1, 0, 0, NULL),
(68, 3, 1, 0, 0, NULL),
(68, 4, 1, 0, 0, NULL),
(68, 5, 1, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pokemon_box`
--

CREATE TABLE `pokemon_box` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `species_id` int(11) NOT NULL,
  `apodo` varchar(150) DEFAULT NULL,
  `nivel` int(11) DEFAULT 1,
  `hp` int(11) DEFAULT NULL,
  `max_hp` int(11) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `naturaleza_id` int(11) DEFAULT NULL,
  `habilidad_id` int(11) DEFAULT NULL,
  `experiencia` int(11) DEFAULT 0,
  `ataque` int(11) DEFAULT 10,
  `defensa` int(11) DEFAULT 10,
  `sp_ataque` int(11) DEFAULT 10,
  `sp_defensa` int(11) DEFAULT 10,
  `velocidad` int(11) DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pokemon_box`
--

INSERT INTO `pokemon_box` (`id`, `user_id`, `species_id`, `apodo`, `nivel`, `hp`, `max_hp`, `status`, `created_at`, `naturaleza_id`, `habilidad_id`, `experiencia`, `ataque`, `defensa`, `sp_ataque`, `sp_defensa`, `velocidad`) VALUES
(46, 11, 3, '', 100, 195, 195, '', '2026-02-04 21:28:29', NULL, NULL, 0, 130, 100, 120, 120, 200),
(47, 10, 4, '', 50, 92, 92, '', '2026-02-04 21:28:45', NULL, NULL, 0, 32, 27, 117, 67, 102),
(48, 11, 5, '', 100, 205, 205, '', '2026-02-04 22:59:28', NULL, NULL, 0, 90, 100, 120, 100, 90),
(49, 68, 1, '', 6, 22, 22, '', '2026-02-04 23:03:52', NULL, NULL, 0, 11, 11, 13, 13, 11),
(50, 68, 2, '', 9, NULL, 27, '', '2026-02-04 23:03:52', NULL, NULL, 0, 15, 14, 17, 15, 18),
(51, 68, 3, '', 6, 21, 21, '', '2026-02-04 23:03:52', NULL, NULL, 0, 12, 10, 11, 11, 16),
(52, 68, 4, NULL, 5, NULL, NULL, '', '2026-02-04 23:03:52', NULL, NULL, 0, 10, 10, 10, 10, 10),
(53, 68, 5, NULL, 5, NULL, NULL, '', '2026-02-04 23:03:52', NULL, NULL, 0, 10, 10, 10, 10, 10),
(54, 67, 3, NULL, 6, NULL, NULL, NULL, '2026-02-05 18:38:55', NULL, NULL, 0, 10, 10, 10, 10, 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pokemon_movimiento`
--

CREATE TABLE `pokemon_movimiento` (
  `pokemon_box_id` int(11) NOT NULL,
  `movimiento_id` int(11) NOT NULL,
  `slot` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pokemon_movimiento`
--

INSERT INTO `pokemon_movimiento` (`pokemon_box_id`, `movimiento_id`, `slot`) VALUES
(49, 10, 1),
(51, 1, 1),
(51, 2, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pokemon_species`
--

CREATE TABLE `pokemon_species` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `sprite` varchar(255) DEFAULT NULL,
  `hp` int(11) DEFAULT 45,
  `ataque` int(11) DEFAULT 49,
  `defensa` int(11) DEFAULT 49,
  `velocidad` int(11) DEFAULT 45,
  `sp_ataque` int(11) DEFAULT 65,
  `sp_defensa` int(11) DEFAULT 65
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pokemon_species`
--

INSERT INTO `pokemon_species` (`id`, `nombre`, `sprite`, `hp`, `ataque`, `defensa`, `velocidad`, `sp_ataque`, `sp_defensa`) VALUES
(1, 'Pikachu', 'pikachu.png', 45, 49, 49, 45, 65, 65),
(2, 'Charmander', 'charmander.png', 39, 52, 43, 65, 60, 50),
(3, 'Bulbasaur', 'bulbasur.png', 35, 55, 40, 90, 50, 50),
(4, 'Greninja', 'greninja.png', 25, 20, 15, 90, 105, 55),
(5, 'Zekrom', 'zekrom.png', 40, 35, 40, 35, 50, 40);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pokemon_species_movimiento`
--

CREATE TABLE `pokemon_species_movimiento` (
  `species_id` int(11) NOT NULL,
  `movimiento_id` int(11) NOT NULL,
  `nivel` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pokemon_species_movimiento`
--

INSERT INTO `pokemon_species_movimiento` (`species_id`, `movimiento_id`, `nivel`) VALUES
(1, 1, 1),
(1, 3, 1),
(1, 6, 10),
(1, 10, 7),
(1, 12, 13),
(2, 3, 1),
(2, 4, 10),
(2, 15, 15),
(3, 1, 1),
(3, 2, 1),
(3, 5, 10),
(3, 7, 18),
(3, 11, 7),
(4, 7, 5),
(5, 8, 15);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `team`
--

CREATE TABLE `team` (
  `user_id` int(11) NOT NULL,
  `slot` tinyint(4) NOT NULL,
  `pokemon_box_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `team`
--

INSERT INTO `team` (`user_id`, `slot`, `pokemon_box_id`) VALUES
(3, 1, NULL),
(4, 1, NULL),
(10, 1, NULL),
(11, 1, NULL),
(11, 3, NULL),
(68, 5, 49),
(68, 3, 50),
(68, 1, 51);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos`
--

CREATE TABLE `tipos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `color` varchar(7) DEFAULT '#888888'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos`
--

INSERT INTO `tipos` (`id`, `nombre`, `color`) VALUES
(1, 'Normal', '#A8A878'),
(2, 'Fuego', '#F08030'),
(3, 'Agua', '#6890F0'),
(4, 'Planta', '#78C850'),
(5, 'Eléctrico', '#F8D030'),
(6, 'Hielo', '#98D8D8'),
(7, 'Lucha', '#C03028'),
(8, 'Veneno', '#A040A0'),
(9, 'Tierra', '#E0C068'),
(10, 'Volador', '#A890F0'),
(11, 'Psíquico', '#F85888'),
(12, 'Bicho', '#A8B820'),
(13, 'Roca', '#B8A038'),
(14, 'Fantasma', '#705898'),
(15, 'Dragón', '#7038F8'),
(16, 'Siniestro', '#705848'),
(17, 'Acero', '#B8B8D0'),
(18, 'Hada', '#EE99AC');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(200) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `money` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `correo`, `contraseña`, `created_at`, `money`) VALUES
(3, 'Mario', 'Pacheco', 'mariopachecobarea@gmail.com', '$2y$12$QGgibLBjX5tpCYWDG4EBPOhVVfULvBxH8kApTpHLdWj31eEKNG7Wm', '2025-12-03 02:29:38', 40400.00),
(4, 'Pablo', 'Pingu', 'pablolorentemart@gmail.com', '$2y$12$S6UiVyAgIPokhnCQU6DzROxd53M0k8DMK772MZrbfe3/mvwUw4CE6', '2025-12-03 02:31:59', 37000.00),
(8, 'Naza', 'Asereje', 'naazaa005@gmail.com', '$2y$12$/3Zb89o/Srk01k7QjUD23uM7F6nOCgqat62PkHjbPwcZsPzq419Jq', '2025-12-04 21:17:11', 50000.00),
(10, 'María', 'Barea', 'mbaentrenadora@pokemon.es', '$2y$12$qgDAwUf0qh9fq.UkOgf9Ae4QMIWj0gUr6vt.slE.HNi4fpLDKA2F6', '2025-12-07 16:23:49', 50000.00),
(11, 'Alejandro', 'Mena Mezquita', 'menamezquitaalejandro@gmail.com', '$2y$12$7lLXXJrTDWvp/pSoDxfvReSwfn9gJjczoafHVu4oHxKy6a/p.CVfW', '2025-12-08 23:35:04', 50000.00),
(67, 'ALberto', 'Garcia Quintana', 'albertogarciaquintana5@gmail.com', 'Albertosaurio', '2026-02-04 21:18:57', 50000.00),
(68, 'Prueba', '1', 'prueba@gmail.com', '$2y$10$Yzifq4fHsV6fW6gszzuh5OpfL3da8DMBzKgIxItO9zjMAq1CB3K2y', '2026-02-04 23:03:51', 46100.00);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `habilidades`
--
ALTER TABLE `habilidades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`user_id`,`item_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indices de la tabla `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`);

--
-- Indices de la tabla `money_transactions`
--
ALTER TABLE `money_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `movimientos`
--
ALTER TABLE `movimientos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `tipo_id` (`tipo_id`);

--
-- Indices de la tabla `naturalezas`
--
ALTER TABLE `naturalezas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `pokedex`
--
ALTER TABLE `pokedex`
  ADD PRIMARY KEY (`user_id`,`species_id`),
  ADD KEY `species_id` (`species_id`);

--
-- Indices de la tabla `pokemon_box`
--
ALTER TABLE `pokemon_box`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `species_id` (`species_id`),
  ADD KEY `fk_naturaleza` (`naturaleza_id`),
  ADD KEY `fk_habilidad` (`habilidad_id`);

--
-- Indices de la tabla `pokemon_movimiento`
--
ALTER TABLE `pokemon_movimiento`
  ADD PRIMARY KEY (`pokemon_box_id`,`movimiento_id`,`slot`),
  ADD KEY `movimiento_id` (`movimiento_id`);

--
-- Indices de la tabla `pokemon_species`
--
ALTER TABLE `pokemon_species`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pokemon_species_movimiento`
--
ALTER TABLE `pokemon_species_movimiento`
  ADD PRIMARY KEY (`species_id`,`movimiento_id`),
  ADD KEY `movimiento_id` (`movimiento_id`);

--
-- Indices de la tabla `team`
--
ALTER TABLE `team`
  ADD PRIMARY KEY (`user_id`,`slot`),
  ADD KEY `pokemon_box_id` (`pokemon_box_id`);

--
-- Indices de la tabla `tipos`
--
ALTER TABLE `tipos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `habilidades`
--
ALTER TABLE `habilidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `money_transactions`
--
ALTER TABLE `money_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=294;

--
-- AUTO_INCREMENT de la tabla `movimientos`
--
ALTER TABLE `movimientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `naturalezas`
--
ALTER TABLE `naturalezas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `pokemon_box`
--
ALTER TABLE `pokemon_box`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT de la tabla `pokemon_species`
--
ALTER TABLE `pokemon_species`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tipos`
--
ALTER TABLE `tipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD CONSTRAINT `inventario_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventario_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `money_transactions`
--
ALTER TABLE `money_transactions`
  ADD CONSTRAINT `money_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `movimientos`
--
ALTER TABLE `movimientos`
  ADD CONSTRAINT `movimientos_ibfk_1` FOREIGN KEY (`tipo_id`) REFERENCES `tipos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `pokedex`
--
ALTER TABLE `pokedex`
  ADD CONSTRAINT `pokedex_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pokedex_ibfk_2` FOREIGN KEY (`species_id`) REFERENCES `pokemon_species` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pokemon_box`
--
ALTER TABLE `pokemon_box`
  ADD CONSTRAINT `fk_habilidad` FOREIGN KEY (`habilidad_id`) REFERENCES `habilidades` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_naturaleza` FOREIGN KEY (`naturaleza_id`) REFERENCES `naturalezas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pokemon_box_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pokemon_box_ibfk_2` FOREIGN KEY (`species_id`) REFERENCES `pokemon_species` (`id`);

--
-- Filtros para la tabla `pokemon_movimiento`
--
ALTER TABLE `pokemon_movimiento`
  ADD CONSTRAINT `pokemon_movimiento_ibfk_1` FOREIGN KEY (`pokemon_box_id`) REFERENCES `pokemon_box` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pokemon_movimiento_ibfk_2` FOREIGN KEY (`movimiento_id`) REFERENCES `movimientos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pokemon_species_movimiento`
--
ALTER TABLE `pokemon_species_movimiento`
  ADD CONSTRAINT `pokemon_species_movimiento_ibfk_1` FOREIGN KEY (`species_id`) REFERENCES `pokemon_species` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pokemon_species_movimiento_ibfk_2` FOREIGN KEY (`movimiento_id`) REFERENCES `movimientos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `team`
--
ALTER TABLE `team`
  ADD CONSTRAINT `team_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_ibfk_2` FOREIGN KEY (`pokemon_box_id`) REFERENCES `pokemon_box` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
