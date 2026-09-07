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

    if ($action !== 'add_page') {
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
$active = $pages[$activePage] ?? [];

if (isset($_GET['created'])) {
    $message = 'New page created successfully.';
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

            <div class="grid gap-4 xl:grid-cols-[minmax(230px,20%)_minmax(0,80%)]">
                <aside class="flex min-h-[calc(100vh-132px)] flex-col gap-3">
                    <form class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm" method="post">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="add_page">
                        <p class="text-[11px] font-black uppercase tracking-[0.16em] text-blue-700">New Page</p>
                        <div class="mt-2 grid gap-2">
                            <input class="rounded-lg border border-slate-300 px-3 py-2 text-sm" name="new_page_name" placeholder="Page name">
                            <input class="rounded-lg border border-slate-300 px-3 py-2 text-sm" name="new_page_slug" placeholder="page-slug">
                            <button class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-black text-white hover:bg-slate-950" type="submit">
                                <i class="fa-solid fa-plus mr-2"></i>Create
                            </button>
                        </div>
                    </form>

                    <nav class="flex flex-1 flex-col rounded-2xl border border-slate-200 bg-white p-2 shadow-sm" data-page-nav>
                        <div class="flex items-center justify-between px-2 py-1">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">Pages</p>
                            <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-black text-slate-500"><?php echo count($pages); ?></span>
                        </div>
                        <div class="mt-1 grid gap-0.5" data-page-list>
                            <?php foreach ($pages as $key => $page): ?>
                                <?php $page = $pages[$key]; ?>
                                <a class="rounded-lg px-3 py-1.5 text-sm font-black leading-5 <?php echo $key === $activePage ? 'bg-blue-700 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100 hover:text-blue-700'; ?>" href="<?php echo e(base_url('dashboard/page-manager.php?page=' . $key)); ?>" data-page-item data-active="<?php echo $key === $activePage ? '1' : '0'; ?>">
                                    <span class="block truncate"><?php echo e($page['label']); ?></span>
                                    <span class="block truncate text-[10px] font-bold opacity-65"><?php echo e($page['path'] ?? ''); ?></span>
                                </a>
                            <?php endforeach; ?>
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
            <form class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:p-5" method="post" enctype="multipart/form-data" data-page-form>
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="save_page">
                <input type="hidden" name="page_key" value="<?php echo e($activePage); ?>">
                <div class="mb-4 flex flex-col gap-2 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.16em] text-blue-700">Editing</p>
                        <h2 class="mt-1 text-xl font-black text-slate-950"><?php echo e($active['label'] ?? 'Page'); ?></h2>
                    </div>
                    <p class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500"><?php echo e($active['path'] ?? ''); ?></p>
                </div>

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
        const items = Array.from(document.querySelectorAll('[data-page-item]'));

        if (!nav || !list || !pager || !prev || !next || !count || !items.length) return;

        let currentPage = 1;
        let perPage = items.length;

        const activeIndex = Math.max(0, items.findIndex(item => item.dataset.active === '1'));

        const measureItemHeight = () => {
            const clone = items[0].cloneNode(true);
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
            items.forEach(item => item.classList.add('hidden'));
            pager.classList.remove('hidden');
            pager.classList.add('flex');

            const navRect = nav.getBoundingClientRect();
            const listTop = list.getBoundingClientRect().top;
            const pagerHeight = pager.offsetHeight || 45;
            const availableHeight = Math.max(80, navRect.bottom - listTop - pagerHeight - 14);
            const itemHeight = measureItemHeight() + 2;

            perPage = Math.max(1, Math.floor(availableHeight / itemHeight));
            if (perPage >= items.length) {
                perPage = items.length;
                currentPage = 1;
                pager.classList.add('hidden');
                pager.classList.remove('flex');
            } else {
                const activePage = Math.floor(activeIndex / perPage) + 1;
                currentPage = Math.min(Math.max(currentPage, activePage), Math.ceil(items.length / perPage));
            }
        };

        const render = () => {
            const totalPages = Math.max(1, Math.ceil(items.length / perPage));
            currentPage = Math.min(Math.max(1, currentPage), totalPages);
            const start = (currentPage - 1) * perPage;
            const end = start + perPage;

            items.forEach((item, index) => {
                item.classList.toggle('hidden', index < start || index >= end);
            });

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
