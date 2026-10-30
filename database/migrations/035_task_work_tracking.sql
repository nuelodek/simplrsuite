ALTER TABLE `tasks`
  ADD COLUMN `estimated_hours` DECIMAL(7,2) NULL DEFAULT NULL AFTER `due_date`;

CREATE TABLE `task_work_sessions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` INT NOT NULL,
  `task_id` INT NOT NULL,
  `employee_id` INT NOT NULL,
  `started_at` DATETIME NOT NULL,
  `ended_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `task_work_task_employee` (`company_id`,`task_id`,`employee_id`,`started_at`),
  KEY `task_work_employee_active` (`company_id`,`employee_id`,`ended_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
