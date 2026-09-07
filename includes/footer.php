<?php require_once __DIR__ . '/config.php'; ?>
</main>
<footer class="bg-[#071426] text-white">
    <div class="mx-auto w-full max-w-[1480px] px-4 py-14 lg:px-8 lg:py-20">
        <div class="grid gap-12 lg:grid-cols-[1.1fr_.65fr_1.25fr] xl:gap-20">
            <div>
                <?php $footerLogo = $site['footer_logo_image'] ?: ($site['logo_image'] ?? ''); ?>
                <?php if ($footerLogo): ?>
                    <img class="h-16 w-auto max-w-[280px] object-contain object-left" src="<?php echo e(base_url($footerLogo)); ?>" alt="<?php echo e($site['title']); ?>">
                <?php else: ?>
                    <div class="grid h-14 w-14 place-items-center rounded-xl bg-amber-400 text-xl font-bold text-[#071426]"><?php echo e($site['logo_text']); ?></div>
                <?php endif; ?>
                <p class="mt-6 max-w-md text-sm leading-7 text-slate-400"><?php echo e($site['tagline']); ?></p>
                <div class="mt-7 flex gap-2">
                    <?php foreach ($site['socials'] as $icon => $url): ?>
                        <a class="grid h-10 w-10 place-items-center rounded-full border border-white/10 text-sm text-slate-300 transition hover:border-amber-400 hover:bg-amber-400 hover:text-[#071426]" href="<?php echo e($url); ?>" aria-label="<?php echo e($icon); ?>">
                            <i class="fa-brands fa-<?php echo e($icon); ?>"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <h2 class="text-xs font-bold uppercase tracking-[.2em] text-amber-300">Explore</h2>
                <nav class="mt-6 grid gap-3 text-sm font-semibold text-slate-300">
                    <a class="w-max transition hover:text-amber-300" href="<?php echo e(base_url('pages/about.php')); ?>">About company</a>
                    <a class="w-max transition hover:text-amber-300" href="<?php echo e(base_url('services.php')); ?>">Our services</a>
                    <a class="w-max transition hover:text-amber-300" href="<?php echo e(base_url('pages/membership-certificates.php')); ?>">Credentials</a>
                    <a class="w-max transition hover:text-amber-300" href="<?php echo e(base_url('pages/team.php')); ?>">Leadership team</a>
                    <a class="w-max transition hover:text-amber-300" href="<?php echo e(base_url('blog.php')); ?>">News & insights</a>
                    <a class="w-max transition hover:text-amber-300" href="<?php echo e(base_url('pages/contact.php')); ?>">Contact</a>
                </nav>
            </div>

            <div>
                <h2 class="text-xs font-bold uppercase tracking-[.2em] text-amber-300">Stay informed</h2>
                <p class="mt-6 max-w-lg text-sm leading-7 text-slate-400">Logistics updates, service notes and company announcements—delivered occasionally.</p>
                <?php if (($_GET['newsletter'] ?? '') === 'success'): ?>
                    <p class="mt-4 rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-200" role="status">Thanks for subscribing.</p>
                <?php elseif (($_GET['newsletter'] ?? '') === 'error'): ?>
                    <p class="mt-4 rounded-xl border border-red-400/20 bg-red-400/10 px-4 py-3 text-sm text-red-200" role="alert">Please enter a valid email and answer.</p>
                <?php endif; ?>
                <form class="mt-6 grid gap-3 sm:grid-cols-[1fr_110px_auto]" method="post" action="<?php echo e(base_url('newsletter-subscribe.php')); ?>">
                    <label class="sr-only" for="footer-subscribe-email">Email address</label>
                    <input id="footer-subscribe-email" class="min-h-12 min-w-0 rounded-full border border-white/10 bg-white/[.06] px-5 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-amber-400" type="email" name="newsletter_email" placeholder="Email address" required>
                    <label class="sr-only" for="footer-newsletter-check">What is four plus six?</label>
                    <input id="footer-newsletter-check" class="min-h-12 min-w-0 rounded-full border border-white/10 bg-white/[.06] px-5 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-amber-400" type="number" name="newsletter_check" placeholder="4 + 6 = ?" required>
                    <button class="min-h-12 rounded-full bg-amber-400 px-6 text-sm font-bold text-[#071426] transition hover:bg-amber-300" type="submit">Subscribe</button>
                </form>
            </div>
        </div>

        <div class="mt-14 grid gap-6 border-t border-white/10 pt-8 md:grid-cols-2">
            <?php foreach (($officeContacts ?? []) as $office): ?>
                <?php if (!($office['visible'] ?? true)) continue; ?>
                <address class="not-italic">
                    <h2 class="text-xs font-bold uppercase tracking-[.18em] text-slate-500"><?php echo e($office['title'] ?? 'Office'); ?></h2>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-slate-400"><?php echo e($office['address'] ?? ''); ?></p>
                </address>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="mx-auto flex w-full max-w-[1480px] flex-col gap-3 px-4 py-5 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between lg:px-8">
            <p><?php echo e($site['copyright']); ?></p>
            <p>Website by <a class="font-semibold text-slate-300 hover:text-amber-300" href="https://kbashar.com" target="_blank" rel="noopener">kbashar.com</a></p>
        </div>
    </div>
</footer>
</body>
</html>
