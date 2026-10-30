# Launch scope: what works, what is limited, what is not built

For owner approval before launch. Each row is checked against the code on 30 Oct
2026. "Available" means it works end to end in the local build and has automated
tests; it does not mean production hosting, email delivery or a payment provider has
been verified. "Limited" describes intentionally narrower functionality. Marketing
pages, pricing, support scripts and sales conversations should promise only the
first column.

| Area | Status | What customers get | What they do not get |
| --- | --- | --- | --- |
| Projects, tasks, boards, calendar, meetings | Available | Records, list and board views, due dates, assignment, calendar and ICS export, meeting pages. | — |
| People and teams (employees, managers, departments, teams) | Available | Directory, reporting lines, promotions, invitations by email (staff choose their own password). | Invitation delivery depends on SMTP and a real HTTPS address, both still unverified (see `requirements.html`). |
| Staff tool access | Limited | Owner grants view or edit per tool; enforced on pages, API and exports. | The older "assigned toolkit" model is not migrated yet. |
| CRM (leads, customers, contacts, accounts, deals) | Available | Records, stages, boards, CSV export. | No email sync or marketing automation. |
| Finance: expenses, ledger, inventory | Available as records | Recordkeeping and reports. | Not audited statements; reconcile with an accountant. |
| Accounting (chart of accounts, journal) | Available as records | Double-entry journal with balance check, trial balance and reports. | Not a tax or statutory filing tool. |
| Invoice register | Limited | A register of invoices you issued elsewhere. | Does not issue legally compliant or tax invoices, send invoices or collect payment. |
| Payroll | Limited | Company-scoped NGN salary profiles, draft and approved pay runs with independent owner/HR approval, employee payslips, manually entered PAYE/pension/NHF/other deduction amounts, bank-transfer CSV exports, and a tenant-scoped Salary Vault ledger. Verified Paystack top-ups and independently reconciled bank deposits can fund the Vault; manual deposits must already be in the same Paystack payout funding pool. Paystack salary transfers reserve cleared allocations first. | No automatic Nigerian tax/statutory calculation or filing; professional verification is required. Top-ups and transfers are disabled unless separately configured. The Vault is an auditable allocation against a shared Paystack merchant balance, not a segregated account or custodial wallet. No bank feeds or live bank balances. HR penalties are never imported or deducted automatically. |
| Banking | **Not built** | — | No bank feeds or balances. General bank transfers are unavailable; payroll payouts are limited to the separate workflow above. Legacy banking demo pages are blocked. |
| Alerts, task queries, penalties | Available | In-app alerts, email (instant or digest), formal queries with response windows, owner-approved penalties, appeals. | Needs the scheduler running every minute in production (`deploy/scheduler.md`). |
| Recruiting and talent directory | Available | Job posts, public job pages, applications with private resumes, interviews, shortlist, talent profiles that start private. | Talent account recovery and consent flows still need the end-to-end review listed in `requirements.html`. |
| Job posting to LinkedIn, Facebook, Instagram | Limited | Works only after the Simplr team configures each platform's keys; otherwise shown as "Not connected". | Self-service connection. |
| GitHub | Available | One-click connect (several accounts), issues, PRs and commits on projects, issue-to-task sync with assignee matching, scheduled sync. | Writing back to GitHub. |
| Accounting and people-system connections | Limited | QuickBooks Online, Xero and Zoho Books authorize a provider account and verify the selected company/organization; Workday authorizes a configured tenant. Tokens are encrypted at rest; accounting connections support verification and disconnect, while Workday supports disconnect. | No accounting records are imported, exported or modified; Workday worker data is not read. Provider credentials, callback registration and production access are not verified locally. See the [connection setup guides](quickbooks-integration.md). |
| Other future integrations (Slack, Google, ADP and others in the catalog) | **Not built** | Planned catalog entries show disabled Connect buttons. | No working connector; the buttons do not connect accounts. |
| Files and attachments | Available | Private uploads (PDF, images, CSV, macro-free Office files), Files library, per-tool permissions, quotas. | Antivirus scanning (owner decision pending). |
| Search, reports, CSV export | Available | Workspace search limited to the tools a person can open; CSV exports with spreadsheet-formula protection. | — |
| Direct messaging | Available | Company-scoped direct and group chats, teammate and workspace-record `@` references, mention notifications, and selected-message sharing with a link to the original conversation. Access follows the Messages toolkit grant. | Updates refresh every five seconds; no WebSocket service, rich media, attachments or push notifications. Apply migration `036_workspace_messaging.sql`. |
| Attendance, leave, benefits | Limited | Attendance records, leave requests, and assigned benefits are tracked in the workspace; they connect to the modern employee directory and people UI. | No clock-in, leave balances, automated approval rules or benefits administration beyond assignment records. |
| Subscriptions | Limited | Paystack (NGN) and Flutterwave (USD) checkout in test mode; entitlements per tool. | Live mode, automatic renewals, refunds and disputes are not enabled; billing enforcement is off until the owner signs off. |
| Support and help | Limited | Private help requests from staff to the workspace owner. | A Simplr support desk or service-level promise. |

## Review notes

- Payroll includes a tenant-scoped, immutable Salary Vault ledger. Paystack top-ups
  are credited only after server-side transaction verification; manual deposits
  need an independent owner/employee reconciliation and must already be in the
  Paystack payout funding pool. Each payroll run requires
  independent approval, and Paystack payouts reserve the tenant allocation before
  submission. Provider funds are pooled operationally, not segregated. Statutory
  amounts remain user-entered and require professional validation.
- GitHub repository sync and the QuickBooks Online, Xero, Workday and Zoho Books
  connection-only flows are described at their actual implemented scope. No
  connector in this group provides payroll, bank feeds, accounting-record sync or
  worker-data sync.
- Tax invoices and bank balances remain unavailable. Messaging and attendance
  have workspace implementations; leave balances and benefits administration
  remain limited as described in the table.

## Decisions the owner needs to make

1. Approve this table as the launch promise, or name rows to change.
2. Pricing page and sales material: confirm how payroll should be packaged. The
   existing "Finance & inventory" toolkit remains records-focused; payroll is a
   separate, limited workflow and is not statutory tax software.
3. Whether to keep the "more integrations on the way" catalog visible at launch.
4. Whether to delete the blocked legacy pages (banking demo, chat, old subscription
   pages) instead of only blocking them; `docs/dependencies.md` lists the outdated
   scripts they load.
