CREATE TABLE IF NOT EXISTS `chat_conversations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` INT UNSIGNED NOT NULL,
  `kind` ENUM('direct','group') NOT NULL,
  `name` VARCHAR(120) DEFAULT NULL,
  `direct_key` CHAR(64) DEFAULT NULL,
  `created_by_role` VARCHAR(16) NOT NULL,
  `created_by_id` INT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chat_direct_unique` (`company_id`,`direct_key`),
  KEY `chat_company_updated` (`company_id`,`updated_at`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chat_members` (
  `company_id` INT UNSIGNED NOT NULL,
  `conversation_id` BIGINT UNSIGNED NOT NULL,
  `member_role` VARCHAR(16) NOT NULL,
  `member_id` INT NOT NULL,
  `joined_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`company_id`,`conversation_id`,`member_role`,`member_id`),
  KEY `chat_member_inbox` (`company_id`,`member_role`,`member_id`,`conversation_id`),
  CONSTRAINT `chat_member_conversation_fk` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` INT UNSIGNED NOT NULL,
  `conversation_id` BIGINT UNSIGNED NOT NULL,
  `sender_role` VARCHAR(16) NOT NULL,
  `sender_id` INT NOT NULL,
  `body` MEDIUMTEXT NOT NULL,
  `quote_text` MEDIUMTEXT DEFAULT NULL,
  `quote_conversation_id` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `chat_message_thread` (`company_id`,`conversation_id`,`id`),
  CONSTRAINT `chat_message_conversation_fk` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chat_message_refs` (
  `company_id` INT UNSIGNED NOT NULL,
  `message_id` BIGINT UNSIGNED NOT NULL,
  `token` CHAR(32) NOT NULL,
  `ref_type` ENUM('person','record') NOT NULL,
  `ref_role` VARCHAR(16) DEFAULT NULL,
  `ref_id` VARCHAR(64) NOT NULL,
  `module_key` VARCHAR(40) DEFAULT NULL,
  `label` VARCHAR(255) NOT NULL,
  `target_url` VARCHAR(500) DEFAULT NULL,
  PRIMARY KEY (`message_id`,`token`),
  KEY `chat_ref_person` (`company_id`,`ref_type`,`ref_role`,`ref_id`),
  CONSTRAINT `chat_message_ref_message_fk` FOREIGN KEY (`message_id`) REFERENCES `chat_messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
