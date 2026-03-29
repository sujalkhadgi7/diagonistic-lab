-- Full database schema for tables used by the application
-- MySQL 8+

-- 1) Frontend customers table
CREATE TABLE IF NOT EXISTS `customers` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_customers_email` (`email`),
  KEY `idx_customers_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Admin users table (column names kept compatible with existing PHP code)
CREATE TABLE IF NOT EXISTS `user` (
  `ID` INT NOT NULL AUTO_INCREMENT,
  `Name` VARCHAR(120) NOT NULL,
  `Gmail` VARCHAR(150) NOT NULL,
  `username` VARCHAR(80) NOT NULL,
  `Password` VARCHAR(255) NOT NULL,
  `type` ENUM('Admin', 'User') NOT NULL DEFAULT 'User',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `uq_user_username` (`username`),
  UNIQUE KEY `uq_user_gmail` (`Gmail`),
  KEY `idx_user_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Appointments table
CREATE TABLE IF NOT EXISTS `appointment` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `package` TEXT NOT NULL,
  `date` DATETIME DEFAULT NULL,
  `report` LONGTEXT DEFAULT NULL,

  -- Legacy compatibility columns used by one dashboard query
  `patient_name` VARCHAR(120) DEFAULT NULL,
  `test_name` VARCHAR(255) DEFAULT NULL,

  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_appointment_email` (`email`),
  KEY `idx_appointment_date` (`date`),
  KEY `idx_appointment_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Diagnostic packages table
CREATE TABLE IF NOT EXISTS `diagnostic_packages` (
  `id` INT NOT NULL,
  `name` VARCHAR(180) NOT NULL,
  `description` TEXT NOT NULL,
  `pricing` INT NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `tags` JSON NOT NULL,
  `related_packages` JSON NOT NULL,
  `popularity` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_diagnostic_packages_name` (`name`),
  KEY `idx_diagnostic_packages_category` (`category`),
  KEY `idx_diagnostic_packages_pricing` (`pricing`),
  KEY `idx_diagnostic_packages_popularity` (`popularity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Optional upgrade helpers for existing old appointment table
-- Run these if your `appointment` table was created with only name/email/package/date/id.
ALTER TABLE `appointment`
  ADD COLUMN IF NOT EXISTS `phone` VARCHAR(20) DEFAULT NULL AFTER `email`,
  ADD COLUMN IF NOT EXISTS `report` LONGTEXT DEFAULT NULL AFTER `date`,
  ADD COLUMN IF NOT EXISTS `patient_name` VARCHAR(120) DEFAULT NULL AFTER `report`,
  ADD COLUMN IF NOT EXISTS `test_name` VARCHAR(255) DEFAULT NULL AFTER `patient_name`,
  ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  MODIFY COLUMN `date` DATETIME DEFAULT NULL;

ALTER TABLE `appointment`
  ADD INDEX `idx_appointment_email` (`email`),
  ADD INDEX `idx_appointment_date` (`date`),
  ADD INDEX `idx_appointment_created_at` (`created_at`);
