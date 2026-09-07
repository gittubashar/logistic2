<?php require_once __DIR__ . '/config.php'; ?>
<div class="bg-[#071426] text-[11px] text-slate-300 sm:text-xs">
    <div class="mx-auto flex w-full max-w-[1480px] flex-wrap items-center justify-between gap-x-3 gap-y-2 px-4 py-2.5 lg:px-8">
        <div class="flex min-w-0 flex-1 flex-wrap items-center gap-x-4 gap-y-1.5 sm:gap-x-6">
            <a class="inline-flex items-center gap-1.5 whitespace-nowrap hover:text-aqua" href="tel:<?php echo e(preg_replace('/\s+/', '', $site['phone'])); ?>">
                <i class="fa-solid fa-phone text-amber-300"></i>
                <?php echo e($site['phone']); ?>
            </a>
            <a class="inline-flex min-w-0 items-center gap-1.5 hover:text-aqua" href="mailto:<?php echo e($site['email']); ?>">
                <i class="fa-solid fa-envelope text-amber-300"></i>
                <span class="max-w-[190px] truncate sm:max-w-none"><?php echo e($site['email']); ?></span>
            </a>
            <a class="hidden items-center gap-1.5 whitespace-nowrap hover:text-aqua sm:inline-flex" href="https://wa.me/<?php echo e(preg_replace('/\D+/', '', $site['whatsapp'])); ?>">
                <i class="fa-brands fa-whatsapp text-amber-300"></i>
                <?php echo e($site['whatsapp']); ?>
            </a>
        </div>
        <div class="flex shrink-0 items-center gap-1.5 sm:gap-2 md:justify-end">
            <span class="mr-1 hidden font-bold uppercase tracking-[.15em] text-slate-500 md:inline">Follow</span>
            <?php foreach ($site['socials'] as $icon => $url): ?>
                <a class="grid h-6 w-6 place-items-center rounded-full border border-white/10 text-[10px] text-white transition hover:border-amber-300 hover:bg-amber-300 hover:text-navy" href="<?php echo e($url); ?>" aria-label="<?php echo e($icon); ?>">
                    <i class="fa-brands fa-<?php echo e($icon); ?>"></i>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
