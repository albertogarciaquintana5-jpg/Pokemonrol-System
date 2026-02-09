-- ============================================
-- FIX DEFINITIVO: recalculate_pokemon_stats
-- BUG: Variables con mismo nombre que columnas
-- ============================================

DELIMITER $$

DROP PROCEDURE IF EXISTS `recalculate_pokemon_stats`$$

CREATE PROCEDURE `recalculate_pokemon_stats`(IN pokemon_id INT)
BEGIN
    DECLARE v_base_hp INT;
    DECLARE v_base_ataque INT;
    DECLARE v_base_defensa INT;
    DECLARE v_base_sp_ataque INT;
    DECLARE v_base_sp_defensa INT;
    DECLARE v_base_velocidad INT;
    
    DECLARE v_iv_hp INT;
    DECLARE v_iv_ataque INT;
    DECLARE v_iv_defensa INT;
    DECLARE v_iv_sp_ataque INT;
    DECLARE v_iv_sp_defensa INT;
    DECLARE v_iv_velocidad INT;
    
    DECLARE v_nature_up VARCHAR(20);
    DECLARE v_nature_down VARCHAR(20);
    DECLARE v_mult_ataque DECIMAL(3,2);
    DECLARE v_mult_defensa DECIMAL(3,2);
    DECLARE v_mult_sp_ataque DECIMAL(3,2);
    DECLARE v_mult_sp_defensa DECIMAL(3,2);
    DECLARE v_mult_velocidad DECIMAL(3,2);
    
    DECLARE v_nivel INT;
    DECLARE v_species INT;
    DECLARE v_nature INT;
    DECLARE v_hp_actual INT;
    DECLARE v_old_max_hp INT;
    DECLARE v_new_max_hp INT;
    DECLARE v_hp_difference INT;
    
    -- Obtener datos del Pokémon
    SELECT species_id, nivel, naturaleza_id, hp, max_hp,
           iv_hp, iv_ataque, iv_defensa, iv_sp_ataque, iv_sp_defensa, iv_velocidad
    INTO v_species, v_nivel, v_nature, v_hp_actual, v_old_max_hp,
         v_iv_hp, v_iv_ataque, v_iv_defensa, v_iv_sp_ataque, v_iv_sp_defensa, v_iv_velocidad
    FROM pokemon_box
    WHERE id = pokemon_id;
    
    -- Obtener estadísticas base
    SELECT hp, ataque, defensa, velocidad, sp_ataque, sp_defensa
    INTO base_hp, base_ataque, base_defensa, base_velocidad, base_sp_ataque, base_sp_defensa
    FROM pokemon_species
    WHERE id = v_species;
    
    -- Generar IVs si faltan (para Pokémon antiguos)
    SET v_iv_hp = IFNULL(v_iv_hp, FLOOR(RAND() * 32));
    SET v_iv_ataque = IFNULL(v_iv_ataque, FLOOR(RAND() * 32));
    SET v_iv_defensa = IFNULL(v_iv_defensa, FLOOR(RAND() * 32));
    SET v_iv_sp_ataque = IFNULL(v_iv_sp_ataque, FLOOR(RAND() * 32));
    SET v_iv_sp_defensa = IFNULL(v_iv_sp_defensa, FLOOR(RAND() * 32));
    SET v_iv_velocidad = IFNULL(v_iv_velocidad, FLOOR(RAND() * 32));
    
    -- Multiplicadores de naturaleza
    SET v_mult_ataque = 1.0;
    SET v_mult_defensa = 1.0;
    SET v_mult_sp_ataque = 1.0;
    SET v_mult_sp_defensa = 1.0;
    SET v_mult_velocidad = 1.0;
    
    IF v_nature IS NOT NULL THEN
        SELECT stat_aumentado, stat_reducido
        INTO v_nature_up, v_nature_down
        FROM naturalezas
        WHERE id = v_nature;
        
        IF v_nature_up = 'ataque' THEN SET v_mult_ataque = 1.1; END IF;
        IF v_nature_up = 'defensa' THEN SET v_mult_defensa = 1.1; END IF;
        IF v_nature_up = 'sp_ataque' THEN SET v_mult_sp_ataque = 1.1; END IF;
        IF v_nature_up = 'sp_defensa' THEN SET v_mult_sp_defensa = 1.1; END IF;
        IF v_nature_up = 'velocidad' THEN SET v_mult_velocidad = 1.1; END IF;
        
        IF v_nature_down = 'ataque' THEN SET v_mult_ataque = 0.9; END IF;
        IF v_nature_down = 'defensa' THEN SET v_mult_defensa = 0.9; END IF;
        IF v_nature_down = 'sp_ataque' THEN SET v_mult_sp_ataque = 0.9; END IF;
        IF v_nature_down = 'sp_defensa' THEN SET v_mult_sp_defensa = 0.9; END IF;
        IF v_nature_down = 'velocidad' THEN SET v_mult_velocidad = 0.9; END IF;
    END IF;
    
    -- Calcular nuevo max_hp
    SET v_new_max_hp = FLOOR(((2 * v_base_hp + v_iv_hp) * v_nivel) / 100) + v_nivel + 10;
    
    -- Calcular nuevo hp actual
    SET v_old_max_hp = IFNULL(v_old_max_hp, v_new_max_hp);
    SET v_hp_actual = IFNULL(v_hp_actual, v_old_max_hp);
    SET v_hp_difference = v_new_max_hp - v_old_max_hp;
    
    -- Recalcular estadísticas
    UPDATE pokemon_box
    SET 
        max_hp = v_new_max_hp,
        hp = LEAST(v_hp_actual + v_hp_difference, v_new_max_hp),
        ataque = FLOOR((FLOOR(((2 * v_base_ataque + v_iv_ataque) * v_nivel) / 100) + 5) * v_mult_ataque),
        defensa = FLOOR((FLOOR(((2 * v_base_defensa + v_iv_defensa) * v_nivel) / 100) + 5) * v_mult_defensa),
        sp_ataque = FLOOR((FLOOR(((2 * v_base_sp_ataque + v_iv_sp_ataque) * v_nivel) / 100) + 5) * v_mult_sp_ataque),
        sp_defensa = FLOOR((FLOOR(((2 * v_base_sp_defensa + v_iv_sp_defensa) * v_nivel) / 100) + 5) * v_mult_sp_defensa),
        velocidad = FLOOR((FLOOR(((2 * v_base_velocidad + v_iv_velocidad) * v_nivel) / 100) + 5) * v_mult_velocidad),
        iv_hp = v_iv_hp,
        iv_ataque = v_iv_ataque,
        iv_defensa = v_iv_defensa,
        iv_sp_ataque = v_iv_sp_ataque,
        iv_sp_defensa = v_iv_sp_defensa,
        iv_velocidad = v_iv_velocidad
    WHERE id = pokemon_id;
END$$

DELIMITER ;
