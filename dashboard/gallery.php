<?php
require_once __DIR__ . '/../includes/config.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . base_url('login.php'));
    exit;
}

$message = (string) ($_SESSION['gallery_message'] ?? '');
unset($_SESSION['gallery_message']);
$error = '';
$selectMode = ($_GET['select'] ?? '') === 'profile';
$sectionSelectMode = ($_GET['select'] ?? '') === 'section';
$pageSelectMode = ($_GET['select'] ?? '') === 'page';
$siteSettingSelectMode = ($_GET['select'] ?? '') === 'site_setting';
$teamMemberSelectMode = ($_GET['select'] ?? '') === 'team_member';
$targetSection = $_GET['section'] ?? '';
$targetPage = $_GET['page'] ?? '';
$targetField = $_GET['field'] ?? '';
$targetIndex = $_GET['index'] ?? null;
$targetMember = $_GET['member'] ?? '';

function gallery_directory(): string
{
    return __DIR__ . '/../uploads/gallery';
}

function gallery_file_target(string $name): ?string
{
    $name = trim($name);
    if ($name === '' || basename($name) !== $name) {
        return null;
    }

    $directory = realpath(gallery_directory());
    $target = realpath(gallery_directory() . DIRECTORY_SEPARATOR . $name);

    if (!$directory || !$target || !is_file($target) || dirname($target) !== $directory) {
        return null;
    }

    return $target;
}

function gallery_return_url(): string
{
    $allowed = ['select', 'section', 'page', 'field', 'index', 'member'];
    $query = array_intersect_key($_GET, array_flip($allowed));
    $url = base_url('dashboard/gallery.php');

    return $query ? $url . '?' . http_build_query($query) : $url;
}

function gallery_finish(string $message): never
{
    $_SESSION['gallery_message'] = $message;
    header('Location: ' . gallery_return_url());
    exit;
}

function gallery_file_size(int $bytes): string
{
    return $bytes >= 1024 * 1024
        ? number_format($bytes / 1024 / 1024, 1) . ' MB'
        : number_format($bytes / 1024, 1) . ' KB';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = (string) ($_POST['action'] ?? 'upload');

    if ($action === 'upload') {
        $files = $_FILES['media'] ?? null;
        $uploadedCount = 0;

        if ($files && is_array($files['name'] ?? null)) {
            foreach ($files['name'] as $index => $name) {
                $single = [
                    'name' => $name,
                    'type' => $files['type'][$index] ?? '',
                    'tmp_name' => $files['tmp_name'][$index] ?? '',
                    'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $files['size'][$index] ?? 0,
                ];

                if (upload_dashboard_file($single, 'gallery')) {
                    $uploadedCount++;
                }
            }
        }

        if ($uploadedCount > 0) {
            gallery_finish($uploadedCount . ' file(s) uploaded successfully.');
        }
        $error = 'No valid files were uploaded. Files must be an allowed type and no larger than 100 MB.';
    } elseif ($action === 'replace') {
        $target = gallery_file_target((string) ($_POST['file_name'] ?? ''));
        $replacement = $_FILES['replacement'] ?? [];

        if (!$target) {
            $error = 'The selected gallery file was not found.';
        } elseif (replace_dashboard_file($replacement, $target)) {
            gallery_finish('Gallery file replaced successfully. Existing website references were preserved.');
        } else {
            $extension = strtoupper(pathinfo($target, PATHINFO_EXTENSION));
            $error = "Unable to replace the file. Select a valid {$extension} file no larger than 100 MB.";
        }
    } elseif ($action === 'delete') {
        $target = gallery_file_target((string) ($_POST['file_name'] ?? ''));

        if (!$target) {
            $error = 'The selected gallery file was not found.';
        } elseif (unlink($target)) {
            gallery_finish('Gallery file deleted successfully.');
        } else {
            $error = 'Unable to delete the gallery file.';
        }
    }
}

$galleryDirectory = gallery_directory();
$files = [];

if (is_dir($galleryDirectory)) {
    foreach (scandir($galleryDirectory) ?: [] as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $path = 'uploads/gallery/' . $file;
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $files[] = [
            'name' => $file,
            'path' => $path,
            'extension' => $extension,
            'size' => filesize(__DIR__ . '/../' . $path) ?: 0,
            'modified' => filemtime(__DIR__ . '/../' . $path) ?: time(),
            'type' => match (true) {
                in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true) => 'image',
                in_array($extension, ['mp4', 'webm'], true) => 'video',
                default => 'document',
            },
        ];
    }
}

usort($files, fn ($a, $b) => $b['modified'] <=> $a['modified']);

