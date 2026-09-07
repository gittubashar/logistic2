<?php

function contact_message_create(array $data): int
{
    if (!db_schema_ensure()) {
        return 0;
    }

    $stmt = db()->prepare('
        INSERT INTO logistic_contact_messages (name, email, service, message, request_ip)
        VALUES (?, ?, ?, ?, ?)
    ');
    $saved = $stmt->execute([
        trim((string) ($data['name'] ?? '')),
        strtolower(trim((string) ($data['email'] ?? ''))),
        trim((string) ($data['service'] ?? '')),
        trim((string) ($data['message'] ?? '')),
        substr(trim((string) ($data['request_ip'] ?? '')), 0, 45),
    ]);

    return $saved ? (int) db()->lastInsertId() : 0;
}

function contact_messages(): array
{
    if (!db_schema_ensure()) {
        return [];
    }

    return db()->query('SELECT * FROM logistic_contact_messages ORDER BY created_at DESC, id DESC')->fetchAll() ?: [];
}

function contact_message_by_id(int $id): ?array
{
    if ($id < 1 || !db_schema_ensure()) {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM logistic_contact_messages WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $message = $stmt->fetch();

    return is_array($message) ? $message : null;
}

function contact_unread_count(): int
{
    if (!db_schema_ensure()) {
        return 0;
    }

    return (int) db()->query("SELECT COUNT(*) FROM logistic_contact_messages WHERE status = 'unread'")->fetchColumn();
}

function contact_message_mark_read(int $id): bool
{
    if ($id < 1 || !db_schema_ensure()) {
        return false;
    }

    $stmt = db()->prepare("UPDATE logistic_contact_messages SET status = 'read' WHERE id = ? AND status = 'unread'");

    return $stmt->execute([$id]);
}

function contact_message_mark_unread(int $id): bool
{
    if ($id < 1 || !db_schema_ensure()) {
        return false;
    }

    $stmt = db()->prepare("UPDATE logistic_contact_messages SET status = 'unread' WHERE id = ?");

    return $stmt->execute([$id]);
}

function contact_message_record_reply(int $id, string $subject, string $reply): bool
{
    if ($id < 1 || !db_schema_ensure()) {
        return false;
    }

    try {
        $stmt = db()->prepare("
            UPDATE logistic_contact_messages
            SET reply_subject = ?, reply_message = ?, replied_at = NOW(), status = 'replied'
            WHERE id = ?
        ");

        return $stmt->execute([trim($subject), trim($reply), $id]);
    } catch (Throwable $exception) {
        error_log('Unable to record contact reply: ' . $exception->getMessage());
        return false;
    }
}

function contact_message_delete(int $id): bool
{
    if ($id < 1 || !db_schema_ensure()) {
        return false;
    }

    $stmt = db()->prepare('DELETE FROM logistic_contact_messages WHERE id = ?');
    $stmt->execute([$id]);

    return $stmt->rowCount() > 0;
}
