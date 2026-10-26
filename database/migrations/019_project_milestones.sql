ALTER TABLE `projects`
  ADD COLUMN `milestoneName` VARCHAR(160) NULL AFTER `projectDescription`,
  ADD COLUMN `milestoneDate` DATE NULL AFTER `milestoneName`,
  ADD COLUMN `milestoneStatus` ENUM('not started','in progress','completed') NOT NULL DEFAULT 'not started' AFTER `milestoneDate`,
  ADD COLUMN `milestoneDescription` TEXT NULL AFTER `milestoneStatus`;
