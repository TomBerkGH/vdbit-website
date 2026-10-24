<?php

declare(strict_types=1);

require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/mcp-tools.php';

$pageTitle = 'MCP-koppeling | VDB-IT';
$pageDescription = 'Koppel je AI-assistent aan de MCP-server van vdb-it.nl en vraag zelf naar ervaring, diensten en projecten.';

$content = require __DIR__ . '/includes/content.php';
$tools = mcp_tools($content);
$mcpUrl = SITE['url'] . '/mcp.php';

$examples = [
    'Wie is ' . SITE['owner'] . ' en wat zijn de specialismen?',
    'Welke diensten biedt ' . SITE['name'] . ' aan voor een productiebedrijf?',
    'Geef een voorbeeld van een project met MCP in de industrie.',
    'Hoe verloopt een traject, van eerste gesprek tot werkend systeem?',
    'Hoe kan ik contact opnemen voor een kennismaking?',
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
                ervaring, diensten en projecten. Het is een kleine demo van wat ik bij klanten bouw: AI die rechtstreeks
                met je eigen systemen en data werkt.
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
                    <em>"welke orders lopen vandaag achter?"</em> en de AI haalt het antwoord direct uit de bron.
                    Hier is de bron gewoon deze website.
                </p>
            </div>
            <ul class="mcp-facts">
                <li><span>Toegang</span><strong>Openbaar, geen account nodig</strong></li>
                <li><span>Rechten</span><strong>Alleen lezen</strong></li>
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
                    <pre><code>claude mcp add --transport http vdb-it <?= e($mcpUrl) ?></code></pre>
                </div>
            </article>

            <article class="card client">
                <h3>VS Code <small>(GitHub Copilot)</small></h3>
                <p>Zet dit in <code>.vscode/mcp.json</code>:</p>
                <div class="code-block code-block-solid">
                    <pre><code><?= e($vscodeConfig) ?></code></pre>
                </div>
            </article>

            <article class="card client">
                <h3>Cursor</h3>
                <p>Zet dit in <code>~/.cursor/mcp.json</code>:</p>
                <div class="code-block code-block-solid">
                    <pre><code><?= e($cursorConfig) ?></code></pre>
                </div>
            </article>
        </div>
        <p class="muted mcp-note">Andere AI-clients die MCP via HTTP ondersteunen, werken met dezelfde URL.</p>

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
                    De server is één PHP-bestand zonder framework. Hij spreekt JSON-RPC 2.0 via Streamable HTTP, zonder
                    sessies: elke POST krijgt direct een JSON-antwoord. Ondersteunde protocolversies:
                    <code>2025-11-25</code>, <code>2025-06-18</code> en <code>2025-03-26</code>.
                </p>
                <a class="btn-github btn-github-light" href="<?= e(SITE['github']) ?>/blob/main/mcp.php" target="_blank" rel="noopener">
                    <?= github_icon() ?>
                    <span>Bekijk mcp.php op GitHub</span>
                    <span aria-hidden="true">&nearr;</span>
                </a>
            </div>
            <div class="code-block code-block-solid">
                <span class="code-label">Zelf proberen met curl</span>
                <pre><code><?= e($curlExample) ?></code></pre>
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
