<?php
require_once __DIR__ . '/includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $profile = admin_user_by_email($email) ?: admin_profile();

    if (($profile['password_hash'] ?? '') !== '' && strtolower($email) === strtolower($profile['email']) && password_verify($password, $profile['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user_id'] = (int) ($profile['id'] ?? 0);
        $_SESSION['admin_name'] = $profile['name'];
        $_SESSION['admin_role'] = $profile['role'] ?? 'admin';
        header('Location: ' . base_url('dashboard/index.php'));
        exit;
    }

    $error = ($profile['password_hash'] ?? '') === '' ? 'Admin account is not configured.' : 'Invalid email or password.';
}

$pageTitle = 'Login - M/S B. S. Trading';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($pageTitle); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="grid min-h-screen place-items-center bg-slate-100 px-4">
    <form class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-xl" method="post">
        <div class="mb-8 text-center">
            <div class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-slate-900 text-xl font-black text-emerald-400"><?php echo e($site['logo_text']); ?></div>
            <h1 class="text-2xl font-black text-slate-900">Admin Login</h1>
        <p class="mt-1 text-sm text-slate-500">Sign in to manage website content.</p>
        </div>
        <?php if ($error): ?>
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700"><?php echo e($error); ?></div>
        <?php endif; ?>
            <?php echo csrf_field(); ?>
        <label class="mb-4 block text-sm font-bold text-slate-600">Email
            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" type="email" name="email" autocomplete="username" required>
        </label>
        <label class="mb-6 block text-sm font-bold text-slate-600">Password
            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" type="password" name="password" autocomplete="current-password" required>
        </label>
        <button class="w-full rounded-xl bg-blue-700 px-5 py-3 font-black text-white hover:bg-slate-900" type="submit">Login</button>
    </form>
</body>
</html>
