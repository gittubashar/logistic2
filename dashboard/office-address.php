<?php
require_once __DIR__ . '/../includes/config.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . base_url('login.php'));
    exit;
}

$message = '';
$officeRows = $officeContacts ?? all_office_addresses($offices, $site);
$newOfficeRow = ['id' => '', 'title' => '', 'address' => '', 'phone_1' => '', 'phone_2' => '', 'phone_3' => '', 'email' => '', 'email_2' => '', 'email_3' => '', 'whatsapp' => '', 'map_embed_code' => '', 'visible' => true];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $officeRows = [];

    foreach (($_POST['title'] ?? []) as $index => $title) {
        if (trim($title) === '' && trim($_POST['address'][$index] ?? '') === '') {
            continue;
        }

        if (isset($_POST['delete'][$index])) {
            continue;
        }

        $officeRows[] = [
            'id' => trim($_POST['id'][$index] ?? '') ?: office_address_id(),
            'title' => trim($title),
            'address' => trim($_POST['address'][$index] ?? ''),
            'phone_1' => trim($_POST['phone_1'][$index] ?? ''),
            'phone_2' => trim($_POST['phone_2'][$index] ?? ''),
            'phone_3' => trim($_POST['phone_3'][$index] ?? ''),
            'email' => trim($_POST['email'][$index] ?? ''),
            'email_2' => trim($_POST['email_2'][$index] ?? ''),
            'email_3' => trim($_POST['email_3'][$index] ?? ''),
            'whatsapp' => trim($_POST['whatsapp'][$index] ?? ''),
            'map_embed_code' => trim($_POST['map_embed_code'][$index] ?? ''),
            'visible' => isset($_POST['visible'][$index]),
        ];
    }

    $message = save_office_addresses($officeRows) ? 'Office address saved successfully.' : 'Unable to save office address.';
}

$pageTitle = 'Office Address - ' . $site['title'];
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
                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 font-bold text-emerald-800"><?php echo e($message); ?></div>
            <?php endif; ?>

            <form class="grid gap-4" method="post" data-office-form>
                <?php echo csrf_field(); ?>
                <div class="grid gap-4 xl:grid-cols-3">
                    <?php foreach (array_merge([$newOfficeRow], $officeRows) as $index => $office): ?>
                        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <input type="hidden" name="id[]" value="<?php echo e($office['id'] ?? ''); ?>">
                            <div class="mb-3 flex items-center justify-between border-b border-slate-200 pb-3">
                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-blue-700"><?php echo $index === 0 ? 'Add Office' : 'Office Info'; ?></p>
                                    <h2 class="mt-1 text-lg font-black text-slate-950"><?php echo e($office['title'] ?: 'New Office'); ?></h2>
                                </div>
                                <div class="flex items-center gap-2">
                                    <label class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-2 text-xs font-black text-slate-700">
                                        <input class="accent-blue-700" type="checkbox" name="visible[<?php echo $index; ?>]" <?php echo ($office['visible'] ?? true) ? 'checked' : ''; ?>>
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
                                <label class="block text-xs font-black uppercase tracking-wide text-slate-600">Office Title
                                    <input class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="title[]" value="<?php echo e($office['title'] ?? ''); ?>" placeholder="Head Office">
                                </label>
                                <label class="block text-xs font-black uppercase tracking-wide text-slate-600">Address
                                    <textarea class="mt-1.5 min-h-20 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm leading-6" name="address[]" placeholder="Office address"><?php echo e($office['address'] ?? ''); ?></textarea>
                                </label>
                                <div class="grid gap-3 md:grid-cols-3">
                                    <label class="block text-xs font-black uppercase tracking-wide text-slate-600">Phone 1
                                        <input class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="phone_1[]" value="<?php echo e($office['phone_1'] ?? ''); ?>" placeholder="+880 1954194762">
                                    </label>
                                    <label class="block text-xs font-black uppercase tracking-wide text-slate-600">Phone 2
                                        <input class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="phone_2[]" value="<?php echo e($office['phone_2'] ?? ''); ?>" placeholder="+880 1954194762">
                                    </label>
                                    <label class="block text-xs font-black uppercase tracking-wide text-slate-600">Phone 3
                                        <input class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="phone_3[]" value="<?php echo e($office['phone_3'] ?? ''); ?>" placeholder="+880">
                                    </label>
                                </div>
                                <label class="block text-xs font-black uppercase tracking-wide text-slate-600">Email
                                    <input class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" type="email" name="email[]" value="<?php echo e($office['email'] ?? ''); ?>" placeholder="contact@bstradingship.com">
                                </label>
                                <div class="grid gap-3 md:grid-cols-3">
                                    <label class="block text-xs font-black uppercase tracking-wide text-slate-600">Email 2
                                    <input class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" type="email" name="email_2[]" value="<?php echo e($office['email_2'] ?? ''); ?>" placeholder="sales@bstradingship.com">
                                    </label>
                                    <label class="block text-xs font-black uppercase tracking-wide text-slate-600">Email 3
                                        <input class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" type="email" name="email_3[]" value="<?php echo e($office['email_3'] ?? ''); ?>" placeholder="support@example.com">
                                    </label>
                                    <label class="block text-xs font-black uppercase tracking-wide text-slate-600">WhatsApp
                                        <input class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="whatsapp[]" value="<?php echo e($office['whatsapp'] ?? ''); ?>" placeholder="+880 1954194762">
                                    </label>
                                </div>
                                <label class="block text-xs font-black uppercase tracking-wide text-slate-600">Google Map Embed URL / Code
                                    <textarea class="mt-1.5 min-h-16 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm leading-6" name="map_embed_code[]" data-map-embed placeholder="Paste iframe code or only the Google Maps src URL"><?php echo e($office['map_embed_code'] ?? ''); ?></textarea>
                                    <span class="mt-1 block text-[11px] font-bold normal-case tracking-normal text-slate-500">For 403-safe saving, iframe code will be converted to only its src URL before submit.</span>
                                </label>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>

                <div class="flex justify-end gap-3">
                    <a class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-black text-slate-700 hover:bg-slate-50" href="<?php echo e(base_url('dashboard/office-address.php')); ?>">
                        <i class="fa-solid fa-plus mr-2"></i>New
                    </a>
                    <button class="inline-flex items-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-black text-white shadow-lg shadow-blue-700/20 hover:bg-slate-950" type="submit">
                        <i class="fa-solid fa-floppy-disk mr-2"></i>Save Office Address
                    </button>
                </div>
            </form>
        </main>
        <?php require __DIR__ . '/footer.php'; ?>
    </div>
</div>
<script>
    (() => {
        const form = document.querySelector('[data-office-form]');
        if (!form) return;

        const extractMapSrc = (value) => {
            const trimmed = value.trim();
            if (!trimmed) return '';

            const match = trimmed.match(/src=["']([^"']+)["']/i);
            if (match?.[1]) return match[1].trim();

            return trimmed;
        };

        form.addEventListener('submit', () => {
            document.querySelectorAll('[data-map-embed]').forEach((field) => {
                field.value = extractMapSrc(field.value);
            });
        });
    })();
</script>
</body>
</html>
