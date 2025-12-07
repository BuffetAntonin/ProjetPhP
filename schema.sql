-- SQL schema for simple users table
CREATE TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(191) NOT NULL UNIQUE, -- FIXED: Reduced from VARCHAR(255) to avoid the 1000-byte key limit with utf8mb4
  `password_hash` VARCHAR(255) NOT NULL,
  `activation_token` VARCHAR(64) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 0,
  `reset_token` VARCHAR(64) DEFAULT NULL,
  `reset_expires` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);