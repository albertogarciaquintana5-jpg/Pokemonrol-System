-- Migration 004: Create a fresh database schema and seed example data
-- Usage: mysql -u root -p < migrations/004-create-fresh-db.sql

-- Drop existing DB if you truly want a clean start (UNCOMMENT to enable)
-- DROP DATABASE IF EXISTS `rol`;

CREATE DATABASE IF NOT EXISTS `rol` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `rol`;

-- Table: usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `apellido` VARCHAR(200) NOT NULL,
  `correo` VARCHAR(255) NOT NULL UNIQUE,
  `contraseña` VARCHAR(255) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `money` DECIMAL(10,2) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: items
CREATE TABLE IF NOT EXISTS `items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `clave` VARCHAR(100) NOT NULL UNIQUE,
  `nombre` VARCHAR(150) NOT NULL,
  `descripcion` TEXT DEFAULT NULL,
  `icono` VARCHAR(100) DEFAULT NULL,
  `price` DECIMAL(10,2) DEFAULT NULL,
  `effect_type` VARCHAR(50) DEFAULT NULL,
  `effect_value` INT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: pokemon_species (minimal)
CREATE TABLE IF NOT EXISTS `pokemon_species` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `sprite` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: pokemon_box
CREATE TABLE IF NOT EXISTS `pokemon_box` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `species_id` INT NOT NULL,
  `apodo` VARCHAR(150) DEFAULT NULL,
  `nivel` INT DEFAULT 1,
  `cp` INT DEFAULT 0,
  `hp` INT DEFAULT NULL,
  `max_hp` INT DEFAULT NULL,
  `status` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (species_id) REFERENCES pokemon_species(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: inventario
CREATE TABLE IF NOT EXISTS `inventario` (
  `user_id` INT NOT NULL,
  `item_id` INT NOT NULL,
  `cantidad` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`,`item_id`),
  FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: team (one row per user+slot)
CREATE TABLE IF NOT EXISTS `team` (
  `user_id` INT NOT NULL,
  `slot` TINYINT NOT NULL,
  `pokemon_box_id` INT DEFAULT NULL,
  PRIMARY KEY (`user_id`,`slot`),
  FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (pokemon_box_id) REFERENCES pokemon_box(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: pokedex
CREATE TABLE IF NOT EXISTS `pokedex` (
  `user_id` INT NOT NULL,
  `species_id` INT NOT NULL,
  `visto` TINYINT DEFAULT 0,
  `capturado` TINYINT DEFAULT 0,
  `veces_visto` INT DEFAULT 0,
  `first_seen_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`user_id`,`species_id`),
  FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (species_id) REFERENCES pokemon_species(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed: example user
-- Password stored as bcrypt hash. If you prefer to create the hash yourself, replace the value below.
INSERT INTO usuarios (nombre, apellido, correo, `contraseña`, `money`) VALUES
('ALberto', 'Garcia Quintana', 'albertogarciaquintana5@gmail.com', '$2y$10$K1q3nWvH5Y7t9uQpR6e8e.O9sGfH2Jt9bZxYw1v2s3d4f5g6h7iJk', 1000.00);

-- Seed: items
INSERT INTO items (clave, nombre, descripcion, icono, price, effect_type, effect_value) VALUES
('potion', 'Poción', 'Restaura 20 HP', 'potion.png', 50.00, 'heal_flat', 20),
('superpotion', 'Super Poción', 'Restaura 50 HP', 'superpotion.svg', 120.00, 'heal_flat', 50),
('pokeball', 'Poké Ball', 'Captura un Pokémon', 'ball.png', 200.00, 'capture', NULL),
('greatball', 'Great Ball', 'Mejor probabilidad de captura', 'superball.svg', 350.00, 'capture', NULL),
('revive', 'Revive', 'Revive y restaura HP parcial', 'revivir.png', 300.00, 'revive', 50),
('antidote', 'Antídoto', 'Quita el estado de veneno', 'antidoto.png', 40.00, 'clear_status', NULL);
-- Artículos adicionales
INSERT INTO items (clave, nombre, descripcion, icono, price, effect_type, effect_value) VALUES
('ultraball', 'Ultra Ball', 'Mejor probabilidad de captura', 'ultraball.png', 600.00, 'capture', NULL),
('hyperpotion', 'Hyper Poción', 'Restaura 200 HP', 'hyperpotion.png', 250.00, 'heal_flat', 200),
('elixir', 'Elixir', 'Restaura PP de los movimientos', 'elixir.png', 1000.00, 'restore_pp', NULL);

-- Seed: pokemon_species (small set)
INSERT INTO pokemon_species (nombre, sprite) VALUES
('Pikachu', 'pikachu.png'),
('Charmander', 'charmander.png'),
('Bulbasaur', 'bulbasaur.png');

-- Create a sample pokemon in the box for the example user
-- Find the user id we just inserted (should be 1 on a fresh DB)
SET @uid = (SELECT id FROM usuarios WHERE correo = 'albertogarciaquintana5@gmail.com' LIMIT 1);
SET @spid = (SELECT id FROM pokemon_species WHERE nombre = 'Pikachu' LIMIT 1);
INSERT INTO pokemon_box (user_id, species_id, apodo, nivel, cp, hp, max_hp, status) VALUES
(@uid, @spid, 'Pika', 5, 200, 35, 35, '');

-- Seed: inventory for the user
SET @potion_id = (SELECT id FROM items WHERE clave = 'potion' LIMIT 1);
SET @pokeball_id = (SELECT id FROM items WHERE clave = 'pokeball' LIMIT 1);
SET @revive_id = (SELECT id FROM items WHERE clave = 'revive' LIMIT 1);
INSERT INTO inventario (user_id, item_id, cantidad) VALUES
(@uid, @potion_id, 5),
(@uid, @pokeball_id, 3),
(@uid, @revive_id, 1);

-- Prepare empty team slots for the user
INSERT INTO team (user_id, slot, pokemon_box_id)
SELECT @uid, s.slot, NULL FROM (SELECT 1 AS slot UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6) AS s
ON DUPLICATE KEY UPDATE pokemon_box_id = VALUES(pokemon_box_id);

-- Optional: mark Pika as seen in pokedex
SET @pika_species = @spid;
INSERT INTO pokedex (user_id, species_id, visto, capturado, veces_visto, first_seen_at)
VALUES (@uid, @pika_species, 1, 0, 1, NOW())
ON DUPLICATE KEY UPDATE visto = GREATEST(visto, VALUES(visto)), veces_visto = veces_visto + VALUES(veces_visto);

-- Final notes:
-- - Si quieres reemplazar la contraseña en claro por un hash tuyo, ejecuta un UPDATE en la tabla `usuarios` con el hash generado por PHP `password_hash()` o similar.
-- - Para importar: desde PowerShell o terminal ejecuta:
--   mysql -u root -p < "c:\xampp\htdocs\DAW_EJERCICIOS\Pokemonrol\migrations\004-create-fresh-db.sql"
