<?php
require_once __DIR__ . '/../includes/config.php';
$adminProfile = admin_profile();

?>
<header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 px-4 py-3 backdrop-blur lg:px-6">
    <div class="flex items-center justify-between gap-4">
        <span class="text-sm font-black text-slate-700">Control panel</span>
        <div class="flex items-center gap-3">
            <a class="hidden items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-black text-blue-700 shadow-sm hover:bg-slate-50 sm:inline-flex" href="<?php echo e(base_url('index.php')); ?>" target="_blank">
                <i class="fa-solid fa-arrow-up-right-from-square mr-2"></i>Visit Website
            </a>
            <div class="group relative">
                <button class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white p-1.5 pr-3 text-left shadow-sm hover:bg-slate-50" type="button">
                    <img class="h-9 w-9 rounded-lg border border-slate-200 object-cover" src="<?php echo e(base_url($adminProfile['image'])); ?>" alt="<?php echo e($adminProfile['name']); ?>">
                    <span class="hidden sm:block">
                        <strong class="block text-sm font-black text-slate-950"><?php echo e($adminProfile['name']); ?></strong>
                        <small class="text-xs font-bold text-slate-500"><?php echo e($adminProfile['designation'] ?: 'Admin'); ?></small>
                    </span>
                    <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
                </button>
                <div class="invisible absolute right-0 top-full w-56 pt-3 opacity-0 transition group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                        <a class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-black text-slate-700 hover:bg-slate-100" href="<?php echo e(base_url('dashboard/profile.php')); ?>">
                            <i class="fa-solid fa-user text-blue-700"></i>User Profile
                        </a>
                        <a class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-black text-slate-700 hover:bg-slate-100 sm:hidden" href="<?php echo e(base_url('index.php')); ?>" target="_blank">
                            <i class="fa-solid fa-arrow-up-right-from-square text-blue-700"></i>Visit Website
                        </a>
                        <a class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-black text-red-600 hover:bg-red-50" href="<?php echo e(base_url('logout.php')); ?>">
                            <i class="fa-solid fa-right-from-bracket"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
