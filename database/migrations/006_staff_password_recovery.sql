CREATE TABLE IF NOT EXISTS staff_password_resets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  staff_role ENUM('employee','manager') NOT NULL,
  auth_id INT NOT NULL,
  staff_id INT NOT NULL,
  company_id INT UNSIGNED NOT NULL,
  email VARCHAR(255) NOT NULL,
  auth_stamp CHAR(64) NOT NULL,
  token_hash CHAR(64) NOT NULL UNIQUE,
  expires_at DATETIME NOT NULL,
  used_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX account_scope (company_id, staff_role, auth_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
