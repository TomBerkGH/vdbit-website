# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

The website of VDB-IT (www.vdb-it.nl): plain PHP 8.5 on LiteSpeed/Apache, with one stylesheet (`assets/css/style.css`) and one vanilla JS file (`assets/js/main.js`). No framework, no Composer, no npm, no build step, no tests, no linter. Site text and code comments are in Dutch. `README.md` (Dutch) documents setup, secrets and the spam limits.

## Commands

```bash
php -S localhost:8000                            # or Laravel Herd: https://vdbit-website.test
git ls-files -z '*.php' | xargs -0 php -l > /dev/null   # the same syntax check the deploy runs
```

Test the MCP server locally (works on `/mcp` and `/mcp/` with the built-in server):

```bash
curl -s -X POST -H 'Content-Type: application/json' -d '{"jsonrpc":"2.0","id":1,"method":"tools/list"}' http://localhost:8000/mcp
```

`.htaccess` (the `/mcp` rewrite, security and cache headers, blocking dotfiles and `*.sqlite`) only applies on the live server; Herd (nginx) and `php -S` ignore it. `/stats` needs `includes/secrets.php` locally: `<?php return ['stats_password_hash' => '<password_hash() output>'];`.

## Deployment

Every push to `main` deploys straight to production via `.github/workflows/deploy.yml`: syntax check, write `includes/secrets.php` from the `STATS_PASSWORD_HASH` secret, then FTPS-upload the tracked files with `SamKirkland/FTP-Deploy-Action`. Files that are not in git (`data/analytics.sqlite`) are never touched on the server, and anything not excluded in the workflow ends up publicly reachable. This workflow is kept identical to the ones in the simons-website and flex-website repos; change them together.

## Architecture

Every entry point (`index.php`, `contact.php`, `privacy.php`, `mcp-koppeling.php`, `mcp/index.php`, `stats/index.php`) first requires `includes/config.php`. That file defines the `SITE` constant (contact details, mail sender), returns a 404 for any `PATH_INFO`, loads `includes/analytics.php` and provides the helpers `e()` (HTML escaping), `asset()` and `stats_password_hash()`. Link CSS/JS through `asset()`: it appends `?v=<filemtime>`, and `.htaccess` caches those files as immutable for a year.

Pages set `$pageTitle` and `$pageDescription` (optionally `$isHome`, `$noIndex`, `$trackPageview`), then include `includes/header.php` and `includes/footer.php`. The header records the pageview. Menu links are anchors on the homepage.

Shared sources, so the site and the MCP server never drift apart:

- `includes/content.php` returns the profile, services, steps and cases. The homepage renders it, and the MCP read tools return it.
- `includes/mcp-tools.php` defines the tools for both the server (`mcp/index.php`) and the explanation page (`mcp-koppeling.php`). `summary` (Dutch, for the page) and `handler` are stripped from `tools/list`. Tools without `inputSchema`/`annotations` get "no input" and read-only defaults. A handler throws `McpToolError` to return a tool result with `isError` instead of a protocol error. `MCP_PROTOCOL_VERSIONS` lives here too.
- `includes/mailer.php` validates and sanitises contact messages (`contact_message_from()`, which also strips newlines to prevent header injection) and sends them (`send_contact_mail()`, always to `SITE['email']`). The contact form and the `send_contact_request` MCP tool both use it. The `MAIL_*` rate limits are defined here.

`mcp/index.php` is a stateless Streamable HTTP JSON-RPC endpoint: no sessions, POST only, JSON only, batches of at most 10 messages, 100 KB body limit. PHP warnings are suppressed so they never corrupt the JSON response.

`includes/analytics.php` holds the cookieless statistics and the rate limiter, both in SQLite (`data/analytics.sqlite`, schema created on first use):

- Visitors are a hash of the truncated IP network (/24 or /48), the user agent and a daily salt that is deleted the next day. The recorded path comes from `SCRIPT_NAME`, not the request URI. `privacy.php` promises exactly this, so keep the two in sync.
- Tracking failures are swallowed: statistics must never break a page.
- `rate_limit_allows()` returns `true`, `false` or `null` (database unavailable). The contact form still sends on `null`; the MCP tool and the `/stats` login refuse. Per-client limits only, with an optional global daily cap that is used only where one abuser blocking everyone is acceptable (the MCP demo).

`stats/index.php` starts a session only on login or when a session cookie already exists, scopes cookies to `/stats/`, and after login sets a cookie that excludes the owner's own visits from the statistics.

New public pages also go into `sitemap.xml`; it is maintained by hand.
