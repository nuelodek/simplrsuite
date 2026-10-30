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

For a multi-domain Plesk deployment, the configured production origins are
`APP_URL=https://vigorous-swartz.161-97-144-85.plesk.page`,
`APP_BUSINESS_URL=https://business.simplr.com`,
`APP_TALENT_URL=https://talents.simplr.com`, and
`APP_WORK_URL=https://work.simplr.com`.
Set them, with `APP_ENV=production` and the real secrets, in Plesk or in a private
server-side `.env`. Never commit `.env`. Keep payment providers disabled until the
owner approves live billing.

## Local setup

Requirements: PHP 8.2+ with `mysqli`, `mysqlnd`, `mbstring`, `openssl`, `curl`,
`fileinfo`, `zip`, `dom` and sessions;
MySQL 8.4 or a compatible MySQL/MariaDB server. MySQL 8.4 is the verified local
database. No JavaScript build step is needed.

1. Create an empty database and a database user. Do not import the historical
   SQL dumps: they contain old application records, not installation fixtures.
2. Create `.env` in the project root (it is git-ignored and is the only
   configuration file) and set `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`,
   and `DB_PASSWORD`. Host environment variables override file configuration.
   Other settings the app reads, all optional locally: `APP_URL`,
   `APP_BUSINESS_URL`, `APP_TALENT_URL`, `APP_WORK_URL`, `APP_TIMEZONE`, `APP_KEY`;
   `MAIL_TRANSPORT`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`, `SMTP_HOST`, `SMTP_PORT`,
   `SMTP_USERNAME`, `SMTP_PASSWORD`, `SMTP_ENCRYPTION`; `GITHUB_CLIENT_ID`,
   `GITHUB_CLIENT_SECRET`, `GITHUB_SYNC_MINUTES`, `CURL_CA_BUNDLE`;
   `QUICKBOOKS_CLIENT_ID`, `QUICKBOOKS_CLIENT_SECRET`, `QUICKBOOKS_ENVIRONMENT`;
   `XERO_CLIENT_ID`, `XERO_CLIENT_SECRET`; `WORKDAY_HOST`, `WORKDAY_TENANT`,
   `WORKDAY_CLIENT_ID`, `WORKDAY_CLIENT_SECRET`, `WORKDAY_SCOPES`;
   `ZOHO_BOOKS_CLIENT_ID`, `ZOHO_BOOKS_CLIENT_SECRET`, `ZOHO_BOOKS_REGION`;
   `PAYSTACK_ENABLED`, `PAYSTACK_MODE`, `PAYSTACK_SECRET_KEY`,
   `PAYROLL_PAYSTACK_TRANSFERS_ENABLED`, `SALARY_VAULT_PAYSTACK_TOPUPS_ENABLED`,
   `FLUTTERWAVE_ENABLED`,
   `FLUTTERWAVE_SECRET_KEY`, `FLUTTERWAVE_WEBHOOK_HASH`, `BILLING_USD_RATE`,
   `BILLING_ENFORCE`; the `LINKEDIN_*`, `FACEBOOK_*` and `INSTAGRAM_*` job-posting
   keys; and the `PRIVACY_*` business identity. Leave unused settings out (or
   commented): a blank line overrides the built-in default.
   Any `LEGACY_DB_*` values are reference-only; the application and migration
   runner use `DB_*` and must remain pointed at the isolated development database.
3. For local testing, use `APP_ENV=local`, `APP_URL=http://127.0.0.1:8000`, and
   `MAIL_TRANSPORT=log`. Test emails are written to `storage/mail/`, which the
   development router and Apache rules prevent from being served.
   Setting `MAIL_TRANSPORT=smtp` sends real email. Before using SMTP for account
   invitations or password recovery, configure `APP_URL` (and audience URLs, when
   used) to a reachable HTTPS deployment; otherwise message links may point to
   localhost. Keep SMTP credentials only in private environment configuration.
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
- Employees, managers, tasks, and projects can be moved to Trash and restored.
  Permanent deletion is workspace-owner-only and is blocked while linked history
  or dependencies remain. Apply migration `030_recoverable_record_archive.sql`
  before using Trash.
