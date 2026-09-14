<?php require_once __DIR__ . '/../includes/config.php'; ?>
<?php $dashboardLogo = $site['footer_logo_image'] ?: ($site['logo_image'] ?? ''); ?>
<?php $sidebarUnreadCount = contact_unread_count(); ?>
<aside class="min-h-screen w-72 bg-slate-950 p-5 text-white">
    <a class="mb-8 flex flex-col items-center justify-center gap-3 rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-5 text-center" href="<?php echo e(base_url('dashboard/index.php')); ?>">
        <?php if ($dashboardLogo): ?>
            <span class="flex min-h-24 w-full items-center justify-center rounded-xl bg-white p-3 shadow-sm">
                <img class="max-h-20 w-auto max-w-48 object-contain" src="<?php echo e(base_url($dashboardLogo)); ?>" alt="<?php echo e($site['title']); ?>">
            </span>
        <?php else: ?>
            <span class="grid h-14 w-14 place-items-center rounded-xl bg-emerald-400 text-lg font-black text-slate-950"><?php echo e($site['logo_text']); ?></span>
            <strong class="block text-sm uppercase leading-5"><?php echo e($site['title']); ?></strong>
        <?php endif; ?>
        <small class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Admin Dashboard</small>
    </a>
    <nav class="grid gap-2 text-sm font-bold">
        <a class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-white/10" href="<?php echo e(base_url('dashboard/index.php')); ?>">
            <i class="fa-solid fa-gauge-high w-5 text-emerald-300"></i>Dashboard
        </a>
        <a class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-white/10" href="<?php echo e(base_url('dashboard/gallery.php')); ?>">
            <i class="fa-solid fa-photo-film w-5 text-emerald-300"></i>Gallery
        </a>
        <a class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-white/10" href="<?php echo e(base_url('dashboard/page-manager.php')); ?>">
            <i class="fa-solid fa-file-pen w-5 text-emerald-300"></i>Page Manager
        </a>
        <a class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-white/10" href="<?php echo e(base_url('dashboard/our-concern.php')); ?>">
            <i class="fa-solid fa-building-columns w-5 text-emerald-300"></i>Our Concern
        </a>
        <a class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-white/10" href="<?php echo e(base_url('dashboard/post-manager.php')); ?>">
            <i class="fa-solid fa-newspaper w-5 text-emerald-300"></i>Post Manager
        </a>
        <a class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-white/10" href="<?php echo e(base_url('dashboard/section-manager.php')); ?>">
            <i class="fa-solid fa-layer-group w-5 text-emerald-300"></i>Section Manager
        </a>
        <a class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-white/10" href="<?php echo e(base_url('dashboard/team-members.php')); ?>">
            <i class="fa-solid fa-users-gear w-5 text-emerald-300"></i>Team Members
        </a>
        <a class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-white/10" href="<?php echo e(base_url('dashboard/membership-certificates.php')); ?>">
            <i class="fa-solid fa-award w-5 text-emerald-300"></i>Membership & Certificates
        </a>
        <a class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-white/10" href="<?php echo e(base_url('dashboard/office-address.php')); ?>">
            <i class="fa-solid fa-location-dot w-5 text-emerald-300"></i>Office Address
        </a>
        <a class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-white/10" href="<?php echo e(base_url('dashboard/newsletter.php')); ?>">
            <i class="fa-solid fa-envelope-open-text w-5 text-emerald-300"></i>Newsletter
        </a>
        <a class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-white/10" href="<?php echo e(base_url('dashboard/mailbox.php')); ?>">
            <i class="fa-solid fa-inbox w-5 text-emerald-300"></i>
            <span>Mailbox</span>
            <?php if ($sidebarUnreadCount > 0): ?>
                <span class="ml-auto min-w-6 rounded-full bg-blue-600 px-2 py-0.5 text-center text-xs text-white"><?php echo $sidebarUnreadCount > 99 ? '99+' : $sidebarUnreadCount; ?></span>
            <?php endif; ?>
        </a>
        <a class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-white/10" href="<?php echo e(base_url('dashboard/site-settings.php')); ?>">
            <i class="fa-solid fa-sliders w-5 text-emerald-300"></i>Site Settings
        </a>
    </nav>
</aside>
