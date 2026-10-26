CREATE TABLE IF NOT EXISTS talent_shortlists (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  company_id INT NOT NULL,
  talent_account_id INT NOT NULL,
  created_by INT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY one_talent_shortlist_per_company (company_id,talent_account_id),
  KEY shortlisted_talent (talent_account_id,company_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS talent_outreach (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  company_id INT NOT NULL,
  talent_account_id INT NOT NULL,
  action_type VARCHAR(20) NOT NULL,
  job_id BIGINT UNSIGNED DEFAULT NULL,
  role_title VARCHAR(150) NOT NULL,
  message VARCHAR(1000) NOT NULL DEFAULT '',
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  profile_snapshot TEXT DEFAULT NULL,
  consented_at DATETIME DEFAULT NULL,
  created_by INT NOT NULL,
  responded_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY company_outreach (company_id,status,action_type,id),
  KEY talent_outreach (talent_account_id,status,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
