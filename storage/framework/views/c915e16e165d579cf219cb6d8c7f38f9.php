

<?php $__env->startSection('title', 'PKM Penagihan - MPNWEB'); ?>

<?php $__env->startSection('content'); ?>

<?php
    // Helper function untuk generate URL sort
    function sortUrl($column, $currentSort, $currentDir) {
        $direction = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
        return request()->fullUrlWithQuery(['sort' => $column, 'direction' => $direction]);
    }

    // Helper icon sort
    function sortIcon($column, $currentSort, $currentDir) {
        if ($currentSort !== $column) {
            return '<i class="fa-solid fa-sort text-slate-300 ml-1 text-xs"></i>';
        }
        return $currentDir === 'asc' 
            ? '<i class="fa-solid fa-sort-up text-amber-600 ml-1 text-xs"></i>' 
            : '<i class="fa-solid fa-sort-down text-amber-600 ml-1 text-xs"></i>';
    }
?>

<!-- HEADER & FILTER -->
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Penerimaan PKM Penagihan</h1>
        <p class="text-base text-slate-500 mt-1">Rincian realisasi PKM Penagihan s.d. bulan terpilih per Juru Sita Pajak Negara (JSPN)</p>
    </div>

    <!-- Form Filter -->
    <form action="<?php echo e(route('penerimaan.pkmpenagihan')); ?>" method="GET" class="bg-white border border-slate-200/80 rounded-2xl p-2.5 px-4 shadow-sm flex flex-wrap items-center gap-3">
        <!-- State Preserve Sort -->
        <input type="hidden" name="sort" value="<?php echo e(request('sort', 'nip_jspn')); ?>">
        <input type="hidden" name="direction" value="<?php echo e(request('direction', 'asc')); ?>">

        <!-- Filter Dropdown DSPC / NON-DSPC -->
        <select name="dspc_filter" class="bg-slate-50 border border-slate-200 text-slate-700 text-base rounded-xl px-4 py-2.5 font-medium outline-none">
            <option value="">-- Semua Kategori --</option>
            <option value="DSPC" <?php echo e(request('dspc_filter') == 'DSPC' ? 'selected' : ''); ?>>DSPC</option>
            <option value="NON-DSPC" <?php echo e(request('dspc_filter') == 'NON-DSPC' ? 'selected' : ''); ?>>NON-DSPC</option>
        </select>

        <!-- Filter Bulan -->
        <select name="bulan" class="bg-slate-50 border border-slate-200 text-slate-700 text-base rounded-xl px-4 py-2.5 font-medium outline-none">
            <?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($m); ?>" <?php echo e(request('bulan', date('m')) == $m ? 'selected' : ''); ?>>
                    s.d. <?php echo e(\Carbon\Carbon::create()->month($m)->translatedFormat('F')); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <!-- Filter Tahun -->
        <select name="tahun" class="bg-slate-50 border border-slate-200 text-slate-700 text-base rounded-xl px-4 py-2.5 font-medium outline-none">
            <?php $__currentLoopData = range(date('Y') - 3, date('Y')); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($year); ?>" <?php echo e(request('tahun', date('Y')) == $year ? 'selected' : ''); ?>><?php echo e($year); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-base font-semibold px-5 py-2.5 rounded-xl transition shadow-sm">
            Terapkan
        </button>

        <?php if(request()->has('bulan') || request()->has('tahun') || request()->has('dspc_filter') || request()->has('sort')): ?>
            <a href="<?php echo e(route('penerimaan.pkmpenagihan')); ?>" class="text-slate-400 hover:text-slate-600 text-base px-2 py-2 transition" title="Reset Filter">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- TABEL PKM PENAGIHAN -->
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-3.5 h-3.5 rounded-full bg-amber-500"></div>
            <h2 class="text-lg font-bold text-slate-800">Tabel Realisasi PKM Penagihan</h2>
        </div>
        <span class="text-sm font-semibold text-slate-500 bg-slate-100 px-3 py-1 rounded-lg">
            Total Data: <?php echo e($pkmData->count()); ?> Baris
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-base">
            <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200 text-sm">
                <tr>
                    <th class="py-4 px-4 w-14 text-center whitespace-nowrap">No</th>
                    
                    <!-- Header Sort NIP JSPN -->
                    <th class="py-4 px-4 whitespace-nowrap">
                        <a href="<?php echo e(sortUrl('nip_jspn', $sortColumn, $sortDirection)); ?>" class="flex items-center gap-1 hover:text-amber-600 transition select-none">
                            NIP JSPN <?php echo sortIcon('nip_jspn', $sortColumn, $sortDirection); ?>

                        </a>
                    </th>

                    <!-- Header Sort NAMA JSPN -->
                    <th class="py-4 px-4 whitespace-nowrap">
                        <a href="<?php echo e(sortUrl('nama_jspn', $sortColumn, $sortDirection)); ?>" class="flex items-center gap-1 hover:text-amber-600 transition select-none">
                            NAMA JSPN <?php echo sortIcon('nama_jspn', $sortColumn, $sortDirection); ?>

                        </a>
                    </th>

                    <!-- Header Sort DSPC / NON-DSPC -->
                    <th class="py-4 px-4 text-center whitespace-nowrap">
                        <a href="<?php echo e(sortUrl('flag_skp', $sortColumn, $sortDirection)); ?>" class="flex items-center justify-center gap-1 hover:text-amber-600 transition select-none">
                            DSPC / NON-DSPC <?php echo sortIcon('flag_skp', $sortColumn, $sortDirection); ?>

                        </a>
                    </th>

                    <!-- Header Sort AKT PENAGIHAN -->
                    <th class="py-4 px-4 text-right whitespace-nowrap">
                        <a href="<?php echo e(sortUrl('akt_penagihan', $sortColumn, $sortDirection)); ?>" class="flex items-center justify-end gap-1 hover:text-amber-600 transition select-none">
                            AKT PENAGIHAN <?php echo sortIcon('akt_penagihan', $sortColumn, $sortDirection); ?>

                        </a>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                <?php $__empty_1 = true; $__currentLoopData = $pkmData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-4 px-4 text-center text-slate-400 font-mono text-sm">
                            <?php echo e($index + 1); ?>

                        </td>
                        
                        <!-- NIP JSPN -->
                        <td class="py-4 px-4 font-mono font-bold text-slate-800 whitespace-nowrap">
                            <?php if($row->nip_jspn === 'Unassign'): ?>
                                <span class="text-rose-600 italic font-sans font-medium">Unassign</span>
                            <?php else: ?>
                                <?php echo e($row->nip_jspn); ?>

                            <?php endif; ?>
                        </td>

                        <!-- NAMA JSPN -->
                        <td class="py-4 px-4 text-slate-900 font-bold">
                            <?php if($row->nama_jspn === 'Unassign'): ?>
                                <span class="text-rose-600 italic font-medium">Unassign</span>
                            <?php else: ?>
                                <?php echo e($row->nama_jspn); ?>

                            <?php endif; ?>
                        </td>

                        <!-- DSPC / NON-DSPC -->
                        <td class="py-4 px-4 text-center whitespace-nowrap">
                            <?php if(strtoupper($row->flag_skp) === 'DSPC'): ?>
                                <span class="bg-amber-100 text-amber-800 border border-amber-300 text-xs px-3 py-1 rounded-full font-bold">
                                    DSPC
                                </span>
                            <?php else: ?>
                                <span class="bg-slate-100 text-slate-600 border border-slate-200 text-xs px-3 py-1 rounded-full font-semibold">
                                    NON-DSPC
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- AKT PENAGIHAN -->
                        <td class="py-4 px-4 text-right font-mono font-bold text-amber-600 whitespace-nowrap">
                            Rp <?php echo e(number_format($row->akt_penagihan ?? 0, 0, ',', '.')); ?>

                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400 italic text-base">
                            Tidak ada data PKM Penagihan yang sesuai dengan filter.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <?php if($pkmData->count() > 0): ?>
                <tfoot class="bg-slate-100/90 font-bold text-slate-900 border-t-2 border-slate-200 text-base">
                    <tr>
                        <td colspan="4" class="py-4 px-4 text-center tracking-wider">TOTAL KESELURUHAN</td>
                        <td class="py-4 px-4 text-right font-mono text-amber-700 whitespace-nowrap">
                            Rp <?php echo e(number_format($pkmData->sum('akt_penagihan'), 0, ',', '.')); ?>

                        </td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mpnweb\resources\views/penerimaan/pkmpenagihan.blade.php ENDPATH**/ ?>