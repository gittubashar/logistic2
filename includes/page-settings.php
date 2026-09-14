<?php

function page_settings_file(): string
{
    return __DIR__ . '/../uploads/page-settings.json';
}

function page_defaults(): array
{
    global $services;

    $pages = [
        'about' => [
            'label' => 'About Page',
            'path' => 'pages/about.php',
            'title' => 'About - M/S B. S. Trading',
            'header_kicker' => 'About M/S B. S. Trading',
            'header_title' => 'Your trusted maritime anchor in Bangladesh',
            'header_text' => 'A premier, fully integrated shipping agency based in Chattogram, Bangladesh, established in 2018.',
            'header_image' => 'uploads/page-header-bg.svg',
            'content' => [],
            'content_html' => '',
        ],
        'team' => [
            'label' => 'Team Page',
            'path' => 'pages/team.php',
            'title' => 'Team - M/S B. S. Trading',
            'header_kicker' => 'Leadership & Team',
            'header_title' => 'Professional maritime leadership',
            'header_text' => 'Meet the team supporting port agency, maritime logistics and cargo operations.',
            'header_image' => 'uploads/page-header-bg.svg',
            'content' => [],
            'content_html' => '',
        ],
        'contact' => [
            'label' => 'Contact Page',
            'path' => 'pages/contact.php',
            'title' => 'Contact - M/S B. S. Trading',
            'header_kicker' => 'Contact',
            'header_title' => 'Contact Us',
            'header_text' => 'Reach our Chattogram operations desk for port agency, crew, customs and cargo support.',
            'header_image' => 'uploads/page-header-bg.svg',
            'map_address' => '1200 Haji Sobhan Soudagar Road, Chaktai, Chattogram, Bangladesh.',
            'map_embed_code' => '',
            'content' => [],
            'content_html' => '',
        ],
        'services' => [
            'label' => 'Services Page',
            'path' => 'services.php',
            'title' => 'Services - M/S B. S. Trading',
            'header_kicker' => 'Services',
            'header_title' => 'Core maritime services',
            'header_text' => 'Port agency, vessel husbandry, crew management, customs, inland logistics and cargo supervision.',
            'header_image' => 'uploads/page-header-bg.svg',
            'content' => [],
            'content_html' => '',
        ],
        'membership_certificates' => [
            'label' => 'Membership & Certificates Page',
            'path' => 'pages/membership-certificates.php',
            'title' => 'Membership & Certificates - M/S B. S. Trading',
            'header_kicker' => 'Credentials',
            'header_title' => 'Membership & Certificates',
            'header_text' => 'Licenses and affiliations supporting M/S B. S. TRADING shipping agency and customs operations.',
            'header_image' => 'uploads/page-header-bg.svg',
            'content' => [],
            'content_html' => '',
        ],
        'blog' => [
            'label' => 'Blog Page',
            'path' => 'blog.php',
            'title' => 'Blog - M/S B. S. Trading',
            'header_kicker' => 'Blog',
            'header_title' => 'Maritime logistics insights',
            'header_text' => 'Practical notes from the operations desk at M/S B. S. Trading.',
            'header_image' => 'uploads/page-header-bg.svg',
            'content' => [],
            'content_html' => '',
        ],
        'our_concern' => [
            'label' => 'Our Concern Page',
            'path' => 'pages/our-concern.php',
            'title' => 'Our Concern - M/S B. S. Trading',
            'header_kicker' => 'Our Concern',
            'header_title' => 'Our Concern',
            'header_text' => 'The businesses and concerns associated with M/S B. S. TRADING.',
            'header_image' => 'uploads/page-header-bg.svg',
            'content' => [],
            'content_html' => '<ul><li>M/S Bandarban Agency</li><li>Purabi Chair Coach</li><li>M/S Purabi Rice Agency</li><li>Purabi Transport Agency</li><li>M/S Bandarban Auto Rice Mill</li><li>Hotel Hill Bird</li><li>M/S Sukhendu Bikash Das</li><li>Hotel Purabi</li><li>M/S Sharothi Enterprise</li><li>Hotel Hill View</li></ul>',
        ],
        'international_freight_forwarding_agent' => [
            'label' => 'Licensed Customs Shipping Agent Page',
            'path' => 'pages/international-freight-forwarding-agent.php',
            'title' => 'Licensed Customs Shipping Agent - M/S B. S. Trading',
            'header_kicker' => 'Licensed & Memberships',
            'header_title' => 'Licensed Customs Shipping Agent',
            'header_text' => 'Authorized to provide shipping agency services across Bangladesh.',
            'header_image' => 'uploads/page-header-bg.svg',
            'content' => [
                'M/S B. S. TRADING is a licensed customs shipping agent authorized to provide shipping agency services across Bangladesh.',
                'Our Chattogram-based team supports vessel calls, port coordination, cargo documentation and operational follow-up with local expertise and regulatory discipline.',
                'We keep principals informed through clear communication, accountable records and responsive service from instruction to completion.',
            ],
            'content_html' => '',
        ],
        'bangladesh_customs_shipping_agent' => [
            'label' => 'Bangladesh Customs Shipping Agent Page',
            'path' => 'pages/bangladesh-customs-shipping-agent.php',
            'title' => 'Bangladesh Shipping Agent Association - M/S B. S. Trading',
            'header_kicker' => 'Licensed & Memberships',
            'header_title' => 'Bangladesh Shipping Agent Association (BSSA)',
            'header_text' => 'Member of the Bangladesh Shipping Agent Association, supporting professional maritime operations.',
            'header_image' => 'uploads/page-header-bg.svg',
            'content' => [
                'M/S B. S. TRADING is a member of the Bangladesh Shipping Agent Association (BSSA).',
                'The association membership reflects our commitment to professional standards, responsible representation and constructive participation in Bangladesh maritime trade.',
                'We pair this industry connection with practical Chattogram port knowledge, responsive principal communication and transparent operational follow-up.',
            ],
            'content_html' => '',
        ],
        'clearing_forwarding_agent' => [
            'label' => 'Clearing & Forwarding Agent Page',
            'path' => 'pages/clearing-forwarding-agent.php',
            'title' => 'Customs Brokerage / C&F Agent - M/S B. S. Trading',
            'header_kicker' => 'Core Credential',
            'header_title' => 'Customs Brokerage / C&F Agent',
            'header_text' => 'Customs brokerage and clearing & forwarding support for compliant cargo movement.',
            'header_image' => 'uploads/page-header-bg.svg',
            'content' => [
                'M/S B. S. TRADING provides customs brokerage and clearing & forwarding support for businesses that need dependable documentation and cargo release coordination.',
                'Our specialists support HS classification, duty and tax calculation, Bills of Entry, Bills of Export, inspections and mandatory regulatory certificates.',
                'We work with regulatory awareness and transparent communication to reduce administrative delays and support friction-free clearance.',
            ],
            'content_html' => '',
        ],
        'govt_first_class_contractor' => [
            'label' => 'Core Values Page',
            'path' => 'pages/govt-first-class-contractor.php',
            'title' => 'Our Core Values - M/S B. S. Trading',
            'header_kicker' => 'Our Core Values',
            'header_title' => 'Integrity, efficiency, safety and client-centricity',
            'header_text' => 'The principles guiding every port call, cargo movement and client relationship.',
            'header_image' => 'uploads/page-header-bg.svg',
            'content' => [
                'Integrity: We conduct every transaction with financial transparency, honesty and strict anti-bribery compliance.',
                'Efficiency: We treat time as a critical resource and engineer workflows to minimize off-hire time and port stays.',
                'Client-Centricity and Safety First: Every solution reflects the principal’s operational needs while protecting maritime safety, the environment and crew welfare.',
            ],
            'content_html' => '',
        ],
    ];

    foreach ($services as $service) {
        $key = 'service_' . str_replace('-', '_', $service['slug']);
        $pages[$key] = [
            'label' => $service['title'] . ' Page',
            'path' => 'pages/' . $service['slug'] . '.php',
            'title' => $service['title'] . ' - M/S B. S. Trading',
            'header_kicker' => 'Service',
            'header_title' => $service['title'],
            'header_text' => $service['text'] ?? '',
            'header_image' => $service['image'] ?? 'uploads/page-header-bg.svg',
            'content' => $service['detail_paragraphs'] ?? [],
            'content_html' => '',
        ];
    }

    foreach ($pages as &$page) {
        $page['system_default'] = true;
    }
    unset($page);

    return $pages;
}

