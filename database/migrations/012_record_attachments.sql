CREATE TABLE IF NOT EXISTS record_attachments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  company_id INT NOT NULL,
  module_key VARCHAR(40) NOT NULL,
  record_id BIGINT UNSIGNED NOT NULL,
  original_name VARCHAR(180) NOT NULL,
  storage_name CHAR(64) NOT NULL,
  media_type VARCHAR(80) NOT NULL,
  size_bytes INT UNSIGNED NOT NULL,
  content_hash CHAR(64) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY storage_unique (storage_name),
  KEY record_files (company_id,module_key,record_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
