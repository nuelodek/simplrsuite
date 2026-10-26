CREATE TABLE IF NOT EXISTS workspace_tickets (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  company_id INT NOT NULL,
  author_role VARCHAR(16) NOT NULL,
  author_id INT NOT NULL,
  subject VARCHAR(180) NOT NULL,
  category VARCHAR(32) NOT NULL,
  state VARCHAR(16) NOT NULL DEFAULT 'open',
  version INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY company_author (company_id, author_role, author_id, updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS workspace_ticket_comments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  ticket_id BIGINT UNSIGNED NOT NULL,
  company_id INT NOT NULL,
  author_role VARCHAR(16) NOT NULL,
  author_id INT NOT NULL,
  body TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ticket_comments (company_id,ticket_id,id),
  FOREIGN KEY (ticket_id) REFERENCES workspace_tickets(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
