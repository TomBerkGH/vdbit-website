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
