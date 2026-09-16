

<?php $__env->startSection('title', 'PKM Pemeriksaan - MPNWEB'); ?>

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
            ? '<i class="fa-solid fa-sort-up text-indigo-600 ml-1 text-xs"></i>' 
            : '<i class="fa-solid fa-sort-down text-indigo-600 ml-1 text-xs"></i>';
    }
?>

<!-- HEADER & FILTER CONTAINER (COMPACT & SEJAJAR BERSAMA SEARCH) -->
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-5">
    
    <!-- Judul & Subjudul -->
    <div>
        <h1 class="text-2xl md:text-3xl font-bold text-slate-800 tracking-tight">Penerimaan PKM Pemeriksaan</h1>
        <p class="text-xs md:text-sm text-slate-500 mt-0.5">Rincian realisasi PKM Pemeriksaan s.d. bulan terpilih per Wajib Pajak (WP)</p>
    </div>

    <!-- Form Filter + Search Bar Compact -->
    <form action="<?php echo e(route('penerimaan.pkmpemeriksaan')); ?>" method="GET" class="bg-white border border-slate-200 rounded-xl p-2 px-3.5 shadow-sm flex flex-wrap items-center gap-2.5">
        <!-- Preserve Sort State -->
        <input type="hidden" name="sort" value="<?php echo e(request('sort', 'total_akt_pemeriksaan')); ?>">
        <input type="hidden" name="direction" value="<?php echo e(request('direction', 'desc')); ?>">

        <!-- Input Search Bar Compact -->
        <div class="relative flex-1 min-w-[220px]">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400 text-xs">
                <i class="fa-solid fa-magnifying-glass"></i>
            </span>
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari NPWP / WP / KLU..." 
                   class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition placeholder:text-slate-400 font-medium">
        </div>

        <!-- Filter Bulan -->
        <select name="bulan" class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg px-3 py-1.5 font-medium focus:ring-2 focus:ring-indigo-500 outline-none transition cursor-pointer">
            <?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $monthName = \Carbon\Carbon::create()->month($m)->translatedFormat('F');
                ?>
                <option value="<?php echo e($m); ?>" <?php echo e(request('bulan', date('m')) == $m ? 'selected' : ''); ?>>
                    s.d. <?php echo e($monthName); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <!-- Filter Tahun -->
        <select name="tahun" class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg px-3 py-1.5 font-medium focus:ring-2 focus:ring-indigo-500 outline-none transition cursor-pointer">
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
        <?php if(request()->has('bulan') || request()->has('tahun') || request()->has('search') || request()->has('sort')): ?>
            <a href="<?php echo e(route('penerimaan.pkmpemeriksaan')); ?>" class="text-slate-400 hover:text-slate-600 text-sm px-1.5 py-1.5 transition" title="Reset Filter">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        <?php endif; ?>
        <!-- Tombol Export Detil Transaksi (Tailwind Style) -->
<a href="<?php echo e(route('pkm.pemeriksaan.export-detil', request()->all())); ?>" 
   class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-3.5 py-1.5 rounded-lg transition shadow-sm flex items-center gap-2 border border-emerald-600">
    <i class="fa-solid fa-file-excel text-xs"></i>
    <span>Export Excel</span>
</a>
    </form>
</div>

