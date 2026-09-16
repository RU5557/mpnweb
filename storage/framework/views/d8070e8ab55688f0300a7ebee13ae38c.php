<?php $__env->startSection('content'); ?>
<!-- HEADER & FILTER CONTAINER -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    
    <!-- Judul & Subjudul -->
    <div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Dashboard Ringkasan</h1>
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
<a href="<?php echo e(route('dashboard.export-detil', request()->all())); ?>" 
   class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-3.5 py-1.5 rounded-lg transition shadow-sm flex items-center gap-2 border border-emerald-600">
    <i class="fa-solid fa-file-excel text-xs"></i>
    <span>Export CSV</span>
</a>
</form>
</div>

<!-- BARIS 1: CARD RINGKASAN UTAMA (3 KOLOM) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    
    <!-- Penerimaan Saat Ini -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200/80 border-l-4 border-l-blue-600 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Penerimaan Saat Ini</span>
                <span class="bg-blue-50 text-blue-700 border border-blue-200/60 text-xs font-semibold px-3 py-1 rounded-md">
                    <?php echo e(number_format($capaianKantor, 1, ',', '.')); ?>% Capaian
                </span>
            </div>

            <div class="text-2xl md:text-3xl font-bold text-slate-900 my-2 tracking-normal tabular-nums">
                <span class="text-slate-500 text-xl font-normal">Rp</span> <?php echo e(number_format($penerimaanSaatIni ?? 0, 0, ',', '.')); ?>

            </div>
        </div>
        <div class="flex items-center gap-4 text-xs font-medium text-slate-600 pt-4 border-t border-slate-100">
            <?php
                $growthMoM = ($penerimaanBlnLalu ?? 0) > 0 ? (($penerimaanSaatIni - $penerimaanBlnLalu) / $penerimaanBlnLalu) * 100 : 0;
                $growthYoY = ($penerimaanThnLalu ?? 0) > 0 ? (($penerimaanSaatIni - $penerimaanThnLalu) / $penerimaanThnLalu) * 100 : 0;
            ?>
            <span>MoM: 
                <span class="<?php echo e($growthMoM >= 0 ? 'text-emerald-600' : 'text-rose-600'); ?> font-semibold tabular-nums">
                    <i class="fa-solid <?php echo e($growthMoM >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'); ?>"></i> 
                    <?php echo e(number_format(abs($growthMoM), 2, ',', '.')); ?>%
                </span>
            </span>
            <span>YoY: 
                <span class="<?php echo e($growthYoY >= 0 ? 'text-emerald-600' : 'text-rose-600'); ?> font-semibold tabular-nums">
                    <i class="fa-solid <?php echo e($growthYoY >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'); ?>"></i> 
                    <?php echo e(number_format(abs($growthYoY), 2, ',', '.')); ?>%
                </span>
            </span>
        </div>
    </div>

    <!-- Penerimaan Bulan Lalu -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200/80 border-l-4 border-l-sky-500 flex flex-col justify-between">
        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Penerimaan Bulan Lalu</span>
        <div class="text-2xl md:text-3xl font-bold text-slate-900 my-2 tracking-normal tabular-nums">
            <span class="text-slate-500 text-xl font-normal">Rp</span> <?php echo e(number_format($penerimaanBlnLalu ?? 0, 0, ',', '.')); ?>

        </div>
        <div class="text-xs font-normal text-slate-400 border-t border-slate-100 pt-4">
            Pembanding bulan sebelumnya
        </div>
    </div>

    <!-- Penerimaan Tahun Lalu -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200/80 border-l-4 border-l-amber-500 flex flex-col justify-between">
        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Penerimaan Tahun Lalu</span>
        <div class="text-2xl md:text-3xl font-bold text-slate-900 my-2 tracking-normal tabular-nums">
            <span class="text-slate-500 text-xl font-normal">Rp</span> <?php echo e(number_format($penerimaanThnLalu ?? 0, 0, ',', '.')); ?>

        </div>
        <div class="text-xs font-normal text-slate-400 border-t border-slate-100 pt-4">
            Pembanding tahun lalu (YoY)
        </div>
    </div>

</div>

