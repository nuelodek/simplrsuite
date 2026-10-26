-- CRM identities belong to each company. Keep all existing records intact.
ALTER TABLE leads DROP INDEX lead_email,
  ADD UNIQUE KEY company_lead_email (company_id,lead_email);
ALTER TABLE contacts DROP INDEX contact_email,
  ADD UNIQUE KEY company_contact_email (company_id,contact_email);
ALTER TABLE accounts DROP INDEX account_email,
  ADD UNIQUE KEY company_account_email (company_id,account_email);
