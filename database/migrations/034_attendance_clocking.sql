SET @clocking_column_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'attendance' AND COLUMN_NAME = 'clock_in'
);
SET @clocking_sql = IF(@clocking_column_exists = 0,
  'ALTER TABLE attendance ADD COLUMN clock_in DATETIME NULL AFTER status', 'SELECT 1');
PREPARE clocking_statement FROM @clocking_sql;
EXECUTE clocking_statement;
DEALLOCATE PREPARE clocking_statement;

SET @clocking_column_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'attendance' AND COLUMN_NAME = 'clock_out'
);
SET @clocking_sql = IF(@clocking_column_exists = 0,
  'ALTER TABLE attendance ADD COLUMN clock_out DATETIME NULL AFTER clock_in', 'SELECT 1');
PREPARE clocking_statement FROM @clocking_sql;
EXECUTE clocking_statement;
DEALLOCATE PREPARE clocking_statement;

SET @clocking_column_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'attendance' AND COLUMN_NAME = 'break_start'
);
SET @clocking_sql = IF(@clocking_column_exists = 0,
  'ALTER TABLE attendance ADD COLUMN break_start DATETIME NULL AFTER clock_out', 'SELECT 1');
PREPARE clocking_statement FROM @clocking_sql;
EXECUTE clocking_statement;
DEALLOCATE PREPARE clocking_statement;

SET @clocking_column_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'attendance' AND COLUMN_NAME = 'break_end'
);
SET @clocking_sql = IF(@clocking_column_exists = 0,
  'ALTER TABLE attendance ADD COLUMN break_end DATETIME NULL AFTER break_start', 'SELECT 1');
PREPARE clocking_statement FROM @clocking_sql;
EXECUTE clocking_statement;
DEALLOCATE PREPARE clocking_statement;
