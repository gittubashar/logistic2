<?php
require_once __DIR__ . '/includes/config.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$post = $slug !== '' ? post_by_slug($slug) : null;

if (!$post) {
    http_response_code(404);
    $pageTitle = 'Article Not Found - ' . $site['title'];
    require_once __DIR__ . '/includes/header.php';
    $pageHeaderKicker = 'Insights';
    $pageHeaderTitle = 'Article not found';
    $pageHeaderText = 'The requested insight is not available.';
    require __DIR__ . '/includes/page-header.php';
    ?>
    <section class="bg-[#f4f5f2] px-4 py-20 text-center">
        <a class="inline-flex min-h-12 items-center gap-3 rounded-full bg-[#071426] px-6 text-sm font-bold text-white" href="<?php echo e(base_url('blog.php')); ?>">Back to insights <i class="fa-solid fa-arrow-left text-xs"></i></a>
    </section>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $post['title'] . ' - ' . $site['title'];
$contentHtml = page_clean_html((string) ($post['content_html'] ?? ''));
require_once __DIR__ . '/includes/header.php';
$pageHeaderKicker = 'Insight · ' . date('M j, Y', strtotime((string) ($post['published_at'] ?? 'now')));
$pageHeaderTitle = $post['title'];
$pageHeaderText = $post['excerpt'] ?? '';
$pageHeaderImage = $post['image'] ?? 'uploads/page-header-bg.svg';
require __DIR__ . '/includes/page-header.php';
?>
<section class="bg-[#f4f5f2] py-16 lg:py-24">
    <div class="home-shell grid gap-8 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start">
        <article class="overflow-hidden border-b border-slate-300">
            <?php if (!empty($post['image'])): ?><img class="max-h-[520px] w-full object-cover" src="<?php echo e(base_url($post['image'])); ?>" alt="<?php echo e($post['title']); ?>"><?php endif; ?>
            <div class="py-7 sm:py-10 lg:py-12">
                <div class="prose max-w-none text-base leading-8 text-slate-600 [&_a]:font-bold [&_a]:text-amber-700 [&_a]:underline [&_a]:decoration-amber-300 [&_a]:underline-offset-4 [&_blockquote]:rounded-r-2xl [&_blockquote]:border-l-4 [&_blockquote]:border-amber-400 [&_blockquote]:bg-amber-50 [&_blockquote]:px-6 [&_blockquote]:py-4 [&_h2]:pt-3 [&_h2]:text-3xl [&_h2]:font-extrabold [&_h2]:tracking-[-.035em] [&_h2]:text-[#071426] [&_h3]:pt-2 [&_h3]:text-2xl [&_h3]:font-bold [&_h3]:text-[#071426] [&_img]:my-8 [&_img]:w-full [&_img]:rounded-2xl [&_li]:ml-5 [&_li]:list-disc [&_li]:marker:text-amber-500 [&_ol_li]:list-decimal [&_p:first-child]:text-lg [&_p:first-child]:font-semibold [&_p:first-child]:leading-9 [&_p:first-child]:text-[#24364b]">
                    <?php echo $contentHtml !== '' ? $contentHtml : '<p>' . e((string) ($post['excerpt'] ?? '')) . '</p>'; ?>
                </div>
                <a class="mt-10 inline-flex items-center gap-3 border-t border-slate-200 pt-7 text-xs font-bold uppercase tracking-[.12em] text-[#071426]" href="<?php echo e(base_url('blog.php')); ?>"><i class="fa-solid fa-arrow-left text-amber-600"></i> Back to insights</a>
            </div>
        </article>
        <aside class="border-t border-slate-300 pt-6 lg:sticky lg:top-32 lg:border-l lg:border-t-0 lg:pl-8">
            <span class="grid h-10 w-10 place-items-center rounded-full bg-amber-400 text-[#071426]"><i class="fa-solid fa-headset"></i></span>
            <h2 class="mt-5 text-xl font-extrabold tracking-[-.03em] text-[#071426]">Need help with a shipment?</h2>
            <p class="mt-3 text-sm leading-7 text-slate-600">Turn the insight into an operating plan with our freight and customs team.</p>
            <a class="mt-6 inline-flex min-h-11 w-full items-center justify-center gap-3 rounded-full bg-[#071426] px-5 text-sm font-bold text-white transition hover:bg-[#102845]" href="<?php echo e(base_url('pages/contact.php')); ?>">Start a conversation <i class="fa-solid fa-arrow-right text-xs"></i></a>
        </aside>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
