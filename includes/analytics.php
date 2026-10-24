<?php

declare(strict_types=1);

/**
 * Eenvoudige, privacyvriendelijke bezoekersstatistieken in SQLite, plus rate limiting.
 * Geen cookies voor bezoekers en geen volledige IP-adressen: bezoekers worden per dag geteld met
 * een pseudonieme code (hash van ingekort IP-adres en browser) waarvan het zout elke dag wisselt
 * en daarna wordt gewist.
 */

const ANALYTICS_DB = __DIR__ . '/../data/analytics.sqlite';
const ANALYTICS_RETENTION_DAYS = 400;
const ANALYTICS_NOTRACK_COOKIE = 'vdbit_notrack';

function analytics_db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        if (!is_dir(dirname(ANALYTICS_DB))) {
            mkdir(dirname(ANALYTICS_DB), 0775, true);
        }

        $pdo = new PDO('sqlite:' . ANALYTICS_DB, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA busy_timeout = 2000');
        // Verwijderde gegevens (zoals oude zouten) echt overschrijven in plaats van alleen vrijgeven.
        $pdo->exec('PRAGMA secure_delete = ON');
        $pdo->exec('CREATE TABLE IF NOT EXISTS pageviews (
            id         INTEGER PRIMARY KEY,
            created_at INTEGER NOT NULL,
            day        TEXT    NOT NULL,
            path       TEXT    NOT NULL,
            referrer   TEXT,
            device     TEXT    NOT NULL,
            browser    TEXT    NOT NULL,
            visitor    TEXT    NOT NULL
        )');
        $pdo->exec('CREATE INDEX IF NOT EXISTS pageviews_day ON pageviews (day)');
        $pdo->exec('CREATE TABLE IF NOT EXISTS events (
            id         INTEGER PRIMARY KEY,
            created_at INTEGER NOT NULL,
            day        TEXT    NOT NULL,
            name       TEXT    NOT NULL
        )');
        $pdo->exec('CREATE TABLE IF NOT EXISTS salts (day TEXT PRIMARY KEY, salt TEXT NOT NULL)');
        $pdo->exec('CREATE TABLE IF NOT EXISTS rate_limits (
            id         INTEGER PRIMARY KEY,
            action     TEXT    NOT NULL,
            client     TEXT    NOT NULL,
            created_at INTEGER NOT NULL
        )');
    }

    return $pdo;
}

function track_pageview(): void
{
    try {
        if (!analytics_should_track()) {
            return;
        }

        $day = date('Y-m-d');
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        analytics_db()
            ->prepare('INSERT INTO pageviews (created_at, day, path, referrer, device, browser, visitor) VALUES (?, ?, ?, ?, ?, ?, ?)')
            ->execute([
                time(),
                $day,
                analytics_path(),
                analytics_referrer(),
                analytics_device($userAgent),
                analytics_browser($userAgent),
                analytics_visitor($day, $userAgent),
            ]);

        if (random_int(1, 100) === 1) {
            analytics_cleanup();
        }
    } catch (Throwable) {
        // Statistieken mogen de site nooit breken.
    }
}

function track_event(string $name): void
{
    try {
        analytics_db()
            ->prepare('INSERT INTO events (created_at, day, name) VALUES (?, ?, ?)')
            ->execute([time(), date('Y-m-d'), $name]);
    } catch (Throwable) {
    }
}

function analytics_should_track(): bool
{
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $purpose = $_SERVER['HTTP_SEC_PURPOSE'] ?? $_SERVER['HTTP_PURPOSE'] ?? '';

    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET'
        && $userAgent !== ''
        && !preg_match('/bot|crawl|spider|slurp|curl|wget|python|headless|lighthouse|preview|monitor/i', $userAgent)
        && !str_contains($purpose, 'prefetch')
        && !isset($_COOKIE[ANALYTICS_NOTRACK_COOKIE]);
}

/** Het PHP-bestand dat de pagina maakt, niet de opgevraagde URL: zo kan niemand verzonnen adressen in de statistieken zetten. */
function analytics_path(): string
{
    $path = preg_replace('#/index\.php$#', '/', $_SERVER['SCRIPT_NAME'] ?? '/');

    return mb_substr($path !== '' ? $path : '/', 0, 200);
}

/** Alleen de domeinnaam van een externe verwijzer, zonder pad of parameters. */
function analytics_referrer(): ?string
{
    $host = parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_HOST);

    if (!is_string($host) || $host === '') {
        return null;
    }

    $host = preg_replace('/^www\./', '', strtolower($host));
    $ownHost = preg_replace('/^www\./', '', strtolower($_SERVER['HTTP_HOST'] ?? ''));

    return $host === $ownHost ? null : mb_substr($host, 0, 100);
}

function analytics_device(string $userAgent): string
{
    return match (true) {
        (bool) preg_match('/iPad|Tablet/i', $userAgent),
        str_contains($userAgent, 'Android') && !str_contains($userAgent, 'Mobi') => 'Tablet',
        (bool) preg_match('/Mobi|Android/i', $userAgent) => 'Mobiel',
        default                                          => 'Desktop',
    };
}

