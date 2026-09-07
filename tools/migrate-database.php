<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$config = require __DIR__ . '/../config/database.php';

try {
    $server = new PDO(
        sprintf('mysql:host=%s;charset=%s', $config['host'], $config['charset']),
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $database = str_replace('`', '``', $config['database']);
    $server->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET {$config['charset']} COLLATE {$config['charset']}_unicode_ci");
} catch (Throwable $error) {
    fwrite(STDERR, 'Unable to create/connect database: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}

require_once __DIR__ . '/../includes/config.php';

if (!db_schema_ensure()) {
    fwrite(STDERR, "Unable to prepare database tables.\n");
    exit(1);
}

function migration_json_file(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $data = json_decode((string) file_get_contents($path), true);

    return is_array($data) ? $data : [];
}

function migrate_about_profile_document(array $sectionSettings): array
{
    $legacyDocument = $sectionSettings['company_profile']['profile_document'] ?? '';

    if ($legacyDocument !== '' && empty($sectionSettings['about']['profile_document'])) {
        $sectionSettings['about']['profile_document'] = $legacyDocument;
        $sectionSettings['about']['profile_document_label'] = $sectionSettings['company_profile']['profile_document_label'] ?? 'Profile Document';
    }

    return $sectionSettings;
}

$siteSettings = migration_json_file(__DIR__ . '/../uploads/site-settings.json');
if ($siteSettings) {
    save_site_settings($siteSettings);
}

$pageSettings = migration_json_file(__DIR__ . '/../uploads/page-settings.json');
if ($pageSettings) {
    save_page_settings($pageSettings);
}

$sectionSettings = migration_json_file(__DIR__ . '/../uploads/section-settings.json');
if ($sectionSettings) {
    db_json_save('section_settings', migrate_about_profile_document($sectionSettings));
} else {
    $sectionSettings = db_json_get('section_settings');
    if ($sectionSettings) {
        db_json_save('section_settings', migrate_about_profile_document($sectionSettings));
    }
}

$officeAddresses = migration_json_file(__DIR__ . '/../uploads/office-addresses.json');
if ($officeAddresses) {
    save_office_addresses($officeAddresses);
} elseif (!db_json_get('office_addresses')) {
    save_office_addresses(default_office_addresses($offices, $site));
}

$membershipCertificates = migration_json_file(__DIR__ . '/../uploads/membership-certificates.json');
if ($membershipCertificates) {
    save_membership_certificates($membershipCertificates);
} elseif (!db_json_get('membership_certificates')) {
    save_membership_certificates(membership_certificate_defaults());
}

$teamMembers = migration_json_file(__DIR__ . '/../uploads/team-members.json');
if ($teamMembers) {
    save_team_members($teamMembers);
} else {
    save_team_members(default_team_members($team));
}

$masonryItems = migration_json_file(__DIR__ . '/../uploads/masonry-gallery.json');
if ($masonryItems) {
    save_masonry_gallery_items($masonryItems);
} else {
    save_masonry_gallery_items(default_masonry_gallery_items());
}

$adminProfile = migration_json_file(__DIR__ . '/../uploads/admin-profile.json');
if (!empty($adminProfile['email']) && !empty($adminProfile['password_hash'])) {
    $adminProfile['id'] = 0;
    save_admin_profile($adminProfile);
}

$posts = migration_json_file(__DIR__ . '/../uploads/posts.json');
if ($posts) {
    foreach ($posts as $post) {
        if (is_array($post)) {
            post_save($post);
        }
    }
}

fwrite(STDOUT, "Database migration completed.\n");
