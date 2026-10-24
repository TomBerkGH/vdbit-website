<?php

declare(strict_types=1);

require __DIR__ . '/../includes/config.php';

const STATS_LOGIN_PER_CLIENT_PER_HOUR = 5;
const STATS_LOGIN_TOTAL_PER_DAY = 30;

$secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

// Alleen een sessie (en dus een cookie) bij inloggen of als je al ingelogd bent; gewone bezoekers krijgen niets.
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_COOKIE[session_name()])) {
    session_start([
        'cookie_path'     => '/stats/',
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
        'cookie_secure'   => $secure,
    ]);
}

$passwordHash = stats_password_hash();
$loginError = null;

if (isset($_GET['uitloggen'])) {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        session_destroy();
        setcookie(session_name(), '', ['expires' => 1, 'path' => '/stats/', 'secure' => $secure, 'httponly' => true, 'samesite' => 'Strict']);
    }

    header('Location: /stats/', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $passwordHash !== '') {
    $password = $_POST['wachtwoord'] ?? '';

    if (!rate_limit_allows('login:stats', STATS_LOGIN_PER_CLIENT_PER_HOUR, STATS_LOGIN_TOTAL_PER_DAY)) {
        $loginError = 'Te veel inlogpogingen. Probeer het over een uur opnieuw.';
    } elseif (is_string($password) && password_verify($password, $passwordHash)) {
        session_regenerate_id(true);
        $_SESSION['stats'] = true;

        // Je eigen bezoeken voortaan niet meetellen in de statistieken.
        setcookie(ANALYTICS_NOTRACK_COOKIE, '1', [
            'expires'  => time() + 60 * 60 * 24 * 365,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => $secure,
        ]);

        header('Location: /stats/', true, 303);
        exit;
    } else {
        $loginError = 'Onjuist wachtwoord.';
    }
}

$loggedIn = ($_SESSION['stats'] ?? false) === true;
$dbError = false;

$periods = [7 => '7 dagen', 30 => '30 dagen', 90 => '90 dagen', 365 => '12 maanden'];
$days = (int) ($_GET['periode'] ?? 30);
$days = array_key_exists($days, $periods) ? $days : 30;

if ($loggedIn) {
    try {
        $db = analytics_db();
    } catch (Throwable $exception) {
        error_log('stats: ' . $exception->getMessage());
        $dbError = true;
        $loggedIn = false;
    }
}

if ($loggedIn) {
    $since = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));

    $scalar = static function (string $sql) use ($db, $since): int {
        $statement = $db->prepare($sql);
        $statement->execute([$since]);

        return (int) $statement->fetchColumn();
    };

    $list = static function (string $column, string $where = '') use ($db, $since): array {
        $statement = $db->prepare("SELECT {$column} AS label, COUNT(*) AS total FROM pageviews
            WHERE day >= ? {$where} GROUP BY {$column} ORDER BY total DESC LIMIT 10");
        $statement->execute([$since]);

        return $statement->fetchAll();
    };

    $pageviews = $scalar('SELECT COUNT(*) FROM pageviews WHERE day >= ?');
    $visitors = $scalar('SELECT COUNT(*) FROM (SELECT DISTINCT day, visitor FROM pageviews WHERE day >= ?)');
    $contacts = $scalar("SELECT COUNT(*) FROM events WHERE name = 'contact' AND day >= ?");

    $perDayStatement = $db->prepare('SELECT day, COUNT(DISTINCT visitor) AS visitors FROM pageviews WHERE day >= ? GROUP BY day');
    $perDayStatement->execute([$since]);
    $perDayCounts = array_column($perDayStatement->fetchAll(), 'visitors', 'day');

    $perDay = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $day = date('Y-m-d', strtotime("-{$i} days"));
        $perDay[$day] = (int) ($perDayCounts[$day] ?? 0);
    }
    $maxPerDay = max(1, ...array_values($perDay));

    $topPages = $list('path');
    $topReferrers = $list('referrer', 'AND referrer IS NOT NULL');
    $devices = $list('device');
    $browsers = $list('browser');

    $mcpStatement = $db->prepare("SELECT substr(name, 5) AS label, COUNT(*) AS total FROM events
        WHERE name LIKE 'mcp:%' AND day >= ? GROUP BY name ORDER BY total DESC");
    $mcpStatement->execute([$since]);
    $mcpCalls = $mcpStatement->fetchAll();
}

