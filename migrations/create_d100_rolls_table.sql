-- ============================================
-- MIGRACIÓN: Añadir tabla d100_rolls
-- ============================================
-- Descripción: Tabla para almacenar el historial de tiradas de dados D100
-- ============================================

CREATE TABLE IF NOT EXISTS `d100_rolls` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `value` INT(11) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_created` (`user_id`, `created_at` DESC),
  CONSTRAINT `fk_d100_rolls_user` FOREIGN KEY (`user_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Índice para mejorar el rendimiento de las consultas de historial
CREATE INDEX idx_d100_user_id ON d100_rolls(user_id);
