<?php
require_once __DIR__ . '/../includes/config.php';
$section = section_content('masonry_gallery');
$items = masonry_gallery_items(true);

if (($section['visible'] ?? true) && $items):
?>
<section class="bg-white py-20 lg:py-28">
    <div class="home-shell">
        <div class="flex flex-col gap-5 border-b border-slate-200 pb-10 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="home-eyebrow"><?php echo e($section['kicker'] ?? 'Inside the operation'); ?></p>
                <h2 class="home-heading mt-5 max-w-3xl"><?php echo e($section['title'] ?? 'Company Operations Gallery'); ?></h2>
            </div>
            <span class="font-mono text-xs font-bold uppercase tracking-[.16em] text-slate-400"><?php echo e((string) count($items)); ?> moments</span>
        </div>

        <div class="mt-10 grid auto-rows-[180px] grid-cols-2 gap-3 sm:auto-rows-[220px] sm:grid-cols-4 sm:gap-5">
            <?php foreach ($items as $index => $item): ?>
                <?php $featured = $index === 0 || $index === 3; ?>
                <figure class="group relative overflow-hidden rounded-3xl bg-slate-100 <?php echo $featured ? 'col-span-2 row-span-2' : 'col-span-1 row-span-1'; ?>">
                    <img class="h-full w-full object-cover transition duration-700 group-hover:scale-105" src="<?php echo e(base_url($item['image'])); ?>" alt="<?php echo e($item['title'] ?? 'Company operations'); ?>" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#071426]/75 via-transparent to-transparent opacity-80"></div>
                    <?php if (!empty($item['title']) || !empty($item['caption'])): ?>
                        <figcaption class="absolute inset-x-0 bottom-0 p-5 text-white sm:p-6">
                            <?php if (!empty($item['title'])): ?><p class="text-sm font-bold"><?php echo e($item['title']); ?></p><?php endif; ?>
                            <?php if (!empty($item['caption'])): ?><p class="mt-1 text-xs leading-5 text-slate-300"><?php echo e($item['caption']); ?></p><?php endif; ?>
                        </figcaption>
                    <?php endif; ?>
                </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
