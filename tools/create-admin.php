<?php
require_once __DIR__ . '/../includes/config.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

if (!db_schema_ensure()) {
    fwrite(STDERR, "Database is not ready. Run: php tools/migrate-database.php\n");
    exit(1);
}

$email = trim($argv[1] ?? '');
$password = (string) ($argv[2] ?? '');
$name = trim($argv[3] ?? 'Administrator');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 10) {
    fwrite(STDERR, "Usage: php tools/create-admin.php admin@example.com StrongPassword123 \"Administrator\"\n");
    fwrite(STDERR, "Password must be at least 10 characters.\n");
    exit(1);
}

$profile = admin_profile();
$profile['id'] = 0;
$profile['name'] = $name;
$profile['email'] = $email;
$profile['password_hash'] = password_hash($password, PASSWORD_DEFAULT);

if (!save_admin_profile($profile)) {
    fwrite(STDERR, "Unable to save admin profile.\n");
    exit(1);
}

fwrite(STDOUT, "Admin profile created for {$email}.\n");
