<?php
require_once __DIR__ . '/../includes/config.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . base_url('login.php'));
    exit;
}

$sections = all_section_content();
$pages = all_page_content();
$teamMembers = team_members($team, false);
$visibleSections = count(array_filter($sections, fn (array $section): bool => (bool) ($section['visible'] ?? true)));
$visibleTeam = count(array_filter($teamMembers, fn (array $member): bool => (bool) ($member['visible'] ?? true)));
$mailboxUnreadCount = contact_unread_count();

$quickActions = [
    ['label' => 'Site Settings', 'text' => 'Logo, topbar, SEO and SMTP.', 'icon' => 'fa-gear', 'url' => 'dashboard/site-settings.php', 'color' => 'bg-blue-700'],
    ['label' => 'Page Manager', 'text' => 'Header image and page content.', 'icon' => 'fa-file-lines', 'url' => 'dashboard/page-manager.php', 'color' => 'bg-slate-900'],
    ['label' => 'Section Manager', 'text' => 'Homepage sections and visibility.', 'icon' => 'fa-table-cells-large', 'url' => 'dashboard/section-manager.php', 'color' => 'bg-emerald-600'],
    ['label' => 'Team Members', 'text' => 'Add, edit and sort leadership.', 'icon' => 'fa-users-gear', 'url' => 'dashboard/team-members.php', 'color' => 'bg-indigo-600'],
    ['label' => 'Mailbox', 'text' => 'Read and reply to contact enquiries.', 'icon' => 'fa-inbox', 'url' => 'dashboard/mailbox.php', 'color' => 'bg-cyan-600'],
];

$pageTitle = 'Dashboard - ' . $site['title'];
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
        <main class="px-4 pb-4 pt-0 lg:px-5 lg:pb-5 lg:pt-0">
            <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-black uppercase tracking-wider text-slate-500">Services</p>
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-blue-50 text-blue-700"><i class="fa-solid fa-truck-fast"></i></span>
                    </div>
                    <strong class="mt-2 block text-3xl font-black text-slate-950"><?php echo count($services); ?></strong>
                    <p class="text-xs font-bold text-slate-500">Cards and detail pages</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-black uppercase tracking-wider text-slate-500">Sections</p>
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-50 text-emerald-700"><i class="fa-solid fa-eye"></i></span>
                    </div>
                    <strong class="mt-2 block text-3xl font-black text-slate-950"><?php echo $visibleSections; ?></strong>
                    <p class="text-xs font-bold text-slate-500">Of <?php echo count($sections); ?> visible</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-black uppercase tracking-wider text-slate-500">Team</p>
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-indigo-50 text-indigo-700"><i class="fa-solid fa-users"></i></span>
                    </div>
                    <strong class="mt-2 block text-3xl font-black text-slate-950"><?php echo $visibleTeam; ?></strong>
                    <p class="text-xs font-bold text-slate-500">Of <?php echo count($teamMembers); ?> members</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-black uppercase tracking-wider text-slate-500">Mailbox</p>
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-cyan-50 text-cyan-700"><i class="fa-solid fa-inbox"></i></span>
                    </div>
                    <strong class="mt-2 block text-3xl font-black text-slate-950"><?php echo $mailboxUnreadCount; ?></strong>
                    <p class="text-xs font-bold text-slate-500">Unread contact messages</p>
                </div>
            </section>

            <section class="mt-4 grid gap-4 xl:grid-cols-[1.2fr_.8fr]">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex items-center justify-between border-b border-slate-200 pb-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-blue-700">Quick Actions</p>
                            <h2 class="text-xl font-black text-slate-950">Common Controls</h2>
                        </div>
                    </div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <?php foreach ($quickActions as $action): ?>
                            <a class="group flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 transition hover:border-blue-200 hover:bg-white hover:shadow-sm" href="<?php echo e(base_url($action['url'])); ?>">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl text-white <?php echo e($action['color']); ?>">
                                    <i class="fa-solid <?php echo e($action['icon']); ?>"></i>
                                </span>
                                <span>
                                    <span class="block font-black text-slate-950 group-hover:text-blue-700"><?php echo e($action['label']); ?></span>
                                    <span class="block text-xs leading-5 text-slate-600"><?php echo e($action['text']); ?></span>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <aside class="grid gap-4">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-blue-700">Site Snapshot</p>
                        <div class="mt-3 grid gap-2">
                            <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3">
                                <span class="font-bold text-slate-600">Title</span>
                                <strong class="text-right text-slate-950"><?php echo e($site['title']); ?></strong>
                            </div>
                            <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3">
                                <span class="font-bold text-slate-600">Email</span>
                                <strong class="text-right text-slate-950"><?php echo e($site['email']); ?></strong>
                            </div>
                            <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3">
                                <span class="font-bold text-slate-600">Pages</span>
                                <strong class="text-right text-slate-950"><?php echo count($pages); ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-950 p-4 text-white shadow-sm">
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-emerald-300">Next Up</p>
                        <h2 class="mt-1 text-xl font-black">Keep the site fresh</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-300">Update headers, service images and contact map before launch.</p>
                        <a class="mt-3 inline-flex rounded-xl bg-emerald-400 px-4 py-2 text-sm font-black text-slate-950 hover:bg-white" href="<?php echo e(base_url('dashboard/site-settings.php')); ?>">Review Settings</a>
                    </div>
                </aside>
            </section>
        </main>
        <?php require __DIR__ . '/footer.php'; ?>
    </div>
</div>
</body>
</html>
