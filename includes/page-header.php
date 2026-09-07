<?php
require_once __DIR__ . '/config.php';

$pageHeaderKicker = $pageHeaderKicker ?? 'M/S B. S. Trading';
$pageHeaderTitle = $pageHeaderTitle ?? ($pageTitle ?? $site['title']);
$pageHeaderText = $pageHeaderText ?? '';
$pageHeaderImage = $pageHeaderImage ?? '';

if ($pageHeaderImage === '' || str_ends_with(strtolower($pageHeaderImage), '.svg')) {
    $pageHeaderImage = 'uploads/gallery/20260712194416-c92c5107.jpg';
}
?>
<section class="compact-page-header relative overflow-hidden bg-[#071426] text-white">
    <div class="absolute inset-0">
        <img class="h-full w-full object-cover object-center opacity-35" src="<?php echo e(base_url($pageHeaderImage)); ?>" alt="" aria-hidden="true">
        <div class="absolute inset-0 bg-gradient-to-r from-[#071426] via-[#071426]/90 to-[#071426]/35"></div>
        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(255,255,255,.035)_1px,transparent_1px)] bg-[length:96px_100%]"></div>
    </div>

    <div class="home-shell relative flex min-h-[350px] items-end py-12 sm:py-14 lg:min-h-[390px] lg:py-16">
        <div class="max-w-4xl">
            <nav class="mb-9 flex items-center gap-2 text-[11px] font-bold uppercase tracking-[.15em] text-slate-400" aria-label="Breadcrumb">
                <a class="transition hover:text-amber-300" href="<?php echo e(base_url('index.php')); ?>">Home</a>
                <i class="fa-solid fa-chevron-right text-[8px] text-slate-600"></i>
                <span class="text-amber-300"><?php echo e($pageHeaderKicker); ?></span>
            </nav>
            <p class="home-eyebrow home-eyebrow--light"><?php echo e($pageHeaderKicker); ?></p>
            <h1 class="mt-5 max-w-4xl text-[clamp(2.5rem,6vw,5.8rem)] font-extrabold leading-[.98] tracking-[-.06em] text-white"><?php echo e($pageHeaderTitle); ?></h1>
            <?php if ($pageHeaderText !== ''): ?>
                <p class="mt-6 max-w-2xl text-base leading-8 text-slate-300 sm:text-lg"><?php echo e($pageHeaderText); ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>
