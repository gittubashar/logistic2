<?php
require_once __DIR__ . '/includes/config.php';

$slug = trim($_GET['slug'] ?? '');
$pageKey = page_key_from_slug($slug);
$page = page_content($pageKey);

if (!$slug || !$page) {
    http_response_code(404);
    $pageTitle = 'Page Not Found - ' . $site['title'];
    require_once __DIR__ . '/includes/header.php';
    $pageHeaderKicker = '404';
    $pageHeaderTitle = 'Page Not Found';
    $pageHeaderText = 'The requested page is not available.';
    require __DIR__ . '/includes/page-header.php';
    ?>
    <section class="bg-white px-4 py-16 text-center">
        <a class="inline-flex rounded-xl bg-aqua px-6 py-3 font-black text-navy" href="<?php echo e(base_url('index.php')); ?>">Back to Home</a>
    </section>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = page_browser_title($page);
require_once __DIR__ . '/includes/header.php';
$pageHeaderKicker = $page['header_kicker'] ?? '';
$pageHeaderTitle = $page['header_title'] ?? ($page['label'] ?? '');
$pageHeaderText = $page['header_text'] ?? '';
$pageHeaderImage = $page['header_image'] ?? 'uploads/page-header-bg.svg';
require __DIR__ . '/includes/page-header.php';
render_page_content_block($page);
require_once __DIR__ . '/includes/footer.php';
