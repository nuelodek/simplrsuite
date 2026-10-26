# Simplr Suite

PHP business workspace for projects, tasks, people, CRM, financial records,
recruiting, and operations. The production-readiness work is on
`codex/production-readiness`. This is a working development build, not a verified
production release. See `work/overnight-handoff.md` for the remaining work.

This folder is the canonical source of truth. Selected work from the historical
`simplr24` checkout has been consolidated here; keep future development here.
The comparison and integration decisions are recorded in `work/consolidation.md`.

## Promotional pages

The same application serves three audience-specific index pages:

- Business: `http://127.0.0.1:8000/landing/business/` → `business.simplr.com`
- Talent: `http://127.0.0.1:8000/landing/talents/` → `talents.simplr.com`
- Work: `http://127.0.0.1:8000/landing/work/` → `work.simplr.com`

The root page selects the audience from an exact hostname match. Local preview
links stay on localhost. For hosting, point all three HTTPS virtual hosts to this
document root and configure `APP_BUSINESS_URL`, `APP_TALENT_URL`, `APP_WORK_URL`.
These origins also route account-email links to the correct audience. When unset,
account mail keeps using `APP_URL`. Cookies remain host-only. DNS, certificates,
and live hosting have not been changed or verified.

For a multi-domain Plesk deployment, [deploy/plesk-production.env.example](./deploy/plesk-production.env.example)
is a secret-free starting template for the configured production origins:
`APP_URL=https://vigorous-swartz.161-97-144-85.plesk.page`,
`APP_BUSINESS_URL=https://business.simplr.com`,
`APP_TALENT_URL=https://talents.simplr.com`, and
`APP_WORK_URL=https://work.simplr.com`.
Configure the values in Plesk or copy the template to a private server-side
`.env.local`; replace every `SET_IN_PLESK` placeholder and provide real secrets
only through the server's private configuration. Do not commit `.env.local`. The
template keeps payment providers disabled and does not deploy the app or verify
the host.

## Local setup

Requirements: PHP 8.2+ with `mysqli`, `mysqlnd`, `mbstring`, `openssl`, `curl`,
`fileinfo` and sessions;
MySQL 8.4 or a compatible MySQL/MariaDB server. MySQL 8.4 is the verified local
database. No JavaScript build step is needed.

1. Create an empty database and a database user. Do not import the historical
   SQL dumps: they contain old application records, not installation fixtures.
2. Copy `.env.example` to `.env.local` and set `DB_HOST`, `DB_PORT`,
   `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`. Host environment variables
   override file configuration. `.env.local` overrides `.env`.
   Any `LEGACY_DB_*` values in `.env.local` are reference-only; the application
   and migration runner use `DB_*` and must remain pointed at the isolated
   development database.
3. For local testing, use `APP_ENV=local`, `APP_URL=http://127.0.0.1:8000`, and
   `MAIL_TRANSPORT=log`. Test emails are written to `storage/mail/`, which the
   development router and Apache rules prevent from being served.
4. Install the schema and migrations:

   ```powershell
   php scripts/migrate.php --install
   ```

   Installation refuses nonempty databases. For an existing compatible database,
   take a backup and apply only migrations with `php scripts/migrate.php`.
   The email uniqueness migration stops if duplicate accounts require review;
   it never deletes or merges accounts.
5. Start the development server with its router:

   ```powershell
   .\start-server.ps1
   # Or:
   php -S 127.0.0.1:8000 -t . router.php
   ```

6. Open `http://127.0.0.1:8000`. Create a workspace and use the verification link
   from its local test email. The default installation contains no accounts or
   synthetic business records.

The PHP development server is for local development. Always use `router.php`:
serving the repository directly without its deny rules can expose private files.

## Working journeys

- Business signup, verification, login, password recovery, and sign out.
- Shared responsive dashboard and 23 record work areas, with search, pagination,
  validated create/edit forms, tenant checks, and stale-edit protection.
- Employee and manager directories and email invitations. Invitees choose their
  own password. Inviting does not immediately change an existing password.
- Staff sign-in and scoped workspaces. Employees update their assigned task
  statuses; managers see the projects they manage and those projects' tasks.
- Staff password recovery by role, work email, and workspace code, with expiring
  single-use links. Shared recovery entry: `/users/forgetpassword.php`.
- Accessible toast notifications enhanced from server-rendered feedback; errors
  remain visible, and recovery works without JavaScript.
- Current API guide at `/users/developers/documentation.php`, linked from
  Integrations. Older API-key examples are replaced with the implemented contract.
- Talent signup, verification, sign-in, password recovery, and private professional
  profiles. Talents explicitly opt into an employer directory and can withdraw
  immediately. Only verified business accounts can browse listed profiles; talent
  sign-in emails are not included. Employers can publish jobs, receive applications,
  and update review stages. Candidates explicitly share a profile snapshot and
  contact email, optionally upload a private PDF resume, track stages, and withdraw.
  Employers can schedule timezone-aware interviews; candidates can accept or
  decline, and employers can cancel scheduled interviews. These updates appear in
  both accounts; the app does not send interview emails. Withdrawal closes review
  and revokes employer resume downloads; submitted records and previously
  downloaded copies remain. Direct messaging is unavailable.
  Historical talent records are preserved and
  are not automatically published or imported into the new profile editor.
