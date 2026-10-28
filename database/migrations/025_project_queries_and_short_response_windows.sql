ALTER TABLE `task_queries`
  ADD COLUMN `project_id` INT NULL DEFAULT NULL AFTER `task_id`,
  ADD KEY `task_queries_project` (`company_id`,`project_id`);

ALTER TABLE `workspace_settings`
  ADD COLUMN `query_response_minutes` INT UNSIGNED NOT NULL DEFAULT 4320 AFTER `query_response_hours`;

UPDATE `workspace_settings`
SET `query_response_minutes` = `query_response_hours` * 60;
