<?php
require_once __DIR__ . '/../includes/config.php';
require_super_admin();

$users = admin_users_all();
$editId = (int) ($_GET['edit'] ?? 0);
$message = '';
$error = '';
$currentAdminId = (int) ($_SESSION['admin_user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? 'save_user';
    $postedId = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete_user') {
        if (admin_user_delete($postedId)) {
            header('Location: ' . base_url('dashboard/user-management.php?deleted=1'));
            exit;
        }
        $error = 'This user cannot be deleted. The active Super Admin account and your own account are protected.';
    }

    if ($action === 'save_user') {
        $existing = null;
        foreach ($users as $user) {
            if ((int) $user['id'] === $postedId) {
                $existing = $user;
                break;
            }
        }

        $role = $_POST['role'] ?? 'admin';
        $isActive = isset($_POST['is_active']);
        $password = (string) ($_POST['password'] ?? '');
        $confirmPassword = (string) ($_POST['password_confirmation'] ?? '');

        if ($postedId > 0 && !$existing) {
            $error = 'User not found.';
        } elseif ($postedId === $currentAdminId && (!$isActive || $role !== 'super_admin')) {
            $error = 'You cannot deactivate or demote your own Super Admin account.';
        } elseif ($existing && ($existing['role'] ?? '') === 'super_admin' && ($role !== 'super_admin' || !$isActive)) {
            $activeSuperAdmins = count(array_filter($users, fn (array $user): bool => ($user['role'] ?? '') === 'super_admin' && (int) $user['is_active'] === 1));
            if ($activeSuperAdmins <= 1) {
                $error = 'At least one active Super Admin must remain.';
            }
        } elseif ($password !== '' && (strlen($password) < 10 || $password !== $confirmPassword)) {
            $error = $password !== $confirmPassword ? 'Password confirmation does not match.' : 'Password must be at least 10 characters.';
        } elseif ($postedId < 1 && strlen($password) < 10) {
            $error = 'A new user password must be at least 10 characters.';
        } else {
            $payload = [
                'name' => trim((string) ($_POST['name'] ?? '')),
                'email' => trim((string) ($_POST['email'] ?? '')),
                'phone' => trim((string) ($_POST['phone'] ?? '')),
                'designation' => trim((string) ($_POST['designation'] ?? 'Admin')),
                'role' => $role,
                'image' => $existing['image'] ?? '',
                'is_active' => $isActive,
                'password_hash' => $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : '',
            ];

            if (!$payload['name'] || !filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
                $error = 'Name and a valid email are required.';
            } elseif (admin_user_save_record($payload, $postedId > 0 ? $postedId : null)) {
                header('Location: ' . base_url('dashboard/user-management.php?edit=' . ($postedId > 0 ? $postedId : 0) . '&saved=1'));
                exit;
            } else {
                $error = 'Unable to save user. The email may already be in use.';
            }
        }
    }
}

$users = admin_users_all();
$editing = null;
foreach ($users as $user) {
    if ((int) $user['id'] === $editId) {
        $editing = $user;
        break;
    }
}
$formUser = $editing ?: ['id' => 0, 'name' => '', 'email' => '', 'phone' => '', 'designation' => 'Admin', 'role' => 'admin', 'is_active' => 1];

if (isset($_GET['saved'])) {
    $message = 'Admin user saved successfully.';
} elseif (isset($_GET['deleted'])) {
    $message = 'Admin user deleted successfully.';
}

