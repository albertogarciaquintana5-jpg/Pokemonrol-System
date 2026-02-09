-- ============================================
-- CORRECCIÓN: Procedimiento recalculate_pokemon_stats
-- FIX: Manejo correcto de valores NULL
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
    DECLARE new_max_hp INT;
    DECLARE hp_difference INT;
    
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
    SET iv_hp_val = IFNULL(iv_hp_val, FLOOR(RAND() * 32));
    SET iv_ataque_val = IFNULL(iv_ataque_val, FLOOR(RAND() * 32));
    SET iv_defensa_val = IFNULL(iv_defensa_val, FLOOR(RAND() * 32));
    SET iv_sp_ataque_val = IFNULL(iv_sp_ataque_val, FLOOR(RAND() * 32));
    SET iv_sp_defensa_val = IFNULL(iv_sp_defensa_val, FLOOR(RAND() * 32));
    SET iv_velocidad_val = IFNULL(iv_velocidad_val, FLOOR(RAND() * 32));
    
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
    
    -- Calcular nuevo max_hp
    SET new_max_hp = FLOOR(((2 * base_hp + iv_hp_val) * nivel) / 100) + nivel + 10;
    
    -- Calcular nuevo hp actual
    -- Si old_max_hp es NULL o 0, establecer al máximo
    -- Si no, ajustar proporcionalmente
    SET old_max_hp = IFNULL(old_max_hp, new_max_hp);
    SET hp_actual = IFNULL(hp_actual, old_max_hp);
    SET hp_difference = new_max_hp - old_max_hp;
    
    -- Recalcular estadísticas
    UPDATE pokemon_box
    SET 
        max_hp = new_max_hp,
        hp = LEAST(hp_actual + hp_difference, new_max_hp),
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
