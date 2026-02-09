-- Migration 007: Reemplaza iconos problemáticos .png por .svg para evitar bordes negros
USE `rol`;

-- Actualizar superpotion -> usar SVG
UPDATE `items` SET `icono` = 'superpotion.svg' WHERE `clave` = 'superpotion' AND (`icono` LIKE '%superpotion%');

-- Actualizar greatball/superball -> usar SVG
UPDATE `items` SET `icono` = 'superball.svg' WHERE `clave` IN ('greatball','superball') AND (`icono` LIKE '%superball%');

-- (Opcional) Si existieran registros usando los PNG directamente por nombre, actualizarlos también
UPDATE `items` SET `icono` = 'superpotion.svg' WHERE `icono` LIKE '%superpotion.png%';
UPDATE `items` SET `icono` = 'superball.svg' WHERE `icono` LIKE '%superball.png%';

-- Nota: ejecutar este script en la base de datos de producción/local para corregir los iconos usados por la app.
