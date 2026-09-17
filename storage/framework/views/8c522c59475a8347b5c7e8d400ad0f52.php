

<?php $__env->startSection('title', 'PKM Pengawasan - MPNWEB'); ?>

<?php $__env->startSection('content'); ?>

<?php
    // Helper function untuk URL Sort
    function sortUrl($column, $currentSort, $currentDir) {
        $direction = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
        return request()->fullUrlWithQuery(['sort' => $column, 'direction' => $direction]);
    }

    // Helper icon Sort
    function sortIcon($column, $currentSort, $currentDir) {
        if ($currentSort !== $column) {
            return '<i class="fa-solid fa-sort text-slate-300 ml-1 text-xs"></i>';
        }
        return $currentDir === 'asc' 
            ? '<i class="fa-solid fa-sort-up text-emerald-600 ml-1 text-xs"></i>' 
            : '<i class="fa-solid fa-sort-down text-emerald-600 ml-1 text-xs"></i>';
    }
?>

<!-- HEADER & FILTER CONTAINER (COMPACT & SEJAJAR) -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">
    
    <!-- Judul & Subjudul -->
    <div>
        <h1 class="text-2xl md:text-3xl font-bold text-slate-800 tracking-tight">Penerimaan PKM Pengawasan</h1>
        <p class="text-xs md:text-sm text-slate-500 mt-0.5">Rincian realisasi PKM Pengawasan s.d. bulan terpilih per Seksi dan AR</p>
    </div>

    <!-- Form Filter Compact -->
    <form action="<?php echo e(route('penerimaan.pkmpengawasan')); ?>" method="GET" class="bg-white border border-slate-200 rounded-xl p-2 px-3.5 shadow-sm flex items-center gap-2.5">
        <!-- Preserve Current Sort State -->
        <input type="hidden" name="sort" value="<?php echo e(request('sort', 'nama_seksi')); ?>">
        <input type="hidden" name="direction" value="<?php echo e(request('direction', 'asc')); ?>">

        <!-- Select Seksi -->
        <select name="seksi" class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg px-3 py-1.5 font-medium focus:ring-2 focus:ring-blue-500 outline-none transition cursor-pointer">
            <option value="">Seksi Pengawasan</option>
            <?php $__currentLoopData = $daftarSeksi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $seksi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($seksi); ?>" <?php echo e(request('seksi') == $seksi ? 'selected' : ''); ?>><?php echo e($seksi); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <!-- Select Bulan (s.d. Format) -->
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
        <?php if(request()->has('bulan') || request()->has('tahun') || request()->has('seksi') || request()->has('sort')): ?>
            <a href="<?php echo e(route('penerimaan.pkmpengawasan')); ?>" class="text-slate-400 hover:text-slate-600 text-sm px-1.5 py-1.5 transition" title="Reset Filter">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        <?php endif; ?>
        <!-- Tombol Export Detil Transaksi (Tailwind Style) -->
<a href="<?php echo e(route('pkm.pengawasan.export-detil', request()->all())); ?>" 
   class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-3.5 py-1.5 rounded-lg transition shadow-sm flex items-center gap-2 border border-emerald-600">
    <i class="fa-solid fa-file-excel text-xs"></i>
    <span>Export CSV</span>
</a>
    </form>
</div>

