CREATE TABLE IF NOT EXISTS staff_tool_access (
  company_id INT NOT NULL,
  staff_role VARCHAR(16) NOT NULL,
  staff_id INT NOT NULL,
  module_key VARCHAR(40) NOT NULL,
  access_level VARCHAR(8) NOT NULL,
  granted_by INT NOT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (company_id, staff_role, staff_id, module_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS workspace_audit (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  company_id INT NOT NULL,
  actor_role VARCHAR(16) NOT NULL,
  actor_id INT NOT NULL,
  action VARCHAR(80) NOT NULL,
  target_type VARCHAR(40) NOT NULL,
  target_id VARCHAR(80) NOT NULL,
  details TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY company_created (company_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
