-- Manager sign-in also requires a workspace code. A person may manage more
-- than one company; directory uniqueness must match that authentication scope.
ALTER TABLE managers DROP INDEX manager_email,
  ADD UNIQUE KEY company_manager_email (company_id,manager_email);
