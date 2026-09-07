<?php
require_once __DIR__ . '/includes/config.php';

$page = page_content('blog');
$pageTitle = page_browser_title($page);
$posts = posts_all(true);
require_once __DIR__ . '/includes/header.php';
$pageHeaderKicker = $page['header_kicker'] ?? 'Insights';
$pageHeaderTitle = $page['header_title'] ?? 'Logistics Insights';
$pageHeaderText = $page['header_text'] ?? 'Practical notes for freight, customs and supply chain operations.';
$pageHeaderImage = $page['header_image'] ?? 'uploads/page-header-bg.svg';
require __DIR__ . '/includes/page-header.php';
?>
<section class="bg-[#f4f5f2] py-14 lg:py-18">
    <div class="home-shell">
        <div class="mb-8 flex flex-col gap-3 border-b border-slate-300 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div><p class="home-eyebrow">From the operations desk</p><h2 class="mt-4 text-2xl font-extrabold tracking-[-.035em] text-[#071426] sm:text-3xl">Useful thinking for moving goods.</h2></div>
            <p class="max-w-md text-sm leading-7 text-slate-600">Notes and explainers from freight forwarding, customs and supply chain operations.</p>
        </div>

        <?php if ($posts): ?>
            <div class="divide-y divide-slate-300 border-y border-slate-300">
                <?php foreach ($posts as $index => $post): ?>
                    <article class="group grid gap-5 py-6 sm:grid-cols-[170px_1fr_auto] sm:items-center">
                        <a class="relative aspect-[16/10] overflow-hidden rounded-lg bg-[#102845]" href="<?php echo e(base_url('post.php?slug=' . rawurlencode($post['slug']))); ?>">
                            <?php if (!empty($post['image'])): ?><img class="h-full w-full object-cover transition duration-500 group-hover:scale-105" src="<?php echo e(base_url($post['image'])); ?>" alt="<?php echo e($post['title']); ?>" loading="lazy"><?php else: ?><div class="grid h-full place-items-center text-2xl text-amber-300"><i class="fa-solid fa-compass-drafting"></i></div><?php endif; ?>
                        </a>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[.16em] text-amber-700"><?php echo e(date('M j, Y', strtotime((string) ($post['published_at'] ?? 'now')))); ?></p>
                            <h3 class="mt-2 text-lg font-extrabold leading-6 text-[#071426]"><a href="<?php echo e(base_url('post.php?slug=' . rawurlencode($post['slug']))); ?>"><?php echo e($post['title']); ?></a></h3>
                            <?php if (!empty($post['excerpt'])): ?><p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"><?php echo e($post['excerpt']); ?></p><?php endif; ?>
                        </div>
                        <a class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[.12em] text-[#071426] transition group-hover:text-amber-700" href="<?php echo e(base_url('post.php?slug=' . rawurlencode($post['slug']))); ?>">Read <i class="fa-solid fa-arrow-right text-[10px]"></i></a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="border-y border-dashed border-slate-300 py-12 text-center"><span class="mx-auto grid h-11 w-11 place-items-center rounded-full bg-[#071426] text-amber-300"><i class="fa-solid fa-pen-nib"></i></span><h2 class="mt-5 text-xl font-extrabold text-[#071426]">New insights are on the way.</h2><p class="mx-auto mt-2 max-w-md text-sm leading-7 text-slate-600">Our operations desk is preparing practical notes about freight, customs and cargo movement.</p><a class="mt-5 inline-flex min-h-11 items-center gap-3 rounded-full bg-[#071426] px-5 text-sm font-bold text-white" href="<?php echo e(base_url('pages/contact.php')); ?>">Talk to our team <i class="fa-solid fa-arrow-right text-xs"></i></a></div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
