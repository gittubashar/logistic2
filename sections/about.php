<?php
require_once __DIR__ . '/../includes/config.php';
$section = section_content('about');

if (($section['visible'] ?? true)):
    $paragraphs = array_values(array_filter($section['paragraphs'] ?? []));
?>
<section class="bg-[#f4f5f2] py-20 lg:py-28">
    <div class="home-shell grid gap-12 lg:grid-cols-[.85fr_1.15fr] lg:gap-20">
        <div class="relative">
            <div class="sticky top-36">
                <p class="home-eyebrow"><?php echo e($section['kicker'] ?? 'About us'); ?></p>
                <div class="mt-7 border-l-2 border-amber-400 pl-6 sm:pl-8">
                    <p class="font-mono text-[clamp(3.5rem,8vw,6rem)] font-bold leading-none tracking-[-.08em] text-[#071426]">06</p>
                    <p class="mt-3 max-w-xs text-sm font-bold uppercase leading-6 tracking-[.15em] text-slate-500">Integrated logistics services. One operating partner.</p>
                </div>
            </div>
        </div>

        <div>
            <h2 class="home-heading max-w-4xl"><?php echo e($section['title'] ?? 'Trusted logistics partner since 2018'); ?></h2>
            <div class="mt-8 grid gap-6 text-base leading-8 text-slate-600 sm:text-lg">
                <?php foreach ($paragraphs as $index => $paragraph): ?>
                    <p<?php echo $index === 0 ? ' class="font-semibold text-[#24364b]"' : ''; ?>><?php echo section_rich_text((string) $paragraph); ?></p>
                <?php endforeach; ?>
            </div>

            <div class="mt-10 flex flex-col gap-5 border-t border-slate-300 pt-8 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex -space-x-2" aria-label="Transport modes">
                    <?php foreach (['fa-ship', 'fa-truck-fast', 'fa-warehouse', 'fa-file-shield'] as $icon): ?>
                        <span class="grid h-11 w-11 place-items-center rounded-full border-2 border-[#f4f5f2] bg-white text-sm text-[#071426] shadow-sm"><i class="fa-solid <?php echo e($icon); ?>"></i></span>
                    <?php endforeach; ?>
                </div>
                <a class="home-text-link text-[#071426] hover:text-amber-700" href="<?php echo e(base_url($section['button_url'] ?? 'pages/about.php')); ?>">
                    <?php echo e($section['button_label'] ?? 'Read Company Story'); ?>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
