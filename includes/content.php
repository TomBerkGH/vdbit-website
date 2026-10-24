<?php

declare(strict_types=1);

/**
 * Inhoud van de site op één plek. Zowel de homepage (index.php) als de MCP-server (mcp/index.php)
 * gebruiken dit bestand, dus een wijziging hier staat meteen op beide.
 */
return [
    'profile' => [
        'name'             => SITE['owner'],
        'company'          => SITE['name'],
        'role'             => 'Zelfstandig software developer en AI-specialist',
        'experience'       => 'Bijna 20 jaar softwareontwikkeling',
        'focus'            => 'MKB en de maakindustrie',
        'location'         => SITE['city'],
        'specialisms'      => ['AI-integraties', 'Industriële procesoptimalisatie', 'Maatwerksoftware', 'MCP-koppelingen (Model Context Protocol)', 'Agentic workflows: snel prototypen en snel in productie', 'AI-sparring, training en begeleiding'],
        'summary'          => 'Ik help MKB-bedrijven in de maakindustrie om AI in te zetten waar het écht iets oplevert: in hun processen, machines en systemen. Met agentic workflows staan prototypes én productieklare oplossingen razendsnel klaar, en ik help mensen en bedrijven ook om zelf met AI aan de slag te gaan.',
        'about'            => [
            'Ik ben ' . SITE['owner'] . ', oprichter van ' . SITE['name'] . ' in ' . SITE['city'] . '. Al bijna 20 jaar ontwikkel ik software, de laatste jaren met de focus op AI.',
            'Mijn kracht zit in de combinatie: technische diepgang en ervaring met industriële procesoptimalisatie. Ik weet hoe een productieproces loopt, waar het schuurt en hoe je software bouwt die mensen op de vloer ook echt gebruiken. Dat zie je terug in FLEX, het platform dat ik voor de industrie heb ontwikkeld.',
            'Zelf bouw ik met agentic workflows: AI-agents die zelfstandig meeprogrammeren, testen en itereren. Daardoor staat een prototype veel sneller klaar, en gaat de doorontwikkeling naar een stabiele productieversie net zo vlot. De time-to-market is een fractie van wat het was.',
            'AI ontwikkelt zich razendsnel. Ik vertaal die ontwikkelingen naar wat het voor jouw bedrijf betekent: nuchter, concreet en met oog voor wat het oplevert. En ik help mensen en bedrijven ook graag om zelf met AI aan de slag te gaan.',
        ],
    ],

    'services' => [
        [
            'title' => 'AI-integraties',
            'text'  => 'AI die meedraait in je bedrijf, niet ernaast. Ik koppel taalmodellen en slimme analyses aan je bestaande software en data, zodat je team sneller werkt en betere beslissingen neemt.',
            'icon'  => '<path d="M12 3v3M12 18v3M3 12h3M18 12h3"/><rect x="7" y="7" width="10" height="10" rx="2"/><path d="M10 10h4v4h-4z"/>',
        ],
        [
            'title' => 'Procesoptimalisatie',
            'text'  => 'Ik ken de productievloer. Samen brengen we in kaart waar tijd, materiaal en geld weglekken, en pakken we dat aan met data, automatisering en AI.',
            'icon'  => '<path d="M3 20h18"/><path d="M5 16l4-5 4 3 6-8"/><path d="M15 6h4v4"/>',
        ],
        [
            'title' => 'Maatwerksoftware',
            'text'  => 'Past standaardsoftware niet bij hoe jij werkt? Dan bouw ik wat wél past: robuuste webapplicaties en koppelingen die jarenlang meegaan.',
            'icon'  => '<path d="M8 8l-4 4 4 4M16 8l4 4-4 4M13.5 5l-3 14"/>',
        ],
        [
            'title' => 'MCP-koppelingen',
            'text'  => 'Met het Model Context Protocol geef je AI-assistenten veilig toegang tot je eigen systemen. Je stelt een vraag en krijgt antwoord uit je eigen productiedata.',
            'icon'  => '<circle cx="6" cy="6" r="2.5"/><circle cx="18" cy="6" r="2.5"/><circle cx="12" cy="18" r="2.5"/><path d="M8.5 6h7M7.3 8.2l3.4 7.6M16.7 8.2l-3.4 7.6"/>',
        ],
        [
            'title' => 'Snel van idee naar productie',
            'text'  => 'Met agentic workflows, waarin AI-agents zelfstandig meebouwen en testen, gaat het razendsnel: vaak binnen enkele dagen een werkend prototype, en daarna net zo snel een robuuste oplossing in productie. Zo is de time-to-market een fractie van wat je gewend bent.',
            'icon'  => '<path d="M13 2L4 14h7l-1 8 9-12h-7z"/>',
        ],
        [
            'title' => 'Zelf aan de slag met AI',
            'text'  => 'Ik help mensen en bedrijven om zelf met AI te werken. Samen sparren over wat AI voor jou kan betekenen, leren hoe je AI slim inzet in je dagelijkse werk, praktische workshops en begeleiding bij je eerste eigen AI-toepassingen en agentic workflows.',
            'icon'  => '<path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5"/><path d="M22 9v5"/>',
        ],
    ],

    'steps' => [
        ['title' => 'Kennismaking', 'text' => 'Een vrijblijvend gesprek over je bedrijf en waar het knelt. Je hoort eerlijk of AI daar iets kan opleveren.'],
        ['title' => 'Verkenning', 'text' => 'Ik kijk mee op de vloer en in je systemen. Je krijgt een concreet voorstel: wat we bouwen en wat het oplevert.'],
        ['title' => 'Bouwen', 'text' => 'Dankzij agentic workflows staat er snel een werkend prototype. We bouwen in korte stappen en sturen bij op basis van de praktijk.'],
        ['title' => 'Snel live', 'text' => 'Van prototype naar productie zonder maandenlange trajecten. Daarna blijf ik betrokken voor onderhoud en de volgende verbeteringen.'],
    ],

    'cases' => [
        [
            'title'   => 'Elk paneel volgen over de productievloer',
            'status'  => 'Lopend project',
            'context' => 'Producent van badkamermeubelen',
            'text'    => 'Een traceability-systeem dat industriële barcodescanners uitleest en vastlegt waar elk paneel zich in de productie bevindt. Het systeem heeft een volledige MCP-server, zodat AI-assistenten rechtstreeks met de productiedata kunnen werken. Vragen als “waar is dit paneel nu?” of “welke orders staan stil?” beantwoordt AI direct uit de bron.',
            'tags'    => ['Industriële scanners', 'Traceability', 'MCP', 'AI-integratie'],
            'link'    => null,
        ],
        [
            'title'   => 'FLEX: asset management met AI',
            'status'  => 'Eigen platform',
            'context' => 'SaaS voor de industrie',
            'text'    => 'Een platform dat ik heb gebouwd voor onderhoud in de industrie: assetbeheer, werkorders, planning en voorraad op één plek. Met AI-gestuurd voorspellend onderhoud worden storingen gesignaleerd voordat ze ontstaan, ook voor kleinere bedrijven.',
            'tags'    => ['Predictive maintenance', 'Machinedata', 'SaaS'],
            'link'    => ['url' => 'https://flex-nexus.nl/', 'label' => 'Bekijk FLEX'],
        ],
    ],
];