$pageTitle = 'Media Gallery - ' . $site['title'];
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
            <?php if ($selectMode || $sectionSelectMode || $pageSelectMode || $siteSettingSelectMode || $teamMemberSelectMode): ?>
                <?php
                $backUrl = base_url('dashboard/profile.php');
                $backText = 'Back to Profile';

                if ($sectionSelectMode) {
                    $backUrl = base_url('dashboard/section-manager.php?section=' . $targetSection);
                    $backText = 'Back to Section Manager';
                } elseif ($pageSelectMode) {
                    $backUrl = base_url('dashboard/page-manager.php?page=' . $targetPage);
                    $backText = 'Back to Page Manager';
                } elseif ($siteSettingSelectMode) {
                    $backUrl = base_url('dashboard/site-settings.php');
                    $backText = 'Back to Site Settings';
                } elseif ($teamMemberSelectMode) {
                    $backUrl = base_url('dashboard/team-members.php?edit=' . rawurlencode($targetMember));
                    $backText = 'Back to Team Member';
                }
                ?>
                <div class="mb-4 flex justify-end">
                    <a class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-black text-blue-700 shadow-sm" href="<?php echo e($backUrl); ?>">
                        <i class="fa-solid fa-arrow-left mr-2"></i><?php echo e($backText); ?>
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 font-bold text-emerald-800"><?php echo e($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 font-bold text-red-700"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form class="mb-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm" method="post" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <label class="block text-sm font-black text-slate-600">Upload Files
                    <input class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm" type="file" name="media[]" multiple>
                </label>
                <button class="mt-4 rounded-xl bg-blue-700 px-5 py-3 font-black text-white shadow-lg shadow-blue-700/20 hover:bg-slate-950" type="submit" name="action" value="upload">
                    <i class="fa-solid fa-upload mr-2"></i>Upload
                </button>
            </form>

            <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_380px]" data-media-library>
                <section>
                <div class="grid grid-cols-[repeat(auto-fill,minmax(180px,1fr))] gap-4">
                <?php foreach ($files as $file): ?>
                    <article class="group relative min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:border-blue-300 hover:shadow-md" data-media-item>
                        <button class="block w-full text-left outline-none focus:ring-2 focus:ring-inset focus:ring-blue-600" type="button"
                            data-media-select
                            data-name="<?php echo e($file['name']); ?>"
                            data-url="<?php echo e(base_url($file['path'])); ?>"
                            data-path="<?php echo e($file['path']); ?>"
                            data-type="<?php echo e($file['type']); ?>"
                            data-extension="<?php echo e($file['extension']); ?>"
                            data-size="<?php echo e(gallery_file_size((int) $file['size'])); ?>"
                            data-modified="<?php echo e(date('M j, Y g:i A', (int) $file['modified'])); ?>">
                        <span class="absolute right-3 top-3 z-10 hidden h-7 w-7 place-items-center rounded-full bg-blue-700 text-xs text-white shadow-lg" data-selected-check><i class="fa-solid fa-check"></i></span>
                        <span class="grid aspect-square w-full place-items-center overflow-hidden bg-slate-100">
                            <?php if ($file['type'] === 'image'): ?>
                                <img class="h-full w-full object-cover transition duration-300 group-hover:scale-105" src="<?php echo e(base_url($file['path'])); ?>" alt="<?php echo e($file['name']); ?>">
                            <?php elseif ($file['type'] === 'video'): ?>
                                <i class="fa-solid fa-film text-5xl text-blue-700"></i>
                            <?php else: ?>
                                <i class="fa-solid fa-file-lines text-5xl text-slate-500"></i>
                            <?php endif; ?>
                        </span>
                        <span class="block p-3">
                            <strong class="block truncate text-xs font-black text-slate-950" title="<?php echo e($file['name']); ?>"><?php echo e($file['name']); ?></strong>
                            <span class="mt-1 block text-xs font-bold uppercase tracking-wider text-slate-400"><?php echo e($file['extension']); ?> &middot; <?php echo e(gallery_file_size((int) $file['size'])); ?></span>
                        </span>
                        </button>
                            <div class="hidden" data-card-actions>
                                <a href="<?php echo e(base_url($file['path'])); ?>" target="_blank" data-card-view>View</a>
                                <?php if ($selectMode && $file['type'] === 'image'): ?>
                                    <a href="<?php echo e(base_url('dashboard/profile.php?gallery_image=' . rawurlencode($file['path']))); ?>" data-card-use>Select</a>
                                <?php elseif ($pageSelectMode && $file['type'] === 'image'): ?>
                                    <?php
                                    $returnQuery = [
                                        'page' => $targetPage,
                                        'field' => $targetField,
                                        'picked_image' => $file['path'],
                                    ];
                                    ?>
                                    <a href="<?php echo e(base_url('dashboard/page-manager.php?' . http_build_query($returnQuery))); ?>" data-card-use>Select</a>
                                <?php elseif ($siteSettingSelectMode && $file['type'] === 'image'): ?>
                                    <?php
                                    $returnQuery = [
                                        'field' => $targetField,
                                        'picked_image' => $file['path'],
                                    ];
                                    ?>
                                    <a href="<?php echo e(base_url('dashboard/site-settings.php?' . http_build_query($returnQuery))); ?>" data-card-use>Select</a>
                                <?php elseif ($teamMemberSelectMode && $file['type'] === 'image'): ?>
                                    <a href="<?php echo e(base_url('dashboard/team-members.php?edit=' . rawurlencode($targetMember) . '&gallery_image=' . rawurlencode($file['path']))); ?>" data-card-use>Select</a>
                                <?php elseif ($sectionSelectMode && ($file['type'] === 'image' || $targetField === 'profile_document')): ?>
                                    <?php
                                    $returnQuery = [
                                        'section' => $targetSection,
                                        'field' => $targetField,
                                        'picked_image' => $file['path'],
                                    ];

                                    if ($targetIndex !== null) {
                                        $returnQuery['index'] = (string) $targetIndex;
                                    }
                                    ?>
                                    <a href="<?php echo e(base_url('dashboard/section-manager.php?' . http_build_query($returnQuery))); ?>" data-card-use>Select</a>
                                <?php endif; ?>
                            </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if (!$files): ?>
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
                    <i class="fa-solid fa-images mb-3 text-4xl text-slate-300"></i>
                    <p class="font-bold">No gallery files yet.</p>
                </div>
            <?php endif; ?>
                </section>

                <aside class="fixed inset-y-0 right-0 z-50 hidden w-full max-w-md overflow-y-auto border-l border-slate-200 bg-white shadow-2xl xl:sticky xl:top-20 xl:z-20 xl:block xl:max-h-[calc(100vh-6rem)] xl:w-auto xl:rounded-3xl xl:border xl:shadow-sm" data-media-sidebar>
                    <div class="grid min-h-full place-items-center p-8 text-center xl:min-h-[520px]" data-media-placeholder>
                        <div>
                            <span class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-slate-100 text-2xl text-slate-300"><i class="fa-solid fa-photo-film"></i></span>
                            <h2 class="mt-4 font-black text-slate-800">Select a file</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-500">Click any media item to view its details and CRUD controls.</p>
                        </div>
                    </div>

                    <div class="hidden" data-media-details>
                        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-700">Attachment details</p>
                                <h2 class="mt-1 text-lg font-black text-slate-950">Media file</h2>
                            </div>
                            <button class="grid h-9 w-9 place-items-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 xl:hidden" type="button" aria-label="Close details" data-media-close><i class="fa-solid fa-xmark"></i></button>
                        </div>

                        <div class="p-5">
                            <div class="grid aspect-video place-items-center overflow-hidden rounded-2xl bg-slate-100">
                                <img class="hidden h-full w-full object-contain" src="" alt="" data-detail-image>
                                <video class="hidden h-full w-full" controls data-detail-video></video>
                                <i class="fa-solid fa-file-lines text-6xl text-slate-400" data-detail-document></i>
                            </div>

                            <h3 class="mt-4 break-all text-sm font-black text-slate-950" data-detail-name></h3>
                            <dl class="mt-3 grid gap-2 rounded-2xl bg-slate-50 p-4 text-xs">
                                <div class="flex justify-between gap-4"><dt class="font-bold text-slate-500">Type</dt><dd class="font-black uppercase text-slate-800" data-detail-extension></dd></div>
                                <div class="flex justify-between gap-4"><dt class="font-bold text-slate-500">Size</dt><dd class="font-black text-slate-800" data-detail-size></dd></div>
                                <div class="flex justify-between gap-4"><dt class="font-bold text-slate-500">Uploaded</dt><dd class="text-right font-black text-slate-800" data-detail-modified></dd></div>
                                <div class="grid gap-1"><dt class="font-bold text-slate-500">Path</dt><dd class="break-all font-mono text-[11px] text-slate-700" data-detail-path></dd></div>
                            </dl>

                            <div class="mt-4 grid gap-2 sm:grid-cols-2">
                                <a class="rounded-xl border border-slate-300 px-4 py-3 text-center text-sm font-black text-slate-700 hover:bg-slate-50" href="#" target="_blank" data-detail-view><i class="fa-solid fa-arrow-up-right-from-square mr-2"></i>View File</a>
                                <a class="hidden rounded-xl bg-blue-700 px-4 py-3 text-center text-sm font-black text-white hover:bg-slate-950" href="#" data-detail-use><i class="fa-solid fa-check mr-2"></i>Use this file</a>
                            </div>

                            <div class="mt-6 border-t border-slate-200 pt-5">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Update file</p>
                                <form class="mt-3 grid gap-3" method="post" enctype="multipart/form-data">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="file_name" value="" data-detail-file-name>
                                    <label class="block text-xs font-black text-slate-600">Replace with another <span data-replace-extension></span> file
                                        <input class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-xs" type="file" name="replacement" required data-replacement-input>
                                    </label>
                                    <button class="rounded-xl bg-amber-500 px-4 py-3 text-sm font-black text-white hover:bg-amber-600" type="submit" name="action" value="replace"><i class="fa-solid fa-arrows-rotate mr-2"></i>Replace File</button>
                                </form>
                            </div>

                            <div class="mt-5 border-t border-slate-200 pt-5">
                                <form method="post" onsubmit="return confirm('Delete this gallery file permanently? Existing website references to this file may stop working.')">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="file_name" value="" data-detail-file-name>
                                    <button class="w-full rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-black text-red-600 hover:bg-red-600 hover:text-white" type="submit" name="action" value="delete"><i class="fa-solid fa-trash mr-2"></i>Delete Permanently</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </main>
        <?php require __DIR__ . '/footer.php'; ?>
    </div>
