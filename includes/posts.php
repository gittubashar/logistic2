<?php

function post_slug(string $title): string
{
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug) ?: 'post';

    return trim($slug, '-');
}

function posts_all(bool $publishedOnly = false): array
{
    if (!db_schema_ensure()) {
        return [];
    }

    $sql = 'SELECT * FROM logistic_posts';
    if ($publishedOnly) {
        $sql .= ' WHERE is_published = 1';
    }
    $sql .= ' ORDER BY published_at DESC, id DESC';

    return db()->query($sql)->fetchAll() ?: [];
}

function post_find(int $id): ?array
{
    if (!db_schema_ensure()) {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM logistic_posts WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $post = $stmt->fetch();

    return $post ?: null;
}

function post_by_slug(string $slug): ?array
{
    if (!db_schema_ensure()) {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM logistic_posts WHERE slug = ? AND is_published = 1 LIMIT 1');
    $stmt->execute([$slug]);
    $post = $stmt->fetch();

    return $post ?: null;
}

function post_save(array $post): bool
{
    if (!db_schema_ensure()) {
        return false;
    }

    try {
        $id = (int) ($post['id'] ?? 0);
        $slug = post_slug($post['slug'] ?: $post['title']);
        $publishedAt = trim((string) ($post['published_at'] ?? '')) ?: date('Y-m-d H:i:s');

        if ($id > 0) {
            $stmt = db()->prepare('
                UPDATE logistic_posts
                SET title = ?, slug = ?, excerpt = ?, content_html = ?, image = ?, is_published = ?, published_at = ?
                WHERE id = ?
            ');

            return $stmt->execute([
                $post['title'],
                $slug,
                $post['excerpt'],
                $post['content_html'],
                $post['image'],
                !empty($post['is_published']) ? 1 : 0,
                $publishedAt,
                $id,
            ]);
        }

        $stmt = db()->prepare('
            INSERT INTO logistic_posts (title, slug, excerpt, content_html, image, is_published, published_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');

        return $stmt->execute([
            $post['title'],
            $slug,
            $post['excerpt'],
            $post['content_html'],
            $post['image'],
            !empty($post['is_published']) ? 1 : 0,
            $publishedAt,
        ]);
    } catch (Throwable) {
        return false;
    }
}

function post_delete(int $id): bool
{
    if (!db_schema_ensure()) {
        return false;
    }

    $stmt = db()->prepare('DELETE FROM logistic_posts WHERE id = ?');

    return $stmt->execute([$id]);
}
