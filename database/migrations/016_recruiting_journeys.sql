CREATE TABLE IF NOT EXISTS job_posts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  company_id INT NOT NULL,
  public_id CHAR(32) NOT NULL,
  title VARCHAR(150) NOT NULL,
  location VARCHAR(160) NOT NULL,
  workplace VARCHAR(20) NOT NULL,
  employment_type VARCHAR(30) NOT NULL,
  description TEXT NOT NULL,
  requirements TEXT NOT NULL,
  closing_date DATE DEFAULT NULL,
  timezone VARCHAR(64) NOT NULL,
  closes_at DATETIME DEFAULT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'draft',
  published_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY job_public_id (public_id),
  KEY company_jobs (company_id,id),
  KEY published_jobs (status,closes_at,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Existing manually entered applications remain intact and in the same register.
ALTER TABLE applications
  MODIFY applicant_name VARCHAR(150) DEFAULT NULL,
  MODIFY email VARCHAR(255) DEFAULT NULL,
  MODIFY position_applied_for VARCHAR(150) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS application_submissions (
  application_id INT NOT NULL PRIMARY KEY,
  company_id INT NOT NULL,
  job_id BIGINT UNSIGNED NOT NULL,
  account_id INT NOT NULL,
  profile_snapshot TEXT NOT NULL,
  candidate_note TEXT NOT NULL,
  resume_attachment_id BIGINT UNSIGNED DEFAULT NULL,
  consented_at DATETIME NOT NULL,
  withdrawn_at DATETIME DEFAULT NULL,
  UNIQUE KEY one_application_per_job (job_id,account_id),
  KEY candidate_applications (account_id,application_id),
  KEY employer_applications (company_id,application_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
