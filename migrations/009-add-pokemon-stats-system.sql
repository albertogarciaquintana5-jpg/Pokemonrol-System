-- Migration 009: Add complete Pokémon stats system
-- Creates all necessary tables for stats, natures, abilities, and moves
-- Usage: mysql -u root -p rol < migrations/009-add-pokemon-stats-system.sql

USE `rol`;

-- =====================================================
-- TABLE: tipos (Types)
-- =====================================================
CREATE TABLE IF NOT EXISTS `tipos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(50) NOT NULL UNIQUE,
  `color` VARCHAR(7) DEFAULT '#888888'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE: naturalezas (Natures)
-- =====================================================
CREATE TABLE IF NOT EXISTS `naturalezas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(50) NOT NULL UNIQUE,
  `stat_aumentado` VARCHAR(20) DEFAULT NULL,
  `stat_reducido` VARCHAR(20) DEFAULT NULL,
  `descripcion` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE: habilidades (Abilities)
-- =====================================================
CREATE TABLE IF NOT EXISTS `habilidades` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL UNIQUE,
  `descripcion` TEXT DEFAULT NULL,
  `efecto` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE: movimientos (Moves)
-- =====================================================
CREATE TABLE IF NOT EXISTS `movimientos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL UNIQUE,
  `tipo_id` INT DEFAULT NULL,
  `categoria` ENUM('fisico','especial','estado') DEFAULT 'fisico',
  `potencia` INT DEFAULT 0,
  `precision` INT DEFAULT 100,
  `pp` INT DEFAULT 15,
  `descripcion` TEXT DEFAULT NULL,
  FOREIGN KEY (tipo_id) REFERENCES tipos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE: pokemon_movimiento (Junction table)
-- =====================================================
CREATE TABLE IF NOT EXISTS `pokemon_movimiento` (
  `pokemon_box_id` INT NOT NULL,
  `movimiento_id` INT NOT NULL,
  `slot` TINYINT NOT NULL,
  `pp_actual` INT DEFAULT NULL,
  PRIMARY KEY (pokemon_box_id, movimiento_id, slot),
  FOREIGN KEY (pokemon_box_id) REFERENCES pokemon_box(id) ON DELETE CASCADE,
  FOREIGN KEY (movimiento_id) REFERENCES movimientos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE: pokemon_species_movimiento
-- =====================================================
CREATE TABLE IF NOT EXISTS `pokemon_species_movimiento` (
  `species_id` INT NOT NULL,
  `movimiento_id` INT NOT NULL,
  `nivel` INT DEFAULT 1,
  PRIMARY KEY (species_id, movimiento_id),
  FOREIGN KEY (species_id) REFERENCES pokemon_species(id) ON DELETE CASCADE,
  FOREIGN KEY (movimiento_id) REFERENCES movimientos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- ALTER pokemon_species: Add stats columns
-- =====================================================
ALTER TABLE pokemon_species ADD COLUMN IF NOT EXISTS `hp` INT DEFAULT 45;
ALTER TABLE pokemon_species ADD COLUMN IF NOT EXISTS `ataque` INT DEFAULT 49;
ALTER TABLE pokemon_species ADD COLUMN IF NOT EXISTS `defensa` INT DEFAULT 49;
ALTER TABLE pokemon_species ADD COLUMN IF NOT EXISTS `sp_ataque` INT DEFAULT 65;
ALTER TABLE pokemon_species ADD COLUMN IF NOT EXISTS `sp_defensa` INT DEFAULT 65;
ALTER TABLE pokemon_species ADD COLUMN IF NOT EXISTS `velocidad` INT DEFAULT 45;

-- =====================================================
-- ALTER pokemon_box: Add nature, ability, experience
-- =====================================================
ALTER TABLE pokemon_box ADD COLUMN IF NOT EXISTS `naturaleza_id` INT DEFAULT NULL;
ALTER TABLE pokemon_box ADD COLUMN IF NOT EXISTS `habilidad_id` INT DEFAULT NULL;
ALTER TABLE pokemon_box ADD COLUMN IF NOT EXISTS `experiencia` INT DEFAULT 0;

-- Note: Foreign keys are created separately if needed
-- If constraint already exists, this won't cause errors

-- =====================================================
-- SEED DATA: TIPOS (18 Pokémon types)
-- =====================================================
INSERT IGNORE INTO `tipos` (`id`, `nombre`, `color`) VALUES
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

-- =====================================================
-- SEED DATA: NATURALEZAS (25)
-- =====================================================
INSERT IGNORE INTO `naturalezas` (`id`, `nombre`, `stat_aumentado`, `stat_reducido`, `descripcion`) VALUES
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

-- =====================================================
-- SEED DATA: HABILIDADES (10)
-- =====================================================
INSERT IGNORE INTO `habilidades` (`id`, `nombre`, `descripcion`, `efecto`) VALUES
(1, 'Estática', 'Puede paralizar al atacante.', 'chance_paralyze_contact'),
(2, 'Sintonía', 'Copia el tipo del ataque recibido.', 'copy_attack_type'),
(3, 'Torrente', 'Aumenta ataque de tipo agua cuando tiene bajo HP.', 'boost_water_low_hp'),
(4, 'Sobrecarga', 'Aumenta ataque especial y velocidad en lluvia.', 'boost_rain'),
(5, 'Marcha acuática', 'Aumenta velocidad en lluvia.', 'boost_speed_rain'),
(6, 'Intimidación', 'Reduce el ataque del enemigo al entrar.', 'lower_enemy_ataque'),
(7, 'Competencia', 'Iguala el ataque especial del rival.', 'match_sp_ataque'),
(8, 'Absorción', 'Absorbe HP del enemigo.', 'absorb_hp'),
(9, 'Rivalidad', 'Hace más daño al mismo género.', 'boost_vs_same_gender'),
(10, 'Premonición', 'Siente el movimiento del rival.', 'reveal_move');

-- =====================================================
-- SEED DATA: MOVIMIENTOS (15)
-- =====================================================
INSERT IGNORE INTO `movimientos` (`id`, `nombre`, `tipo_id`, `categoria`, `potencia`, `precision`, `pp`, `descripcion`) VALUES
(1, 'Ataque Rápido', 1, 'fisico', 40, 100, 35, 'Ataque rápido y sencillo'),
(2, 'Bofetón Lodo', 3, 'fisico', 20, 100, 30, 'Golpe con lodo de baja potencia'),
(3, 'Placaje', 1, 'fisico', 40, 100, 35, 'Ataque de carga al rival'),
(4, 'Puño Fuego', 2, 'fisico', 75, 100, 15, 'Puño envuelto en fuego'),
(5, 'Rayo Hielo', 6, 'especial', 90, 100, 10, 'Rayo de hielo que congela'),
(6, 'Rayo', 5, 'especial', 90, 100, 15, 'Potente descarga eléctrica'),
(7, 'Poder Psíquico', 11, 'especial', 90, 100, 10, 'Ataque psíquico devastador'),
(8, 'Terremoto', 9, 'fisico', 100, 100, 10, 'Terremoto que afecta a todos'),
(9, 'Destello Espectral', 14, 'especial', 80, 100, 15, 'Ataque fantasmal que ignora defensas'),
(10, 'Danza Espada', 1, 'estado', 0, 100, 20, 'Aumenta el ataque del usuario'),
(11, 'Defensa Férrea', 17, 'estado', 0, 100, 15, 'Aumenta la defensa del usuario'),
(12, 'Síntesis', 4, 'estado', 0, 100, 5, 'Restaura HP'),
(13, 'Recuperación', 1, 'estado', 0, 100, 10, 'Restaura la mitad del HP máximo'),
(14, 'Protección', 1, 'estado', 0, 100, 10, 'Se protege del siguiente ataque'),
(15, 'Rueda de Fuego', 2, 'fisico', 60, 100, 25, 'Giro envuelto en fuego');

-- =====================================================
-- ASIGNAR MOVIMIENTOS A ESPECIES (Ejemplos)
-- =====================================================
INSERT IGNORE INTO `pokemon_species_movimiento` (`species_id`, `movimiento_id`, `nivel`) VALUES
(1, 1, 1),   -- Pikachu aprende Ataque Rápido
(1, 6, 10),  -- Pikachu aprende Rayo
(2, 3, 1),   -- Charmander aprende Placaje
(2, 4, 10),  -- Charmander aprende Puño Fuego
(3, 2, 1),   -- Bulbasaur aprende Bofetón Lodo
(3, 5, 10),  -- Bulbasaur aprende Rayo Hielo
(4, 7, 5),   -- Greninja aprende Poder Psíquico
(5, 8, 15);  -- Zekrom aprende Terremoto

-- =====================================================
-- ASIGNAR NATURALEZA Y HABILIDAD A POKÉMON EXISTENTES
-- =====================================================
UPDATE pokemon_box SET 
  naturaleza_id = IF(naturaleza_id IS NULL, 3, naturaleza_id),
  habilidad_id = IF(habilidad_id IS NULL, 6, habilidad_id)
WHERE naturaleza_id IS NULL OR habilidad_id IS NULL;

-- =====================================================
-- FIN DE LA MIGRACIÓN 009
-- =====================================================
