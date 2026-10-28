# Simplr Suite overnight continuation

## Configuration consolidated into `.env` (28 October 2026, Claude, owner request)

Only `.env` remains (git-ignored, excluded from Docker builds). It holds the former
`.env.local` values verbatim (active), the business identity for the privacy notice,
the older July 2025 credentials, credentials moved out of legacy files (Marqeta
webhook login, CurrencyAPI key, two mail usernames), and every other setting
commented out. The loader (`app/bootstrap.php`) and the six test scripts read only
`.env`. Deleted: `.env.local`, its backups (project and Temp), `.env.example`,
`deploy/plesk-production.env.example`, `PHP-v3-master/.env.example`. Checks after the
change: health 200, migrations up to date, preflight 15/15, rich text 15, accounting
21, session hardening 23/23.

## Evening continuation — production-readiness items (27 October 2026, Claude)

Requested: pick the `requirements.html` items that can be solved now, record them
so other agents do not collide, and keep the server running. Claims are in
`work/agent-claims.md`; Codex was working at the same time (privacy page, env
examples, `scripts/access_smoke_test.py`) and its notes are in `work/handoff.txt`.

- **Tenant isolation (CRITICAL item).** New `scripts/tenant_isolation_test.py`:
  two synthetic companies plus a view-only employee, a manager with no grants and
  an anonymous visitor. It covers search, the Files library, downloads and
  removal, record views, the records API (list, update, board move, archive), CSV
  export (including spreadsheet-formula neutralising), alerts, queries, accounting,
  dashboards, calendar and activity. Staff grants are restored afterwards. No
  cross-company leak was found in these surfaces.
- **Open redirect fixed.** `users/admin/notifications.php` accepted
  `return_to=/\evil.example`; browsers treat `/\` like `//`, so the visitor left the
  site. New `local_path()` in `app/bootstrap.php` rejects `//`, any backslash and
  control characters; the alert-open redirect uses it too.
- **GitHub idempotency.** Found by reading the code: the scheduled job and a Sync
  click could both create a task for the same new issue. The second insert then
  failed on the unique key, leaving a duplicate task. `github_sync_issues()` now
  holds a per-link MySQL lock, so a second sync waits and finds the existing task.
  The cron job counts a busy link as `busy` rather than an error.
  `scripts/github_smoke_test.py` now runs four syncs at once (exactly one task per
  issue) and has a second company try to sync, unlink, disable auto-sync,
  disconnect, map people, link and view company A's GitHub setup (all refused,
  state unchanged).
- **Scheduler monitoring.** Migration `031_scheduler_runs.sql`, `app/jobs.php`
  (`job_begin` lock plus heartbeat, `job_finish`, crash recorded by exception class
  only), `scripts/scheduler_status.php` (exit 1 when a job is never/late/stuck/
  failed, `--json`, `--quiet` for cron alerts). Both jobs use it;
  `jobs/notifications.php` gained only the two heartbeat lines, so overlapping
  minute runs now skip. `jobs/github-sync.php` stops taking new repositories a
  minute before its next run. Runbook with exact cron lines, states and staging
  steps: `deploy/scheduler.md`. The README listed `*/5` for notifications in one
  place and every minute in another; it now says every minute.
- **Hygiene.** `.playwright-mcp/` (Playwright MCP captures) is ignored; nothing was
  deleted. PHP lint on every changed/untracked PHP file and `git diff --check` are
  clean.

Verification (isolated local DB, `APP_ENV=local`, `MAIL_TRANSPORT=log`, GitHub stub
only): tenant isolation 121/121 (twice), GitHub stub journey 117/117 (was 92),
consolidation smoke 388/388, preflight local pass, Paystack simulation 56/56,
Flutterwave simulation 46/46, accounting unit 21/21, rich text 15/15, recruiting
policy 14/14, marketing 40/40, `node --check` on suite/richtext/accounting JS.
Scheduler: never → ok → overlap skipped → crash recorded as failed → recovered.
Summaries are in `work/test-results/claude-regression-*.json`.

Running two agents' suites at once exhausts the app's own login rate limits on the
shared synthetic fixtures (10 per account per 15 minutes); failures showing
"fixture login" are that, not product defects. Rerun one agent at a time, or wait
the window out. `access_smoke_test.py` also needs the Apache helper on 8001
(`httpd.exe -f .local/apache-test.conf -DFOREGROUND`).

Second batch (22:30–22:45):

- **Dependencies:** `docs/dependencies.md`. The Composer tree exists only for Sentry,
  which no reachable page loads. PHPMailer 6.9.1 (LGPL-2.1) is the only bundled
  library in use; `PHP-v3-master/` (Flutterwave SDK) and the empty
  `mediawiki-php-excimer/` are unused. Blocked legacy pages load pdf.js 2.11.338
  (CVE-2024-4367), unpinned Unit components, Google Tag Manager and old jQuery and
  socket.io. `users/admin/sentryinitialize.php` hardcodes a Sentry DSN; add it to the
  owner's rotation list.
- **Container fix:** `Dockerfile` did not install `zip`, so every DOCX/XLSX/PPTX
  upload would be rejected in the container. It now installs `libzip-dev` and `zip`;
  `scripts/preflight.php` checks `zip` and `dom` (local preflight 15/15). The image
  has not been built here.
- **Launch scope:** `docs/launch-scope.md`, an available/limited/not-built table for
  owner approval. The only misleading copy found: the integrations page said payroll
  and banking "need a connected provider before they can be used". It now says
  Simplr does not run payroll, payouts or bank balances yet.
- **Data inventory:** `docs/data-inventory.md`, covering personal data per table, who
  sees it, what deletes it, legacy tables (including bank account numbers) and gaps:
  no retention job, no export or deletion requests, withdrawn resumes are kept.
  Owner questions are listed.
- **Release runbook:** `deploy/release.md` covers roles, staging rehearsal, backup
  (database, storage and APP_KEY), migrate, checks, rollback vs forward fix and sign-off.
