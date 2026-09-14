<?php
require_once __DIR__ . '/../includes/config.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . base_url('login.php'));
    exit;
}

function page_lines_to_array(string $value): array
{
    return array_values(array_filter(array_map('trim', preg_split('/\R/', $value) ?: []), fn ($line) => $line !== ''));
}

function render_page_image_picker(string $label, string $inputName, string $uploadName, string $value, string $pageKey, string $field): void
{
    $galleryUrl = base_url('dashboard/gallery.php?' . http_build_query([
        'select' => 'page',
        'page' => $pageKey,
        'field' => $field,
    ]));
    ?>
    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
        <label class="block text-xs font-black uppercase tracking-wide text-slate-600"><?php echo e($label); ?></label>
        <div class="mt-2 grid gap-2 md:grid-cols-[132px_1fr]">
            <div class="grid aspect-video place-items-center overflow-hidden rounded-lg bg-white">
                <?php if ($value !== ''): ?>
                    <img class="h-full w-full object-cover" src="<?php echo e(base_url($value)); ?>" alt="">
                <?php else: ?>
                    <i class="fa-solid fa-image text-2xl text-slate-300"></i>
                <?php endif; ?>
            </div>
            <div class="grid content-start gap-2">
                <input class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="<?php echo e($inputName); ?>" value="<?php echo e($value); ?>" placeholder="uploads/page-header-bg.svg">
                <div class="grid gap-2 sm:grid-cols-2">
                    <a class="inline-flex items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-black text-blue-700 hover:bg-blue-100" href="<?php echo e($galleryUrl); ?>">
                        <i class="fa-solid fa-images mr-2"></i>Pick from gallery
                    </a>
                    <label class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-black text-slate-700 hover:bg-slate-50">
                        <i class="fa-solid fa-upload mr-2"></i>Pick from computer
                        <input class="hidden" type="file" name="<?php echo e($uploadName); ?>" accept="image/*">
                    </label>
                </div>
            </div>
        </div>
    </div>
    <?php
}

