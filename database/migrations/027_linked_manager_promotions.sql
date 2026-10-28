ALTER TABLE `managers`
  ADD COLUMN `employee_id` INT NULL DEFAULT NULL AFTER `department_id`,
  ADD UNIQUE KEY `managers_company_employee` (`company_id`,`employee_id`);

UPDATE `managers` m
JOIN (
  SELECT `company_id`, LOWER(`manager_email`) AS `email_key`, MIN(`id`) AS `manager_id`, COUNT(*) AS `matches`
  FROM `managers`
  GROUP BY `company_id`, LOWER(`manager_email`)
) manager_matches ON manager_matches.`manager_id` = m.`id` AND manager_matches.`matches` = 1
JOIN (
  SELECT `company_id`, LOWER(`email`) AS `email_key`, MIN(`id`) AS `employee_id`, COUNT(*) AS `matches`
  FROM `employees`
  GROUP BY `company_id`, LOWER(`email`)
) employee_matches ON employee_matches.`company_id` = m.`company_id` AND employee_matches.`email_key` = LOWER(m.`manager_email`) AND employee_matches.`matches` = 1
SET m.`employee_id` = employee_matches.`employee_id`;

ALTER TABLE `teams`
  ADD COLUMN `department_id` INT NULL DEFAULT NULL AFTER `team_name`,
  ADD KEY `teams_company_department` (`company_id`,`department_id`);
