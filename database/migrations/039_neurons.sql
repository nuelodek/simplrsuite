CREATE TABLE IF NOT EXISTS `workspace_neurons` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` INT NOT NULL,
  `created_by` INT NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `neuron_type` VARCHAR(40) NOT NULL,
  `description` VARCHAR(500) NOT NULL DEFAULT '',
  `schedule_type` VARCHAR(12) NOT NULL,
  `schedule_time` TIME NULL DEFAULT NULL,
  `weekday` TINYINT UNSIGNED NULL DEFAULT NULL,
  `timezone` VARCHAR(64) NOT NULL,
  `next_run_at` DATETIME NULL DEFAULT NULL,
  `status` VARCHAR(12) NOT NULL DEFAULT 'active',
  `last_run_at` DATETIME NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `neurons_due` (`status`,`next_run_at`,`id`),
  KEY `neurons_company` (`company_id`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `workspace_neuron_runs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `neuron_id` BIGINT UNSIGNED NOT NULL,
  `company_id` INT NOT NULL,
  `scheduled_for` DATETIME NOT NULL,
  `started_at` DATETIME NOT NULL,
  `finished_at` DATETIME NULL DEFAULT NULL,
  `status` VARCHAR(12) NOT NULL DEFAULT 'running',
  `result_json` TEXT NULL,
  `error_message` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `neuron_runs_company_history` (`company_id`,`neuron_id`,`id`),
  KEY `neuron_runs_started` (`started_at`),
  CONSTRAINT `workspace_neuron_runs_neuron_fk` FOREIGN KEY (`neuron_id`) REFERENCES `workspace_neurons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
