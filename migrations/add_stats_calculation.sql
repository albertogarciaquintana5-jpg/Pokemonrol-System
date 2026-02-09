-- ============================================
-- SISTEMA DE CÁLCULO AUTOMÁTICO DE ESTADÍSTICAS
-- Basado en fórmulas oficiales de Pokémon
-- ============================================

-- Paso 1: Añadir columna para almacenar IVs (Individual Values) en pokemon_box
ALTER TABLE `pokemon_box` 
ADD COLUMN `iv_hp` TINYINT UNSIGNED DEFAULT NULL AFTER `velocidad`,
ADD COLUMN `iv_ataque` TINYINT UNSIGNED DEFAULT NULL AFTER `iv_hp`,
ADD COLUMN `iv_defensa` TINYINT UNSIGNED DEFAULT NULL AFTER `iv_ataque`,
ADD COLUMN `iv_sp_ataque` TINYINT UNSIGNED DEFAULT NULL AFTER `iv_defensa`,
ADD COLUMN `iv_sp_defensa` TINYINT UNSIGNED DEFAULT NULL AFTER `iv_sp_ataque`,
ADD COLUMN `iv_velocidad` TINYINT UNSIGNED DEFAULT NULL AFTER `iv_sp_defensa`;

-- Paso 2: Eliminar el trigger anterior si existe
DROP TRIGGER IF EXISTS `calculate_pokemon_stats_on_insert`;

DELIMITER $$

-- Paso 3: Crear trigger que calcula las estadísticas automáticamente
CREATE TRIGGER `calculate_pokemon_stats_on_insert` 
BEFORE INSERT ON `pokemon_box`
FOR EACH ROW
BEGIN
    DECLARE base_hp INT;
    DECLARE base_ataque INT;
    DECLARE base_defensa INT;
    DECLARE base_sp_ataque INT;
    DECLARE base_sp_defensa INT;
    DECLARE base_velocidad INT;
    
    DECLARE iv_hp_val INT;
    DECLARE iv_ataque_val INT;
    DECLARE iv_defensa_val INT;
    DECLARE iv_sp_ataque_val INT;
    DECLARE iv_sp_defensa_val INT;
    DECLARE iv_velocidad_val INT;
    
    DECLARE nature_up VARCHAR(20);
    DECLARE nature_down VARCHAR(20);
    DECLARE mult_ataque DECIMAL(3,2);
    DECLARE mult_defensa DECIMAL(3,2);
    DECLARE mult_sp_ataque DECIMAL(3,2);
    DECLARE mult_sp_defensa DECIMAL(3,2);
    DECLARE mult_velocidad DECIMAL(3,2);
    
    DECLARE nivel INT;
    
    -- Obtener estadísticas base de la especie
    SELECT hp, ataque, defensa, velocidad, sp_ataque, sp_defensa
    INTO base_hp, base_ataque, base_defensa, base_velocidad, base_sp_ataque, base_sp_defensa
    FROM pokemon_species
    WHERE id = NEW.species_id;
    
    -- Establecer nivel (usar el proporcionado o 5 por defecto)
    SET nivel = IFNULL(NEW.nivel, 5);
    SET NEW.nivel = nivel;
    
    -- Generar IVs aleatorios si no están establecidos (0-31)
    SET iv_hp_val = IFNULL(NEW.iv_hp, FLOOR(RAND() * 32));
    SET iv_ataque_val = IFNULL(NEW.iv_ataque, FLOOR(RAND() * 32));
    SET iv_defensa_val = IFNULL(NEW.iv_defensa, FLOOR(RAND() * 32));
    SET iv_sp_ataque_val = IFNULL(NEW.iv_sp_ataque, FLOOR(RAND() * 32));
    SET iv_sp_defensa_val = IFNULL(NEW.iv_sp_defensa, FLOOR(RAND() * 32));
    SET iv_velocidad_val = IFNULL(NEW.iv_velocidad, FLOOR(RAND() * 32));
    
    -- Guardar IVs generados
    SET NEW.iv_hp = iv_hp_val;
    SET NEW.iv_ataque = iv_ataque_val;
    SET NEW.iv_defensa = iv_defensa_val;
    SET NEW.iv_sp_ataque = iv_sp_ataque_val;
    SET NEW.iv_sp_defensa = iv_sp_defensa_val;
    SET NEW.iv_velocidad = iv_velocidad_val;
    
    -- Obtener modificadores de naturaleza si existe
    SET mult_ataque = 1.0;
    SET mult_defensa = 1.0;
    SET mult_sp_ataque = 1.0;
    SET mult_sp_defensa = 1.0;
    SET mult_velocidad = 1.0;
    
    IF NEW.naturaleza_id IS NOT NULL THEN
        SELECT stat_aumentado, stat_reducido
        INTO nature_up, nature_down
        FROM naturalezas
        WHERE id = NEW.naturaleza_id;
        
        -- Aplicar bonificación (+10%)
        IF nature_up = 'ataque' THEN SET mult_ataque = 1.1; END IF;
        IF nature_up = 'defensa' THEN SET mult_defensa = 1.1; END IF;
        IF nature_up = 'sp_ataque' THEN SET mult_sp_ataque = 1.1; END IF;
        IF nature_up = 'sp_defensa' THEN SET mult_sp_defensa = 1.1; END IF;
        IF nature_up = 'velocidad' THEN SET mult_velocidad = 1.1; END IF;
        
        -- Aplicar penalización (-10%)
        IF nature_down = 'ataque' THEN SET mult_ataque = 0.9; END IF;
        IF nature_down = 'defensa' THEN SET mult_defensa = 0.9; END IF;
        IF nature_down = 'sp_ataque' THEN SET mult_sp_ataque = 0.9; END IF;
        IF nature_down = 'sp_defensa' THEN SET mult_sp_defensa = 0.9; END IF;
        IF nature_down = 'velocidad' THEN SET mult_velocidad = 0.9; END IF;
    END IF;
    
    -- FÓRMULA OFICIAL DE POKÉMON (sin EVs para recién capturados)
    -- HP: floor(((2 * Base + IV) * Level) / 100) + Level + 10
    SET NEW.max_hp = FLOOR(((2 * base_hp + iv_hp_val) * nivel) / 100) + nivel + 10;
    SET NEW.hp = IFNULL(NEW.hp, NEW.max_hp); -- HP actual = máximo si no se especifica
    
    -- OTRAS STATS: floor((floor(((2 * Base + IV) * Level) / 100) + 5) * Nature)
    SET NEW.ataque = FLOOR((FLOOR(((2 * base_ataque + iv_ataque_val) * nivel) / 100) + 5) * mult_ataque);
    SET NEW.defensa = FLOOR((FLOOR(((2 * base_defensa + iv_defensa_val) * nivel) / 100) + 5) * mult_defensa);
    SET NEW.sp_ataque = FLOOR((FLOOR(((2 * base_sp_ataque + iv_sp_ataque_val) * nivel) / 100) + 5) * mult_sp_ataque);
    SET NEW.sp_defensa = FLOOR((FLOOR(((2 * base_sp_defensa + iv_sp_defensa_val) * nivel) / 100) + 5) * mult_sp_defensa);
    SET NEW.velocidad = FLOOR((FLOOR(((2 * base_velocidad + iv_velocidad_val) * nivel) / 100) + 5) * mult_velocidad);
    
    -- Establecer estado vacío si no se proporciona
    SET NEW.status = IFNULL(NEW.status, '');
