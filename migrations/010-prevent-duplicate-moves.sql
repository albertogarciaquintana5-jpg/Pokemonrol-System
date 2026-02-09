-- Migration 010: Prevent duplicate moves per pokemon_box
-- Removes duplicate entries in pokemon_movimiento (keeps lowest slot)
-- and adds a UNIQUE index on (pokemon_box_id, movimiento_id) to enforce uniqueness
-- Usage: mysql -u root -p rol < migrations/010-prevent-duplicate-moves.sql

USE `rol`;

START TRANSACTION;

-- Remove duplicates: keep the row with the smallest slot for each (pokemon_box_id, movimiento_id)
DELETE pm FROM pokemon_movimiento pm
JOIN (
  SELECT pokemon_box_id, movimiento_id, MIN(slot) AS keep_slot
  FROM pokemon_movimiento
  GROUP BY pokemon_box_id, movimiento_id
  HAVING COUNT(*) > 1
) dup ON pm.pokemon_box_id = dup.pokemon_box_id
       AND pm.movimiento_id = dup.movimiento_id
       AND pm.slot != dup.keep_slot;

-- Add unique index to prevent duplicates in future
ALTER TABLE pokemon_movimiento
  ADD UNIQUE KEY `uq_pokemon_movimiento` (`pokemon_box_id`, `movimiento_id`);

COMMIT;

-- NOTE:
-- If you run this migration and the ALTER fails because the index already exists, it is safe to ignore.
-- Run this on a backup or during maintenance window if you have many rows to avoid locking issues.
