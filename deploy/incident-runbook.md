# Incident runbook

What to do when monitoring raises an alert, when a credential leaks, and how to
tell customers. Names in angle brackets are for the owner to fill in; nothing here
invents a contact, a promise or a legal duty. Pair it with `deploy/scheduler.md`
(cron and job health) and `deploy/release.md` (rollback and restore).

## Who does what

| Role | Person | Reachable at |
| --- | --- | --- |
| First responder (on call) | `<name>` | `<phone / chat>` |
| Owner, approves customer messages and restores | `<name>` | `<phone / chat>` |
| Payments contact (Paystack, Flutterwave dashboards) | `<name>` | |
| Hosting contact (server, DNS, backups) | `<name>` | |

## Signals

| Source | How | Alerts on |
| --- | --- | --- |
| `php scripts/ops_check.php --quiet` | cron every 5 minutes; non-zero exit triggers `<alert command>` | database down, unapplied migrations, late/failed jobs, stuck or failed alert emails, payment mismatches and unknown orders, low disk, unwritable storage, stale backups, bursts of application errors |
| `/health.php` | external uptime monitor every minute | site or database unreachable (returns 503) |
| Error references | users quote "Reference: abcd1234" from the error page | look it up in the PHP error log: `Simplr [abcd1234] ExceptionClass at file:line` |

Every application error is logged once with a reference, the exception class and
file:line, never its message, so logs do not hold personal data or secrets.

## Severity

| Level | Examples | Respond within |
| --- | --- | --- |
| 1 — down or unsafe | site or database down; credential leak; data shown to the wrong company; payment taken but not fulfilled | immediately, owner informed |
| 2 — degraded | alert emails stuck; jobs late; disk under 10%; repeated errors on one page | same working day |
| 3 — minor | one GitHub repository failing to sync; a single error reference | next working day |

The response times are a starting proposal for the owner to confirm, not a promise
to customers.

## First steps per alert

- **database**: check MySQL is running and reachable with the `DB_*` values in the
  server's `.env`; check disk space (MySQL stops when the disk is full). If data is
  damaged, follow "Data was changed wrongly" in `deploy/release.md`.
- **migrations**: a release was deployed without `php scripts/migrate.php`. Take a
  backup, then run it. Never mark a migration applied by hand.
- **job:notifications / job:github-sync**: follow the state table in
  `deploy/scheduler.md` (never ran, late, stuck, failed).
- **email-outbox**: test SMTP credentials and the provider's status page; the job
  retries each message three times within two days. Do not resend by hand, or people
  get duplicates.
- **payments**: a *mismatch* means a callback's amount, currency or reference did
  not match the order. The order is not fulfilled, so check the provider dashboard
  before doing anything. An *unknown* order means the provider call failed midway:
  look the reference up in the dashboard, then fulfil or close it. Never fulfil an
  order the provider does not show as paid.
- **storage**: free space or grow the disk before uploads and MySQL fail. Old backup
  files and logs are the usual culprits; attachments must not be deleted to make room.
- **errors**: read the references in the log; if one page fails repeatedly, roll back
  the last release (`deploy/release.md`) or switch the feature off.

## Credential leak or compromise

1. Revoke the leaked key at the provider first; that is what stops misuse.
2. Put the new key in the server's `.env` (or host configuration) and restart PHP.
3. Check the provider's activity log for use you do not recognise (payments,
   transfers, cards issued, emails sent, GitHub access).
4. Record what leaked, when, how, and what was rotated.

Where each credential lives and where to rotate it:

| Credential | `.env` name | Rotate at |
| --- | --- | --- |
| Database password | `DB_PASSWORD` | MySQL / hosting panel |
| SMTP login | `SMTP_USERNAME`, `SMTP_PASSWORD` | the mail provider |
| GitHub OAuth app | `GITHUB_CLIENT_ID`, `GITHUB_CLIENT_SECRET` | GitHub → Settings → Developer settings → OAuth Apps |
| Token encryption key | `APP_KEY` | generate a new one; then every company reconnects GitHub, since stored tokens can no longer be decrypted |
| Paystack | `PAYSTACK_SECRET_KEY` | Paystack → Settings → API Keys & Webhooks |
| Flutterwave | `FLUTTERWAVE_SECRET_KEY`, `FLUTTERWAVE_WEBHOOK_HASH` | Flutterwave → Settings → API |
| Job-posting tokens | `LINKEDIN_*`, `FACEBOOK_*`, `INSTAGRAM_*` | each platform's developer settings |

`docs/owner-actions.md` lists the credentials already known to be exposed in Git history.

## Data shown to the wrong company

1. Treat it as level 1. Take the affected page or feature offline (roll back or block
   the route in `app/routes.php`).
2. Use the audit trail (`workspace_audit`) and web logs to find which records were
   seen and by whom.
3. The owner decides, with legal advice, who must be told and by when.
4. Add a regression check to `scripts/tenant_isolation_test.py` before re-enabling.

## Telling customers

Only the owner approves customer messages. A starting template:

> We are investigating a problem affecting `<what customers notice>` since
> `<time, timezone>`. `<What still works / what to avoid>`. We will update you by
> `<time>`.

Then follow up when it is fixed: what happened, what was affected, what changed.
Do not guess at causes or promise compensation in the first message.

## After every level 1 or 2 incident

Within a week, write down the timeline, cause, what detected it, what slowed the
response, and the follow-up work. Add a test or monitoring check so the same fault
is caught earlier next time.
