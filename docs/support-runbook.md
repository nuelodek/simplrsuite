# Customer support and incident runbook

This runbook describes support paths that exist in the application and the
operational decisions still needed from the business owner. It does not create a
service-level agreement or declare a staffed support service.

## Confirmed customer-facing paths

| Need | Current path | Scope and limitation |
| --- | --- | --- |
| Workspace question or access issue | Authenticated `/users/help.php` | An employee or manager submits a private request to their workspace administrator. The author and workspace administrator can read it; the administrator can reply and resolve it. Requests are not routed to Simplr staff. |
| Business owner password recovery | `/users/admin/forgetpassword.php` | Use the account recovery flow. Staff must use the shared recovery page and provide the workspace code. Do not request or accept a user's password. |
| Employee/manager password recovery | `/users/forgetpassword.php` | The shared recovery flow requires role, work email and workspace code. Reset links are expiring and single-use. |
| Talent account recovery | `/users/forgetpassword.php` | Select the talent account type and follow the email recovery flow. |
| Payment return without confirmed access | Billing return page | Keep the payment reference, check status through the page, and contact the payment provider through the configured private merchant channel if unresolved. Do not ask the customer to pay again while status is pending. |
| Privacy question | Public privacy policy contact details | The listed email and phone are the business contact supplied by the owner. Use them for privacy contact as published; do not imply a staffed general support desk unless the owner confirms this. |
| Service availability | `GET /health.php` | Database connectivity/liveness only. It does not prove that login, email, payments, background jobs or all customer journeys are healthy. |

## Triage and handling

1. Classify the report as account access, workspace/help request, billing,
   privacy/data request, suspected security incident, or service availability.
2. Record the report in the existing authenticated workspace help flow when it
   concerns one workspace. Keep customer-specific details out of public issues,
   chat transcripts and source control.
3. Ask only for the minimum information needed to identify the affected account
   or transaction. Never request passwords, reset links, MFA secrets, complete
   payment-card or bank details, private keys, provider credentials, or uploaded
   resumes as troubleshooting material.
4. For a suspected compromise or exposure, preserve relevant timestamps and
   non-secret error references, restrict further sharing, and escalate to the
   designated technical/security owner. Do not promise containment or notify
   regulators/customers on behalf of the business without the owner's incident
   decision and qualified advice.
5. For payment uncertainty, use the order/reference and provider verification
   path. A browser redirect is not proof of settlement. Do not manually grant
   entitlements or retry a real charge as a diagnostic step.
6. Close workspace tickets only after the requester has a response and the
   workspace administrator records the resolution. A workspace ticket does not
   constitute deletion of the underlying account or records.

## Operational decisions required before declaring support ready

The business owner must designate:

- a monitored general support channel and responsible person/team;
- staffed hours and response/incident escalation targets (do not infer an SLA);
- a backup contact for loss of access to the primary support channel;
- a security incident decision-maker and a privacy/legal escalation contact;
- how customer identity is verified before account or data changes;
- retention and access rules for support conversations and incident evidence.

Until these are supplied and exercised, this document is an internal runbook
draft. Workspace help and account recovery are implemented, but general support
coverage and incident response are not verified.
