-- When a process claimed an alert email for sending. notification_deliver() claims a
-- message before sending it, so a web request and the scheduler never both send it.
-- A claim older than ten minutes belongs to a run that died and may be retried.
ALTER TABLE `notifications`
  ADD COLUMN `email_claimed_at` DATETIME NULL DEFAULT NULL AFTER `email_attempts`;
