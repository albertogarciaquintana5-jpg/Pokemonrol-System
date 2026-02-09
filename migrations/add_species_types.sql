-- ============================================
-- MIGRACIÓN: Añadir tipos a pokemon_species
-- ============================================
-- Descripción: Añade columnas tipo_primario_id y tipo_secundario_id
--             a pokemon_species para vincular cada especie con sus tipos.
--             Los tipos determinan resistencias, debilidades y efectividad.
-- ============================================

-- Añadir columnas de tipo a pokemon_species
ALTER TABLE `pokemon_species`
    ADD COLUMN `tipo_primario_id` INT(11) DEFAULT NULL AFTER `sprite`,
    ADD COLUMN `tipo_secundario_id` INT(11) DEFAULT NULL AFTER `tipo_primario_id`,
    ADD CONSTRAINT `fk_species_tipo_primario` FOREIGN KEY (`tipo_primario_id`) REFERENCES `tipos`(`id`) ON DELETE RESTRICT,
    ADD CONSTRAINT `fk_species_tipo_secundario` FOREIGN KEY (`tipo_secundario_id`) REFERENCES `tipos`(`id`) ON DELETE RESTRICT;

-- ============================================
-- ACTUALIZAR ESPECIES EXISTENTES CON SUS TIPOS
-- ============================================
-- Pikachu: Eléctrico
UPDATE `pokemon_species` SET `tipo_primario_id` = 5 WHERE `id` = 1;

-- Charmander: Fuego
UPDATE `pokemon_species` SET `tipo_primario_id` = 2 WHERE `id` = 2;

-- Bulbasaur: Planta/Veneno
UPDATE `pokemon_species` SET `tipo_primario_id` = 4, `tipo_secundario_id` = 8 WHERE `id` = 3;

-- Greninja: Agua/Siniestro
UPDATE `pokemon_species` SET `tipo_primario_id` = 3, `tipo_secundario_id` = 16 WHERE `id` = 4;

-- Zekrom: Dragón/Eléctrico
UPDATE `pokemon_species` SET `tipo_primario_id` = 15, `tipo_secundario_id` = 5 WHERE `id` = 5;

-- ============================================
-- VERIFICACIÓN
-- ============================================
-- Descomentar para verificar que los tipos se asignaron correctamente:
-- SELECT 
--     ps.id,
--     ps.nombre,
--     t1.nombre AS tipo_primario,
--     t1.color AS color_primario,
--     t2.nombre AS tipo_secundario,
--     t2.color AS color_secundario
-- FROM pokemon_species ps
-- LEFT JOIN tipos t1 ON ps.tipo_primario_id = t1.id
-- LEFT JOIN tipos t2 ON ps.tipo_secundario_id = t2.id;
