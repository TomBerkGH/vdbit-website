<?php

declare(strict_types=1);

require __DIR__ . '/includes/config.php';

function redirectToContact(string $status): never
{
    header('Location: ./?contact=' . $status . '#contact', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ./#contact', true, 303);
    exit;
}

// Spambot heeft het verborgen veld ingevuld: doe alsof het gelukt is.
if (trim($_POST['website'] ?? '') !== '') {
    redirectToContact('ok');
}

$field = static fn (string $name, int $max): string => mb_substr(trim((string) ($_POST[$name] ?? '')), 0, $max);

$name    = $field('naam', 100);
$company = $field('bedrijf', 100);
$email   = $field('email', 150);
$phone   = $field('telefoon', 30);
$message = $field('bericht', 5000);

$emailIsValid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;

if ($name === '' || $message === '' || !$emailIsValid) {
    redirectToContact('fout');
}

// Regeleinden uit velden die in mailheaders of het onderwerp terechtkomen voorkomen header-injectie.
$name = str_replace(["\r", "\n"], ' ', $name);

$body = "Nieuw bericht via " . SITE['url'] . "\n\n"
    . "Naam:     {$name}\n"
    . "Bedrijf:  " . ($company !== '' ? $company : '-') . "\n"
    . "E-mail:   {$email}\n"
    . "Telefoon: " . ($phone !== '' ? $phone : '-') . "\n\n"
    . "Bericht:\n{$message}\n";

$headers = [
    'From'         => SITE['name'] . ' website <' . SITE['mail_from'] . '>',
    'Reply-To'     => $email,
    'Content-Type' => 'text/plain; charset=UTF-8',
];

$sent = mail(
    SITE['email'],
    mb_encode_mimeheader('Contactformulier: ' . $name, 'UTF-8'),
    $body,
    $headers,
    '-f' . SITE['mail_from'],
);

if ($sent) {
    track_event('contact');
}

redirectToContact($sent ? 'ok' : 'fout');