function saved_page_settings(): array
{
    $dbSettings = db_json_get('page_settings');
    if ($dbSettings) {
        return $dbSettings;
    }

    $file = page_settings_file();

    if (!is_file($file)) {
        return [];
    }

    $data = json_decode((string) file_get_contents($file), true);

    return is_array($data) ? $data : [];
}

function all_page_content(): array
{
    $saved = saved_page_settings();
    $customPages = $saved['custom_pages'] ?? [];
    unset($saved['custom_pages']);
    unset($saved['company_profile']);

    $defaults = page_defaults();
    $pages = section_merge($defaults, $saved);

    foreach (array_keys($defaults) as $key) {
        if (isset($pages[$key]) && is_array($pages[$key])) {
            $pages[$key]['system_default'] = true;
            $pages[$key]['custom'] = false;
        }
    }

    return $pages + $customPages;
}

function custom_pages(): array
{
    $saved = saved_page_settings();
    $customPages = $saved['custom_pages'] ?? [];

    return is_array($customPages) ? $customPages : [];
}

function page_is_custom(string $key, array $page = []): bool
{
    return !empty($page['custom']) || str_starts_with($key, 'custom_');
}

function page_type_label(string $key, array $page = []): string
{
    return page_is_custom($key, $page) ? 'Custom Page' : 'System Default';
}

