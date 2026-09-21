<?php
require_once __DIR__ . '/../includes/config.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . base_url('login.php'));
    exit;
}

function render_setting_image_picker(string $label, string $field, string $uploadField, string $value): void
{
    $galleryUrl = base_url('dashboard/gallery.php?' . http_build_query([
        'select' => 'site_setting',
        'field' => $field,
    ]));
    ?>
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <label class="block text-sm font-black text-slate-600"><?php echo e($label); ?></label>
        <div class="mt-3 grid gap-3 md:grid-cols-[130px_1fr]">
            <div class="grid aspect-square place-items-center overflow-hidden rounded-xl bg-white">
                <?php if ($value !== ''): ?>
                    <img class="h-full w-full object-contain p-3" src="<?php echo e(base_url($value)); ?>" alt="">
                <?php else: ?>
                    <i class="fa-solid fa-image text-3xl text-slate-300"></i>
                <?php endif; ?>
            </div>
            <div class="grid content-start gap-3">
                <input class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" name="<?php echo e($field); ?>" value="<?php echo e($value); ?>" placeholder="uploads/settings/logo.png">
                <div class="grid gap-2 sm:grid-cols-2">
                    <a class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-black text-blue-700 hover:bg-blue-100" href="<?php echo e($galleryUrl); ?>">
                        <i class="fa-solid fa-images mr-2"></i>Pick from gallery
                    </a>
                    <label class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-black text-slate-700 hover:bg-slate-50">
                        <i class="fa-solid fa-upload mr-2"></i>Pick from computer
                        <input class="hidden" type="file" name="<?php echo e($uploadField); ?>" accept="image/*,.ico">
                    </label>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function site_setting_multi_upload_file(string $field, int $index): array
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

function save_site_button_settings(array $buttons, string $profileDocument): bool
{
    $sectionSettings = saved_section_settings();
    $sectionSettings['hero'] = is_array($sectionSettings['hero'] ?? null) ? $sectionSettings['hero'] : [];
    $sectionSettings['hero']['buttons'] = $buttons;

    if ($profileDocument !== '') {
        $sectionSettings['about'] = is_array($sectionSettings['about'] ?? null) ? $sectionSettings['about'] : [];
        $sectionSettings['about']['profile_document'] = $profileDocument;
    }

    if (db_json_save('section_settings', $sectionSettings)) {
        return true;
    }

    $file = section_settings_file();
    $directory = dirname($file);
    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    return file_put_contents($file, json_encode($sectionSettings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
}

$message = (string) ($_SESSION['site_settings_message'] ?? '');
unset($_SESSION['site_settings_message']);
$error = '';
$settings = all_site_settings(site_setting_defaults($site));
$heroSettings = section_content('hero');
$aboutSettings = section_content('about');
$siteButtons = $heroSettings['buttons'] ?? [];
$profileDocument = trim((string) ($aboutSettings['profile_document'] ?? ''));

if ($profileDocument === '') {
    foreach ($siteButtons as $button) {
        if (strcasecmp(trim((string) ($button['label'] ?? '')), 'Download Company Profile') === 0) {
            $profileDocument = trim((string) ($button['url'] ?? ''));
            break;
        }
    }
}

if (($_GET['picked_image'] ?? '') !== '' && ($_GET['field'] ?? '') !== '') {
    $field = $_GET['field'];
    $pickedImage = trim($_GET['picked_image']);

    if (in_array($field, ['logo_image', 'footer_logo_image', 'favicon'], true) && str_starts_with($pickedImage, 'uploads/')) {
        $settings[$field] = $pickedImage;
        $message = 'Image selected from gallery. Click Save Site Settings to apply it.';
    }
}

$canSaveSettings = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);

    if ($contentLength > 0 && empty($_POST)) {
        $effectiveFileLimit = (string) ini_get('upload_max_filesize');
        $effectivePostLimit = (string) ini_get('post_max_size');
        $error = "The upload request could not be processed. Effective server limits: file {$effectiveFileLimit}, request {$effectivePostLimit}. The upload may have timed out; please select the file again and retry.";
    } elseif (!csrf_is_valid()) {
        $error = 'Your session token expired. Nothing was saved. Please try again using the refreshed form below.';
    } else {
        $canSaveSettings = true;
    }
}

if ($canSaveSettings) {
    $logoImage = trim($_POST['logo_image'] ?? '');
    $footerLogoImage = trim($_POST['footer_logo_image'] ?? '');
    $favicon = trim($_POST['favicon'] ?? '');
    $logoUpload = $_FILES['logo_upload'] ?? [];
    $footerLogoUpload = $_FILES['footer_logo_upload'] ?? [];
    $faviconUpload = $_FILES['favicon_upload'] ?? [];
    $uploadedLogo = upload_dashboard_file($logoUpload, 'settings');
    if (!$uploadedLogo && dashboard_upload_was_requested($logoUpload)) {
        $error = dashboard_upload_last_error() ?: 'Header logo could not be uploaded.';
    }
    $uploadedFooterLogo = upload_dashboard_file($footerLogoUpload, 'settings');
    if ($error === '' && !$uploadedFooterLogo && dashboard_upload_was_requested($footerLogoUpload)) {
        $error = dashboard_upload_last_error() ?: 'Footer logo could not be uploaded.';
    }
    $uploadedFavicon = upload_dashboard_file($faviconUpload, 'settings');
    if ($error === '' && !$uploadedFavicon && dashboard_upload_was_requested($faviconUpload)) {
        $error = dashboard_upload_last_error() ?: 'Favicon could not be uploaded.';
    }
    $buttonPayload = [];
    $uploadedProfileDocument = '';

    foreach (($_POST['button_label'] ?? []) as $index => $labelValue) {
        $label = trim((string) $labelValue);
        if ($label === '') {
            continue;
        }

        $url = trim((string) ($_POST['button_url'][$index] ?? ''));
        if (strcasecmp($label, 'Download Company Profile') === 0) {
            $documentUpload = site_setting_multi_upload_file('button_document_upload', (int) $index);
            $uploadError = (int) ($documentUpload['error'] ?? UPLOAD_ERR_NO_FILE);

            if ($uploadError !== UPLOAD_ERR_NO_FILE) {
                $extension = strtolower(pathinfo((string) ($documentUpload['name'] ?? ''), PATHINFO_EXTENSION));
                if (in_array($uploadError, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
                    $error = 'Company profile upload failed. The file exceeds the 100 MB upload limit.';
                } elseif ($uploadError === UPLOAD_ERR_PARTIAL) {
                    $error = 'Company profile upload was interrupted. Please select the file and try again.';
                } elseif ($uploadError !== UPLOAD_ERR_OK) {
                    $error = 'Company profile file could not be uploaded. Please select it again.';
                } elseif (!in_array($extension, ['pdf', 'doc', 'docx'], true)) {
                    $error = 'Company profile upload failed. Please select a PDF, DOC or DOCX file.';
                } elseif ((int) ($documentUpload['size'] ?? 0) > 100 * 1024 * 1024) {
                    $error = 'Company profile upload failed. The maximum file size is 100 MB.';
                } else {
                    $uploadedProfileDocument = upload_dashboard_file($documentUpload, 'gallery') ?: '';
                    if ($uploadedProfileDocument === '') {
                        $error = 'Company profile file could not be uploaded.';
                    }
                }
            }

            $url = $uploadedProfileDocument ?: $url;
            $profileDocument = $url;
        }

        $buttonPayload[] = [
            'label' => $label,
            'url' => $url,
            'style' => trim((string) ($_POST['button_style'][$index] ?? 'brand')),
        ];
    }

    $settings = [
        'title' => trim($_POST['title'] ?? ''),
        'since' => trim($_POST['since'] ?? ''),
        'tagline' => trim($_POST['tagline'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'mobile' => trim($_POST['mobile'] ?? ''),
        'whatsapp' => trim($_POST['whatsapp'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'logo_text' => trim($_POST['logo_text'] ?? ''),
        'logo_image' => $uploadedLogo ?: $logoImage,
        'footer_logo_image' => $uploadedFooterLogo ?: $footerLogoImage,
        'favicon' => $uploadedFavicon ?: $favicon,
        'meta_title' => trim($_POST['meta_title'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
        'meta_keywords' => trim($_POST['meta_keywords'] ?? ''),
        'copyright' => trim($_POST['copyright'] ?? ''),
        'socials' => [
            'facebook' => trim($_POST['social_facebook'] ?? '#'),
            'linkedin' => trim($_POST['social_linkedin'] ?? '#'),
            'instagram' => trim($_POST['social_instagram'] ?? '#'),
            'x-twitter' => trim($_POST['social_x_twitter'] ?? '#'),
        ],
        'smtp' => [
            'host' => trim($_POST['smtp_host'] ?? ''),
            'port' => trim($_POST['smtp_port'] ?? '587'),
            'username' => trim($_POST['smtp_username'] ?? ''),
            'password' => trim($_POST['smtp_password'] ?? ''),
            'encryption' => trim($_POST['smtp_encryption'] ?? 'tls'),
            'from_email' => trim($_POST['smtp_from_email'] ?? ''),
            'from_name' => trim($_POST['smtp_from_name'] ?? ''),
        ],
    ];

    $siteSaved = $error === '' && save_site_settings($settings);
    $buttonsSaved = $error === '' && save_site_button_settings($buttonPayload, $profileDocument);

    if ($siteSaved && $buttonsSaved) {
        $_SESSION['site_settings_message'] = $uploadedProfileDocument !== ''
            ? 'Site settings saved. The company profile was uploaded and added to Gallery.'
            : 'Site settings saved successfully.';
        $activeTab = trim((string) ($_POST['active_settings_tab'] ?? 'identity'));
        $allowedTabs = ['identity', 'contact', 'buttons', 'social', 'seo', 'smtp'];
        $activeTab = in_array($activeTab, $allowedTabs, true) ? $activeTab : 'identity';
        header('Location: ' . base_url('dashboard/site-settings.php') . '?tab=' . rawurlencode($activeTab));
        exit;
    } elseif ($error === '') {
        $error = 'Unable to save all site settings.';
    }
}

$pageTitle = 'Site Settings - ' . $site['title'];
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

            <form class="grid gap-4" method="post" enctype="multipart/form-data" data-settings-tabs>
                <?php echo csrf_field(); ?>
                <input type="hidden" name="active_settings_tab" value="identity" data-active-settings-tab>
                <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
                    <div class="flex min-w-max gap-2">
                        <button class="rounded-xl px-4 py-3 text-sm font-black" type="button" data-settings-tab="identity">
                            <i class="fa-solid fa-id-card mr-2"></i>Identity
                        </button>
                        <button class="rounded-xl px-4 py-3 text-sm font-black" type="button" data-settings-tab="contact">
                            <i class="fa-solid fa-phone mr-2"></i>Contact
                        </button>
                        <button class="rounded-xl px-4 py-3 text-sm font-black" type="button" data-settings-tab="buttons">
                            <i class="fa-solid fa-arrow-pointer mr-2"></i>Buttons
                        </button>
                        <button class="rounded-xl px-4 py-3 text-sm font-black" type="button" data-settings-tab="social">
                            <i class="fa-solid fa-share-nodes mr-2"></i>Social
                        </button>
                        <button class="rounded-xl px-4 py-3 text-sm font-black" type="button" data-settings-tab="seo">
                            <i class="fa-solid fa-magnifying-glass-chart mr-2"></i>SEO
                        </button>
                        <button class="rounded-xl px-4 py-3 text-sm font-black" type="button" data-settings-tab="smtp">
                            <i class="fa-solid fa-envelope-circle-check mr-2"></i>SMTP
                        </button>
                    </div>
                </div>

                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm lg:p-7" data-settings-panel="identity">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-blue-700">Identity</p>
                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        <label class="block text-sm font-black text-slate-600">Site Title
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="title" value="<?php echo e($settings['title'] ?? ''); ?>">
                        </label>
                        <label class="block text-sm font-black text-slate-600">Since Text
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="since" value="<?php echo e($settings['since'] ?? ''); ?>">
                        </label>
                        <label class="block text-sm font-black text-slate-600 lg:col-span-2">Tagline
                            <textarea class="mt-2 min-h-24 w-full rounded-xl border border-slate-300 px-4 py-3 leading-7" name="tagline"><?php echo e($settings['tagline'] ?? ''); ?></textarea>
                        </label>
                        <label class="block text-sm font-black text-slate-600">Logo Text
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="logo_text" value="<?php echo e($settings['logo_text'] ?? ''); ?>">
                        </label>
                        <div></div>
                        <?php render_setting_image_picker('Header Logo Upload', 'logo_image', 'logo_upload', $settings['logo_image'] ?? ''); ?>
                        <?php render_setting_image_picker('Footer Logo Upload', 'footer_logo_image', 'footer_logo_upload', $settings['footer_logo_image'] ?? ''); ?>
                        <?php render_setting_image_picker('Site Icon / Favicon', 'favicon', 'favicon_upload', $settings['favicon'] ?? ''); ?>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm lg:p-7" data-settings-panel="contact">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-blue-700">Contact / Topbar</p>
                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        <label class="block text-sm font-black text-slate-600">Phone
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="phone" value="<?php echo e($settings['phone'] ?? ''); ?>">
                        </label>
                        <label class="block text-sm font-black text-slate-600">Mobile
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="mobile" value="<?php echo e($settings['mobile'] ?? ''); ?>">
                        </label>
                        <label class="block text-sm font-black text-slate-600">Whatsapp
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="whatsapp" value="<?php echo e($settings['whatsapp'] ?? ''); ?>">
                        </label>
                        <label class="block text-sm font-black text-slate-600">Email
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" type="email" name="email" value="<?php echo e($settings['email'] ?? ''); ?>">
                        </label>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm lg:p-7" data-settings-panel="buttons">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-blue-700">Homepage Buttons</p>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Manage the buttons displayed in the homepage hero section.</p>
                    <div class="mt-5 grid gap-4">
                        <?php foreach (array_merge($siteButtons, [['label' => '', 'url' => '', 'style' => 'brand']]) as $index => $button): ?>
                            <?php
                            $buttonLabel = (string) ($button['label'] ?? '');
                            $isProfileButton = strcasecmp(trim($buttonLabel), 'Download Company Profile') === 0;
                            $buttonUrl = $isProfileButton && $profileDocument !== '' ? $profileDocument : (string) ($button['url'] ?? '');
                            $documentName = $buttonUrl !== '' ? basename($buttonUrl) : '';
                            ?>
                            <div class="grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 lg:grid-cols-[1fr_1.5fr_160px]" data-button-row>
                                <label class="block text-sm font-black text-slate-600">Button Label
                                    <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal" name="button_label[]" value="<?php echo e($buttonLabel); ?>" placeholder="Button label" data-button-label>
                                </label>
                                <div data-button-url-field class="<?php echo $isProfileButton ? 'hidden' : ''; ?>">
                                    <label class="block text-sm font-black text-slate-600">Button URL
                                        <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal" name="button_url[]" value="<?php echo e($buttonUrl); ?>" placeholder="Button URL" <?php echo $isProfileButton ? 'disabled' : ''; ?>>
                                    </label>
                                </div>
                                <div data-button-document-field class="rounded-xl border border-blue-100 bg-white p-3 <?php echo $isProfileButton ? '' : 'hidden'; ?>">
                                    <input type="hidden" name="button_url[]" value="<?php echo e($buttonUrl); ?>" data-document-path-input <?php echo $isProfileButton ? '' : 'disabled'; ?>>
                                    <p class="text-xs font-black uppercase tracking-wide text-blue-700">Company Profile File</p>
                                    <?php if ($documentName !== ''): ?>
                                        <a class="mt-2 block truncate text-xs font-bold text-slate-600 hover:text-blue-700" href="<?php echo e(base_url($buttonUrl)); ?>" target="_blank" title="<?php echo e($documentName); ?>">
                                            <i class="fa-solid fa-file-lines mr-1.5"></i><?php echo e($documentName); ?>
                                        </a>
                                    <?php else: ?>
                                        <p class="mt-2 text-xs text-slate-400">No file uploaded</p>
                                    <?php endif; ?>
                                    <label class="mt-3 inline-flex cursor-pointer items-center rounded-lg bg-blue-700 px-3 py-2 text-xs font-black text-white hover:bg-slate-950">
                                        <i class="fa-solid fa-upload mr-2"></i>Choose file
                                        <input type="hidden" name="MAX_FILE_SIZE" value="104857600">
                                        <input class="hidden" type="file" name="button_document_upload[]" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" data-profile-upload>
                                    </label>
                                    <p class="mt-2 text-[11px] font-bold text-slate-400">PDF, DOC or DOCX · Max 100 MB</p>
                                    <p class="mt-2 hidden truncate text-xs font-black text-emerald-700" data-profile-upload-name></p>
                                </div>
                                <label class="block text-sm font-black text-slate-600">Style
                                    <select class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal" name="button_style[]">
                                        <?php foreach (['orange', 'brand', 'aqua'] as $style): ?>
                                            <option value="<?php echo e($style); ?>" <?php echo ($button['style'] ?? '') === $style ? 'selected' : ''; ?>><?php echo e(ucfirst($style)); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm lg:p-7" data-settings-panel="social">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-blue-700">Social Links</p>
                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        <label class="block text-sm font-black text-slate-600">Facebook
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="social_facebook" value="<?php echo e($settings['socials']['facebook'] ?? '#'); ?>">
                        </label>
                        <label class="block text-sm font-black text-slate-600">LinkedIn
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="social_linkedin" value="<?php echo e($settings['socials']['linkedin'] ?? '#'); ?>">
                        </label>
                        <label class="block text-sm font-black text-slate-600">Instagram
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="social_instagram" value="<?php echo e($settings['socials']['instagram'] ?? '#'); ?>">
                        </label>
                        <label class="block text-sm font-black text-slate-600">X / Twitter
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="social_x_twitter" value="<?php echo e($settings['socials']['x-twitter'] ?? '#'); ?>">
                        </label>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm lg:p-7" data-settings-panel="seo">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-blue-700">SEO Metadata</p>
                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        <label class="block text-sm font-black text-slate-600">Meta Title
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="meta_title" value="<?php echo e($settings['meta_title'] ?? ''); ?>">
                        </label>
                        <label class="block text-sm font-black text-slate-600">Meta Keywords
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="meta_keywords" value="<?php echo e($settings['meta_keywords'] ?? ''); ?>">
                        </label>
                        <label class="block text-sm font-black text-slate-600 lg:col-span-2">Meta Description
                            <textarea class="mt-2 min-h-28 w-full rounded-xl border border-slate-300 px-4 py-3 leading-7" name="meta_description"><?php echo e($settings['meta_description'] ?? ''); ?></textarea>
                        </label>
                        <label class="block text-sm font-black text-slate-600 lg:col-span-2">Copyright
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="copyright" value="<?php echo e($settings['copyright'] ?? ''); ?>">
                        </label>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm lg:p-7" data-settings-panel="smtp">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-blue-700">SMTP Configuration</p>
                    <div class="mt-5 grid gap-4 lg:grid-cols-3">
                        <label class="block text-sm font-black text-slate-600">SMTP Host
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="smtp_host" value="<?php echo e($settings['smtp']['host'] ?? ''); ?>">
                        </label>
                        <label class="block text-sm font-black text-slate-600">Port
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="smtp_port" value="<?php echo e($settings['smtp']['port'] ?? '587'); ?>">
                        </label>
                        <label class="block text-sm font-black text-slate-600">Encryption
                            <select class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="smtp_encryption">
                                <?php foreach (['tls', 'ssl', 'none'] as $option): ?>
                                    <option value="<?php echo e($option); ?>" <?php echo ($settings['smtp']['encryption'] ?? 'tls') === $option ? 'selected' : ''; ?>><?php echo e(strtoupper($option)); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="block text-sm font-black text-slate-600">Username
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="smtp_username" value="<?php echo e($settings['smtp']['username'] ?? ''); ?>">
                        </label>
                        <label class="block text-sm font-black text-slate-600">Password
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" type="password" name="smtp_password" value="<?php echo e($settings['smtp']['password'] ?? ''); ?>">
                        </label>
                        <label class="block text-sm font-black text-slate-600">From Email
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" type="email" name="smtp_from_email" value="<?php echo e($settings['smtp']['from_email'] ?? ''); ?>">
                        </label>
                        <label class="block text-sm font-black text-slate-600 lg:col-span-3">From Name
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="smtp_from_name" value="<?php echo e($settings['smtp']['from_name'] ?? ''); ?>">
                        </label>
                    </div>
                </section>

                <div class="flex flex-wrap items-center justify-end gap-3">
                    <div class="hidden items-center gap-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-black text-blue-800" role="status" data-upload-status>
                        <i class="fa-solid fa-circle-notch animate-spin"></i>
                        <span>Uploading and saving… Please keep this page open.</span>
                    </div>
                    <button class="inline-flex items-center rounded-xl bg-blue-700 px-6 py-3 font-black text-white shadow-lg shadow-blue-700/20 hover:bg-slate-950 disabled:cursor-wait disabled:opacity-60" type="submit" data-settings-submit>
                        <i class="fa-solid fa-floppy-disk mr-2" data-settings-submit-icon></i><span data-settings-submit-text>Save Site Settings</span>
                    </button>
                </div>
            </form>
        </main>
        <?php require __DIR__ . '/footer.php'; ?>
    </div>
</div>
<script>
    (() => {
        const root = document.querySelector('[data-settings-tabs]');
        if (!root) return;

        const tabs = Array.from(root.querySelectorAll('[data-settings-tab]'));
        const panels = Array.from(root.querySelectorAll('[data-settings-panel]'));
        const activeTabInput = root.querySelector('[data-active-settings-tab]');
        const activeClasses = ['bg-blue-700', 'text-white', 'shadow-sm'];
        const inactiveClasses = ['text-slate-600', 'hover:bg-slate-100'];

        const activate = (key) => {
            tabs.forEach(tab => {
                const active = tab.dataset.settingsTab === key;
                tab.classList.toggle('bg-blue-700', active);
                tab.classList.toggle('text-white', active);
                tab.classList.toggle('shadow-sm', active);
                tab.classList.toggle('text-slate-600', !active);
                tab.classList.toggle('hover:bg-slate-100', !active);
            });

            panels.forEach(panel => {
                panel.classList.toggle('hidden', panel.dataset.settingsPanel !== key);
            });

            if (activeTabInput) activeTabInput.value = key;
            window.localStorage.setItem('siteSettingsTab', key);
        };

        tabs.forEach(tab => {
            tab.addEventListener('click', () => activate(tab.dataset.settingsTab));
        });

        root.querySelectorAll('[data-button-row]').forEach(row => {
            const label = row.querySelector('[data-button-label]');
            const urlField = row.querySelector('[data-button-url-field]');
            const documentField = row.querySelector('[data-button-document-field]');
            const urlInput = urlField?.querySelector('input[name="button_url[]"]');
            const documentPathInput = row.querySelector('[data-document-path-input]');

            const syncButtonFields = () => {
                const isProfile = label?.value.trim().toLowerCase() === 'download company profile';
                urlField?.classList.toggle('hidden', isProfile);
                documentField?.classList.toggle('hidden', !isProfile);
                if (urlInput) urlInput.disabled = isProfile;
                if (documentPathInput) documentPathInput.disabled = !isProfile;
            };

            label?.addEventListener('input', syncButtonFields);
            syncButtonFields();
        });

        root.querySelectorAll('[data-profile-upload]').forEach(input => {
            const fileName = input.closest('[data-button-document-field]')?.querySelector('[data-profile-upload-name]');
            input.addEventListener('change', () => {
                const file = input.files?.[0];
                if (!fileName) return;
                fileName.textContent = file ? `${file.name} · ${(file.size / 1024 / 1024).toFixed(1)} MB selected` : '';
                fileName.classList.toggle('hidden', !file);
            });
        });

        root.addEventListener('submit', event => {
            const selectedFile = Array.from(root.querySelectorAll('[data-profile-upload]'))
                .map(input => input.files?.[0])
                .find(Boolean);

            if (selectedFile && selectedFile.size > 100 * 1024 * 1024) {
                event.preventDefault();
                window.alert('The company profile file must be 100 MB or less.');
                return;
            }

            const submitButton = root.querySelector('[data-settings-submit]');
            const submitText = root.querySelector('[data-settings-submit-text]');
            const submitIcon = root.querySelector('[data-settings-submit-icon]');
            const uploadStatus = root.querySelector('[data-upload-status]');
            if (submitButton) submitButton.disabled = true;
            if (submitText) submitText.textContent = selectedFile ? 'Uploading…' : 'Saving…';
            if (submitIcon) submitIcon.className = 'fa-solid fa-circle-notch mr-2 animate-spin';
            if (uploadStatus) {
                uploadStatus.classList.remove('hidden');
                uploadStatus.classList.add('inline-flex');
            }
        });

        const saved = window.localStorage.getItem('siteSettingsTab');
        const requested = new URLSearchParams(window.location.search).get('tab');
        const first = tabs[0]?.dataset.settingsTab || 'identity';
        const initial = tabs.some(tab => tab.dataset.settingsTab === requested)
            ? requested
            : (tabs.some(tab => tab.dataset.settingsTab === saved) ? saved : first);
        activate(initial);
    })();
</script>
</body>
</html>
