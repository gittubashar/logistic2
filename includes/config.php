<?php
if (PHP_SAPI === 'cli') {
    $_SESSION = $_SESSION ?? [];
} elseif (session_status() === PHP_SESSION_NONE) {
    $secureCookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secureCookie,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_is_valid(): bool
{
    $token = (string) ($_POST['csrf_token'] ?? '');

    return $token !== '' && hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token);
}

function require_csrf(): void
{
    if (!csrf_is_valid()) {
        http_response_code(419);
        exit('Invalid session token. Please refresh the page and try again.');
    }
}

require_once __DIR__ . '/database.php';

function base_url(string $path = ''): string
{
    $path = trim($path);

    if ($path === '') {
        return project_base_path() . '/';
    }

    if ($path === '#' || str_starts_with($path, '#') || preg_match('#^(https?:)?//#i', $path) || preg_match('#^(mailto|tel):#i', $path)) {
        return $path;
    }

    return project_base_path() . '/' . ltrim($path, '/');
}

function project_base_path(): string
{
    static $basePath = null;

    if ($basePath !== null) {
        return $basePath;
    }

    $projectRoot = realpath(__DIR__ . '/..');
    $documentRoot = realpath((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));

    if ($projectRoot && $documentRoot && str_starts_with(strtolower($projectRoot), strtolower($documentRoot))) {
        $basePath = str_replace('\\', '/', substr($projectRoot, strlen($documentRoot)));
        $basePath = '/' . trim($basePath, '/');
        $basePath = $basePath === '/' ? '' : $basePath;

        return $basePath;
    }

    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $scriptDir = trim(dirname($scriptName), '/');
    $parts = $scriptDir === '' || $scriptDir === '.' ? [] : explode('/', $scriptDir);

    if ($parts && in_array(end($parts), ['pages', 'dashboard'], true)) {
        array_pop($parts);
    }

    $basePath = $parts ? '/' . implode('/', $parts) : '';

    return $basePath;
}

$site = [
    'title' => 'M/S B. S. TRADING',
    'since' => 'Since 2018',
    'tagline' => 'Shipping Agent | Logistics | Transportation',
    'phone' => '+880 1954194762',
    'mobile' => '+880 1552717809',
    'whatsapp' => '+880 1954194762',
    'email' => 'contact@bstradingship.com',
    'logo_text' => 'BS',
    'copyright' => 'Copyright (c) ' . date('Y') . ' M/S B. S. TRADING. All rights reserved.',
    'socials' => [
        'facebook' => '#',
        'linkedin' => '#',
        'instagram' => '#',
        'x-twitter' => '#',
    ],
];

