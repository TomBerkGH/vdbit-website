<?php

declare(strict_types=1);

/**
 * Google reCAPTCHA (score-based) voor het contactformulier, net als bij simons-website: onzichtbaar, Google geeft
 * een score van 0 (bot) tot 1 (mens). Zonder geheime sleutel in includes/secrets.php (zoals lokaal) staat het uit.
 * Het script van Google wordt pas geladen als iemand het formulier gaat invullen (assets/js/main.js);
 * privacy/index.php belooft precies dat.
 */

const RECAPTCHA_ACTION = 'contact';
const RECAPTCHA_MIN_SCORE = 0.5;

function recaptcha_enabled(): bool
{
    return secret('recaptcha_secret_key') !== '';
}

/** Laat Google het token uit het formulier controleren. Ook false als Google niet bereikbaar is. */
function recaptcha_passes(mixed $token): bool
{
    if (!is_string($token) || $token === '' || strlen($token) > 4000) {
        return false;
    }

    $context = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query(['secret' => secret('recaptcha_secret_key'), 'response' => $token]),
            'timeout' => 10,
        ],
    ]);

    $response = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
    $result = is_string($response) ? json_decode($response, true) : null;

    return is_array($result)
        && ($result['success'] ?? false) === true
        && ($result['action'] ?? '') === RECAPTCHA_ACTION
        && (float) ($result['score'] ?? 0) >= RECAPTCHA_MIN_SCORE;
}
