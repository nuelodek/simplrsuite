CREATE TABLE IF NOT EXISTS billing_orders (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  company_id INT NOT NULL,
  created_by INT NOT NULL,
  reference VARCHAR(80) NOT NULL,
  request_nonce CHAR(64) NOT NULL,
  expected_email VARCHAR(255) NOT NULL,
  amount_minor BIGINT UNSIGNED NOT NULL,
  currency CHAR(3) NOT NULL,
  provider_mode VARCHAR(8) NOT NULL,
  items_json TEXT NOT NULL,
  state VARCHAR(32) NOT NULL DEFAULT 'initializing',
  checkout_url VARCHAR(512) DEFAULT NULL,
  provider_transaction_id VARCHAR(40) DEFAULT NULL,
  paid_at DATETIME DEFAULT NULL,
  fulfilled_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY reference_unique (reference),
  UNIQUE KEY company_nonce (company_id,request_nonce),
  UNIQUE KEY provider_payment (provider_mode,provider_transaction_id),
  KEY company_orders (company_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS billing_entitlements (
  company_id INT NOT NULL,
  tool_key VARCHAR(32) NOT NULL,
  provider_mode VARCHAR(8) NOT NULL,
  valid_until DATETIME NOT NULL,
  last_order_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (company_id,tool_key,provider_mode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS billing_events (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED DEFAULT NULL,
  event_type VARCHAR(40) NOT NULL,
  result VARCHAR(40) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY order_events (order_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
