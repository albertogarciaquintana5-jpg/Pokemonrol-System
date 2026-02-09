-- ============================================
-- Insertar datos de pokemon_species con tipos
-- ============================================
-- Orden de stats: hp, ataque, defensa, velocidad, sp_ataque, sp_defensa

-- Insertar especies con sus tipos asignados (usando REPLACE para actualizar si existen)
REPLACE INTO `pokemon_species` (`id`, `nombre`, `sprite`, `tipo_primario_id`, `tipo_secundario_id`, `hp`, `ataque`, `defensa`, `velocidad`, `sp_ataque`, `sp_defensa`) VALUES
(1, 'Pikachu', 'pikachu.png', 5, NULL, 45, 49, 49, 45, 65, 65),
(2, 'Charmander', 'charmander.png', 2, NULL, 39, 52, 43, 65, 60, 50),
(3, 'Bulbasaur', 'bulbasur.png', 4, 8, 35, 55, 40, 90, 50, 50),
(4, 'Greninja', 'greninja.png', 3, 16, 25, 20, 15, 90, 105, 55),
(5, 'Zekrom', 'zekrom.png', 15, 5, 40, 35, 40, 35, 50, 40);
