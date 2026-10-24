<?php

declare(strict_types=1);

/**
 * MCP-server (Model Context Protocol) van vdb-it.nl, als technische demo.
 * Bereikbaar op /mcp (via een rewrite in .htaccess, zonder redirect naar /mcp/).
 *
 * Streamable HTTP-transport zonder sessies: elke POST bevat een JSON-RPC-bericht (of een batch)
 * en krijgt direct een JSON-antwoord. De leestools halen hun gegevens uit includes/content.php,
 * dezelfde bron als de homepage. Met send_contact_request kan een AI-assistent een bericht sturen
 * (met rate limiting, zie includes/mcp-tools.php).
 */

// PHP-waarschuwingen mogen nooit in het JSON-antwoord belanden; ze gaan naar het errorlog.
ini_set('display_errors', '0');

require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/mailer.php';
require __DIR__ . '/../includes/mcp-tools.php';

const MCP_SERVER_VERSION = '1.2.0';
const MCP_MAX_BODY_BYTES = 100_000;
const MCP_MAX_BATCH = 10;

// Openbaar endpoint: browsergebaseerde MCP-clients mogen er overal vandaan bij.
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
        'error' => 'Dit is een MCP-endpoint. Koppel het aan een AI-assistent, zie ' . SITE['url'] . '/mcp-koppeling.php',
    ]);
}

// MCP-clients sturen altijd JSON. Dit weert ook "simpele" cross-site formulierverzoeken.
if (!str_starts_with(strtolower($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json')) {
    respond(415, rpcError(null, -32600, 'Content-Type must be application/json'));
}

// Een client die een protocolversie meestuurt die we niet kennen, krijgt volgens de specificatie een 400.
$protocolHeader = $_SERVER['HTTP_MCP_PROTOCOL_VERSION'] ?? null;

if ($protocolHeader !== null && !in_array($protocolHeader, MCP_PROTOCOL_VERSIONS, true)) {
    respond(400, rpcError(null, -32600, 'Unsupported MCP-Protocol-Version: ' . mb_substr($protocolHeader, 0, 20)));
}

$input = (string) file_get_contents('php://input', length: MCP_MAX_BODY_BYTES + 1);

if (strlen($input) > MCP_MAX_BODY_BYTES) {
    respond(413, rpcError(null, -32600, 'Request too large'));
}

try {
    $body = json_decode($input, true, 32, JSON_THROW_ON_ERROR);
} catch (JsonException) {
    respond(400, rpcError(null, -32700, 'Parse error'));
}

$tools = mcp_tools(require __DIR__ . '/../includes/content.php');

// Batch: een lijst van berichten, elk met een eigen antwoord (notificaties krijgen er geen).
if (is_array($body) && $body !== [] && array_is_list($body)) {
    if (count($body) > MCP_MAX_BATCH) {
        respond(400, rpcError(null, -32600, 'Batch too large (max ' . MCP_MAX_BATCH . ' messages)'));
    }

    $responses = array_values(array_filter(array_map(static fn (mixed $message): ?array => handleMessage($message, $tools), $body)));
    $responses === [] ? respond(202) : respond(200, $responses);
}

$response = handleMessage($body, $tools);
$response === null ? respond(202) : respond(200, $response);

// ---------------------------------------------------------------------------

/** Verwerkt één JSON-RPC-bericht. Geeft null terug voor notificaties en antwoorden van de client. */
function handleMessage(mixed $message, array $tools): ?array
{
    if (!is_array($message) || array_is_list($message)) {
        return rpcError(null, -32600, 'Invalid Request');
    }

    if (($message['jsonrpc'] ?? null) !== '2.0' || !is_string($message['method'] ?? null)) {
        // Antwoorden of fouten van de client hoeven we niet te verwerken.
        if (array_key_exists('result', $message) || array_key_exists('error', $message)) {
            return null;
        }

        $id = $message['id'] ?? null;

        return rpcError(is_string($id) || is_int($id) ? $id : null, -32600, 'Invalid Request');
    }

    // Notificaties (zonder id) krijgen geen antwoord.
    if (!array_key_exists('id', $message)) {
        return null;
    }

    $id = $message['id'];

    if (!is_string($id) && !is_int($id)) {
        return rpcError(null, -32600, 'Invalid Request: id must be a string or an integer');
    }

    $params = $message['params'] ?? [];

    if (!is_array($params)) {
        return rpcError($id, -32602, 'Invalid params');
    }

    try {
        return match ($message['method']) {
            'initialize' => rpcResult($id, initialize($params)),
            'ping'       => rpcResult($id, new stdClass()),
            'tools/list' => rpcResult($id, ['tools' => array_values(array_map(
                static fn (array $tool): array => array_diff_key($tool, ['handler' => true, 'summary' => true]),
                $tools,
            ))]),
            'tools/call' => callTool($id, $params, $tools),
            default      => rpcError($id, -32601, 'Method not found: ' . $message['method']),
        };
    } catch (Throwable $exception) {
        error_log('mcp: ' . $exception->getMessage());

        return rpcError($id, -32603, 'Internal error');
    }
}

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
            . 'When the user wants to get in touch, you can send a message with send_contact_request, '
            . 'but only after the user has confirmed the message and their own name and e-mail address. '
            . 'The content is in Dutch; translate it when the user writes in another language.',
    ];
}

function callTool(int|string $id, array $params, array $tools): array
{
    $name = $params['name'] ?? null;

    if (!is_string($name) || !isset($tools[$name])) {
        return rpcError($id, -32602, 'Unknown tool: ' . (is_string($name) ? $name : '(none)'));
    }

    track_event('mcp:' . $name);

    $arguments = is_array($params['arguments'] ?? null) ? $params['arguments'] : [];

    try {
        $data = $tools[$name]['handler']($arguments);
    } catch (McpToolError $error) {
        return rpcResult($id, toolError($error->getMessage()));
    } catch (Throwable $exception) {
        error_log('mcp tool ' . $name . ': ' . $exception->getMessage());

        return rpcResult($id, toolError('Er ging iets mis bij het uitvoeren van deze tool. Probeer het later opnieuw.'));
    }

    return rpcResult($id, [
        'content'           => [['type' => 'text', 'text' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)]],
        'structuredContent' => $data,
        'isError'           => false,
    ]);
}

function toolError(string $message): array
{
    return ['content' => [['type' => 'text', 'text' => $message]], 'isError' => true];
}

function rpcResult(int|string $id, array|stdClass $result): array
{
    return ['jsonrpc' => '2.0', 'id' => $id, 'result' => $result];
}

function rpcError(int|string|null $id, int $code, string $message): array
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
