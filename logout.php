<?php
require_once __DIR__ . '/includes/config.php';
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_user_id']);
unset($_SESSION['admin_name']);
session_regenerate_id(true);
header('Location: ' . base_url('login.php'));
exit;
