# Local Development Strategy

To properly support the multi-tenant architecture locally using Laravel Herd, the application defines a specific central domain and relies on subdomains for isolated tenant environments.

## Authorized URL Strategy

The local project strictly adheres to the following domain structures:

- **Control Plane (Entry Point):** `http://app.sitios-urbanos.test`
  - Used for authenticated login, user profile access, and community discovery.
- **Tenant Runtime:** `http://{community_slug}.app.sitios-urbanos.test`
  - Isolated contexts configured dynamically by your active community structure. All operations related to units, residents, and other community tools happen here.

## How to use

1. Ensure your `.env` contains the following block:
   ```env
   APP_URL=http://app.sitios-urbanos.test
   CENTRAL_DOMAIN=app.sitios-urbanos.test
   SESSION_DOMAIN=.sitios-urbanos.test
   ```
2. Navigate to `http://app.sitios-urbanos.test` to login to the system.
3. Access individual community contexts by navigating directly to `http://{demo}.app.sitios-urbanos.test/units` where `{demo}` implies your authenticated community alias.

> [!NOTE]
> `http://sitios-urbanos.test` is not functionally active by design since the central tenant isolation bounds occur purely through the `app.` prefix.

Your session securely persists across domains because the `SESSION_DOMAIN` accepts cookies starting at the highest bounded level (`.sitios-urbanos.test`).