</div>
<script>
    (() => {
        const library = document.querySelector('[data-media-library]');
        if (!library) return;

        const sidebar = library.querySelector('[data-media-sidebar]');
        const placeholder = library.querySelector('[data-media-placeholder]');
        const details = library.querySelector('[data-media-details]');
        const items = Array.from(library.querySelectorAll('[data-media-item]'));
        const image = library.querySelector('[data-detail-image]');
        const video = library.querySelector('[data-detail-video]');
        const documentIcon = library.querySelector('[data-detail-document]');
        const fileNameInputs = Array.from(library.querySelectorAll('[data-detail-file-name]'));
        const replacementInput = library.querySelector('[data-replacement-input]');

        const setText = (selector, value) => {
            const element = library.querySelector(selector);
            if (element) element.textContent = value;
        };

        const openDetails = button => {
            const data = button.dataset;
            const selectedItem = button.closest('[data-media-item]');
            items.forEach(item => {
                const selected = item === selectedItem;
                item.classList.toggle('border-blue-600', selected);
                item.classList.toggle('ring-2', selected);
                item.classList.toggle('ring-blue-200', selected);
                const check = item.querySelector('[data-selected-check]');
                check?.classList.toggle('hidden', !selected);
                check?.classList.toggle('grid', selected);
            });

            sidebar?.classList.remove('hidden');
            placeholder?.classList.add('hidden');
            details?.classList.remove('hidden');
            setText('[data-detail-name]', data.name || '');
            setText('[data-detail-extension]', data.extension || '');
            setText('[data-detail-size]', data.size || '');
            setText('[data-detail-modified]', data.modified || '');
            setText('[data-detail-path]', data.path || '');
            setText('[data-replace-extension]', (data.extension || '').toUpperCase());
            fileNameInputs.forEach(input => input.value = data.name || '');

            const actions = selectedItem?.querySelector('[data-card-actions]');
            const viewLink = library.querySelector('[data-detail-view]');
            if (viewLink) viewLink.href = actions?.querySelector('[data-card-view]')?.href || data.url || '#';
            const useUrl = actions?.querySelector('[data-card-use]')?.href || '';
            const useLink = library.querySelector('[data-detail-use]');
            if (useLink) {
                useLink.href = useUrl || '#';
                useLink.classList.toggle('hidden', !useUrl);
            }

            image?.classList.toggle('hidden', data.type !== 'image');
            video?.classList.toggle('hidden', data.type !== 'video');
            documentIcon?.classList.toggle('hidden', data.type === 'image' || data.type === 'video');
            if (image) {
                image.src = data.type === 'image' ? data.url : '';
                image.alt = data.name || '';
            }
            if (video) video.src = data.type === 'video' ? data.url : '';
            if (replacementInput) {
                replacementInput.value = '';
                replacementInput.accept = data.extension ? `.${data.extension}` : '';
            }

            if (window.innerWidth < 1280) document.body.classList.add('overflow-hidden');
        };

        library.querySelectorAll('[data-media-select]').forEach(button => {
            button.addEventListener('click', () => openDetails(button));
        });

        const closeSidebar = () => {
            sidebar?.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };
        library.querySelector('[data-media-close]')?.addEventListener('click', closeSidebar);
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && window.innerWidth < 1280) closeSidebar();
        });
    })();
</script>
</body>
</html>
