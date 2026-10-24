<?php

declare(strict_types=1);

require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/mailer.php';
require __DIR__ . '/includes/mcp-tools.php';

$pageTitle = 'MCP-koppeling | VDB-IT';
$pageDescription = 'Koppel je AI-assistent aan de MCP-server van vdb-it.nl en vraag zelf naar ervaring, diensten en projecten.';

$content = require __DIR__ . '/includes/content.php';
$tools = mcp_tools($content);
$mcpUrl = SITE['url'] . '/mcp';

$examples = [
    'Wie is ' . SITE['owner'] . ' en wat zijn de specialismen?',
    'Welke diensten biedt ' . SITE['name'] . ' aan voor een productiebedrijf?',
    'Geef een voorbeeld van een project met MCP in de industrie.',
    'Hoe verloopt een traject, van eerste gesprek tot werkend systeem?',
    'Stuur ' . SITE['owner'] . ' een bericht dat ik interesse heb in een AI-workshop.',
];

$curlExample = <<<BASH
curl -X POST {$mcpUrl} \\
  -H "Content-Type: application/json" \\
  -H "Accept: application/json, text/event-stream" \\
  -d '{"jsonrpc":"2.0","id":1,"method":"tools/call","params":{"name":"get_profile"}}'
BASH;

$vscodeConfig = json_encode(['servers' => ['vdb-it' => ['type' => 'http', 'url' => $mcpUrl]]], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
$cursorConfig = json_encode(['mcpServers' => ['vdb-it' => ['url' => $mcpUrl]]], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

require __DIR__ . '/includes/header.php';
?>

<section class="section page mcp-page">
    <div class="container">
        <div class="mcp-intro">
            <span class="label">&gt; mcp</span>
            <h1>Praat met deze website <span class="gradient-text">via AI.</span></h1>
            <p class="lede">
                Deze website heeft een eigen MCP-server. Koppel je AI-assistent eraan en stel zelf vragen over mijn
                ervaring, diensten en projecten, of laat de AI direct een bericht naar me sturen. Het is een kleine demo
                van wat ik bij klanten bouw: AI die rechtstreeks met je eigen systemen en data werkt.
            </p>
            <div class="code-block code-block-solid">
                <span class="code-label">MCP-endpoint</span>
                <code><?= e($mcpUrl) ?></code>
            </div>
        </div>

        <div class="mcp-explain">
            <div>
                <h2>Wat is MCP?</h2>
                <p>
                    Het <strong>Model Context Protocol</strong> is een open standaard waarmee AI-assistenten zoals Claude
                    veilig gegevens kunnen opvragen uit andere systemen. Een MCP-server biedt <em>tools</em> aan; de AI
                    bepaalt zelf welke tool hij nodig heeft om je vraag te beantwoorden.
                </p>
                <p>
                    In een fabriek kan dat een ERP-, MES- of traceability-systeem zijn. Je vraagt dan bijvoorbeeld
                    <em>&ldquo;welke orders lopen vandaag achter?&rdquo;</em> en de AI haalt het antwoord direct uit de bron.
                    Hier is de bron gewoon deze website.
                </p>
            </div>
            <ul class="mcp-facts">
                <li><span>Toegang</span><strong>Openbaar, geen account nodig</strong></li>
                <li><span>Rechten</span><strong>Lezen en een bericht sturen</strong></li>
                <li><span>Transport</span><strong>Streamable HTTP</strong></li>
                <li><span>Tools</span><strong><?= count($tools) ?></strong></li>
            </ul>
        </div>

        <h2 class="mcp-heading">Koppelen in een minuut</h2>
        <div class="client-grid">
            <article class="card client">
                <h3>Claude <small>(web en desktop)</small></h3>
                <ol>
                    <li>Ga naar <em>Instellingen &rarr; Connectors</em>.</li>
                    <li>Kies <em>Aangepaste connector toevoegen</em>.</li>
                    <li>Vul als naam <code>VDB-IT</code> in en plak de URL van het endpoint.</li>
                    <li>Zet de connector aan in een gesprek en stel je vraag.</li>
                </ol>
            </article>

            <article class="card client">
                <h3>Claude Code</h3>
                <p>Eén commando in je terminal:</p>
                <div class="code-block code-block-solid">
                    <pre tabindex="0" role="region" aria-label="Commando voor Claude Code"><code>claude mcp add --transport http vdb-it <?= e($mcpUrl) ?></code></pre>
                </div>
            </article>

            <article class="card client">
                <h3>VS Code <small>(GitHub Copilot)</small></h3>
                <p>Zet dit in <code>.vscode/mcp.json</code>:</p>
                <div class="code-block code-block-solid">
                    <pre tabindex="0" role="region" aria-label="Configuratie voor VS Code"><code><?= e($vscodeConfig) ?></code></pre>
                </div>
            </article>

            <article class="card client">
                <h3>Cursor</h3>
                <p>Zet dit in <code>~/.cursor/mcp.json</code>:</p>
                <div class="code-block code-block-solid">
                    <pre tabindex="0" role="region" aria-label="Configuratie voor Cursor"><code><?= e($cursorConfig) ?></code></pre>
                </div>
            </article>
        </div>
        <p class="muted mcp-note">Andere AI-clients die MCP via HTTP ondersteunen, werken met dezelfde URL.</p>

        <h2 class="mcp-heading">AI die ook iets dóét</h2>
        <div class="mcp-explain mcp-explain-tight">
            <div>
                <p>
                    De meeste tools lezen alleen gegevens. Met <code>send_contact_request</code> kan je AI-assistent ook een
                    bericht naar mij sturen, bijvoorbeeld om een kennismaking aan te vragen. Je AI laat je eerst het bericht
                    zien en vraagt om bevestiging; het bericht komt bij mij binnen net als via het contactformulier.
                </p>
                <p>
                    Precies zo kan AI in jouw bedrijf een werkorder aanmaken, een storing melden of een order vrijgeven:
                    altijd met duidelijke grenzen en met de mens die bevestigt.
                </p>
            </div>
            <ul class="mcp-facts">
                <li><span>Ontvanger</span><strong>Altijd alleen <?= e(SITE['name']) ?></strong></li>
                <li><span>Bevestiging</span><strong>Door de gebruiker</strong></li>
                <li><span>Limiet</span><strong><?= MAIL_MCP_PER_CLIENT_PER_HOUR ?> per uur, <?= MAIL_MCP_TOTAL_PER_DAY ?> per dag</strong></li>
            </ul>
        </div>

        <h2 class="mcp-heading">Probeer bijvoorbeeld</h2>
        <ul class="prompt-list">
            <?php foreach ($examples as $example): ?>
                <li>&ldquo;<?= e($example) ?>&rdquo;</li>
            <?php endforeach ?>
        </ul>

        <h2 class="mcp-heading">Beschikbare tools</h2>
        <div class="card tool-table-wrap">
            <table class="tool-table">
                <thead>
                    <tr><th scope="col">Tool</th><th scope="col">Wat het oplevert</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($tools as $tool): ?>
                        <tr>
                            <td><code><?= e($tool['name']) ?></code></td>
                            <td><?= e($tool['summary']) ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <div class="mcp-dev">
            <div>
                <h2>Voor ontwikkelaars</h2>
                <p>
                    De server bestaat uit een paar eenvoudige PHP-bestanden, zonder framework. Hij spreekt JSON-RPC 2.0 via
                    Streamable HTTP, zonder sessies: elke POST krijgt direct een JSON-antwoord. Ondersteunde protocolversies:
                    <?= implode(', ', array_map(static fn (string $version): string => '<code>' . e($version) . '</code>', MCP_PROTOCOL_VERSIONS)) ?>.
                </p>
                <a class="btn-github btn-github-light" href="<?= e(SITE['github']) ?>/blob/main/mcp/index.php" target="_blank" rel="noopener">
                    <?= github_icon() ?>
                    <span>Bekijk de MCP-server op GitHub</span>
                    <span aria-hidden="true">&nearr;</span>
                </a>
            </div>
            <div class="code-block code-block-solid">
                <span class="code-label">Zelf proberen met curl</span>
                <pre tabindex="0" role="region" aria-label="Voorbeeld met curl"><code><?= e($curlExample) ?></code></pre>
            </div>
        </div>

        <div class="mcp-cta card">
            <div>
                <h2>Dit ook voor jouw systemen?</h2>
                <p class="muted">Ik bouw MCP-koppelingen op ERP, MES, traceability en maatwerksoftware, zodat je team AI kan laten werken met de eigen bedrijfsdata.</p>
            </div>
            <a class="btn" href="./#contact">Plan een kennismaking <span aria-hidden="true">&rarr;</span></a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
