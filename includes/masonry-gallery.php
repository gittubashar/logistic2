<?php

function masonry_gallery_file(): string
{
    return __DIR__ . '/../uploads/masonry-gallery.json';
}

function default_masonry_gallery_items(): array
{
    return [
        ['id' => 'mg-sea', 'title' => 'Sea Freight Operations', 'category' => 'Freight', 'caption' => 'Coordinated ocean freight movement with reliable documentation support.', 'image' => 'uploads/service-sea-freight.svg', 'sort_order' => 1, 'visible' => true],
        ['id' => 'mg-road', 'title' => 'Road Transport', 'category' => 'Transport', 'caption' => 'Domestic movement support for time-sensitive cargo delivery.', 'image' => 'uploads/service-road-transport.svg', 'sort_order' => 2, 'visible' => true],
        ['id' => 'mg-warehouse', 'title' => 'Warehousing Support', 'category' => 'Storage', 'caption' => 'Organized storage, distribution and value-added logistics services.', 'image' => 'uploads/service-warehouse.svg', 'sort_order' => 3, 'visible' => true],
        ['id' => 'mg-customs', 'title' => 'Customs Brokerage', 'category' => 'Customs', 'caption' => 'Compliance-led customs documentation and clearing support.', 'image' => 'uploads/service-customs-brokerage.svg', 'sort_order' => 4, 'visible' => true],
    ];
}

function normalize_masonry_item(array $item, int $index = 0): array
{
    $title = trim((string) ($item['title'] ?? ''));

    return [
        'id' => trim((string) ($item['id'] ?? 'masonry-' . substr(md5($title . $index), 0, 10))),
        'title' => $title,
        'category' => trim((string) ($item['category'] ?? 'Operations')),
        'caption' => trim((string) ($item['caption'] ?? '')),
        'image' => trim((string) ($item['image'] ?? '')),
        'sort_order' => (int) ($item['sort_order'] ?? ($index + 1)),
        'visible' => (bool) ($item['visible'] ?? true),
    ];
}

function masonry_gallery_items(bool $visibleOnly = true): array
{
    if (db_schema_ensure()) {
        $sql = 'SELECT id, title, category, caption, image, sort_order, visible FROM logistic_masonry_gallery';
        $sql .= $visibleOnly ? ' WHERE visible = 1' : '';
        $sql .= ' ORDER BY sort_order ASC, title ASC';
        $items = db()->query($sql)->fetchAll();

        if ($items) {
            return array_map(
                fn (array $item, int $index): array => normalize_masonry_item($item, $index),
                $items,
                array_keys($items)
            );
        }
    }

    $file = masonry_gallery_file();
    $items = [];

    if (is_file($file)) {
        $data = json_decode((string) file_get_contents($file), true);
        $items = is_array($data) ? $data : [];
    }

    if (!$items) {
        $items = default_masonry_gallery_items();
    }

    $items = array_map(
        fn (array $item, int $index): array => normalize_masonry_item($item, $index),
        $items,
        array_keys($items)
    );

    usort($items, fn (array $a, array $b): int => $a['sort_order'] <=> $b['sort_order']);

    if ($visibleOnly) {
        $items = array_values(array_filter($items, fn (array $item): bool => $item['visible']));
    }

    return $items;
}

function save_masonry_gallery_items(array $items): bool
{
    if (db_schema_ensure()) {
        $items = array_values(array_map(
            fn (array $item, int $index): array => normalize_masonry_item($item, $index),
            $items,
            array_keys($items)
        ));

        $pdo = db();
        $pdo->beginTransaction();

        try {
            $pdo->exec('DELETE FROM logistic_masonry_gallery');
            $stmt = $pdo->prepare('
                INSERT INTO logistic_masonry_gallery (id, title, category, caption, image, sort_order, visible)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ');

            foreach ($items as $item) {
                $stmt->execute([
                    $item['id'],
                    $item['title'],
                    $item['category'],
                    $item['caption'],
                    $item['image'],
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

    $file = masonry_gallery_file();
    $directory = dirname($file);

    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    $items = array_values(array_map(
        fn (array $item, int $index): array => normalize_masonry_item($item, $index),
        $items,
        array_keys($items)
    ));

    return file_put_contents($file, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
}

function find_masonry_item(array $items, string $id): ?array
{
    foreach ($items as $item) {
        if ($item['id'] === $id) {
            return $item;
        }
    }

    return null;
}