$pageTitle = 'User Management - ' . $site['title'];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($pageTitle); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-slate-100 text-slate-700">
<div class="flex min-h-screen">
    <?php require __DIR__ . '/sidebar.php'; ?>
    <div class="flex min-h-screen min-w-0 flex-1 flex-col">
        <?php require __DIR__ . '/topbar.php'; ?>
        <main class="px-3 pb-5 pt-0 lg:px-5 lg:pb-8">
            <?php if ($message): ?><div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800"><?php echo e($message); ?></div><?php endif; ?>
            <?php if ($error): ?><div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700"><?php echo e($error); ?></div><?php endif; ?>

            <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div><p class="text-[10px] font-black uppercase tracking-[.18em] text-red-600">Super Admin only</p><h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">User Management</h1><p class="mt-1 text-sm text-slate-500">Create, update, deactivate or remove admin accounts.</p></div>
                <span class="inline-flex w-fit items-center gap-2 rounded-full border border-red-200 bg-red-50 px-3 py-2 text-[10px] font-black uppercase tracking-wide text-red-700"><i class="fa-solid fa-shield-halved"></i>Private control</span>
            </div>

            <div class="grid gap-5 xl:grid-cols-[minmax(320px,380px)_minmax(0,1fr)]">
                <section class="h-fit rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:p-5">
                    <div class="mb-4 flex items-center justify-between border-b border-slate-200 pb-3"><div><p class="text-[10px] font-black uppercase tracking-[.16em] text-blue-700"><?php echo $editing ? 'Edit account' : 'New account'; ?></p><h2 class="mt-1 text-lg font-black text-slate-950"><?php echo e($formUser['name'] ?: 'Admin user'); ?></h2></div><?php if ($editing): ?><a class="text-xs font-bold text-slate-400 hover:text-blue-700" href="<?php echo e(base_url('dashboard/user-management.php')); ?>">Clear</a><?php endif; ?></div>
                    <form method="post" class="grid gap-3">
                        <?php echo csrf_field(); ?><input type="hidden" name="action" value="save_user"><input type="hidden" name="id" value="<?php echo e((string) $formUser['id']); ?>">
                        <label class="text-[10px] font-black uppercase tracking-wide text-slate-500">Full name<input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" name="name" value="<?php echo e($formUser['name']); ?>" required></label>
                        <label class="text-[10px] font-black uppercase tracking-wide text-slate-500">Email<input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" type="email" name="email" value="<?php echo e($formUser['email']); ?>" required></label>
                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1"><label class="text-[10px] font-black uppercase tracking-wide text-slate-500">Phone<input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" name="phone" value="<?php echo e($formUser['phone']); ?>"></label><label class="text-[10px] font-black uppercase tracking-wide text-slate-500">Designation<input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" name="designation" value="<?php echo e($formUser['designation']); ?>"></label></div>
                        <label class="text-[10px] font-black uppercase tracking-wide text-slate-500">Role<select class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm" name="role"><option value="admin" <?php echo ($formUser['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option><option value="editor" <?php echo ($formUser['role'] ?? '') === 'editor' ? 'selected' : ''; ?>>Editor</option><option value="super_admin" <?php echo ($formUser['role'] ?? '') === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option></select></label>
                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1"><label class="text-[10px] font-black uppercase tracking-wide text-slate-500">Password <?php if ($editing): ?><span class="font-normal normal-case text-slate-400">(leave blank to keep)</span><?php endif; ?><input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" type="password" name="password" autocomplete="new-password" <?php echo $editing ? '' : 'required'; ?>></label><label class="text-[10px] font-black uppercase tracking-wide text-slate-500">Confirm password<input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" type="password" name="password_confirmation" autocomplete="new-password" <?php echo $editing ? '' : 'required'; ?>></label></div>
                        <label class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-bold text-slate-600"><input class="h-4 w-4 accent-blue-700" type="checkbox" name="is_active" <?php echo !empty($formUser['is_active']) ? 'checked' : ''; ?>>Active account</label>
                        <button class="mt-1 inline-flex items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 py-3 text-sm font-black text-white hover:bg-slate-950" type="submit"><i class="fa-solid fa-floppy-disk"></i><?php echo $editing ? 'Update user' : 'Create user'; ?></button>
                    </form>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:p-5">
                    <div class="mb-4 flex items-center justify-between border-b border-slate-200 pb-3"><div><p class="text-[10px] font-black uppercase tracking-[.16em] text-red-600">Admin accounts</p><h2 class="mt-1 text-lg font-black text-slate-950"><?php echo count($users); ?> users</h2></div><span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-slate-500">Private list</span></div>
                    <div class="divide-y divide-slate-200">
                        <?php foreach ($users as $user): ?>
                            <article class="flex flex-col gap-3 py-4 first:pt-0 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><h3 class="truncate text-sm font-black text-slate-950"><?php echo e($user['name']); ?></h3><span class="rounded-full <?php echo $user['role'] === 'super_admin' ? 'bg-red-100 text-red-700' : 'bg-blue-50 text-blue-700'; ?> px-2 py-0.5 text-[9px] font-black uppercase tracking-wide"><?php echo e($user['role'] === 'super_admin' ? 'Super Admin' : ucfirst($user['role'])); ?></span><span class="rounded-full <?php echo (int) $user['is_active'] === 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'; ?> px-2 py-0.5 text-[9px] font-black uppercase tracking-wide"><?php echo (int) $user['is_active'] === 1 ? 'Active' : 'Inactive'; ?></span></div><p class="mt-1 truncate text-xs text-slate-500"><?php echo e($user['email']); ?><?php if (!empty($user['designation'])): ?> · <?php echo e($user['designation']); ?><?php endif; ?></p></div>
                                <div class="flex shrink-0 items-center gap-3"><a class="text-[10px] font-black uppercase tracking-wide text-blue-700 hover:text-blue-900" href="<?php echo e(base_url('dashboard/user-management.php?edit=' . (int) $user['id'])); ?>"><i class="fa-solid fa-pen-to-square mr-1"></i>Edit</a><?php if ((int) $user['id'] !== $currentAdminId): ?><form method="post" onsubmit="return confirm('Delete this admin user?');"><?php echo csrf_field(); ?><input type="hidden" name="action" value="delete_user"><input type="hidden" name="id" value="<?php echo (int) $user['id']; ?>"><button class="text-[10px] font-black uppercase tracking-wide text-red-600 hover:text-red-800" type="submit"><i class="fa-solid fa-trash mr-1"></i>Delete</button></form><?php endif; ?></div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        </main>
        <?php require __DIR__ . '/footer.php'; ?>
    </div>
</div>
</body>
</html>
