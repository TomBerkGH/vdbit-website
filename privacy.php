<?php

declare(strict_types=1);

require __DIR__ . '/includes/config.php';

$pageTitle = 'Privacyverklaring | VDB-IT';
$pageDescription = 'Hoe VDB-IT omgaat met persoonsgegevens die je via deze website deelt.';

require __DIR__ . '/includes/header.php';
?>

<section class="section page">
    <div class="container prose">
        <span class="label">Privacy</span>
        <h1>Privacyverklaring</h1>
        <p class="muted">Laatst bijgewerkt: oktober 2026</p>

        <p>
            <?= e(SITE['name']) ?>, gevestigd aan <?= e(SITE['street']) ?>, <?= e(SITE['postcode']) ?> <?= e(SITE['city']) ?>,
            is verantwoordelijk voor de verwerking van persoonsgegevens zoals beschreven in deze verklaring.
        </p>

        <h2>Welke gegevens verwerk ik?</h2>
        <p>
            Als je het contactformulier invult of mij mailt, ontvang ik de gegevens die je zelf opgeeft:
            je naam, e-mailadres en bericht, en als je die invult ook je bedrijfsnaam en telefoonnummer.
        </p>

        <h2>Waarom?</h2>
        <p>
            Ik gebruik deze gegevens uitsluitend om op je vraag te reageren en, als je dat wilt, een kennismaking te plannen.
            De grondslag hiervoor is je verzoek om contact (gerechtvaardigd belang). Ik verkoop je gegevens niet en deel ze
            niet met derden, behalve mijn e-mail- en hostingprovider die nodig zijn om berichten te ontvangen.
        </p>

        <h2>Hoe lang bewaar ik ze?</h2>
        <p>
            Ik bewaar je bericht zolang dat nodig is om je vraag af te handelen. Leidt het contact tot een opdracht,
            dan bewaar ik de gegevens zolang de samenwerking duurt en zo lang als de wet voorschrijft.
        </p>

        <h2>Cookies en statistieken</h2>
        <p>
            Deze website plaatst geen cookies bij bezoekers en gebruikt geen externe analytics- of trackingdiensten.
            Lettertypes worden vanaf deze server geladen, niet via externe diensten. Je keuze voor het lichte of donkere
            thema wordt alleen lokaal in je browser opgeslagen.
        </p>
        <p>
            Om te zien hoe de site gebruikt wordt, houd ik op mijn eigen server eenvoudige, anonieme statistieken bij:
            welke pagina bezocht wordt, van welke website je kwam, en welk type apparaat en browser je gebruikt.
            Je IP-adres wordt niet opgeslagen. Om bezoekers per dag te kunnen tellen, wordt een anonieme code gebruikt die
            elke dag verandert, zodat bezoeken niet over meerdere dagen aan elkaar te koppelen zijn. Deze statistieken
            worden na ongeveer 13 maanden verwijderd en niet met anderen gedeeld.
        </p>

        <h2>Je rechten</h2>
        <p>
            Je hebt het recht om je gegevens in te zien, te laten corrigeren of te laten verwijderen. Stuur daarvoor een
            mail naar <a href="mailto:<?= e(SITE['email']) ?>"><?= e(SITE['email']) ?></a>. Ben je niet tevreden over hoe
            ik met je gegevens omga, dan kun je een klacht indienen bij de
            <a href="https://autoriteitpersoonsgegevens.nl" target="_blank" rel="noopener">Autoriteit Persoonsgegevens</a>.
        </p>

        <p><a href="./">&larr; Terug naar de homepage</a></p>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
