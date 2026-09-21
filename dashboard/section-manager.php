<?php
require_once __DIR__ . '/../includes/config.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . base_url('login.php'));
    exit;
}

function lines_to_array(string $value): array
{
    return array_values(array_filter(array_map('trim', preg_split('/\R/', $value) ?: []), fn ($line) => $line !== ''));
}

function save_section_settings(array $settings): bool
{
    if (db_json_save('section_settings', $settings)) {
        return true;
    }

    $file = section_settings_file();
    $directory = dirname($file);

    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    return file_put_contents($file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
}

function multi_upload_file(string $field, int $index): array
{
    $files = $_FILES[$field] ?? [];

    return [
        'name' => $files['name'][$index] ?? '',
        'type' => $files['type'][$index] ?? '',
        'tmp_name' => $files['tmp_name'][$index] ?? '',
        'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
        'size' => $files['size'][$index] ?? 0,
    ];
}

function section_image_recommendation(string $section, string $field): string
{
    if ($section === 'hero') {
        return 'Recommended size: 1920 x 900 px. Use a wide landscape image under 500 KB for faster loading.';
    }

    if ($section === 'services' && $field === 'item_image') {
        return 'Recommended size: 900 x 650 px. Keep all service card images the same ratio.';
    }

    if ($section === 'masonry_gallery') {
        return 'Recommended size: 900 x 1200 px for portrait or 1200 x 900 px for landscape masonry items.';
    }

    if (in_array($section, ['about', 'company_profile'], true)) {
        return 'Recommended size: 1200 x 900 px. Use a clear landscape image for content/background sections.';
    }

    return 'Recommended size: 1200 x 800 px. Use optimized JPG/WebP/PNG files.';
}

function render_image_picker(string $label, string $inputName, string $value, string $section, string $field, string $uploadName, ?int $index = null): void
{
    $query = [
        'select' => 'section',
        'section' => $section,
        'field' => $field,
    ];

    if ($index !== null) {
        $query['index'] = (string) $index;
    }

    $galleryUrl = base_url('dashboard/gallery.php?' . http_build_query($query));
    $recommendation = section_image_recommendation($section, $field);
    ?>
    <div class="rounded-2xl border border-slate-200 bg-white p-4">
        <label class="block text-sm font-black text-slate-600"><?php echo e($label); ?></label>
        <p class="mt-1 rounded-xl bg-emerald-50 px-3 py-2 text-xs font-bold leading-5 text-emerald-800"><?php echo e($recommendation); ?></p>
        <div class="mt-3 grid gap-3 md:grid-cols-[120px_1fr]">
            <div class="grid aspect-square place-items-center overflow-hidden rounded-xl bg-slate-100">
                <?php if ($value !== ''): ?>
                    <img class="h-full w-full object-cover" src="<?php echo e(base_url($value)); ?>" alt="">
                <?php else: ?>
                    <i class="fa-solid fa-image text-3xl text-slate-300"></i>
                <?php endif; ?>
            </div>
            <div class="grid content-start gap-3">
                <input class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" name="<?php echo e($inputName); ?>" value="<?php echo e($value); ?>" placeholder="uploads/image.svg">
                <div class="grid gap-2 sm:grid-cols-2">
                    <a class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-black text-blue-700 hover:bg-blue-100" href="<?php echo e($galleryUrl); ?>">
                        <i class="fa-solid fa-images mr-2"></i>Pick from gallery
                    </a>
                    <label class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm font-black text-slate-700 hover:bg-white">
                        <i class="fa-solid fa-upload mr-2"></i>Pick from computer
                        <input class="hidden" type="file" name="<?php echo e($uploadName); ?>" accept="image/*">
                    </label>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function render_document_picker(string $label, string $inputName, string $value, string $section, string $field, string $uploadName): void
{
    $galleryUrl = base_url('dashboard/gallery.php?' . http_build_query([
        'select' => 'section',
        'section' => $section,
        'field' => $field,
    ]));
    $extension = $value !== '' ? strtoupper(pathinfo($value, PATHINFO_EXTENSION)) : '';
    ?>
    <div class="rounded-2xl border border-slate-200 bg-white p-4">
        <label class="block text-sm font-black text-slate-600"><?php echo e($label); ?></label>
        <div class="mt-3 grid gap-3 md:grid-cols-[120px_1fr]">
            <div class="grid aspect-square place-items-center rounded-xl bg-slate-100 text-center">
                <div>
                    <i class="fa-solid fa-file-lines text-3xl text-blue-700"></i>
                    <p class="mt-2 text-xs font-black text-slate-500"><?php echo e($extension ?: 'DOC'); ?></p>
                </div>
            </div>
            <div class="grid content-start gap-3">
                <input class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" name="<?php echo e($inputName); ?>" value="<?php echo e($value); ?>" placeholder="uploads/documents/bs-trading-company-profile.pdf">
                <div class="grid gap-2 sm:grid-cols-2">
                    <a class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-black text-blue-700 hover:bg-blue-100" href="<?php echo e($galleryUrl); ?>">
                        <i class="fa-solid fa-folder-open mr-2"></i>Pick from gallery
                    </a>
                    <label class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm font-black text-slate-700 hover:bg-white">
                        <i class="fa-solid fa-upload mr-2"></i>Pick from computer
                        <input class="hidden" type="file" name="<?php echo e($uploadName); ?>" accept=".pdf,.doc,.docx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                    </label>
                </div>
            </div>
        </div>
    </div>
    <?php
}

$hiddenSectionManagerKeys = ['company_profile', 'masonry_gallery', 'office', 'contact'];
$sectionKeys = array_values(array_filter(
    array_keys(section_defaults()),
    fn (string $key): bool => !in_array($key, $hiddenSectionManagerKeys, true)
));
$activeSection = $_GET['section'] ?? ($sectionKeys[0] ?? 'hero');
$activeSection = in_array($activeSection, $sectionKeys, true) ? $activeSection : ($sectionKeys[0] ?? 'hero');
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $activeSection = $_POST['section_key'] ?? $activeSection;
    $defaults = section_defaults();
    $settings = saved_section_settings();
    $current = $defaults[$activeSection] ?? [];
    $payload = [
        'label' => $current['label'] ?? ucwords(str_replace('_', ' ', $activeSection)),
        'visible' => isset($_POST['visible']),
    ];
    $extraSaved = true;
    $uploadSectionFile = static function (array $file, string $folder) use (&$error): ?string {
        $uploaded = upload_dashboard_file($file, $folder);
        if (!$uploaded && dashboard_upload_was_requested($file)) {
            $error = dashboard_upload_last_error() ?: 'The selected file could not be uploaded.';
        }

        return $uploaded;
    };

    if ($activeSection === 'hero') {
        $image = trim($_POST['image'] ?? '');
        $uploadedImage = $uploadSectionFile($_FILES['image_upload'] ?? [], 'sections');
        $image = $uploadedImage ?: $image;
        $highlights = [];
        $highlightLinks = [];

        foreach (($_POST['highlight_label'] ?? []) as $index => $label) {
            $label = trim((string) $label);
            $url = trim($_POST['highlight_url'][$index] ?? '');

            if ($label === '') {
                continue;
            }

            $highlights[] = $label;
            $highlightLinks[] = $url;
        }

        $payload += [
            'kicker' => trim($_POST['kicker'] ?? ''),
            'title' => trim($_POST['title'] ?? ''),
            'subtitle' => trim($_POST['subtitle'] ?? ''),
            'image' => $image,
            'highlights' => $highlights,
            'highlight_links' => $highlightLinks,
            'buttons' => [],
        ];

        foreach (($_POST['button_label'] ?? []) as $index => $label) {
            if (trim($label) === '') {
                continue;
            }

            $payload['buttons'][] = [
                'label' => trim($label),
                'url' => trim($_POST['button_url'][$index] ?? '#'),
                'style' => trim($_POST['button_style'][$index] ?? 'brand'),
            ];
        }
    } elseif ($activeSection === 'about') {
        $image = trim($_POST['image'] ?? '');
        $uploadedImage = $uploadSectionFile($_FILES['image_upload'] ?? [], 'sections');
        $image = $uploadedImage ?: $image;
        $profileDocument = trim($_POST['profile_document'] ?? '');
        $uploadedDocument = $uploadSectionFile($_FILES['profile_document_upload'] ?? [], 'documents');
        $profileDocument = $uploadedDocument ?: $profileDocument;

        $payload += [
            'kicker' => trim($_POST['kicker'] ?? ''),
            'title' => trim($_POST['title'] ?? ''),
            'image' => $image,
            'profile_document' => $profileDocument,
            'profile_document_label' => trim($_POST['profile_document_label'] ?? 'Profile Document'),
            'paragraphs' => lines_to_array($_POST['paragraphs'] ?? ''),
            'button_label' => trim($_POST['button_label'] ?? ''),
            'button_url' => trim($_POST['button_url'] ?? ''),
        ];
    } elseif ($activeSection === 'services') {
        $payload += [
            'kicker' => trim($_POST['kicker'] ?? ''),
            'title' => trim($_POST['title'] ?? ''),
            'items' => [],
        ];

        foreach (($_POST['item_title'] ?? []) as $index => $title) {
            if (trim($title) === '') {
                continue;
            }

            $image = trim($_POST['item_image'][$index] ?? '');
            $uploadedImage = $uploadSectionFile(multi_upload_file('item_image_upload', $index), 'sections');
            $image = $uploadedImage ?: $image;

            $payload['items'][] = [
                'slug' => trim($_POST['item_slug'][$index] ?? ''),
                'title' => trim($title),
                'icon' => trim($_POST['item_icon'][$index] ?? 'fa-box'),
                'image' => $image,
                'text' => trim($_POST['item_text'][$index] ?? ''),
            ];
        }
    } elseif ($activeSection === 'team') {
        $payload += [
            'kicker' => trim($_POST['kicker'] ?? ''),
            'title' => trim($_POST['title'] ?? ''),
        ];
    } elseif ($activeSection === 'company_profile') {
        $image = trim($_POST['image'] ?? '');
        $uploadedImage = $uploadSectionFile($_FILES['image_upload'] ?? [], 'sections');
        $image = $uploadedImage ?: $image;
        $profileDocument = trim($_POST['profile_document'] ?? '');
        $uploadedDocument = $uploadSectionFile($_FILES['profile_document_upload'] ?? [], 'documents');
        $profileDocument = $uploadedDocument ?: $profileDocument;

        $payload += [
            'kicker' => trim($_POST['kicker'] ?? ''),
            'title' => trim($_POST['title'] ?? ''),
            'image' => $image,
            'profile_document' => $profileDocument,
            'profile_document_label' => trim($_POST['profile_document_label'] ?? 'Profile Document'),
            'items' => lines_to_array($_POST['items'] ?? ''),
        ];
    } elseif ($activeSection === 'masonry_gallery') {
        $payload += [
            'kicker' => trim($_POST['kicker'] ?? ''),
            'title' => trim($_POST['title'] ?? ''),
        ];

        $masonryPayload = [];

        foreach (($_POST['masonry_image'] ?? []) as $index => $imageValue) {
            $image = trim($imageValue);
            $title = trim($_POST['masonry_title'][$index] ?? '');
            $category = trim($_POST['masonry_category'][$index] ?? 'Operations');
            $caption = trim($_POST['masonry_caption'][$index] ?? '');

            if (isset($_POST['masonry_delete'][$index])) {
                continue;
            }

            if ($image === '') {
                continue;
            }

            $masonryPayload[] = [
                'id' => trim($_POST['masonry_id'][$index] ?? '') ?: 'masonry-' . bin2hex(random_bytes(5)),
                'title' => $title,
                'category' => $category !== '' ? $category : 'Operations',
                'caption' => $caption,
                'image' => $image,
                'sort_order' => (int) ($_POST['masonry_sort_order'][$index] ?? ($index + 1)),
                'visible' => isset($_POST['masonry_visible'][$index]),
            ];
        }

        $uploadedFiles = $_FILES['masonry_upload'] ?? null;
        if ($uploadedFiles && is_array($uploadedFiles['name'])) {
            foreach ($uploadedFiles['name'] as $uploadIndex => $fileName) {
                $uploadedImage = $uploadSectionFile([
                    'name' => $fileName,
                    'type' => $uploadedFiles['type'][$uploadIndex] ?? '',
                    'tmp_name' => $uploadedFiles['tmp_name'][$uploadIndex] ?? '',
                    'error' => $uploadedFiles['error'][$uploadIndex] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $uploadedFiles['size'][$uploadIndex] ?? 0,
                ], 'masonry');

                if (!$uploadedImage) {
                    continue;
                }

                $masonryPayload[] = [
                    'id' => 'masonry-' . bin2hex(random_bytes(5)),
                    'title' => pathinfo((string) $fileName, PATHINFO_FILENAME),
                    'category' => trim($_POST['masonry_default_category'] ?? 'Operations') ?: 'Operations',
                    'caption' => '',
                    'image' => $uploadedImage,
                    'sort_order' => count($masonryPayload) + 1,
                    'visible' => true,
                ];
            }
        }

        $extraSaved = save_masonry_gallery_items($masonryPayload);
    } elseif ($activeSection === 'office') {
        $image = trim($_POST['image'] ?? '');
        $uploadedImage = $uploadSectionFile($_FILES['image_upload'] ?? [], 'sections');
        $payload['image'] = $uploadedImage ?: $image;
        $payload['items'] = [];
        foreach (($_POST['office_name'] ?? []) as $index => $name) {
            if (trim($name) === '') {
                continue;
            }

            $payload['items'][trim($name)] = trim($_POST['office_address'][$index] ?? '');
        }
    }

    if ($error === '') {
        $settings[$activeSection] = $payload;
        $message = (save_section_settings($settings) && $extraSaved) ? 'Section settings saved successfully.' : 'Unable to save section settings.';
    }
}

$sections = array_filter(
    all_section_content(),
    fn (string $key): bool => !in_array($key, $hiddenSectionManagerKeys, true),
    ARRAY_FILTER_USE_KEY
);
$active = $sections[$activeSection] ?? [];
$masonryItems = $activeSection === 'masonry_gallery' ? masonry_gallery_items(false) : [];

if ($activeSection === 'about') {
    $legacyProfileSection = section_content('company_profile');
    if (empty($active['profile_document']) && !empty($legacyProfileSection['profile_document'])) {
        $active['profile_document'] = $legacyProfileSection['profile_document'];
        $active['profile_document_label'] = $legacyProfileSection['profile_document_label'] ?? 'Profile Document';
    }
}

if (($_GET['picked_image'] ?? '') !== '' && ($_GET['section'] ?? '') === $activeSection) {
    $pickedImage = trim($_GET['picked_image']);
    $pickerField = $_GET['field'] ?? '';
    $pickerIndex = isset($_GET['index']) ? (int) $_GET['index'] : null;

    if (str_starts_with($pickedImage, 'uploads/')) {
        if ($pickerField === 'image') {
            $active['image'] = $pickedImage;
        } elseif ($pickerField === 'item_image' && $pickerIndex !== null) {
            $active['items'][$pickerIndex]['image'] = $pickedImage;
        } elseif ($pickerField === 'member_image' && $pickerIndex !== null) {
            $active['items'][$pickerIndex]['image'] = $pickedImage;
        } elseif ($pickerField === 'profile_document') {
            $active['profile_document'] = $pickedImage;
        } elseif ($pickerField === 'masonry_image' && $pickerIndex !== null) {
            if (!isset($masonryItems[$pickerIndex])) {
                $masonryItems[$pickerIndex] = [
                    'id' => '',
                    'title' => '',
                    'image' => '',
                    'sort_order' => $pickerIndex + 1,
                    'visible' => true,
                ];
            }

            $masonryItems[$pickerIndex]['image'] = $pickedImage;
        }

        $message = 'Image selected from gallery. Click Save Section to apply it.';
    }
}

$pageTitle = 'Section Manager - ' . $site['title'];
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
        <?php if ($error): ?>
            <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 font-bold text-red-700"><?php echo e($error); ?></div>
        <?php endif; ?>

        <div class="mb-5 overflow-x-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
            <div class="flex min-w-max gap-2">
                <?php foreach ($sections as $key => $section): ?>
                    <a class="rounded-xl px-4 py-3 text-sm font-black <?php echo $key === $activeSection ? 'bg-blue-700 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'; ?>" href="<?php echo e(base_url('dashboard/section-manager.php?section=' . $key)); ?>">
                        <?php echo e($section['label'] ?? ucwords(str_replace('_', ' ', $key))); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <form class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm lg:p-7" method="post" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="section_key" value="<?php echo e($activeSection); ?>">
            <div class="mb-6 flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-700">Editing</p>
                    <h2 class="mt-1 text-2xl font-black text-slate-950"><?php echo e($active['label'] ?? 'Section'); ?></h2>
                </div>
                <label class="inline-flex items-center gap-3 rounded-xl bg-slate-100 px-4 py-3 text-sm font-black text-slate-700">
                    <input class="h-4 w-4 accent-blue-700" type="checkbox" name="visible" <?php echo ($active['visible'] ?? true) ? 'checked' : ''; ?>>
                    Show on website
                </label>
            </div>

            <?php if (in_array($activeSection, ['hero', 'about', 'services', 'team', 'company_profile', 'masonry_gallery'], true)): ?>
                <div class="grid gap-4 lg:grid-cols-2">
                    <label class="block text-sm font-black text-slate-600">Kicker
                        <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-500" name="kicker" value="<?php echo e($active['kicker'] ?? ''); ?>">
                    </label>
                    <label class="block text-sm font-black text-slate-600">Title
                        <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-500" name="title" value="<?php echo e($active['title'] ?? ''); ?>">
                    </label>
                </div>
            <?php endif; ?>

            <?php if ($activeSection === 'hero'): ?>
                <?php
                $heroDefaultHighlightLinks = [
                    'port agency & vessel husbandry' => 'pages/shipping-agent.php',
                    'crew management & repatriation' => 'pages/sea-freight.php',
                    'customs brokerage / c&f agent' => 'pages/customs-brokerage.php',
                    'stevedoring & cargo supervision' => 'pages/project-cargo.php',
                ];
                $heroDefaultHighlightLinksByIndex = [
                    'pages/shipping-agent.php',
                    'pages/sea-freight.php',
                    'pages/customs-brokerage.php',
                    'pages/project-cargo.php',
                ];
                $heroHighlightLabels = array_values($active['highlights'] ?? []);
                $heroHighlightUrls = array_values($active['highlight_links'] ?? []);
                $heroHighlightRows = [];
                foreach ($heroHighlightLabels as $index => $label) {
                    $label = (string) $label;
                    $key = strtolower(preg_replace('/\s+/', ' ', trim(strip_tags($label))));
                    $heroHighlightRows[] = [
                        'label' => $label,
                        'url' => $heroHighlightUrls[$index] ?? ($heroDefaultHighlightLinks[$key] ?? ($heroDefaultHighlightLinksByIndex[$index] ?? '')),
                    ];
                }
                $heroHighlightRows[] = ['label' => '', 'url' => ''];
                ?>
                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <label class="block text-sm font-black text-slate-600">Subtitle
                        <textarea class="mt-2 min-h-28 w-full rounded-xl border border-slate-300 px-4 py-3 leading-7 outline-none focus:border-blue-500" name="subtitle"><?php echo e($active['subtitle'] ?? ''); ?></textarea>
                    </label>
                    <?php render_image_picker('Background Image', 'image', $active['image'] ?? '', $activeSection, 'image', 'image_upload'); ?>
                </div>
                <div class="mt-6 grid gap-3">
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-blue-700">Right Highlight Cards</p>
                    <?php foreach ($heroHighlightRows as $highlight): ?>
                        <div class="grid gap-3 rounded-2xl bg-slate-50 p-4 lg:grid-cols-[1fr_1fr]">
                            <input class="rounded-xl border border-slate-300 px-4 py-3" name="highlight_label[]" value="<?php echo e($highlight['label']); ?>" placeholder="Highlight text">
                            <input class="rounded-xl border border-slate-300 px-4 py-3" name="highlight_url[]" value="<?php echo e($highlight['url']); ?>" placeholder="Highlight URL">
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-6 grid gap-3">
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-blue-700">Buttons</p>
                    <?php foreach (array_merge(($active['buttons'] ?? []), [['label' => '', 'url' => '', 'style' => 'brand']]) as $button): ?>
                        <div class="grid gap-3 rounded-2xl bg-slate-50 p-4 lg:grid-cols-[1fr_1fr_160px]">
                            <input class="rounded-xl border border-slate-300 px-4 py-3" name="button_label[]" value="<?php echo e($button['label'] ?? ''); ?>" placeholder="Button label">
                            <input class="rounded-xl border border-slate-300 px-4 py-3" name="button_url[]" value="<?php echo e($button['url'] ?? ''); ?>" placeholder="Button URL">
                            <select class="rounded-xl border border-slate-300 px-4 py-3" name="button_style[]">
                                <?php foreach (['orange', 'brand', 'aqua'] as $style): ?>
                                    <option value="<?php echo e($style); ?>" <?php echo ($button['style'] ?? '') === $style ? 'selected' : ''; ?>><?php echo e(ucfirst($style)); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($activeSection === 'about'): ?>
                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <?php render_image_picker('Section Image', 'image', $active['image'] ?? '', $activeSection, 'image', 'image_upload'); ?>
                    <div class="grid gap-4">
                        <label class="block text-sm font-black text-slate-600">Document Button Text
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="profile_document_label" value="<?php echo e($active['profile_document_label'] ?? 'Profile Document'); ?>">
                        </label>
                        <?php render_document_picker('Profile Document', 'profile_document', $active['profile_document'] ?? '', $activeSection, 'profile_document', 'profile_document_upload'); ?>
                    </div>
                    <label class="block text-sm font-black text-slate-600">Button Label
                        <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="button_label" value="<?php echo e($active['button_label'] ?? ''); ?>">
                    </label>
                    <label class="block text-sm font-black text-slate-600">Button URL
                        <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="button_url" value="<?php echo e($active['button_url'] ?? ''); ?>">
                    </label>
                    <label class="block text-sm font-black text-slate-600 lg:col-span-2">Paragraphs <span class="font-semibold text-slate-400">(one paragraph per line)</span>
                        <textarea class="mt-2 min-h-48 w-full rounded-xl border border-slate-300 px-4 py-3 leading-7" name="paragraphs"><?php echo e(implode("\n", $active['paragraphs'] ?? [])); ?></textarea>
                    </label>
                </div>
            <?php elseif ($activeSection === 'services'): ?>
                <div class="mt-6 grid gap-4">
                    <?php foreach (array_merge(($active['items'] ?? []), [['title' => '', 'slug' => '', 'icon' => '', 'image' => '', 'text' => '']]) as $index => $item): ?>
                        <div class="grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 lg:grid-cols-2">
                            <input class="rounded-xl border border-slate-300 px-4 py-3" name="item_title[]" value="<?php echo e($item['title'] ?? ''); ?>" placeholder="Service title">
                            <input class="rounded-xl border border-slate-300 px-4 py-3" name="item_slug[]" value="<?php echo e($item['slug'] ?? ''); ?>" placeholder="service-slug">
                            <input class="rounded-xl border border-slate-300 px-4 py-3" name="item_icon[]" value="<?php echo e($item['icon'] ?? ''); ?>" placeholder="fa-ship">
                            <div class="lg:col-span-2">
                                <?php render_image_picker('Service Card Image', 'item_image[]', $item['image'] ?? '', $activeSection, 'item_image', 'item_image_upload[]', $index); ?>
                            </div>
                            <textarea class="min-h-24 rounded-xl border border-slate-300 px-4 py-3 leading-7 lg:col-span-2" name="item_text[]" placeholder="Short card text"><?php echo e($item['text'] ?? ''); ?></textarea>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($activeSection === 'team'): ?>
                <div class="mt-6 rounded-2xl border border-blue-200 bg-blue-50 p-5">
                    <p class="font-black text-slate-950">Team member data is managed separately.</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Use the Team Members controller to add, edit, update, hide or delete people shown in this section.</p>
                    <a class="mt-4 inline-flex rounded-xl bg-blue-700 px-5 py-3 text-sm font-black text-white hover:bg-slate-950" href="<?php echo e(base_url('dashboard/team-members.php')); ?>">
                        <i class="fa-solid fa-users-gear mr-2"></i>Manage Team Members
                    </a>
                </div>
            <?php elseif ($activeSection === 'company_profile'): ?>
                <div class="mt-4">
                    <?php render_image_picker('Section Image', 'image', $active['image'] ?? '', $activeSection, 'image', 'image_upload'); ?>
                </div>
                <div class="mt-4 grid gap-4 lg:grid-cols-[1fr_2fr]">
                    <label class="block text-sm font-black text-slate-600">Document Button Text
                        <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="profile_document_label" value="<?php echo e($active['profile_document_label'] ?? 'Profile Document'); ?>">
                    </label>
                    <?php render_document_picker('Profile Document', 'profile_document', $active['profile_document'] ?? '', $activeSection, 'profile_document', 'profile_document_upload'); ?>
                </div>
                <label class="mt-4 block text-sm font-black text-slate-600">Credential Items <span class="font-semibold text-slate-400">(one per line)</span>
                    <textarea class="mt-2 min-h-48 w-full rounded-xl border border-slate-300 px-4 py-3 leading-7" name="items"><?php echo e(implode("\n", $active['items'] ?? [])); ?></textarea>
                </label>
            <?php elseif ($activeSection === 'masonry_gallery'): ?>
                <div class="mt-5 grid gap-4">
                    <div class="grid gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm leading-6 text-emerald-950 lg:grid-cols-[1.4fr_.8fr]">
                        <div>
                            <p class="font-black">About Gallery</p>
                            <p class="mt-1 text-emerald-800">Use logistics proof images: port handling, warehouse, transport, customs desk, team coordination or cargo movement.</p>
                            <p class="mt-1 font-bold">Recommended size: 1200 x 900 px landscape or 900 x 1200 px portrait.</p>
                        </div>
                        <label class="block text-sm font-black text-emerald-900">Default Upload Category
                            <input class="mt-2 w-full rounded-xl border border-emerald-200 bg-white px-4 py-3" name="masonry_default_category" value="Operations" placeholder="Operations">
                        </label>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <label class="block text-sm font-black text-slate-600">Gallery Upload
                            <input class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm" type="file" name="masonry_upload[]" accept="image/*" multiple>
                        </label>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <?php foreach (array_values($masonryItems) as $index => $item): ?>
                            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                <div class="aspect-[16/10] overflow-hidden bg-slate-100">
                                    <img class="h-full w-full object-cover" src="<?php echo e(base_url($item['image'] ?? '')); ?>" alt="<?php echo e($item['title'] ?? ''); ?>">
                                </div>
                                <div class="grid gap-3 p-4">
                                    <input type="hidden" name="masonry_id[]" value="<?php echo e($item['id'] ?? ''); ?>">
                                    <input type="hidden" name="masonry_image[]" value="<?php echo e($item['image'] ?? ''); ?>">
                                    <div class="grid gap-3 sm:grid-cols-[1fr_160px]">
                                        <label class="block text-sm font-black text-slate-600">Title
                                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="masonry_title[]" value="<?php echo e($item['title'] ?? ''); ?>" placeholder="Gallery image title">
                                        </label>
                                        <label class="block text-sm font-black text-slate-600">Category
                                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="masonry_category[]" value="<?php echo e($item['category'] ?? 'Operations'); ?>" placeholder="Port / Warehouse">
                                        </label>
                                    </div>
                                    <label class="block text-sm font-black text-slate-600">Short Caption
                                        <textarea class="mt-2 min-h-20 w-full rounded-xl border border-slate-300 px-4 py-3 leading-6" name="masonry_caption[]" placeholder="One or two lines about this operation"><?php echo e($item['caption'] ?? ''); ?></textarea>
                                    </label>
                                    <div class="grid gap-3 sm:grid-cols-[120px_1fr_1fr]">
                                        <label class="block text-sm font-black text-slate-600">Order
                                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" type="number" name="masonry_sort_order[]" value="<?php echo e((string) ($item['sort_order'] ?? ($index + 1))); ?>">
                                        </label>
                                        <label class="flex items-end gap-2 rounded-xl bg-slate-50 px-3 py-3 text-sm font-black text-slate-700">
                                            <input class="h-4 w-4 accent-blue-700" type="checkbox" name="masonry_visible[<?php echo $index; ?>]" <?php echo ($item['visible'] ?? true) ? 'checked' : ''; ?>>
                                            Show
                                        </label>
                                        <label class="flex items-end gap-2 rounded-xl bg-red-50 px-3 py-3 text-sm font-black text-red-700">
                                            <input class="h-4 w-4 accent-red-600" type="checkbox" name="masonry_delete[<?php echo $index; ?>]">
                                            Delete
                                        </label>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                        <?php if (!$masonryItems): ?>
                            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500 sm:col-span-2 xl:col-span-3">
                                <i class="fa-solid fa-images mb-3 text-4xl text-slate-300"></i>
                                <p class="font-bold">No masonry images yet. Upload images above and save.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ($activeSection === 'office'): ?>
                <div class="mb-4">
                    <?php render_image_picker('Section Image', 'image', $active['image'] ?? '', $activeSection, 'image', 'image_upload'); ?>
                </div>
                <div class="grid gap-4">
                    <?php foreach (array_merge(($active['items'] ?? []), ['' => '']) as $name => $address): ?>
                        <div class="grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 lg:grid-cols-[280px_1fr]">
                            <input class="rounded-xl border border-slate-300 px-4 py-3" name="office_name[]" value="<?php echo e($name); ?>" placeholder="Office title">
                            <textarea class="min-h-24 rounded-xl border border-slate-300 px-4 py-3 leading-7" name="office_address[]" placeholder="Office address"><?php echo e($address); ?></textarea>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="mt-8 flex justify-end">
                <button class="inline-flex items-center rounded-xl bg-blue-700 px-6 py-3 font-black text-white shadow-lg shadow-blue-700/20 hover:bg-slate-950" type="submit">
                    <i class="fa-solid fa-floppy-disk mr-2"></i>Save Section
                </button>
            </div>
        </form>
    </main>
    <?php require __DIR__ . '/footer.php'; ?>
    </div>
</div>
<script>
    (() => {
        const textareas = Array.from(document.querySelectorAll('textarea'));

        const applyWrap = (textarea, before, after = '') => {
            const start = textarea.selectionStart ?? textarea.value.length;
            const end = textarea.selectionEnd ?? textarea.value.length;
            const selected = textarea.value.slice(start, end);
            textarea.setRangeText(before + selected + after, start, end, 'end');
            textarea.focus();
        };

        const applyLineWrap = (textarea, before, after = '') => {
            const value = textarea.value;
            let start = textarea.selectionStart ?? 0;
            let end = textarea.selectionEnd ?? 0;

            if (start === end) {
                start = value.lastIndexOf('\n', Math.max(0, start - 1)) + 1;
                const nextBreak = value.indexOf('\n', end);
                end = nextBreak === -1 ? value.length : nextBreak;
            }

            const selected = value.slice(start, end);
            textarea.setRangeText(before + selected + after, start, end, 'end');
            textarea.focus();
        };

        const buttons = [
            ['bold', '<strong>', '</strong>', 'fa-bold', 'Bold'],
            ['italic', '<em>', '</em>', 'fa-italic', 'Italic'],
            ['underline', '<u>', '</u>', 'fa-underline', 'Underline'],
            ['left', '<div style="text-align:left">', '</div>', 'fa-align-left', 'Align left', true],
            ['center', '<div style="text-align:center">', '</div>', 'fa-align-center', 'Align center', true],
            ['right', '<div style="text-align:right">', '</div>', 'fa-align-right', 'Align right', true],
            ['justify', '<div style="text-align:justify">', '</div>', 'fa-align-justify', 'Justify', true],
        ];

        textareas.forEach(textarea => {
            if (textarea.closest('[data-editor-toolbar-ready]')) return;

            const wrapper = document.createElement('div');
            wrapper.dataset.editorToolbarReady = '1';
            wrapper.className = 'mt-2 overflow-hidden rounded-xl border border-slate-300 bg-white';

            const toolbar = document.createElement('div');
            toolbar.className = 'flex flex-wrap gap-1.5 border-b border-slate-200 bg-slate-50 p-2';

            buttons.forEach(([name, before, after, icon, title, lineMode]) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.title = title;
                button.className = 'rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-black text-slate-700 hover:bg-slate-100';
                button.innerHTML = `<i class="fa-solid ${icon}"></i>`;
                button.addEventListener('click', () => {
                    if (lineMode) {
                        applyLineWrap(textarea, before, after);
                    } else {
                        applyWrap(textarea, before, after);
                    }
                });
                toolbar.appendChild(button);
            });

            const listButton = document.createElement('button');
            listButton.type = 'button';
            listButton.title = 'Bullet list';
            listButton.className = 'rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-black text-slate-700 hover:bg-slate-100';
            listButton.innerHTML = '<i class="fa-solid fa-list-ul"></i>';
            listButton.addEventListener('click', () => applyWrap(textarea, '<ul><li>', '</li></ul>'));
            toolbar.appendChild(listButton);

            const linkButton = document.createElement('button');
            linkButton.type = 'button';
            linkButton.title = 'Link';
            linkButton.className = 'rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-black text-slate-700 hover:bg-slate-100';
            linkButton.innerHTML = '<i class="fa-solid fa-link"></i>';
            linkButton.addEventListener('click', () => {
                const url = window.prompt('Enter link URL');
                if (!url) return;
                applyWrap(textarea, `<a href="${url.replace(/"/g, '&quot;')}">`, '</a>');
            });
            toolbar.appendChild(linkButton);

            textarea.parentNode.insertBefore(wrapper, textarea);
            wrapper.appendChild(toolbar);
            wrapper.appendChild(textarea);
            textarea.classList.remove('mt-2', 'border', 'border-slate-300', 'rounded-xl');
            textarea.classList.add('block', 'w-full', 'rounded-none', 'border-0', 'focus:ring-0');
        });
    })();
</script>
</body>
</html>
