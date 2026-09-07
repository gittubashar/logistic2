<?php
require_once __DIR__ . '/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('index.php'));
    exit;
}

$email = trim($_POST['newsletter_email'] ?? '');
$humanCheck = (int) ($_POST['newsletter_check'] ?? 0);
$redirect = $_SERVER['HTTP_REFERER'] ?? base_url('index.php');
$status = ($humanCheck === 10 && newsletter_subscribe($email)) ? 'newsletter=success' : 'newsletter=error';
$separator = str_contains($redirect, '?') ? '&' : '?';

header('Location: ' . $redirect . $separator . $status);
exit;