<!-- TABEL PKM PENGAWASAN PER SEKSI & AR -->
<div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
            <h2 class="text-base font-bold text-slate-800">Tabel Penerimaan PKM Pengawasan</h2>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200 text-xs">
                <tr>
                    <th class="py-3 px-3.5 w-12 text-center whitespace-nowrap">No</th>
                    
                    <!-- Header Sort Nama Seksi -->
                    <th class="py-3 px-3.5 whitespace-nowrap">
                        <a href="<?php echo e(sortUrl('nama_seksi', $sortColumn, $sortDirection)); ?>" class="flex items-center gap-1 hover:text-emerald-600 transition select-none">
                            Nama Seksi <?php echo sortIcon('nama_seksi', $sortColumn, $sortDirection); ?>

                        </a>
                    </th>

                    <!-- Header Sort Nama AR -->
                    <th class="py-3 px-3.5 whitespace-nowrap">
                        <a href="<?php echo e(sortUrl('nama_ar', $sortColumn, $sortDirection)); ?>" class="flex items-center gap-1 hover:text-emerald-600 transition select-none">
                            Nama AR <?php echo sortIcon('nama_ar', $sortColumn, $sortDirection); ?>

                        </a>
                    </th>

                    <!-- Header Sort Akt Pengawasan -->
                    <th class="py-3 px-3.5 text-right whitespace-nowrap">
                        <a href="<?php echo e(sortUrl('total_akt_pengawasan', $sortColumn, $sortDirection)); ?>" class="flex items-center justify-end gap-1 hover:text-emerald-600 transition select-none">
                            Akt Pengawasan <?php echo sortIcon('total_akt_pengawasan', $sortColumn, $sortDirection); ?>

                        </a>
                    </th>

                    <!-- Header Sort Lainnya -->
                    <th class="py-3 px-3.5 text-right whitespace-nowrap">
                        <a href="<?php echo e(sortUrl('total_lainnya', $sortColumn, $sortDirection)); ?>" class="flex items-center justify-end gap-1 hover:text-emerald-600 transition select-none">
                            Lainnya <?php echo sortIcon('total_lainnya', $sortColumn, $sortDirection); ?>

                        </a>
                    </th>

                    <!-- Header Sort WRA Pengawasan -->
                    <th class="py-3 px-3.5 text-right whitespace-nowrap">
                        <a href="<?php echo e(sortUrl('total_wra_pengawasan', $sortColumn, $sortDirection)); ?>" class="flex items-center justify-end gap-1 hover:text-emerald-600 transition select-none">
                            WRA Pengawasan <?php echo sortIcon('total_wra_pengawasan', $sortColumn, $sortDirection); ?>

                        </a>
                    </th>

                    <!-- Header Sort Total PKM Pengawasan -->
                    <th class="py-3 px-3.5 text-right whitespace-nowrap">
                        <a href="<?php echo e(sortUrl('total_pkm_pengawasan', $sortColumn, $sortDirection)); ?>" class="flex items-center justify-end gap-1 hover:text-emerald-600 transition select-none">
                            Total PKM Pengawasan <?php echo sortIcon('total_pkm_pengawasan', $sortColumn, $sortDirection); ?>

                        </a>
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                <?php $__empty_1 = true; $__currentLoopData = $pkmData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-2.5 px-3.5 text-center text-slate-400 font-mono text-xs"><?php echo e($index + 1); ?></td>
                        <td class="py-2.5 px-3.5 font-semibold text-slate-800">
                            <?php if($row->nama_seksi === 'Unassign'): ?>
                                <span class="bg-rose-100 text-rose-700 px-2 py-0.5 rounded-md text-xs border border-rose-200 inline-block font-bold">
                                    Unassign
                                </span>
                            <?php else: ?>
                                <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded-md text-xs border border-slate-200 inline-block">
                                    <?php echo e($row->nama_seksi); ?>

                                </span>
                            <?php endif; ?>
                        </td>
<td class="py-2.5 px-3.5 text-slate-900 font-semibold text-[13px]">
    <?php if($row->nama_ar === 'Unassign'): ?>
        <span class="text-rose-600 italic">Unassign</span>
    <?php else: ?>
        <?php echo e($row->nama_ar); ?>

    <?php endif; ?>
</td>
                        <td class="py-2.5 px-3.5 text-right font-mono text-slate-600">
                            Rp <?php echo e(number_format($row->total_akt_pengawasan ?? 0, 0, ',', '.')); ?>

                        </td>
                        <td class="py-2.5 px-3.5 text-right font-mono text-slate-600">
                            Rp <?php echo e(number_format($row->total_lainnya ?? 0, 0, ',', '.')); ?>

                        </td>
                        <td class="py-2.5 px-3.5 text-right font-mono text-slate-600">
                            Rp <?php echo e(number_format($row->total_wra_pengawasan ?? 0, 0, ',', '.')); ?>

                        </td>
                        <td class="py-2.5 px-3.5 text-right font-mono font-bold text-emerald-600">
                            Rp <?php echo e(number_format($row->total_pkm_pengawasan ?? 0, 0, ',', '.')); ?>

                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="py-10 text-center text-slate-400 italic text-sm">
                            Tidak ada data PKM Pengawasan untuk filter bulan/tahun ini.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>

            <!-- FOOTER TOTAL / SUMMARY -->
            <?php if($pkmData->count() > 0): ?>
                <tfoot class="bg-slate-100/90 font-bold text-slate-900 border-t-2 border-slate-200 text-sm">
                    <tr>
                        <td colspan="3" class="py-3 px-3.5 text-center tracking-wider">TOTAL KESELURUHAN</td>
                        <td class="py-3 px-3.5 text-right font-mono">
                            Rp <?php echo e(number_format($pkmData->sum('total_akt_pengawasan'), 0, ',', '.')); ?>

                        </td>
                        <td class="py-3 px-3.5 text-right font-mono">
                            Rp <?php echo e(number_format($pkmData->sum('total_lainnya'), 0, ',', '.')); ?>

                        </td>
                        <td class="py-3 px-3.5 text-right font-mono">
                            Rp <?php echo e(number_format($pkmData->sum('total_wra_pengawasan'), 0, ',', '.')); ?>

                        </td>
                        <td class="py-3 px-3.5 text-right font-mono text-emerald-700">
                            Rp <?php echo e(number_format($pkmData->sum('total_pkm_pengawasan'), 0, ',', '.')); ?>

                        </td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mpnweb\resources\views\penerimaan\pkmpengawasan.blade.php ENDPATH**/ ?>