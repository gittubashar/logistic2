<?php
require_once __DIR__ . '/../includes/config.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . base_url('login.php'));
    exit;
}

function membership_upload_from_array(array $files, int $index): array
{
    return [
        'name' => $files['name'][$index] ?? '',
        'type' => $files['type'][$index] ?? '',
        'tmp_name' => $files['tmp_name'][$index] ?? '',
        'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
        'size' => $files['size'][$index] ?? 0,
    ];
}

$message = '';
$membership = membership_certificates(false);
$newItem = ['id' => '', 'title' => '', 'type' => '', 'issuer' => '', 'date' => '', 'description' => '', 'image' => '', 'document' => '', 'visible' => true];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $items = [];
    $imageUploads = $_FILES['image_upload'] ?? [];
    $documentUploads = $_FILES['document_upload'] ?? [];

    foreach (($_POST['title'] ?? []) as $index => $title) {
        $title = trim((string) $title);
        $description = trim($_POST['description'][$index] ?? '');

        if ($title === '' && $description === '') {
            continue;
        }

        if (isset($_POST['delete'][$index])) {
            continue;
        }

        $image = trim($_POST['image'][$index] ?? '');
        if (isset($imageUploads['name'][$index])) {
            $uploadedImage = upload_dashboard_file(membership_upload_from_array($imageUploads, $index), 'certificates');
            $image = $uploadedImage ?: $image;
        }

        $document = trim($_POST['document'][$index] ?? '');
        if (isset($documentUploads['name'][$index])) {
            $uploadedDocument = upload_dashboard_file(membership_upload_from_array($documentUploads, $index), 'certificates');
            $document = $uploadedDocument ?: $document;
        }

        $items[] = [
            'id' => trim($_POST['id'][$index] ?? '') ?: membership_certificate_id(),
            'title' => $title,
            'type' => trim($_POST['type'][$index] ?? ''),
            'issuer' => trim($_POST['issuer'][$index] ?? ''),
            'date' => trim($_POST['date'][$index] ?? ''),
            'description' => $description,
            'image' => $image,
            'document' => $document,
            'visible' => isset($_POST['visible'][$index]),
        ];
    }

    $membership = [
        'kicker' => trim($_POST['kicker'] ?? 'Membership & Certificates'),
        'title' => trim($_POST['page_title'] ?? ''),
        'subtitle' => trim($_POST['subtitle'] ?? ''),
        'items' => $items,
    ];

    $message = save_membership_certificates($membership) ? 'Membership & Certificates saved successfully.' : 'Unable to save membership data.';
    $membership = membership_certificates(false);
}

$pageTitle = 'Membership & Certificates - ' . $site['title'];
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
        <main class="px-5 pb-5 pt-0 lg:px-8 lg:pb-8 lg:pt-0">
            <?php if ($message): ?>
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800"><?php echo e($message); ?></div>
            <?php endif; ?>

            <form class="grid gap-4" method="post" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="grid gap-3 lg:grid-cols-[220px_1fr_1.4fr]">
                        <label class="block text-xs font-black uppercase tracking-wide text-slate-600">Kicker
                            <input class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="kicker" value="<?php echo e($membership['kicker'] ?? 'Membership & Certificates'); ?>">
                        </label>
                        <label class="block text-xs font-black uppercase tracking-wide text-slate-600">Page Title
                            <input class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="page_title" value="<?php echo e($membership['title'] ?? ''); ?>">
                        </label>
                        <label class="block text-xs font-black uppercase tracking-wide text-slate-600">Subtitle
                            <input class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="subtitle" value="<?php echo e($membership['subtitle'] ?? ''); ?>">
                        </label>
                    </div>
                </section>

                <div class="grid gap-4 xl:grid-cols-2">
                    <?php foreach (array_merge([$newItem], $membership['items'] ?? []) as $index => $item): ?>
                        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <input type="hidden" name="id[]" value="<?php echo e($item['id'] ?? ''); ?>">
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3">
                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-blue-700"><?php echo $index === 0 ? 'Add New' : 'Certificate Item'; ?></p>
                                    <h2 class="mt-1 text-lg font-black text-slate-950"><?php echo e(($item['title'] ?? '') ?: 'New Membership / Certificate'); ?></h2>
                                </div>
                                <div class="flex items-center gap-2">
                                    <label class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-2 text-xs font-black text-slate-700">
                                        <input class="accent-blue-700" type="checkbox" name="visible[<?php echo $index; ?>]" <?php echo ($item['visible'] ?? true) ? 'checked' : ''; ?>>
                                        Show
                                    </label>
                                    <?php if ($index !== 0): ?>
                                        <label class="inline-flex items-center gap-2 rounded-lg bg-red-50 px-3 py-2 text-xs font-black text-red-700">
                                            <input class="accent-red-600" type="checkbox" name="delete[<?php echo $index; ?>]">
                                            Delete
                                        </label>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="grid gap-3">
                                <div class="grid gap-3 md:grid-cols-2">
                                    <label class="block text-xs font-black uppercase tracking-wide text-slate-600">Title
                                        <input class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="title[]" value="<?php echo e($item['title'] ?? ''); ?>" placeholder="BAFFA Membership">
                                    </label>
                                    <label class="block text-xs font-black uppercase tracking-wide text-slate-600">Type
                                        <input class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="type[]" value="<?php echo e($item['type'] ?? ''); ?>" placeholder="Membership / License">
                                    </label>
                                </div>
                                <div class="grid gap-3 md:grid-cols-2">
                                    <label class="block text-xs font-black uppercase tracking-wide text-slate-600">Issuer
                                        <input class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="issuer[]" value="<?php echo e($item['issuer'] ?? ''); ?>" placeholder="Issuing authority">
                                    </label>
                                    <label class="block text-xs font-black uppercase tracking-wide text-slate-600">Date / Status
                                        <input class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="date[]" value="<?php echo e($item['date'] ?? ''); ?>" placeholder="Active / Since 2018">
                                    </label>
                                </div>
                                <label class="block text-xs font-black uppercase tracking-wide text-slate-600">Description
                                    <textarea class="mt-1.5 min-h-20 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm leading-6" name="description[]" placeholder="Short credential details"><?php echo e($item['description'] ?? ''); ?></textarea>
                                </label>
                                <div class="grid gap-3 md:grid-cols-2">
                                    <label class="block text-xs font-black uppercase tracking-wide text-slate-600">Image Path
                                        <input class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="image[]" value="<?php echo e($item['image'] ?? ''); ?>" placeholder="uploads/certificates/image.jpg">
                                        <input class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs" type="file" name="image_upload[]" accept="image/*">
                                    </label>
                                    <label class="block text-xs font-black uppercase tracking-wide text-slate-600">Document Path
                                        <input class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="document[]" value="<?php echo e($item['document'] ?? ''); ?>" placeholder="uploads/certificates/file.pdf">
                                        <input class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs" type="file" name="document_upload[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp">
                                    </label>
                                </div>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>

                <div class="flex justify-end gap-3">
                    <a class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-black text-slate-700 hover:bg-slate-50" href="<?php echo e(base_url('pages/membership-certificates.php')); ?>" target="_blank">
                        <i class="fa-solid fa-arrow-up-right-from-square mr-2"></i>View Page
                    </a>
                    <button class="inline-flex items-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-black text-white shadow-lg shadow-blue-700/20 hover:bg-slate-950" type="submit">
                        <i class="fa-solid fa-floppy-disk mr-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </main>
        <?php require __DIR__ . '/footer.php'; ?>
    </div>
</div>
</body>
</html>
