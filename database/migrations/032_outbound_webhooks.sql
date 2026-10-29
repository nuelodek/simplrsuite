CREATE TABLE IF NOT EXISTS `webhook_destinations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` INT NOT NULL,
  `created_by` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `url_ciphertext` TEXT NOT NULL,
  `signing_secret_ciphertext` TEXT NOT NULL,
  `enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `last_delivery_at` DATETIME DEFAULT NULL,
  `last_delivery_status` VARCHAR(24) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `webhook_destinations_company` (`company_id`,`enabled`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `webhook_destination_modules` (
  `company_id` INT NOT NULL,
  `destination_id` BIGINT UNSIGNED NOT NULL,
  `module_key` VARCHAR(32) NOT NULL,
  PRIMARY KEY (`destination_id`,`module_key`),
  KEY `webhook_destination_modules_company` (`company_id`,`module_key`),
  CONSTRAINT `webhook_destination_modules_destination_fk` FOREIGN KEY (`destination_id`) REFERENCES `webhook_destinations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `webhook_outbox` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` INT NOT NULL,
  `destination_id` BIGINT UNSIGNED NOT NULL,
  `event_id` CHAR(32) NOT NULL,
  `event_name` VARCHAR(64) NOT NULL,
  `payload_json` MEDIUMTEXT NOT NULL,
  `state` ENUM('pending','sending','delivered','failed') NOT NULL DEFAULT 'pending',
  `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `next_attempt_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `delivered_at` DATETIME DEFAULT NULL,
  `last_error` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `webhook_outbox_event` (`event_id`),
  KEY `webhook_outbox_ready` (`state`,`next_attempt_at`,`id`),
  KEY `webhook_outbox_destination` (`company_id`,`destination_id`,`state`),
  CONSTRAINT `webhook_outbox_destination_fk` FOREIGN KEY (`destination_id`) REFERENCES `webhook_destinations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