function page_key_from_slug(string $slug): string
{
    $slug = strtolower(trim($slug));
    $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug) ?: '';
    $slug = trim($slug, '-');

    return 'custom_' . str_replace('-', '_', $slug);
}

function page_slug_from_key(string $key): string
{
    if (str_starts_with($key, 'custom_')) {
        return str_replace('_', '-', substr($key, 7));
    }

    return str_replace('_', '-', $key);
}

function page_content(string $key): array
{
    $pages = all_page_content();

    return $pages[$key] ?? [];
}

function page_browser_title(array $page): string
{
    global $site;

    $headerTitle = trim((string) ($page['header_title'] ?? $page['label'] ?? ''));
    $siteTitle = trim((string) ($site['title'] ?? ''));

    if ($headerTitle === '') {
        return $siteTitle;
    }

    if ($siteTitle === '') {
        return $headerTitle;
    }

    return $headerTitle . ' - ' . $siteTitle;
}

function save_page_settings(array $settings): bool
{
    if (db_json_save('page_settings', $settings)) {
        return true;
    }

    $file = page_settings_file();
    $directory = dirname($file);

    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    return file_put_contents($file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
}

function page_clean_html(string $html): string
{
    $allowedTags = '<div><p><br><strong><b><em><i><u><ul><ol><li><h2><h3><h4><blockquote><a><img>';

    return strip_tags($html, $allowedTags);
}

function render_page_content_block(array $page): void
{
    $paragraphs = $page['content'] ?? [];
    $contentHtml = page_clean_html((string) ($page['content_html'] ?? ''));

    if (!$paragraphs && $contentHtml === '') {
        return;
    }
    ?>
    <section class="bg-[#f4f5f2] py-16 lg:py-24">
        <div class="home-shell grid gap-8 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start">
            <article class="border-b border-slate-300 pb-10 sm:pb-12">
                <div class="space-y-6 text-base leading-8 text-slate-600 [&_a]:font-bold [&_a]:text-amber-700 [&_a]:underline [&_a]:decoration-amber-300 [&_a]:underline-offset-4 [&_blockquote]:rounded-r-2xl [&_blockquote]:border-l-4 [&_blockquote]:border-amber-400 [&_blockquote]:bg-amber-50 [&_blockquote]:px-6 [&_blockquote]:py-4 [&_h2]:pt-3 [&_h2]:text-3xl [&_h2]:font-extrabold [&_h2]:tracking-[-.035em] [&_h2]:text-[#071426] [&_h3]:pt-2 [&_h3]:text-2xl [&_h3]:font-bold [&_h3]:text-[#071426] [&_img]:my-8 [&_img]:w-full [&_img]:rounded-2xl [&_img]:border [&_img]:border-slate-200 [&_img]:object-cover [&_li]:ml-5 [&_li]:list-disc [&_li]:marker:text-amber-500 [&_ol_li]:list-decimal [&_p:first-child]:text-lg [&_p:first-child]:font-semibold [&_p:first-child]:leading-9 [&_p:first-child]:text-[#24364b]">
                    <?php if ($contentHtml !== ''): ?>
                        <?php echo $contentHtml; ?>
                    <?php elseif ($paragraphs): ?>
                        <?php foreach ($paragraphs as $paragraph): ?>
                            <p><?php echo e($paragraph); ?></p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </article>

            <aside class="border-t border-slate-300 pt-6 lg:sticky lg:top-32 lg:border-l lg:border-t-0 lg:pl-8">
                <span class="grid h-10 w-10 place-items-center rounded-full bg-amber-400 text-[#071426]"><i class="fa-solid fa-headset"></i></span>
                <h2 class="mt-5 text-xl font-extrabold tracking-[-.03em] text-[#071426]">Need expert support?</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">Talk to our team about freight, customs, transport or cargo handling requirements.</p>
                <a class="mt-6 inline-flex min-h-11 items-center justify-center gap-3 rounded-full bg-[#071426] px-5 text-sm font-bold text-white transition hover:bg-[#102845]" href="<?php echo e(base_url('pages/contact.php')); ?>">Request a quote <i class="fa-solid fa-arrow-right text-xs"></i></a>
                <div class="mt-6 border-t border-slate-300 pt-5">
                    <a class="flex items-center gap-3 text-sm font-semibold text-slate-600 transition hover:text-amber-700" href="tel:<?php echo e(preg_replace('/[^\d+]/', '', (string) ($GLOBALS['site']['phone'] ?? ''))); ?>"><i class="fa-solid fa-phone text-amber-700"></i><?php echo e($GLOBALS['site']['phone'] ?? ''); ?></a>
                </div>
            </aside>
        </div>
    </section>
    <?php
}
