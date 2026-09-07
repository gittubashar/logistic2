<?php

function newsletter_subscribers(bool $activeOnly = false): array
{
    if (!db_schema_ensure()) {
        return [];
    }

    $sql = 'SELECT * FROM logistic_newsletter_subscribers';
    if ($activeOnly) {
        $sql .= ' WHERE is_active = 1';
    }
    $sql .= ' ORDER BY subscribed_at DESC';

    return db()->query($sql)->fetchAll() ?: [];
}

function newsletter_subscribe(string $email): bool
{
    $email = strtolower(trim($email));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !db_schema_ensure()) {
        return false;
    }

    $stmt = db()->prepare('
        INSERT INTO logistic_newsletter_subscribers (email, is_active)
        VALUES (?, 1)
        ON DUPLICATE KEY UPDATE is_active = 1, updated_at = CURRENT_TIMESTAMP
    ');

    return $stmt->execute([$email]);
}

function newsletter_delete_subscribers(array $ids): bool
{
    $ids = array_values(array_filter(array_map('intval', $ids)));

    if (!$ids || !db_schema_ensure()) {
        return false;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("DELETE FROM logistic_newsletter_subscribers WHERE id IN ({$placeholders})");

    return $stmt->execute($ids);
}

function newsletter_emails_by_ids(array $ids): array
{
    $ids = array_values(array_filter(array_map('intval', $ids)));

    if (!$ids || !db_schema_ensure()) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT email FROM logistic_newsletter_subscribers WHERE is_active = 1 AND id IN ({$placeholders}) ORDER BY email ASC");
    $stmt->execute($ids);

    return array_column($stmt->fetchAll() ?: [], 'email');
}

function newsletter_send_promotional_email(array $emails, string $subject, string $body): int
{
    $subject = trim($subject);
    $body = trim($body);

    if (!$emails || $subject === '' || $body === '') {
        return 0;
    }

    $sent = 0;
    $htmlBody = '<div style="font-family:Arial,sans-serif;line-height:1.7;color:#334155">' . nl2br(e($body)) . '</div>';

    foreach (array_unique($emails) as $email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            continue;
        }

        if (smtp_send_html_email($email, $subject, $htmlBody)) {
            $sent++;
        }
    }

    return $sent;
}
