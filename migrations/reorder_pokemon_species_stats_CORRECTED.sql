-- =====================================================
-- CORRECCIÓN: Reordenar columnas de stats en pokemon_species
-- Orden correcto: hp, ataque, defensa, velocidad, sp_ataque, sp_defensa
-- =====================================================

USE rol;

-- Reordenar las columnas de estadísticas
ALTER TABLE `pokemon_species`
  MODIFY COLUMN `defensa` INT(11) DEFAULT 49 AFTER `ataque`,
  MODIFY COLUMN `velocidad` INT(11) DEFAULT 45 AFTER `defensa`,
  MODIFY COLUMN `sp_ataque` INT(11) DEFAULT 65 AFTER `velocidad`,
  MODIFY COLUMN `sp_defensa` INT(11) DEFAULT 80 AFTER `sp_ataque`;

-- Verificar el nuevo orden
DESCRIBE pokemon_species;
