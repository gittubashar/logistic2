<?php
require_once __DIR__ . '/../includes/config.php';

$section = section_content('about');
$legacySection = section_content('company_profile');
$heroSection = section_content('hero');
$profileDocument = $section['profile_document'] ?? ($legacySection['profile_document'] ?? '');
$profileLabel = $section['profile_document_label'] ?? ($legacySection['profile_document_label'] ?? 'Download Company Profile');
$legacyCredentialPattern = '/(?:International\s+Freight\s+Forwarding\s+Agent|Bangladesh\s+Customs\s+Shipping\s+Agent|Clearing\s*(?:&|and)\s*Forwarding\s+Agent|Govt\.?\s+First\s+Class\s+Contractor)/i';
$highlights = array_values(array_filter(
    $heroSection['highlights'] ?? ($legacySection['items'] ?? []),
    static function ($item) use ($legacyCredentialPattern): bool {
        return trim(strip_tags((string) $item)) !== ''
            && !preg_match($legacyCredentialPattern, strip_tags((string) $item));
    }
));
if (!$highlights) {
    $highlights = [
        'Port Agency & Vessel Husbandry',
        'Crew Management & Repatriation',
        'Customs Brokerage / C&F Agent',
        'Stevedoring & Cargo Supervision',
    ];
}

if (($section['visible'] ?? true)):
?>
<section class="bg-[#071426] py-16 text-white lg:py-20">
    <div class="home-shell">
        <div class="grid gap-10 lg:grid-cols-[.85fr_1.15fr] lg:items-center">
            <div>
                <p class="home-eyebrow home-eyebrow--light">Company credentials</p>
                <h2 class="mt-5 max-w-xl text-3xl font-extrabold leading-tight tracking-[-.04em] text-white sm:text-4xl">The documents behind dependable delivery.</h2>
                <p class="mt-5 max-w-xl text-sm leading-7 text-slate-400">See the capabilities, memberships and operating credentials that support our freight and customs work.</p>
                <?php if ($profileDocument): ?>
                    <a class="mt-8 inline-flex min-h-12 items-center justify-center gap-3 rounded-full bg-amber-400 px-6 text-sm font-bold text-[#071426] transition hover:bg-amber-300" href="<?php echo e(base_url($profileDocument)); ?>" target="_blank" download><i class="fa-solid fa-file-arrow-down"></i><?php echo e($profileLabel); ?></a>
                <?php endif; ?>
            </div>
            <?php if ($highlights): ?>
                <div class="grid gap-px overflow-hidden rounded-3xl border border-white/10 bg-white/10 sm:grid-cols-2">
                    <?php foreach ($highlights as $index => $highlight): ?>
                        <div class="bg-[#0b1c32] p-6 sm:p-7">
                            <span class="font-mono text-xs font-bold tracking-[.16em] text-amber-300">0<?php echo e((string) ($index + 1)); ?></span>
                            <p class="mt-6 font-bold leading-7 text-white"><?php echo section_rich_text((string) $highlight); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>