$months = ['jan', 'feb', 'mrt', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'];
$formatDay = static fn (string $day): string => (int) substr($day, 8, 2) . ' ' . $months[(int) substr($day, 5, 2) - 1];

$pageTitle = 'Statistieken | VDB-IT';
$pageDescription = 'Bezoekersstatistieken van vdb-it.nl';
$noIndex = true;
$trackPageview = false;

require __DIR__ . '/../includes/header.php';
?>

<section class="section page stats">
    <div class="container">
        <span class="label">Statistieken</span>

        <?php if ($dbError): ?>
            <h1>Statistieken niet beschikbaar</h1>
            <p class="muted">De statistiekendatabase kon niet worden geopend. Controleer of PHP de extensie <code>pdo_sqlite</code> heeft en of de map <code>data/</code> schrijfbaar is.</p>
        <?php elseif (!$loggedIn): ?>
            <h1>Inloggen</h1>
            <?php if ($passwordHash === ''): ?>
                <p class="muted">
                    Er is nog geen wachtwoord ingesteld. Maak een hash met
                    <code>php -r "echo password_hash('jouw-wachtwoord', PASSWORD_DEFAULT);"</code>
                    en zet die in <code>includes/secrets.php</code> (zie de README).
                </p>
            <?php else: ?>
                <form class="card stats-login" method="post">
                    <?php if ($loginError !== null): ?>
                        <p class="form-message form-error" role="alert"><?= e($loginError) ?></p>
                    <?php endif ?>
                    <label>Wachtwoord<input type="password" name="wachtwoord" required autofocus autocomplete="current-password"></label>
                    <button class="btn" type="submit">Inloggen</button>
                </form>
            <?php endif ?>
        <?php else: ?>
            <div class="stats-head">
                <h1>Bezoekers</h1>
                <nav class="stats-periods" aria-label="Periode">
                    <?php foreach ($periods as $value => $label): ?>
                        <a href="?periode=<?= $value ?>" <?= $value === $days ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
                    <?php endforeach ?>
                    <a href="?uitloggen=1" class="stats-logout">Uitloggen</a>
                </nav>
            </div>

            <div class="stats-totals">
                <div class="card"><span>Bezoekers <small>(per dag geteld)</small></span><strong><?= number_format($visitors, 0, ',', '.') ?></strong></div>
                <div class="card"><span>Paginaweergaven</span><strong><?= number_format($pageviews, 0, ',', '.') ?></strong></div>
                <div class="card"><span>Berichten <small>(formulier + MCP)</small></span><strong><?= number_format($contacts, 0, ',', '.') ?></strong></div>
            </div>

            <div class="card stats-chart-card">
                <h2>Bezoekers per dag</h2>
                <div class="stats-chart" role="img" aria-label="Staafdiagram van het aantal bezoekers per dag">
                    <?php foreach ($perDay as $day => $count): ?>
                        <div class="stats-bar" style="--h: <?= round($count / $maxPerDay * 100, 1) ?>%" title="<?= e($formatDay($day)) ?>: <?= $count ?>"></div>
                    <?php endforeach ?>
                </div>
                <div class="stats-axis">
                    <span><?= e($formatDay(array_key_first($perDay))) ?></span>
                    <span>max <?= $maxPerDay ?> per dag</span>
                    <span><?= e($formatDay(array_key_last($perDay))) ?></span>
                </div>
            </div>

            <div class="stats-lists">
                <?php foreach (['Pagina\'s' => $topPages, 'Verwijzers' => $topReferrers, 'Apparaten' => $devices, 'Browsers' => $browsers, 'MCP-aanroepen' => $mcpCalls] as $title => $rows): ?>
                    <div class="card">
                        <h2><?= e($title) ?></h2>
                        <?php if ($rows === []): ?>
                            <p class="muted">Nog geen gegevens.</p>
                        <?php else: ?>
                            <ul class="stats-list">
                                <?php foreach ($rows as $row): ?>
                                    <li style="--w: <?= round($row['total'] / $rows[0]['total'] * 100, 1) ?>%">
                                        <span><?= e((string) $row['label']) ?></span><strong><?= $row['total'] ?></strong>
                                    </li>
                                <?php endforeach ?>
                            </ul>
                        <?php endif ?>
                    </div>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
