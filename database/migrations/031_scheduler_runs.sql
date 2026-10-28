-- One row per scheduled job: when it last started and finished, how it ended and
-- how many runs in a row have failed. scripts/scheduler_status.php reads it so a
-- stopped or failing cron job raises an alert instead of going quiet.
CREATE TABLE IF NOT EXISTS `scheduler_runs` (
  `job` VARCHAR(64) NOT NULL,
  `last_started_at` DATETIME NULL DEFAULT NULL,
  `last_finished_at` DATETIME NULL DEFAULT NULL,
  `last_succeeded_at` DATETIME NULL DEFAULT NULL,
  `last_status` VARCHAR(20) NOT NULL DEFAULT 'never',
  `last_duration_ms` INT UNSIGNED NULL DEFAULT NULL,
  `last_summary` VARCHAR(500) NULL DEFAULT NULL,
  `last_error` VARCHAR(255) NULL DEFAULT NULL,
  `consecutive_failures` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`job`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
