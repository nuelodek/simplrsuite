CREATE TABLE IF NOT EXISTS talent_tokens (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  account_id INT NOT NULL,
  purpose ENUM('verify','reset') NOT NULL,
  token_hash CHAR(64) NOT NULL UNIQUE,
  expires_at DATETIME NOT NULL,
  used_at DATETIME DEFAULT NULL,
  INDEX account_purpose (account_id, purpose)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Separate from historical imported profiles: listing always requires consent.
CREATE TABLE IF NOT EXISTS talent_portfolios (
  account_id INT PRIMARY KEY,
  headline VARCHAR(160) NOT NULL,
  location VARCHAR(160) NOT NULL DEFAULT '',
  experience VARCHAR(255) NOT NULL DEFAULT '',
  skills TEXT NOT NULL,
  bio TEXT NOT NULL,
  portfolio_url VARCHAR(1000) NOT NULL DEFAULT '',
  availability VARCHAR(80) NOT NULL DEFAULT '',
  visibility ENUM('private','listed') NOT NULL DEFAULT 'private',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
