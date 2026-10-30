ALTER TABLE workspace_settings
  ADD COLUMN company_logo_path VARCHAR(80) NOT NULL DEFAULT '' AFTER timezone;
