<?php
require_once __DIR__ . '/../includes/config.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . base_url('login.php'));
    exit;
}

$allConcerns = our_concerns($concerns, false);
$editId = trim((string) ($_GET['edit'] ?? ''));
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? 'save_concern';

    if ($action === 'delete_concern') {
        $deleteId = trim((string) ($_POST['id'] ?? ''));
        $allConcerns = array_values(array_filter($allConcerns, fn (array $item): bool => $item['id'] !== $deleteId));
        if (save_our_concerns($allConcerns)) {
            header('Location: ' . base_url('dashboard/our-concern.php?deleted=1'));
            exit;
        }
        $error = 'Unable to delete concern.';
    }

    if ($action === 'save_concern') {
        $id = trim((string) ($_POST['id'] ?? ''));
        $isNew = $id === '';
        $id = $isNew ? 'concern-' . bin2hex(random_bytes(5)) : $id;
        $existing = find_our_concern($allConcerns, $id);
        $image = trim((string) ($_POST['image'] ?? ($existing['image'] ?? '')));
        $uploaded = upload_dashboard_file($_FILES['image_upload'] ?? [], 'concerns');
        $image = $uploaded ?: $image;
        $title = trim((string) ($_POST['title'] ?? ''));

        $item = normalize_our_concern([
            'id' => $id,
            'title' => $title,
            'image' => $image,
            'website' => trim((string) ($_POST['website'] ?? '')),
            'social_link' => trim((string) ($_POST['social_link'] ?? '')),
            'about_concern' => trim((string) ($_POST['about_concern'] ?? '')),
            'sort_order' => (int) ($_POST['sort_order'] ?? 1),
            'visible' => isset($_POST['visible']),
        ]);

        if ($item['title'] === '') {
            $error = 'Concern title is required.';
        } else {
            if ($isNew) {
                $allConcerns[] = $item;
            } else {
                foreach ($allConcerns as &$savedItem) {
                    if ($savedItem['id'] === $id) {
                        $savedItem = $item;
                        break;
                    }
                }
                unset($savedItem);
            }

            usort($allConcerns, fn (array $a, array $b): int => $a['sort_order'] <=> $b['sort_order']);
            if (save_our_concerns($allConcerns)) {
                header('Location: ' . base_url('dashboard/our-concern.php?edit=' . rawurlencode($id) . '&saved=1'));
                exit;
            }
            $error = 'Unable to save concern.';
        }
    }
}

$allConcerns = our_concerns($concerns, false);
$editing = $editId !== '' ? find_our_concern($allConcerns, $editId) : null;
$formItem = $editing ?: [
    'id' => '',
    'title' => '',
    'image' => '',
    'website' => '',
    'social_link' => '',
    'about_concern' => '',
    'sort_order' => count($allConcerns) + 1,
    'visible' => true,
];

if (isset($_GET['saved'])) {
    $message = 'Concern saved successfully.';
} elseif (isset($_GET['deleted'])) {
    $message = 'Concern deleted successfully.';
}

