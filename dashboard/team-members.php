<?php
require_once __DIR__ . '/../includes/config.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . base_url('login.php'));
    exit;
}

function team_member_upload(): ?string
{
    return upload_dashboard_file($_FILES['image_upload'] ?? [], 'team');
}

$allMembers = team_members($team, false);
$editId = trim($_GET['edit'] ?? '');
$message = '';
$error = '';
$pickedTeamImage = '';

if ($editId !== '' && isset($_GET['gallery_image']) && str_starts_with($_GET['gallery_image'], 'uploads/')) {
    $pickedTeamImage = trim($_GET['gallery_image']);
    $message = 'Team member image selected from gallery. Click Update Member to apply it.';
}

if (isset($_GET['updated'])) {
    $message = 'Team member image updated from gallery.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $deleteId = trim($_POST['id'] ?? '');
        $allMembers = array_values(array_filter($allMembers, fn (array $member): bool => $member['id'] !== $deleteId));
        save_team_members($allMembers);
        header('Location: ' . base_url('dashboard/team-members.php?deleted=1'));
        exit;
    }

    if ($action === 'save') {
        $id = trim($_POST['id'] ?? '');
        $isNew = $id === '';
        $id = $isNew ? 'team-' . bin2hex(random_bytes(5)) : $id;
        $existing = find_team_member($allMembers, $id);
        $image = trim($_POST['image'] ?? ($existing['image'] ?? ''));
        $teamUpload = $_FILES['image_upload'] ?? [];
        $uploaded = team_member_upload();
        $image = $uploaded ?: $image;
        if (!$uploaded && dashboard_upload_was_requested($teamUpload)) {
            $error = dashboard_upload_last_error() ?: 'Team member image could not be uploaded.';
        }

        $memberData = normalize_team_member([
            'id' => $id,
            'name' => trim($_POST['name'] ?? ''),
            'role' => trim($_POST['role'] ?? ''),
            'image' => $image,
            'text' => trim($_POST['text'] ?? ''),
            'sort_order' => (int) ($_POST['sort_order'] ?? 1),
            'visible' => isset($_POST['visible']),
        ]);

        if ($error === '' && $memberData['name'] === '') {
            $error = 'Team member name is required.';
        } elseif ($error === '') {
            if ($isNew) {
                $allMembers[] = $memberData;
            } else {
                foreach ($allMembers as &$member) {
                    if ($member['id'] === $id) {
                        $member = $memberData;
                        break;
                    }
                }
                unset($member);
            }

            save_team_members($allMembers);
            header('Location: ' . base_url('dashboard/team-members.php?edit=' . rawurlencode($id) . '&saved=1'));
            exit;
        }
    }
}

if (isset($_GET['saved'])) {
    $message = 'Team member saved successfully.';
} elseif (isset($_GET['deleted'])) {
    $message = 'Team member deleted successfully.';
}

$allMembers = team_members($team, false);
$editingMember = $editId !== '' ? find_team_member($allMembers, $editId) : null;
$formMember = $editingMember ?: [
    'id' => '',
    'name' => '',
    'role' => '',
    'image' => '',
    'text' => '',
    'sort_order' => count($allMembers) + 1,
    'visible' => true,
];

if ($pickedTeamImage !== '') {
    $formMember['image'] = $pickedTeamImage;
}

