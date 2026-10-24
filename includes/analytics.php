<?php

declare(strict_types=1);

/**
 * Eenvoudige, privacyvriendelijke bezoekersstatistieken in SQLite.
 * Geen cookies voor bezoekers en geen IP-adressen: unieke bezoekers worden per dag geteld
 * met een hash waarvan het zout elke dag wisselt en daarna wordt weggegooid.
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

function analytics_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $path = preg_replace('#/index\.php$#', '/', $path);

    return mb_substr($path, 0, 200);
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
        (bool) preg_match('/iPad|Tablet/i', $userAgent)  => 'Tablet',
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

/** Anonieme bezoekerscode die alleen binnen één dag gelijk blijft. */
function analytics_visitor(string $day, string $userAgent): string
{
    $db = analytics_db();
    $insert = $db->prepare('INSERT OR IGNORE INTO salts (day, salt) VALUES (?, ?)');
    $insert->execute([$day, bin2hex(random_bytes(16))]);

    // Nieuwe dag: het zout van gisteren weggooien, zodat bezoeken niet over dagen te koppelen zijn.
    if ($insert->rowCount() > 0) {
        $db->prepare('DELETE FROM salts WHERE day < ?')->execute([$day]);
    }

    $select = $db->prepare('SELECT salt FROM salts WHERE day = ?');
    $select->execute([$day]);

    return substr(hash('sha256', $select->fetchColumn() . ($_SERVER['REMOTE_ADDR'] ?? '') . $userAgent), 0, 16);
}

function analytics_cleanup(): void
{
    $db = analytics_db();
    $cutoff = date('Y-m-d', strtotime('-' . ANALYTICS_RETENTION_DAYS . ' days'));
    $db->prepare('DELETE FROM pageviews WHERE day < ?')->execute([$cutoff]);
    $db->prepare('DELETE FROM events WHERE day < ?')->execute([$cutoff]);
}
