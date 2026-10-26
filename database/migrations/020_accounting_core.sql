CREATE TABLE IF NOT EXISTS `accounting_accounts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` INT UNSIGNED NOT NULL,
  `account_code` VARCHAR(20) NOT NULL,
  `account_name` VARCHAR(120) NOT NULL,
  `category` ENUM('current_asset','non_current_asset','current_liability','non_current_liability','equity','revenue','expense') NOT NULL,
  `normal_balance` ENUM('debit','credit') NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounting_accounts_company_code` (`company_id`,`account_code`),
  KEY `accounting_accounts_company_category` (`company_id`,`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `accounting_journal_entries` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` INT UNSIGNED NOT NULL,
  `entry_date` DATE NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `reference` VARCHAR(100) NOT NULL DEFAULT '',
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `accounting_journal_company_date` (`company_id`,`entry_date`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `accounting_journal_lines` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `journal_entry_id` BIGINT UNSIGNED NOT NULL,
  `account_id` BIGINT UNSIGNED NOT NULL,
  `debit` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `credit` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `accounting_journal_lines_entry` (`journal_entry_id`),
  KEY `accounting_journal_lines_account` (`account_id`),
  CONSTRAINT `accounting_journal_lines_entry_fk` FOREIGN KEY (`journal_entry_id`) REFERENCES `accounting_journal_entries` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `accounting_journal_lines_account_fk` FOREIGN KEY (`account_id`) REFERENCES `accounting_accounts` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
