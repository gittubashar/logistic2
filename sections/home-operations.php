<?php
require_once __DIR__ . '/../includes/config.php';

$credentialsSection = section_content('company_profile');
$heroSection = section_content('hero');
$credentials = array_values(array_filter(
    $heroSection['highlights'] ?? ($credentialsSection['items'] ?? []),
    static fn ($item): bool => trim(strip_tags((string) $item)) !== ''
));
$credentialLinks = $heroSection['highlight_links'] ?? [
    'pages/international-freight-forwarding-agent.php',
    'pages/bangladesh-customs-shipping-agent.php',
    'pages/clearing-forwarding-agent.php',
    'pages/govt-first-class-contractor.php',
];
$profileDocument = $credentialsSection['profile_document'] ?? '';
?>
<section class="home-operations relative overflow-hidden bg-[#071426] py-16 text-white lg:py-20">
    <div class="home-shell relative">
        <div class="grid gap-10 lg:grid-cols-[.8fr_1.2fr] lg:items-start">
            <div>
                <p class="home-eyebrow home-eyebrow--light">How we work</p>
                <h2 class="mt-4 max-w-xl text-3xl font-extrabold leading-tight tracking-[-.04em] text-white sm:text-4xl">One clear workflow from instruction to delivery.</h2>
                <p class="mt-5 max-w-md text-sm leading-7 text-slate-400">Documentation, port coordination and inland movement stay connected, so the next step is always visible.</p>
            </div>

            <ol class="divide-y divide-white/10 border-y border-white/10">
                <?php
                $steps = [
                    ['01', 'Understand', 'Cargo, route, timing and compliance requirements are reviewed.'],
                    ['02', 'Plan', 'The right service, documents and operating sequence are aligned.'],
                    ['03', 'Coordinate', 'Carriers, customs, port and transport partners move in sync.'],
                    ['04', 'Deliver', 'Progress is communicated through release and final handover.'],
                ];
                ?>
                <?php foreach ($steps as [$number, $title, $text]): ?>
                    <li class="group grid gap-3 py-5 sm:grid-cols-[56px_150px_1fr] sm:items-center">
                        <span class="font-mono text-xs font-bold tracking-[.18em] text-amber-300"><?php echo e($number); ?></span>
                        <h3 class="text-lg font-bold text-white"><?php echo e($title); ?></h3>
                        <p class="text-sm leading-6 text-slate-400"><?php echo e($text); ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>

        <?php if ($credentials): ?>
            <div class="mt-14 border-t border-white/10 pt-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="home-eyebrow home-eyebrow--light">Capabilities & credentials</p>
                        <p class="mt-2 text-sm text-slate-400">Specialized support for regulated and operationally complex cargo.</p>
                    </div>
                    <?php if ($profileDocument): ?>
                        <a class="home-text-link text-white hover:text-amber-300" href="<?php echo e(base_url($profileDocument)); ?>" target="_blank" rel="noopener"><i class="fa-regular fa-file-pdf"></i><?php echo e($credentialsSection['profile_document_label'] ?? 'Company Profile'); ?><i class="fa-solid fa-arrow-up-right-from-square text-xs"></i></a>
                    <?php endif; ?>
                </div>
                <div class="mt-6 grid border-y border-white/10 sm:grid-cols-2 lg:grid-cols-4 lg:divide-x lg:divide-white/10">
                    <?php foreach ($credentials as $index => $credential): ?>
                        <a class="group flex items-start gap-3 py-5 lg:px-5 lg:first:pl-0" href="<?php echo e(base_url($credentialLinks[$index] ?? 'pages/about.php')); ?>">
                            <span class="font-mono text-xs font-bold text-amber-300">0<?php echo e((string) ($index + 1)); ?></span>
                            <span class="text-sm font-bold leading-6 text-slate-200 transition group-hover:text-amber-300"><?php echo section_rich_text((string) $credential); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
