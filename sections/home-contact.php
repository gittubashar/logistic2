<?php
require_once __DIR__ . '/../includes/config.php';

$visibleOffices = array_values(array_filter($officeContacts ?? [], static fn (array $office): bool => (bool) ($office['visible'] ?? true)));
$primaryOffice = $visibleOffices[0] ?? null;
$whatsappNumber = preg_replace('/\D+/', '', (string) ($site['whatsapp'] ?? ''));
?>
<section class="home-contact bg-[#f4f5f2] py-16 lg:py-20">
    <div class="home-shell">
        <div class="grid gap-8 border-y border-slate-300 py-10 lg:grid-cols-[1.2fr_.8fr] lg:gap-16 lg:py-12">
            <div>
                <p class="home-eyebrow">Start a conversation</p>
                <h2 class="mt-4 max-w-2xl text-3xl font-extrabold leading-tight tracking-[-.04em] text-[#071426] sm:text-4xl">Your next shipment deserves a clear plan.</h2>
                <p class="mt-4 max-w-xl text-sm leading-7 text-slate-600">Tell us what you are moving, where it needs to go and when. Our team will help identify the right logistics path.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a class="inline-flex min-h-11 items-center justify-center gap-3 rounded-full bg-[#071426] px-5 text-sm font-bold text-white transition hover:bg-[#102845]" href="<?php echo e(base_url('pages/contact.php')); ?>">Request a quote <i class="fa-solid fa-arrow-right text-xs"></i></a>
                    <?php if ($whatsappNumber): ?><a class="inline-flex min-h-11 items-center justify-center gap-2 rounded-full border border-slate-300 px-5 text-sm font-bold text-[#071426] transition hover:border-amber-400 hover:bg-amber-100" href="https://wa.me/<?php echo e($whatsappNumber); ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp text-lg text-emerald-600"></i> WhatsApp</a><?php endif; ?>
                </div>
            </div>

            <div class="grid gap-5 border-t border-slate-300 pt-7 lg:border-l lg:border-t-0 lg:pl-10 lg:pt-0">
                <a class="group flex items-start gap-3" href="tel:<?php echo e(preg_replace('/[^\d+]/', '', (string) $site['phone'])); ?>"><i class="fa-solid fa-phone mt-1 text-sm text-amber-700"></i><span><small class="block text-[11px] font-bold uppercase tracking-[.14em] text-slate-500">Call our team</small><strong class="mt-1 block text-sm text-[#071426]"><?php echo e($site['phone']); ?></strong></span></a>
                <a class="group flex items-start gap-3" href="mailto:<?php echo e($site['email']); ?>"><i class="fa-solid fa-envelope mt-1 text-sm text-amber-700"></i><span><small class="block text-[11px] font-bold uppercase tracking-[.14em] text-slate-500">Email</small><strong class="mt-1 block text-sm text-[#071426]"><?php echo e($site['email']); ?></strong></span></a>
                <?php if ($primaryOffice): ?><div class="flex items-start gap-3"><i class="fa-solid fa-location-dot mt-1 text-sm text-amber-700"></i><span><small class="block text-[11px] font-bold uppercase tracking-[.14em] text-slate-500"><?php echo e($primaryOffice['title'] ?? 'Head Office'); ?></small><strong class="mt-1 block text-sm font-semibold leading-6 text-[#071426]"><?php echo e($primaryOffice['address'] ?? ''); ?></strong></span></div><?php endif; ?>
            </div>
        </div>
    </div>
</section>
