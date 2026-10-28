# Owner actions before launch

Everything in `requirements.html` that code cannot finish on its own: each item needs
your account, your decision or a real server. Ordered by urgency. When you finish
one, tick it here and set the matching item in `requirements.html` to Verified (or
tell an agent, who can record the evidence).

## 1. Rotate exposed credentials (do this first)

A scan on 27 Oct 2026 found real credentials written into old, unused pages. The
values were blanked in the current files (none of these pages can be opened through
the site), but they are still in Git history and in anyone's copy of the
repository, so treat every one as compromised.

| What | Where it was | What to do |
| --- | --- | --- |
| **Paystack live secret key** | `users/admin/createbankaccount.php`, inside browser JavaScript; 13 commits | Paystack dashboard → Settings → API Keys & Webhooks: generate new live keys. The old secret can move money. Check the Transactions and Transfers history for anything you do not recognise. |
| Paystack test secret and public keys | `users/admin/simplrduplo.php`, `users/admin/intialiazepayment.js` | Regenerate the test keys on the same page. |
| Unit (banking) API tokens | `users/admin/createusbankaccount.php`, `users/talents/unitbankdemo.php` | Revoke the tokens in the Unit dashboard, or close the Unit account if it is no longer used. |
| Sentry DSN | `users/admin/sentryinitialize.php` | Sentry → project settings → Client Keys: revoke the key (or delete the project if unused). |
| An email account password | `toolkits/acct_payroll/recordpayment.php`, `users/admin/processtools.php`, `users/admin/revoketools.php` (same password three times) | Change that mailbox's password. It is **not** the SMTP login currently configured in `.env.local` (checked without revealing either). |
| Flutterwave keys | public keys in `users/admin/renewsubscription.php`, `subpro.php`, `intialiazepayment.js`; secret-key patterns in 12 historical commits | Regenerate Flutterwave keys before going live. |
| Google API key | 2 historical commits only | Restrict or delete the key in Google Cloud Console → Credentials. |

Then:
- [ ] Put the new keys only in the server's private `.env` (never in code).
- [ ] Decide whether to rewrite Git history. Rotation is what protects you; rewriting
      history needs a separate plan because everyone must re-clone.
- [ ] Secret scanning for new commits already runs in `.github/workflows/secret-scan.yml`.

## 2. Hosting and addresses

- [ ] Choose the four public addresses (business site, talent site, work app, main
      app) and point DNS at the server. Get TLS certificates.
- [ ] On the server `.env`: `APP_ENV=production`, the four `APP_*_URL` values as
      `https://` addresses, database credentials, and a new `APP_KEY`:
      `php -r 'echo "base64:", base64_encode(random_bytes(32)), PHP_EOL;'`.
      Keep `APP_KEY` in your password manager: it decrypts stored GitHub tokens.
- [ ] Update the GitHub OAuth app's callback to `https://<app address>/users/admin/github.php`.
- [ ] Run `php scripts/preflight.php --production` on the server. Locally it shows
      15 of 27; the rest are these settings.

## 3. Email

- [ ] Pick the sending address and provider; set `SMTP_*` and `MAIL_FROM_*` on the server.
- [ ] Add SPF, DKIM and DMARC records for the sending domain (your provider shows them).
- [ ] Name one test mailbox. An agent then sends one employee and one manager
      invitation from staging and you confirm both arrive and the links work once.

## 4. Payments and pricing

- [ ] Create fresh Paystack (NGN) and Flutterwave (USD) **sandbox** keys for staging.
- [ ] Approve plan prices, the USD conversion rule, refunds and disputes, and the
      date billing enforcement starts (`BILLING_ENFORCE`). See `work/payment-launch.md`.
- [ ] Confirm the live merchant accounts (and USD collection with Flutterwave).

## 5. Legal and privacy

- [ ] Give the legal business name, registered address, privacy contact email and
      phone for the privacy notice (`public/privacypolicy.php`; nothing is invented there).
- [ ] Have a qualified reviewer approve the notice and its date, then set the approval
      in config.
- [ ] Answer the six questions at the end of `docs/data-inventory.md` (retention
      periods, who can request export or deletion, resumes after withdrawal, backups,
      legacy tables, processors).

## 6. Product scope

- [ ] Approve `docs/launch-scope.md` (what works, what is limited, what is not built),
      or mark the rows to change. Marketing and pricing should promise only "Available".
- [ ] Decide whether the "more integrations on the way" catalog stays visible.

## 7. Operations

- [ ] Backups: choose how much data you could afford to lose (RPO), how long a restore
      may take (RTO), how long backups are kept and where (off the server, encrypted).
- [ ] Scheduler: approve installing the cron lines in `deploy/scheduler.md` on staging,
      and name the alert command or person for `scripts/scheduler_status.php`.
- [ ] Error tracking: keep Sentry (with a new key) or remove the package (`docs/dependencies.md`).
- [ ] Attachments: decide on virus scanning or accept the risk (`docs/attachment-operations.md`).
- [ ] Support: name who answers support, security and payment issues (`docs/support-runbook.md`).
- [ ] Capacity: expected number of companies, staff and uploads in the first three
      months, so load limits can be set.

## 8. Release

- [ ] Approve a staging rehearsal and a production window using `deploy/release.md`.
- [ ] Final sign-off once the items above are done. Deployment stays a separate,
      explicit go-ahead.
