CREATE TABLE IF NOT EXISTS pos_registers (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_by INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY pos_register_company_name (company_id, name),
  KEY pos_register_company_active (company_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pos_shifts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id INT UNSIGNED NOT NULL,
  register_id BIGINT UNSIGNED NOT NULL,
  opened_by_role VARCHAR(16) NOT NULL,
  opened_by_id INT UNSIGNED NOT NULL,
  closed_by_role VARCHAR(16) DEFAULT NULL,
  closed_by_id INT UNSIGNED DEFAULT NULL,
  status ENUM('open','closed') NOT NULL DEFAULT 'open',
  opening_cash DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  closing_cash DECIMAL(12,2) DEFAULT NULL,
  expected_cash DECIMAL(12,2) DEFAULT NULL,
  opened_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  closed_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY pos_shift_company_register_status (company_id, register_id, status),
  KEY pos_shift_company_operator_status (company_id, opened_by_role, opened_by_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pos_sales (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id INT UNSIGNED NOT NULL,
  register_shift_id BIGINT UNSIGNED NOT NULL,
  reference VARCHAR(32) NOT NULL,
  operator_role VARCHAR(16) NOT NULL,
  operator_id INT UNSIGNED NOT NULL,
  customer_id INT UNSIGNED DEFAULT NULL,
  currency CHAR(3) NOT NULL,
  status ENUM('reserved','completed','partial_refund','refunded','cancelled','expired') NOT NULL DEFAULT 'reserved',
  subtotal DECIMAL(12,2) NOT NULL,
  discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  total DECIMAL(12,2) NOT NULL,
  refunded_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  payment_method ENUM('cash','card','other') DEFAULT NULL,
  cash_received DECIMAL(12,2) DEFAULT NULL,
  change_due DECIMAL(12,2) DEFAULT NULL,
  expires_at TIMESTAMP NULL DEFAULT NULL,
  restocked_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  completed_at TIMESTAMP NULL DEFAULT NULL,
  cancelled_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY pos_sale_company_reference (company_id, reference),
  KEY pos_sale_company_status_date (company_id, status, created_at),
  KEY pos_sale_shift_status (register_shift_id, status),
  KEY pos_sale_company_reservation (company_id, status, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pos_sale_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  sale_id BIGINT UNSIGNED NOT NULL,
  company_id INT UNSIGNED NOT NULL,
  inventory_item_id INT UNSIGNED NOT NULL,
  item_name VARCHAR(100) NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  unit_price DECIMAL(12,2) NOT NULL,
  line_total DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (id),
  KEY pos_sale_items_sale (company_id, sale_id),
  KEY pos_sale_items_inventory (company_id, inventory_item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pos_refunds (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id INT UNSIGNED NOT NULL,
  sale_id BIGINT UNSIGNED NOT NULL,
  register_shift_id BIGINT UNSIGNED NOT NULL,
  operator_role VARCHAR(16) NOT NULL,
  operator_id INT UNSIGNED NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  refund_method ENUM('cash','card','other') NOT NULL,
  restocked TINYINT(1) NOT NULL DEFAULT 0,
  note VARCHAR(255) NOT NULL DEFAULT '',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY pos_refund_company_sale (company_id, sale_id, created_at),
  KEY pos_refund_company_shift_method (company_id, register_shift_id, refund_method)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