<!-- BARIS 2 & 3: GRID PERFORMANCE CARD (3 KOLOM x 2 BARIS) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <!-- Card 1: PPM -->
    <?php
        $targetPpm = $target->target_ppm ?? 0;
        $persenPPM = $targetPpm > 0 ? (($realisasiPPM ?? 0) / $targetPpm) * 100 : 0;
        $growthPPM = ($realisasiPPMLalu ?? 0) > 0 ? ((($realisasiPPM ?? 0) - $realisasiPPMLalu) / $realisasiPPMLalu) * 100 : 0;
    ?>
    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">PPM</h3>
                    <p class="text-xs text-slate-400 font-normal">Capaian Target</p>
                </div>
                <span class="bg-blue-50 text-blue-700 border border-blue-200/60 font-semibold text-sm px-3 py-1 rounded-md tracking-normal tabular-nums">
                    <?php echo e(number_format($persenPPM, 1, ',', '.')); ?>%
                </span>
            </div>
            <div class="space-y-2.5 text-sm text-slate-600 border-t pt-4 border-slate-100">
                <div class="flex justify-between">
                    <span>Target:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp <?php echo e(number_format($targetPpm, 0, ',', '.')); ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Realisasi:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp <?php echo e(number_format($realisasiPPM ?? 0, 0, ',', '.')); ?></span>
                </div>
            </div>
        </div>
        <div class="text-xs pt-4 mt-4 border-t border-slate-100 flex justify-between items-center font-medium">
            <span class="text-slate-500">Pertumbuhan YoY:</span>
            <span class="<?php echo e($growthPPM >= 0 ? 'text-emerald-600' : 'text-rose-600'); ?> font-semibold tabular-nums">
                <i class="fa-solid <?php echo e($growthPPM >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'); ?>"></i> 
                <?php echo e(number_format(abs($growthPPM), 2, ',', '.')); ?>%
            </span>
        </div>
    </div>

    <!-- Card 2: PKM -->
    <?php
        $targetPkm = $target->target_pkm ?? 0;
        $persenPKM = $targetPkm > 0 ? (($realisasiPKM ?? 0) / $targetPkm) * 100 : 0;
        $growthPKM = ($realisasiPKMLalu ?? 0) > 0 ? ((($realisasiPKM ?? 0) - $realisasiPKMLalu) / $realisasiPKMLalu) * 100 : 0;
    ?>
    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">PKM</h3>
                    <p class="text-xs text-slate-400 font-normal">Capaian Target</p>
                </div>
                <span class="bg-sky-50 text-sky-700 border border-sky-200/60 font-semibold text-sm px-3 py-1 rounded-md tracking-normal tabular-nums">
                    <?php echo e(number_format($persenPKM, 1, ',', '.')); ?>%
                </span>
            </div>
            <div class="space-y-2.5 text-sm text-slate-600 border-t pt-4 border-slate-100">
                <div class="flex justify-between">
                    <span>Target:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp <?php echo e(number_format($targetPkm, 0, ',', '.')); ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Realisasi:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp <?php echo e(number_format($realisasiPKM ?? 0, 0, ',', '.')); ?></span>
                </div>
            </div>
        </div>
        <div class="text-xs pt-4 mt-4 border-t border-slate-100 flex justify-between items-center font-medium">
            <span class="text-slate-500">Pertumbuhan YoY:</span>
            <span class="<?php echo e($growthPKM >= 0 ? 'text-emerald-600' : 'text-rose-600'); ?> font-semibold tabular-nums">
                <i class="fa-solid <?php echo e($growthPKM >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'); ?>"></i> 
                <?php echo e(number_format(abs($growthPKM), 2, ',', '.')); ?>%
            </span>
        </div>
    </div>

    <!-- Card 3: PBP -->
    <?php
        $targetPbp = $target->target_pbp ?? 0;
        $persenPBP = $targetPbp > 0 ? (($realisasiPBP ?? 0) / $targetPbp) * 100 : 0;
        $sisaPBP = max(0, $targetPbp - ($realisasiPBP ?? 0));
    ?>
    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">PBP</h3>
                    <p class="text-xs text-slate-400 font-normal">Capaian Target</p>
                </div>
                <span class="bg-amber-50 text-amber-700 border border-amber-200/60 font-semibold text-sm px-3 py-1 rounded-md tracking-normal tabular-nums">
                    <?php echo e(number_format($persenPBP, 1, ',', '.')); ?>%
                </span>
            </div>
            <div class="space-y-2.5 text-sm text-slate-600 border-t pt-4 border-slate-100">
                <div class="flex justify-between">
                    <span>Target:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp <?php echo e(number_format($targetPbp, 0, ',', '.')); ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Realisasi:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp <?php echo e(number_format($realisasiPBP ?? 0, 0, ',', '.')); ?></span>
                </div>
            </div>
        </div>
        
        <div class="text-xs pt-4 mt-4 border-t border-slate-100 flex justify-between items-center font-medium">
            <span class="text-slate-500">Sisa Target:</span>
            <span class="text-rose-600 font-semibold tabular-nums">
                Rp <?php echo e(number_format($sisaPBP, 0, ',', '.')); ?>

            </span>
        </div>
    </div>

    <!-- Card 4: PKM Pengawasan -->
    <?php
        $targetPengawasan = $target->target_pkm_pengawasan ?? 0;
        $persenPengawasan = $targetPengawasan > 0 ? (($realisasiPengawasan ?? 0) / $targetPengawasan) * 100 : 0;
        $growthPengawasan = ($realisasiPengawasanLalu ?? 0) > 0 ? ((($realisasiPengawasan ?? 0) - $realisasiPengawasanLalu) / $realisasiPengawasanLalu) * 100 : 0;
    ?>
    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">PKM Pengawasan</h3>
                    <p class="text-xs text-slate-400 font-normal">Capaian Target</p>
                </div>
                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200/60 font-semibold text-sm px-3 py-1 rounded-md tracking-normal tabular-nums">
                    <?php echo e(number_format($persenPengawasan, 1, ',', '.')); ?>%
                </span>
            </div>
            <div class="space-y-2.5 text-sm text-slate-600 border-t pt-4 border-slate-100">
                <div class="flex justify-between">
                    <span>Target:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp <?php echo e(number_format($targetPengawasan, 0, ',', '.')); ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Realisasi:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp <?php echo e(number_format($realisasiPengawasan ?? 0, 0, ',', '.')); ?></span>
                </div>
            </div>
        </div>
        <div class="text-xs pt-4 mt-4 border-t border-slate-100 flex justify-between items-center font-medium">
            <span class="text-slate-500">Pertumbuhan YoY:</span>
            <span class="<?php echo e($growthPengawasan >= 0 ? 'text-emerald-600' : 'text-rose-600'); ?> font-semibold tabular-nums">
                <i class="fa-solid <?php echo e($growthPengawasan >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'); ?>"></i> 
                <?php echo e(number_format(abs($growthPengawasan), 2, ',', '.')); ?>%
            </span>
        </div>
    </div>

    <!-- Card 5: PKM Pemeriksaan -->
    <?php
        $targetPemeriksaan = $target->target_pkm_pemeriksaan ?? 0;
        $persenPemeriksaan = $targetPemeriksaan > 0 ? (($realisasiPemeriksaan ?? 0) / $targetPemeriksaan) * 100 : 0;
        $growthPemeriksaan = ($realisasiPemeriksaanLalu ?? 0) > 0 ? ((($realisasiPemeriksaan ?? 0) - $realisasiPemeriksaanLalu) / $realisasiPemeriksaanLalu) * 100 : 0;
    ?>
    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">PKM Pemeriksaan</h3>
                    <p class="text-xs text-slate-400 font-normal">Capaian Target</p>
                </div>
                <span class="bg-slate-100 text-slate-700 border border-slate-200 font-semibold text-sm px-3 py-1 rounded-md tracking-normal tabular-nums">
                    <?php echo e(number_format($persenPemeriksaan, 1, ',', '.')); ?>%
                </span>
            </div>
            <div class="space-y-2.5 text-sm text-slate-600 border-t pt-4 border-slate-100">
                <div class="flex justify-between">
                    <span>Target:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp <?php echo e(number_format($targetPemeriksaan, 0, ',', '.')); ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Realisasi:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp <?php echo e(number_format($realisasiPemeriksaan ?? 0, 0, ',', '.')); ?></span>
                </div>
            </div>
        </div>
        <div class="text-xs pt-4 mt-4 border-t border-slate-100 flex justify-between items-center font-medium">
            <span class="text-slate-500">Pertumbuhan YoY:</span>
            <span class="<?php echo e($growthPemeriksaan >= 0 ? 'text-emerald-600' : 'text-rose-600'); ?> font-semibold tabular-nums">
                <i class="fa-solid <?php echo e($growthPemeriksaan >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'); ?>"></i> 
                <?php echo e(number_format(abs($growthPemeriksaan), 2, ',', '.')); ?>%
            </span>
        </div>
    </div>

    <!-- Card 6: PKM Penagihan -->
    <?php
        $targetPenagihan = $target->target_pkm_penagihan ?? 0;
        $persenPenagihan = $targetPenagihan > 0 ? (($realisasiPenagihan ?? 0) / $targetPenagihan) * 100 : 0;
        $growthPenagihan = ($realisasiPenagihanLalu ?? 0) > 0 ? ((($realisasiPenagihan ?? 0) - $realisasiPenagihanLalu) / $realisasiPenagihanLalu) * 100 : 0;
    ?>
    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">PKM Penagihan</h3>
                    <p class="text-xs text-slate-400 font-normal">Capaian Target</p>
                </div>
                <span class="bg-slate-100 text-slate-800 border border-slate-200 font-semibold text-sm px-3 py-1 rounded-md tracking-normal tabular-nums">
                    <?php echo e(number_format($persenPenagihan, 1, ',', '.')); ?>%
                </span>
            </div>
            <div class="space-y-2.5 text-sm text-slate-600 border-t pt-4 border-slate-100">
                <div class="flex justify-between">
                    <span>Target:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp <?php echo e(number_format($targetPenagihan, 0, ',', '.')); ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Realisasi:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp <?php echo e(number_format($realisasiPenagihan ?? 0, 0, ',', '.')); ?></span>
                </div>
            </div>
        </div>
        <div class="text-xs pt-4 mt-4 border-t border-slate-100 flex justify-between items-center font-medium">
            <span class="text-slate-500">Pertumbuhan YoY:</span>
            <span class="<?php echo e($growthPenagihan >= 0 ? 'text-emerald-600' : 'text-rose-600'); ?> font-semibold tabular-nums">
                <i class="fa-solid <?php echo e($growthPenagihan >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'); ?>"></i> 
                <?php echo e(number_format(abs($growthPenagihan), 2, ',', '.')); ?>%
            </span>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mpnweb\resources\views/penerimaan/dashboard.blade.php ENDPATH**/ ?>