<?php

declare(strict_types=1);

/** Fout die de tool aan de AI-client teruggeeft als resultaat met isError, in plaats van als protocolfout. */
final class McpToolError extends RuntimeException
{
}

/**
 * De tools van de MCP-server. Gebruikt door mcp/index.php (de server zelf)
 * en mcp-koppeling.php (de uitlegpagina), zodat die altijd overeenkomen.
 * Elke handler krijgt de arguments van de aanroep mee.
 */
function mcp_tools(array $content): array
{
    $noInput = ['type' => 'object', 'properties' => new stdClass(), 'additionalProperties' => false];
    $readOnly = ['readOnlyHint' => true, 'destructiveHint' => false, 'idempotentHint' => true, 'openWorldHint' => false];

    $tools = [
        [
            'name'        => 'get_profile',
            'title'       => 'Profiel',
            'summary'     => 'Wie ik ben: rol, ervaring, specialismen, sector en een korte bio.',
            'description' => 'Get the profile of ' . SITE['owner'] . ': role, years of experience, specialisms, sector focus, location and a short bio.',
            'handler'     => static fn (): array => $content['profile'],
        ],
        [
            'name'        => 'list_services',
            'title'       => 'Diensten',
            'summary'     => 'De diensten die ik aanbied, met een korte uitleg per dienst.',
            'description' => 'List the services ' . SITE['name'] . ' offers, such as AI integrations, process optimisation, custom software, MCP integrations, '
                . 'fast prototyping and fast delivery to production with agentic workflows, and helping people and companies get started with AI themselves.',
            'handler'     => static fn (): array => [
                'services' => array_map(static fn (array $service): array => array_diff_key($service, ['icon' => true]), $content['services']),
            ],
        ],
        [
            'name'        => 'list_cases',
            'title'       => 'Projecten',
            'summary'     => 'Voorbeeldprojecten, zoals het traceability-systeem en het FLEX-platform.',
            'description' => 'List example projects and products, including the industrial traceability system with MCP and the FLEX asset management platform.',
            'handler'     => static fn (): array => ['cases' => $content['cases']],
        ],
        [
            'name'        => 'get_work_approach',
            'title'       => 'Werkwijze',
            'summary'     => 'Hoe een traject verloopt, van kennismaking tot een werkend systeem.',
            'description' => 'Describe how a project with ' . SITE['name'] . ' typically runs, from the first introduction to a live system.',
            'handler'     => static fn (): array => ['steps' => $content['steps']],
        ],
        [
            'name'        => 'get_contact_info',
            'title'       => 'Contactgegevens',
            'summary'     => 'Telefoon, e-mail, adres en website om contact op te nemen.',
            'description' => 'Get contact details (phone, e-mail, address, website) to reach ' . SITE['owner'] . ' or plan an introduction.',
            'handler'     => static fn (): array => [
                'name'         => SITE['owner'],
                'company'      => SITE['name'],
                'email'        => SITE['email'],
                'phone'        => SITE['phone'],
                'address'      => SITE['street'] . ', ' . SITE['postcode'] . ' ' . SITE['city'],
                'website'      => SITE['url'],
                'contact_form' => SITE['url'] . '/#contact',
            ],
        ],
        [
            'name'        => 'send_contact_request',
            'title'       => 'Bericht sturen',
            'summary'     => 'Stuurt namens de gebruiker een bericht naar mij, bijvoorbeeld voor een kennismaking. De AI vraagt eerst om bevestiging.',
            'description' => 'Send a message to ' . SITE['owner'] . ' on behalf of the user, for example to request an introduction, a workshop or a quote. '
                . 'Only use this when the user explicitly asks to get in touch. Before sending, show the user the exact message and confirm '
                . 'their own name and e-mail address; never make these up. The message is always delivered to ' . SITE['name'] . ' only. '
                . 'Write the message in Dutch unless the user prefers otherwise.',
            'inputSchema' => [
                'type'                 => 'object',
                'properties'           => [
                    'name'    => ['type' => 'string', 'description' => 'Full name of the user', 'maxLength' => 100],
                    'email'   => ['type' => 'string', 'description' => 'E-mail address of the user, used to reply', 'format' => 'email', 'maxLength' => 150],
                    'message' => ['type' => 'string', 'description' => 'The message to ' . SITE['owner'], 'maxLength' => 5000],
                    'company' => ['type' => 'string', 'description' => 'Company name (optional)', 'maxLength' => 100],
                    'phone'   => ['type' => 'string', 'description' => 'Phone number (optional)', 'maxLength' => 30],
                ],
                'required'             => ['name', 'email', 'message'],
                'additionalProperties' => false,
            ],
            'annotations' => ['readOnlyHint' => false, 'destructiveHint' => false, 'idempotentHint' => false, 'openWorldHint' => true],
            'handler'     => static function (array $arguments): array {
                $message = contact_message_from($arguments);

                if ($message === null) {
                    throw new McpToolError('Naam, een geldig e-mailadres en een bericht zijn verplicht.');
                }

                if (!rate_limit_allows('mail:mcp', MAIL_MCP_PER_CLIENT_PER_HOUR, MAIL_TOTAL_PER_DAY)) {
                    throw new McpToolError('Het bericht kan nu niet worden verstuurd (limiet bereikt of tijdelijke storing). '
                        . 'Probeer het later opnieuw, of mail of bel ' . SITE['owner'] . ' direct: ' . SITE['email'] . ', ' . SITE['phone'] . '.');
                }

                if (!send_contact_mail($message, 'MCP-bericht: ', 'de MCP-server van ' . SITE['url'])) {
                    throw new McpToolError('Het versturen is niet gelukt. Mail of bel ' . SITE['owner'] . ' direct: ' . SITE['email'] . ', ' . SITE['phone'] . '.');
                }

                return [
                    'sent'    => true,
                    'to'      => SITE['owner'] . ' (' . SITE['name'] . ')',
                    'message' => 'Het bericht is verstuurd. ' . SITE['owner'] . ' reageert meestal binnen één werkdag op ' . $message['email'] . '.',
                ];
            },
        ],
    ];

    return array_column(
        array_map(static fn (array $tool): array => $tool + ['inputSchema' => $noInput, 'annotations' => $readOnly], $tools),
        null,
        'name',
    );
}
