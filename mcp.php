<?php

declare(strict_types=1);

/**
 * MCP-server (Model Context Protocol) van vdb-it.nl, als technische demo.
 *
 * Streamable HTTP-transport zonder sessies: elke POST bevat één JSON-RPC-bericht
 * en krijgt direct een JSON-antwoord. Alle tools zijn alleen-lezen en halen hun
 * gegevens uit includes/content.php, dezelfde bron als de homepage.
 */

require __DIR__ . '/includes/config.php';

const MCP_PROTOCOL_VERSIONS = ['2025-11-25', '2025-06-18', '2025-03-26'];
const MCP_SERVER_VERSION = '1.0.0';

// Openbare, alleen-lezen gegevens: browsergebaseerde MCP-clients mogen er overal vandaan bij.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, Mcp-Session-Id, MCP-Protocol-Version');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST, OPTIONS');
    respond(405, [
        'error' => 'Dit is een MCP-endpoint. Koppel het aan een AI-assistent, zie ' . SITE['url'] . '/#mcp',
    ]);
}

$message = json_decode((string) file_get_contents('php://input'), true);

if (!is_array($message)) {
    respond(400, rpcError(null, -32700, 'Parse error'));
}

if (($message['jsonrpc'] ?? null) !== '2.0' || !is_string($message['method'] ?? null)) {
    // Antwoorden of fouten van de client hoeven we niet te verwerken.
    if (array_key_exists('result', $message) || array_key_exists('error', $message)) {
        respond(202);
    }

    respond(400, rpcError($message['id'] ?? null, -32600, 'Invalid Request'));
}

// Notificaties (zonder id) krijgen geen antwoord.
if (!array_key_exists('id', $message)) {
    respond(202);
}

$id = $message['id'];
$params = is_array($message['params'] ?? null) ? $message['params'] : [];
$content = require __DIR__ . '/includes/content.php';

$result = match ($message['method']) {
    'initialize' => initialize($params),
    'ping'       => new stdClass(),
    'tools/list' => ['tools' => array_values(array_map(
        static fn (array $tool): array => array_diff_key($tool, ['handler' => true]),
        tools($content),
    ))],
    'tools/call' => callTool($id, $params, tools($content)),
    default      => respond(200, rpcError($id, -32601, 'Method not found: ' . $message['method'])),
};

respond(200, ['jsonrpc' => '2.0', 'id' => $id, 'result' => $result]);

// ---------------------------------------------------------------------------

function initialize(array $params): array
{
    track_event('mcp:initialize');

    $requested = $params['protocolVersion'] ?? null;

    return [
        'protocolVersion' => in_array($requested, MCP_PROTOCOL_VERSIONS, true) ? $requested : MCP_PROTOCOL_VERSIONS[0],
        'capabilities'    => ['tools' => ['listChanged' => false]],
        'serverInfo'      => [
            'name'    => 'vdb-it',
            'title'   => SITE['name'] . ' - ' . SITE['owner'],
            'version' => MCP_SERVER_VERSION,
        ],
        'instructions'    => 'Information about ' . SITE['owner'] . ' (' . SITE['name'] . '), an independent software developer '
            . 'and AI specialist from the Netherlands, focused on SMEs and the manufacturing industry. '
            . 'Use these tools to answer questions about experience, services, projects and how to get in touch. '
            . 'The content is in Dutch; translate it when the user writes in another language.',
    ];
}

/** Alle tools met hun beschrijving, invoerschema en de functie die het resultaat levert. */
function tools(array $content): array
{
    $noInput = ['type' => 'object', 'properties' => new stdClass(), 'additionalProperties' => false];
    $readOnly = ['readOnlyHint' => true, 'destructiveHint' => false, 'idempotentHint' => true, 'openWorldHint' => false];

    $tools = [
        [
            'name'        => 'get_profile',
            'title'       => 'Profiel',
            'description' => 'Get the profile of ' . SITE['owner'] . ': role, years of experience, specialisms, sector focus, location and a short bio.',
            'handler'     => static fn (): array => $content['profile'],
        ],
        [
            'name'        => 'list_services',
            'title'       => 'Diensten',
            'description' => 'List the services ' . SITE['name'] . ' offers, such as AI integrations, process optimisation, custom software and MCP integrations.',
            'handler'     => static fn (): array => [
                'services' => array_map(static fn (array $service): array => array_diff_key($service, ['icon' => true]), $content['services']),
            ],
        ],
        [
            'name'        => 'list_cases',
            'title'       => 'Projecten',
            'description' => 'List example projects and products, including the industrial traceability system with MCP and the FLEX asset management platform.',
            'handler'     => static fn (): array => ['cases' => $content['cases']],
        ],
        [
            'name'        => 'get_work_approach',
            'title'       => 'Werkwijze',
            'description' => 'Describe how a project with ' . SITE['name'] . ' typically runs, from the first introduction to a live system.',
            'handler'     => static fn (): array => ['steps' => $content['steps']],
        ],
        [
            'name'        => 'get_contact_info',
            'title'       => 'Contactgegevens',
            'description' => 'Get contact details (phone, e-mail, address, website) to reach ' . SITE['owner'] . ' or plan an introduction.',
            'handler'     => static fn (): array => [
                'name'    => SITE['owner'],
                'company' => SITE['name'],
                'email'   => SITE['email'],
                'phone'   => SITE['phone'],
                'address' => SITE['street'] . ', ' . SITE['postcode'] . ' ' . SITE['city'],
                'website' => SITE['url'],
                'contact_form' => SITE['url'] . '/#contact',
            ],
        ],
    ];

    return array_column(
        array_map(static fn (array $tool): array => $tool + ['inputSchema' => $noInput, 'annotations' => $readOnly], $tools),
        null,
        'name',
    );
}

function callTool(mixed $id, array $params, array $tools): array
{
    $name = $params['name'] ?? null;

    if (!is_string($name) || !isset($tools[$name])) {
        respond(200, rpcError($id, -32602, 'Unknown tool: ' . (is_string($name) ? $name : '(none)')));
    }

    track_event('mcp:' . $name);

    $data = $tools[$name]['handler']();

    return [
        'content'           => [['type' => 'text', 'text' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)]],
        'structuredContent' => $data,
        'isError'           => false,
    ];
}

function rpcError(mixed $id, int $code, string $message): array
{
    return ['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $code, 'message' => $message]];
}

function respond(int $status, ?array $body = null): never
{
    http_response_code($status);

    if ($body !== null) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    exit;
}