- Overview dashboard with overdue alerts, live KPIs (active projects, open tasks,
  open pipeline, month's net cash), a focus list, project progress from task
  completion, pipeline and task charts, upcoming events and readable activity.
- Workspace search and command palette (Ctrl/⌘+K or `/`): jump to pages, start
  new records, and search records in the tools the signed-in person can open
  (`GET /api/search.php?q=`, company-scoped, same grants as the record pages).
- Stage filters on record lists and drag-and-drop boards for tasks, projects,
  deals, leads, products, objectives, incidents and compliance. Cards also move
  with the arrow keys. A move revalidates the whole record and uses the same
  edit grant, CSRF and stale-version checks as the edit form
  (`POST /api/records.php?module=…` with `action=move`).
- Point of sale for retail checkout, linked to shared Inventory and Customers.
  Cashiers open a register shift, reserve stock for 20 minutes, record cash,
  card, or external payments without processing them, and complete or cancel
  sales. Refunds, optional full-sale restocking, receipt printing, daily totals,
  and shift cash reconciliation are included. Apply migration
  `035_point_of_sale.sql` before opening `/users/admin/pos.php`. Configure a
  supported workspace currency in Settings and add at least one register.
  POS access follows the Sales & CRM toolkit grant; card payments and refunds
  must be processed outside Simplr.
- Rich text for long fields (descriptions, agendas, job descriptions, notes): a
  toolbar editor that degrades to a plain textarea. The server stores a small
  allowlisted HTML subset rebuilt from scratch (`app/richtext.php`); older plain
  text is escaped and shown with its line breaks. CSV and calendar exports use
  plain text. `php scripts/richtext_unit_test.php` covers the sanitizer.
- Files (`/users/admin/files.php`): every attachment the signed-in person can open,
  searchable by name, tool and type, with a 100 MB workspace storage meter. Uploads
  there create a record in the new Documents tool. Attachments accept PDF, PNG,
  JPEG, macro-free Word/Excel/PowerPoint (`.docx/.xlsx/.pptx`) and CSV. Every
  record list has an Export button (CSV through the existing report export).
- GitHub (`/users/admin/github.php`): plug and play. Simplr provides one GitHub
  OAuth App for every workspace (`GITHUB_CLIENT_ID`/`GITHUB_CLIENT_SECRET` in the
  server environment); its authorization callback URL must be exactly
  `<APP_URL>/users/admin/github.php`. Workspace owners just click Connect GitHub;
  a fine-grained personal access token remains under "Other ways to connect".
  Disconnecting revokes the grant on GitHub. Tokens are encrypted with AES-256-GCM using
  `APP_KEY` (required in production; local development creates a private key in
  `storage/keys/`). Linked repositories show open issues, pull requests and recent
  commits on the project page; Sync turns open issues into tasks once and completes
  tasks whose issues closed. A workspace can connect several GitHub accounts; each
  linked repository reads through its own account. Owners match GitHub usernames to
  employees (GitHub page or the employee record), so synced tasks are assigned,
  follow reassignments made on GitHub, alert the assignee and get the due-date
  reminders from `jobs/notifications.php`. Schedule both jobs on the server:
  `*/15 * * * * php jobs/github-sync.php` (auto-sync can be turned off per
  repository; `GITHUB_SYNC_MINUTES` changes the interval) and
  `* * * * * php jobs/notifications.php`; `deploy/scheduler.md` has the full cron
  setup and monitoring. Nothing is written to GitHub. Test it offline with
  `scripts/github_stub_server.php` and `scripts/github_smoke_test.py` (instructions
  at the top of the test).
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
  downloaded copies remain. Company-scoped direct and group messaging is
  available at `/users/admin/messages.php`, with teammate and workspace-record
  `@` references, mention notifications, and selected-message sharing. Staff
  access follows the Messages toolkit grant; refresh-based live updates do not
  require a separate WebSocket service. Apply migration
  `036_workspace_messaging.sql` before opening Messages. Attachments and push
  notifications are not included. Historical messages without reliable company
  and participant ownership are not imported.
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
In People & teams, the organization chart places each department head above that
department's teams and other managers. Owners can promote an employee to manager
or department head; the employee record is retained and a separate manager
invitation grants manager sign-in after acceptance. Demoting a linked manager
suspends manager access and requires reassignment of their reports and department
head role when applicable. Team records can be assigned directly to a department.
The chart follows explicit employee manager assignments; when none exists, it
places the employee under their department head or, if there is no head, the
department's only listed manager. This display fallback does not change records.
The calendar shows a month grid, highlights days with events, task deadlines,
or project start/end dates and milestones, and opens that day's items in a dialog.
Task assignees and project managers are shown when available; tasks and projects
appear only for users who have access to those workspace tools.

### Alerts, task queries, and penalties

Alerts appear in the signed-in workspace bell and Alerts page. Routine task
reminders follow each person's email preference (instant, daily digest, or
in-app only); formal query and penalty notices are always emailed. Query
deadlines and warning expiry are reviewed by the CLI scheduler. Run
`php jobs/notifications.php` every minute after migrations are applied so
short response windows are processed promptly; task reminders are deduplicated
to one per task per local day. The job also retries the email outbox and sends
digests. Configure `APP_URL` as an HTTPS origin before enabling SMTP.
Both jobs record a heartbeat; `php scripts/scheduler_status.php` exits 1 when a job
is late, stuck or failing, so it can drive an alert. See `deploy/scheduler.md`.
`php scripts/ops_check.php` adds database, migration, email outbox, payment, disk,
backup and error-rate checks; `deploy/incident-runbook.md` says what to do per alert.

