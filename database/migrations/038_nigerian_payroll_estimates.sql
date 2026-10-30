ALTER TABLE payroll_profiles
  ADD COLUMN monthly_housing DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER monthly_salary,
  ADD COLUMN monthly_transport DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER monthly_housing,
  ADD COLUMN annual_rent_paid DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER monthly_allowances,
  ADD COLUMN pension_applicable TINYINT(1) NOT NULL DEFAULT 0 AFTER annual_rent_paid,
  ADD COLUMN nhf_applicable TINYINT(1) NOT NULL DEFAULT 0 AFTER pension_applicable,
  ADD COLUMN rent_relief_documented TINYINT(1) NOT NULL DEFAULT 0 AFTER nhf_applicable,
  ADD COLUMN rent_document_reference VARCHAR(255) NOT NULL DEFAULT '' AFTER rent_relief_documented,
  ADD COLUMN previous_paye DECIMAL(15,2) NULL DEFAULT NULL AFTER rent_document_reference,
  ADD COLUMN previous_pension DECIMAL(15,2) NULL DEFAULT NULL AFTER previous_paye,
  ADD COLUMN previous_nhf DECIMAL(15,2) NULL DEFAULT NULL AFTER previous_pension;

ALTER TABLE payroll_items
  ADD COLUMN housing DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER basic_pay,
  ADD COLUMN transport DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER housing,
  ADD COLUMN annual_rent_paid DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER allowances,
  ADD COLUMN pension_applicable TINYINT(1) NOT NULL DEFAULT 0 AFTER annual_rent_paid,
  ADD COLUMN nhf_applicable TINYINT(1) NOT NULL DEFAULT 0 AFTER pension_applicable,
  ADD COLUMN rent_relief_documented TINYINT(1) NOT NULL DEFAULT 0 AFTER nhf_applicable,
  ADD COLUMN rent_document_reference VARCHAR(255) NOT NULL DEFAULT '' AFTER rent_relief_documented;

ALTER TABLE payroll_runs
  ADD COLUMN statutory_settings_version VARCHAR(40) NOT NULL DEFAULT '' AFTER status,
  ADD COLUMN statutory_applied_by INT NULL DEFAULT NULL AFTER statutory_settings_version,
  ADD COLUMN statutory_applied_at DATETIME NULL DEFAULT NULL AFTER statutory_applied_by;
