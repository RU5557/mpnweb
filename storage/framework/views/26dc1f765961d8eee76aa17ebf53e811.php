

<?php $__env->startSection('title', 'Penerimaan PPM - MPNWEB'); ?>

<?php $__env->startSection('content'); ?>

<!-- HEADER & FILTER CONTAINER -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    
    <!-- Judul & Subjudul -->
    <div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Penerimaan PPM</h1>
        <p class="text-sm text-slate-500 mt-1">Overview penerimaan, capaian target, dan performa PPM</p>
    </div>

<!-- Form Filter Compact & Sejajar -->
<form action="<?php echo e(route('penerimaan.ppm')); ?>" method="GET" class="bg-white border border-slate-200 rounded-xl p-2 px-3.5 shadow-sm flex items-center gap-2.5">
    
    <!-- Select Bulan -->
    <select name="bulan" class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg px-3 py-1.5 font-medium focus:ring-2 focus:ring-blue-500 outline-none transition cursor-pointer">
        <?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $monthName = \Carbon\Carbon::create()->month($m)->translatedFormat('F');
            ?>
            <option value="<?php echo e($m); ?>" <?php echo e(request('bulan', date('m')) == $m ? 'selected' : ''); ?>>
                <?php echo e($monthName); ?>

            </option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>

    <!-- Select Tahun -->
    <select name="tahun" class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg px-3 py-1.5 font-medium focus:ring-2 focus:ring-blue-500 outline-none transition cursor-pointer">
        <?php $__currentLoopData = range(date('Y') - 3, date('Y')); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($year); ?>" <?php echo e(request('tahun', date('Y')) == $year ? 'selected' : ''); ?>>
                <?php echo e($year); ?>

            </option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>

    <!-- Tombol Terapkan -->
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-1.5 rounded-lg transition shadow-sm flex items-center gap-2">
        Terapkan
    </button>

    <!-- Tombol Reset -->
    <?php if(request()->has('bulan') || request()->has('tahun')): ?>
        <a href="<?php echo e(route('penerimaan.ppm')); ?>" class="text-slate-400 hover:text-slate-600 text-sm px-1.5 py-1.5 transition" title="Reset Filter">
            <i class="fa-solid fa-rotate-left"></i>
        </a>
    <?php endif; ?>
    <!-- Tombol Export Detil Transaksi (Tailwind Style) -->
<a href="<?php echo e(route('ppm.export-detil', request()->all())); ?>" 
   class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-3.5 py-1.5 rounded-lg transition shadow-sm flex items-center gap-2 border border-emerald-600">
    <i class="fa-solid fa-file-excel text-xs"></i>
    <span>Export CSV</span>
</a>
</form>
</div>

