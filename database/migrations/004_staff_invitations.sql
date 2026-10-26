CREATE TABLE IF NOT EXISTS staff_invitations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  staff_role ENUM('employee','manager') NOT NULL,
  staff_id INT NOT NULL,
  email VARCHAR(255) NOT NULL,
  token_hash CHAR(64) NOT NULL UNIQUE,
  expires_at DATETIME NOT NULL,
  used_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX invitation_staff (company_id, staff_role, staff_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
