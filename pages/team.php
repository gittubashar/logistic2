<?php
require_once __DIR__ . '/../includes/config.php';
$page = page_content('team');
$pageTitle = page_browser_title($page);
require_once __DIR__ . '/../includes/header.php';
$pageHeaderKicker = $page['header_kicker'];
$pageHeaderTitle = $page['header_title'];
$pageHeaderText = $page['header_text'];
$pageHeaderImage = $page['header_image'];
require __DIR__ . '/../includes/page-header.php';
render_page_content_block($page);
$teamMembers = team_members([], true);
?>
<section class="bg-[#f4f5f2] py-14 lg:py-18">
    <div class="home-shell">
        <div class="mb-8 flex flex-col gap-3 border-b border-slate-300 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div><p class="home-eyebrow">Leadership team</p><h2 class="mt-4 text-2xl font-extrabold tracking-[-.035em] text-[#071426] sm:text-3xl">Experience behind every operation.</h2></div>
            <p class="max-w-md text-sm leading-7 text-slate-600">Business strategy, compliance and day-to-day logistics delivery.</p>
        </div>

        <?php if ($teamMembers): ?>
            <div class="grid border-y border-slate-300 md:grid-cols-2 md:gap-x-10">
                <?php foreach ($teamMembers as $index => $member): ?>
                    <article class="group grid grid-cols-[76px_1fr] gap-4 border-b border-slate-300 py-6 last:border-b-0 md:[&:nth-last-child(2)]:border-b-0">
                        <?php if (!empty($member['image'])): ?>
                            <img class="h-[76px] w-[76px] rounded-full border border-slate-200 bg-white object-cover object-top" src="<?php echo e(base_url($member['image'])); ?>" alt="<?php echo e($member['name']); ?>">
                        <?php else: ?>
                            <span class="grid h-[76px] w-[76px] place-items-center rounded-full border border-amber-200 bg-amber-50 font-mono text-sm font-bold text-amber-700"><?php echo e(strtoupper(substr(trim($member['name']), 0, 2))); ?></span>
                        <?php endif; ?>
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold uppercase tracking-[.16em] text-amber-700"><?php echo e($member['role']); ?></p>
                            <h3 class="mt-2 text-lg font-extrabold leading-6 text-[#071426]"><?php echo e($member['name']); ?></h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600"><?php echo e(team_member_excerpt($member['text'] ?? '', 16)); ?></p>
                            <a class="mt-3 inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[.12em] text-[#071426] transition group-hover:text-amber-700" href="<?php echo e(team_member_url($member)); ?>">View profile <i class="fa-solid fa-arrow-right text-[10px]"></i></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="border-y border-dashed border-slate-300 py-12 text-center text-sm text-slate-500"><p class="font-bold">No team members available.</p></div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
