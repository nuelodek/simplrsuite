CREATE TABLE IF NOT EXISTS `integration_connections` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` INT NOT NULL,
  `provider` VARCHAR(30) NOT NULL,
  `auth_type` VARCHAR(20) NOT NULL,
  `account_login` VARCHAR(100) NOT NULL,
  `account_name` VARCHAR(160) NOT NULL DEFAULT '',
  `account_url` VARCHAR(255) NOT NULL DEFAULT '',
  `token_ciphertext` TEXT NOT NULL,
  `token_scopes` VARCHAR(255) NOT NULL DEFAULT '',
  `connected_by` INT NOT NULL,
  `connected_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_checked_at` DATETIME NULL DEFAULT NULL,
  `last_error` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `integration_connections_company_provider` (`company_id`,`provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `github_repository_links` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` INT NOT NULL,
  `project_id` INT NOT NULL,
  `repo_full_name` VARCHAR(200) NOT NULL,
  `repo_id` BIGINT UNSIGNED NOT NULL,
  `html_url` VARCHAR(255) NOT NULL,
  `is_private` TINYINT(1) NOT NULL DEFAULT 0,
  `default_branch` VARCHAR(120) NOT NULL DEFAULT '',
  `description` VARCHAR(500) NOT NULL DEFAULT '',
  `linked_by` INT NOT NULL,
  `linked_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_synced_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `github_links_company_project_repo` (`company_id`,`project_id`,`repo_full_name`),
  KEY `github_links_company_repo` (`company_id`,`repo_full_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `github_issue_tasks` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` INT NOT NULL,
  `link_id` BIGINT UNSIGNED NOT NULL,
  `issue_number` INT UNSIGNED NOT NULL,
  `issue_url` VARCHAR(255) NOT NULL,
  `task_id` INT NOT NULL,
  `imported_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `github_issue_once_per_link` (`company_id`,`link_id`,`issue_number`),
  KEY `github_issue_task` (`company_id`,`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `integration_cache` (
  `company_id` INT NOT NULL,
  `cache_key` CHAR(64) NOT NULL,
  `payload` MEDIUMTEXT NOT NULL,
  `expires_at` DATETIME NOT NULL,
  PRIMARY KEY (`company_id`,`cache_key`),
  KEY `integration_cache_expiry` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