$services = [
    [
        'slug' => 'shipping-agent',
        'title' => 'Port Agency & Vessel Husbandry',
        'icon' => 'fa-anchor',
        'image' => 'uploads/service-shipping-agent.svg',
        'text' => 'Port agency and vessel husbandry support for smooth arrivals, clearances, supplies and turnarounds at Chattogram.',
        'detail_title' => 'A dependable port-side operating partner',
        'detail_paragraphs' => [
            'M/S B. S. TRADING provides a comprehensive suite of port agency and vessel husbandry services for shipowners and charterers calling at Chattogram.',
            'We support pre-arrival planning, inward and outward port clearances, coordination with the Chittagong Port Authority, marine bunkers, freshwater, lubricants and ship provisions.',
            'Our team keeps communication clear between ship masters and port officials, while coordinating technical support, local workshops, underwater surveys and ship-spares clearance when required.',
        ],
        'features' => ['Pre-arrival and port clearance', 'Marine supplies and provisions', 'Technical and repair coordination', 'Ship-spares customs support'],
        'process' => ['Pre-arrival planning', 'Port and authority coordination', 'Husbandry execution', 'Departure follow-up'],
        'best_for' => ['Shipowners', 'Charterers', 'Vessel operators', 'Port calls at Chattogram'],
        'support' => ['Vessel clearance', 'Bunker and freshwater supply', 'Repair and survey coordination', 'Port status reporting'],
    ],
    [
        'slug' => 'sea-freight',
        'title' => 'Crew Management & Repatriation',
        'icon' => 'fa-people-arrows',
        'image' => 'uploads/service-sea-freight.svg',
        'text' => 'Practical crew-change, immigration, medical, travel and repatriation support at port and outer anchorage.',
        'detail_title' => 'Safe, compliant crew movement from port to home',
        'detail_paragraphs' => [
            'M/S B. S. TRADING manages crew changes efficiently to maintain smooth vessel operations and crew welfare at Chattogram port and outer anchorage.',
            'We coordinate OK-to-Board letters, visa-on-arrival processing, sign-on and sign-off logistics, shore passes and safe launch transfers to and from the outer anchorage.',
            'Our support also covers medical and dental assistance, emergency evacuation, hotel accommodation, domestic ticketing and final international repatriation in line with immigration requirements.',
        ],
        'features' => ['Immigration and paperwork', 'Outer anchorage transfers', 'Medical and dental support', 'Travel and repatriation logistics'],
        'process' => ['Crew-change instruction', 'Document and immigration review', 'Transfer and welfare support', 'Repatriation confirmation'],
        'best_for' => ['Crew changes', 'Sign-on and sign-off', 'Medical assistance', 'International repatriation'],
        'support' => ['Visa and shore-pass support', 'Launch transfer coordination', 'Hotel and ticketing', 'Emergency response'],
    ],
    [
        'slug' => 'road-transport',
        'title' => 'Inland Shipping & Road Transport Logistics',
        'icon' => 'fa-truck-ramp-box',
        'image' => 'uploads/service-road-transport.svg',
        'text' => 'Multimodal inland movement through dependable road fleets, river networks and secure last-mile delivery.',
        'detail_title' => 'One inland network for the next leg of cargo',
        'detail_paragraphs' => [
            'We combine domestic road transport with strategic inland shipping across Bangladesh to create a practical, cost-aware movement plan after cargo leaves the port.',
            'Our network supports FCL and LCL containers, break-bulk and heavy-lift project cargo using prime movers, flatbed trailers, covered vans, inland container terminals and river ports.',
            'By integrating land fleets with riverine barging, we help bypass congestion, reduce domestic transport cost and deliver cargo safely to industrial hubs and final destinations.',
        ],
        'features' => ['Domestic trucking and inland shipping', 'Cross-border road freight', 'Project and heavy-lift movement', 'First-mile and last-mile delivery'],
        'process' => ['Cargo and route review', 'Mode and equipment planning', 'Movement monitoring', 'Secure final delivery'],
        'best_for' => ['Containerized cargo', 'Industrial project cargo', 'Bulk movement', 'Port-to-destination delivery'],
        'support' => ['Fleet and trailer planning', 'River route coordination', 'Heavy-lift supervision', 'Delivery reporting'],
    ],
    [
        'slug' => 'customs-brokerage',
        'title' => 'Customs Brokerage / C&F Agent',
        'icon' => 'fa-file-contract',
        'image' => 'uploads/service-customs-brokerage.svg',
        'text' => 'Regulatory expertise for HS classification, valuation, documentation, assessment and friction-free cargo clearance.',
        'detail_title' => 'Clear customs with confidence and control',
        'detail_paragraphs' => [
            'M/S B. S. TRADING provides customs clearing and forwarding support at Chattogram Port, Dhaka Airport, Mongla Port and key land customs stations including Benapole.',
            'Our brokerage specialists manage HS code classification, import and export valuation, duty optimization, Bills of Entry, Bills of Export, inspections and mandatory regulatory certificates.',
            'We proactively resolve documentation queries before cargo reaches the border, reducing demurrage risk and supporting rapid, compliant release.',
        ],
        'features' => ['HS code classification', 'Duty and tax calculation', 'Bills of Entry and Export', 'Regulatory certificate support'],
        'process' => ['Document and cargo review', 'Classification and valuation', 'Customs submission', 'Assessment and release'],
        'best_for' => ['Importers and exporters', 'Regulated cargo', 'Port and land customs', 'Document-sensitive shipments'],
        'support' => ['C&F documentation', 'Customs assessment', 'Inspection coordination', 'Query and dispute follow-up'],
    ],
    [
        'slug' => 'warehousing-vas',
        'title' => 'Customs Documentation & Cargo Clearance',
        'icon' => 'fa-file-shield',
        'image' => 'uploads/service-warehouse.svg',
        'text' => 'Manifest filing, port dues, financial calculations and proactive documentation control for compliant clearance.',
        'detail_title' => 'Documentation that keeps cargo moving',
        'detail_paragraphs' => [
            'Our specialized customs desk takes charge of inward and outward cargo manifests, port dues, financial calculations and complex import-export documentation.',
            'We work closely with cargo surveyors, terminal managers and customs officials to resolve administrative discrepancies quickly and protect clients from unnecessary delay, fines or demurrage.',
            'With responsive document control and transparent reporting, clients receive a clear view of cargo status from filing through clearance.',
        ],
        'features' => ['Manifest filing', 'Port dues and financial calculations', 'Expedited clearance', 'Dispute resolution'],
        'process' => ['Document collection', 'Manifest and calculation', 'Authority coordination', 'Clearance confirmation'],
        'best_for' => ['Port-call cargo', 'Import and export documents', 'Cargo release support', 'Operational compliance'],
        'support' => ['Manifest preparation', 'Document verification', 'Terminal coordination', 'Status updates'],
    ],
    [
        'slug' => 'project-cargo',
        'title' => 'Stevedoring & Cargo Supervision',
        'icon' => 'fa-people-carry-box',
        'image' => 'uploads/service-project-cargo.svg',
        'text' => 'On-site monitoring, flow optimization and accountable cargo supervision for safe port and project operations.',
        'detail_title' => 'Hands-on supervision where cargo meets the quay',
        'detail_paragraphs' => [
            'Our stevedoring and cargo supervision service brings disciplined oversight to vessel operations, cargo handling and complex port-side movements.',
            'We focus on versatile oversight, on-site monitoring, flow optimization and indisputable documentation so each operating party understands the work, timing and handover condition.',
            'For project cargo and heavy-lift operations, our team coordinates the practical details that protect cargo, crew, equipment and delivery schedules.',
        ],
        'features' => ['On-site cargo supervision', 'Handling and flow monitoring', 'Heavy-lift coordination', 'Operational documentation'],
        'process' => ['Operation briefing', 'Site and equipment review', 'Supervised handling', 'Handover reporting'],
        'best_for' => ['Port cargo operations', 'Break-bulk cargo', 'Project and heavy-lift cargo', 'Government contracts'],
        'support' => ['Stevedore coordination', 'Cargo tally and records', 'Safety and flow monitoring', 'Completion reporting'],
    ],
];

