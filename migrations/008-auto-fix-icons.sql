-- Migration 008: Actualiza `items.icono` a .svg cuando exista el fichero SVG correspondiente
USE `rol`;

-- Para cada SVG presente en img/items/ (excepto default.svg) actualizamos las filas que apunten a la versión PNG u otras variantes

-- Super Poción
UPDATE `items` SET `icono` = 'superpotion.svg'
  WHERE (LOWER(`icono`) LIKE '%superpotion%' OR LOWER(`clave`) = 'superpotion')
    AND LOWER(`icono`) NOT LIKE '%.svg%';

-- Super Ball / Great Ball
UPDATE `items` SET `icono` = 'superball.svg'
  WHERE (LOWER(`icono`) LIKE '%superball%' OR LOWER(`clave`) IN ('greatball','superball'))
    AND LOWER(`icono`) NOT LIKE '%.svg%';

-- Nota: esta migración solo cambia las entradas que actualmente NO apunten a un .svg
-- Ejecuta: mysql -u root -p < migrations/008-auto-fix-icons.sql