- Internal record API at `/api/records.php?module=projects` (and other allowlisted
  module keys). GET returns a paginated JSON list; POST accepts URL-encoded form
  fields. Verified owners have access; employee/manager access uses explicit
  company-scoped view/edit grants. Writes also require the session's
  `csrf_token` form field or `X-CSRF-Token` header. This is a browser-session API,
  not a public integration-token API.

Additional workspace features include staff toolkit permissions, company currency
and timezone settings, event calendar and ICS downloads, permission-scoped CSV
reports, internal help requests, audit activity and private record attachments.
PDF/PNG/JPEG attachments require record access, download as files, and are limited
to the lower of 5 MB or the server upload limit, 20 per record and 100 MB per company.
Uploads are not antivirus-scanned. Persist and back up `storage/attachments`
together with database metadata; never expose that directory directly over HTTP.

Billing offers **Paystack for NGN and Flutterwave for USD**, following the original
`simplr24` currency/provider choice. The server owns prices, conversion, orders and
payment verification. Checkout remains disabled without provider configuration.
See `work/payment-launch.md` for source pricing, setup, tests and remaining gates.

Amounts in existing finance tables are recorded amounts; this build does not
execute payroll payments, calculate statutory payroll, issue compliant tax invoices, or
fetch live banking balances. The invoice area is an invoice register.

## Verification

With the local server, local database, and log mail transport running:

```powershell
python scripts/smoke_test.py
# Includes the core checks above, then checks talent authentication and privacy:
python scripts/talent_smoke_test.py
# Includes core and talent checks, then staff recovery and consolidation checks:
python scripts/consolidation_smoke_test.py
python scripts/marketing_smoke_test.py
node scripts/consolidation_browser_test.cjs
node --check public/scripts/suite.js
php scripts/preflight.php
```

Recruiting journey checks: `python scripts/recruiting_smoke_test.py`,
`node scripts/recruiting_browser_test.cjs`, and
`php scripts/recruiting_policy_test.php`. Run the smoke test before its browser
check so the synthetic fixtures exist; roles created by these checks are closed
after verification. These checks never contact a payment provider.

Additional journey checks: `access_smoke_test.py`, `operations_smoke_test.py`,
`help_smoke_test.py`, `attachments_smoke_test.py`, `email_scope_test.py` and
`workspace_browser_test.cjs` under `scripts/`. Payment test setup is documented
in `work/payment-launch.md`. Do not run fixture suites against real customer data.

The smoke test creates synthetic companies and records using real HTTP forms and
API calls. It retains those records for inspection and writes results under
`work/test-results/`. It exercises account recovery, tenant isolation, input
validation, record updates, invitations, role restrictions, and legacy adapters.
It never sends real email. Run it only against a disposable local development
database, not a production copy. Repeated rapid runs can trigger the app's real
rate limits; wait for the 15-minute window rather than weakening those limits.

## Deployment configuration

- Set `APP_ENV=production`, the real HTTPS `APP_URL`, database credentials, and
  SMTP configuration through the deployment environment. Log mail is only
  available when `APP_ENV=local`.
- The Dockerfile enables the required PHP database, string and HTTP extensions,
  Apache rewrite and headers, and prepares private writable attachment storage. The
  container build has not yet been tested locally; Docker is not installed here.
- `.htaccess` and `router.php` deny private configuration, SQL, dependency,
  storage, and maintenance paths. Apache must allow these `.htaccess` rules.
- `GET /health.php` checks database connectivity without returning credentials.
  It is a liveness/connectivity check, not a production-readiness certification.
- `.env` is removed from Git tracking while retaining the local file. Previously
  committed secrets must be rotated before release; removing them from the
  current tree does not remove them from Git history.
- `php scripts/preflight.php --production` lists missing production configuration
  without printing credentials or contacting providers. It does not prove external
  services work. Non-reviewed legacy PHP routes are blocked by the shared manifest;
  they remain in the repository as historical code.
- A local backup drill is available as `php scripts/backup_restore_drill.php
  "C:/tools/mysql/mysql-8.4.5-winx64/bin"`. It accepts only a loopback `simplr_dev*`
  database with local/log-mail configuration, restores into a newly named database,
  compares table counts and copies/hashes private attachments. It never overwrites
  or drops an existing database. Dumps and restored databases remain private local
  artifacts, not a configured production backup policy.
- Before launch, verify rotated secrets, DNS/HTTPS, real SMTP delivery, both
  provider sandboxes/webhooks, USD collection approval, reviewed privacy details,
  production restore/retention procedures and the documented operating limits.
