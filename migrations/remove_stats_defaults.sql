-- ============================================
-- ELIMINAR VALORES DEFAULT DE STATS
-- Para que phpMyAdmin no muestre valores predefinidos
-- ============================================

ALTER TABLE `pokemon_box` 
  ALTER COLUMN `ataque` DROP DEFAULT,
  ALTER COLUMN `defensa` DROP DEFAULT,
  ALTER COLUMN `sp_ataque` DROP DEFAULT,
  ALTER COLUMN `sp_defensa` DROP DEFAULT,
  ALTER COLUMN `velocidad` DROP DEFAULT;

-- Ahora las stats no tendrán valor default
-- El trigger las calculará automáticamente en cada INSERT
