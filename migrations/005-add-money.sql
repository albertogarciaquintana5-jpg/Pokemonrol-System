-- Migration 005: Añade columna `money` y tabla de transacciones (seguridad adicional)
USE `rol`;

-- Añadir columna `money` si no existe
ALTER TABLE `usuarios` ADD COLUMN IF NOT EXISTS `money` DECIMAL(10,2) NOT NULL DEFAULT 0;

-- Tabla opcional para registrar transacciones (compras/ventas/donaciones)
CREATE TABLE IF NOT EXISTS `money_transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `meta` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Nota: ejecutar este archivo si la migración 004 ya fue aplicada en una base existente.
