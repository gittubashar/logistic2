<?php
require_once __DIR__ . '/../includes/config.php';

$page = page_content('our_concern');
$pageTitle = page_browser_title($page);
require_once __DIR__ . '/../includes/header.php';
$pageHeaderKicker = $page['header_kicker'] ?? 'Our Concern';
$pageHeaderTitle = $page['header_title'] ?? 'Our Concern';
$pageHeaderText = $page['header_text'] ?? '';
$pageHeaderImage = $page['header_image'] ?? 'uploads/page-header-bg.svg';
require __DIR__ . '/../includes/page-header.php';
render_page_content_block($page);
require_once __DIR__ . '/../includes/footer.php';