END$$

DELIMITER ;

-- ============================================
-- FUNCIÓN PARA RECALCULAR STATS AL SUBIR DE NIVEL
-- ============================================

DELIMITER $$

DROP PROCEDURE IF EXISTS `recalculate_pokemon_stats`$$

CREATE PROCEDURE `recalculate_pokemon_stats`(IN pokemon_id INT)
BEGIN
    DECLARE base_hp INT;
    DECLARE base_ataque INT;
    DECLARE base_defensa INT;
    DECLARE base_sp_ataque INT;
    DECLARE base_sp_defensa INT;
    DECLARE base_velocidad INT;
    
    DECLARE iv_hp_val INT;
    DECLARE iv_ataque_val INT;
    DECLARE iv_defensa_val INT;
    DECLARE iv_sp_ataque_val INT;
    DECLARE iv_sp_defensa_val INT;
    DECLARE iv_velocidad_val INT;
    
    DECLARE nature_up VARCHAR(20);
    DECLARE nature_down VARCHAR(20);
    DECLARE mult_ataque DECIMAL(3,2);
    DECLARE mult_defensa DECIMAL(3,2);
    DECLARE mult_sp_ataque DECIMAL(3,2);
    DECLARE mult_sp_defensa DECIMAL(3,2);
    DECLARE mult_velocidad DECIMAL(3,2);
    
    DECLARE nivel INT;
    DECLARE species INT;
    DECLARE nature INT;
    DECLARE hp_actual INT;
    DECLARE old_max_hp INT;
    
    -- Obtener datos del Pokémon
    SELECT species_id, nivel, naturaleza_id, hp, max_hp,
           iv_hp, iv_ataque, iv_defensa, iv_sp_ataque, iv_sp_defensa, iv_velocidad
    INTO species, nivel, nature, hp_actual, old_max_hp,
         iv_hp_val, iv_ataque_val, iv_defensa_val, iv_sp_ataque_val, iv_sp_defensa_val, iv_velocidad_val
    FROM pokemon_box
    WHERE id = pokemon_id;
    
    -- Obtener estadísticas base
    SELECT hp, ataque, defensa, velocidad, sp_ataque, sp_defensa
    INTO base_hp, base_ataque, base_defensa, base_velocidad, base_sp_ataque, base_sp_defensa
    FROM pokemon_species
    WHERE id = species;
    
    -- Generar IVs si faltan (para Pokémon antiguos)
    IF iv_hp_val IS NULL THEN SET iv_hp_val = FLOOR(RAND() * 32); END IF;
    IF iv_ataque_val IS NULL THEN SET iv_ataque_val = FLOOR(RAND() * 32); END IF;
    IF iv_defensa_val IS NULL THEN SET iv_defensa_val = FLOOR(RAND() * 32); END IF;
    IF iv_sp_ataque_val IS NULL THEN SET iv_sp_ataque_val = FLOOR(RAND() * 32); END IF;
    IF iv_sp_defensa_val IS NULL THEN SET iv_sp_defensa_val = FLOOR(RAND() * 32); END IF;
    IF iv_velocidad_val IS NULL THEN SET iv_velocidad_val = FLOOR(RAND() * 32); END IF;
    
    -- Multiplicadores de naturaleza
    SET mult_ataque = 1.0;
    SET mult_defensa = 1.0;
    SET mult_sp_ataque = 1.0;
    SET mult_sp_defensa = 1.0;
    SET mult_velocidad = 1.0;
    
    IF nature IS NOT NULL THEN
        SELECT stat_aumentado, stat_reducido
        INTO nature_up, nature_down
        FROM naturalezas
        WHERE id = nature;
        
        IF nature_up = 'ataque' THEN SET mult_ataque = 1.1; END IF;
        IF nature_up = 'defensa' THEN SET mult_defensa = 1.1; END IF;
        IF nature_up = 'sp_ataque' THEN SET mult_sp_ataque = 1.1; END IF;
        IF nature_up = 'sp_defensa' THEN SET mult_sp_defensa = 1.1; END IF;
        IF nature_up = 'velocidad' THEN SET mult_velocidad = 1.1; END IF;
        
        IF nature_down = 'ataque' THEN SET mult_ataque = 0.9; END IF;
        IF nature_down = 'defensa' THEN SET mult_defensa = 0.9; END IF;
        IF nature_down = 'sp_ataque' THEN SET mult_sp_ataque = 0.9; END IF;
        IF nature_down = 'sp_defensa' THEN SET mult_sp_defensa = 0.9; END IF;
        IF nature_down = 'velocidad' THEN SET mult_velocidad = 0.9; END IF;
    END IF;
    
    -- Recalcular estadísticas
    UPDATE pokemon_box
    SET 
        max_hp = FLOOR(((2 * base_hp + iv_hp_val) * nivel) / 100) + nivel + 10,
        hp = hp_actual + (FLOOR(((2 * base_hp + iv_hp_val) * nivel) / 100) + nivel + 10 - old_max_hp),
        ataque = FLOOR((FLOOR(((2 * base_ataque + iv_ataque_val) * nivel) / 100) + 5) * mult_ataque),
        defensa = FLOOR((FLOOR(((2 * base_defensa + iv_defensa_val) * nivel) / 100) + 5) * mult_defensa),
        sp_ataque = FLOOR((FLOOR(((2 * base_sp_ataque + iv_sp_ataque_val) * nivel) / 100) + 5) * mult_sp_ataque),
        sp_defensa = FLOOR((FLOOR(((2 * base_sp_defensa + iv_sp_defensa_val) * nivel) / 100) + 5) * mult_sp_defensa),
        velocidad = FLOOR((FLOOR(((2 * base_velocidad + iv_velocidad_val) * nivel) / 100) + 5) * mult_velocidad),
        iv_hp = iv_hp_val,
        iv_ataque = iv_ataque_val,
        iv_defensa = iv_defensa_val,
        iv_sp_ataque = iv_sp_ataque_val,
        iv_sp_defensa = iv_sp_defensa_val,
        iv_velocidad = iv_velocidad_val
    WHERE id = pokemon_id;
END$$

DELIMITER ;

-- ============================================
-- SCRIPT DE PRUEBA
-- ============================================

-- Ejemplo de uso:
-- 1. Insertar un nuevo Pokémon (stats se calculan automáticamente)
-- INSERT INTO pokemon_box (user_id, species_id, nivel) VALUES (68, 1, 10);

-- 2. Recalcular stats después de subir de nivel
-- UPDATE pokemon_box SET nivel = 15 WHERE id = 49;
-- CALL recalculate_pokemon_stats(49);
