-- Agregar columna nivel_requerido a movimientos
ALTER TABLE movimientos ADD COLUMN nivel_requerido INT DEFAULT 1 NOT NULL AFTER pp;

-- Actualizar algunos movimientos con niveles de ejemplo
-- Movimientos básicos (nivel 1-10)
UPDATE movimientos SET nivel_requerido = 1 WHERE nombre IN ('Ataque Rápido', 'Placaje', 'Bofetón Lodo');
UPDATE movimientos SET nivel_requerido = 5 WHERE nombre IN ('Puño Fuego');
UPDATE movimientos SET nivel_requerido = 7 WHERE nombre IN ('Defensa Férrea', 'Danza Espada');
UPDATE movimientos SET nivel_requerido = 10 WHERE nombre IN ('Rayo Hielo', 'Rayo');

-- Movimientos intermedios (nivel 15-30)
UPDATE movimientos SET nivel_requerido = 15 WHERE nombre IN ('Poder Psíquico');
UPDATE movimientos SET nivel_requerido = 20 WHERE nombre IN ('Terremoto', 'Destello Espectral');
UPDATE movimientos SET nivel_requerido = 25 WHERE nombre IN ('Hiperrayo', 'Hidrobomba');

-- Movimientos avanzados (nivel 35+)
UPDATE movimientos SET nivel_requerido = 30 WHERE potencia >= 95 AND categoria != 'estado';
UPDATE movimientos SET nivel_requerido = 35 WHERE potencia >= 110;
UPDATE movimientos SET nivel_requerido = 40 WHERE potencia >= 120;

-- Verificar cambios
SELECT id, nombre, potencia, pp, nivel_requerido, categoria 
FROM movimientos 
ORDER BY nivel_requerido, nombre 
LIMIT 20;