function analytics_browser(string $userAgent): string
{
    return match (true) {
        str_contains($userAgent, 'Edg/')     => 'Edge',
        str_contains($userAgent, 'OPR/')     => 'Opera',
        str_contains($userAgent, 'Firefox/') => 'Firefox',
        str_contains($userAgent, 'Chrome/')  => 'Chrome',
        str_contains($userAgent, 'Safari/')  => 'Safari',
        default                              => 'Overig',
    };
}

/** Pseudonieme bezoekerscode die alleen binnen één dag gelijk blijft. */
function analytics_visitor(string $day, string $userAgent): string
{
    return substr(hash('sha256', analytics_salt($day) . analytics_network() . $userAgent), 0, 16);
}

/**
 * Alleen het netwerkdeel van het IP-adres (/24 bij IPv4, /48 bij IPv6). Het volledige adres wordt
 * nooit gebruikt of opgeslagen, zodat een code niet naar één specifiek IP-adres te herleiden is.
 */
function analytics_network(): string
{
    $packed = @inet_pton($_SERVER['REMOTE_ADDR'] ?? '');

    if ($packed === false) {
        return '';
    }

    // IPv4-adres in IPv6-vorm (::ffff:a.b.c.d): behandel het als gewoon IPv4, anders lijkt iedereen hetzelfde netwerk.
    if (strlen($packed) === 16 && str_starts_with($packed, str_repeat("\0", 10) . "\xff\xff")) {
        $packed = substr($packed, 12);
    }

    return bin2hex(substr($packed, 0, strlen($packed) === 4 ? 3 : 6));
}

function analytics_salt(string $day): string
{
    $db = analytics_db();
    $select = $db->prepare('SELECT salt FROM salts WHERE day = ?');
    $select->execute([$day]);
    $salt = $select->fetchColumn();

    if ($salt !== false) {
        return (string) $salt;
    }

    // Nieuwe dag: nieuw zout, en dat van eerdere dagen weggooien zodat bezoeken niet over dagen te koppelen zijn.
    $db->prepare('INSERT OR IGNORE INTO salts (day, salt) VALUES (?, ?)')->execute([$day, bin2hex(random_bytes(16))]);
    $db->prepare('DELETE FROM salts WHERE day < ?')->execute([$day]);
    $select->execute([$day]);

    return (string) $select->fetchColumn();
}

/**
 * Registreert een poging en zegt of die binnen de limieten valt.
 *
 * Limieten gelden per netwerk (zie analytics_network()), per uur en optioneel per dag. Een totale daglimiet
 * voor alle bezoekers samen is optioneel en alleen bedoeld voor acties waarbij het niet erg is dat één
 * misbruiker ze voor iedereen kan blokkeren (zoals de MCP-demo); het contactformulier en de login hebben er
 * daarom geen. Let op: de bezoekerscode wisselt om middernacht mee met het dagelijkse zout, dus de tellers
 * per netwerk beginnen dan opnieuw.
 *
 * @return bool|null true = toegestaan, false = limiet bereikt, null = database niet beschikbaar
 */
function rate_limit_allows(string $action, int $perClientPerHour, ?int $perClientPerDay = null, ?int $totalPerDay = null): ?bool
{
    $db = null;

    try {
        $db = analytics_db();
        $now = time();
        $client = substr(hash('sha256', analytics_salt(date('Y-m-d')) . analytics_network()), 0, 16);

        // Tellen en registreren in één transactie, zodat gelijktijdige verzoeken de limiet niet omzeilen.
        $db->exec('BEGIN IMMEDIATE');
        $db->prepare('DELETE FROM rate_limits WHERE created_at < ?')->execute([$now - 86400]);

        $count = static function (string $where, array $params) use ($db): int {
            $statement = $db->prepare("SELECT COUNT(*) FROM rate_limits WHERE action = ? AND {$where}");
            $statement->execute($params);

            return (int) $statement->fetchColumn();
        };

        $allowed = $count('client = ? AND created_at >= ?', [$action, $client, $now - 3600]) < $perClientPerHour
            && ($perClientPerDay === null || $count('client = ? AND created_at >= ?', [$action, $client, $now - 86400]) < $perClientPerDay)
            && ($totalPerDay === null || $count('created_at >= ?', [$action, $now - 86400]) < $totalPerDay);

        if ($allowed) {
            $db->prepare('INSERT INTO rate_limits (action, client, created_at) VALUES (?, ?, ?)')->execute([$action, $client, $now]);
        }

        $db->exec('COMMIT');

        return $allowed;
    } catch (Throwable $exception) {
        try {
            $db?->exec('ROLLBACK');
        } catch (Throwable) {
        }

        error_log('rate_limit_allows: ' . $exception->getMessage());

        return null;
    }
}

function analytics_cleanup(): void
{
    $db = analytics_db();
    $cutoff = date('Y-m-d', strtotime('-' . ANALYTICS_RETENTION_DAYS . ' days'));
    $db->prepare('DELETE FROM pageviews WHERE day < ?')->execute([$cutoff]);
    $db->prepare('DELETE FROM events WHERE day < ?')->execute([$cutoff]);
    $db->prepare('DELETE FROM rate_limits WHERE created_at < ?')->execute([time() - 86400]);
}
