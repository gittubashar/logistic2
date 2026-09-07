<?php
require_once __DIR__ . '/../includes/config.php';

$contactError = '';
$contactSuccess = (string) ($_SESSION['contact_success'] ?? '');
unset($_SESSION['contact_success']);
$contactForm = [
    'name' => '',
    'email' => '',
    'service' => '',
    'message' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    foreach (array_keys($contactForm) as $field) {
        $contactForm[$field] = trim((string) ($_POST[$field] ?? ''));
    }

    $humanAnswer = trim((string) ($_POST['human_answer'] ?? ''));
    $honeypot = trim((string) ($_POST['website'] ?? ''));

    if ($honeypot !== '') {
        $_SESSION['contact_success'] = 'Thank you. Your message has been received.';
        header('Location: ' . base_url('pages/contact.php'));
        exit;
    }

    if ($contactForm['name'] === '' || mb_strlen($contactForm['name']) > 190) {
        $contactError = 'Please enter your name.';
    } elseif (!filter_var($contactForm['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($contactForm['email']) > 190) {
        $contactError = 'Please enter a valid email address.';
    } elseif (mb_strlen($contactForm['service']) > 190) {
        $contactError = 'The service name is too long.';
    } elseif ($contactForm['message'] === '' || mb_strlen($contactForm['message']) > 10000) {
        $contactError = 'Please enter a message of up to 10,000 characters.';
    } elseif ($humanAnswer !== '16') {
        $contactError = 'The human check answer is incorrect.';
    } else {
        $messageId = contact_message_create($contactForm + [
            'request_ip' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
        ]);

        if ($messageId > 0) {
            $_SESSION['contact_success'] = 'Thank you. Your message has been sent successfully.';
            header('Location: ' . base_url('pages/contact.php'));
            exit;
        }

        $contactError = 'Your message could not be saved. Please try again later.';
    }
}

$page = page_content('contact');
$pageTitle = page_browser_title($page);
require_once __DIR__ . '/../includes/header.php';
$pageHeaderKicker = $page['header_kicker'];
$pageHeaderTitle = $page['header_title'];
$pageHeaderText = $page['header_text'];
$pageHeaderImage = $page['header_image'];
require __DIR__ . '/../includes/page-header.php';
render_page_content_block($page);

function contact_office_map_url(array $office): string
{
    $embedCode = (string) ($office['map_embed_code'] ?? '');
    if ($embedCode !== '' && preg_match('/src=["\']([^"\']+)["\']/i', $embedCode, $matches)) {
        return $matches[1];
    }
    if ($embedCode !== '' && preg_match('#^https?://#i', trim($embedCode))) {
        return trim($embedCode);
    }

    $address = trim((string) ($office['address'] ?? ''));

    return 'https://www.google.com/maps?q=' . rawurlencode($address) . '&z=17&output=embed';
}

function contact_phone_href(string $phone): string
{
    return preg_replace('/[^\d+]/', '', $phone);
}
?>
<section class="bg-[#f4f5f2] py-16 lg:py-24">
    <div class="home-shell">
        <div class="grid gap-8 lg:grid-cols-[.72fr_1.28fr] lg:items-start">
            <aside class="compact-contact-info border-b border-slate-300 pb-7 lg:border-b-0 lg:border-r lg:pb-0 lg:pr-10">
                <div>
                    <span class="grid h-10 w-10 place-items-center rounded-full bg-amber-400 text-sm text-[#071426]"><i class="fa-solid fa-message"></i></span>
                    <p class="mt-5 text-[11px] font-bold uppercase tracking-[.2em] text-amber-700">Talk to operations</p>
                    <h2 class="mt-4 text-3xl font-extrabold leading-tight tracking-[-.04em] text-white">Let’s map your next shipment.</h2>
                    <p class="mt-4 text-sm leading-7 text-slate-600">Share the cargo, route and timing. Our team will respond with the right next step.</p>
                </div>
                <div class="mt-7 grid gap-4 border-t border-slate-300 pt-6">
                    <a class="flex items-center gap-4 text-sm font-semibold text-slate-300 hover:text-amber-300" href="tel:<?php echo e(contact_phone_href($site['phone'])); ?>"><span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-white/[.06] text-amber-300"><i class="fa-solid fa-phone"></i></span><?php echo e($site['phone']); ?></a>
                    <a class="flex min-w-0 items-center gap-4 text-sm font-semibold text-slate-300 hover:text-amber-300" href="mailto:<?php echo e($site['email']); ?>"><span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-white/[.06] text-amber-300"><i class="fa-solid fa-envelope"></i></span><span class="truncate"><?php echo e($site['email']); ?></span></a>
                    <a class="flex items-center gap-4 text-sm font-semibold text-slate-300 hover:text-amber-300" href="https://wa.me/<?php echo e(preg_replace('/\D+/', '', $site['whatsapp'])); ?>" target="_blank" rel="noopener"><span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-white/[.06] text-amber-300"><i class="fa-brands fa-whatsapp"></i></span><?php echo e($site['whatsapp']); ?></a>
                </div>
            </aside>

            <div class="border-b border-slate-300 pb-8 lg:pb-10">
                <p class="home-eyebrow">Send an enquiry</p>
                <h2 class="mt-5 text-3xl font-extrabold tracking-[-.04em] text-[#071426] sm:text-4xl">Tell us what you need.</h2>
                <p class="mt-4 text-sm leading-7 text-slate-600">Complete the form and our logistics desk will get back to you.</p>
            <?php if ($contactSuccess): ?>
                <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800" role="status">
                    <i class="fa-solid fa-circle-check mr-2"></i><?php echo e($contactSuccess); ?>
                </div>
            <?php endif; ?>
            <?php if ($contactError): ?>
                <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700" role="alert">
                    <i class="fa-solid fa-circle-exclamation mr-2"></i><?php echo e($contactError); ?>
                </div>
            <?php endif; ?>
            <form class="mt-8 grid gap-5 sm:grid-cols-2" method="post">
                <?php echo csrf_field(); ?>
                <div class="absolute -left-[9999px]" aria-hidden="true">
                    <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>
                <label class="grid gap-2 text-xs font-bold uppercase tracking-[.12em] text-slate-500">Your name
                    <input class="min-h-[52px] rounded-xl border border-slate-300 bg-[#f8f9f7] px-4 text-base font-normal normal-case tracking-normal text-slate-800 outline-none transition focus:border-amber-500 focus:bg-white" type="text" name="name" value="<?php echo e($contactForm['name']); ?>" maxlength="190" autocomplete="name" required>
                </label>
                <label class="grid gap-2 text-xs font-bold uppercase tracking-[.12em] text-slate-500">Email address
                    <input class="min-h-[52px] rounded-xl border border-slate-300 bg-[#f8f9f7] px-4 text-base font-normal normal-case tracking-normal text-slate-800 outline-none transition focus:border-amber-500 focus:bg-white" type="email" name="email" value="<?php echo e($contactForm['email']); ?>" maxlength="190" autocomplete="email" required>
                </label>
                <label class="grid gap-2 text-xs font-bold uppercase tracking-[.12em] text-slate-500 sm:col-span-2">Service required
                    <input class="min-h-[52px] rounded-xl border border-slate-300 bg-[#f8f9f7] px-4 text-base font-normal normal-case tracking-normal text-slate-800 outline-none transition focus:border-amber-500 focus:bg-white" type="text" name="service" value="<?php echo e($contactForm['service']); ?>" maxlength="190" placeholder="For example: Sea Freight">
                </label>
                <label class="grid gap-2 text-xs font-bold uppercase tracking-[.12em] text-slate-500 sm:col-span-2">Message
                    <textarea class="min-h-36 rounded-xl border border-slate-300 bg-[#f8f9f7] px-4 py-3 text-base font-normal normal-case leading-7 tracking-normal text-slate-800 outline-none transition focus:border-amber-500 focus:bg-white" name="message" maxlength="10000" required><?php echo e($contactForm['message']); ?></textarea>
                </label>
                <div class="grid gap-4 sm:col-span-2 sm:grid-cols-[1fr_auto] sm:items-end">
                    <label class="grid gap-2 text-xs font-bold uppercase tracking-[.12em] text-slate-500">Human check: 7 + 9 = ?
                        <input class="min-h-[52px] w-full rounded-xl border border-slate-300 bg-[#f8f9f7] px-4 text-base font-normal normal-case tracking-normal text-slate-800 outline-none transition focus:border-amber-500 focus:bg-white" type="number" name="human_answer" inputmode="numeric" placeholder="Answer" required>
                    </label>
                    <button class="min-h-[52px] rounded-full bg-[#071426] px-7 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-[#102845]" type="submit">
                        Send message <i class="fa-solid fa-arrow-right ml-2 text-xs text-amber-300"></i>
                    </button>
                </div>
            </form>
            </div>
        </div>

        <div class="mt-8 grid gap-5 lg:grid-cols-2">
            <?php foreach (($officeContacts ?? []) as $office): ?>
                <?php if (!($office['visible'] ?? true)) continue; ?>
                <?php $mapUrl = contact_office_map_url($office); ?>
                <article class="overflow-hidden border-y border-slate-300 sm:grid sm:grid-cols-[.9fr_1.1fr]">
                    <div class="min-h-72 overflow-hidden bg-slate-100 sm:min-h-full">
                        <iframe class="h-full min-h-72 w-full grayscale-[.25]" src="<?php echo e($mapUrl); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="<?php echo e(($office['title'] ?? 'Office') . ' location'); ?>"></iframe>
                    </div>
                    <address class="p-6 not-italic sm:p-8">
                        <p class="text-[11px] font-bold uppercase tracking-[.18em] text-amber-700">Office location</p>
                        <h2 class="mt-3 text-2xl font-extrabold text-[#071426]"><?php echo e($office['title'] ?? 'Office'); ?></h2>
                        <p class="mt-4 text-sm leading-7 text-slate-600"><?php echo e($office['address'] ?? ''); ?></p>
                        <div class="mt-6 grid gap-2 text-sm font-semibold">
                            <?php foreach (['phone_1' => 'fa-phone', 'phone_2' => 'fa-mobile-screen', 'phone_3' => 'fa-phone-volume'] as $field => $icon): ?>
                                <?php if (!empty($office[$field])): ?><a class="inline-flex items-center gap-3 text-slate-600 hover:text-amber-700" href="tel:<?php echo e(contact_phone_href($office[$field])); ?>"><i class="fa-solid <?php echo e($icon); ?> w-4 text-amber-500"></i><?php echo e($office[$field]); ?></a><?php endif; ?>
                            <?php endforeach; ?>
                            <?php foreach (['email', 'email_2', 'email_3'] as $field): ?>
                                <?php if (!empty($office[$field])): ?><a class="inline-flex min-w-0 items-center gap-3 text-slate-600 hover:text-amber-700" href="mailto:<?php echo e($office[$field]); ?>"><i class="fa-solid fa-envelope w-4 shrink-0 text-amber-500"></i><span class="truncate"><?php echo e($office[$field]); ?></span></a><?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </address>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>
