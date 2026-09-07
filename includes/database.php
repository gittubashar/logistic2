<?php

function db_config(): array
{
    return require __DIR__ . '/../config/database.php';
}

function db(): ?PDO
{
    static $pdo = false;

    if ($pdo !== false) {
        return $pdo;
    }

    $config = db_config();
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $config['host'],
        $config['database'],
        $config['charset']
    );

    try {
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (Throwable) {
        $pdo = null;
    }

    return $pdo;
}

function db_is_available(): bool
{
    return db() instanceof PDO;
}

function db_schema_ensure(): bool
{
    static $ready = null;

    if ($ready !== null) {
        return $ready;
    }

    $pdo = db();
    if (!$pdo) {
        return $ready = false;
    }

    $tryAlter = static function (string $sql) use ($pdo): void {
        try {
            $pdo->exec($sql);
        } catch (Throwable) {
        }
    };

    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS logistic_app_settings (
                setting_key VARCHAR(120) PRIMARY KEY,
                setting_value LONGTEXT NOT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS logistic_admin_users (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(160) NOT NULL,
                email VARCHAR(190) NOT NULL UNIQUE,
                phone VARCHAR(80) DEFAULT '',
                designation VARCHAR(120) DEFAULT 'Admin',
                role VARCHAR(50) NOT NULL DEFAULT 'admin',
                image VARCHAR(255) DEFAULT '',
                password_hash VARCHAR(255) NOT NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $tryAlter("ALTER TABLE logistic_admin_users ADD COLUMN name VARCHAR(160) NOT NULL DEFAULT '' AFTER id");
        $tryAlter("ALTER TABLE logistic_admin_users ADD COLUMN email VARCHAR(190) NOT NULL DEFAULT '' AFTER name");
        $tryAlter("ALTER TABLE logistic_admin_users ADD COLUMN phone VARCHAR(80) DEFAULT '' AFTER email");
        $tryAlter("ALTER TABLE logistic_admin_users ADD COLUMN designation VARCHAR(120) DEFAULT 'Admin' AFTER phone");
        $tryAlter("ALTER TABLE logistic_admin_users ADD COLUMN role VARCHAR(50) NOT NULL DEFAULT 'admin' AFTER designation");
        $tryAlter("ALTER TABLE logistic_admin_users ADD COLUMN image VARCHAR(255) DEFAULT '' AFTER role");
        $tryAlter("ALTER TABLE logistic_admin_users ADD COLUMN password_hash VARCHAR(255) NOT NULL DEFAULT '' AFTER image");
        $tryAlter("ALTER TABLE logistic_admin_users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER password_hash");
        $tryAlter("ALTER TABLE logistic_admin_users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER is_active");
        $tryAlter("ALTER TABLE logistic_admin_users ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
        $tryAlter("ALTER TABLE logistic_admin_users ADD UNIQUE KEY logistic_admin_users_email_unique (email)");
        $defaultSuperAdmin = $pdo->prepare('
            INSERT IGNORE INTO logistic_admin_users
                (name, email, phone, designation, role, image, password_hash, is_active)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, 1)
        ');
        $defaultSuperAdmin->execute([
            'Super Admin',
            'me@kbashar.com',
            '',
            'Super Admin',
            'super_admin',
            '',
            '$2a$12$KzCpMKzind15L2VYhvTTaOc1HPKgUO.kFX1BIDs18Sq/coi.Me1nG',
        ]);
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS logistic_team_members (
                id VARCHAR(80) PRIMARY KEY,
                name VARCHAR(190) NOT NULL,
                role VARCHAR(190) DEFAULT '',
                image VARCHAR(255) DEFAULT '',
                bio TEXT,
                sort_order INT NOT NULL DEFAULT 1,
                visible TINYINT(1) NOT NULL DEFAULT 1,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $tryAlter("ALTER TABLE logistic_team_members ADD COLUMN name VARCHAR(190) NOT NULL DEFAULT '' AFTER id");
        $tryAlter("ALTER TABLE logistic_team_members ADD COLUMN role VARCHAR(190) DEFAULT '' AFTER name");
        $tryAlter("ALTER TABLE logistic_team_members ADD COLUMN image VARCHAR(255) DEFAULT '' AFTER role");
        $tryAlter("ALTER TABLE logistic_team_members ADD COLUMN bio TEXT AFTER image");
        $tryAlter("ALTER TABLE logistic_team_members ADD COLUMN sort_order INT NOT NULL DEFAULT 1 AFTER bio");
        $tryAlter("ALTER TABLE logistic_team_members ADD COLUMN visible TINYINT(1) NOT NULL DEFAULT 1 AFTER sort_order");
        $tryAlter("ALTER TABLE logistic_team_members ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER visible");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS logistic_masonry_gallery (
                id VARCHAR(80) PRIMARY KEY,
                title VARCHAR(190) DEFAULT '',
                category VARCHAR(120) DEFAULT '',
                caption TEXT,
                image VARCHAR(255) NOT NULL,
                sort_order INT NOT NULL DEFAULT 1,
                visible TINYINT(1) NOT NULL DEFAULT 1,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $tryAlter("ALTER TABLE logistic_masonry_gallery ADD COLUMN title VARCHAR(190) DEFAULT '' AFTER id");
        $tryAlter("ALTER TABLE logistic_masonry_gallery ADD COLUMN category VARCHAR(120) DEFAULT '' AFTER title");
        $tryAlter("ALTER TABLE logistic_masonry_gallery ADD COLUMN caption TEXT AFTER category");
        $tryAlter("ALTER TABLE logistic_masonry_gallery ADD COLUMN image VARCHAR(255) NOT NULL DEFAULT '' AFTER caption");
        $tryAlter("ALTER TABLE logistic_masonry_gallery ADD COLUMN sort_order INT NOT NULL DEFAULT 1 AFTER image");
        $tryAlter("ALTER TABLE logistic_masonry_gallery ADD COLUMN visible TINYINT(1) NOT NULL DEFAULT 1 AFTER sort_order");
        $tryAlter("ALTER TABLE logistic_masonry_gallery ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER visible");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS logistic_newsletter_subscribers (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(190) NOT NULL UNIQUE,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $tryAlter("ALTER TABLE logistic_newsletter_subscribers ADD COLUMN email VARCHAR(190) NOT NULL DEFAULT '' AFTER id");
        $tryAlter("ALTER TABLE logistic_newsletter_subscribers ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER email");
        $tryAlter("ALTER TABLE logistic_newsletter_subscribers ADD COLUMN subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER is_active");
        $tryAlter("ALTER TABLE logistic_newsletter_subscribers ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER subscribed_at");
        $tryAlter("ALTER TABLE logistic_newsletter_subscribers ADD UNIQUE KEY logistic_newsletter_email_unique (email)");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS logistic_contact_messages (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(190) NOT NULL,
                email VARCHAR(190) NOT NULL,
                service VARCHAR(190) DEFAULT '',
                message TEXT NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'unread',
                reply_subject VARCHAR(255) DEFAULT '',
                reply_message LONGTEXT,
                replied_at DATETIME DEFAULT NULL,
                request_ip VARCHAR(45) DEFAULT '',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX logistic_contact_status_created_index (status, created_at),
                INDEX logistic_contact_email_index (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $tryAlter("ALTER TABLE logistic_contact_messages ADD COLUMN name VARCHAR(190) NOT NULL DEFAULT '' AFTER id");
        $tryAlter("ALTER TABLE logistic_contact_messages ADD COLUMN email VARCHAR(190) NOT NULL DEFAULT '' AFTER name");
        $tryAlter("ALTER TABLE logistic_contact_messages ADD COLUMN service VARCHAR(190) DEFAULT '' AFTER email");
        $tryAlter("ALTER TABLE logistic_contact_messages ADD COLUMN message TEXT NOT NULL AFTER service");
        $tryAlter("ALTER TABLE logistic_contact_messages ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'unread' AFTER message");
        $tryAlter("ALTER TABLE logistic_contact_messages ADD COLUMN reply_subject VARCHAR(255) DEFAULT '' AFTER status");
        $tryAlter("ALTER TABLE logistic_contact_messages ADD COLUMN reply_message LONGTEXT AFTER reply_subject");
        $tryAlter("ALTER TABLE logistic_contact_messages ADD COLUMN replied_at DATETIME DEFAULT NULL AFTER reply_message");
        $tryAlter("ALTER TABLE logistic_contact_messages ADD COLUMN request_ip VARCHAR(45) DEFAULT '' AFTER replied_at");
        $tryAlter("ALTER TABLE logistic_contact_messages ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER request_ip");
        $tryAlter("ALTER TABLE logistic_contact_messages ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS logistic_posts (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(220) NOT NULL,
                slug VARCHAR(220) NOT NULL UNIQUE,
                excerpt TEXT,
                content_html LONGTEXT,
                image VARCHAR(255) DEFAULT '',
                is_published TINYINT(1) NOT NULL DEFAULT 1,
                published_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $tryAlter("ALTER TABLE logistic_posts ADD COLUMN title VARCHAR(220) NOT NULL DEFAULT '' AFTER id");
        $tryAlter("ALTER TABLE logistic_posts ADD COLUMN slug VARCHAR(220) NOT NULL DEFAULT '' AFTER title");
        $tryAlter("ALTER TABLE logistic_posts ADD COLUMN excerpt TEXT AFTER slug");
        $tryAlter("ALTER TABLE logistic_posts ADD COLUMN content_html LONGTEXT AFTER excerpt");
        $tryAlter("ALTER TABLE logistic_posts ADD COLUMN image VARCHAR(255) DEFAULT '' AFTER content_html");
        $tryAlter("ALTER TABLE logistic_posts ADD COLUMN is_published TINYINT(1) NOT NULL DEFAULT 1 AFTER image");
        $tryAlter("ALTER TABLE logistic_posts ADD COLUMN published_at DATETIME DEFAULT CURRENT_TIMESTAMP AFTER is_published");
        $tryAlter("ALTER TABLE logistic_posts ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER published_at");
        $tryAlter("ALTER TABLE logistic_posts ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
        $tryAlter("ALTER TABLE logistic_posts ADD UNIQUE KEY logistic_posts_slug_unique (slug)");
    } catch (Throwable) {
        return $ready = false;
    }

    return $ready = true;
}

function db_json_get(string $key): array
{
    if (!db_schema_ensure()) {
        return [];
    }

    $stmt = db()->prepare('SELECT setting_value FROM logistic_app_settings WHERE setting_key = ? LIMIT 1');
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();

    if (!is_string($value) || $value === '') {
        return [];
    }

    $data = json_decode($value, true);

    return is_array($data) ? $data : [];
}

function db_json_save(string $key, array $value): bool
{
    if (!db_schema_ensure()) {
        return false;
    }

    $stmt = db()->prepare('
        INSERT INTO logistic_app_settings (setting_key, setting_value)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ');

    return $stmt->execute([$key, json_encode($value, JSON_UNESCAPED_SLASHES)]);
}
