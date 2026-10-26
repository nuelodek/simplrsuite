ALTER TABLE billing_orders
  ADD COLUMN provider VARCHAR(20) NOT NULL DEFAULT 'paystack' AFTER currency,
  DROP INDEX provider_payment,
  ADD UNIQUE KEY provider_payment (provider,provider_mode,provider_transaction_id);
