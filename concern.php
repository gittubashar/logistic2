<?php
require_once __DIR__ . '/includes/config.php';

$allConcerns = our_concerns($concerns);
$concern = find_our_concern($allConcerns, (string) ($_GET['id'] ?? ''));

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
$summary = our_concern_summary($concern, 25);
?>
<main class="min-h-[70vh] bg-[#f7f8fb] py-7 sm:py-10 lg:py-14">
    <div class="mx-auto grid w-full max-w-[1400px] gap-7 px-3 sm:px-5 lg:grid-cols-[minmax(0,1fr)_320px] lg:gap-10 lg:px-5">
        <article class="min-w-0 rounded-[1.75rem] border border-slate-200 bg-white px-6 py-8 sm:px-10 sm:py-10 lg:px-12">
            <div class="border-b border-slate-200 pb-7 sm:pb-9">
                <p class="text-[11px] font-black uppercase tracking-[.22em] text-indigo-600">System Default</p>
                <h1 class="mt-4 max-w-4xl text-4xl font-black leading-[1.05] tracking-[-.055em] text-[#071426] sm:text-5xl lg:text-[3.8rem]">
                    <?php echo e($concern['title']); ?>
                </h1>
            </div>

            <div class="grid gap-8 pt-8 md:grid-cols-[250px_minmax(0,1fr)] md:gap-10 md:pt-9">
                <div class="aspect-[4/4.5] overflow-hidden rounded-xl bg-[#102845]">
                <?php if (!empty($concern['image'])): ?>
                    <img class="h-full w-full object-cover" src="<?php echo e(base_url($concern['image'])); ?>" alt="<?php echo e($concern['title']); ?>">
                <?php else: ?>
                    <div class="grid h-full place-items-center bg-gradient-to-br from-indigo-600 to-[#071426] text-6xl font-black tracking-widest text-white"><?php echo e(strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $concern['title']) ?: 'OC', 0, 2))); ?></div>
                <?php endif; ?>
                </div>
                <div class="min-w-0">
                    <h2 class="text-2xl font-extrabold leading-tight tracking-[-.035em] text-[#071426] sm:text-3xl">About <?php echo e($concern['title']); ?></h2>
                    <p class="mt-5 whitespace-pre-line text-base leading-8 text-slate-600"><?php echo e($summary); ?></p>

                    <div class="mt-7 flex flex-wrap gap-3">
                        <?php if (!empty($concern['website'])): ?><a class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-[.12em] text-[#071426] hover:text-indigo-600" href="<?php echo e(concern_external_url($concern['website'])); ?>" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square text-indigo-600"></i>Visit website</a><?php endif; ?>
                        <?php if (!empty($concern['social_link'])): ?><a class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-[.12em] text-[#071426] hover:text-indigo-600" href="<?php echo e(concern_external_url($concern['social_link'])); ?>" target="_blank" rel="noopener"><i class="fa-solid fa-share-nodes text-indigo-600"></i>Social media</a><?php endif; ?>
                    </div>
                </div>
            </div>
        </article>

        <aside class="border-t border-slate-200 pt-6 lg:sticky lg:top-28 lg:border-l lg:border-t-0 lg:pl-8">
            <p class="text-[11px] font-black uppercase tracking-[.2em] text-indigo-600">All Concern</p>
            <nav class="mt-4 divide-y divide-slate-200 border-y border-slate-200" aria-label="All concerns">
                <?php foreach ($allConcerns as $item): ?>
                    <a class="group relative -mx-1 flex items-start gap-3 rounded-lg px-3 py-3 text-sm font-bold leading-5 transition duration-200 <?php echo $item['id'] === $concern['id'] ? 'bg-indigo-50 text-indigo-700 shadow-sm' : 'text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 hover:shadow-sm'; ?>" href="<?php echo e(concern_url($item)); ?>">
                        <span class="mt-0.5 font-mono text-[10px] font-black text-slate-400 transition group-hover:text-indigo-500">0<?php echo e((string) $item['sort_order']); ?></span>
                        <span class="transition group-hover:translate-x-0.5"><?php echo e($item['title']); ?></span>
                        <i class="fa-solid fa-arrow-right ml-auto mt-1 text-[10px] text-indigo-400 opacity-0 transition duration-200 group-hover:translate-x-0.5 group-hover:opacity-100"></i>
                    </a>
                <?php endforeach; ?>
            </nav>
            <a class="mt-5 inline-flex items-center gap-2 text-xs font-black uppercase tracking-[.12em] text-[#071426] hover:text-indigo-600" href="<?php echo e(base_url('pages/our-concern.php')); ?>"><i class="fa-solid fa-arrow-left text-indigo-600"></i>All concerns</a>
        </aside>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
