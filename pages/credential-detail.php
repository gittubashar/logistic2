<?php
require_once __DIR__ . '/../includes/config.php';

$credentialPageKey = $credentialPageKey ?? '';
$page = page_content($credentialPageKey);

if (!$page) {
    http_response_code(404);
    $page = [
        'header_kicker' => 'Credential',
        'header_title' => 'Page Not Found',
        'header_text' => 'The requested credential page could not be found.',
        'header_image' => 'uploads/page-header-bg.svg',
        'content' => [],
        'content_html' => '',
    ];
}

$pageTitle = page_browser_title($page);
require_once __DIR__ . '/../includes/header.php';
$pageHeaderKicker = $page['header_kicker'] ?? 'Credential';
$pageHeaderTitle = $page['header_title'] ?? '';
$pageHeaderText = $page['header_text'] ?? '';
$pageHeaderImage = $page['header_image'] ?? 'uploads/page-header-bg.svg';
require __DIR__ . '/../includes/page-header.php';
render_page_content_block($page);
require_once __DIR__ . '/../includes/footer.php';
?>
