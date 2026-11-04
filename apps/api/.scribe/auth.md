# Authenticating requests

To authenticate requests, include a **`X-XSRF-TOKEN`** header with the value **`"{XSRF-TOKEN cookie value}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

CourtKu uses a login session cookie, not API tokens (ADR-004). Call <code>GET /sanctum/csrf-cookie</code>, then <code>POST /auth/login</code>; the browser keeps the session cookie, and every request that changes something sends the <code>XSRF-TOKEN</code> cookie back in the <code>X-XSRF-TOKEN</code> header. Owners and the admin also need two-factor login.
