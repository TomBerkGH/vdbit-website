# vdb-it.nl

Website van VDB-IT. Gewone PHP, geen framework, geen build-stap.

## Lokaal draaien

Via Laravel Herd: <https://vdbit-website.test>

Of zonder Herd:

```bash
php -S localhost:8000
```

## Structuur

| Bestand                 | Wat                                                        |
|-------------------------|------------------------------------------------------------|
| `index.php`             | Homepage; diensten en werkwijze staan als lijstjes bovenin |
| `privacy.php`           | Privacyverklaring                                          |
| `contact.php`           | Verwerkt het contactformulier en mailt het bericht         |
| `includes/config.php`   | Contactgegevens, KvK-nummer en afzenderadres               |
| `includes/header.php`   | `<head>`, logo en menu                                     |
| `includes/footer.php`   | Footer                                                     |
| `includes/analytics.php`| Bezoekersstatistieken in SQLite (`data/analytics.sqlite`)  |
| `stats.php`             | Statistiekenpagina, met wachtwoord beveiligd               |
| `includes/content.php`  | Profiel, diensten, werkwijze en cases (site én MCP)        |
| `mcp.php`               | MCP-server (alleen-lezen) voor AI-assistenten              |
| `assets/css/style.css`  | Alle styling; kleuren per thema bovenin                    |
| `assets/js/main.js`     | Thema-knop, mobiel menu, animaties                         |

## Online zetten

Elke push naar `main` zet de site via FTPS op de hosting (`.github/workflows/deploy.yml`).
Stel eenmalig deze secrets in onder *Settings → Secrets and variables → Actions*:

| Secret           | Voorbeeld                         |
|------------------|-----------------------------------|
| `FTP_SERVER`     | `ftp.vdb-it.nl`                   |
| `FTP_USERNAME`   | je FTP-gebruikersnaam             |
| `FTP_PASSWORD`   | je FTP-wachtwoord                 |
| `FTP_SERVER_DIR` | `public_html/` (met slash op het eind) |
| `STATS_PASSWORD_HASH` | hash van je wachtwoord voor `stats.php` (zie hieronder) |

Let op: staat er op de server nog een oude `index.html`, verwijder die dan. Anders krijgt die voorrang op `index.php`.

## MCP-server

`mcp.php` is een MCP-server (Streamable HTTP, zonder sessies) met alleen-lezen tools:
`get_profile`, `list_services`, `list_cases`, `get_work_approach` en `get_contact_info`.
De gegevens komen uit `includes/content.php`. Aanroepen verschijnen in `stats.php`.

Testen:

```bash
claude mcp add --transport http vdb-it https://www.vdb-it.nl/mcp.php
```

## Statistieken

Bezoeken worden zonder cookies en zonder IP-adres bijgehouden in `data/analytics.sqlite`
(staat niet in git en wordt niet overschreven bij een deploy). Bekijk ze op `/stats.php`.

Wachtwoord instellen:

```bash
php -r "echo password_hash('jouw-wachtwoord', PASSWORD_DEFAULT);"
```

- **Online:** zet de uitkomst als secret `STATS_PASSWORD_HASH`; de deploy maakt er `includes/secrets.php` van.
- **Lokaal:** maak `includes/secrets.php` aan met `<?php return ['stats_password_hash' => '<de hash>'];`

Na inloggen krijgt je browser een cookie waardoor je eigen bezoeken niet meetellen.
