<?php
require_once __DIR__ . '/../includes/config.php';

$section = section_content('hero');
$aboutSection = section_content('about');
$companyProfileSection = section_content('company_profile');

if (($section['visible'] ?? true)):
    $heroImage = (string) ($section['image'] ?? '');
    if ($heroImage === '' || str_ends_with($heroImage, 'hero-bg.svg')) {
        $heroImage = 'uploads/gallery/20260712194416-c92c5107.jpg';
    }

    $heroButtons = $section['buttons'] ?? [];
    $serviceCount = count($services);
    $officeCount = count(array_filter(
        $officeContacts ?? [],
        static fn (array $office): bool => (bool) ($office['visible'] ?? true)
    ));
?>
<section class="home-hero relative bg-[#071426] text-white">
    <div class="grid min-h-[690px] lg:grid-cols-[.95fr_1.05fr]">
        <div class="relative z-10 flex items-center overflow-hidden px-4 py-16 sm:px-8 lg:px-10 lg:py-24 xl:pl-[max(4rem,calc((100vw-1440px)/2))] xl:pr-16">
            <div class="pointer-events-none absolute -left-40 top-12 h-96 w-96 rounded-full border border-white/[.06]" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -left-20 top-32 h-64 w-64 rounded-full border border-white/[.06]" aria-hidden="true"></div>
            <div class="home-reveal relative max-w-3xl">
                <p class="home-eyebrow home-eyebrow--light"><?php echo e($section['kicker'] ?? $site['since']); ?> · Bangladesh</p>
                <h1 class="mt-6 text-[clamp(2.8rem,6vw,6.6rem)] font-extrabold leading-[.96] tracking-[-.065em] text-white">
                    M/S B. S. <span class="text-amber-300">TRADING</span>
                </h1>
                <p class="mt-7 max-w-2xl text-base font-medium leading-8 text-slate-300 sm:text-lg"><?php echo section_rich_text((string) ($section['subtitle'] ?? $site['tagline'])); ?></p>

                <div class="mt-9 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <?php foreach (array_slice($heroButtons, 0, 3) as $index => $button): ?>
                        <?php
                        $buttonUrl = $button['url'] ?? '#';
                        if (($button['label'] ?? '') === 'Download Company Profile') {
                            $buttonUrl = $aboutSection['profile_document'] ?: ($companyProfileSection['profile_document'] ?? $buttonUrl);
                        }
                        $buttonClasses = $index === 0
                            ? 'bg-amber-400 text-[#071426] hover:bg-amber-300'
                            : 'border border-white/20 bg-white/[.06] text-white hover:border-white/40 hover:bg-white/10';
                        ?>
                        <a class="inline-flex min-h-[52px] items-center justify-center gap-3 rounded-full px-6 py-3.5 text-sm font-bold transition hover:-translate-y-0.5 <?php echo e($buttonClasses); ?>" href="<?php echo e(base_url($buttonUrl)); ?>"<?php echo str_contains((string) ($button['label'] ?? ''), 'Profile') ? ' target="_blank" rel="noopener"' : ''; ?>>
                            <?php echo e($button['label'] ?? 'Learn More'); ?>
                            <i class="fa-solid <?php echo $index === 0 ? 'fa-arrow-up-right-from-square' : 'fa-arrow-right'; ?> text-xs"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="home-reveal home-reveal-delay relative min-h-[460px] overflow-hidden lg:min-h-full">
            <img class="absolute inset-0 h-full w-full object-cover" src="<?php echo e(base_url($heroImage)); ?>" alt="Container terminal and freight operations" fetchpriority="high">
            <div class="absolute inset-0 bg-gradient-to-b from-[#071426]/10 via-transparent to-[#071426]/70 lg:bg-gradient-to-r lg:from-[#071426] lg:via-[#071426]/10 lg:to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 p-5 sm:p-8 lg:p-10">
                <div class="ml-auto max-w-sm rounded-2xl border border-white/15 bg-[#071426]/80 p-5 backdrop-blur-md">
                    <div class="flex items-center gap-3">
                        <span class="relative flex h-3 w-3">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-300 opacity-60"></span>
                            <span class="relative inline-flex h-3 w-3 rounded-full bg-amber-300"></span>
                        </span>
                        <span class="text-xs font-bold uppercase tracking-[.18em] text-amber-200">Operations connected</span>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-slate-200">24/7/365 support for shipowners, charterers and cargo operators throughout the Bay of Bengal.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="relative z-20 border-t border-white/10 bg-white text-[#071426]">
        <div class="home-shell grid grid-cols-2 divide-x divide-slate-200 sm:grid-cols-4">
            <div class="px-4 py-6 sm:px-6 lg:py-8">
                <strong class="block text-2xl font-extrabold sm:text-3xl">2018</strong>
                <span class="mt-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Established</span>
            </div>
            <div class="px-4 py-6 sm:px-6 lg:py-8">
                <strong class="block text-2xl font-extrabold sm:text-3xl"><?php echo e((string) $serviceCount); ?></strong>
                <span class="mt-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Core services</span>
            </div>
            <div class="border-t border-slate-200 px-4 py-6 sm:border-t-0 sm:px-6 lg:py-8">
                <strong class="block text-2xl font-extrabold sm:text-3xl"><?php echo e((string) $officeCount); ?></strong>
                <span class="mt-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Bangladesh offices</span>
            </div>
            <div class="border-t border-slate-200 px-4 py-6 sm:border-t-0 sm:px-6 lg:py-8">
                <strong class="block text-2xl font-extrabold sm:text-3xl">360°</strong>
                <span class="mt-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Cargo support</span>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
