<?php

declare(strict_types=1);

/**
 * Centrale gegevens van de site. Pas hier je contactgegevens aan;
 * ze worden overal (header, footer, contact, privacy) automatisch gebruikt.
 */
const SITE = [
    'name'       => 'VDB-IT',
    'owner'      => 'Tom van den Berk',
    'url'        => 'https://www.vdb-it.nl',
    'email'      => 'tom.berk@vdb-it.nl',
    'phone'      => '+31 6 40883324',
    'phone_href' => '+31640883324',
    'street'     => 'Pater vic. van Heeswijkstraat 1',
    'postcode'   => '5462 CL',
    'city'       => 'Veghel',
    'kvk'        => '', // Vul je KvK-nummer in om het in de footer te tonen.
    'github'     => 'https://github.com/TomBerkGH/vdbit-website',

    // Afzender van de mails die het contactformulier verstuurt.
    // Gebruik een adres op je eigen domein, anders belanden ze in de spam.
    'mail_from'  => 'website@vdb-it.nl',
];

date_default_timezone_set('Europe/Amsterdam');

require __DIR__ . '/analytics.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/** Het GitHub-logo (Octicon "mark-github") als inline SVG. */
function github_icon(string $class = ''): string
{
    return '<svg class="' . e($class) . '" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0 0 16 8c0-4.42-3.58-8-8-8z"/></svg>';
}

/** Wachtwoord-hash voor stats.php, uit het (niet in git opgenomen) bestand includes/secrets.php. */
function stats_password_hash(): string
{
    $file = __DIR__ . '/secrets.php';

    return is_file($file) ? (string) ((require $file)['stats_password_hash'] ?? '') : '';
}

/** Voegt een versie toe aan een asset-URL, zodat browsers na een wijziging de nieuwe versie laden. */
function asset(string $path): string
{
    $file = __DIR__ . '/../' . $path;

    return $path . (is_file($file) ? '?v=' . filemtime($file) : '');
}