<!-- TABEL PKM PEMERIKSAAN -->
<div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-3 h-3 rounded-full bg-indigo-600"></div>
            <h2 class="text-base font-bold text-slate-800">Tabel Realisasi PKM Pemeriksaan</h2>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200 text-xs">
                <tr>
                    <th class="py-3 px-3.5 w-12 text-center whitespace-nowrap">No</th>
                    
                    <!-- Header Sort NPWP -->
                    <th class="py-3 px-3.5 whitespace-nowrap">
                        <a href="<?php echo e(sortUrl('npwp', $sortColumn, $sortDirection)); ?>" class="flex items-center gap-1 hover:text-indigo-600 transition select-none">
                            NPWP <?php echo sortIcon('npwp', $sortColumn, $sortDirection); ?>

                        </a>
                    </th>
                    
                    <!-- Header Sort Nama WP -->
                    <th class="py-3 px-3.5 whitespace-nowrap">
                        <a href="<?php echo e(sortUrl('nama_wp', $sortColumn, $sortDirection)); ?>" class="flex items-center gap-1 hover:text-indigo-600 transition select-none">
                            Nama WP <?php echo sortIcon('nama_wp', $sortColumn, $sortDirection); ?>

                        </a>
                    </th>
                    
                    <!-- Header Sort KODE KLU -->
                    <th class="py-3 px-3.5 text-center whitespace-nowrap">
                        <a href="<?php echo e(sortUrl('kd_klu', $sortColumn, $sortDirection)); ?>" class="flex items-center justify-center gap-1 hover:text-indigo-600 transition select-none">
                            KODE KLU <?php echo sortIcon('kd_klu', $sortColumn, $sortDirection); ?>

                        </a>
                    </th>
                    
                    <!-- Header Sort Nama KLU -->
                    <th class="py-3 px-3.5 max-w-xs whitespace-nowrap">
                        <a href="<?php echo e(sortUrl('nm_klu', $sortColumn, $sortDirection)); ?>" class="flex items-center gap-1 hover:text-indigo-600 transition select-none">
                            Nama KLU <?php echo sortIcon('nm_klu', $sortColumn, $sortDirection); ?>

                        </a>
                    </th>
                    
                    <!-- Header Sort TOTAL PKM PEMERIKSAAN -->
                    <th class="py-3 px-3.5 text-right whitespace-nowrap">
                        <a href="<?php echo e(sortUrl('total_akt_pemeriksaan', $sortColumn, $sortDirection)); ?>" class="flex items-center justify-end gap-1 hover:text-indigo-600 transition select-none">
                            TOTAL PKM PEMERIKSAAN <?php echo sortIcon('total_akt_pemeriksaan', $sortColumn, $sortDirection); ?>

                        </a>
                    </th>
                </tr>
            </thead>
            
            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                <?php $__empty_1 = true; $__currentLoopData = $pkmData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-2.5 px-3.5 text-center text-slate-400 font-mono text-xs">
                            <?php echo e($pkmData->firstItem() + $index); ?>

                        </td>
                        <td class="py-2.5 px-3.5 font-mono font-semibold text-slate-800 whitespace-nowrap text-xs">
                            <?php echo e($row->npwp15); ?>

                        </td>
                        <!-- Nama WP: Font dikecilkan sedikit ke 13px -->
                        <td class="py-2.5 px-3.5 text-slate-900 font-semibold text-[13px]">
                            <?php if($row->nama_wp === 'WP Tidak Terdaftar'): ?>
                                <span class="text-rose-600 italic">WP Tidak Terdaftar</span>
                            <?php else: ?>
                                <?php echo e($row->nama_wp); ?>

                            <?php endif; ?>
                        </td>
                        <td class="py-2.5 px-3.5 text-center font-mono text-slate-600 text-xs whitespace-nowrap">
                            <?php echo e($row->kd_klu); ?>

                        </td>
                        <td class="py-2.5 px-3.5 text-slate-600 max-w-xs truncate text-xs" title="<?php echo e($row->nm_klu); ?>">
                            <?php echo e($row->nm_klu); ?>

                        </td>
                        <td class="py-2.5 px-3.5 text-right font-mono font-bold text-indigo-600 whitespace-nowrap">
                            Rp <?php echo e(number_format($row->total_akt_pemeriksaan ?? 0, 0, ',', '.')); ?>

                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="py-10 text-center text-slate-400 italic text-sm">
                            Tidak ada data PKM Pemeriksaan yang sesuai dengan filter/pencarian.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>

            <?php if($pkmData->count() > 0): ?>
                <tfoot class="bg-slate-100/90 font-bold text-slate-900 border-t-2 border-slate-200 text-sm">
                    <tr>
                        <td colspan="5" class="py-3 px-3.5 text-center tracking-wider">TOTAL SUBHALAMAN INI</td>
                        <td class="py-3 px-3.5 text-right font-mono text-indigo-700 whitespace-nowrap">
                            Rp <?php echo e(number_format($pkmData->sum('total_akt_pemeriksaan'), 0, ',', '.')); ?>

                        </td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>

    <!-- Paginasi (10 Data Per Halaman) -->
    <div class="p-3 border-t border-slate-100 bg-slate-50/50">
        <?php echo e($pkmData->links()); ?>

    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mpnweb\resources\views/penerimaan/pkmpemeriksaan.blade.php ENDPATH**/ ?>