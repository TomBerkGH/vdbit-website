<?php

declare(strict_types=1);

require __DIR__ . '/includes/config.php';

$pageTitle = 'VDB-IT | AI-specialist voor het MKB en de maakindustrie';
$pageDescription = 'Tom van den Berk: zelfstandig software developer met bijna 20 jaar ervaring en specialist in AI. '
    . 'AI-integraties, procesoptimalisatie, maatwerksoftware en MCP-koppelingen voor het MKB.';
$isHome = true;

$content = require __DIR__ . '/includes/content.php';
$mcpUrl = SITE['url'] . '/mcp.php';

$contactStatus = $_GET['contact'] ?? null;

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="hero-glow" aria-hidden="true"></div>
    <div class="container hero-inner">
        <div class="hero-text">
            <span class="label reveal">&gt; ai.connect(werkvloer)</span>
            <h1 class="reveal">AI die werkt op <span class="gradient-text">de werkvloer.</span></h1>
            <p class="lede reveal">
                Ik ben <?= e(SITE['owner']) ?>, zelfstandig software developer met bijna 20 jaar ervaring en specialist in AI.
                Ik help MKB-bedrijven in de maakindustrie om AI in te zetten waar het écht iets oplevert:
                in hun processen, machines en systemen.
            </p>
            <div class="hero-actions reveal">
                <a class="btn" href="#contact">Plan een kennismaking <span aria-hidden="true">&rarr;</span></a>
                <a class="btn btn-ghost" href="#cases">Bekijk wat ik bouw</a>
            </div>
            <ul class="hero-facts reveal">
                <li><strong>~20</strong> jaar software&shy;ontwikkeling</li>
                <li><strong>AI</strong> specialist</li>
                <li><strong>FLEX</strong> eigen industrieel platform</li>
            </ul>
        </div>
        <svg class="hero-mark" viewBox="0 0 410 315" aria-hidden="true">
            <polygon points="0,33 245,110 12,145" fill="var(--c-red)"/>
            <polygon points="118,14 312,2 265,102" fill="var(--c-green)"/>
            <polygon points="346,0 408,73 285,102" fill="var(--c-orange)"/>
            <polygon points="32,182 245,128 140,312 148,208" fill="var(--c-blue)"/>
        </svg>
    </div>
</section>

<section class="section" id="diensten">
    <div class="container">
        <span class="label reveal">Diensten</span>
        <h2 class="reveal">Waarmee ik je help</h2>
        <p class="section-intro reveal">Geen AI om de AI, maar oplossingen die passen bij jouw processen en mensen.</p>
        <div class="service-grid">
            <?php foreach ($content['services'] as $service): ?>
                <article class="card reveal">
                    <svg class="card-icon" viewBox="0 0 24 24" aria-hidden="true"><?= $service['icon'] ?></svg>
                    <h3><?= e($service['title']) ?></h3>
                    <p><?= e($service['text']) ?></p>
                </article>
            <?php endforeach ?>
        </div>
    </div>
</section>

<section class="section section-alt" id="werkwijze">
    <div class="container">
        <span class="label reveal">Werkwijze</span>
        <h2 class="reveal">Van eerste gesprek tot werkend systeem</h2>
        <p class="section-intro reveal">Als zzp'er ben ik je enige aanspreekpunt. Korte lijnen, geen accountmanagers, geen verrassingen.</p>
        <ol class="steps">
            <?php foreach ($content['steps'] as $i => $step): ?>
                <li class="step reveal">
                    <span class="step-number"><?= sprintf('%02d', $i + 1) ?></span>
                    <h3><?= e($step['title']) ?></h3>
                    <p><?= e($step['text']) ?></p>
                </li>
            <?php endforeach ?>
        </ol>
    </div>
</section>

