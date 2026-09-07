<?php
require_once __DIR__ . '/../includes/config.php';
$section = section_content('office');
$officeItems = $section['items'] ?? $offices;

if (($section['visible'] ?? true)):
    $officeList = array_values($officeItems);
?>
<section class="bg-[#f4f5f2] py-20 lg:py-28">
    <div class="home-shell">
        <div class="grid gap-8 border-b border-slate-300 pb-10 lg:grid-cols-[.9fr_1.1fr] lg:items-end">
            <div>
                <p class="home-eyebrow">Where to find us</p>
                <h2 class="home-heading mt-5 max-w-2xl">Close to the cargo. Ready for the next move.</h2>
            </div>
            <p class="max-w-xl text-base leading-8 text-slate-600">Our Chattogram office provides responsive port agency, maritime logistics and cargo support for shipowners, charterers and cargo operators.</p>
        </div>

        <div class="mt-10 grid gap-5 lg:grid-cols-2">
            <?php foreach ($officeItems as $name => $address): ?>
                <article class="group relative overflow-hidden rounded-[2rem] border border-slate-200 bg-white p-7 shadow-[0_16px_45px_rgba(7,20,38,.05)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_24px_60px_rgba(7,20,38,.10)] sm:p-10">
                    <div class="flex items-start justify-between gap-6">
                        <span class="grid h-14 w-14 place-items-center rounded-2xl bg-[#071426] text-xl text-amber-300"><i class="fa-solid fa-location-dot"></i></span>
                        <span class="font-mono text-xs font-bold tracking-[.16em] text-slate-400">0<?php echo e((string) (array_search($name, array_keys($officeItems), true) + 1)); ?></span>
                    </div>
                    <h3 class="mt-10 text-2xl font-extrabold tracking-[-.03em] text-[#071426]"><?php echo e($name); ?></h3>
                    <p class="mt-4 max-w-md text-sm leading-7 text-slate-600"><?php echo section_rich_text((string) $address); ?></p>
                    <a class="mt-7 inline-flex items-center gap-3 text-xs font-bold uppercase tracking-[.12em] text-[#071426] transition group-hover:text-amber-700" href="<?php echo e('https://www.google.com/maps/search/?api=1&query=' . rawurlencode((string) $address)); ?>" target="_blank" rel="noopener">
                        Open in maps <span class="grid h-8 w-8 place-items-center rounded-full border border-slate-300 transition group-hover:border-amber-400 group-hover:bg-amber-400"><i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></span>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
