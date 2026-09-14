<?php
require_once __DIR__ . '/../includes/config.php';

$membership = membership_certificates(true);
$page = page_content('membership_certificates');
$pageTitle = page_browser_title($page);
require_once __DIR__ . '/../includes/header.php';
$pageHeaderKicker = $page['header_kicker'] ?? 'Credentials';
$pageHeaderTitle = $page['header_title'] ?? 'Membership & Certificates';
$pageHeaderText = $page['header_text'] ?? '';
$pageHeaderImage = $page['header_image'] ?? 'uploads/page-header-bg.svg';
require __DIR__ . '/../includes/page-header.php';
?>
<section class="bg-[#f4f5f2] py-14 lg:py-18">
    <div class="home-shell">
        <div class="grid gap-8 lg:grid-cols-[280px_1fr] lg:items-start lg:gap-14">
            <aside class="border-b border-slate-300 pb-7 lg:border-b-0 lg:border-r lg:pb-0 lg:pr-10">
                <span class="grid h-10 w-10 place-items-center rounded-full bg-amber-400 text-[#071426]"><i class="fa-solid fa-award"></i></span>
                <p class="mt-5 text-[11px] font-bold uppercase tracking-[.18em] text-amber-700"><?php echo e($membership['kicker'] ?? 'Membership & Certificates'); ?></p>
                <h2 class="mt-3 text-2xl font-extrabold leading-tight tracking-[-.035em] text-[#071426]">Recognized logistics credentials.</h2>
                <p class="mt-4 text-sm leading-7 text-slate-600"><?php echo e($membership['subtitle'] ?? ''); ?></p>
                <p class="mt-6 font-mono text-xs font-bold uppercase tracking-[.16em] text-slate-500"><?php echo count($membership['items'] ?? []); ?> records · Since 2018</p>
            </aside>

            <?php if (!empty($membership['items'])): ?>
                <div class="divide-y divide-slate-300 border-y border-slate-300">
                    <?php foreach ($membership['items'] as $index => $item): ?>
                        <article class="group grid gap-5 py-6 sm:grid-cols-[120px_1fr_auto] sm:items-center">
                            <div class="relative aspect-[16/10] overflow-hidden rounded-lg bg-[#102845]">
                                <?php if (!empty($item['image'])): ?><img class="h-full w-full object-cover" src="<?php echo e(base_url($item['image'])); ?>" alt="<?php echo e($item['title']); ?>" loading="lazy"><?php else: ?><div class="grid h-full place-items-center text-xl text-amber-300"><i class="fa-solid fa-certificate"></i></div><?php endif; ?>
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-3"><span class="font-mono text-[10px] font-bold tracking-[.15em] text-amber-700">0<?php echo e((string) ($index + 1)); ?></span><span class="text-[11px] font-bold uppercase tracking-[.14em] text-slate-400"><?php echo e($item['type']); ?></span><?php if (($item['date'] ?? '') !== ''): ?><span class="text-xs text-slate-500"><?php echo e($item['date']); ?></span><?php endif; ?></div>
                                <h3 class="mt-2 text-lg font-extrabold leading-6 text-[#071426]"><?php echo e($item['title']); ?></h3>
                                <?php if (($item['issuer'] ?? '') !== ''): ?><p class="mt-1 text-sm font-bold text-amber-700"><?php echo e($item['issuer']); ?></p><?php endif; ?>
                                <?php if (($item['description'] ?? '') !== ''): ?><p class="mt-2 text-sm leading-6 text-slate-600"><?php echo e($item['description']); ?></p><?php endif; ?>
                            </div>
                            <?php if (!empty($item['document'])): ?><a class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[.12em] text-[#071426] transition group-hover:text-amber-700" href="<?php echo e(base_url($item['document'])); ?>" target="_blank" rel="noopener">View <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a><?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="border-y border-dashed border-slate-300 py-12 text-center text-sm text-slate-500"><p class="font-bold">No credentials published yet.</p></div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
