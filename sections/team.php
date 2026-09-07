<?php
require_once __DIR__ . '/../includes/config.php';
$section = section_content('team');
if (($section['visible'] ?? true)):
?>
<section class="bg-[#f4f5f2] py-20 lg:py-28">
    <div class="home-shell">
        <div class="mb-10 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div><p class="home-eyebrow"><?php echo e($section['kicker'] ?? 'Team'); ?></p><h2 class="home-heading mt-5 max-w-3xl"><?php echo e($section['title'] ?? ('Leadership behind ' . $site['title'])); ?></h2></div>
            <a class="home-text-link text-[#071426] hover:text-amber-700" href="<?php echo e(base_url('pages/team.php')); ?>">Meet everyone <i class="fa-solid fa-arrow-right text-xs"></i></a>
        </div>
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            <?php foreach ($team as $index => $member): ?>
                <article class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-[0_22px_55px_rgba(7,20,38,.09)]">
                    <?php if (!empty($member['image'])): ?>
                        <img class="aspect-[4/4.5] w-full object-cover object-top transition duration-500 group-hover:scale-[1.03]" src="<?php echo e(base_url($member['image'])); ?>" alt="<?php echo e($member['name']); ?>">
                    <?php else: ?>
                        <div class="grid aspect-[4/4.5] place-items-center bg-amber-50 font-mono text-2xl font-bold text-amber-700"><?php echo e(strtoupper(substr(trim($member['name']), 0, 2))); ?></div>
                    <?php endif; ?>
                    <div class="p-6"><p class="text-[11px] font-bold uppercase tracking-[.16em] text-amber-700"><?php echo e($member['role']); ?></p><h3 class="mt-3 text-xl font-extrabold leading-7 text-[#071426]"><?php echo e($member['name']); ?></h3><a class="mt-5 inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[#071426]" href="<?php echo e(team_member_url($member)); ?>">View profile <i class="fa-solid fa-arrow-right text-amber-600"></i></a></div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
