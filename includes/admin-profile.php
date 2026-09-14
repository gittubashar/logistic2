<?php

function admin_profile_file(): string
{
    return __DIR__ . '/../uploads/admin-profile.json';
}

function default_admin_profile(): array
{
    return [
        'name' => 'Super Admin',
        'email' => 'me@kbashar.com',
        'phone' => '',
        'designation' => 'Super Admin',
        'role' => 'super_admin',
        'image' => '',
        'password_hash' => '$2a$12$KzCpMKzind15L2VYhvTTaOc1HPKgUO.kFX1BIDs18Sq/coi.Me1nG',
    ];
}

function admin_profile(): array
{
    if (db_schema_ensure()) {
        $id = (int) ($_SESSION['admin_user_id'] ?? 0);
        $profile = $id > 0 ? admin_user_by_id($id) : admin_first_user();

        if ($profile) {
            return array_merge(default_admin_profile(), $profile);
        }

        return default_admin_profile();
    }

    $file = admin_profile_file();

    if (!is_file($file)) {
        return default_admin_profile();
    }

    $data = json_decode((string) file_get_contents($file), true);

    return is_array($data) ? array_merge(default_admin_profile(), $data) : default_admin_profile();
}

function save_admin_profile(array $profile): bool
{
    if (db_schema_ensure()) {
        $id = (int) ($profile['id'] ?? ($_SESSION['admin_user_id'] ?? 0));

        if ($id > 0) {
            $stmt = db()->prepare('
                UPDATE logistic_admin_users
                SET name = ?, email = ?, phone = ?, designation = ?, image = ?, password_hash = ?
                WHERE id = ?
            ');

            return $stmt->execute([
                $profile['name'] ?? '',
                $profile['email'] ?? '',
                $profile['phone'] ?? '',
                $profile['designation'] ?? 'Admin',
                $profile['image'] ?? '',
                $profile['password_hash'] ?? '',
                $id,
            ]);
        }

        $stmt = db()->prepare('
            INSERT INTO logistic_admin_users (name, email, phone, designation, image, password_hash, is_active)
            VALUES (?, ?, ?, ?, ?, ?, 1)
            ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                phone = VALUES(phone),
                designation = VALUES(designation),
                image = VALUES(image),
                password_hash = VALUES(password_hash),
                is_active = 1
        ');

        return $stmt->execute([
            $profile['name'] ?? '',
            $profile['email'] ?? '',
            $profile['phone'] ?? '',
            $profile['designation'] ?? 'Admin',
                $profile['image'] ?? '',
            $profile['password_hash'] ?? '',
        ]);
    }

    $file = admin_profile_file();
    $directory = dirname($file);

    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    return file_put_contents($file, json_encode($profile, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
}

function admin_user_by_email(string $email): ?array
{
    if (!db_schema_ensure()) {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM logistic_admin_users WHERE LOWER(email) = LOWER(?) AND is_active = 1 LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    return is_array($user) ? $user : null;
}

function admin_user_by_id(int $id): ?array
{
    if (!db_schema_ensure()) {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM logistic_admin_users WHERE id = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    return is_array($user) ? $user : null;
}

function admin_first_user(): ?array
{
    if (!db_schema_ensure()) {
        return null;
    }

    $user = db()->query('SELECT * FROM logistic_admin_users WHERE is_active = 1 ORDER BY id ASC LIMIT 1')->fetch();

    return is_array($user) ? $user : null;
}

function admin_is_super_admin(): bool
{
    if (empty($_SESSION['admin_logged_in'])) {
        return false;
    }

    if (db_schema_ensure()) {
        $id = (int) ($_SESSION['admin_user_id'] ?? 0);
        $user = $id > 0 ? admin_user_by_id($id) : null;

        return is_array($user) && ($user['role'] ?? '') === 'super_admin';
    }

    return ($_SESSION['admin_role'] ?? '') === 'super_admin';
}

function require_super_admin(): void
{
    if (!admin_is_super_admin()) {
        http_response_code(403);
        exit('Super Admin access required.');
    }
}

function admin_users_all(): array
{
    if (!db_schema_ensure()) {
        return [];
    }

    return db()->query('SELECT id, name, email, phone, designation, role, image, is_active, created_at, updated_at FROM logistic_admin_users ORDER BY role DESC, name ASC')->fetchAll() ?: [];
}

function admin_user_save_record(array $user, ?int $id = null): bool
{
    if (!db_schema_ensure()) {
        return false;
    }

    $name = trim((string) ($user['name'] ?? ''));
    $email = trim((string) ($user['email'] ?? ''));
    $phone = trim((string) ($user['phone'] ?? ''));
    $designation = trim((string) ($user['designation'] ?? 'Admin')) ?: 'Admin';
    $role = in_array(($user['role'] ?? 'admin'), ['admin', 'editor', 'super_admin'], true) ? $user['role'] : 'admin';
    $image = trim((string) ($user['image'] ?? ''));
    $active = !empty($user['is_active']) ? 1 : 0;
    $passwordHash = (string) ($user['password_hash'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    try {
        if ($id !== null && $id > 0) {
            $sql = 'UPDATE logistic_admin_users SET name = ?, email = ?, phone = ?, designation = ?, role = ?, image = ?, is_active = ?';
            $params = [$name, $email, $phone, $designation, $role, $image, $active];
            if ($passwordHash !== '') {
                $sql .= ', password_hash = ?';
                $params[] = $passwordHash;
            }
            $sql .= ' WHERE id = ?';
            $params[] = $id;

            return db()->prepare($sql)->execute($params);
        }

        if ($passwordHash === '') {
            return false;
        }

        return db()->prepare('INSERT INTO logistic_admin_users (name, email, phone, designation, role, image, password_hash, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')->execute([$name, $email, $phone, $designation, $role, $image, $passwordHash, $active]);
    } catch (Throwable) {
        return false;
    }
}

function admin_user_delete(int $id): bool
{
    if (!db_schema_ensure() || $id < 1 || $id === (int) ($_SESSION['admin_user_id'] ?? 0)) {
        return false;
    }

    $lookup = db()->prepare('SELECT role, is_active FROM logistic_admin_users WHERE id = ? LIMIT 1');
    $lookup->execute([$id]);
    $user = $lookup->fetch();
    if (!is_array($user)) {
        return false;
    }

    if (($user['role'] ?? '') === 'super_admin') {
        $activeSuperAdmins = (int) db()->query("SELECT COUNT(*) FROM logistic_admin_users WHERE role = 'super_admin' AND is_active = 1")->fetchColumn();
        if ($activeSuperAdmins <= 1) {
            return false;
        }
    }

    return db()->prepare('DELETE FROM logistic_admin_users WHERE id = ?')->execute([$id]);
}

function dashboard_upload_is_valid(array $file, ?string $requiredExtension = null): bool
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return false;
    }

    $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'ico', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'mp4', 'webm'];

    if (!in_array($extension, $allowed, true) || ($requiredExtension !== null && $extension !== strtolower($requiredExtension))) {
        return false;
    }

    if ((int) ($file['size'] ?? 0) > 100 * 1024 * 1024) {
        return false;
    }

    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array($extension, $imageExtensions, true) && @getimagesize($file['tmp_name']) === false) {
        return false;
    }

    return true;
}

function upload_dashboard_file(array $file, string $folder = 'gallery'): ?string
{
    if (!dashboard_upload_is_valid($file)) {
        return null;
    }

    $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));

    $directory = __DIR__ . '/../uploads/' . trim($folder, '/');

    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    $filename = date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
    $target = $directory . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return null;
    }

    $relativePath = 'uploads/' . trim($folder, '/') . '/' . $filename;

    if (trim($folder, '/') !== 'gallery') {
        $galleryDirectory = __DIR__ . '/../uploads/gallery';

        if (!is_dir($galleryDirectory)) {
            mkdir($galleryDirectory, 0775, true);
        }

        copy($target, $galleryDirectory . '/' . $filename);
    }

    return $relativePath;
}

function replace_dashboard_file(array $file, string $target): bool
{
    $target = realpath($target) ?: '';
    if ($target === '' || !is_file($target)) {
        return false;
    }

    $extension = strtolower(pathinfo($target, PATHINFO_EXTENSION));
    if (!dashboard_upload_is_valid($file, $extension)) {
        return false;
    }

    $temporaryTarget = dirname($target) . DIRECTORY_SEPARATOR . '.replacement-' . bin2hex(random_bytes(6)) . '.' . $extension;
    if (!move_uploaded_file((string) $file['tmp_name'], $temporaryTarget)) {
        return false;
    }

    $replaced = copy($temporaryTarget, $target);
    unlink($temporaryTarget);

    return $replaced;
}
