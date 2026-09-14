<?php
require_once __DIR__ . '/config.php';
$pageTitle = $pageTitle ?? $site['title'];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo e($site['meta_description'] ?? $site['tagline']); ?>">
    <meta name="keywords" content="<?php echo e($site['meta_keywords'] ?? ''); ?>">
    <title><?php echo e($pageTitle); ?></title>
    <?php if (!empty($site['favicon'])): ?>
        <link rel="icon" href="<?php echo e(base_url($site['favicon'])); ?>">
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: '#07142f',
                        brand: '#1267b5',
                        aqua: '#f4bd27',
                        ink: '#0f172a'
                    },
                    boxShadow: {
                        soft: '0 20px 45px rgba(15, 23, 42, .10)'
                    }
                }
            }
        };
    </script>
    <style>
        :root {
            --home-navy: #071426;
            --home-navy-soft: #102845;
            --home-gold: #f4bd27;
            --home-paper: #f4f5f2;
            --home-ink: #122033;
        }

        html { scroll-behavior: smooth; }
        body { font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Manrope', ui-sans-serif, system-ui, sans-serif; }

        .home-shell {
            width: min(100% - 2rem, 1440px);
            margin-inline: auto;
        }

        .home-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .75rem;
            color: #9a6710;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .19em;
            line-height: 1.4;
            text-transform: uppercase;
        }

        .home-eyebrow::before {
            width: 2.25rem;
            height: 2px;
            background: var(--home-gold);
            content: '';
        }

        .home-eyebrow--light { color: #f7d77d; }

        .home-heading {
            color: var(--home-ink);
            font-size: clamp(2rem, 4.5vw, 4.5rem);
            font-weight: 800;
            letter-spacing: -.05em;
            line-height: 1.04;
        }

        .home-text-link {
            display: inline-flex;
            align-items: center;
            gap: .65rem;
            font-size: .82rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            transition: color .2s ease;
        }

        .site-header.is-scrolled {
            box-shadow: 0 14px 35px rgba(7, 20, 38, .10);
        }

        .home-page main { overflow: hidden; }

        .home-reveal {
            animation: homeReveal .75s cubic-bezier(.22, 1, .36, 1) both;
        }

        .home-reveal-delay { animation-delay: .12s; }

        @keyframes homeReveal {
            from { opacity: 0; transform: translateY(22px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (min-width: 1024px) {
            .home-shell { width: min(100% - 5rem, 1440px); }
        }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .home-reveal, .home-reveal-delay { animation: none; }
        }

        /* Compact editorial system: tighter rhythm, quieter surfaces, smaller type. */
        body.compact-site main h1 {
            font-size: clamp(2.1rem, 4.2vw, 4.2rem) !important;
            line-height: 1.02 !important;
        }

        body.compact-site main h2 {
            font-size: clamp(1.45rem, 2.7vw, 2.7rem) !important;
            line-height: 1.12 !important;
        }

        body.compact-site .home-heading {
            font-size: clamp(1.8rem, 3.5vw, 3.4rem) !important;
            letter-spacing: -.045em;
        }

        body.compact-site .home-hero > div:first-child {
            min-height: 560px !important;
        }

        body.compact-site .home-hero h1 {
            font-size: clamp(2.45rem, 5vw, 4.8rem) !important;
        }

        body.compact-site .compact-page-header .home-shell {
            min-height: 350px !important;
        }

        body.compact-site .compact-page-header nav {
            margin-bottom: 1.5rem !important;
        }

        body.compact-site .compact-contact-info h2 { color: var(--home-ink) !important; }
        body.compact-site .compact-contact-info p { color: #475569 !important; }
        body.compact-site .compact-contact-info a { color: #475569 !important; }
        body.compact-site .compact-contact-info a:hover { color: #a66b0a !important; }
        body.compact-site .compact-contact-info a span { background: #f8f9f7 !important; color: #a66b0a !important; }

        body.compact-site main .py-28,
        body.compact-site main .lg\:py-28,
        body.compact-site main .py-24,
        body.compact-site main .lg\:py-24 {
            padding-top: 4.5rem !important;
            padding-bottom: 4.5rem !important;
        }

        body.compact-site main .py-20,
        body.compact-site main .lg\:py-20 {
            padding-top: 4rem !important;
            padding-bottom: 4rem !important;
        }

        body.compact-site main [class*="rounded-3xl"],
        body.compact-site main [class*="rounded-[2rem]"],
        body.compact-site main [class*="rounded-2xl"] {
            border-radius: 1rem !important;
        }

        body.compact-site main [class*="shadow-["] {
            box-shadow: 0 10px 28px rgba(7, 20, 38, .05) !important;
        }

        body.compact-site .home-shell {
            width: min(100% - 2rem, 1180px);
        }

        @media (min-width: 1024px) {
            body.compact-site .home-shell { width: min(100% - 4rem, 1180px); }
        }
    </style>
</head>
<body class="compact-site <?php echo !empty($isHomePage) ? 'home-page ' : ''; ?>bg-slate-50 text-slate-700 antialiased">
<?php require __DIR__ . '/topbar.php'; ?>
<header class="site-header sticky top-0 z-50 border-b border-slate-200/80 bg-white/95 backdrop-blur-xl transition-shadow">
    <div class="mx-auto flex w-full max-w-[1480px] items-center justify-between gap-4 px-4 py-3 lg:px-8">
        <a class="flex items-center gap-3 text-ink" href="<?php echo e(base_url('index.php')); ?>">
            <?php if (!empty($site['logo_image'])): ?>
                <img class="h-12 w-auto max-w-[min(520px,58vw)] object-contain sm:h-14" src="<?php echo e(base_url($site['logo_image'])); ?>" alt="<?php echo e($site['title']); ?>">
            <?php else: ?>
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-navy text-base font-black text-amber-300 sm:h-12 sm:w-12 sm:text-lg"><?php echo e($site['logo_text']); ?></span>
                <span>
                    <strong class="block text-sm font-black uppercase tracking-wide sm:text-lg"><?php echo e($site['title']); ?></strong>
                    <small class="block text-[10px] font-bold uppercase tracking-[0.16em] text-amber-600 sm:text-xs sm:tracking-[0.2em]"><?php echo e($site['since']); ?></small>
                </span>
            <?php endif; ?>
        </a>
        <div class="ml-auto flex items-center justify-end gap-3 lg:gap-6">
            <nav class="hidden items-center justify-end gap-1 text-[13px] font-bold text-slate-600 lg:flex xl:text-sm">
                <a class="rounded-full px-3 py-2.5 transition hover:bg-slate-100 hover:text-navy" href="<?php echo e(base_url('index.php')); ?>">
                    Home
                </a>
                <a class="rounded-full px-3 py-2.5 transition hover:bg-slate-100 hover:text-navy" href="<?php echo e(base_url('pages/about.php')); ?>">
                    About
                </a>
                <a class="rounded-full px-3 py-2.5 transition hover:bg-slate-100 hover:text-navy" href="<?php echo e(base_url('pages/our-concern.php')); ?>">
                    Our Concern
                </a>
                <a class="rounded-full px-3 py-2.5 transition hover:bg-slate-100 hover:text-navy" href="<?php echo e(base_url('pages/membership-certificates.php')); ?>">
                    Credentials
                </a>
                <div class="group relative">
                    <a class="inline-flex items-center gap-2 rounded-full px-3 py-2.5 transition hover:bg-slate-100 hover:text-navy" href="<?php echo e(base_url('services.php')); ?>">
                        Services <i class="fa-solid fa-chevron-down text-[9px]"></i>
                    </a>
                    <div class="invisible absolute left-1/2 top-full z-50 w-72 -translate-x-1/2 pt-4 opacity-0 transition group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
                        <div class="grid gap-1 rounded-2xl border border-slate-200 bg-white p-3 text-sm shadow-soft">
                            <a class="flex items-center gap-3 rounded-xl px-3 py-3 text-slate-700 hover:bg-slate-100 hover:text-navy" href="<?php echo e(base_url('services.php')); ?>">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-amber-100 text-amber-700"><i class="fa-solid fa-layer-group"></i></span>
                                <span>All Services</span>
                            </a>
                            <?php foreach ($services as $navService): ?>
                                <a class="flex items-center gap-3 rounded-xl px-3 py-3 text-slate-700 hover:bg-slate-100 hover:text-navy" href="<?php echo e(base_url('pages/' . $navService['slug'] . '.php')); ?>">
                                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-amber-100 text-amber-700"><i class="fa-solid <?php echo e($navService['icon'] ?? 'fa-circle-dot'); ?>"></i></span>
                                    <span><?php echo e($navService['title']); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <a class="rounded-full px-3 py-2.5 transition hover:bg-slate-100 hover:text-navy" href="<?php echo e(base_url('pages/team.php')); ?>">
                    Team
                </a>
                <a class="rounded-full px-3 py-2.5 transition hover:bg-slate-100 hover:text-navy" href="<?php echo e(base_url('blog.php')); ?>">
                    Blog
                </a>
                <a class="rounded-full px-3 py-2.5 transition hover:bg-slate-100 hover:text-navy" href="<?php echo e(base_url('pages/contact.php')); ?>">
                    Contact
                </a>
            </nav>
            <a class="hidden items-center gap-3 rounded-full bg-navy px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-[#102845] md:inline-flex" href="<?php echo e(base_url('pages/contact.php')); ?>">
                Get a Quote <span class="grid h-6 w-6 place-items-center rounded-full bg-amber-400 text-[10px] text-navy"><i class="fa-solid fa-arrow-right"></i></span>
            </a>
            <button class="inline-grid h-11 w-11 place-items-center rounded-xl border border-slate-200 bg-white text-ink shadow-sm lg:hidden" type="button" data-mobile-menu-open aria-label="Open navigation">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
        </div>
    </div>
</header>
<div class="fixed inset-0 z-[80] hidden bg-navy/70 backdrop-blur-sm lg:hidden" data-mobile-menu>
    <div class="ml-auto flex h-full w-[min(88vw,360px)] flex-col bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <strong class="block text-sm font-black uppercase text-ink"><?php echo e($site['title']); ?></strong>
                <small class="text-xs font-bold uppercase tracking-[0.18em] text-amber-700"><?php echo e($site['since']); ?></small>
            </div>
            <button class="grid h-10 w-10 place-items-center rounded-xl bg-slate-100 text-ink" type="button" data-mobile-menu-close aria-label="Close navigation">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <nav class="grid gap-1 overflow-y-auto p-5 text-base font-black text-ink">
            <a class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-slate-100 hover:text-brand" href="<?php echo e(base_url('index.php')); ?>">
                <i class="fa-solid fa-house w-5 text-brand"></i>Home
            </a>
            <a class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-slate-100 hover:text-brand" href="<?php echo e(base_url('pages/about.php')); ?>">
                <i class="fa-solid fa-circle-info w-5 text-brand"></i>About
            </a>
            <a class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-slate-100 hover:text-brand" href="<?php echo e(base_url('pages/our-concern.php')); ?>">
                <i class="fa-solid fa-building-columns w-5 text-brand"></i>Our Concern
            </a>
            <a class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-slate-100 hover:text-brand" href="<?php echo e(base_url('pages/membership-certificates.php')); ?>">
                <i class="fa-solid fa-award w-5 text-brand"></i>Membership & Certificates
            </a>
            <div class="rounded-xl">
                <button class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left hover:bg-slate-100 hover:text-brand" type="button" data-mobile-submenu-toggle aria-expanded="false">
                    <i class="fa-solid fa-truck-fast w-5 text-brand"></i>
                    <span class="flex-1">Services</span>
                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition" data-mobile-submenu-icon></i>
                </button>
                <div class="hidden grid gap-1 border-l-2 border-amber-300/50 pl-4" data-mobile-submenu>
                    <a class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100 hover:text-brand" href="<?php echo e(base_url('services.php')); ?>">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-brand/10 text-brand"><i class="fa-solid fa-layer-group"></i></span>
                        <span>All Services</span>
                    </a>
                    <?php foreach ($services as $navService): ?>
                        <a class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100 hover:text-brand" href="<?php echo e(base_url('pages/' . $navService['slug'] . '.php')); ?>">
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-aqua/10 text-aqua"><i class="fa-solid <?php echo e($navService['icon'] ?? 'fa-circle-dot'); ?>"></i></span>
                            <span><?php echo e($navService['title']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <a class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-slate-100 hover:text-brand" href="<?php echo e(base_url('pages/team.php')); ?>">
                <i class="fa-solid fa-users w-5 text-brand"></i>Team
            </a>
            <a class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-slate-100 hover:text-brand" href="<?php echo e(base_url('blog.php')); ?>">
                <i class="fa-solid fa-newspaper w-5 text-brand"></i>Blog
            </a>
            <a class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-slate-100 hover:text-brand" href="<?php echo e(base_url('pages/contact.php')); ?>">
                <i class="fa-solid fa-envelope-open-text w-5 text-brand"></i>Contact
            </a>
        </nav>
        <div class="mt-auto border-t border-slate-200 p-5">
            <a class="flex w-full items-center justify-center gap-2 rounded-xl bg-navy px-5 py-3 font-black text-white shadow-soft hover:bg-[#102845]" href="<?php echo e(base_url('pages/contact.php')); ?>">
                <i class="fa-solid fa-paper-plane"></i>Get a Quote
            </a>
        </div>
    </div>
</div>
<script>
    (() => {
        const menu = document.querySelector('[data-mobile-menu]');
        const openButton = document.querySelector('[data-mobile-menu-open]');
        const closeButton = document.querySelector('[data-mobile-menu-close]');
        const submenuToggle = document.querySelector('[data-mobile-submenu-toggle]');
        const submenu = document.querySelector('[data-mobile-submenu]');
        const submenuIcon = document.querySelector('[data-mobile-submenu-icon]');
        const stickyHeader = document.querySelector('.site-header');

        if (!menu || !openButton || !closeButton) return;

        const syncHeaderState = () => {
            stickyHeader?.classList.toggle('is-scrolled', window.scrollY > 12);
        };

        const openMenu = () => {
            menu.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        };

        const closeMenu = () => {
            menu.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        openButton.addEventListener('click', openMenu);
        closeButton.addEventListener('click', closeMenu);
        menu.addEventListener('click', event => {
            if (event.target === menu) closeMenu();
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') closeMenu();
        });

        submenuToggle?.addEventListener('click', () => {
            if (!submenu) return;

            const isOpen = !submenu.classList.contains('hidden');
            submenu.classList.toggle('hidden', isOpen);
            submenuToggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            submenuIcon?.classList.toggle('rotate-180', !isOpen);
        });

        syncHeaderState();
        window.addEventListener('scroll', syncHeaderState, { passive: true });
    })();
</script>
<main>
