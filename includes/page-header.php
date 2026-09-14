<?php
require_once __DIR__ . '/config.php';

$pageHeaderKicker = $pageHeaderKicker ?? 'M/S B. S. Trading';
$pageHeaderTitle = $pageHeaderTitle ?? ($pageTitle ?? $site['title']);
$pageHeaderText = $pageHeaderText ?? '';
$pageHeaderImage = $pageHeaderImage ?? '';
$pageHeaderTitleClass = $pageHeaderTitleClass ?? 'text-[clamp(2rem,4.5vw,4.25rem)]';

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
            <p class="home-eyebrow home-eyebrow--light"><?php echo e($pageHeaderKicker); ?></p>
            <h1 class="mt-5 max-w-4xl <?php echo e($pageHeaderTitleClass); ?> font-extrabold leading-[.98] tracking-[-.06em] text-white"><?php echo e($pageHeaderTitle); ?></h1>
            <?php if ($pageHeaderText !== ''): ?>
                <p class="mt-6 max-w-2xl text-base leading-8 text-slate-300 sm:text-lg"><?php echo e($pageHeaderText); ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>