- `requirements.html` now carries statuses and a one-line evidence note on every item
  touched today (they are the file's defaults, so every viewer sees them).

Third batch (23:00–23:40):

- **Route audit:** `scripts/route_audit_test.py` checks all 163 manifest routes,
  statically (sign-in helper, guarded legacy adapter, redirect stub, or reviewed
  public list with reasons) and over HTTP (anonymous GET, anonymous and signed-in
  POST without CSRF). 980/980. Matrix in `work/test-results/route-audit.json`. New
  routes must be added to the review lists or carry a guard, or the test fails.
- **Session hardening:** `scripts/session_hardening_test.py` 23/23 covers owner
  sign-in and reset enumeration, rejection of planted session IDs, logout revoking a
  copied cookie, GET logout doing nothing, HttpOnly/SameSite, and Secure when APP_URL
  is HTTPS (throwaway server on port 8095).
- **Migrations:** `scripts/migration_matrix_test.php` works on disposable
  `simplr_dev_mig_*` databases only (26 s per build; the default samples 17 of 32
  baselines, `--all` runs every one). It covers clean install, a rerun that changes
  nothing, upgrade from each sampled baseline with synthetic rows kept and the final
  schema identical to a clean install, the 002 duplicate-email stop, and a list of
  migrations that fail when run twice.
- **Secrets:** a scan by key type (values never printed) found a Paystack **live**
  secret key inside browser JavaScript in `users/admin/createbankaccount.php`, plus
  Paystack test keys, Unit tokens, a Sentry DSN and a mailbox password in dead legacy
  files and history. Values were blanked in the 8 current files. None matches the
  currently configured SMTP or payment settings. Rotation steps are in
  `docs/owner-actions.md`, the single owner checklist (linked from `requirements.html`).
- **Mobile dashboard:** the consolidation browser suite failed "Dashboard fits mobile
  viewport" (402px at 390px). Long one-line task titles set the grid track's minimum.
  Appended `.dashboard-columns > * { min-width: 0; }`; suite 55/55 on fresh fixtures.
- **Fixture order matters:** `consolidation_browser_test.cjs` needs a fresh alpha
  (run `consolidation_smoke_test.py` first). `workspace_browser_test.cjs` needs
  access, attachments and help suites rerun after any fixture refresh.
  `paystack_http_test.py` needs `paystack_test.php` first and the payment router on 8002.
  The isolation test now creates its chart in company B so alpha stays empty for the
  browser suite.

Final verification (isolated local DB, log mail, all passing): consolidation 388, access
234, attachments 54, isolation 121, route audit 980, session 23, recruiting 149, help 35,
operations 73, email scope 58, GitHub 113, marketing 40, Paystack 56, Flutterwave 46,
payment HTTP 40, accounting 21, rich text 15, recruiting policy 14, browser suites
consolidation 55, recruiting 35, workspace 83, migration matrix 74 (514 s). Migrations
that fail if replayed by hand: 002, 009, 013, 014, 015, 018, 019, 024, 025, 027, 028, 029,
030 (duplicate column/index); `migrate.php` never replays them, so only manual replays
are affected. Disposable databases were all dropped.

Not done here (owner, hosting or other agents): staff toolkit grants, talent journey,
promotions, payments sandbox, privacy/legal facts, hosting, backups, accessibility
review, capacity budgets. Nothing committed.

## Night continuation — editor, files, GitHub (26–27 October 2026)

Requested: a WYSIWYG editor, a place to upload and download files, less archaic
forms (especially Accounting), working integrations starting with GitHub, a
searchable integration catalog, and a logo that matches the browser-tab icon.

- Rich text: `app/richtext.php` (allowlist sanitizer that rebuilds HTML from
  scratch) and `public/scripts/richtext.js` (toolbar editor on `textarea[data-rich]`).
  All module textarea fields are rich; public job pages, record details, calendar
  and meeting pages render sanitized HTML; CSV and ICS exports use plain text.
- Files: `/users/admin/files.php` plus a Documents tool (migration 022). Attachments
  also accept macro-free DOCX/XLSX/PPTX (package structure checked) and CSV.
  Shared `store_record_attachment()` keeps quotas, locking and audit in one place.
  Record lists have an Export button (existing permission-scoped CSV export).
- Forms: one control style across the workspace; the journal form now groups
  accounts by category, prefixes amounts with the currency, shows live totals and
  balance, and no longer prints “Currency not set” beside every amount.
- GitHub: migration 023, `app/github.php`, `/users/admin/github.php`,
  `app/github-panel.php`. OAuth (state-checked) or fine-grained token; tokens are
  AES-256-GCM encrypted with `APP_KEY` (local fallback key in ignored
  `storage/keys/`). Project pages show issues, PRs and commits (2-minute cache);
  Sync creates tasks from open issues once and completes tasks whose issues closed.
  Nothing is written to GitHub. Preflight now requires `APP_KEY` for production.
  Real GitHub has not been exercised here: that needs your token or an OAuth app.
- Integrations page: GitHub card first; the planned catalog is searchable.
- Logo: tab-icon mark (tilted −12°), “simplr” with 10px “SUITE” beneath the “s”.
- Fixed Copilot bugs found by the suites: talent invitation/offer responses always
  failed (version computed from joined columns), and shortlist empty-state copy
  did not match its test.

Verification: richtext unit 15/15, accounting unit 21/21, GitHub stub journey
42/42, consolidation smoke 197/268/388, consolidation browser 55/55, access
217/217 (with the local Apache helper on 8001), attachments 54/54, recruiting
149/149, recruiting browser 35/35. The recruiting browser test now types into the
rich editors. `.env.local` was temporarily set to log mail during runs and restored.

## Evening continuation — launch polish (26 October 2026)

Requested: close UI and workflow gaps before tomorrow's market launch, using
`simplr24` only as a read-only idea source. Work stayed in this workspace; the
historical checkout and `simplrco_dashboard` were not changed.

Broken flows fixed:
- Migrations 018–020 were written but never applied, so Reports crashed (1146).
  `018_job_social_targets` used MariaDB-only `ADD COLUMN IF NOT EXISTS` and
  `019_project_milestones` duplicated columns that had also been added to
  `schema.sql`, which would break a fresh install. 018 now uses MySQL syntax and
  `schema.sql` is back to its baseline; a disposable fresh install applied all
  migrations, then pending ones were applied to `simplr_dev`.
- `/users/admin/accounting.php` existed but was not in the route allowlist (404).
- Creating a project without a milestone status crashed (empty value into the
  `NOT NULL` enum). Empty now means `not started`.
- No journal entry could be posted: the blank opposite column on every line was
  rejected as an invalid amount. Blank now means zero; thousands separators are
  accepted. The form shows a live debit/credit balance.

Product polish:
- Shell: grouped sidebar, Ctrl/⌘+K command palette with scoped record search
  (`api/search.php`), "New" quick-create menu, account menu, compact tour button.
- Record pages: stage filter chips with counts, List/Board switch, drag-and-drop
  and arrow-key boards (`action=move` revalidates the whole record and keeps the
  edit grant, CSRF and stale-version checks), human dates, overdue flags,
  currency labels and project progress from task completion.
- Overview rebuilt (attention strip, KPIs, focus list, projects, pipeline, task
  donut, agenda, money and hiring snapshot, readable recent activity).
- Activity page now uses sentences with names and record links (`app/activity.php`).
- Customer-facing copy no longer exposes server variable names or "legacy
  catalog" notes; landing-page kicker labels removed per the owner's notes.
- Error page uses the brand mark and returns each role to its own home.

Verification (local `simplr_dev`, dev server forced to `MAIL_TRANSPORT=log`):
consolidation smoke 197/197, 268/268, 388/388; consolidation browser 55/55;
accounting unit 21/21; PHP lint of edited files, `node --check`, `git diff --check`.
`.env.local` has real SMTP enabled, so test runs temporarily set
`MAIL_TRANSPORT=log` there and restored the identical file afterwards. Two test
fixes: the API guide heading lost its full stop in an earlier edit, and the
browser test now waits for the chart-of-accounts redirect. The accounting browser
section had never run before because the route was missing. Not rerun this time:
recruiting, access, operations, help, attachments, email-scope and workspace
browser suites. Repeated runs hit the app's own 15-minute auth rate limits;
wait them out rather than loosening limits. Demo records (projects, tasks, deals,
leads, ledger, events) were added to the synthetic "Test Workspace Alpha" fixture
through the record API. Nothing is committed.

## Daytime continuation — interview scheduling (26 October 2026)

Added the next recruiting workflow to the canonical workspace: employers with
application edit access can schedule timezone-aware interviews, candidates can
accept or decline in My applications, and employers can cancel pending or
accepted interviews. Interview times are stored in UTC and displayed in the
selected timezone. Remote meeting links must use HTTPS; in-person interviews need
a location. Updates appear in both accounts; interview email is not sent.

- The user clarified the reference context: historical PHP source is
  `C:/Program Files/Ampps/www/simplr24`, and the existing database to inspect is
  `simplrco_dashboard` through local phpMyAdmin. Both remain read-only reference
  sources. phpMyAdmin confirmed the legacy database has an `interviews` table
  (`talent_id`, date/time, interviewer, attended, feedback, company_id), but it
  is not linked to the new job applications. The new migration is deliberately
  application-scoped rather than altering this legacy table.
- The legacy schema has a `messages` table, but the old talent chat pages query
  `talentchat`, which is absent from the selected database. The `messages` schema
  lacks a company/thread scope, so the old chat is not safe to copy directly.
- The user created the isolated `simplr_dev` database. The canonical schema and
  all 17 migrations, including `017_application_interviews.sql`, are installed
  there. The active local app connects on port 3306 as the configured database user; the
  account has broad MySQL privileges, so keep this use confined to local
  development and use a database-scoped account in production. `simplrco_dashboard`
  remains untouched.
- Only schema metadata was inspected in phpMyAdmin; no legacy customer rows were
  queried, modified, or imported. The historical project was read-only.
- With the user-provided local DB credentials, verified `simplrco_dashboard` on
  port 3306 in a read-only session and inventoried its 60 tables/columns. This is
  the historical reference database, not the app's isolated `simplr_dev` test
  database. Never point the canonical app or migrations at it.
- At the user's request, the supplied MySQL account is used for the active local
  `DB_*` connection to `simplr_dev`; the same host/account values are also kept
  under `LEGACY_DB_*` for reference. Both are in ignored `.env.local`; the
  application uses only `DB_*`. Never set `DB_DATABASE=simplrco_dashboard` or run
  migrations against that historical customer database.
- At the user's request, selectively imported all 13 source company workspaces
  into new, independently generated company IDs in `simplr_dev`. Migrated 40
  company-owned business rows: roles, departments, managers, employees,
  projects, leads, customers, CRM accounts/contacts, clients and meetings.
  Exact duplicate source rows were deduplicated; records without a mapped owner
  workspace were excluded. The source transaction was read-only.
- Source owner emails/company names were preserved, but each new owner password
  is a random undisclosed hash; old passwords and reset/verification tokens were
  not copied. Owners must request a password reset before signing in. Existing
  employee/manager directory entries were imported without authentication
  credentials; they must be invited/set up in Simplr Suite before staff can log in.
- Deliberately excluded bank account details, legacy auth tables/passwords,
  reset tokens, messages, payment records, subscription entitlements, logs and
  unscoped/orphan records. No bank or payment activity was brought into the app.
  Imported workspaces do not inherit legacy paid access or subscription status;
  review current plans and entitlements separately before enabling billing.
  The migration audit tables `legacy_import_batches`,
  `legacy_import_companies`, and `legacy_import_rows` record target IDs and
  provenance for the completed batch without duplicating source credentials.
- Post-import verification confirmed 13 owned company mappings, 40 mapped
  business rows, zero missing target records, zero broken company-scoped
  department/manager/role/project-manager relationships, and zero mappings from
  excluded sensitive tables. The target still has synthetic test workspaces as
  well as imported workspaces; use the `legacy_import_companies` mapping to
  distinguish them. No production database/server was used.
- Per user request, configured the SMTP settings from the read-only historical
  `simplr24/.env` into ignored, local-only `.env.local`. `MAIL_TRANSPORT=smtp`
  is active for the local app. The server accepted configuration validation via
  `mail_ready()`; no test email or external SMTP connection was made. The legacy
  `SMTP_FROM` value was not a bare email address, so the validated SMTP username
  is used as `MAIL_FROM_ADDRESS`. SMTP is real outbound delivery, not the prior
  local mail log. Current `APP_URL` is localhost, so do not send invitation or
  password-recovery messages to real users until a reachable HTTPS app origin is
  configured. Revert local delivery to `MAIL_TRANSPORT=log` when done; remove
  the copied SMTP secret entries from `.env.local` to fully revert.
- The live legacy DB schema differs from the checked-in historical SQL schema:
  the live DB has no `tasks`, `talentprofile`, `talents`, or `talentchat`, although
  some appear in the historical SQL files. The canonical app expects `tasks` and
  has separate new talent and recruiting tables, so schema-file presence alone
  does not establish that those data/table structures exist in the live DB.
- Existing canonical workspace modules map to legacy tables for projects,
  employees, managers, departments, CRM, finance, inventory, assets, events,
  applications, and part of security. Legacy-only areas include attendance,
  benefits, bank account details, budgeting, cash flow, messages, AI/reporting,
  and several cyber tracking registers. A metadata table is not proof that the
  feature had a working UI or safe workflow; source search found no attendance
  or benefits entry/update flow to port directly.
- The live `interviews` table has no job-application key; new interviews remain
  scoped to the modern `application_submissions` journey. Legacy `messages`
  stores sender/recipient/message but no tenant or conversation scope, and the
  old chat page expects a separate, absent `talentchat` table. Do not expose that
  message history through the new app without a deliberate identity/consent and
  tenant-mapping design. Legacy `bankaccount` stores account numbers and needs
  explicit protection and migration approval before any data migration.
- Legacy `toolkit_subscriptions` uses one wide row with per-tool month/date
  columns; it does not map directly to the canonical order and provider-scoped
  entitlement model. Do not bulk-copy or grant paid access from it without a
  reviewed entitlement reconciliation.
- Scheduling and responses use CSRF, application/interview version checks,
  account/company scope, workspace edit grants, rate limits and audit events.
- Database-independent interview policy/render checks passed **10/10**; the
  database-backed recruiting policy suite passed **14/14**. Recruiting HTTP
  journey passed **125/125**; access regression passed **217/217**. The
  consolidation/core/talent regression scripts passed **197/197**, **267/267**,
  and **387/387**. Consolidation browser checks passed **42/42** and recruiting
  browser checks **35/35**. Changed PHP files lint; the Python HTTP test script
  compiles. Local `/health.php` returns 200. No production database or server
  was used.

User request: inspect and improve the whole application overnight so it becomes
presentable and suitable for launch. User explicitly chose **all modules equally**.
No user authorization for subagents, deployment, external messages, or real
financial transactions. Continue coding autonomously without routine questions.

Historical heartbeat: `simplr-suite-overnight-improvements`, now **paused**.
Overnight authorization ended at 07:00 Africa/Lagos on 23 October 2026. Subsequent
work below was explicitly requested by the human. Do not reactivate the automation
unless requested. Do not mark the application production-ready without evidence.

## Latest human-requested continuation: recruiting (24 October 2026)

The human asked “hi, please next.” Completed the next concrete journey in the
canonical workspace: employer job publishing → talent application → employer
review/stage update → candidate tracking/withdrawal. The overnight automation
remains paused. This was a daytime continuation, not a resumed heartbeat.

- Read the reference recruiter's mock dashboards, `displaytalent.php`, talent
  submission/profile upload code and `ziprecruiter.php`. These contain useful
  professional-profile fields and workflow categories, but no complete working
  application journey. No reference files or credentials were copied/changed.
- Migration `016_recruiting_journeys.sql` adds job postings and application
  submission metadata. The existing `applications` register remains the employer's
  source of truth, including manual legacy records. Applied only to isolated local
  `simplr_dev`; apply through the migration runner on a deployment host later.
- Job drafts are private; publishing creates a public role with location, work
  arrangement, employment type, requirements and optional timezone-aware deadline.
  Closing/deadline expiry stops applications. Recruiting billing entitlement
  covers jobs and review; inactive subscriptions stop applications when enforced.
- `/jobs/` is searchable. `/users/talents/apply.php?job=...` preserves the intended
  job through sign-in/account verification and profile completion. Applicants
  confirm sharing an immutable profile/contact-email snapshot and optional PDF.
  Directory visibility stays independent. Server derives account/company/job/stage;
  duplicate submissions, stale job/profile versions and forged identity are blocked.
- PDF resumes reuse private `record_attachments` storage, validation and workspace
  quota. Only the applicant and authorized employer reviewers can download them;
  other talents/tenants and public storage URLs cannot. Employers cannot replace
  or delete applicant-submitted resumes through the generic attachment API.
- `/users/admin/application.php?id=...` supports tenant-scoped review with view/edit
  grants, CSRF, stale-version protection and audited stages. Generic applicant edits
  redirect to review; generic API cannot overwrite submitted identity/profile.
- `/users/talents/applications.php` shows only that account's applications/stages.
  Withdrawal requires confirmation and a current version, closes review and revokes
  future employer resume downloads. It does not erase submitted records or copies
  already downloaded. One application per job; no reapplication after withdrawal.
- Talent/business navigation and promotional copy connect these journeys. Shared
  forms remain responsive; role details, candidate consent and employer review
  inspected at mobile width. Legal privacy notice remains a draft for owner review.

Verification in this continuation:
- `python scripts/recruiting_smoke_test.py`: **107/107**.
- `node scripts/recruiting_browser_test.cjs`: **35/35**, including browser job
  publication, return after login, submission, employer stage change, candidate
  status, withdrawal and six screens at 1280/390px. Test jobs are closed afterward.
- `php scripts/recruiting_policy_test.php`: **10/10**, deadlines/timezone/billing
  gates with no provider calls or entitlement mutations.
- Existing core + talent + recovery consolidation: **387/387** (run 1792799420).
  Existing fixture files restored after this run to preserve other regression links.
- Attachments: **54/54**; marketing: **40/40**. Local preflight **13/13**.
- Reviewed PHP syntax: **186/186 files**; recruiting JavaScript/Python syntax and
  `git diff --check` passed. Scoped Apache verification helper stopped; preview on
  port 8000 still answers health, jobs, talent landing and employee login with 200.
- All **285** inventoried `simplr24` reference files remain unchanged.
- Screenshots and detailed JSON reports are under `work/test-results/recruiting-*`.
  Tests use synthetic example.test accounts, inert PDFs, local logged mail and no
  external services. No provider transactions, deployment, real email or messaging.

Host blocker: C: repeatedly fluctuated between zero and about 35 MB free. One
interrupted save truncated `app/modules.php`; recovered it from the pre-consolidation
archive plus exact applied patch history, then reran the core journeys successfully.
Subsequent saves used temporary files plus replacement. No empty reviewed PHP files
remain. Asked the user to free at least 1 GB while preserving both Simplr folders.
Three completed, disposable `simplr_restore_20261023_*` verification databases were
removed to recover space; their backup archives/reports remain in `.local/backup-drills`.
The pre-migration restore drill verified 88 tables, 1,165 rows and one attachment.
No actual workspace database or user record was deleted. Avoid another restore drill
or large dependency installation until disk space is available.

Remaining launch dependencies from the previous checkpoint still apply: production
hosting/DNS/HTTPS, real email configuration, approved business/privacy details and
provider activation with test-mode end-to-end evidence. Direct talent messaging,
interview scheduling, resume parsing/scanning and a retention/deletion workflow are
not implemented. File downloads explicitly disclose that antivirus scanning is absent.
Do not call the entire product production-ready. Main preview remains on port 8000.

## Active daytime continuation (23 October 2026)

### Latest verified checkpoint: Paystack + Flutterwave

The human added **Flutterwave for USD alongside Paystack for NGN**, then explicitly
directed us back to the `simplr24` pricing reference. Do not ask for new USD prices:
`users/admin/subpro.php` in that read-only checkout defines NGN prices, conversion
and provider selection. The server now carries over those NGN prices and the
0.00063 USD/NGN fallback as a clearly displayed, configurable fixed conversion
(`BILLING_USD_RATE`), not a live FX quote. No source credentials were copied/called.
See `work/payment-launch.md` for the exact source lines and activation checklist.
All 285 inventoried historical source files were rechecked and remain unchanged.

### Integrations catalog and sandbox credential status (26 October 2026)

- The admin Integrations page now lists all 50 historical service names under
  the original three categories and marks them **Not connected**. This is a
  catalog restoration only: the old controls were placeholders, not working
  OAuth/API connectors. The locally available historical brand images are
  PayPal, Plaid, QuickBooks, Square and Xero; other catalog entries use initials.
- Paystack NGN and Flutterwave USD are separate prepaid billing options, not
  integrations for connecting those catalog services. Both remain disabled
  locally until fresh server-side sandbox credentials are configured.
- The old checkout's Paystack public test key cannot authorize this server flow.
  A test secret was found hardcoded in legacy source and is treated as exposed;
  no Flutterwave v3 test secret or webhook hash was found. Do not reuse or copy
  those legacy values. No provider transaction or email was sent for this work.
- See `work/payment-launch.md` for safe configuration and verification steps.

- Completed both hosted-checkout adapters, server quotes, provider-bound order
  snapshots/nonces/transaction IDs, verification, webhooks and manual renewal.
  NGN/Paystack and USD/Flutterwave tabs show their totals and connection status.
  Both providers extend the same test/live toolkit entitlement. Original cart
  conversion rounds once: two project months USD 4.41, not USD 4.42. Source Sales
  and CRM offers are combined because they expose the same current record tools.
- Flutterwave v3 verifies the stored tx_ref and decimal USD amount; v3 webhooks
  authenticate verif-hash. Paystack authenticates raw-body HMAC-SHA512. Provider
  event payloads and browser success parameters cannot grant access themselves.
- Migration 012 adds private PDF/PNG/JPEG record attachments and view details.
  Owner/editor uploads/removals and viewer downloads enforce tenant/tool access,
  CSRF and private storage. Server upload limit is reflected in the form. Limits:
  at most 5 MB/file, 20 files/record, 100 MB/company. Forced downloads, no public
  URLs or inline execution. No malware scanning or talent resume upload workflow.
- Migrations 013 and 015 correct global CRM and manager-directory email uniqueness
  to company scope, preserving existing records. Optional CRM emails now store
  NULL so multiple records can omit email. Migration 014 namespaces provider IDs.
  All migrations through 015 applied only to the isolated local database.
- Fixed mobile table overflow caused by absolutely positioned accessible labels
  escaping their scroll container. Fixed corrupted dash/separator text. Browser
  screenshots reviewed for dollar billing, record details and staff mobile table.
- Added read-only scripts/preflight.php and a guarded local backup/restore drill.
  Docker gets required extensions, upload settings and writable storage; Docker
  is unavailable here, so no image build is claimed. Storage must be persistent.
- Privacy notice is visibly a draft with actual configured review date, rather
  than silently showing today's date. Legal name/address/contact answer remains
  pending. The legal prose has not been certified or approved.

Latest passing evidence in work/test-results/:

| Check | Result |
| --- | --- |
| Consolidation/core/talent/recovery | 387/387 |
| Staff permissions/legacy routes/dev + Apache boundary | 217/217 |
| Settings/calendar/reports/legacy record compatibility | 73/73 |
| Internal help/tenant/author controls | 35/35 |
| Private attachments | 54/54 |
| Company-scoped CRM/manager emails and optional blank emails | 58/58 |
| Paystack simulated provider checks | 56/56 |
| Flutterwave simulated provider checks | 46/46 |
| Both provider HTTP guards + concurrent settlement | 40/40 |
| New workspace desktop/mobile/browser billing totals | 83/83 |
| Public/account/consolidation browser journeys | 42/42 |
| Three audience marketing routes | 40/40 |
| Changed/new PHP lint | 185 files, no failures |
| JavaScript syntax / git diff --check | passed |
| Local preflight | 13/13 |

Provider tests use CLI fake transports and obvious fake-key helper configuration;
**no actual provider calls, charges or real emails**. Test helper router is now
reproducible at scripts/payment_test_router.php. Earlier failures were real global
email scoping and table overflow, plus fixture reuse, missing beta relationship
fixtures, and the helper router failing to return the static-file sentinel.
All were fixed and the affected suites rerun. During the final core rerun, the
three existing synthetic browser fixture JSONs were checkpointed/restored so
module fixtures remained aligned. A concurrent payment fixture run collided with
that refresh; both CLI payment suites and HTTP checks were rerun sequentially
afterward and passed. Do not count preliminary failed runs as successes.

The final backup drill restored into new database
`simplr_restore_20261023_213000_64e71c`: **88 tables, 1,165 rows, matching extended
table checksums and one attachment hash**, source unchanged. Private dump/copies
are in `.local/backup-drills/20261023_213000_64e71c`; temporary client credentials
were removed. Earlier restored test database is also retained. No real database
was overwritten or dropped. This is not a production backup/retention service.

Production preflight intentionally reports 11 missing checks: production mode,
four real HTTPS origins, SMTP, live Paystack, live Flutterwave, paid editing
enforcement, privacy owner details and reviewed notice. Production work also
needs key rotation, DNS/certificates, real provider test checkout/webhook delivery,
Flutterwave USD collection approval, rate/pricing review, customer entitlement
migration, monitoring/reconciliation and production restore/retention procedures.
Automatic refunds/disputes/recurring billing and talent applications/messaging
remain unavailable. Unreviewed legacy routes are blocked, not fully implemented.

Main preview remains http://127.0.0.1:8000. The overnight automation remains
**PAUSED**. Work is uncommitted and unpushed, including all prior user changes.
No deployment, external messages, source-tree writes or subagents.
Owned test helpers on ports 8001 and 8002 were stopped after verification; the
main preview health endpoint still returns HTTP 200. Consolidated evidence is
`work/test-results/daytime-verification.json`.

### Earlier daytime implementation notes (superseded where stated above)

The human explicitly asked to continue after the morning report, then said
"lets go on". This is active authorized coding; the automation stays PAUSED.
All earlier changes are preserved. No deploy, real email, payment or subagents.

- Added migration 007: role/company/staff/module view/edit grants and workspace
  audit records. Admin Staff access page, immediate permission checks on API and
  forms, transactional grant recheck on record writes, stale access-form checks.
  Old tools_assigned rows stay intact and are shown for explicit permission review.
  Admin-only directory/invitation/permission operations remain restricted.
- Replaced unsafe legacy task/team/lead updates and data lookups with shared
  permission/tenant/CSRF/validation/version checks. Staff toolkit URLs now open
  permission-aware modern tools; read-only forms hide add/edit controls.
- Added explicit app/routes.php manifest and shared app/http.php dispatcher for
  PHP development and Apache. Non-reviewed PHP URLs, provider demos, direct uploads,
  source and private paths return 404 without executing. Existing files are retained
  in the repo. This CONTAINS unreviewed routes; it does not implement those features.
- API supports versioned updates; developer guide updated for staff permissions.
- 387/387 prior consolidation checks passed after the access/routing changes,
  evidence consolidation-smoke-1792747982.json. New access suite passed 217/217,
  checking grant/revoke/view/edit across two companies and roles, old mutation
  routes, private paths, GET/POST on dev PHP AND isolated local Apache.
- Native Apache test process started using .local/apache-test.conf on loopback
  8001, independent of normal AMPPS config. Syntax passed and routes tested.
  Main preview remains loopback 8000. PHP health is healthy. No production host
  changes. Docker missing mbstring was fixed but image not built yet.
- Migrations 008 workspace settings and 009 event timezone applied locally.
  Added settings, events CRUD/calendar/ICS, module-permission-scoped reports/CSV.
  Currency is explicit, no exchange conversion. Event timezones persist; old
  events require choosing timezone before ICS. CSV escapes formula-like cells.
- Legacy task numeric project references now resolve safely in staff views/forms;
  edits sync the two historical project-manager columns. Compatibility fixture
  temporarily simulates an old missing-FK schema on a single local connection,
  then restores the synthetic task reference in finally. No real data touched.
- Operations tests passed 73/73; help author/tenant/CSRF/reply/version workflow
  passed 35/35; new workspace Chromium responsive/navigation checks passed 57/57.
  Earlier failures were fixture reuse (unchanged settings, duplicate blank lead
  email, existing grants) and simulating an old schema with the new project FK.
  Test fixtures were repaired without weakening application guards.
- Added migration 010 and /users/help.php internal owner/staff request/reply flow,
  owner resolution, private per-author scope, pagination, audit activity UI.
- Human chose no payments briefly, then explicitly corrected: "launch with
  Paystack". Paystack IS now launch scope. Privacy contact/legal details question
  is still pending. Do not reuse old credentials or make real provider calls.
- Paystack implementation underway: migration 011, server-owned NGN toolkit price
  catalog from original subpro.php (Sales & CRM combined to avoid duplicate sale),
  hosted checkout, manual prepaid 1–12 month renewals, server verification, HMAC
  webhook, deduped nonce/order/transaction IDs, per-mode entitlements. Reading and
  exports remain available after expiry; editing enforced when BILLING_ENFORCE=true.
  Main local environment remains unconfigured/disabled; no config values changed.
- Paystack CLI provider simulation passed 51/51; no real provider traffic or
  charges. HTTP boundary, concurrent settlement, docs and browser checks pending.
- Browser screenshot review found captures caught mobile sidebar mid-transition;
  screenshot calls now disable animations. Re-capture and visually recheck.
- Safe attachments and broader recruitment/messaging remain unfinished. Do not
  claim entire release ready. Provider test checkout on a deployed HTTPS host,
  newly issued keys, DNS, SMTP, privacy details, backups remain launch inputs.

## Final checkpoint (23 October 2026, 08:00 Lagos)

### Later user-authorized consolidation and promotional pages

The human subsequently requested selective consolidation from `simplr24`, then
three promotional entry points. This was ordinary active work after the overnight
window; the overnight automation remains paused. `simplrsuite` is now explicitly
the canonical workspace in `AGENTS.md` and README. See `work/consolidation.md` for
the complete source comparison, port decisions, and host configuration.

- Source found at `C:/Program Files/Ampps/www/simplr24`, HEAD `43079aa`, with
  pre-existing uncommitted changes. All 285 inventoried source files remain
  byte-identical after this work; no source credentials or customer data imported.
- Reused and adapted the toast component, four-role recovery entry point (adding
  secure employee/manager password recovery), and developer documentation route.
  Most source application code already existed in this repository. Unsafe or
  unfinished subscription/payment and dashboard experiments were not imported.
- Applied migration `006_staff_password_recovery.sql` locally. Staff recovery
  explicitly binds role, company, directory identity and auth record, and checks
  the current password stamp. Duplicate emails across companies/roles are tested.
- Added `/landing/business/`, `/landing/talents/`, `/landing/work/` with separate
  messaging, colors and account journeys. Root hostname routing supports
  `business.simplr.com`, `talents.simplr.com`, `work.simplr.com`. The Work audience
  is employees/managers. Host routing uses an exact allowlist; private `/work/`
  remains blocked. HTTPS/DNS/hosting was not changed.
- Added optional APP_BUSINESS_URL / APP_TALENT_URL / APP_WORK_URL origins for
  promotional and account-mail links. Existing APP_URL remains the mail fallback.
- Final verification: **387/387** consolidation HTTP checks (includes core and
  talent), **40/40** marketing routing/configuration checks, **42/42** actual
  Chromium browser checks, **19 changed PHP files** linted without errors. Node
  syntax and `git diff --check` passed. Source hashes and pre-consolidation ZIP
  validity passed. Final HTTP evidence: `consolidation-smoke-1792743433.json`;
  browser: `consolidation-browser.json`; marketing: `marketing-smoke.json`.
- Actual desktop/mobile screenshots for every audience are in
  `work/test-results/landing-{business,talents,work}-{desktop,mobile}.png`.
  Headless Chromium automation worked despite the earlier CUA initialization
  failure. All five newly viewed audience screenshots showed coherent layout;
  automated desktop/mobile overflow and action visibility checks passed for all.
- Changes remain local and uncommitted, including all preserved overnight work.
  Launch blockers listed below still apply. Staff password recovery is now done;
  broader staff toolkit permissions and production integrations are still open.

The overnight window has ended. Automation `simplr-suite-overnight-improvements`
was successfully updated to **PAUSED**. Do not resume overnight work on later
heartbeats. Final verification of the active talent change was completed after the
deadline. A new human request asks for dashboard images; use the saved screenshots
below, clearly labelled as the overnight test build.

Changes since the earlier checkpoint:

- Replaced broken talent authentication with shared styled signup/sign-in,
  email verification, resend, recovery, single-use hashed one-hour tokens,
  password hashing, rate limits, CSRF, session rotation and reset invalidation.
  Client-submitted account IDs and verified status cannot select identity.
- Added migration `005_talent_workspace.sql`, applied to local `simplr_dev`.
  New `talent_portfolios` are account-owned, private by default, and published
  only by explicit selection. Historical `talents` data remains untouched and
  is not automatically imported or published. This is a compatibility gap to
  address with owner-reviewed migration, not data loss.
- Added professional profile editor, safe HTTPS portfolio links, escaped output,
  input limits and optimistic edit conflict handling. Unsupported uploads return
  an explicit validation error. Applications, resumes and messaging remain
  unavailable in the new talent workspace and are labelled accordingly.
- Added `/users/admin/talent-directory.php`: verified admins only, opted-in
  verified talent only, search and pagination, no sign-in email disclosure.
  Making a profile private immediately removes it from results. Recruiting
  navigation points to this directory. Talent navigation is role-specific.
- Replaced old talent login/signup/verification/profile submission/list routes
  with adapters to the new paths. Other legacy talent routes remain unreviewed.
- Talent sign-out returns to talent sign-in. Corrected an encoding artifact in
  the generic workspace search placeholder after screenshot inspection.

Verification:

- `python scripts/talent_smoke_test.py`: **267/267 passed**. Includes a fresh
  197-check core run, then talent signup/verification/login/reset, role isolation,
  account ownership, private/listed/unlisted visibility, email privacy, XSS,
  CSRF, stale update, unsafe link and unsupported upload rejection, logout.
- Standalone core run also passed **197/197** before talent checks. All test
  fixtures are synthetic `example.test` accounts with local file mail only.
- Full PHP syntax check completed: **379 files, zero errors**, stored in
  `work/test-results/php-lint.json`. The count includes some bundled PHP sources
  outside excluded dependency directories. Final logout and workspace edits also
  passed individual lint. Node syntax and `git diff --check` passed; Git reported
  only normal LF/CRLF conversion notices. Latest HTTP evidence is
  `work/test-results/talent-smoke-1792737079.json` (**267/267**).
- Existing screenshot evidence: `work/test-results/dashboard-desktop.png`
  and `projects-mobile.png`. Desktop contains a deliberately escaped XSS test
  project title; mobile screenshot predates the search-placeholder text fix.
  These are actual browser captures, not generated mockups. Earlier desktop and
  mobile interaction checks passed. New talent browser visual QA is still pending:
  CUA failed to initialize with `failed to write kernel assets ... path specified`.
- Local PHP and dedicated MySQL services were restarted after they were found
  stopped. Existing datadir and persisted credentials reused, with NO init-file
  reset and no data import. `/health.php` returned `status: ok`.

Launch blockers remain: direct legacy endpoints and uploads are incompletely
audited; full staff toolkit permissions are missing; payment/banking/payroll
providers are unconfigured/unverified; committed credentials need rotation;
legacy data compatibility, privacy contact placeholders and deployment/backup
validation remain. This is a materially improved local build, **not a certified
production-ready release**. No deployment, push, real mail or financial operation
was performed. Changes remain in the shared working tree on the branch below.

## Earlier checkpoint (23 October 2026, about 00:40 Lagos)

Repository: `C:/Users/HP/simplrsuite`; branch `codex/production-readiness`, created
from initial `main` commit `2dbaf7c`. Changes are not committed or pushed. Original
user-owned untracked `start-server.ps1` was preserved and its PHP invocation was
updated to use `router.php`. `.env` remains on disk, but its removal from tracking
is staged and it is now ignored. Never print its values. Do not reset these changes.

## Implemented

- New shared `app/bootstrap.php`, database/auth/mail helpers, environment config,
  escaping, CSRF, safe exceptions, session rotation, rate limits, typed tenant
  checks, hashed expiring single-use account tokens. Production errors do not
  disclose connection details. Existing three DB adapters use this configuration.
- Rebuilt public `index.php`, admin account screens and overview with forest green,
  lime, and neutral design system in `public/styles/suite.css`; local SVG icons,
  responsive shell, real metrics, honest empty states. Marketing preview explicitly
  says it is illustrative. No random balance/KPI widgets or false testimonials.
- `app/modules.php` and `users/admin/workspace.php`: 21 allowlisted record areas:
  projects, tasks, departments, managers, employees, teams, leads, customers,
  contacts, accounts, deals, invoice register, expenses, ledger, inventory,
  applications, incidents, compliance, products, office assets, objectives.
  Search/pagination, create/edit, input validation, relationship tenant checks,
  HTML escaping, and optimistic version conflict protection. No deletes added.
- `/api/records.php` GET paginated reads and POST creates, verified admin cookie
  session plus CSRF required; it is NOT a public API-key/OAuth integration.
- Admin signup ignores client-supplied status/company IDs; verification and reset
  use real routes, expiry, token hashes, and mail abstraction. Session password
  fingerprint invalidates sessions after password reset.
- Staff invitations (`users/admin/invite.php`, `users/accept-invitation.php`),
  hashed 24-hour tokens, user-chosen passwords, recipient binding, no plaintext
  password email. Employee/manager sign-ins repaired and restyled. Staff workspace
  shows their own assigned work; employees may update only assigned task status.
  Manager login no longer overwrites every department's manager. Shared admin
  session helper now rejects employee/manager identities.
- 17 legacy create endpoints migrated through `app/legacy-record.php` (admin
  department/project/task/team/manager/employee, legacy invite/create-directory,
  finance add endpoints, CRM adds, project toolkit adds). Company IDs come from
  session; validation and CSRF shared. Uploads explicitly rejected here until
  secure attachments are implemented. These adapters return JSON.
- 20 old read/page URLs redirect to the protected modern record pages (admin
  employee/team/task lists, CRM pages, finance lists/pages, project dashboard/list).
  These currently require admin; employee toolkit access remains to implement.
- Integration availability page accurately distinguishes local email log,
  configured but unverified SMTP, and unavailable payments/payroll/banking.
- Schema-only `database/schema.sql` derived from historical dump with NO records;
  fresh-install compatibility fixes for MySQL 8.4 (`DEFAULT (curdate())`, unique
  referenced identifiers). Migrations 001 accounts, 002 unique business email,
  003 objectives, 004 staff invitations. CLI migrator refuses installing into a
  nonempty DB and does not merge/delete duplicate accounts.
- Router/Apache private-file restrictions, upload execution deny rules, Docker
  mysqli/pdo_mysql and Apache configuration, `.dockerignore`, health endpoint,
  new 404 and responsive mobile route, README with setup/limitations.

## Verification so far

- Fresh schema installed successfully on isolated MySQL 8.4.5. No old data dump
  imported. Original records, integrations, and live environments untouched.
- Latest `python scripts/smoke_test.py`: **197/197 checks passed**. Exercises all
  21 record areas, signup/verification/reset, session rotation/invalidation,
  tenant isolation, XSS escaping, invalid dates/amounts, cross-company references,
  optimistic update conflict, staff invitations and one-time use, staff vs admin
  permissions, task updates, private paths/assets, legacy reader/write adapters.
- Earlier 142/177-check passes retained; one intermediate failure was MySQL
  collation in manager ID comparison. Fixed using binary comparison for numeric
  legacy ID fallback. Latest pass includes the fix.
- Initial full lint: 190 original PHP files passed. Later 203 passed. A new full
  lint is running in exec session 16398 as of this checkpoint; collect its result.
  It writes `work/test-results/php-lint.json`. JS node check passed. `git diff
  --check` follows in the same command; inspect final output.
- Actual in-app browser desktop sign-in and dashboard inspected. At 390x844,
  verified no whole-page horizontal overflow, navigation opens/closes, and editing
  a synthetic project persisted. Caught/fixed an overbroad router rule that blocked
  `/public/scripts/suite.js`; static asset assertions were added to smoke tests.
  Mobile sidebar now uses inert when closed, close/backdrop controls, Escape,
  focus restoration and keyboard trapping. CSS contrast/readability improved.
- Screenshots in ignored `work/test-results/`; dashboard-desktop.png is an earlier
  screenshot with synthetic XSS test title. Browser record id=1 was renamed via UI
  to `Workspace launch` afterward. New screenshot should be captured if needed.

## Local services and test workflow

- PHP on `127.0.0.1:8000`, process launched hidden with working directory repo and
  `php -S 127.0.0.1:8000 -t . router.php`. Logs `.local/php-*.log`. Log request URLs
  contain test tokens; don't print raw logs without redacting token query values.
- Existing local MySQL/phpMyAdmin server on loopback port 3306. The user created
  empty `simplr_dev`, then the canonical installer populated it with the base
  schema and all 17 migrations. Active local settings are in ignored `.env.local`
  with `APP_ENV=local`, `MAIL_TRANSPORT=log`, and `DB_DATABASE=simplr_dev`.
  Do NOT print credentials. The same MySQL account has broad privileges; never
  use it for production deployment. `simplrco_dashboard` remains a read-only
  historical reference and was not used by migrations or tests.
- MySQL launch had local credential reset via `.local/reset-root.sql` (private);
  the current process used `--init-file` for that reset. Don't change other MySQL
  instances or restart with another application's data dir.
- Mail test outbox `storage/mail/*.json`; test fixtures and auth secrets ignored.
- Smoke tests create synthetic companies, emails at example.test, and records;
  retain them for review. Rate limits are real: repeated rapid runs can reach
  signup/IP limits. For completed local browser checks, only rate-limit windows
  tied to loopback and synthetic fixture logins were expired; application limits
  were not weakened.
- Latest successful test browser credentials are `.local/browser-fixture.json`
  (synthetic only). Browser tab id 1 in IAB/browser 2 is signed into an earlier
  synthetic workspace. If session persists, reuse. Viewport was 390x844; reset
  before finishing. CUA docs must be reloaded after context reset per tool rules.
- No Docker executable available; container build/Apache rules not runtime tested.

## Next priorities — significant work remains

1. **Complete old-route coverage.** Many original independent PHP endpoints are
   still unaudited and may have no authorization, tenant checks, CSRF, output
   escaping, or trusted provider verification. New UI protections do not fix those
   direct URLs. Enumerate remaining routes and migrate/secure them, especially
   update/delete, team/tool assignment, fetch/read, uploads, messages and payment
   callbacks. Do not falsely claim the whole app is safe. Keep supported behavior
   and replace obsolete sample pages with coherent routes; don't merely hide links.
2. **Talent journey** is still old/broken (`users/talents/db.php` is absent; legacy
   signup/verify paths disagree with schema). Implement equivalent modern secure
   talent signup/login/verification/recovery/profile with correct ownership,
   validated safe uploads and clear recruitment visibility. Do not expose private
   applicant data globally or invent hiring decisions. Public site links there.
3. **Assigned toolkit permissions for staff** are unfinished. New employee/manager
   workspace is a scoped task/project view; existing full toolkit capability has
   not been migrated into it. Admin adapters require admin and do not yet support
   the prior assigned-tool model. Add explicit assignment/entitlement checks and
   appropriate role-scoped toolkit access; test with multiple companies and roles.
4. **Providers:** old bank/payment/Marqeta/Flutterwave/Paystack scripts still exist
   with hardcoded/sample credentials and unsafe/unverified flows. Never execute
   live requests using these keys. Remove hardcoded credentials, use server-side
   configuration, documented APIs, timeouts and honest failures; verify amounts,
   currency, identity, signatures, idempotency and tenant binding. Use provider
   test mode/mocks without transactions. Full activation needs real owner/provider
   setup. Old committed credentials need rotation before launch. Do not merely
   rename placeholders as functioning integrations.
5. Remaining functional coverage: events/meetings/calendar, messages, support,
   subscriptions/entitlements, reports, safe attachments, actual payroll processing,
   profile/settings and workspace currency/timezone. Invoice register is NOT a
   legally compliant invoice generator. Old records and relationships must remain
   usable. Existing legacy project/task references mix numeric and unique IDs;
   new paths consistently use projects.unique_id for tasks.project_id.
6. UI/data polish: field/grid responsiveness, useful list filters (legacy filtered
   task pages currently redirect to the all-tasks list), clearer money currency,
   accessible statuses, complete talent/staff/mobile browser verification. Add
   assets/behavior checks when changing routes. New shell is intentionally smaller
   than the old 4,000-line placeholder-heavy admin page; fill real feature gaps.
7. Deployment verification: test migrations against realistic *synthetic* old
   schemas/records, Docker/Apache rules (not yet run), PHP extensions for providers,
   private-file restrictions on actual hosting, monitoring, backup/restore and
   upgrade instructions. Don't deploy/push without appropriate authorization.
8. Existing privacy page has address/phone placeholders and a JavaScript-generated
   fake last-updated date. Do not invent legal business/contact details. Flag exact
   owner inputs for the morning and improve template handling honestly.

Preserve all completed work, test evidence, user files, and source history. No
routine approval questions. Report material new failures or real external
dependencies. Morning report must distinguish local verification from production
readiness and provider activation.

## Accessibility continuation — 28 Oct 2026 (Copilot)

- Local-only Playwright spot check covered `/`, the business/talent/work landing
  pages, the privacy notice, admin and employee sign-in, and password recovery.
  All 8 routes returned HTTP 200 and had one main landmark and one H1. The check
  found no unnamed links/controls or images missing `alt`; document width matched
  the viewport at 320px, 390px and 1280px.
- Keyboard tab checks on admin sign-in, employee sign-in and recovery showed
  visible focus and a sensible sequence. Sign-in field outlines were only
  2px pale green (`#dceadf`) against white. Updated the auth form focus outline
  in `public/styles/suite.css` to a 3px contrasting green ring.
- This is a partial public/auth accessibility pass, not completion of the
  requirement. Authenticated workspace journeys, calendar dialog, people tree,
  zoom and a full contrast review remain. `quality-accessibility` is now
  `in-progress`, not verified.
- Corrected `privacy-owner-input` back to `blocked`: the supplied business and
  public contact details are configured, but legal approval and CAC status
  confirmation required by the item are still outstanding. The prior
  `verified` state was too broad.
- No application login was performed; no database, email, payment, external
  service or deployment actions were taken.

## Accessibility continuation — authenticated narrow-screen checks (28 Oct 2026, Copilot)

- Used the local app at `127.0.0.1:8000` and the synthetic owner fixture; browser
  requests to non-local hosts were blocked. No business records were changed.
- Calendar keyboard check passed: Enter opened the day dialog, the dialog had an
  accessible name, focus moved inside, Escape closed it, and focus returned to
  the day button. Visible organization-chart controls had accessible names.
- At 320px, both authenticated pages initially overflowed horizontally by 44px
  because the compact topbar controls retained their wider-workspace layout.
  Added a <=364px topbar padding/gap/workspace-width adjustment in
  `public/styles/suite.css`. Calendar and organization pages then fit at 320,
  360, 390 and 1280px. Both authenticated pages also fit a 640×422 CSS viewport,
  equivalent to 200% desktop zoom from 1280×844.
- `requirements.html` records the new evidence but keeps `quality-accessibility`
  in progress. Full authenticated workflow coverage, a full contrast review and
  remaining zoom checks have not been completed. Next safe scope: continue local
  keyboard and error-feedback checks on synthetic staff workspace/form routes.
- Sign-in affected only local synthetic authentication/session/rate-limit state;
  no email, payments, provider calls or deployment actions occurred.
- A later boundary probe found 1–3px overflow at 361–363px with the initial
  360px breakpoint. The CSS breakpoint was extended through 364px; the attempted
  follow-up login was throttled, so that boundary adjustment still needs a
  browser recheck after the local fixture's cooldown. Do not clear the rate limit
  to force the check.

## Local checks and throttle-safe follow-up — 28 Oct 2026 (Copilot)

- `php scripts/preflight.php`: **15/15** local read-only checks passed, including
  database connectivity, applied migrations, route targets, private storage,
  required extensions, and log-mail safety. The script explicitly excludes
  production DNS/HTTPS, SMTP delivery, provider activation, backups, and legal
  compliance.
- Non-login unit suites passed: rich text **15**, accounting **21**, recruiting
  policy **14/14**. `git diff --check` passed for the accessibility/tracker/handoff
  files; only the existing LF-to-CRLF warning appeared for `suite.css`.
- Re-attempted the authenticated narrow-width check once after the earlier
  cooldown. The synthetic login was still throttled, so no page measurements
  were obtained. No further login attempt was made, and no rate-limit rows were
  cleared or altered.
- After the natural cooldown, one delayed local-only synthetic owner login
  succeeded. Calendar and organization pages returned HTTP 200 and matched
  document width to viewport width at 320, 360, 361, 362, 363, 364, 365 and
  390px. This verifies the previously open 361–364px breakpoint. No rate-limit
  rows were cleared or altered.
- The <=364px topbar rule remains in `public/styles/suite.css`. Keep
  `quality-accessibility` in progress because the broader workflow, contrast,
  and browser zoom review is not complete.
- A local-only contrast probe identified shared muted text at about 4.27:1 on the
  landing background. Darkened `--muted` from `#6f7b76` to `#66736e`; browser
  checks now measure 4.81:1 on the landing background and 4.95:1 on white.
  Public, privacy, admin sign-in and employee sign-in pages still fit at 320px.
  This improves the shared token but does not replace a full contrast review.
- Extended contrast adjustments to low-contrast small text in the public preview
  and workflow mockups, while retaining a light brand suffix on the dark auth
  panel. The browser check measured foreground/background contrast for 69 rendered
  label instances across 7 public/privacy/auth routes; all passed 4.5:1 normal
  text or 3:1 large text. A separate visible-text scan found no remaining below-
  target text color pairs on those seven routes. This is not a full application-
  wide contrast audit; backgrounds with imagery/gradients and authenticated
  screens still require review.
- Updated `scripts/migration_matrix_test.php` so schema comparisons also include
  foreign-key mappings and update/delete actions, then ran
  `php scripts/migration_matrix_test.php --all` against generated
  `simplr_dev_mig_*` databases: **134/134** checks passed in 939 seconds. Clean
  install/no-op and all **32/32** synthetic baselines passed; the synthetic
  owner, employee, project and task rows survived and resulting columns,
  indexes, foreign keys and actions matched the clean-install schema. The
  duplicate-email migration stopped without deleting rows. The report lists 19
  migrations safe to rerun and 13 that fail if replayed. That matrix did not
  inject partial-DDL failures; a separate migration-027 recovery drill is
  recorded below. No historical or production database was accessed.
- Added `scripts/migration_failure_recovery_test.php` to inject the known
  migration-027 duplicate-column partial-failure case in a disposable
  `simplr_dev_recovery_*` database. It passed **9/9** checks: the manager-link
  ALTER/backfill persisted, the runner did not record the incomplete migration,
  the missing team index was applied, and only after schema verification was
  migration 027 recorded so the supported runner could finish. No non-synthetic
  database was touched.
- Corrected `deploy/release.md`: after manual partial-migration repair, the
  runner cannot record the file automatically. The documented recovery now
  requires statement/data inspection, migration-specific repair, schema
  verification, then an explicitly reviewed marker insert or backup restore.
  The new synthetic test is documented in `README.md`. This validates one
  specific failure mode, not all partial-DDL cases; `data-upgrade` remains in
  progress.
- A one-shot delayed employee accessibility follow-up failed in the Playwright
  DOM-inspection expression with `TypeError: Cannot read properties of undefined
  (reading 'length')`. It produced no employee-control findings; an earlier
  staff-page check's three possible unnamed controls remain unconfirmed. No
  follow-up login was attempted; fix the inspection expression before any
  future authenticated check.

## Synthetic role-transition checks — 28 Oct 2026 (Copilot)

- Added `scripts/role_transition_test.php`. It refuses non-local or non-log-mail
  configuration, creates and drops only a generated `simplr_dev_roles_*`
  database, installs the current schema/migrations, and uses synthetic records.
- `php scripts/role_transition_test.php` passed **21/21** checks. Evidence covers
  linking an existing employee to a pending manager record, department-head
  assignment, audit event creation, manager demotion while preserving the
  employee, employee-role restoration, direct-report/department/project manager
  reassignment, invalidating a pending manager invitation, organization chart
  leadership, and rejection of self-replacement with dependent records left
  unchanged. The test calls transition helpers directly and sends no email.
- `identity-roles` is now **in progress**, not verified. The UI promotion +
  invitation retry path and full workspace-facing journey still need synthetic
  coverage.

## Consolidation smoke rerun — 28 Oct 2026 (Copilot)

- Ran `python scripts/consolidation_smoke_test.py` with local app configuration
  and log mail: core **197/197**, talent **268/268**, and consolidated
  recovery/consolidation **388/388** checks passed. No external email or provider
  traffic was used.
- This is targeted current evidence, not the complete release gate. Access,
  attachment, recruiting, tenant-isolation, GitHub, payment-mock, browser, and
  other documented suites have not all been rerun against the latest worktree;
  `quality-full-suite` remains in progress.

- Also re-ran `php scripts/preflight.php`: **15/15** local read-only checks
  passed. The focused PHP suites passed rich text **15/15**, accounting **21/21**,
  and recruiting policy **14/14**. These confirm local prerequisites and focused
  unit behavior only; they do not establish production readiness.
- `python scripts/marketing_smoke_test.py` passed **40/40** local public-route
  checks, including audience-specific page selection, canonical links, correct
  account destinations, rejection of a hostile `Host` value, and environment-
  shaped audience-link generation. This does not resolve or validate live DNS,
  TLS, or deployed origins.
- After waiting out the local authentication cooldown, ran
  `python scripts/access_smoke_test.py` once: **234/234** checks passed. It
  covered synthetic owner/employee/manager grants, view/edit/revocation,
  tenant boundaries, CSRF, stale grant forms, legacy write paths, and blocked
  routes on local ports. The test deliberately leaves a synthetic view-only
  employee grant for browser checks and writes `.local/access-browser-fixture.json`.
  No non-local request, email, or payment traffic was made. Do not retry login
  suites until another natural cooldown has elapsed.
- After a further natural cooldown, ran `python
  scripts/attachments_smoke_test.py` once: **54/54** checks passed using only
  synthetic files and records. Coverage includes upload validation, forced
  private downloads, cross-company/view-only grant behavior, storage web denial,
  and scoped removal; the run removed its own test attachments. No malware or
  real customer files were used. Antivirus scanning, owner-approved retention,
  and production storage/backup ownership remain undecided, so
  `product-files` remains blocked.
- No new owner decisions or evidence were available for CAC status/legal
  approval, production origins, support coverage, backup objectives, launch
  scope, or attachment scanning/retention. Do not infer approval or mark those
  owner-dependent gates verified; preserve their existing blocked/pending
  status until evidence is supplied.

## Scheduler-monitoring tracker reconciliation — 28 Oct 2026 (Copilot)

- The monitoring item had remained `pending` despite the existing implementation
  and isolated failure/recovery evidence. Updated it to `in-progress`, not
  `verified`, and added the evidence and remaining gates to `requirements.html`.
- Reviewed `deploy/scheduler.md` and `scripts/scheduler_status.php`; ran the
  status command read-only against the local development database. It exited 1
  and reported both jobs `late`, consistent with the paused local scheduler and
  the documented alert behavior. No jobs were started.
- Staging alert delivery, a named on-call/alert recipient, monitoring for SMTP,
  provider, storage and backup failures, error-tracking choice, and an incident
  exercise remain unverified. No production host, provider, external alert or
  scheduled automation was contacted or enabled.
- Also reconciled the stale `quality-full-suite` note with the current
  28 Oct results: consolidation 388/388, preflight 15/15, access 234/234,
  attachments 54/54, marketing 40/40 and recruiting policy 14/14. Kept it
  in progress because the documented suite set is not complete; the recent
  authentication checks mean another login suite should wait for natural
  cooldown.
- Reconciled `identity-talent` from pending to in progress based on the
  268/268 talent checks included in the current consolidation run. That synthetic
  journey covers signup, verification, login/logout, password recovery, profile
  visibility/withdrawal, escaping and cross-identity protection. Resume upload
  is explicitly unavailable; browser application journey, employer access
  constrained to application context, and retention/deletion policy remain open.
- Re-ran non-login local unit checks: `php scripts/richtext_unit_test.php`
  **15/15**, `php scripts/accounting_unit_test.php` **21/21**, and
  `php scripts/recruiting_policy_test.php` **14/14**. `node --check` passed
  for `scripts/projdashboard.js` and `scripts/messages.js`; scoped
  `git diff --check` passed. These focused checks do not close the full-suite
  gate or production-only requirements.

## Local backup/restore drill — 28 Oct 2026 (Copilot)

- Ran `php scripts/backup_restore_drill.php 'C:\Program Files\Ampps\mysql\bin'`.
  The script's guard confirmed the local-only environment (`APP_ENV=local`,
  log mail, localhost MySQL, `simplr_dev` source database).
- Drill passed: **111 tables**, **3,442 rows**, and **7 attachments** restored;
  table counts/checksums and attachment hashes matched; source remained
  unchanged. The counts-only result is in `work/test-results/backup-restore.json`.
- Removed only the exact generated `simplr_restore_*` test database and its
  private `.local/backup-drills/20261028_063654_d3e2b8` directory after
  verification; retained the report. This demonstrates a local snapshot
  restore only. It does not configure or verify production backup scheduling,
  off-host encryption/access, retention, alerts, or owner-approved RPO/RTO.
  Updated `deploy-backup` to `in-progress`, not verified.
