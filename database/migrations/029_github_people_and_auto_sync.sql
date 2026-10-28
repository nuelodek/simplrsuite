-- GitHub usernames on employees let synced issues become assigned tasks.
ALTER TABLE `employees`
  ADD COLUMN `github_login` VARCHAR(39) NULL DEFAULT NULL,
  ADD KEY `employees_github_login` (`company_id`,`github_login`);

-- Repository links sync on the scheduler unless turned off.
ALTER TABLE `github_repository_links`
  ADD COLUMN `auto_sync` TINYINT(1) NOT NULL DEFAULT 1 AFTER `last_synced_at`,
  ADD COLUMN `last_sync_error` VARCHAR(255) NOT NULL DEFAULT '' AFTER `auto_sync`;

-- The GitHub assignee last applied to each synced task. A later change on GitHub
-- reassigns the task; a manual change in Simplr is kept until GitHub changes.
ALTER TABLE `github_issue_tasks`
  ADD COLUMN `github_assignee` VARCHAR(39) NOT NULL DEFAULT '' AFTER `task_id`;
