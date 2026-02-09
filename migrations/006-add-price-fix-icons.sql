-- Migration 006: Añade columna `price` a items y normaliza `icono` (añadir .png si falta)
USE `rol`;

-- Añadir columna price si no existe
ALTER TABLE `items` ADD COLUMN IF NOT EXISTS `price` DECIMAL(10,2) DEFAULT NULL;

-- Normalizar iconos: si no tienen extensión, añadir .png
-- No forzamos .png; en su lugar añadimos extensión por defecto .png sólo si no hay ninguna
UPDATE `items` SET `icono` = CONCAT(`icono`, '.png') WHERE `icono` IS NOT NULL AND `icono` <> '' AND `icono` NOT LIKE '%.%';

-- Establecer precios para claves conocidas
UPDATE `items` SET `price` = CASE
  WHEN `clave` = 'potion' THEN 50.00
  WHEN `clave` = 'superpotion' THEN 120.00
  WHEN `clave` = 'pokeball' THEN 200.00
  WHEN `clave` = 'greatball' OR `clave` = 'superball' THEN 350.00
  WHEN `clave` = 'revive' THEN 300.00
  WHEN `clave` = 'antidote' THEN 40.00
  WHEN `clave` = 'ultraball' THEN 600.00
  WHEN `clave` = 'hyperpotion' THEN 250.00
  WHEN `clave` = 'elixir' THEN 1000.00
  ELSE `price` END
WHERE `clave` IN ('potion','superpotion','pokeball','greatball','superball','revive','antidote','ultraball','hyperpotion','elixir');

-- Nota: ejecutar este script en bases existentes para tener iconos con extensión y precios.
