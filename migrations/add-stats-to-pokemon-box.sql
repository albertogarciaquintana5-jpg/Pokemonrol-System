-- Migración: Añadir columnas de stats a pokemon_box si no existen

ALTER TABLE pokemon_box ADD COLUMN IF NOT EXISTS `ataque` INT DEFAULT 10;
ALTER TABLE pokemon_box ADD COLUMN IF NOT EXISTS `defensa` INT DEFAULT 10;
ALTER TABLE pokemon_box ADD COLUMN IF NOT EXISTS `sp_ataque` INT DEFAULT 10;
ALTER TABLE pokemon_box ADD COLUMN IF NOT EXISTS `sp_defensa` INT DEFAULT 10;
ALTER TABLE pokemon_box ADD COLUMN IF NOT EXISTS `velocidad` INT DEFAULT 10;

-- Verificar estructura
DESCRIBE pokemon_box;
