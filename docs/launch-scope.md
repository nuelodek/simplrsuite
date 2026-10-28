# Launch scope: what works, what is limited, what is not built

For owner approval before launch. Each row is checked against the code on 27 Oct
2026. "Available" means it works end to end in the local build and has automated
tests; it does not mean production hosting, email delivery or a payment provider has
been verified. Marketing pages, pricing, support scripts and sales conversations
should promise only the first column.

| Area | Status | What customers get | What they do not get |
| --- | --- | --- | --- |
| Projects, tasks, boards, calendar, meetings | Available | Records, list and board views, due dates, assignment, calendar and ICS export, meeting pages. | — |
| People and teams (employees, managers, departments, teams) | Available | Directory, reporting lines, promotions, invitations by email (staff choose their own password). | Invitation delivery depends on SMTP and a real HTTPS address, both still unverified (see `requirements.html`). |
| Staff tool access | Limited | Owner grants view or edit per tool; enforced on pages, API and exports. | The older "assigned toolkit" model is not migrated yet. |
| CRM (leads, customers, contacts, accounts, deals) | Available | Records, stages, boards, CSV export. | No email sync or marketing automation. |
| Finance: expenses, ledger, inventory | Available as records | Recordkeeping and reports. | Not audited statements; reconcile with an accountant. |
| Accounting (chart of accounts, journal) | Available as records | Double-entry journal with balance check, trial balance and reports. | Not a tax or statutory filing tool. |
| Invoice register | Limited | A register of invoices you issued elsewhere. | Does not issue legally compliant or tax invoices, send invoices or collect payment. |
| Payroll | **Not built** | — | No pay runs, payslips, statutory deductions or payouts. Penalty deductions are recorded for HR only and never paid or deducted. |
| Banking | **Not built** | — | No bank feeds, balances or transfers. Legacy banking demo pages are blocked. |
| Alerts, task queries, penalties | Available | In-app alerts, email (instant or digest), formal queries with response windows, owner-approved penalties, appeals. | Needs the scheduler running every minute in production (`deploy/scheduler.md`). |
| Recruiting and talent directory | Available | Job posts, public job pages, applications with private resumes, interviews, shortlist, talent profiles that start private. | Talent account recovery and consent flows still need the end-to-end review listed in `requirements.html`. |
| Job posting to LinkedIn, Facebook, Instagram | Limited | Works only after the Simplr team configures each platform's keys; otherwise shown as "Not connected". | Self-service connection. |
| GitHub | Available | One-click connect (several accounts), issues, PRs and commits on projects, issue-to-task sync with assignee matching, scheduled sync. | Writing back to GitHub. |
| Other integrations (QuickBooks, Slack, Google, ADP and others in the catalog) | **Not built** | Listed as "not connected yet" so customers can vote. | Any working connector. |
| Files and attachments | Available | Private uploads (PDF, images, CSV, macro-free Office files), Files library, per-tool permissions, quotas. | Antivirus scanning (owner decision pending). |
| Search, reports, CSV export | Available | Workspace search limited to the tools a person can open; CSV exports with spreadsheet-formula protection. | — |
| Direct messaging | **Not built** | — | Legacy chat pages are blocked. |
| Attendance, leave, benefits | **Not built** | — | No clock-in, leave balances or benefits administration. |
| Subscriptions | Limited | Paystack (NGN) and Flutterwave (USD) checkout in test mode; entitlements per tool. | Live mode, automatic renewals, refunds and disputes are not enabled; billing enforcement is off until the owner signs off. |
| Support and help | Limited | Private help requests from staff to the workspace owner. | A Simplr support desk or service-level promise. |

## Changes made in this review

- The integrations page said bank accounts, payouts and payroll "need a connected
  provider before they can be used", which implied they work once connected. It now
  says Simplr does not run payroll, payouts or bank balances yet, with the badge "Not
  available yet".
- No other reachable page claimed payroll, tax invoices, bank balances, messaging,
  attendance or benefits. The README already lists these limits.

## Decisions the owner needs to make

1. Approve this table as the launch promise, or name rows to change.
2. Pricing page and sales material: confirm they match the table, especially
   "Finance & inventory" (records only, no payroll despite its internal key name).
3. Whether to keep the "more integrations on the way" catalog visible at launch.
4. Whether to delete the blocked legacy pages (banking demo, chat, old subscription
   pages) instead of only blocking them; `docs/dependencies.md` lists the outdated
   scripts they load.
