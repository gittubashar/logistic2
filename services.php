<?php
require_once __DIR__ . '/includes/config.php';
$page = page_content('services');
$pageTitle = page_browser_title($page);
require_once __DIR__ . '/includes/header.php';
$pageHeaderKicker = $page['header_kicker'];
$pageHeaderTitle = $page['header_title'];
$pageHeaderText = $page['header_text'];
$pageHeaderImage = $page['header_image'];
require __DIR__ . '/includes/page-header.php';
require __DIR__ . '/sections/services.php';
render_page_content_block($page);
require_once __DIR__ . '/includes/footer.php';
