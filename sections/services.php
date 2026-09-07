<?php
require_once __DIR__ . '/../includes/config.php';
$section = section_content('services');

if (($section['visible'] ?? true)):
    $serviceItems = array_values($section['items'] ?? $services);
?>
<section id="services" class="bg-white py-16 lg:py-20">
    <div class="home-shell">
        <div class="grid gap-6 border-b border-slate-200 pb-8 lg:grid-cols-[.9fr_1.1fr] lg:items-end">
            <div>
                <p class="home-eyebrow"><?php echo e($section['kicker'] ?? 'Services'); ?></p>
                <h2 class="home-heading mt-4 max-w-2xl">Reliable vessel turnarounds, from port to final delivery.</h2>
            </div>
            <div class="lg:pb-1">
                <p class="max-w-xl text-sm leading-7 text-slate-600"><?php echo e($section['title'] ?? 'Complete logistics services for freight, customs and supply chain operations'); ?></p>
                <a class="home-text-link mt-5 text-[#071426] hover:text-amber-700" href="<?php echo e(base_url('services.php')); ?>">View all services <i class="fa-solid fa-arrow-right text-xs"></i></a>
            </div>
        </div>

        <div class="mt-8 grid border-t border-slate-200 md:grid-cols-2 md:gap-x-10">
            <?php foreach ($serviceItems as $index => $service): ?>
                <article class="group flex min-h-[142px] items-start gap-4 border-b border-slate-200 py-6 transition hover:border-amber-400">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full border border-slate-200 text-sm text-amber-700 transition group-hover:border-amber-400 group-hover:bg-amber-400 group-hover:text-[#071426]"><i class="fa-solid <?php echo e($service['icon'] ?? 'fa-circle-dot'); ?>"></i></span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-4">
                            <h3 class="text-lg font-extrabold tracking-[-.025em] text-[#071426]"><?php echo e($service['title']); ?></h3>
                            <span class="font-mono text-[10px] font-bold tracking-[.15em] text-slate-400">0<?php echo e((string) ($index + 1)); ?></span>
                        </div>
                        <p class="mt-2 max-w-md text-sm leading-6 text-slate-600"><?php echo section_rich_text((string) ($service['text'] ?? '')); ?></p>
                        <a class="mt-3 inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[.12em] text-slate-500 transition group-hover:text-amber-700" href="<?php echo e(base_url('pages/' . $service['slug'] . '.php')); ?>">Explore <i class="fa-solid fa-arrow-right text-[10px]"></i></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
