ALTER TABLE `employees`
  ADD COLUMN `archived_at` DATETIME NULL DEFAULT NULL,
  ADD COLUMN `archived_by` INT NULL DEFAULT NULL,
  ADD KEY `employees_archive_scope` (`company_id`,`archived_at`);

ALTER TABLE `managers`
  ADD COLUMN `archived_at` DATETIME NULL DEFAULT NULL,
  ADD COLUMN `archived_by` INT NULL DEFAULT NULL,
  ADD KEY `managers_archive_scope` (`company_id`,`archived_at`);

ALTER TABLE `tasks`
  ADD COLUMN `archived_at` DATETIME NULL DEFAULT NULL,
  ADD COLUMN `archived_by` INT NULL DEFAULT NULL,
  ADD KEY `tasks_archive_scope` (`company_id`,`archived_at`);

ALTER TABLE `projects`
  ADD COLUMN `archived_at` DATETIME NULL DEFAULT NULL,
  ADD COLUMN `archived_by` INT NULL DEFAULT NULL,
  ADD KEY `projects_archive_scope` (`company_id`,`archived_at`);
