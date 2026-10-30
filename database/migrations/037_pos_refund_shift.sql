SET @pos_refund_shift_column_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pos_refunds' AND COLUMN_NAME = 'register_shift_id'
);
SET @pos_refund_shift_sql = IF(@pos_refund_shift_column_exists = 0,
  'ALTER TABLE pos_refunds ADD COLUMN register_shift_id BIGINT UNSIGNED NULL AFTER sale_id', 'SELECT 1');
PREPARE pos_refund_shift_statement FROM @pos_refund_shift_sql;
EXECUTE pos_refund_shift_statement;
DEALLOCATE PREPARE pos_refund_shift_statement;

UPDATE pos_refunds r
JOIN pos_sales s ON s.id = r.sale_id AND s.company_id = r.company_id
SET r.register_shift_id = s.register_shift_id
WHERE r.register_shift_id IS NULL;

SET @pos_refund_shift_index_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pos_refunds' AND INDEX_NAME = 'pos_refund_company_shift_method'
);
SET @pos_refund_shift_index_sql = IF(@pos_refund_shift_index_exists = 0,
  'ALTER TABLE pos_refunds ADD KEY pos_refund_company_shift_method (company_id, register_shift_id, refund_method)', 'SELECT 1');
PREPARE pos_refund_shift_index_statement FROM @pos_refund_shift_index_sql;
EXECUTE pos_refund_shift_index_statement;
DEALLOCATE PREPARE pos_refund_shift_index_statement;
