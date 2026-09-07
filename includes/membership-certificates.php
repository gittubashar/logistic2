<?php

function membership_certificate_defaults(): array
{
    return [
        'kicker' => 'Licensed & Memberships',
        'title' => 'Credentials behind maritime reliability',
        'subtitle' => 'Licenses and affiliations supporting M/S B. S. TRADING shipping agency and customs operations.',
        'items' => [
            [
                'id' => 'license-shipping-agent',
                'title' => 'Licensed Customs Shipping Agent',
                'type' => 'Membership',
                'issuer' => 'Authorized across Bangladesh',
                'date' => 'Active',
                'description' => 'Authorized to provide shipping agency services across Bangladesh.',
                'image' => '',
                'document' => '',
                'visible' => true,
            ],
            [
                'id' => 'membership-bssa',
                'title' => 'Bangladesh Shipping Agent Association (BSSA)',
                'type' => 'Membership',
                'issuer' => 'BSSA',
                'date' => 'Active',
                'description' => 'Member of the Bangladesh Shipping Agent Association.',
                'image' => '',
                'document' => '',
                'visible' => true,
            ],
            [
                'id' => 'credential-cf',
                'title' => 'Customs Brokerage / C&F Agent',
                'type' => 'Credential',
                'issuer' => 'M/S B. S. TRADING',
                'date' => 'Active',
                'description' => 'Customs brokerage and clearing & forwarding support for compliant cargo movement.',
                'image' => '',
                'document' => '',
                'visible' => true,
            ],
            [
                'id' => 'credential-shipping-agency',
                'title' => 'Shipping Agency Services',
                'type' => 'Service Credential',
                'issuer' => 'M/S B. S. TRADING',
                'date' => 'Since 2018',
                'description' => 'Port agency, vessel husbandry and maritime logistics support from Chattogram.',
                'image' => '',
                'document' => '',
                'visible' => true,
            ],
        ],
    ];
}

function membership_certificate_id(): string
{
    return 'certificate-' . bin2hex(random_bytes(5));
}

function normalize_membership_certificate(array $item, int $index = 0): array
{
    $title = trim((string) ($item['title'] ?? ''));

    return [
        'id' => trim((string) ($item['id'] ?? '')) ?: 'certificate-' . substr(md5($title . $index), 0, 10),
        'title' => $title,
        'type' => trim((string) ($item['type'] ?? 'Certificate')),
        'issuer' => trim((string) ($item['issuer'] ?? '')),
        'date' => trim((string) ($item['date'] ?? '')),
        'description' => trim((string) ($item['description'] ?? '')),
        'image' => trim((string) ($item['image'] ?? '')),
        'document' => trim((string) ($item['document'] ?? '')),
        'visible' => (bool) ($item['visible'] ?? true),
    ];
}

function membership_certificates(bool $visibleOnly = true): array
{
    $settings = section_merge(membership_certificate_defaults(), db_json_get('membership_certificates'));
    $items = $settings['items'] ?? [];

    $items = array_map(
        fn (array $item, int $index): array => normalize_membership_certificate($item, $index),
        is_array($items) ? $items : [],
        array_keys(is_array($items) ? $items : [])
    );

    if ($visibleOnly) {
        $items = array_values(array_filter($items, fn (array $item): bool => $item['visible']));
    }

    $settings['items'] = $items;

    return $settings;
}

function save_membership_certificates(array $settings): bool
{
    $settings['items'] = array_values(array_map(
        fn (array $item, int $index): array => normalize_membership_certificate($item, $index),
        $settings['items'] ?? [],
        array_keys($settings['items'] ?? [])
    ));

    return db_json_save('membership_certificates', $settings);
}
