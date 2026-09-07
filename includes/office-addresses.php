<?php

function default_office_addresses(array $officeMap, array $site): array
{
    $officeMap = $officeMap ?: ['Chattogram Office' => '1200 Haji Sobhan Soudagar Road, Chaktai, Chattogram, Bangladesh.'];

    return array_map(
        static function (string $address, string $title) use ($site): array {
            return [
                'id' => 'office_' . substr(md5($title), 0, 10),
                'title' => $title,
                'address' => $address,
                'phone_1' => $site['phone'] ?? '',
                'phone_2' => $site['mobile'] ?? '',
                'phone_3' => '',
                'email' => $site['email'] ?? '',
                'email_2' => $site['email_2'] ?? 'sales@bstradingship.com',
                'email_3' => $site['email_3'] ?? 'bstrading1200@gmail.com',
                'whatsapp' => $site['whatsapp'] ?? ($site['mobile'] ?? ''),
                'map_embed_code' => '',
                'visible' => true,
            ];
        },
        array_values($officeMap),
        array_keys($officeMap)
    );
}

function saved_office_addresses(): array
{
    $dbSettings = db_json_get('office_addresses');

    return is_array($dbSettings) ? $dbSettings : [];
}

function all_office_addresses(array $officeMap, array $site): array
{
    $defaults = default_office_addresses($officeMap, $site);
    $saved = saved_office_addresses();

    if (!$saved) {
        return $defaults;
    }

    foreach ($defaults as $index => $default) {
        if (isset($saved[$index]) && is_array($saved[$index])) {
            $saved[$index] = array_replace($default, $saved[$index]);
        }
    }

    return array_values(array_map(
        fn (array $office): array => array_replace([
            'id' => '',
            'title' => '',
            'address' => '',
            'phone_1' => '',
            'phone_2' => '',
            'phone_3' => '',
            'email' => '',
            'email_2' => '',
            'email_3' => '',
            'whatsapp' => '',
            'map_embed_code' => '',
            'visible' => true,
        ], $office),
        $saved
    ));
}

function save_office_addresses(array $offices): bool
{
    return db_json_save('office_addresses', array_values($offices));
}

function office_address_id(): string
{
    return 'office_' . bin2hex(random_bytes(4));
}

function office_address_map(array $officeContacts): array
{
    $map = [];

    foreach ($officeContacts as $office) {
        if (!($office['visible'] ?? true)) {
            continue;
        }

        $title = trim((string) ($office['title'] ?? ''));
        $address = trim((string) ($office['address'] ?? ''));

        if ($title !== '') {
            $map[$title] = $address;
        }
    }

    return $map;
}
