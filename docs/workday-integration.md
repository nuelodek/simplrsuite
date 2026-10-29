# Workday OAuth connection

The Workday connector supports a tenant-specific OAuth authorization-code
connection, encrypted access/refresh-token storage, and manual refresh-token
rotation. This initial connection phase does not read, import, export, or modify
worker records.

## Configure the tenant and OAuth client

1. In Workday, have the tenant administrator configure an OAuth client for the
   integration system user and grant only the scopes required for the intended
   Workday APIs.
2. Set `WORKDAY_HOST` to the tenant's Workday service host only (for example,
   `wd5.myworkday.com` or `wd5-impl-services1.workday.com`), and set `WORKDAY_TENANT` to the exact Workday tenant
   name used by its OAuth endpoints.
3. Set `WORKDAY_CLIENT_ID` and `WORKDAY_CLIENT_SECRET` in the server
   environment. Never put credentials in source control or expose the secret in
   the workspace UI.
4. Set `WORKDAY_SCOPES` to the space-separated scopes registered for the OAuth
   client. Leave it empty if the tenant's client is configured to apply its
   default scopes.
5. Register the exact redirect URI shown on `/users/admin/workday.php`:
   `{APP_URL}/users/admin/workday.php`.
6. As a workspace admin, open Integrations → Workday and select **Connect
   Workday**. Production requires `APP_ENV=production` and an HTTPS `APP_URL`.

The OAuth endpoints are derived from the tenant service host and tenant:
`https://{WORKDAY_HOST}/ccx/oauth2/{WORKDAY_TENANT}/authorize` and `/token`.
The host is validated to be a Workday service domain (`workday.com` or
`myworkday.com`). Tokens are encrypted at rest
with the application's `APP_KEY`; production must set and securely back up that
key.

The connection page only records a successful OAuth grant. It does not claim
that a Workday API is enabled or that worker data is synchronized. A future
worker sync must be designed around the tenant's approved API scopes, mapped
fields, consent, retention, and deletion rules.

Disconnect removes local tokens. Revoke the Simplr OAuth client in the tenant's
Workday configuration to end provider-side access. No real Workday credentials
or provider calls are part of local tests; tests use synthetic CLI transports.
