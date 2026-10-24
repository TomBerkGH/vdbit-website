# vdb-it.nl

Website van VDB-IT. Gewone PHP, geen framework, geen build-stap.
Vereist PHP 8.1+ met de extensies `pdo_sqlite` en `mbstring` (standaard op vrijwel elke hosting).

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
| `includes/mailer.php`   | Controleert en mailt contactberichten (formulier én MCP)   |
| `includes/config.php`   | Contactgegevens, KvK-nummer en afzenderadres               |
| `includes/header.php`   | `<head>`, logo en menu                                     |
| `includes/footer.php`   | Footer                                                     |
| `includes/analytics.php`| Bezoekersstatistieken in SQLite (`data/analytics.sqlite`)  |
| `stats/index.php`       | Statistiekenpagina op `/stats`, met wachtwoord beveiligd   |
| `includes/content.php`  | Profiel, diensten, werkwijze en cases (site én MCP)        |
| `mcp/index.php`         | MCP-server op `/mcp` (lezen en bericht sturen)             |
| `includes/mcp-tools.php`| De tools van de MCP-server (server én uitlegpagina)        |
| `mcp-koppeling.php`     | Uitlegpagina: hoe koppel je een AI-client aan de MCP       |
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
| `STATS_PASSWORD_HASH` | hash van je wachtwoord voor `/stats` (zie hieronder) |

Let op: staat er op de server nog een oude `index.html`, verwijder die dan. Anders krijgt die voorrang op `index.php`.

## MCP-server

`mcp/index.php` is een MCP-server op `https://www.vdb-it.nl/mcp` (Streamable HTTP, zonder sessies) met de leestools
`get_profile`, `list_services`, `list_cases`, `get_work_approach` en `get_contact_info`.
De gegevens komen uit `includes/content.php`. Aanroepen verschijnen op `/stats`.

Met `send_contact_request` kan een AI-assistent een bericht sturen. Dat gaat via dezelfde code als
het contactformulier (`includes/mailer.php`), altijd naar het adres uit `config.php`, met als onderwerp
"MCP-bericht: [naam]".

Limieten tegen spam (`MAIL_*` in `includes/mailer.php`), per netwerk (ingekort IP-adres):

- Formulier: 5 berichten per uur en 10 per dag. Geen totale daglimiet, zodat één misbruiker het
  formulier niet voor iedereen kan blokkeren. Werkt de database niet, dan wordt er toch verstuurd.
- MCP-tool: 10 berichten per uur, en voor alle bezoekers samen 30 per dag.
- Inloggen op `/stats`: 5 pogingen per uur (`STATS_LOGIN_PER_CLIENT_PER_HOUR`), geen totale limiet.
- MCP-batches: maximaal 10 berichten per verzoek.

Testen:

```bash
claude mcp add --transport http vdb-it https://www.vdb-it.nl/mcp
```

## Statistieken

Bezoeken worden zonder cookies en zonder volledig IP-adres (pseudoniem, per dag) bijgehouden in `data/analytics.sqlite`
(staat niet in git en wordt niet overschreven bij een deploy). Bekijk ze op `/stats`.

Wachtwoord instellen:

```bash
php -r "echo password_hash('jouw-wachtwoord', PASSWORD_DEFAULT);"
```

- **Online:** zet de uitkomst als secret `STATS_PASSWORD_HASH`; de deploy maakt er `includes/secrets.php` van.
- **Lokaal:** maak `includes/secrets.php` aan met `<?php return ['stats_password_hash' => '<de hash>'];`

Na inloggen krijgt je browser een cookie waardoor je eigen bezoeken niet meetellen.
