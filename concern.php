<?php
require_once __DIR__ . '/includes/config.php';

$concern = find_our_concern($concerns, (string) ($_GET['id'] ?? ''));

if (!$concern) {
    http_response_code(404);
    $pageTitle = 'Concern Not Found - ' . $site['title'];
    require_once __DIR__ . '/includes/header.php';
    $pageHeaderKicker = 'Our Concern';
    $pageHeaderTitle = 'Concern Not Found';
    $pageHeaderText = 'The requested concern is not available.';
    $pageHeaderImage = 'uploads/page-header-bg.svg';
    require __DIR__ . '/includes/page-header.php';
    ?>
    <section class="bg-[#f4f5f2] px-4 py-16 text-center">
        <a class="inline-flex rounded-xl bg-[#071426] px-6 py-3 font-black text-white" href="<?php echo e(base_url('pages/our-concern.php')); ?>">Back to Our Concern</a>
    </section>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $concern['title'] . ' - ' . $site['title'];
require_once __DIR__ . '/includes/header.php';
$pageHeaderKicker = 'Our Concern';
$pageHeaderTitle = $concern['title'];
$pageHeaderText = $concern['about_concern'] ?: 'A concern associated with M/S B. S. TRADING.';
$pageHeaderImage = $concern['image'] ?: 'uploads/page-header-bg.svg';
require __DIR__ . '/includes/page-header.php';
?>
<section class="bg-[#f4f5f2] py-14 lg:py-20">
    <div class="home-shell grid gap-8 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start">
        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="aspect-[16/7] overflow-hidden bg-[#102845]">
                <?php if (!empty($concern['image'])): ?>
                    <img class="h-full w-full object-cover" src="<?php echo e(base_url($concern['image'])); ?>" alt="<?php echo e($concern['title']); ?>">
                <?php else: ?>
                    <div class="grid h-full place-items-center bg-gradient-to-br from-[#102845] to-[#071426] text-5xl font-black tracking-widest text-amber-300"><?php echo e(strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $concern['title']) ?: 'OC', 0, 2))); ?></div>
                <?php endif; ?>
            </div>
            <div class="p-6 sm:p-8">
                <p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-700">Concern profile</p>
                <h2 class="mt-3 text-2xl font-extrabold tracking-[-.03em] text-[#071426]">About <?php echo e($concern['title']); ?></h2>
                <p class="mt-4 whitespace-pre-line text-base leading-8 text-slate-600"><?php echo e($concern['about_concern'] ?: 'Information about this concern will be published soon.'); ?></p>
            </div>
        </article>

        <aside class="border-t border-slate-300 pt-6 lg:sticky lg:top-32 lg:border-l lg:border-t-0 lg:pl-8">
            <p class="text-[10px] font-black uppercase tracking-[.18em] text-slate-400">Explore</p>
            <div class="mt-4 grid gap-3">
                <?php if (!empty($concern['website'])): ?><a class="inline-flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-[#071426] hover:border-amber-300" href="<?php echo e(concern_external_url($concern['website'])); ?>" target="_blank" rel="noopener"><i class="fa-solid fa-globe w-4 text-amber-600"></i>Visit website</a><?php endif; ?>
                <?php if (!empty($concern['social_link'])): ?><a class="inline-flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-[#071426] hover:border-amber-300" href="<?php echo e(concern_external_url($concern['social_link'])); ?>" target="_blank" rel="noopener"><i class="fa-solid fa-share-nodes w-4 text-amber-600"></i>Social media</a><?php endif; ?>
                <a class="inline-flex items-center gap-3 rounded-xl bg-[#071426] px-4 py-3 text-sm font-bold text-white hover:bg-[#102845]" href="<?php echo e(base_url('pages/our-concern.php')); ?>"><i class="fa-solid fa-arrow-left w-4 text-amber-300"></i>All concerns</a>
            </div>
        </aside>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