An authorised workspace owner or manager can issue a written query about a
task or project. Select several eligible people or the whole eligible project
team; Simplr creates a separate query and private response record for each
person. Response windows can be as short as one minute. The subject can respond
in Simplr before or after the response window; once it passes, the query is
eligible for review and a late response is marked as late. A missed deadline
does not automatically impose a penalty. Managers may propose a penalty, but
only the workspace owner approves or rejects it. After approval, the owner can
assign it to an authenticated employee or manager for HR, Accounting, or another
function.
The assigned person can forward it to another staff member or record completion;
handoffs are audited and notify the next assignee. A subject may acknowledge or
appeal an approved penalty. Simplr records these decisions and does not execute
payroll deductions, post accounting entries, or impose a penalty automatically.
Configure the default response window and warning expiry under Workspace
settings.

Payroll supports company-scoped NGN compensation profiles, monthly pay runs,
employee payslips, editable per-run statutory deduction amounts, and
bank-transfer CSV exports. A payroll editor submits a run for approval by the
workspace owner or a distinct employee with payroll edit access; preparers cannot
self-approve. The Salary Vault tracks a cleared, tenant-scoped NGN allocation in
an auditable ledger. Paystack top-ups are disabled unless
`SALARY_VAULT_PAYSTACK_TOPUPS_ENABLED=true` and are credited only after
server-side transaction verification. External bank deposits require a distinct
owner/employee reconciliation before credit, and must already be in the same
Paystack payout funding pool before approval. Paystack's provider balance is
shared operationally across workspaces; the Vault is an allocation ledger, not a
segregated bank account or custodial wallet. Payroll transfers require sufficient
cleared Vault funds, reserve them before provider submission, and return funds
only after Paystack confirms failure or reversal. Uncertain outcomes remain
reserved until reconciled. Transfers remain disabled unless
`PAYROLL_PAYSTACK_TRANSFERS_ENABLED=true`. Bank account numbers are encrypted at
rest. Payroll does not calculate or file Nigerian tax, pension, or housing-fund
obligations. Have a qualified payroll professional verify each amount before
approval. HR penalties are never imported as payroll deductions.

Billing offers **Paystack for NGN and Flutterwave for USD**, following the original
`simplr24` currency/provider choice. The server owns prices, conversion, orders and
payment verification. Checkout remains disabled without provider configuration.
See `work/payment-launch.md` for source pricing, setup, tests and remaining gates.
The Integrations page also lists all 50 services advertised by the old dashboard
as future requests; their old buttons did not implement API connections. GitHub
supports repository issue sync. QuickBooks Online, Xero and Zoho Books support
provider authorization and read-only company/organization verification; Workday
supports tenant authorization only. These connectors do not import, export or
modify accounting records or read worker records. Configure provider credentials
in the server environment and follow the setup guides:
[QuickBooks](docs/quickbooks-integration.md), [Xero](docs/xero-integration.md),
[Workday](docs/workday-integration.md), and
[Zoho Books](docs/zoho-books-integration.md). No live provider credentials or
calls are part of local verification. Sandbox checkout also remains disabled
until fresh Paystack and Flutterwave server-side test credentials are configured;
the old public key and exposed legacy secret are not reused.

