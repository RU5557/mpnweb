<!DOCTYPE html>
<html lang="id" x-data="{ 
    sidebarOpen: false, 
    isPinned: false,
    handleMouseEnter() { if (!this.isPinned) this.sidebarOpen = true; },
    handleMouseLeave() { if (!this.isPinned) this.sidebarOpen = false; }
}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'MPNWEB'); ?></title>

    <!-- Tailwind CSS & Font Awesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-100 font-sans text-slate-800 text-sm min-h-screen flex antialiased">

    <!-- ==================== SIDEBAR (AUTO COLLAPSE & PIN) ==================== -->
    <aside 
        @mouseenter="handleMouseEnter()"
        @mouseleave="handleMouseLeave()"
        :class="sidebarOpen || isPinned ? 'w-60' : 'w-20'" 
        class="bg-slate-900 text-slate-300 min-h-screen transition-all duration-300 flex flex-col justify-between fixed left-0 top-0 bottom-0 z-40 border-r border-slate-800 shadow-xl">
        
        <div>
            <!-- Sidebar Header -->
            <div class="h-12 flex items-center justify-between px-4 border-b border-slate-800">
                <div x-show="sidebarOpen || isPinned" class="flex items-center gap-2.5 overflow-hidden">
                    <div class="bg-blue-600 text-white p-1.5 rounded-lg font-bold flex items-center justify-center w-7 h-7 shadow-md shadow-blue-500/20 shrink-0">
                        <i class="fa-solid fa-chart-pie text-xs"></i>
                    </div>
                    <span class="font-extrabold text-white text-lg tracking-wide">MPNWEB</span>
                </div>
                
                <!-- Lock / Pin Button -->
                <button @click="isPinned = !isPinned; sidebarOpen = isPinned" 
                        class="p-1.5 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition mx-auto flex items-center justify-center"
                        :title="isPinned ? 'Matikan Pin (Aktifkan Auto-Collapse)' : 'Kunci Sidebar (Matikan Auto-Collapse)'">
                    <i class="fa-solid text-sm" :class="isPinned ? 'fa-thumbtack text-blue-400' : (sidebarOpen ? 'fa-bars-staggered' : 'fa-bars')"></i>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="p-3 space-y-1.5">
                <!-- 1. Dashboard -->
                <a href="<?php echo e(route('penerimaan.dashboard')); ?>" 
                   class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition-colors <?php echo e(request()->routeIs('penerimaan.dashboard') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-300'); ?>">
                    <i class="fa-solid fa-border-all text-[15px] w-6 text-center shrink-0"></i>
                    <span x-show="sidebarOpen || isPinned" class="truncate">Dashboard</span>
                </a>

<!-- Nav Link Penjagaan (Dropdown) -->
<div x-data="{ open: <?php echo e(request()->routeIs('penerimaan.penjagaan.*') ? 'true' : 'false'); ?> }" class="space-y-1">
    <!-- Tombol Induk / Header Dropdown -->
    <button @click="open = !open" 
            :class="request()->routeIs('penerimaan.penjagaan.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'"
            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition-colors">
        <div class="flex items-center gap-3.5">
            <i class="fa-solid fa-chart-line text-[15px] w-6 text-center shrink-0"></i>
            <span x-show="sidebarOpen || isPinned" class="truncate">Penjagaan</span>
        </div>
        <i x-show="sidebarOpen || isPinned" class="fa-solid text-xs transition-transform duration-200" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
    </button>

    <!-- Submenu Dropdown -->
    <div x-show="open && (sidebarOpen || isPinned)" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         class="pl-9 space-y-1">
        
        <!-- 1. Penjagaan Bulanan -->
        <a href="<?php echo e(route('penerimaan.penjagaan.bulanan')); ?>" 
           class="block px-3 py-2 rounded-lg text-xs font-medium transition-colors <?php echo e(request()->routeIs('penerimaan.penjagaan.bulanan') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'); ?>">
            Bulanan
        </a>

        <!-- 2. Penjagaan Harian -->
        <a href="<?php echo e(route('penerimaan.penjagaan.harian')); ?>" 
           class="block px-3 py-2 rounded-lg text-xs font-medium transition-colors <?php echo e(request()->routeIs('penerimaan.penjagaan.harian') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'); ?>">
            Harian
        </a>

        <!-- 3. Penjagaan vs Bulan Lalu -->
        <a href="<?php echo e(route('penerimaan.penjagaan.vsbulanlalu')); ?>" 
           class="block px-3 py-2 rounded-lg text-xs font-medium transition-colors <?php echo e(request()->routeIs('penerimaan.penjagaan.vsbulanlalu') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'); ?>">
            Vs Bulan Lalu
        </a>
    </div>
</div>

                <!-- 2. PPM -->
                <a href="<?php echo e(route('penerimaan.ppm')); ?>" 
                   class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition-colors <?php echo e(request()->routeIs('penerimaan.ppm') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-300'); ?>">
                    <i class="fa-solid fa-wallet text-[15px] w-6 text-center shrink-0"></i>
                    <span x-show="sidebarOpen || isPinned" class="truncate">Penerimaan PPM</span>
                </a>

                <!-- 3. PKM Pengawasan -->
                <a href="<?php echo e(route('penerimaan.pkmpengawasan')); ?>" 
                   class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition-colors <?php echo e(request()->routeIs('penerimaan.pkmpengawasan') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-300'); ?>">
                    <i class="fa-solid fa-user-check text-[15px] w-6 text-center shrink-0"></i>
                    <span x-show="sidebarOpen || isPinned" class="truncate">PKM Pengawasan</span>
                </a>

                <!-- 4. PKM Pemeriksaan -->
                <a href="<?php echo e(route('penerimaan.pkmpemeriksaan')); ?>" 
                   class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition-colors <?php echo e(request()->routeIs('penerimaan.pkmpemeriksaan') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-300'); ?>">
                    <i class="fa-solid fa-magnifying-glass-chart text-[15px] w-6 text-center shrink-0"></i>
                    <span x-show="sidebarOpen || isPinned" class="truncate">PKM Pemeriksaan</span>
                </a>

                <!-- 5. PKM Penagihan -->
                <a href="<?php echo e(route('penerimaan.pkmpenagihan')); ?>" 
                   class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition-colors <?php echo e(request()->routeIs('penerimaan.pkmpenagihan') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-300'); ?>">
                    <i class="fa-solid fa-gavel text-[15px] w-6 text-center shrink-0"></i>
                    <span x-show="sidebarOpen || isPinned" class="truncate">PKM Penagihan</span>
                </a>
            </nav>
        </div>

        <!-- Sidebar Footer -->
        <div class="p-3 border-t border-slate-800 text-xs text-slate-400 flex justify-between items-center">
            <span x-show="sidebarOpen || isPinned" class="font-medium">© <?php echo e(date('Y')); ?> MPNWEB</span>
            <span class="bg-slate-800 text-slate-300 px-2 py-0.5 rounded text-[11px] font-mono">v1.0</span>
        </div>
    </aside>

    <!-- ==================== CONTENT WRAPPER ==================== -->
    <div :class="sidebarOpen || isPinned ? 'ml-60' : 'ml-20'" class="flex-grow transition-all duration-300 flex flex-col min-h-screen">
        
        <!-- TOPBAR NAV (LEBIH TIPIK / COMPACT: h-12) -->
        <header class="h-12 bg-white border-b border-slate-200 px-5 flex items-center justify-between sticky top-0 z-30 shadow-sm">
            <div class="flex items-center gap-3 text-[13px] font-medium text-slate-700 overflow-hidden max-w-4xl">
                <span class="bg-pink-100 text-pink-700 text-xs px-2.5 py-0.5 rounded-full font-bold flex items-center gap-1.5 whitespace-nowrap shadow-sm">
                    <i class="fa-solid fa-bullhorn text-pink-500"></i> Informasi
                </span>
                
                <div class="truncate text-[13px] md:text-sm">
                    <?php if(isset($rollingText) && $rollingText): ?>
                        <span class="text-slate-500">
                            Update: <?php echo e(\Carbon\Carbon::parse($rollingText->tanggal)->translatedFormat('d M Y')); ?> |
                        </span> 
                        NKO: <strong class="text-slate-900"><?php echo e(number_format($rollingText->nko, 2)); ?>%</strong> | 
                        Rank Nasional: <strong class="text-slate-900">#<?php echo e($rollingText->ranking_nasional); ?></strong> | 
                        Rank Kanwil: <strong class="text-slate-900">#<?php echo e($rollingText->ranking_kanwil); ?></strong>
                    <?php else: ?>
                        <span class="text-slate-400 italic">Belum ada data info harian.</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="<?php echo e(route('admin.index')); ?>" class="flex items-center gap-2.5 hover:bg-slate-100 py-1 px-2.5 rounded-lg transition border border-slate-200/60">
                    <div class="text-right">
                        <div class="text-xs font-bold text-slate-800 leading-tight">Admin KPP</div>
                        <div class="text-[10px] text-slate-500 leading-none">Seksi Pengolahan Data</div>
                    </div>
                    <div class="w-7 h-7 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold shadow-sm shrink-0">
                        <i class="fa-solid fa-user-gear text-xs"></i>
                    </div>
                </a>
            </div>
        </header>

<!-- MAIN CONTENT CONTAINER (Sudah diberi pembatas lebar max-w-7xl agar grafik tidak melar) -->
<main class="p-6 flex-grow bg-slate-50">
    <div class="max-w-7xl mx-auto w-full">
        <?php if(session('success')): ?>
            <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs p-3 rounded-xl mb-5 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2 font-medium">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                    <span><?php echo e(session('success')); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php echo $__env->yieldContent('content'); ?>
    </div>
</main>
    </div>

</body>
</html><?php /**PATH C:\xampp\htdocs\mpnweb\resources\views/layouts/app.blade.php ENDPATH**/ ?>