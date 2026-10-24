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
            Als je het contactformulier invult, mij mailt of via een AI-assistent een bericht stuurt met de
            MCP-koppeling van deze site, ontvang ik de gegevens die je zelf opgeeft: je naam, e-mailadres en bericht,
            en als je die invult ook je bedrijfsnaam en telefoonnummer.
        </p>

        <h2>Waarom?</h2>
        <p>
            Ik gebruik deze gegevens uitsluitend om op je vraag te reageren en, als je dat wilt, een kennismaking te plannen.
            De grondslag hiervoor is mijn gerechtvaardigd belang om op vragen te reageren, of het nemen van stappen op jouw
            verzoek voordat we eventueel een overeenkomst sluiten. Ik verkoop je gegevens niet en deel ze niet met derden,
            behalve mijn e-mail- en hostingprovider die nodig zijn om berichten te ontvangen.
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
            Om te zien hoe de site gebruikt wordt, houd ik op mijn eigen server eenvoudige statistieken bij:
            welke pagina bezocht wordt, van welke website je kwam (alleen de domeinnaam), en welk type apparaat en
            browser je gebruikt. Je volledige IP-adres wordt daarbij niet opgeslagen. Om bezoekers per dag te kunnen
            tellen, wordt een pseudonieme code gemaakt uit een ingekort IP-adres en de browsergegevens die je browser
            meestuurt (de zogeheten user-agent). Die code verandert elke dag: de sleutel om hem te maken wordt bij het
            eerste bezoek van een nieuwe dag gewist, zodat bezoeken niet over meerdere dagen aan elkaar te koppelen zijn.
            De statistieken worden na ongeveer 13 maanden automatisch verwijderd en niet met anderen gedeeld.
        </p>
        <p>
            Om misbruik te voorkomen, zoals spam via het contactformulier of de MCP-koppeling en het raden van het
            wachtwoord van de statistiekenpagina, wordt op dezelfde pseudonieme manier bijgehouden hoe vaak er vanaf een
            netwerk berichten worden verstuurd of inlogpogingen worden gedaan. Die gegevens worden na 24 uur niet meer
            gebruikt en bij de eerstvolgende opschoning verwijderd.
        </p>
        <p>
            De grondslag voor de statistieken en de bescherming tegen misbruik is mijn gerechtvaardigd belang: weten hoe
            de site gebruikt wordt en de site en mijn inbox beschermen. Door het inkorten van IP-adressen en het dagelijks
            wisselen van de code houd ik de inbreuk op je privacy zo klein mogelijk.
        </p>
        <p>
            Mijn hostingprovider houdt, zoals gebruikelijk, technische serverlogs bij (waaronder IP-adressen) voor de
            beveiliging en het goed laten werken van de server. Daar heb ik zelf geen invloed op.
        </p>
        <p>
            Alleen als ik zelf inlog op de beveiligde statistiekenpagina worden er cookies geplaatst: een sessiecookie om
            ingelogd te blijven en een cookie zodat mijn eigen bezoeken niet in de statistieken meetellen. Bij gewone
            bezoekers worden deze cookies niet geplaatst.
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