$pageTitle = 'Team Members - ' . $site['title'];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($pageTitle); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-slate-100 text-slate-700">
<div class="flex min-h-screen">
    <?php require __DIR__ . '/sidebar.php'; ?>
    <div class="flex min-h-screen min-w-0 flex-1 flex-col">
        <?php require __DIR__ . '/topbar.php'; ?>
        <main class="px-4 pb-5 pt-0 lg:px-6 lg:pb-8 lg:pt-0">
            <?php if ($message): ?>
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800"><?php echo e($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700"><?php echo e($error); ?></div>
            <?php endif; ?>

            <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-blue-700">Team Control</p>
                    <h1 class="text-xl font-black text-slate-950"><?php echo $formMember['id'] ? 'Edit Team Member' : 'Add Team Member'; ?></h1>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-50" href="<?php echo e(base_url('dashboard/team-members.php')); ?>">
                        <i class="fa-solid fa-plus mr-2"></i>New Member
                    </a>
                    <a class="inline-flex items-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-black text-white hover:bg-slate-950" href="<?php echo e(base_url('pages/team.php')); ?>" target="_blank">
                        <i class="fa-solid fa-arrow-up-right-from-square mr-2"></i>View Page
                    </a>
                </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-[320px_minmax(0,1fr)]">
                <aside class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm xl:sticky xl:top-20 xl:self-start">
                    <div class="mb-3 flex items-center justify-between border-b border-slate-200 px-2 pb-3">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-blue-700">Members</p>
                            <h2 class="text-lg font-black text-slate-950"><?php echo count($allMembers); ?> people</h2>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700"><?php echo count(array_filter($allMembers, fn (array $member): bool => $member['visible'])); ?> visible</span>
                    </div>
                    <div class="grid gap-2">
                        <?php if (!$allMembers): ?>
                            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 text-center text-sm font-bold text-slate-500">No team members yet.</div>
                        <?php endif; ?>
                        <?php foreach ($allMembers as $member): ?>
                            <?php $isActiveMember = ($formMember['id'] ?? '') === ($member['id'] ?? ''); ?>
                            <a class="grid grid-cols-[48px_1fr] gap-3 rounded-xl border px-3 py-3 transition <?php echo $isActiveMember ? 'border-blue-200 bg-blue-50' : 'border-transparent hover:border-slate-200 hover:bg-slate-50'; ?>" href="<?php echo e(base_url('dashboard/team-members.php?edit=' . rawurlencode($member['id']))); ?>">
                                <img class="h-12 w-12 rounded-xl border border-slate-200 bg-white object-cover p-0.5" src="<?php echo e(base_url($member['image'])); ?>" alt="<?php echo e($member['name']); ?>">
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-black text-slate-950"><?php echo e($member['name']); ?></span>
                                    <span class="mt-0.5 block truncate text-xs font-bold text-blue-700"><?php echo e($member['role']); ?></span>
                                    <span class="mt-1 inline-flex rounded-full px-2 py-0.5 text-[10px] font-black <?php echo $member['visible'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500'; ?>"><?php echo $member['visible'] ? 'Visible' : 'Hidden'; ?></span>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </aside>

                <form class="min-w-0 rounded-2xl border border-slate-200 bg-white shadow-sm" method="post" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id" value="<?php echo e($formMember['id']); ?>">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.2em] text-blue-700"><?php echo $formMember['id'] ? 'Editing' : 'Creating'; ?></p>
                            <h2 class="mt-1 text-2xl font-black text-slate-950"><?php echo e($formMember['name'] ?: 'New Team Member'); ?></h2>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <label class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-black">
                                <input class="accent-blue-700" type="checkbox" name="visible" <?php echo ($formMember['visible'] ?? true) ? 'checked' : ''; ?>>
                                Show
                            </label>
                        </div>
                    </div>

                    <div class="grid gap-5 p-5">
                        <section class="grid gap-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 lg:grid-cols-[240px_minmax(0,1fr)]">
                            <div>
                                <img class="aspect-[4/5] w-full rounded-2xl border border-slate-200 bg-white object-cover p-1 shadow-sm" src="<?php echo e(base_url($formMember['image'])); ?>" alt="">
                                <p class="mt-3 rounded-xl bg-emerald-50 px-3 py-2 text-xs font-bold leading-5 text-emerald-800">Recommended size: 800 x 1000 px. Keep all team photos in similar ratio.</p>
                            </div>
                            <div class="grid content-start gap-4">
                                <label class="block text-sm font-black text-slate-600">Image Path
                                    <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-blue-500" name="image" value="<?php echo e($formMember['image']); ?>" placeholder="uploads/team-member.svg">
                                </label>
                                <div class="grid gap-2 sm:grid-cols-2">
                                <?php if ($formMember['id']): ?>
                                    <a class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-black text-blue-700 hover:bg-blue-100" href="<?php echo e(base_url('dashboard/gallery.php?select=team_member&member=' . rawurlencode($formMember['id']))); ?>">
                                        <i class="fa-solid fa-images mr-2"></i>Pick from gallery
                                    </a>
                                <?php else: ?>
                                    <span class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-center text-xs font-black text-slate-500">Save first for gallery pick</span>
                                <?php endif; ?>
                                <label class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-black text-slate-700 hover:bg-slate-50">
                                    <i class="fa-solid fa-upload mr-2"></i>Pick from computer
                                    <input class="hidden" type="file" name="image_upload" accept="image/*">
                                </label>
                                </div>
                            </div>
                        </section>

                        <div class="grid gap-4 lg:grid-cols-[1fr_1fr_160px]">
                            <label class="block text-sm font-black text-slate-600">Name
                                <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-500" name="name" value="<?php echo e($formMember['name']); ?>" required>
                            </label>
                            <label class="block text-sm font-black text-slate-600">Role
                                <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-500" name="role" value="<?php echo e($formMember['role']); ?>">
                            </label>
                            <label class="block text-sm font-black text-slate-600">Sort Order
                                <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-500" type="number" name="sort_order" value="<?php echo e((string) $formMember['sort_order']); ?>">
                            </label>
                        </div>
                        <label class="block text-sm font-black text-slate-600">Bio
                            <textarea class="mt-2 min-h-64 w-full rounded-xl border border-slate-300 px-4 py-3 leading-7 outline-none focus:border-blue-500" name="text" placeholder="Write professional biography, education, experience and operational responsibility."><?php echo e($formMember['text']); ?></textarea>
                        </label>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <?php if ($formMember['id']): ?>
                            <button class="rounded-xl border border-red-200 bg-red-50 px-5 py-3 font-black text-red-700 hover:bg-red-100" type="submit" form="delete-member-form" onclick="return confirm('Delete this team member?')">
                                <i class="fa-solid fa-trash mr-2"></i>Delete
                            </button>
                        <?php endif; ?>
                        <button class="rounded-xl bg-blue-700 px-5 py-3 font-black text-white shadow-lg shadow-blue-700/20 hover:bg-slate-950" type="submit">
                            <i class="fa-solid fa-floppy-disk mr-2"></i><?php echo $formMember['id'] ? 'Update Member' : 'Add Member'; ?>
                        </button>
                    </div>
                </form>

                <?php if ($formMember['id']): ?>
                    <form id="delete-member-form" method="post">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo e($formMember['id']); ?>">
                    </form>
                <?php endif; ?>

            </div>
        </main>
        <?php require __DIR__ . '/footer.php'; ?>
    </div>
</div>
</body>
</html>
