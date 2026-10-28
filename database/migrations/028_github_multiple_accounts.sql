-- A workspace may connect several GitHub accounts; each repository link remembers
-- which account it reads through. Existing links move to the workspace's account.
ALTER TABLE `integration_connections`
  DROP INDEX `integration_connections_company_provider`,
  ADD UNIQUE KEY `integration_connections_account` (`company_id`,`provider`,`account_login`),
  ADD KEY `integration_connections_company_provider` (`company_id`,`provider`);

ALTER TABLE `github_repository_links`
  ADD COLUMN `connection_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `project_id`,
  ADD KEY `github_links_connection` (`company_id`,`connection_id`);

UPDATE `github_repository_links` l
  JOIN `integration_connections` c ON c.company_id = l.company_id AND c.provider = 'github'
  SET l.connection_id = c.id
  WHERE l.connection_id IS NULL;
