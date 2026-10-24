<?php

declare(strict_types=1);

/**
 * De tools van de MCP-server. Gebruikt door mcp.php (de server zelf)
 * en mcp-koppeling.php (de uitlegpagina), zodat die altijd overeenkomen.
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
    ];

    return array_column(
        array_map(static fn (array $tool): array => $tool + ['inputSchema' => $noInput, 'annotations' => $readOnly], $tools),
        null,
        'name',
    );
}