$pageKeys = array_keys(page_defaults());
$allPagesInitial = all_page_content();
$activePage = $_GET['page'] ?? ($pageKeys[0] ?? 'about');
$activePage = isset($allPagesInitial[$activePage]) ? $activePage : ($pageKeys[0] ?? 'about');
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? 'save_page';

    if ($action === 'add_page') {
        $pageName = trim($_POST['new_page_name'] ?? '');
        $pageSlug = strtolower(trim($_POST['new_page_slug'] ?? ''));
        $pageSlug = preg_replace('/[^a-z0-9-]+/', '-', $pageSlug ?: $pageName) ?: '';
        $pageSlug = trim($pageSlug, '-');
        $newPageKey = page_key_from_slug($pageSlug);
        $settings = saved_page_settings();
        $customPages = custom_pages();

        if ($pageName === '' || $pageSlug === '') {
            $error = 'Page name and slug are required.';
        } elseif (isset(all_page_content()[$newPageKey])) {
            $error = 'This page slug already exists.';
        } else {
            $customPages[$newPageKey] = [
                'label' => $pageName,
                'path' => 'page.php?slug=' . $pageSlug,
                'header_kicker' => 'Page',
                'header_title' => $pageName,
                'header_text' => '',
                'header_image' => 'uploads/page-header-bg.svg',
                'content' => [],
                'content_html' => '',
                'custom' => true,
                'slug' => $pageSlug,
            ];
            $settings['custom_pages'] = $customPages;

            if (save_page_settings($settings)) {
                header('Location: ' . base_url('dashboard/page-manager.php?page=' . $newPageKey . '&created=1'));
                exit;
            }

            $error = 'Unable to create page.';
        }
    }

    if ($action === 'delete_page') {
        $deleteKey = trim((string) ($_POST['page_key'] ?? ''));
        $currentPages = all_page_content();
        $saved = saved_page_settings();
        $customPages = custom_pages();

        if ($deleteKey === '' || !isset($customPages[$deleteKey]) || !page_is_custom($deleteKey, $currentPages[$deleteKey] ?? [])) {
            $error = 'Only Custom Pages can be deleted.';
        } else {
            unset($customPages[$deleteKey]);
            $saved['custom_pages'] = $customPages;

            if (save_page_settings($saved)) {
                $fallbackPage = array_key_first(page_defaults()) ?: 'about';
                header('Location: ' . base_url('dashboard/page-manager.php?page=' . $fallbackPage . '&deleted=1'));
                exit;
            }

            $error = 'Unable to delete page.';
        }
    }

    if ($action !== 'add_page' && $action !== 'delete_page') {
        $activePage = $_POST['page_key'] ?? $activePage;
        $defaults = page_defaults();
        $settings = saved_page_settings();
        $allPagesForCurrent = all_page_content();
        $current = $allPagesForCurrent[$activePage] ?? ($defaults[$activePage] ?? []);
        $headerImage = trim($_POST['header_image'] ?? ($current['header_image'] ?? ''));
        $uploadedImage = upload_dashboard_file($_FILES['header_image_upload'] ?? [], 'pages');
        $headerImage = $uploadedImage ?: $headerImage;
        $headerTitle = trim($_POST['header_title'] ?? '');

        $pagePayload = [
            'label' => $current['label'] ?? ucwords(str_replace('_', ' ', $activePage)),
            'path' => $current['path'] ?? '',
            'header_kicker' => trim($_POST['header_kicker'] ?? ''),
            'header_title' => $headerTitle,
            'header_text' => trim($_POST['header_text'] ?? ''),
            'header_image' => $headerImage,
            'content' => page_lines_to_array($_POST['content'] ?? ''),
            'content_html' => page_clean_html(trim($_POST['content_html'] ?? '')),
        ];

        if (($current['custom'] ?? false) || str_starts_with($activePage, 'custom_')) {
            $pagePayload['custom'] = true;
            $pagePayload['slug'] = $current['slug'] ?? page_slug_from_key($activePage);
            $settings['custom_pages'][$activePage] = $pagePayload;
        } else {
            $settings[$activePage] = $pagePayload;
        }

        $message = save_page_settings($settings) ? 'Page settings saved successfully.' : 'Unable to save page settings.';
    }
}

$pages = all_page_content();
$systemPages = [];
$customPageItems = [];
foreach ($pages as $key => $page) {
    if (page_is_custom($key, $page)) {
        $customPageItems[$key] = $page;
    } else {
        $systemPages[$key] = $page;
    }
}
$active = $pages[$activePage] ?? [];

if (isset($_GET['created'])) {
    $message = 'New page created successfully.';
}

if (isset($_GET['deleted'])) {
    $message = 'Custom page deleted successfully.';
}

if (($_GET['picked_image'] ?? '') !== '' && ($_GET['page'] ?? '') === $activePage) {
    $pickedImage = trim($_GET['picked_image']);
    if (str_starts_with($pickedImage, 'uploads/')) {
        $active['header_image'] = $pickedImage;
        $message = 'Image selected from gallery. Click Save Page to apply it.';
    }
}

$pageTitle = 'Page Manager - ' . $site['title'];
$editorHtml = (string) ($active['content_html'] ?? '');

