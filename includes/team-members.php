<?php

function team_members_file(): string
{
    return __DIR__ . '/../uploads/team-members.json';
}

function normalize_team_member(array $member, int $index = 0): array
{
    $name = trim((string) ($member['name'] ?? ''));

    return [
        'id' => trim((string) ($member['id'] ?? 'team-' . substr(md5($name . $index), 0, 10))),
        'name' => $name,
        'role' => trim((string) ($member['role'] ?? '')),
        'image' => trim((string) ($member['image'] ?? '')),
        'text' => trim((string) ($member['text'] ?? '')),
        'sort_order' => (int) ($member['sort_order'] ?? ($index + 1)),
        'visible' => (bool) ($member['visible'] ?? true),
    ];
}

function default_team_members(array $fallback): array
{
    return array_map(
        fn (array $member, int $index): array => normalize_team_member($member, $index),
        $fallback,
        array_keys($fallback)
    );
}

function team_members(array $fallback = [], bool $visibleOnly = true): array
{
    if (db_schema_ensure()) {
        $sql = 'SELECT id, name, role, image, bio AS text, sort_order, visible FROM logistic_team_members';
        $sql .= $visibleOnly ? ' WHERE visible = 1' : '';
        $sql .= ' ORDER BY sort_order ASC, name ASC';
        $members = db()->query($sql)->fetchAll();

        if ($members) {
            return array_map(
                fn (array $member, int $index): array => normalize_team_member($member, $index),
                $members,
                array_keys($members)
            );
        }
    }

    $file = team_members_file();
    $members = [];

    if (is_file($file)) {
        $data = json_decode((string) file_get_contents($file), true);
        $members = is_array($data) ? $data : [];
    }

    if (!$members) {
        $members = default_team_members($fallback);
    } else {
        $members = array_map(
            fn (array $member, int $index): array => normalize_team_member($member, $index),
            $members,
            array_keys($members)
        );
    }

    usort($members, fn (array $a, array $b): int => $a['sort_order'] <=> $b['sort_order']);

    if ($visibleOnly) {
        $members = array_values(array_filter($members, fn (array $member): bool => $member['visible']));
    }

    return $members;
}

function save_team_members(array $members): bool
{
    if (db_schema_ensure()) {
        $members = array_values(array_map(
            fn (array $member, int $index): array => normalize_team_member($member, $index),
            $members,
            array_keys($members)
        ));

        $pdo = db();
        $pdo->beginTransaction();

        try {
            $pdo->exec('DELETE FROM logistic_team_members');
            $stmt = $pdo->prepare('
                INSERT INTO logistic_team_members (id, name, role, image, bio, sort_order, visible)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ');

            foreach ($members as $member) {
                $stmt->execute([
                    $member['id'],
                    $member['name'],
                    $member['role'],
                    $member['image'],
                    $member['text'],
                    $member['sort_order'],
                    $member['visible'] ? 1 : 0,
                ]);
            }

            return $pdo->commit();
        } catch (Throwable) {
            $pdo->rollBack();
            return false;
        }
    }

    $file = team_members_file();
    $directory = dirname($file);

    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    $members = array_values(array_map(
        fn (array $member, int $index): array => normalize_team_member($member, $index),
        $members,
        array_keys($members)
    ));

    return file_put_contents($file, json_encode($members, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
}

function find_team_member(array $members, string $id): ?array
{
    foreach ($members as $member) {
        if ($member['id'] === $id) {
            return $member;
        }
    }

    return null;
}

function team_member_word_count(string $text): int
{
    $words = preg_split('/\s+/', trim(strip_tags($text))) ?: [];

    return count(array_filter($words, fn ($word): bool => $word !== ''));
}

function team_member_excerpt(string $text, int $limit = 20): string
{
    $words = array_values(array_filter(preg_split('/\s+/', trim(strip_tags($text))) ?: [], fn ($word): bool => $word !== ''));

    if (count($words) <= $limit) {
        return trim(strip_tags($text));
    }

    return implode(' ', array_slice($words, 0, $limit)) . '...';
}

function team_member_needs_read_more(string $text, int $limit = 20): bool
{
    return team_member_word_count($text) > $limit;
}

function team_member_url(array $member): string
{
    return base_url('team_member_view.php?id=' . rawurlencode((string) ($member['id'] ?? '')));
}
