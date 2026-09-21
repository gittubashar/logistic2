<?php
require_once __DIR__ . '/../includes/config.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . base_url('login.php'));
    exit;
}

$profile = admin_profile();
$message = '';
$error = '';

if (isset($_GET['gallery_image'])) {
    $image = trim($_GET['gallery_image']);
    if (str_starts_with($image, 'uploads/')) {
        $profile['image'] = $image;
        $message = 'Profile picture selected from gallery. Click Save Profile to apply it.';
    }
}

if (isset($_GET['updated'])) {
    $message = 'Profile picture updated from gallery.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $profile['name'] = trim($_POST['name'] ?? $profile['name']);
    $profile['email'] = trim($_POST['email'] ?? $profile['email']);
    $profile['phone'] = trim($_POST['phone'] ?? $profile['phone']);
    $profile['designation'] = trim($_POST['designation'] ?? $profile['designation']);
    $selectedImage = trim($_POST['selected_image'] ?? '');

    if (str_starts_with($selectedImage, 'uploads/')) {
        $profile['image'] = $selectedImage;
    }

    $profileUpload = $_FILES['profile_image'] ?? [];
    $uploaded = upload_dashboard_file($profileUpload, 'profile');
    if ($uploaded) {
        $profile['image'] = $uploaded;
    } elseif (dashboard_upload_was_requested($profileUpload)) {
        $error = dashboard_upload_last_error() ?: 'Profile image could not be uploaded.';
    }

    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($newPassword !== '' || $confirmPassword !== '') {
        if (strlen($newPassword) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Password confirmation does not match.';
        } else {
            $profile['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }
    }

    if ($error === '') {
        save_admin_profile($profile);
        $_SESSION['admin_name'] = $profile['name'];
        $message = 'Profile updated successfully.';
    }
}

$pageTitle = 'User Profile - ' . $site['title'];
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
        <main class="px-5 pb-5 pt-0 lg:px-8 lg:pb-8 lg:pt-0">
            <?php if ($message): ?>
                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 font-bold text-emerald-800"><?php echo e($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 font-bold text-red-700"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form class="grid gap-6 lg:grid-cols-[340px_1fr]" method="post" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="selected_image" value="<?php echo e($profile['image']); ?>">
                <aside class="rounded-3xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                    <img class="mx-auto h-44 w-44 rounded-3xl border border-slate-200 object-cover shadow-sm" src="<?php echo e(base_url($profile['image'])); ?>" alt="<?php echo e($profile['name']); ?>">
                    <h2 class="mt-5 text-2xl font-black text-slate-950"><?php echo e($profile['name']); ?></h2>
                    <p class="mt-1 text-sm font-bold text-slate-500"><?php echo e($profile['designation']); ?></p>
                    <div class="mt-6 grid gap-3">
                        <label class="block text-left text-sm font-black text-slate-600">Pick from computer
                            <input class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm" type="file" name="profile_image" accept="image/*">
                        </label>
                        <a class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-5 py-3 text-sm font-black text-blue-700 hover:bg-blue-100" href="<?php echo e(base_url('dashboard/gallery.php?select=profile')); ?>">
                            <i class="fa-solid fa-images mr-2"></i>Pick from gallery
                        </a>
                    </div>
                </aside>

                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm lg:p-7">
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="block text-sm font-black text-slate-600">Full Name
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-500" name="name" value="<?php echo e($profile['name']); ?>" required>
                        </label>
                        <label class="block text-sm font-black text-slate-600">Email
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-500" type="email" name="email" value="<?php echo e($profile['email']); ?>" required>
                        </label>
                        <label class="block text-sm font-black text-slate-600">Phone
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-500" name="phone" value="<?php echo e($profile['phone']); ?>">
                        </label>
                        <label class="block text-sm font-black text-slate-600">Designation
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-500" name="designation" value="<?php echo e($profile['designation']); ?>">
                        </label>
                    </div>

                    <div class="mt-8 border-t border-slate-200 pt-6">
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-blue-700">Password</p>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <label class="block text-sm font-black text-slate-600">New Password
                                <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-500" type="password" name="new_password" autocomplete="new-password">
                            </label>
                            <label class="block text-sm font-black text-slate-600">Confirm Password
                                <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-500" type="password" name="confirm_password" autocomplete="new-password">
                            </label>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button class="inline-flex items-center rounded-xl bg-blue-700 px-6 py-3 font-black text-white shadow-lg shadow-blue-700/20 hover:bg-slate-950" type="submit">
                            <i class="fa-solid fa-floppy-disk mr-2"></i>Save Profile
                        </button>
                    </div>
                </section>
            </form>
        </main>
        <?php require __DIR__ . '/footer.php'; ?>
    </div>
</div>
</body>
</html>