if ($editorHtml === '' && !empty($active['content'])) {
    $editorHtml = implode('', array_map(fn ($paragraph): string => '<p>' . e($paragraph) . '</p>', $active['content']));
}
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
        <main class="px-3 pb-3 pt-0 lg:px-5 lg:pb-5 lg:pt-0">
            <?php if ($message): ?>
                <div class="mb-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800"><?php echo e($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="mb-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700"><?php echo e($error); ?></div>
            <?php endif; ?>

            <div class="grid gap-5 xl:grid-cols-[minmax(290px,27%)_minmax(0,73%)] 2xl:grid-cols-[minmax(320px,25%)_minmax(0,75%)]">
                <aside class="flex min-h-[calc(100vh-132px)] flex-col gap-3" data-page-sidebar>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-blue-700">Page control</p>
                                <h2 class="mt-1 text-lg font-black text-slate-950">Manage pages</h2>
                            </div>
                            <span class="grid h-9 w-9 place-items-center rounded-xl bg-blue-50 text-blue-700"><i class="fa-solid fa-sliders"></i></span>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-2 text-center">
                            <div class="rounded-xl bg-slate-50 px-2 py-2"><strong class="block text-lg font-black text-slate-950"><?php echo count($systemPages); ?></strong><span class="text-[10px] font-bold uppercase tracking-wide text-slate-500">System default</span></div>
                            <div class="rounded-xl bg-amber-50 px-2 py-2"><strong class="block text-lg font-black text-amber-700"><?php echo count($customPageItems); ?></strong><span class="text-[10px] font-bold uppercase tracking-wide text-amber-700">Custom page</span></div>
                        </div>
                    </div>

                    <form class="rounded-2xl border border-blue-100 bg-blue-50/60 p-3 shadow-sm" method="post">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="add_page">
                        <p class="text-[11px] font-black uppercase tracking-[0.16em] text-blue-700"><i class="fa-solid fa-plus mr-1"></i>New Custom Page</p>
                        <p class="mt-1 text-xs leading-5 text-slate-600">Create an editable page without changing the built-in system pages.</p>
                        <div class="mt-2 grid gap-2">
                            <label class="text-[10px] font-black uppercase tracking-wide text-slate-500">Page name<input class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm" name="new_page_name" placeholder="e.g. Sustainability" required></label>
                            <label class="text-[10px] font-black uppercase tracking-wide text-slate-500">URL slug<input class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm" name="new_page_slug" placeholder="sustainability" required></label>
                            <button class="rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-black text-white hover:bg-slate-950" type="submit">
                                <i class="fa-solid fa-plus mr-2"></i>Create Custom Page
                            </button>
                        </div>
                    </form>

                    <nav class="flex flex-1 flex-col rounded-2xl border border-slate-200 bg-white p-2 shadow-sm" data-page-nav>
                        <div class="flex items-center gap-2 px-2 py-1">
                            <label class="sr-only" for="page-search">Search pages</label>
                            <div class="relative flex-1"><i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i><input id="page-search" class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-8 pr-2 text-xs font-semibold outline-none focus:border-blue-400" placeholder="Search pages..." data-page-search></div>
                            <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-black text-slate-500"><?php echo count($pages); ?></span>
                        </div>
                        <div class="mt-2 grid grid-cols-3 gap-1 rounded-lg bg-slate-100 p-1" data-page-filters>
                            <button class="rounded-md bg-white px-2 py-1.5 text-[10px] font-black text-slate-700 shadow-sm" type="button" data-page-filter="all">All</button>
                            <button class="rounded-md px-2 py-1.5 text-[10px] font-black text-slate-500 hover:text-blue-700" type="button" data-page-filter="system">System</button>
                            <button class="rounded-md px-2 py-1.5 text-[10px] font-black text-slate-500 hover:text-amber-700" type="button" data-page-filter="custom">Custom</button>
                        </div>
                        <div class="mt-3 min-h-0 flex-1 overflow-y-auto pr-1" data-page-list>
                            <p class="px-2 pb-1 text-[10px] font-black uppercase tracking-[0.16em] text-slate-400" data-page-heading="system">System Default</p>
                            <div class="grid gap-1" data-page-group="system">
                                <?php foreach ($systemPages as $key => $page): ?>
                                    <a class="group rounded-xl border border-transparent px-3 py-2 <?php echo $key === $activePage ? 'border-blue-200 bg-blue-50 text-blue-800' : 'text-slate-700 hover:border-slate-200 hover:bg-slate-50'; ?>" href="<?php echo e(base_url('dashboard/page-manager.php?page=' . $key)); ?>" data-page-item data-page-type="system" data-page-search-text="<?php echo e(strtolower(($page['label'] ?? '') . ' ' . ($page['path'] ?? ''))); ?>" data-active="<?php echo $key === $activePage ? '1' : '0'; ?>">
                                        <span class="flex items-center gap-2"><i class="fa-solid fa-lock text-[10px] <?php echo $key === $activePage ? 'text-blue-600' : 'text-slate-400'; ?>"></i><span class="min-w-0 flex-1 truncate text-xs font-black"><?php echo e($page['label']); ?></span><span class="rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-black uppercase text-slate-500">Default</span></span>
                                        <span class="mt-1 block truncate pl-5 text-[10px] font-semibold text-slate-400"><?php echo e($page['path'] ?? ''); ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                            <p class="mt-4 px-2 pb-1 text-[10px] font-black uppercase tracking-[0.16em] text-amber-600" data-page-heading="custom">Custom Page</p>
                            <div class="grid gap-1" data-page-group="custom">
                                <?php if ($customPageItems): ?>
                                    <?php foreach ($customPageItems as $key => $page): ?>
                                        <a class="group rounded-xl border border-transparent px-3 py-2 <?php echo $key === $activePage ? 'border-amber-200 bg-amber-50 text-amber-800' : 'text-slate-700 hover:border-amber-100 hover:bg-amber-50/50'; ?>" href="<?php echo e(base_url('dashboard/page-manager.php?page=' . $key)); ?>" data-page-item data-page-type="custom" data-page-search-text="<?php echo e(strtolower(($page['label'] ?? '') . ' ' . ($page['path'] ?? ''))); ?>" data-active="<?php echo $key === $activePage ? '1' : '0'; ?>">
                                            <span class="flex items-center gap-2"><i class="fa-solid fa-pen-to-square text-[10px] <?php echo $key === $activePage ? 'text-amber-600' : 'text-slate-400'; ?>"></i><span class="min-w-0 flex-1 truncate text-xs font-black"><?php echo e($page['label']); ?></span><span class="rounded bg-amber-100 px-1.5 py-0.5 text-[9px] font-black uppercase text-amber-700">Custom</span></span>
                                            <span class="mt-1 block truncate pl-5 text-[10px] font-semibold text-slate-400"><?php echo e($page['path'] ?? ''); ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="px-3 py-3 text-xs leading-5 text-slate-400">No custom pages yet. Use the form above to create one.</p>
                                <?php endif; ?>
                            </div>
                            <p class="hidden px-3 py-4 text-xs text-slate-400" data-page-empty>No pages match your search.</p>
                        </div>
                        <div class="mt-auto hidden items-center justify-between border-t border-slate-100 pt-3" data-page-pager>
                            <button class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-xs font-black text-blue-700 hover:bg-blue-50 disabled:pointer-events-none disabled:text-slate-300" type="button" data-page-prev>
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <span class="text-xs font-black text-slate-500" data-page-count>1 / 1</span>
                            <button class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-xs font-black text-blue-700 hover:bg-blue-50 disabled:pointer-events-none disabled:text-slate-300" type="button" data-page-next>
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        </div>
                    </nav>
                </aside>

                <section class="min-w-0">
            <?php if (page_is_custom($activePage, $active)): ?>
                <form id="delete-custom-page-form" method="post" onsubmit="return confirm('Delete this custom page? This cannot be undone.');">
                    <?php echo csrf_field(); ?><input type="hidden" name="action" value="delete_page"><input type="hidden" name="page_key" value="<?php echo e($activePage); ?>">
                </form>
            <?php endif; ?>
            <form class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:p-5" method="post" enctype="multipart/form-data" data-page-form>
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="save_page">
                <input type="hidden" name="page_key" value="<?php echo e($activePage); ?>">
                <div class="mb-4 flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2"><p class="text-[11px] font-black uppercase tracking-[0.16em] text-blue-700">Editing</p><span class="rounded-full <?php echo page_is_custom($activePage, $active) ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600'; ?> px-2.5 py-1 text-[10px] font-black uppercase tracking-wide"><?php echo e(page_type_label($activePage, $active)); ?></span></div>
                        <h2 class="mt-1 text-xl font-black text-slate-950"><?php echo e($active['label'] ?? 'Page'); ?></h2>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <?php if (!empty($active['path'])): ?>
                            <a class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-600 hover:border-blue-300 hover:text-blue-700" href="<?php echo e(base_url($active['path'])); ?>" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square"></i>Preview</a>
                        <?php endif; ?>
                        <p class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500"><?php echo e($active['path'] ?? ''); ?></p>
                    </div>
                </div>

                <?php if (page_is_custom($activePage, $active)): ?>
                    <div class="mb-4 flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs leading-5 text-amber-800"><i class="fa-solid fa-circle-info mr-1"></i>This is a Custom Page. You can edit or remove it at any time.</p>
                        <button class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-white px-3 py-2 text-xs font-black text-red-600 hover:bg-red-50" type="submit" form="delete-custom-page-form"><i class="fa-solid fa-trash"></i>Delete page</button>
                    </div>
                <?php else: ?>
                    <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs leading-5 text-slate-600"><i class="fa-solid fa-lock mr-1 text-slate-400"></i>System Default page. Its built-in structure stays available; saved edits are stored as an override.</div>
                <?php endif; ?>

                <div class="grid gap-3 lg:grid-cols-2">
                    <label class="block text-xs font-black uppercase tracking-wide text-slate-600">Header Kicker
                        <input class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500" name="header_kicker" value="<?php echo e($active['header_kicker'] ?? ''); ?>">
                    </label>
                    <label class="block text-xs font-black uppercase tracking-wide text-slate-600">Header Title
                        <input class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500" name="header_title" value="<?php echo e($active['header_title'] ?? ''); ?>">
                    </label>
                    <label class="block text-xs font-black uppercase tracking-wide text-slate-600 lg:col-span-2">Header Text
                        <textarea class="mt-1.5 min-h-20 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm leading-6 outline-none focus:border-blue-500" name="header_text"><?php echo e($active['header_text'] ?? ''); ?></textarea>
                    </label>
                    <div class="lg:col-span-2">
                        <?php render_page_image_picker('Header Image', 'header_image', 'header_image_upload', $active['header_image'] ?? '', $activePage, 'header_image'); ?>
                    </div>
                    <div class="lg:col-span-2">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <label class="block text-xs font-black uppercase tracking-wide text-slate-600">Content Editor</label>
                            <a class="inline-flex items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-black text-blue-700 hover:bg-blue-100" href="<?php echo e(base_url('dashboard/gallery.php')); ?>" target="_blank">
                                <i class="fa-solid fa-photo-film mr-2"></i>Media Library
                            </a>
                        </div>
                        <div class="mt-1.5 overflow-hidden rounded-xl border border-slate-300 bg-white">
                            <div class="flex flex-wrap gap-1.5 border-b border-slate-200 bg-slate-50 p-2">
                                <button class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-black text-slate-700 hover:bg-slate-100" type="button" data-editor-command="bold"><i class="fa-solid fa-bold"></i></button>
                                <button class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-black text-slate-700 hover:bg-slate-100" type="button" data-editor-command="italic"><i class="fa-solid fa-italic"></i></button>
                                <button class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-black text-slate-700 hover:bg-slate-100" type="button" data-editor-command="underline"><i class="fa-solid fa-underline"></i></button>
                                <button class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-black text-slate-700 hover:bg-slate-100" type="button" data-editor-command="justifyLeft" title="Align left"><i class="fa-solid fa-align-left"></i></button>
                                <button class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-black text-slate-700 hover:bg-slate-100" type="button" data-editor-command="justifyCenter" title="Align center"><i class="fa-solid fa-align-center"></i></button>
                                <button class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-black text-slate-700 hover:bg-slate-100" type="button" data-editor-command="justifyRight" title="Align right"><i class="fa-solid fa-align-right"></i></button>
                                <button class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-black text-slate-700 hover:bg-slate-100" type="button" data-editor-command="justifyFull" title="Justify"><i class="fa-solid fa-align-justify"></i></button>
                                <button class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-black text-slate-700 hover:bg-slate-100" type="button" data-editor-command="insertUnorderedList"><i class="fa-solid fa-list-ul"></i></button>
                                <button class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-black text-slate-700 hover:bg-slate-100" type="button" data-editor-block="h2">H2</button>
                                <button class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-black text-slate-700 hover:bg-slate-100" type="button" data-editor-block="h3">H3</button>
                                <button class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-black text-slate-700 hover:bg-slate-100" type="button" data-editor-link><i class="fa-solid fa-link"></i></button>
                                <button class="rounded-md border border-blue-200 bg-blue-50 px-2.5 py-1.5 text-xs font-black text-blue-700 hover:bg-blue-100" type="button" data-editor-image><i class="fa-solid fa-image mr-1"></i>Media</button>
                            </div>
                            <div class="min-h-[420px] px-4 py-3 leading-7 outline-none prose max-w-none prose-img:rounded-2xl prose-img:border prose-img:border-slate-200 prose-img:shadow-sm" contenteditable="true" data-page-editor><?php echo page_clean_html($editorHtml); ?></div>
                        </div>
                        <input type="hidden" name="content_html" data-page-editor-input value="<?php echo e(page_clean_html($editorHtml)); ?>">
                        <textarea class="hidden" name="content"><?php echo e(implode("\n", $active['content'] ?? [])); ?></textarea>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button class="inline-flex items-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-black text-white shadow-lg shadow-blue-700/20 hover:bg-slate-950" type="submit">
                        <i class="fa-solid fa-floppy-disk mr-2"></i>Save Page
                    </button>
                </div>
            </form>
                </section>
            </div>
        </main>
        <?php require __DIR__ . '/footer.php'; ?>
    </div>
</div>
<script>
    (() => {
        const nav = document.querySelector('[data-page-nav]');
        const list = document.querySelector('[data-page-list]');
        const pager = document.querySelector('[data-page-pager]');
        const prev = document.querySelector('[data-page-prev]');
        const next = document.querySelector('[data-page-next]');
        const count = document.querySelector('[data-page-count]');
        const search = document.querySelector('[data-page-search]');
        const filters = Array.from(document.querySelectorAll('[data-page-filter]'));
        const empty = document.querySelector('[data-page-empty]');
        const items = Array.from(document.querySelectorAll('[data-page-item]'));

        if (!nav || !list || !pager || !prev || !next || !count || !items.length) return;

        let currentPage = 1;
        let perPage = items.length;
        let activeFilter = 'all';
        let query = '';

        const matchedItems = () => items.filter(item => {
            const typeMatch = activeFilter === 'all' || item.dataset.pageType === activeFilter;
            const searchMatch = query === '' || (item.dataset.pageSearchText || '').includes(query);
            return typeMatch && searchMatch;
        });

        const measureItemHeight = () => {
            const source = matchedItems()[0] || items[0];
            const clone = source.cloneNode(true);
            clone.classList.remove('hidden');
            clone.style.visibility = 'hidden';
            clone.style.position = 'absolute';
            clone.style.left = '-9999px';
            list.appendChild(clone);
            const height = clone.offsetHeight || 54;
            clone.remove();

            return height;
        };

        const calculatePerPage = () => {
            const matched = matchedItems();
            items.forEach(item => item.classList.add('hidden'));
            if (!matched.length) {
                pager.classList.add('hidden');
                pager.classList.remove('flex');
                return;
            }
            pager.classList.remove('hidden');
            pager.classList.add('flex');

            const navRect = nav.getBoundingClientRect();
            const listTop = list.getBoundingClientRect().top;
            const pagerHeight = pager.offsetHeight || 45;
            const availableHeight = Math.max(80, navRect.bottom - listTop - pagerHeight - 14);
            const itemHeight = measureItemHeight() + 2;

            perPage = Math.max(1, Math.floor(availableHeight / itemHeight));
            if (perPage >= matched.length) {
                perPage = matched.length;
                currentPage = 1;
                pager.classList.add('hidden');
                pager.classList.remove('flex');
            } else {
                const activeIndex = Math.max(0, matched.findIndex(item => item.dataset.active === '1'));
                const activePage = activeIndex >= 0 ? Math.floor(activeIndex / perPage) + 1 : 1;
                currentPage = Math.min(Math.max(currentPage, activePage), Math.ceil(matched.length / perPage));
            }
        };

        const render = () => {
            const matched = matchedItems();
            const totalPages = Math.max(1, Math.ceil(matched.length / Math.max(1, perPage)));
            currentPage = Math.min(Math.max(1, currentPage), totalPages);
            const start = (currentPage - 1) * perPage;
            const end = start + perPage;

            items.forEach(item => {
                item.classList.add('hidden');
            });
            matched.slice(start, end).forEach(item => item.classList.remove('hidden'));

            document.querySelectorAll('[data-page-group]').forEach(group => {
                const hasVisible = matched.some(item => item.dataset.pageType === group.dataset.pageGroup);
                group.classList.toggle('hidden', !hasVisible);
                document.querySelector(`[data-page-heading="${group.dataset.pageGroup}"]`)?.classList.toggle('hidden', !hasVisible);
            });
            if (empty) empty.classList.toggle('hidden', matched.length > 0);

            count.textContent = `${currentPage} / ${totalPages}`;
            prev.disabled = currentPage <= 1;
            next.disabled = currentPage >= totalPages;

            if (totalPages <= 1) {
                pager.classList.add('hidden');
                pager.classList.remove('flex');
            } else {
                pager.classList.remove('hidden');
                pager.classList.add('flex');
            }
        };

        const refresh = () => {
            currentPage = 1;
            calculatePerPage();
            render();
        };

        prev.addEventListener('click', () => {
            currentPage -= 1;
            render();
        });

        next.addEventListener('click', () => {
            currentPage += 1;
            render();
        });

        search?.addEventListener('input', () => {
            query = search.value.trim().toLowerCase();
            refresh();
        });

        filters.forEach(button => button.addEventListener('click', () => {
            activeFilter = button.dataset.pageFilter || 'all';
            filters.forEach(filter => {
                const selected = filter === button;
                filter.classList.toggle('bg-white', selected);
                filter.classList.toggle('shadow-sm', selected);
                filter.classList.toggle('text-slate-700', selected);
                filter.classList.toggle('text-slate-500', !selected);
            });
            refresh();
        }));

        window.addEventListener('resize', refresh);
        refresh();
    })();

    (() => {
        const editor = document.querySelector('[data-page-editor]');
        const input = document.querySelector('[data-page-editor-input]');
        const form = editor?.closest('form');
        const siteBase = <?php echo json_encode(base_url('')); ?>;

        if (!editor || !input || !form) return;

        document.querySelectorAll('[data-editor-command]').forEach(button => {
            button.addEventListener('click', () => {
                document.execCommand(button.dataset.editorCommand, false, null);
                editor.focus();
            });
        });

        document.querySelectorAll('[data-editor-block]').forEach(button => {
            button.addEventListener('click', () => {
                document.execCommand('formatBlock', false, button.dataset.editorBlock);
                editor.focus();
            });
        });

        document.querySelector('[data-editor-link]')?.addEventListener('click', () => {
            const url = window.prompt('Enter link URL');
            if (!url) return;
            document.execCommand('createLink', false, url);
            editor.focus();
        });

        document.querySelector('[data-editor-image]')?.addEventListener('click', () => {
            let url = window.prompt('Enter image URL or uploads path');
            if (!url) return;

            url = url.trim();
            if (url.startsWith('uploads/')) {
                url = siteBase + url;
            }

            document.execCommand('insertHTML', false, `<p><img src="${url.replace(/"/g, '&quot;')}" alt=""></p>`);
            editor.focus();
        });

        form.addEventListener('submit', () => {
            input.value = editor.innerHTML.trim();
        });
    })();
</script>
</body>
</html>
