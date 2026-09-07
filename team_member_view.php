<?php
require_once __DIR__ . '/includes/config.php';

$memberId = trim($_GET['id'] ?? '');
$member = $memberId !== '' ? find_team_member(team_members($team, false), $memberId) : null;

if (!$member || (!($member['visible'] ?? true) && empty($_SESSION['admin_logged_in']))) {
    http_response_code(404);
    $pageTitle = 'Team Member Not Found - ' . $site['title'];
    require_once __DIR__ . '/includes/header.php';
    $pageHeaderKicker = 'Team';
    $pageHeaderTitle = 'Team member not found';
    $pageHeaderText = 'The requested leadership profile is not available.';
    require __DIR__ . '/includes/page-header.php';
    ?>
    <section class="bg-[#f4f5f2] px-4 py-20 text-center"><a class="inline-flex rounded-full bg-[#071426] px-6 py-3 font-bold text-white" href="<?php echo e(base_url('pages/team.php')); ?>">Back to Team</a></section>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $member['name'] . ' - ' . $site['title'];
require_once __DIR__ . '/includes/header.php';
$pageHeaderKicker = $member['role'] ?: 'Team';
$pageHeaderTitle = $member['name'];
$pageHeaderText = 'Leadership profile of ' . $site['title'];
$pageHeaderImage = 'uploads/page-header-bg.svg';
require __DIR__ . '/includes/page-header.php';
?>
<section class="bg-[#f4f5f2] py-16 lg:py-24">
    <div class="home-shell">
        <div class="grid overflow-hidden border-y border-slate-300 lg:grid-cols-[260px_1fr] lg:gap-12">
            <div class="relative min-h-[320px] bg-slate-100 lg:min-h-[380px]">
                <?php if (!empty($member['image'])): ?>
                    <img class="absolute inset-0 h-full w-full object-cover object-top" src="<?php echo e(base_url($member['image'])); ?>" alt="<?php echo e($member['name']); ?>">
                <?php else: ?>
                    <div class="grid h-full place-items-center bg-amber-50 font-mono text-4xl font-bold text-amber-700"><?php echo e(strtoupper(substr(trim($member['name']), 0, 2))); ?></div>
                <?php endif; ?>
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-[#071426] to-transparent p-5 pt-20 text-white">
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-amber-300"><?php echo e($member['role']); ?></p>
                    <h2 class="mt-2 text-2xl font-extrabold text-white"><?php echo e($member['name']); ?></h2>
                </div>
            </div>
            <article class="py-8 sm:py-10 lg:py-12">
                <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-7">
                    <p class="home-eyebrow">Biography</p>
                    <a class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500 hover:text-amber-700" href="<?php echo e(base_url('pages/team.php')); ?>"><i class="fa-solid fa-arrow-left"></i> Team</a>
                </div>
                <div class="mt-8 whitespace-pre-line text-base leading-8 text-slate-600"><?php echo e($member['text']); ?></div>
                <div class="mt-10 border-t border-slate-300 pt-6">
                    <p class="text-sm leading-7 text-slate-600">Connect with our team for logistics planning and operational support.</p>
                    <a class="mt-4 inline-flex items-center gap-3 text-sm font-bold text-[#071426]" href="<?php echo e(base_url('pages/contact.php')); ?>">Contact company <i class="fa-solid fa-arrow-right text-xs text-amber-700"></i></a>
                </div>
            </article>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
