<?php

function site_settings_file(): string
{
    return __DIR__ . '/../uploads/site-settings.json';
}

function site_setting_defaults(array $site): array
{
    return [
        'title' => $site['title'],
        'since' => $site['since'],
        'tagline' => $site['tagline'],
        'phone' => $site['phone'],
        'mobile' => $site['mobile'],
        'whatsapp' => $site['whatsapp'],
        'email' => $site['email'],
        'logo_text' => $site['logo_text'],
        'logo_image' => '',
        'footer_logo_image' => '',
        'favicon' => '',
        'meta_title' => $site['title'],
        'meta_description' => $site['tagline'],
        'meta_keywords' => 'freight forwarding, logistics, customs, shipping agent, Bangladesh',
        'copyright' => $site['copyright'],
        'socials' => $site['socials'],
        'smtp' => [
            'host' => '',
            'port' => '587',
            'username' => '',
            'password' => '',
            'encryption' => 'tls',
            'from_email' => $site['email'],
            'from_name' => $site['title'],
        ],
    ];
}

function saved_site_settings(): array
{
    $dbSettings = db_json_get('site_settings');
    if ($dbSettings) {
        return $dbSettings;
    }

    $file = site_settings_file();

    if (!is_file($file)) {
        return [];
    }

    $data = json_decode((string) file_get_contents($file), true);

    return is_array($data) ? $data : [];
}

function all_site_settings(array $defaults): array
{
    return section_merge($defaults, saved_site_settings());
}

function save_site_settings(array $settings): bool
{
    if (db_json_save('site_settings', $settings)) {
        return true;
    }

    $file = site_settings_file();
    $directory = dirname($file);

    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    return file_put_contents($file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
}
