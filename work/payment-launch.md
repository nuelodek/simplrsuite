# Payment launch: Paystack + Flutterwave

The human's current choice is **Paystack for NGN and Flutterwave for USD**.
The earlier no-payments choice is superseded. This code is in the canonical
`simplrsuite` workspace. No provider account, key, DNS, webhook dashboard or live
transaction has been configured or verified by this work.

## Source pricing and behavior

Read-only reference: `C:/Program Files/Ampps/www/simplr24/users/admin/subpro.php`,
lines 495–510 (NGN prices and conversion fallback), 573–588 (cart conversion),
597–618 (currency chooses provider). That source has NGN base prices and a
client-side currency API with a fallback, rather than an independent USD price
catalog. Its original credentials were not copied or called.

The server preserves those NGN prices and the original fallback conversion of
**0.00063 USD per NGN**, now an explicitly displayed fixed business price
conversion controlled by `BILLING_USD_RATE`. It is not a fetched market rate.
The cart is converted once and rounded to cents: Projects for one month is
USD 2.21; two months is USD 4.41. Orders snapshot the rate and NGN source prices.
Changing configuration does not recalculate an existing order's amount.

| Toolkit | NGN per month |
| --- | ---: |
| Finance & inventory | 3,000 |
| Recruiting | 2,500 |
| Sales & CRM | 4,000 |
| Security | 4,000 |
| Projects & tasks | 3,500 |
| Business strategy | 3,500 |
| Compliance | 3,000 |
| Product development | 4,000 |
| Office assets | 3,500 |

The old separate Sales offer used the same record tools as CRM. They are one
purchase here, avoiding a duplicate charge for the same functionality.
Subscriptions are prepaid for 1–12 months with manual renewal. Automatic recurring
charges, refunds, disputes, banking and payroll disbursements are not implemented.

The current local workspace does not have usable provider sandbox credentials.
The legacy checkout only supplies a Paystack public test key, which cannot be used
by this server-side hosted-checkout flow. A Paystack test secret was also found
hardcoded in legacy source; treat it as exposed and do not reuse it. No Flutterwave
v3 test secret or webhook hash was found. Neither provider has been enabled or
charged. Issue fresh sandbox credentials and configure them only in the ignored
local `.env` (or the deployment secret store); never paste them into chat,
source files, or commit history.

## Deployment configuration

Configure secrets on the host, never in JavaScript, source history or browser
storage. Rotate previously committed credentials. Use newly issued provider keys.

- `PAYSTACK_MODE=test` selects the shared test entitlement environment for both
  providers. Use `live` only after provider sandbox checks and account activation.
- `PAYSTACK_ENABLED=true`, `PAYSTACK_SECRET_KEY`: Paystack key matching that mode.
- `FLUTTERWAVE_ENABLED=true`, `FLUTTERWAVE_SECRET_KEY`: Flutterwave **v3** secret
  key matching the same mode. `FLUTTERWAVE_WEBHOOK_HASH` must be a random secret
  of at least 32 characters, also set in the Flutterwave dashboard.
- Review `BILLING_USD_RATE=0.00063` as a pricing decision. Confirm the Flutterwave
  business account supports USD collections and the intended settlement arrangement.
- `APP_ENV=production`, `APP_URL` and audience origins must be real HTTPS origins.
  Return URLs use the configured business origin; no request Host controls them.
- `BILLING_ENFORCE=true` makes expired/unpaid toolkit records read-only, retaining
  exports. Existing customers need an explicit entitlement migration decision
  before enabling this. Local preview keeps enforcement off.
- Configure `https://<business-host>/api/paystack-webhook.php` in Paystack and
  `https://<business-host>/api/flutterwave-webhook.php` in Flutterwave. Enable
  provider retries, including Flutterwave's retry option, and check deliveries.

Checkout is disabled when the selected provider is unconfigured. No old provider
SDK demo or client-supplied amount can create access. The owner can recheck an
order from Billing after a lost callback. A callback's success query string does
not grant access. Both providers renew the same toolkit entitlement within the
same test/live environment; test payments cannot grant live access.

## Verification and operating limits

Paystack uses hosted initialization, reference verification, integer kobo, and
HMAC-SHA512 on the exact webhook body. Flutterwave uses hosted v3 checkout,
verification by the stored `tx_ref`, exact USD cents, and the v3 `verif-hash`
header. The webhook amount and browser transaction ID are not trusted. Both
paths verify the customer, reference, currency, amount and successful status.
Provider+mode+transaction identity is unique. Transactional settlement and nonce
deduplication protect against repeated requests and concurrent confirmations.

API requests have bounded timeouts, fixed HTTPS origins and TLS verification.
Transient webhook verification failures return a retryable response. A mismatch
records a review event and grants no access. Monitor provider delivery failures
and `billing_events`; there is no deployed background reconciliation worker or
automatic dispute/refund entitlement handling yet. Operators must investigate
those cases before a paid launch can be described as fully operational.

No provider API calls or charges were made by the local regression tests. CLI
tests use fake transports; HTTP tests acknowledge already-paid synthetic orders.
For local reproduction, run the normal server on 8000 and an isolated Apache
instance on 8001 for the route tests, then a helper on 8002:

```powershell
php -S 127.0.0.1:8002 -t . scripts/payment_test_router.php
php scripts/paystack_test.php
php scripts/flutterwave_test.php
python scripts/paystack_http_test.py
node scripts/workspace_browser_test.cjs
```

The helper contains obvious fake credentials and is restricted to local/log-mail
mode. Do not use it for an actual checkout. Browser tests block external requests.
Full fixtures come from consolidation, access, operations, help and attachment
tests in that order. Local configuration is not evidence of provider activation.

Required provider acceptance checks on a deployed test HTTPS host: successful
checkout in each currency, failed/cancelled checkout, correct charged amount,
webhook delivery/retry, duplicate delivery, return after a lost session, renewal
across providers, expired editing with readable exports, and reconciliation after
a missed callback. Real charges remain outside the current authorization.

Primary contracts reviewed:
[Paystack verification](https://paystack.com/docs/payments/verify-payments/),
[Paystack webhooks](https://paystack.com/docs/payments/webhooks/),
[Flutterwave Standard](https://developer.flutterwave.com/docs/flutterwave-standard-1),
[Flutterwave verification](https://developer.flutterwave.com/docs/transaction-verification),
[Flutterwave reference verification](https://developer.flutterwave.com/reference/verify-transaction-with-tx_ref),
[Flutterwave v3 webhooks](https://developer.flutterwave.com/docs/webhooks).