<section class="section section-tech" id="cases">
    <div class="tech-glow" aria-hidden="true"></div>
    <div class="container">
        <span class="label reveal">&gt; cases</span>
        <h2 class="reveal">Wat ik bouw</h2>
        <p class="section-intro reveal">AI en software die elke dag draaien in een productieomgeving.</p>

        <div class="case-grid">
            <?php foreach ($content['cases'] as $case): ?>
                <article class="case reveal">
                    <div class="case-meta"><span class="status"><?= e($case['status']) ?></span><span><?= e($case['context']) ?></span></div>
                    <h3><?= e($case['title']) ?></h3>
                    <p><?= e($case['text']) ?></p>
                    <ul class="tags">
                        <?php foreach ($case['tags'] as $tag): ?>
                            <li><?= e($tag) ?></li>
                        <?php endforeach ?>
                    </ul>
                    <?php if ($case['link']): ?>
                        <a class="case-link" href="<?= e($case['link']['url']) ?>" target="_blank" rel="noopener"><?= e($case['link']['label']) ?> <span aria-hidden="true">&nearr;</span></a>
                    <?php endif ?>
                </article>
            <?php endforeach ?>
        </div>

        <div class="mcp-demo reveal" id="mcp">
            <div class="mcp-demo-text">
                <span class="status">Live demo</span>
                <h3>Deze website heeft zelf een MCP-server</h3>
                <p>
                    Koppel je AI-assistent aan deze site en vraag zelf naar mijn ervaring, diensten en projecten.
                    Precies zo koppel ik AI aan de systemen van mijn klanten.
                </p>
                <p class="mcp-demo-hint">
                    In Claude: <em>Instellingen &rarr; Connectors &rarr; Aangepaste connector toevoegen</em> en plak de URL.
                    <a href="mcp-koppeling.php">Uitleg voor andere AI-clients &rarr;</a>
                </p>
                <a class="btn-github" href="<?= e(SITE['github']) ?>" target="_blank" rel="noopener">
                    <?= github_icon() ?>
                    <span>Bekijk de broncode op GitHub</span>
                    <span aria-hidden="true">&nearr;</span>
                </a>
            </div>
            <div class="mcp-demo-code">
                <div class="code-block">
                    <span class="code-label">MCP-endpoint</span>
                    <code><?= e($mcpUrl) ?></code>
                </div>
                <div class="code-block">
                    <span class="code-label">Claude Code</span>
                    <code>claude mcp add --transport http vdb-it <?= e($mcpUrl) ?></code>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section" id="over-mij">
    <div class="container about">
        <div class="about-text">
            <span class="label reveal">Over mij</span>
            <h2 class="reveal">Thuis in de code én op de werkvloer</h2>
            <?php foreach ($content['profile']['about'] as $paragraph): ?>
                <p class="reveal"><?= e($paragraph) ?></p>
            <?php endforeach ?>
        </div>
        <ul class="about-panel reveal">
            <li><span>Ervaring</span><strong>Bijna 20 jaar</strong></li>
            <li><span>Specialisme</span><strong>AI &amp; MCP</strong></li>
            <li><span>Sector</span><strong>MKB &amp; maakindustrie</strong></li>
            <li><span>Standplaats</span><strong><?= e(SITE['city']) ?></strong></li>
        </ul>
    </div>
</section>

<section class="section section-alt" id="contact">
    <div class="container contact">
        <div class="contact-text">
            <span class="label reveal">Contact</span>
            <h2 class="reveal">Benieuwd wat AI voor jouw bedrijf kan doen?</h2>
            <p class="reveal">Vertel kort waar je tegenaan loopt. Ik reageer binnen één werkdag en we plannen een vrijblijvende kennismaking.</p>
            <ul class="contact-direct reveal">
                <li><span>Bel</span><a href="tel:<?= e(SITE['phone_href']) ?>"><?= e(SITE['phone']) ?></a></li>
                <li><span>Mail</span><a href="mailto:<?= e(SITE['email']) ?>"><?= e(SITE['email']) ?></a></li>
            </ul>
        </div>

        <form class="contact-form card reveal" action="contact.php" method="post">
            <?php if ($contactStatus === 'ok'): ?>
                <p class="form-message form-success" role="status">Bedankt voor je bericht! Ik neem snel contact met je op.</p>
            <?php elseif ($contactStatus === 'fout'): ?>
                <p class="form-message form-error" role="alert">
                    Het versturen is helaas niet gelukt. Controleer je gegevens of mail direct naar
                    <a href="mailto:<?= e(SITE['email']) ?>"><?= e(SITE['email']) ?></a>.
                </p>
            <?php endif ?>

            <div class="field-row">
                <label>Naam<input type="text" name="naam" required maxlength="100" autocomplete="name"></label>
                <label>Bedrijf <small>(optioneel)</small><input type="text" name="bedrijf" maxlength="100" autocomplete="organization"></label>
            </div>
            <div class="field-row">
                <label>E-mail<input type="email" name="email" required maxlength="150" autocomplete="email"></label>
                <label>Telefoon <small>(optioneel)</small><input type="tel" name="telefoon" maxlength="30" autocomplete="tel"></label>
            </div>
            <label>Bericht<textarea name="bericht" rows="5" required maxlength="5000"></textarea></label>

            <!-- Honeypot tegen spambots: echte bezoekers zien dit veld niet. -->
            <label class="hp" aria-hidden="true">Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>

            <p class="form-note">Je gegevens gebruik ik alleen om op je bericht te reageren. Zie de <a href="privacy.php">privacyverklaring</a>.</p>
            <button class="btn" type="submit">Verstuur bericht <span aria-hidden="true">&rarr;</span></button>
        </form>
    </div>
</section>

<script type="application/ld+json">
<?= json_encode([
    '@context'    => 'https://schema.org',
    '@type'       => 'ProfessionalService',
    'name'        => SITE['name'],
    'founder'     => SITE['owner'],
    'url'         => SITE['url'],
    'email'       => SITE['email'],
    'telephone'   => SITE['phone'],
    'description' => $pageDescription,
    'address'     => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => SITE['street'],
        'postalCode'      => SITE['postcode'],
        'addressLocality' => SITE['city'],
        'addressCountry'  => 'NL',
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