function service_by_slug(string $slug): ?array
{
    global $services;

    foreach ($services as $service) {
        if ($service['slug'] === $slug) {
            return $service;
        }
    }

    return null;
}

$team = [
    ['name' => 'Mr. Pankaj Kanti Das', 'role' => 'Proprietor & CEO', 'image' => '', 'text' => 'Leads the company with a focus on professional port agency, maritime logistics, client trust and reliable execution at Chattogram.'],
    ['name' => 'Mrs. Deaboshree Das', 'role' => 'Director', 'image' => '', 'text' => 'Supports business coordination, operational discipline and the company’s commitment to transparent, client-focused service.'],
    ['name' => 'Mr. Utsha Das', 'role' => 'Director', 'image' => '', 'text' => 'Supports logistics planning, stakeholder coordination and the continued development of dependable maritime services.'],
    ['name' => 'Miss Arsha Das', 'role' => 'Director', 'image' => '', 'text' => 'Contributes to the company’s long-term vision, service quality and responsive support for international principals.'],
];

require_once __DIR__ . '/team-members.php';
$team = team_members($team);

$concerns = [
    ['title' => 'BANDARBAN AGENCY'],
    ['title' => 'M/S PURABI RICE AGENCY'],
    ['title' => 'M/S BANDARBAN AUTO RICE MILL'],
    ['title' => 'M/S SUKHENDU BIKASH DAS'],
    ['title' => 'M/S SHAROTHI ENTERPRISE'],
    ['title' => 'PURABI CHAIR COACH'],
    ['title' => 'PURABI TRANSPORT AGENCY'],
    ['title' => 'HOTEL HILL BIRD'],
    ['title' => 'HOTEL PURABI'],
    ['title' => 'HOTEL HILL VIEW'],
];

require_once __DIR__ . '/our-concerns.php';
$concerns = our_concerns($concerns);

$offices = [
    'Chattogram Office' => '1200 Haji Sobhan Soudagar Road, Chaktai, Chattogram, Bangladesh.',
];

require_once __DIR__ . '/section-settings.php';
require_once __DIR__ . '/site-settings.php';
$site = all_site_settings(site_setting_defaults($site));
require_once __DIR__ . '/office-addresses.php';
$officeContacts = all_office_addresses($offices, $site);
$offices = office_address_map($officeContacts);
require_once __DIR__ . '/masonry-gallery.php';
require_once __DIR__ . '/page-settings.php';
require_once __DIR__ . '/admin-profile.php';
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/newsletter.php';
require_once __DIR__ . '/contact-messages.php';
require_once __DIR__ . '/posts.php';
require_once __DIR__ . '/membership-certificates.php';
