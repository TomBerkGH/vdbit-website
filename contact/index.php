<?php

declare(strict_types=1);

// Waarschuwingen (bijv. van mail()) mogen de redirect-headers niet breken; ze gaan naar het errorlog.
ini_set('display_errors', '0');

require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/mailer.php';

function redirectToContact(string $status): never
{
    // Het anker wijst naar de melding zelf (die focus kan krijgen), niet naar de sectie.
    header('Location: /?contact=' . $status . '#formulier-melding', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /#contact', true, 303);
    exit;
}

// Spambot heeft het verborgen veld ingevuld: doe alsof het gelukt is.
$honeypot = $_POST['website'] ?? '';
if (!is_string($honeypot) || trim($honeypot) !== '') {
    redirectToContact('ok');
}

$message = contact_message_from([
    'name'    => $_POST['naam'] ?? '',
    'company' => $_POST['bedrijf'] ?? '',
    'email'   => $_POST['email'] ?? '',
    'phone'   => $_POST['telefoon'] ?? '',
    'message' => $_POST['bericht'] ?? '',
]);

if ($message === null) {
    redirectToContact('fout');
}

// Bij een databasefout (null) toch versturen: het contactformulier is te belangrijk om dan dicht te zetten.
if (rate_limit_allows('mail:form', MAIL_FORM_PER_CLIENT_PER_HOUR, MAIL_FORM_PER_CLIENT_PER_DAY) === false) {
    redirectToContact('limiet');
}

$sent = send_contact_mail($message, 'Contactformulier: ', SITE['url']);

redirectToContact($sent ? 'ok' : 'fout');
