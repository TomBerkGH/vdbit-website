<?php

declare(strict_types=1);

/**
 * Contactberichten opschonen, controleren en mailen.
 * Gebruikt door het contactformulier (contact/index.php) en de MCP-tool send_contact_request.
 */

// Limieten tegen spam, per netwerk (zie rate_limit_allows()). Het formulier heeft bewust geen totale
// daglimiet, zodat één misbruiker het niet voor iedereen kan blokkeren. De MCP-demo heeft die wel.
const MAIL_FORM_PER_CLIENT_PER_HOUR = 5;
const MAIL_FORM_PER_CLIENT_PER_DAY = 10;
const MAIL_MCP_PER_CLIENT_PER_HOUR = 10;
const MAIL_MCP_TOTAL_PER_DAY = 30;

/**
 * Schoont de velden op en controleert ze.
 *
 * @param array{name?: mixed, company?: mixed, email?: mixed, phone?: mixed, message?: mixed} $input
 * @return array{name: string, company: string, email: string, phone: string, message: string}|null null als iets ontbreekt of ongeldig is
 */
function contact_message_from(array $input): ?array
{
    $field = static fn (string $key, int $max): string => mb_substr(trim(is_scalar($input[$key] ?? null) ? (string) $input[$key] : ''), 0, $max);

    $message = [
        // Regeleinden uit velden die in mailheaders of het onderwerp terechtkomen voorkomen header-injectie.
        'name'    => str_replace(["\r", "\n"], ' ', $field('name', 100)),
        'company' => $field('company', 100),
        'email'   => $field('email', 150),
        'phone'   => $field('phone', 30),
        'message' => $field('message', 5000),
    ];

    if ($message['name'] === '' || $message['message'] === '' || filter_var($message['email'], FILTER_VALIDATE_EMAIL) === false) {
        return null;
    }

    return $message;
}

/** Mailt een (gecontroleerd) contactbericht naar het adres uit de config. */
function send_contact_mail(array $message, string $subjectPrefix, string $via): bool
{
    $body = "Nieuw bericht via {$via}\n\n"
        . "Naam:     {$message['name']}\n"
        . "Bedrijf:  " . ($message['company'] !== '' ? $message['company'] : '-') . "\n"
        . "E-mail:   {$message['email']}\n"
        . "Telefoon: " . ($message['phone'] !== '' ? $message['phone'] : '-') . "\n\n"
        . "Bericht:\n{$message['message']}\n";

    $headers = [
        'From'         => SITE['name'] . ' website <' . SITE['mail_from'] . '>',
        'Reply-To'     => $message['email'],
        'Content-Type' => 'text/plain; charset=UTF-8',
    ];

    $sent = mail(
        SITE['email'],
        mb_encode_mimeheader($subjectPrefix . $message['name'], 'UTF-8'),
        $body,
        $headers,
        '-f' . SITE['mail_from'],
    );

    if ($sent) {
        track_event('contact');
    }

    return $sent;
}
