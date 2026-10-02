<?php
require_once __DIR__ . '/../includes/config.php';

$page = page_content('our_concern');
$latestPosts = array_slice(posts_all(true), 0, 9);
$pageTitle = page_browser_title($page);
require_once __DIR__ . '/../includes/header.php';
$pageHeaderKicker = $page['header_kicker'] ?? 'Our Concern';
$pageHeaderTitle = $page['header_title'] ?? 'Our Concern';
$pageHeaderText = $page['header_text'] ?? '';
$pageHeaderImage = $page['header_image'] ?? 'uploads/page-header-bg.svg';
require __DIR__ . '/../includes/page-header.php';
?>
<section class="bg-[#f4f5f2] py-14 lg:py-20">
    <div class="home-shell">
        <?php if (!empty($page['content_html'])): ?>
            <div class="mb-8 max-w-3xl text-sm leading-7 text-slate-600"><?php echo page_clean_html((string) $page['content_html']); ?></div>
        <?php endif; ?>

        <?php if ($concerns): ?>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <?php foreach ($concerns as $concern): ?>
                    <a class="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-[0_18px_45px_rgba(7,20,38,.08)]" href="<?php echo e(concern_url($concern)); ?>">
                        <div class="relative aspect-[16/9] overflow-hidden bg-[#102845]">
                            <?php if (!empty($concern['image'])): ?>
                                <img class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]" src="<?php echo e(base_url($concern['image'])); ?>" alt="<?php echo e($concern['title']); ?>" loading="lazy">
                            <?php else: ?>
                                <div class="grid h-full place-items-center bg-gradient-to-br from-[#102845] to-[#071426] text-3xl font-black tracking-widest text-amber-300"><?php echo e(strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $concern['title']) ?: 'OC', 0, 2))); ?></div>
                            <?php endif; ?>
                            <span class="absolute right-3 top-3 grid h-8 w-8 place-items-center rounded-full bg-white/90 text-xs text-[#071426] shadow-sm transition group-hover:bg-amber-400"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                        </div>
                        <div class="p-5 text-center">
                            <h3 class="text-sm font-extrabold leading-5 tracking-[-.01em] text-[#071426] sm:text-base"><?php echo e($concern['title']); ?></h3>
                            <p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-600"><?php echo e(our_concern_summary($concern)); ?></p>
                            <?php if (!empty($concern['website'])): ?><p class="mt-3 truncate text-xs font-bold text-slate-400"><i class="fa-solid fa-globe mr-1 text-amber-600"></i><?php echo e($concern['website']); ?></p><?php endif; ?>
                            <span class="mt-4 inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-[10px] font-black uppercase tracking-[.14em] text-[#071426] transition group-hover:border-amber-400 group-hover:bg-amber-400"><span>Read More</span><i class="fa-solid fa-arrow-right text-[10px] text-amber-600 group-hover:text-[#071426]"></i></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="border-y border-dashed border-slate-300 py-12 text-center text-sm text-slate-500">No concerns published yet.</div>
        <?php endif; ?>
    </div>
</section>
<section class="bg-white py-14 lg:py-20">
    <div class="home-shell">
        <div class="mb-8 flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="home-eyebrow">From the operations desk</p>
                <h2 class="mt-3 text-2xl font-extrabold tracking-[-.035em] text-[#071426] sm:text-3xl">Latest posts</h2>
            </div>
            <a class="inline-flex items-center justify-center gap-2 rounded-full bg-[#071426] px-5 py-2.5 text-xs font-black uppercase tracking-[.14em] text-white transition hover:bg-[#102845]" href="<?php echo e(base_url('blog.php')); ?>">All Posts <i class="fa-solid fa-arrow-right text-[10px] text-amber-300"></i></a>
        </div>

        <?php if ($latestPosts): ?>
            <div class="grid gap-5 sm:grid-cols-2 md:grid-cols-3">
                <?php foreach ($latestPosts as $post): ?>
                    <?php $postUrl = base_url('post.php?slug=' . rawurlencode((string) $post['slug'])); ?>
                    <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-[0_18px_45px_rgba(7,20,38,.08)]">
                        <a class="block aspect-[16/9] overflow-hidden bg-[#102845]" href="<?php echo e($postUrl); ?>">
                            <?php if (!empty($post['image'])): ?>
                                <img class="h-full w-full object-cover transition duration-500 group-hover:scale-105" src="<?php echo e(base_url($post['image'])); ?>" alt="<?php echo e($post['title']); ?>" loading="lazy">
                            <?php else: ?>
                                <div class="grid h-full place-items-center text-2xl text-amber-300"><i class="fa-solid fa-compass-drafting"></i></div>
                            <?php endif; ?>
                        </a>
                        <div class="p-5">
                            <p class="text-[10px] font-black uppercase tracking-[.16em] text-amber-700"><?php echo e(date('M j, Y', strtotime((string) ($post['published_at'] ?? 'now')))); ?></p>
                            <h3 class="mt-2 line-clamp-2 text-lg font-extrabold leading-6 text-[#071426]"><a href="<?php echo e($postUrl); ?>"><?php echo e($post['title']); ?></a></h3>
                            <?php if (!empty($post['excerpt'])): ?><p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-600"><?php echo e($post['excerpt']); ?></p><?php endif; ?>
                            <a class="mt-4 inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-[.14em] text-[#071426] transition group-hover:text-amber-700" href="<?php echo e($postUrl); ?>">Read More <i class="fa-solid fa-arrow-right text-[10px] text-amber-600"></i></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="border-y border-dashed border-slate-300 py-10 text-center text-sm text-slate-500">No published posts yet.</div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
