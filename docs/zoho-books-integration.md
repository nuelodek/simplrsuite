# Zoho Books connection

The Zoho Books connector supports OAuth authorization, encrypted
access/refresh-token storage, automatic access-token refresh, organization
selection, and read-only verification of the selected organization. It does not
import, export, or modify accounting records.

## Configure the Zoho client

1. Register an OAuth client in the [Zoho API Console](https://api-console.zoho.com/).
2. Register the exact redirect URI shown on `/users/admin/zoho-books.php`:
   `{APP_URL}/users/admin/zoho-books.php`.
3. Set `ZOHO_BOOKS_CLIENT_ID` and `ZOHO_BOOKS_CLIENT_SECRET` in the server
   environment. Never put credentials in source control or expose the secret in
   the workspace UI.
4. Set `ZOHO_BOOKS_REGION` to the data center used by the Zoho account:
   `com`, `eu`, `in`, `com.au`, `jp`, `com.cn`, or `sa`. Nigeria is commonly
   served by `com`, but use the domain where the account actually signs in.
5. As a workspace admin, open Integrations → Zoho Books, authorize access, and
   choose the organization to associate with the workspace. Production
   connections require `APP_ENV=production` and an HTTPS `APP_URL`.

The connector uses the regional Zoho OAuth and API hosts derived from the
allowlisted region. It requests only `ZohoBooks.settings.READ` and requests
offline access so it can refresh the authorization. OAuth state is
session-bound and expires after ten minutes. The selected organization is
verified through Zoho Books' read-only organization endpoint before tokens are
saved. Tokens are encrypted at rest using the application's `APP_KEY`;
production must set and securely back up that key.

## Verify or disconnect

The admin page can re-check the selected organization and refresh expired
access tokens. Zoho Books refresh tokens have no fixed expiry according to the
provider documentation, but can be revoked by the user. Disconnect removes
local tokens and requests Zoho token revocation; if the request cannot be
confirmed, remove Simplr in the Zoho connected-app settings.

The connector does not synchronize accounting entities. No real provider
credentials or network calls are part of local tests; tests use synthetic
CLI-only transport responses.

Provider references:

- [Zoho Books OAuth](https://www.zoho.com/books/api/v3/oauth/)
- [Zoho Books Organizations API](https://www.zoho.com/books/api/v3/organizations/)
