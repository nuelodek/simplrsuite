ALTER TABLE payroll_items
  ADD COLUMN overtime_pay DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER bonus,
  ADD COLUMN voluntary_pension DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER pension,
  ADD COLUMN salary_advance_deduction DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER voluntary_pension,
  ADD COLUMN employer_pension DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER salary_advance_deduction,
  ADD COLUMN employer_nsitf DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER employer_pension,
  ADD COLUMN employer_itf DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER employer_nsitf;
