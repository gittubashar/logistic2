<?php

function section_is_list(array $array): bool
{
    return array_keys($array) === range(0, count($array) - 1);
}

function section_merge(array $default, array $custom): array
{
    foreach ($custom as $key => $value) {
        if (is_array($value) && isset($default[$key]) && is_array($default[$key]) && !section_is_list($value) && !section_is_list($default[$key])) {
            $default[$key] = section_merge($default[$key], $value);
            continue;
        }

        $default[$key] = $value;
    }

    return $default;
}

function section_settings_file(): string
{
    return __DIR__ . '/../uploads/section-settings.json';
}

function section_defaults(): array
{
    global $site, $services, $team, $offices;

    return [
        'hero' => [
            'label' => 'Hero Section',
            'visible' => true,
            'kicker' => $site['since'],
            'title' => $site['title'],
            'subtitle' => $site['tagline'],
            'image' => 'uploads/hero-bg.svg',
            'buttons' => [
                ['label' => 'Download Company Profile', 'url' => 'uploads/documents/bs-trading-company-profile.pdf', 'style' => 'orange'],
                ['label' => 'Explore Services', 'url' => '#services', 'style' => 'brand'],
                ['label' => 'Request Support', 'url' => 'pages/contact.php', 'style' => 'aqua'],
            ],
            'highlights' => ['Port Agency & Vessel Husbandry', 'Crew Management & Repatriation', 'Customs Brokerage / C&F Agent', 'Stevedoring & Cargo Supervision'],
            'highlight_links' => [
                'pages/shipping-agent.php',
                'pages/sea-freight.php',
                'pages/customs-brokerage.php',
                'pages/project-cargo.php',
            ],
        ],
        'about' => [
            'label' => 'About Section',
            'visible' => true,
            'kicker' => 'About M/S B. S. Trading',
            'title' => 'Your trusted maritime anchor in Bangladesh',
            'image' => 'uploads/about-logistics.svg',
            'profile_document' => '',
            'profile_document_label' => 'Profile Document',
            'paragraphs' => [
                'M/S B. S. TRADING is a premier, fully integrated shipping agency based in Chattogram, Bangladesh. Established in 2018, we specialize in maritime logistics, port agency and comprehensive cargo handling solutions.',
                'We connect local trade with global maritime networks by pairing deep local expertise with international regulatory compliance. Our professional team navigates complex port procedures to support fast, safe and cost-effective vessel turnarounds.',
                'Because global trade never stops, our dedicated team provides uninterrupted 24/7/365 support for shipowners, charterers and cargo operators throughout the Bay of Bengal.',
            ],
            'button_label' => 'Read Company Profile',
            'button_url' => 'pages/about.php',
        ],
        'services' => [
            'label' => 'Services Section',
            'visible' => true,
            'kicker' => 'Services',
            'title' => 'Shipping agency, maritime logistics and cargo operations from Chattogram.',
            'items' => $services,
        ],
        'team' => [
            'label' => 'Team Section',
            'visible' => true,
            'kicker' => 'Team',
            'title' => 'Leadership behind ' . $site['title'],
            'items' => $team,
        ],
        'company_profile' => [
            'label' => 'About Profile Strip',
            'visible' => true,
            'kicker' => 'Licensed & Memberships',
            'title' => '',
            'image' => 'uploads/page-header-bg.svg',
            'profile_document' => 'uploads/documents/bs-trading-company-profile.pdf',
            'profile_document_label' => 'Profile Document',
            'items' => ['Licensed Customs Shipping Agent', 'Customs Brokerage / C&F Agent', 'Bangladesh Shipping Agent Association (BSSA)', 'Authorized shipping agency services across Bangladesh'],
        ],
        'masonry_gallery' => [
            'label' => 'Masonry Gallery',
            'visible' => true,
            'kicker' => 'Gallery',
            'title' => 'Company Operations Gallery',
        ],
        'office' => [
            'label' => 'Office Section',
            'visible' => true,
            'image' => 'uploads/about-logistics.svg',
            'items' => $offices,
        ],
    ];
}

function saved_section_settings(): array
{
    $dbSettings = db_json_get('section_settings');
    if ($dbSettings) {
        return $dbSettings;
    }

    $file = section_settings_file();

    if (!is_file($file)) {
        return [];
    }

    $json = file_get_contents($file);
    $data = json_decode((string) $json, true);

    return is_array($data) ? $data : [];
}

function all_section_content(): array
{
    $saved = saved_section_settings();
    $sections = section_merge(section_defaults(), $saved);

    if (empty($saved['about']['profile_document']) && !empty($saved['company_profile']['profile_document'])) {
        $sections['about']['profile_document'] = $saved['company_profile']['profile_document'];
        $sections['about']['profile_document_label'] = $saved['company_profile']['profile_document_label'] ?? 'Profile Document';
    }

    return $sections;
}

function section_content(string $key): array
{
    $sections = all_section_content();

    return $sections[$key] ?? [];
}

function section_clean_html(string $html): string
{
    $allowedTags = '<div><p><br><strong><b><em><i><u><ul><ol><li><blockquote><a><span>';

    return strip_tags($html, $allowedTags);
}

function section_rich_text(string $value): string
{
    return section_clean_html($value);
}
