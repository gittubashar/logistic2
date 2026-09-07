<?php
require_once __DIR__ . '/../includes/config.php';

$service = service_by_slug($serviceSlug ?? '');
$servicePage = null;

if (!$service) {
    http_response_code(404);
    $pageTitle = 'Service Not Found - ' . $site['title'];
} else {
    $servicePageKey = 'service_' . str_replace('-', '_', $service['slug']);
    $servicePage = page_content($servicePageKey);
    $servicePage = array_replace([
        'label' => $service['title'] . ' Page',
        'header_kicker' => 'Service',
        'header_title' => $service['title'],
        'header_text' => $service['text'] ?? '',
        'header_image' => $service['image'] ?? 'uploads/page-header-bg.svg',
        'content' => $service['detail_paragraphs'] ?? [],
        'content_html' => '',
    ], $servicePage ?: []);
    $pageTitle = page_browser_title($servicePage);
}

require_once __DIR__ . '/../includes/header.php';
?>
<?php if (!$service): ?>
    <?php
    $pageHeaderKicker = '404';
    $pageHeaderTitle = 'Service Not Found';
    $pageHeaderText = 'The requested service page is not available.';
    require __DIR__ . '/../includes/page-header.php';
    ?>
    <section class="bg-[#f4f5f2] px-4 py-20 text-center">
        <a class="inline-flex rounded-full bg-[#071426] px-6 py-3 font-bold text-white hover:bg-[#102845]" href="<?php echo e(base_url('services.php')); ?>">Back to Services</a>
    </section>
<?php else: ?>
    <?php
    $pageHeaderKicker = $servicePage['header_kicker'] ?? 'Service';
    $pageHeaderTitle = $servicePage['header_title'] ?? $service['title'];
    $pageHeaderText = $servicePage['header_text'] ?? $service['text'];
    $pageHeaderImage = $servicePage['header_image'] ?? ($service['image'] ?? 'uploads/page-header-bg.svg');
    require __DIR__ . '/../includes/page-header.php';

    $paragraphs = array_values(array_filter($servicePage['content'] ?? ($service['detail_paragraphs'] ?? [])));
    $contentHtml = page_clean_html((string) ($servicePage['content_html'] ?? ''));
    ?>
    <section class="bg-[#f4f5f2] py-16 lg:py-24">
        <div class="home-shell">
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start">
                <article class="border-b border-slate-300 pb-10 sm:pb-12">
                    <p class="home-eyebrow">Service overview</p>
                    <h2 class="mt-6 max-w-3xl text-3xl font-extrabold tracking-[-.04em] text-[#071426] sm:text-4xl"><?php echo e($service['detail_title'] ?? $service['title']); ?></h2>
                    <div class="mt-7 space-y-5 text-base leading-8 text-slate-600 [&_a]:font-bold [&_a]:text-amber-700 [&_h2]:text-2xl [&_h2]:font-bold [&_h2]:text-[#071426] [&_li]:ml-5 [&_li]:list-disc [&_li]:marker:text-amber-500">
                        <?php if ($contentHtml !== ''): ?>
                            <?php echo $contentHtml; ?>
                        <?php else: ?>
                            <?php foreach ($paragraphs as $index => $paragraph): ?>
                                <p<?php echo $index === 0 ? ' class="text-lg font-semibold leading-9 text-[#24364b]"' : ''; ?>><?php echo e((string) $paragraph); ?></p>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </article>

                <aside class="border-t border-slate-300 pt-7 lg:sticky lg:top-32 lg:border-l lg:border-t-0 lg:pl-8">
                    <span class="grid h-10 w-10 place-items-center rounded-full bg-amber-400 text-sm text-[#071426]"><i class="fa-solid <?php echo e($service['icon'] ?? 'fa-box'); ?>"></i></span>
                    <h2 class="mt-5 text-xl font-extrabold text-[#071426]">Plan this shipment</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-600">Share your cargo details and our operations team will help map the next steps.</p>
                    <a class="mt-6 inline-flex min-h-11 items-center justify-center gap-3 rounded-full bg-[#071426] px-5 text-sm font-bold text-white transition hover:bg-[#102845]" href="<?php echo e(base_url('pages/contact.php')); ?>">Request support <i class="fa-solid fa-arrow-right text-xs"></i></a>
                    <a class="mt-4 flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-amber-700" href="tel:<?php echo e(preg_replace('/[^\d+]/', '', $site['phone'])); ?>"><i class="fa-solid fa-phone text-amber-700"></i><?php echo e($site['phone']); ?></a>
                </aside>
            </div>

            <?php if (!empty($service['features'])): ?>
                <div class="mt-8 grid border-y border-slate-300 sm:grid-cols-2 lg:grid-cols-4 lg:divide-x lg:divide-slate-300">
                    <?php foreach ($service['features'] as $index => $feature): ?>
                        <div class="border-b border-slate-200 p-5 last:border-b-0 lg:border-b-0 lg:px-6 lg:first:pl-0">
                            <span class="font-mono text-xs font-bold tracking-[.16em] text-amber-700">0<?php echo e((string) ($index + 1)); ?></span>
                            <p class="mt-3 text-sm font-bold leading-6 text-[#071426]"><?php echo e($feature); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!empty($service['process'])): ?>
        <section class="bg-[#071426] py-16 text-white lg:py-20">
            <div class="home-shell">
                <div class="grid gap-8 lg:grid-cols-[.65fr_1.35fr] lg:items-start">
                    <div>
                        <p class="home-eyebrow home-eyebrow--light">Our process</p>
                        <h2 class="mt-5 text-3xl font-extrabold tracking-[-.04em] text-white sm:text-4xl">A clear route from request to completion.</h2>
                    </div>
                    <ol class="grid divide-y divide-white/10 border-y border-white/10 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
                        <?php foreach ($service['process'] as $index => $step): ?>
                            <li class="p-5">
                                <span class="text-xs font-bold tracking-[.18em] text-amber-300">STEP <?php echo e((string) ($index + 1)); ?></span>
                                <p class="mt-5 text-lg font-bold text-white"><?php echo e($step); ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($service['best_for']) || !empty($service['support'])): ?>
        <section class="bg-white py-16 lg:py-24">
            <div class="home-shell grid gap-6 lg:grid-cols-2">
                <?php foreach ([['Best suited for', $service['best_for'] ?? []], ['Our support includes', $service['support'] ?? []]] as [$title, $items]): ?>
                    <div class="border-t border-slate-300 pt-7 sm:pt-8">
                        <h2 class="text-2xl font-extrabold tracking-[-.03em] text-[#071426]"><?php echo e($title); ?></h2>
                        <ul class="mt-7 grid gap-4 sm:grid-cols-2">
                            <?php foreach ($items as $item): ?>
                                <li class="flex items-start gap-3 text-sm font-semibold leading-6 text-slate-600"><i class="fa-solid fa-circle-check mt-1 text-amber-500"></i><span><?php echo e($item); ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
