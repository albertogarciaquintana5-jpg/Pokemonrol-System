-- =====================================================
-- Agregar columna num_tirada a pokemon_species
-- Para sistema de captura por tirada de dado
-- =====================================================

USE rol;

-- Agregar columna num_tirada (inicialmente permite NULL)
ALTER TABLE `pokemon_species` 
ADD COLUMN `num_tirada` INT DEFAULT NULL AFTER `sprite`;

-- Poblar con números secuenciales del 1 hasta el total de Pokémon
SET @row_number = 0;
UPDATE pokemon_species 
SET num_tirada = (@row_number := @row_number + 1)
ORDER BY id;

-- Ahora hacerla NOT NULL y UNIQUE
ALTER TABLE `pokemon_species`
MODIFY COLUMN `num_tirada` INT NOT NULL UNIQUE;

-- Verificar
SELECT COUNT(*) as total_pokemon, MIN(num_tirada) as primer_numero, MAX(num_tirada) as ultimo_numero 
FROM pokemon_species;

-- Ver algunos ejemplos
SELECT id, nombre, sprite, num_tirada 
FROM pokemon_species 
ORDER BY num_tirada 
LIMIT 20;