$pageTitle = 'Our Concern - ' . $site['title'];
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
        <main class="px-3 pb-5 pt-0 lg:px-5 lg:pb-8">
            <?php if ($message): ?><div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800"><?php echo e($message); ?></div><?php endif; ?>
            <?php if ($error): ?><div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700"><?php echo e($error); ?></div><?php endif; ?>

            <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div><p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-700">Content control</p><h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Our Concern</h1><p class="mt-1 text-sm text-slate-500">Manage the concerns shown as cards on the public page.</p></div>
                <a class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-600 hover:border-amber-300 hover:text-amber-700" href="<?php echo e(base_url('pages/our-concern.php')); ?>" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square"></i>Preview page</a>
            </div>

            <div class="grid gap-5 xl:grid-cols-[minmax(320px,360px)_minmax(0,1fr)]">
                <section class="h-fit rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:p-5">
                    <div class="mb-4 flex items-center justify-between border-b border-slate-200 pb-3"><div><p class="text-[10px] font-black uppercase tracking-[.16em] text-blue-700"><?php echo $editing ? 'Edit concern' : 'Add concern'; ?></p><h2 class="mt-1 text-lg font-black text-slate-950"><?php echo e($formItem['title'] ?: 'New concern'); ?></h2></div><?php if ($editing): ?><a class="text-xs font-bold text-slate-400 hover:text-blue-700" href="<?php echo e(base_url('dashboard/our-concern.php')); ?>">Clear</a><?php endif; ?></div>
                    <form method="post" enctype="multipart/form-data" class="grid gap-3">
                        <?php echo csrf_field(); ?><input type="hidden" name="action" value="save_concern"><input type="hidden" name="id" value="<?php echo e($formItem['id']); ?>">
                        <label class="text-[10px] font-black uppercase tracking-wide text-slate-500">Title<input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm font-semibold outline-none focus:border-blue-500" name="title" value="<?php echo e($formItem['title']); ?>" placeholder="Concern title" required></label>
                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1"><label class="text-[10px] font-black uppercase tracking-wide text-slate-500">Website<input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" type="url" name="website" value="<?php echo e($formItem['website']); ?>" placeholder="https://example.com"></label><label class="text-[10px] font-black uppercase tracking-wide text-slate-500">Social media link<input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" type="url" name="social_link" value="<?php echo e($formItem['social_link']); ?>" placeholder="https://facebook.com/..."></label></div>
                        <label class="text-[10px] font-black uppercase tracking-wide text-slate-500">About concern<textarea class="mt-1 min-h-28 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm leading-6" name="about_concern" placeholder="Short profile or description"><?php echo e($formItem['about_concern']); ?></textarea></label>
                        <label class="text-[10px] font-black uppercase tracking-wide text-slate-500">Portfolio image<input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" type="file" name="image_upload" accept="image/*"><input class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs" name="image" value="<?php echo e($formItem['image']); ?>" placeholder="Or enter uploads path"></label>
                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1"><label class="text-[10px] font-black uppercase tracking-wide text-slate-500">Display order<input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" type="number" name="sort_order" min="1" value="<?php echo e((string) $formItem['sort_order']); ?>"></label><label class="flex items-center gap-2 self-end rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-bold text-slate-600"><input class="h-4 w-4 accent-blue-700" type="checkbox" name="visible" <?php echo $formItem['visible'] ? 'checked' : ''; ?>>Show on public page</label></div>
                        <button class="mt-1 inline-flex items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 py-3 text-sm font-black text-white hover:bg-slate-950" type="submit"><i class="fa-solid fa-floppy-disk"></i><?php echo $editing ? 'Update concern' : 'Add concern'; ?></button>
                    </form>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:p-5">
                    <div class="mb-4 flex items-center justify-between border-b border-slate-200 pb-3"><div><p class="text-[10px] font-black uppercase tracking-[.16em] text-amber-700">Published concerns</p><h2 class="mt-1 text-lg font-black text-slate-950"><?php echo count($allConcerns); ?> total</h2></div><span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-slate-500">Card directory</span></div>
                    <?php if ($allConcerns): ?>
                        <div class="grid gap-3 sm:grid-cols-2 2xl:grid-cols-3">
                            <?php foreach ($allConcerns as $item): ?>
                                <article class="overflow-hidden rounded-xl border border-slate-200 <?php echo $item['visible'] ? 'bg-white' : 'bg-slate-50 opacity-60'; ?>">
                                    <div class="flex gap-3 p-3"><div class="grid h-14 w-20 shrink-0 place-items-center overflow-hidden rounded-lg bg-[#102845]"><?php if ($item['image']): ?><img class="h-full w-full object-cover" src="<?php echo e(base_url($item['image'])); ?>" alt="" loading="lazy"><?php else: ?><span class="text-sm font-black tracking-widest text-amber-300"><?php echo e(strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $item['title']) ?: 'OC', 0, 2))); ?></span><?php endif; ?></div><div class="min-w-0 flex-1"><p class="text-[9px] font-black uppercase tracking-[.15em] text-amber-700">#<?php echo e((string) $item['sort_order']); ?><?php if (!$item['visible']): ?> · Hidden<?php endif; ?></p><h3 class="mt-1 truncate text-sm font-black text-slate-950" title="<?php echo e($item['title']); ?>"><?php echo e($item['title']); ?></h3></div></div>
                                    <div class="flex items-center justify-between border-t border-slate-100 px-3 py-2"><a class="text-[10px] font-black uppercase tracking-wide text-blue-700 hover:text-blue-900" href="<?php echo e(base_url('dashboard/our-concern.php?edit=' . rawurlencode($item['id']))); ?>"><i class="fa-solid fa-pen-to-square mr-1"></i>Edit</a><form method="post" onsubmit="return confirm('Delete this concern?');"><?php echo csrf_field(); ?><input type="hidden" name="action" value="delete_concern"><input type="hidden" name="id" value="<?php echo e($item['id']); ?>"><button class="text-[10px] font-black uppercase tracking-wide text-red-600 hover:text-red-800" type="submit"><i class="fa-solid fa-trash mr-1"></i>Delete</button></form></div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?><div class="border-y border-dashed border-slate-300 py-12 text-center text-sm text-slate-500">No concerns yet. Add the first one from the form.</div><?php endif; ?>
                </section>
            </div>
        </main>
        <?php require __DIR__ . '/footer.php'; ?>
    </div>
</div>
</body>
</html>
