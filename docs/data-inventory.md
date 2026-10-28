# Personal data inventory (technical draft for the privacy owner)

A factual map of the personal data the current build stores, who can reach it and
what removes it, taken from the local schema and code on 27 Oct 2026. It is not a
legal assessment: purposes, lawful bases and retention periods are business
decisions, listed as questions at the end. No real user data was inspected.

All workspace data lives in one MySQL database and is separated by `company_id`.
Uploaded files live in `storage/attachments/` under random names and are served only
through `/api/attachments.php` after a permission check. `scripts/tenant_isolation_test.py`
checks the company boundary for the main surfaces.

## What is stored

| Data | Where (table) | About whom | Who can see it | What removes it today |
| --- | --- | --- | --- | --- |
| Owner account: name, company, email, password hash | `users` | Business owners | The owner | No self-service account deletion. |
| Staff directory: name, email, phone, job title, GitHub username, reporting line | `employees`, `managers`, `departments`, `teams` | Employees, managers | Owner; staff with a grant for the tool | Archive, then owner purge from Trash (`app/archive.php`), which also deletes their invitations and alerts. |
| Staff sign-in: email, password hash; invitation and reset tokens (hashed) | `employees_auth`, `manager_auth`, `staff_invitations`, `staff_password_resets` | Staff | Nobody through the UI | Tokens are single-use and expire, but the rows are never cleaned up. |
| CRM: lead, customer and contact names, emails, phones, addresses | `leads`, `customers`, `contacts`, `accounts`, `deals` | The business's customers | Owner; staff with a grant | Archive and purge. |
| Tasks, projects, meetings, notes (may contain personal details in free text) | `tasks`, `projects`, `events`, `meetings`, `documents` | Anyone mentioned | Owner; staff with a grant; assignees see their own tasks | Archive and purge. |
| Files and resumes | `record_attachments` + `storage/attachments/` | Anyone in the file | Owner; staff with a grant for the record's tool; a candidate sees only their own resume | Removing the attachment deletes the file. A withdrawn application ends employer access to the resume but keeps the file. |
| Job applications: name, email, phone, resume | `applications`, `application_submissions` | Applicants | Owner; staff with the Applications grant | Withdrawal ends resume access; the record stays. |
| Talent profiles: name, industry, email, password hash, profile details, visibility | `talentprofile` and related talent tables | Job seekers | The talent; employers only when the profile is listed or through an application | Status changes; no full deletion flow found. |
| Alerts and email outbox: title, body, email status | `notifications`, `notification_preferences` | Owners and staff | The recipient only | Deleted when the staff member is purged; otherwise kept forever. |
| Queries and penalties: message, response, decision, appeal | `task_queries`, `penalties` and handoffs | Staff | The issuer, the subject and the owner | Not deleted. These are HR records; retention needs a decision. |
| Audit trail: actor role and id, action, record ids, small details | `workspace_audit` | Owners and staff | The owner (Activity page) | Kept forever. |
| GitHub: account login, encrypted token, cached issue data | `integration_connections`, `integration_cache`, `github_*` | Owners and GitHub users | The owner | Disconnect revokes the grant and deletes the token; the cache is cleared on change. |
| Billing: order reference, expected payer email, amounts | `billing_orders` and entitlement tables | Owners | The owner | Kept (financial records). Card details are never received; checkout happens on the provider's page. |
| Security counters: hashed IP and identifier buckets | `auth_rate_limits` | Anyone who signs in | Nobody | Rows are reset after 15 minutes but never deleted. |
| Mail log in local mode | `storage/mail/` | Recipients | Server operators | Not rotated. Production uses SMTP instead. |

## Legacy tables still in the schema

These come from the earlier product. Current pages do not write them, and the pages
that did return 404. Some may hold data if a historical database is ever imported:

- `bankaccount` (names, emails, phone numbers, **bank account numbers**, bank names)
- `talents` (gender, salary expectation, resume file, LinkedIn), `talentchat`, `messages`
- `bookdemo`, `clients`, `nairapayments`, `usdpayment`, `totalpayments`, `toolkit_subscriptions`
- `users.token` and `password_resets.token`: older plaintext token columns. Current
  code stores token hashes in `auth_tokens`, `staff_password_resets` and `talent_tokens`.

`AGENTS.md` already forbids importing historical dumps. Any import of these tables
needs the owner's approval and a decision on whether to drop them.

## Gaps found

1. No scheduled cleanup at all: expired tokens, old alerts, audit history, rate-limit
   rows and local mail logs grow forever.
2. No way to export one person's data, and no deletion request flow for owners,
   staff or talent accounts. Archive and purge exist for workspace records only.
3. A withdrawn application keeps the resume file even though no employer can open it.
4. Free-text fields (task notes, meeting details, documents) can hold anything, so
   retention rules have to cover them too.
5. Backups (`scripts/backup_restore_drill.php`) copy the database and attachments
   together; deleting a person does not remove them from older backups.

## Questions for the privacy owner

1. How long should each row above be kept after it stops being needed: closed
   applications, withdrawn resumes, alerts, audit trail, HR queries and penalties,
   inactive talent profiles, deleted staff?
2. Who may ask for an export or deletion (owner, staff member, talent, applicant),
   how is their identity checked, and how fast must it be done?
3. Should a withdrawn application delete the resume file immediately?
4. How long are backups kept, and is "deleted from live data, ages out of backups"
   acceptable?
5. Should the legacy tables listed above be dropped from production?
6. Which processors receive data (SMTP provider, Paystack, Flutterwave, GitHub,
   hosting provider) and in which countries? The privacy notice must list them.

Once these are answered, the technical work is: a retention job in `jobs/`
(scheduled like the others in `deploy/scheduler.md`), an export and deletion
workflow with tests, and an updated privacy notice.