<!-- Baris 1: 3 Card Utama (WP, Kategori/Sektor, Jenis Pajak) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-5">

    <!-- Card Top 10 WP -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100">
        <h3 class="font-bold text-slate-800 text-sm mb-3 border-b pb-2 flex justify-between items-center">
            <span>10 WP Terbesar</span>
            <span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">Top 10</span>
        </h3>
        <div class="space-y-2 text-xs">
            <?php $__empty_1 = true; $__currentLoopData = $topWp; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex justify-between items-center py-1 border-b border-slate-50 last:border-none">
                    <div class="flex-1 min-w-0 pr-2 truncate">
                        <span class="font-bold text-slate-400 mr-1"><?php echo e($index + 1); ?>.</span>
                        <span class="font-medium text-slate-700" title="<?php echo e($item->nama_wp); ?>"><?php echo e($item->nama_wp); ?></span>
                    </div>
                    <span class="font-bold text-slate-800 shrink-0">Rp <?php echo e(number_format($item->total, 0, ',', '.')); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-slate-400 text-center py-4">Tidak ada data</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Card Top 10 Kategori / Sektor -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100">
        <h3 class="font-bold text-slate-800 text-sm mb-3 border-b pb-2 flex justify-between items-center">
            <span>10 Sektor Terbesar</span>
            <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">Top 10</span>
        </h3>
        <div class="space-y-2 text-xs">
            <?php $__empty_1 = true; $__currentLoopData = $topKategori; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex justify-between items-center py-1 border-b border-slate-50 last:border-none">
                    <div class="flex-1 min-w-0 pr-2 truncate">
                        <span class="font-bold text-slate-400 mr-1"><?php echo e($index + 1); ?>.</span>
                        <span class="font-medium text-slate-700" title="<?php echo e($item->nm_kategori); ?>"><?php echo e($item->nm_kategori); ?></span>
                    </div>
                    <span class="font-bold text-slate-800 shrink-0">Rp <?php echo e(number_format($item->total, 0, ',', '.')); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-slate-400 text-center py-4">Tidak ada data</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Card Top 10 Jenis Pajak -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100">
        <h3 class="font-bold text-slate-800 text-sm mb-3 border-b pb-2 flex justify-between items-center">
            <span>10 Jenis Pajak Terbesar</span>
            <span class="text-[10px] bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full font-medium">Top 10</span>
        </h3>
        <div class="space-y-2 text-xs">
            <?php $__empty_1 = true; $__currentLoopData = $topJenisPajak; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex justify-between items-center py-1 border-b border-slate-50 last:border-none">
                    <div class="flex-1 min-w-0 pr-2 truncate">
                        <span class="font-bold text-slate-400 mr-1"><?php echo e($index + 1); ?>.</span>
                        <span class="font-medium text-slate-700" title="<?php echo e($item->jenis_pajak); ?>"><?php echo e($item->jenis_pajak); ?></span>
                    </div>
                    <span class="font-bold text-slate-800 shrink-0">Rp <?php echo e(number_format($item->total, 0, ',', '.')); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-slate-400 text-center py-4">Tidak ada data</p>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Baris 2: 4 Card WP per Jenis Pajak Spesifik (2x2 Grid) -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">

    <!-- Top WP PPN DN -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100">
        <h3 class="font-bold text-slate-800 text-sm mb-3 border-b pb-2">10 WP PPN DN Terbesar</h3>
        <div class="space-y-2 text-xs">
            <?php $__empty_1 = true; $__currentLoopData = $topWpPpnDn; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex justify-between items-center py-1 border-b border-slate-50 last:border-none">
                    <div class="flex-1 min-w-0 pr-2 truncate">
                        <span class="font-bold text-slate-400 mr-1"><?php echo e($index + 1); ?>.</span>
                        <span class="font-medium text-slate-700" title="<?php echo e($item->nama_wp); ?>"><?php echo e($item->nama_wp); ?></span>
                    </div>
                    <span class="font-bold text-slate-800 shrink-0">Rp <?php echo e(number_format($item->total, 0, ',', '.')); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-slate-400 text-center py-4">Tidak ada data</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top WP PPN Impor -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100">
        <h3 class="font-bold text-slate-800 text-sm mb-3 border-b pb-2">10 WP PPN Impor Terbesar</h3>
        <div class="space-y-2 text-xs">
            <?php $__empty_1 = true; $__currentLoopData = $topWpPpnImpor; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex justify-between items-center py-1 border-b border-slate-50 last:border-none">
                    <div class="flex-1 min-w-0 pr-2 truncate">
                        <span class="font-bold text-slate-400 mr-1"><?php echo e($index + 1); ?>.</span>
                        <span class="font-medium text-slate-700" title="<?php echo e($item->nama_wp); ?>"><?php echo e($item->nama_wp); ?></span>
                    </div>
                    <span class="font-bold text-slate-800 shrink-0">Rp <?php echo e(number_format($item->total, 0, ',', '.')); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-slate-400 text-center py-4">Tidak ada data</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top WP PPh Pasal 25/29 Badan -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100">
        <h3 class="font-bold text-slate-800 text-sm mb-3 border-b pb-2">10 WP PPh 25/29 Badan Terbesar</h3>
        <div class="space-y-2 text-xs">
            <?php $__empty_1 = true; $__currentLoopData = $topWpPphBadan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex justify-between items-center py-1 border-b border-slate-50 last:border-none">
                    <div class="flex-1 min-w-0 pr-2 truncate">
                        <span class="font-bold text-slate-400 mr-1"><?php echo e($index + 1); ?>.</span>
                        <span class="font-medium text-slate-700" title="<?php echo e($item->nama_wp); ?>"><?php echo e($item->nama_wp); ?></span>
                    </div>
                    <span class="font-bold text-slate-800 shrink-0">Rp <?php echo e(number_format($item->total, 0, ',', '.')); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-slate-400 text-center py-4">Tidak ada data</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top WP PPh Pasal 21 -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100">
        <h3 class="font-bold text-slate-800 text-sm mb-3 border-b pb-2">10 WP PPh Pasal 21 Terbesar</h3>
        <div class="space-y-2 text-xs">
            <?php $__empty_1 = true; $__currentLoopData = $topWpPph21; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex justify-between items-center py-1 border-b border-slate-50 last:border-none">
                    <div class="flex-1 min-w-0 pr-2 truncate">
                        <span class="font-bold text-slate-400 mr-1"><?php echo e($index + 1); ?>.</span>
                        <span class="font-medium text-slate-700" title="<?php echo e($item->nama_wp); ?>"><?php echo e($item->nama_wp); ?></span>
                    </div>
                    <span class="font-bold text-slate-800 shrink-0">Rp <?php echo e(number_format($item->total, 0, ',', '.')); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-slate-400 text-center py-4">Tidak ada data</p>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mpnweb\resources\views/penerimaan/ppm.blade.php ENDPATH**/ ?>