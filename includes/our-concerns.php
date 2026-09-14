<?php

function our_concerns_file(): string
{
    return __DIR__ . '/../uploads/our-concerns.json';
}

function our_concern_slug(string $value): string
{
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?: 'concern';

    return trim($slug, '-');
}

function normalize_our_concern(array $concern, int $index = 0): array
{
    $title = trim((string) ($concern['title'] ?? ''));
    $id = trim((string) ($concern['id'] ?? ''));
    if ($id === '') {
        $id = 'concern-' . our_concern_slug($title) . '-' . substr(md5($title . $index), 0, 6);
    }

    return [
        'id' => $id,
        'title' => $title,
        'image' => trim((string) ($concern['image'] ?? '')),
        'website' => trim((string) ($concern['website'] ?? '')),
        'social_link' => trim((string) ($concern['social_link'] ?? ($concern['social_media_link'] ?? ''))),
        'about_concern' => trim((string) ($concern['about_concern'] ?? ($concern['about'] ?? ''))),
        'sort_order' => (int) ($concern['sort_order'] ?? ($index + 1)),
        'visible' => (bool) ($concern['visible'] ?? true),
    ];
}

function default_our_concerns(array $fallback): array
{
    return array_values(array_map(
        fn (array $concern, int $index): array => normalize_our_concern($concern, $index),
        $fallback,
        array_keys($fallback)
    ));
}

function our_concerns(array $fallback = [], bool $visibleOnly = true): array
{
    if (db_schema_ensure()) {
        $sql = 'SELECT id, title, image, website, social_link, about_concern, sort_order, visible FROM logistic_our_concerns';
        $sql .= $visibleOnly ? ' WHERE visible = 1' : '';
        $sql .= ' ORDER BY sort_order ASC, title ASC';
        $items = db()->query($sql)->fetchAll();

        if ($items) {
            return array_map(
                fn (array $item, int $index): array => normalize_our_concern($item, $index),
                $items,
                array_keys($items)
            );
        }
    }

    $file = our_concerns_file();
    $items = [];
    if (is_file($file)) {
        $data = json_decode((string) file_get_contents($file), true);
        $items = is_array($data) ? $data : [];
    }

    $items = $items ?: default_our_concerns($fallback);
    $items = array_values(array_map(
        fn (array $item, int $index): array => normalize_our_concern($item, $index),
        $items,
        array_keys($items)
    ));
    usort($items, fn (array $a, array $b): int => $a['sort_order'] <=> $b['sort_order']);

    return $visibleOnly
        ? array_values(array_filter($items, fn (array $item): bool => $item['visible']))
        : $items;
}

function save_our_concerns(array $items): bool
{
    $items = array_values(array_map(
        fn (array $item, int $index): array => normalize_our_concern($item, $index),
        $items,
        array_keys($items)
    ));

    if (db_schema_ensure()) {
        $pdo = db();
        $pdo->beginTransaction();

        try {
            $pdo->exec('DELETE FROM logistic_our_concerns');
            $stmt = $pdo->prepare('
                INSERT INTO logistic_our_concerns (id, title, image, website, social_link, about_concern, sort_order, visible)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ');

            foreach ($items as $item) {
                $stmt->execute([
                    $item['id'],
                    $item['title'],
                    $item['image'],
                    $item['website'],
                    $item['social_link'],
                    $item['about_concern'],
                    $item['sort_order'],
                    $item['visible'] ? 1 : 0,
                ]);
            }

            return $pdo->commit();
        } catch (Throwable) {
            $pdo->rollBack();
            return false;
        }
    }

    $file = our_concerns_file();
    $directory = dirname($file);
    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    return file_put_contents($file, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
}

function find_our_concern(array $items, string $id): ?array
{
    $needle = trim($id);
    foreach ($items as $item) {
        if (($item['id'] ?? '') === $needle || our_concern_slug((string) ($item['title'] ?? '')) === our_concern_slug($needle)) {
            return $item;
        }
    }

    return null;
}

function concern_url(array $concern): string
{
    return base_url('concern.php?id=' . rawurlencode((string) ($concern['id'] ?? '')));
}

function concern_external_url(string $url): string
{
    $url = trim($url);
    if ($url === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $url)) {
        return $url;
    }

    return 'https://' . ltrim($url, '/');
}