Amounts in existing finance tables are recorded amounts; Salary Vault allocations
are separate from accounting and bank balances. This build does not calculate
statutory payroll, issue compliant tax invoices, or fetch live banking balances.
The invoice area is an invoice register. The Accounting
page provides a separate company-scoped chart of accounts, balanced journal entries,
an income statement, a balance sheet and three management ratios. Existing ledger
records are not imported; enter opening balances as a balanced journal entry. These
reports are not audited or tax statements and should be reconciled with a qualified
accountant.

## Verification

With the local server, local database, and log mail transport running:

```powershell
python scripts/smoke_test.py
# Includes the core checks above, then checks talent authentication and privacy:
python scripts/talent_smoke_test.py
# Includes core and talent checks, then staff recovery and consolidation checks:
python scripts/consolidation_smoke_test.py
php scripts/accounting_unit_test.php
php scripts/richtext_unit_test.php
php scripts/migration_matrix_test.php --all
php scripts/migration_failure_recovery_test.php
php scripts/role_transition_test.php
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

Secret scanning runs on pull requests and pushes with Gitleaks, scanning the
commits introduced by each change and redacting findings. To scan the full
repository history locally, install Gitleaks and run
`gitleaks git --redact --no-banner --log-opts="--all"`; historical findings
require owner-led credential rotation and review, not an allowlist.

The smoke test creates synthetic companies and records using real HTTP forms and
API calls. It retains those records for inspection and writes results under
`work/test-results/`. It exercises account recovery, tenant isolation, input
validation, record updates, invitations, role restrictions, and legacy adapters.
It never sends real email. Run it only against a disposable local development
database, not a production copy. Repeated rapid runs can trigger the app's real
rate limits; wait for the 15-minute window rather than weakening those limits.

## Troubleshooting

- **Invitation or recovery email is not arriving:** for local work, use
  `APP_ENV=local` and `MAIL_TRANSPORT=log`, then inspect the generated test
  message under `storage/mail/` without sharing its contents; links there can
  contain account tokens. For a real SMTP setup, confirm the approved test
  recipient, `MAIL_FROM_ADDRESS`, `SMTP_HOST`, `SMTP_PORT`, credentials and
  encryption in private server configuration. Check the PHP/server error log
  for the redacted delivery error. Do not switch to real SMTP for local tests.
- **An email link points to localhost or the wrong audience:** configure the
  real HTTPS `APP_URL` and, when audience subdomains are deployed, the matching
  `APP_BUSINESS_URL`, `APP_TALENT_URL` and `APP_WORK_URL`. Do not send test
  messages to real users while those settings are local or placeholders.
- **Install or upgrade stops at a migration:** `php scripts/migrate.php --install`
  is only for a new empty database. Back up an existing database before running
  `php scripts/migrate.php` without `--install`. If the email uniqueness
  migration reports duplicates, stop and reconcile them with the account owner;
  the migration intentionally does not delete or merge accounts. Do not mark a
  migration applied manually to bypass an error.
- **Scheduled alerts or GitHub sync are late/failing:** run
  `php scripts/scheduler_status.php` as the same service user as the scheduled
  jobs, inspect the job logs and PHP error log, and compare the configured cron
  cadence with `deploy/scheduler.md`. Confirm the scheduler account can read the
  application's private environment and write to `storage/`. Do not run jobs
  through a web request or use root.
- **A backup/restore check fails:** the included
  `scripts/backup_restore_drill.php` is restricted to a local `simplr_dev*`
  database with log mail; it is not a production restore tool. Preserve the
  source database, inspect the private drill logs and address missing tables,
  checksum mismatches or attachment errors before retrying. Production backup
  destinations, retention and recovery objectives still require an approved
  operations plan.
- **Payment setup or checkout is unavailable:** provider checkout is gated by
  server configuration and remains disabled until owner-approved sandbox
  verification and production merchant setup are complete. Follow
  `work/payment-launch.md`; never paste provider credentials into tickets or
  documentation, enable live mode for testing, or perform a real charge as a
  troubleshooting step.

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
