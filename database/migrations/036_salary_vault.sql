CREATE TABLE IF NOT EXISTS salary_vaults (
  company_id INT NOT NULL,
  currency VARCHAR(3) NOT NULL DEFAULT 'NGN',
  balance_kobo BIGINT UNSIGNED NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (company_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS salary_vault_entries (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id INT NOT NULL,
  entry_reference VARCHAR(150) NOT NULL,
  entry_type VARCHAR(30) NOT NULL,
  amount_kobo BIGINT NOT NULL,
  funding_id BIGINT UNSIGNED NULL,
  payroll_item_id BIGINT UNSIGNED NULL,
  actor_role VARCHAR(16) NOT NULL,
  actor_id INT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY salary_vault_entry_once (company_id, entry_reference),
  KEY salary_vault_entry_history (company_id, id),
  KEY salary_vault_entry_funding (company_id, funding_id),
  KEY salary_vault_entry_payroll (company_id, payroll_item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS salary_vault_fundings (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id INT NOT NULL,
  funding_method VARCHAR(16) NOT NULL,
  amount_kobo BIGINT UNSIGNED NOT NULL,
  currency VARCHAR(3) NOT NULL DEFAULT 'NGN',
  status VARCHAR(24) NOT NULL DEFAULT 'pending',
  reference VARCHAR(100) NOT NULL,
  external_reference VARCHAR(120) NULL DEFAULT NULL,
  expected_email VARCHAR(255) NOT NULL DEFAULT '',
  checkout_url VARCHAR(512) NOT NULL DEFAULT '',
  provider_mode VARCHAR(12) NOT NULL DEFAULT '',
  provider_transaction_id VARCHAR(40) NULL DEFAULT NULL,
  note VARCHAR(1000) NOT NULL DEFAULT '',
  created_by_role VARCHAR(16) NOT NULL,
  created_by_id INT NOT NULL,
  approved_by_role VARCHAR(16) NULL DEFAULT NULL,
  approved_by_id INT NULL DEFAULT NULL,
  approved_at DATETIME NULL DEFAULT NULL,
  credited_at DATETIME NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY salary_vault_funding_reference (reference),
  UNIQUE KEY salary_vault_funding_manual_reference (company_id, external_reference),
  UNIQUE KEY salary_vault_provider_transaction (provider_mode, provider_transaction_id),
  KEY salary_vault_funding_queue (company_id, status, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE payroll_runs
  ADD COLUMN created_by_role VARCHAR(16) NOT NULL DEFAULT 'admin' AFTER created_by,
  ADD COLUMN approved_by_role VARCHAR(16) NULL DEFAULT NULL AFTER approved_by;

UPDATE payroll_runs
SET status = 'pending_approval', approved_by = NULL, approved_by_role = NULL, approved_at = NULL
WHERE status = 'approved' AND approved_by_role IS NULL;
