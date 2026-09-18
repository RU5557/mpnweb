

<?php $__env->startSection('title', 'Pencarian Data WP'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6" 
     x-data="{ 
         targetTable: '<?php echo e($targetTable ?? 'masterfile'); ?>',
         loading: false 
     }">
    
    <div>
        <h1 class="text-xl font-bold text-slate-800">Pencarian Data Wajib Pajak</h1>
        <p class="text-xs text-slate-500">Cari informasi masterfile WP atau riwayat transaksi penerimaan pajak.</p>
    </div>

    <!-- ==================== FORM PENCARIAN ==================== -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
        <form action="<?php echo e(route('wp.search')); ?>" 
              method="GET" 
              @submit="loading = true" 
              class="space-y-4">
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                
                <!-- Selector Target Tabel -->
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Target Tabel</label>
                    <select name="target_table" 
                            x-model="targetTable"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl p-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="masterfile">Masterfile WP</option>
                        <option value="detil_transaksi">Detil DRM</option>
                    </select>
                </div>

                <!-- Input Keyword -->
                <div class="md:col-span-7">
                    <!-- Penyesuaian Judul Label -->
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kata Kunci (NPWP / Nama / Fungsi)</label>
                    <div class="relative">
                        <input type="text" name="q" value="<?php echo e($keyword ?? ''); ?>" placeholder="Masukkan NPWP, Nama WP, atau Fungsi..." 
                               class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl pl-9 pr-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
                    </div>
                </div>

                <!-- Tombol Cari -->
                <div class="md:col-span-2">
                    <button type="submit" 
                            :disabled="loading"
                            :class="loading ? 'opacity-75 cursor-not-allowed' : ''"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs px-4 py-2.5 rounded-xl transition shadow-md shadow-blue-500/20 flex items-center justify-center gap-2">
                        <i x-show="loading" class="fa-solid fa-circle-notch fa-spin text-xs" x-cloak></i>
                        <i x-show="!loading" class="fa-solid fa-search text-xs"></i>
                        <span x-text="loading ? 'Mencari...' : 'Cari Data'"></span>
                    </button>
                </div>
            </div>

            <!-- Filter Tambahan Khusus Detil Transaksi -->
            <div x-show="targetTable === 'detil_transaksi'" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="pt-3 border-t border-slate-100">
                 
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-xs font-semibold text-blue-600 flex items-center gap-1">
                        <i class="fa-solid fa-filter"></i> Filter Transaksi:
                    </span>

                    <div class="w-36">
                        <select name="thn_setor" class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua Tahun --</option>
                            <?php for($y = date('Y'); $y >= 2025; $y--): ?>
                                <option value="<?php echo e($y); ?>" <?php echo e(($thnSetor ?? '') == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="w-36">
                        <select name="bln_setor" class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua Bulan --</option>
                            <?php
                                $bulan = [
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ];
                            ?>
                            <?php $__currentLoopData = $bulan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>" <?php echo e(($blnSetor ?? '') == $key ? 'selected' : ''); ?>><?php echo e($val); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- ==================== AREA TABEL SKELETON ==================== -->
    <div x-show="loading" x-cloak class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden animate-pulse">
        <div class="p-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
            <div class="h-4 bg-slate-200 rounded w-1/4"></div>
            <div class="h-4 bg-slate-200 rounded w-1/12"></div>
        </div>
        <div class="p-4 space-y-4">
            <div class="grid grid-cols-6 gap-4 border-b border-slate-100 pb-3">
                <div class="h-3 bg-slate-200 rounded col-span-1"></div>
                <div class="h-3 bg-slate-200 rounded col-span-2"></div>
                <div class="h-3 bg-slate-200 rounded col-span-1"></div>
                <div class="h-3 bg-slate-200 rounded col-span-1"></div>
                <div class="h-3 bg-slate-200 rounded col-span-1"></div>
            </div>
            <?php for($i = 0; $i < 5; $i++): ?>
                <div class="grid grid-cols-6 gap-4 py-2">
                    <div class="h-4 bg-slate-200 rounded col-span-1"></div>
                    <div class="h-4 bg-slate-200 rounded col-span-2"></div>
                    <div class="h-4 bg-slate-200 rounded col-span-1"></div>
                    <div class="h-4 bg-slate-200 rounded col-span-1"></div>
                    <div class="h-4 bg-slate-200 rounded col-span-1"></div>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- ==================== HASIL TABEL ASLI ==================== -->
    <?php if($results): ?>
        <!-- Helper Macro/Blade Function untuk Link Sorting -->
        <?php
            function sortUrl($col, $currentSortBy, $currentSortOrder, $keyword, $targetTable, $thnSetor, $blnSetor) {
                $nextOrder = ($currentSortBy === $col && $currentSortOrder === 'asc') ? 'desc' : 'asc';
                return route('wp.search', [
                    'q' => $keyword,
                    'target_table' => $targetTable,
                    'thn_setor' => $thnSetor,
                    'bln_setor' => $blnSetor,
                    'sort_by' => $col,
                    'sort_order' => $nextOrder
                ]);
            }
        ?>

        <div x-show="!loading" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <span class="text-xs font-semibold text-slate-600">
                    Hasil Pencarian di <strong class="text-slate-900"><?php echo e($targetTable === 'masterfile' ? 'Masterfile WP' : 'Detil DRM'); ?></strong> 
                    (Total: <?php echo e($results->total()); ?> data)
                </span>
            </div>

            <div class="overflow-x-auto">
                <?php if($targetTable === 'masterfile'): ?>
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[11px] border-b">
                            <tr>
                                <th class="p-3">
                                    <a href="<?php echo e(sortUrl('npwp15', $sortBy, $sortOrder, $keyword, $targetTable, $thnSetor, $blnSetor)); ?>" class="flex items-center gap-1 hover:text-blue-600">
                                        NPWP15 / NPWP16
                                        <i class="fa-solid <?php echo e($sortBy === 'npwp15' ? ($sortOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort text-slate-300'); ?>"></i>
                                    </a>
                                </th>
                                <th class="p-3">
                                    <a href="<?php echo e(sortUrl('nama_wp', $sortBy, $sortOrder, $keyword, $targetTable, $thnSetor, $blnSetor)); ?>" class="flex items-center gap-1 hover:text-blue-600">
                                        Nama Wajib Pajak
                                        <i class="fa-solid <?php echo e($sortBy === 'nama_wp' ? ($sortOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort text-slate-300'); ?>"></i>
                                    </a>
                                </th>
                                <th class="p-3">Alamat</th>
                                <th class="p-3">Jenis / Status</th>
                                <th class="p-3">No. Telepon</th>
                                <th class="p-3">
                                    <a href="<?php echo e(sortUrl('nama_ar', $sortBy, $sortOrder, $keyword, $targetTable, $thnSetor, $blnSetor)); ?>" class="flex items-center gap-1 hover:text-blue-600">
                                        Account Representative
                                        <i class="fa-solid <?php echo e($sortBy === 'nama_ar' ? ($sortOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort text-slate-300'); ?>"></i>
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php $__empty_1 = true; $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 font-mono font-semibold text-slate-800">
                                        <div><?php echo e($item->npwp15); ?></div>
                                        <?php if($item->npwp16): ?>
                                            <div class="text-[10px] text-slate-400 font-normal">NIK/16: <?php echo e($item->npwp16); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3 font-medium text-slate-800"><?php echo e($item->nama_wp); ?></td>
                                    <td class="p-3 text-[11px]">
                                        <?php echo e($item->alamat); ?>

                                        <?php if($item->kecamatan || $item->kota): ?>
                                            <div class="text-slate-400 text-[10px]"><?php echo e($item->kecamatan); ?>, <?php echo e($item->kota); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3">
                                        <div class="font-semibold text-slate-700"><?php echo e($item->jenis_wp ?? '-'); ?></div>
                                        <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full text-[10px] font-bold"><?php echo e($item->status_wp ?? '-'); ?></span>
                                    </td>
                                    <td class="p-3"><?php echo e($item->telp ?? '-'); ?></td>
                                    <td class="p-3 font-medium text-slate-700"><?php echo e($item->nama_ar ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-slate-400 italic">Data Masterfile tidak ditemukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[11px] border-b">
                            <tr>
                                <th class="p-3">
                                    <a href="<?php echo e(sortUrl('tgl_setor', $sortBy, $sortOrder, $keyword, $targetTable, $thnSetor, $blnSetor)); ?>" class="flex items-center gap-1 hover:text-blue-600">
                                        Tgl Setor
                                        <i class="fa-solid <?php echo e($sortBy === 'tgl_setor' ? ($sortOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort text-slate-300'); ?>"></i>
                                    </a>
                                </th>
                                <th class="p-3">
                                    <a href="<?php echo e(sortUrl('npwp15', $sortBy, $sortOrder, $keyword, $targetTable, $thnSetor, $blnSetor)); ?>" class="flex items-center gap-1 hover:text-blue-600">
                                        NPWP15 / Nama WP
                                        <i class="fa-solid <?php echo e($sortBy === 'npwp15' ? ($sortOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort text-slate-300'); ?>"></i>
                                    </a>
                                </th>
                                <!-- Kolom NTPN diganti dengan Fungsi -->
                                <th class="p-3">
                                    <a href="<?php echo e(sortUrl('fungsi', $sortBy, $sortOrder, $keyword, $targetTable, $thnSetor, $blnSetor)); ?>" class="flex items-center gap-1 hover:text-blue-600">
                                        Fungsi
                                        <i class="fa-solid <?php echo e($sortBy === 'fungsi' ? ($sortOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort text-slate-300'); ?>"></i>
                                    </a>
                                </th>
                                <th class="p-3">MAP / Bayar</th>
                                <th class="p-3">Masa / Thn Pajak</th>
                                <th class="p-3 text-right">
                                    <a href="<?php echo e(sortUrl('jml_setor', $sortBy, $sortOrder, $keyword, $targetTable, $thnSetor, $blnSetor)); ?>" class="flex items-center justify-end gap-1 hover:text-blue-600">
                                        Jumlah Setor (Rp)
                                        <i class="fa-solid <?php echo e($sortBy === 'jml_setor' ? ($sortOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort text-slate-300'); ?>"></i>
                                    </a>
                                </th>
                                <th class="p-3">
                                    <a href="<?php echo e(sortUrl('nama_ar', $sortBy, $sortOrder, $keyword, $targetTable, $thnSetor, $blnSetor)); ?>" class="flex items-center gap-1 hover:text-blue-600">
                                        Account Representative
                                        <i class="fa-solid <?php echo e($sortBy === 'nama_ar' ? ($sortOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort text-slate-300'); ?>"></i>
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php $__empty_1 = true; $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 whitespace-nowrap">
                                        <?php echo e($item->tgl_setor ? \Carbon\Carbon::parse($item->tgl_setor)->format('d/m/Y') : '-'); ?>

                                    </td>
                                    <td class="p-3">
                                        <div class="font-mono font-semibold text-slate-800"><?php echo e($item->npwp15); ?></div>
                                        <div class="text-[11px] text-slate-600"><?php echo e($item->nama_wp ?? $item->nama_master); ?></div>
                                    </td>
                                    <!-- Menampilkan data Fungsi -->
                                    <td class="p-3">
                                        <span class="bg-slate-100 text-slate-700 px-2 py-1 rounded font-mono text-[11px] font-semibold border border-slate-200">
                                            <?php echo e($item->fungsi ?? '-'); ?>

                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <div class="font-mono font-bold text-slate-700"><?php echo e($item->kd_map); ?> / <?php echo e($item->kd_bayar); ?></div>
                                        <div class="text-[10px] text-slate-500"><?php echo e($item->jenis_pajak ?? '-'); ?></div>
                                    </td>
                                    <td class="p-3 whitespace-nowrap">
                                        Masa <?php echo e($item->masa_pajak ?? '-'); ?> / <?php echo e($item->thn_pajak ?? '-'); ?>

                                    </td>
                                    <td class="p-3 text-right font-semibold text-emerald-600 whitespace-nowrap">
                                        Rp <?php echo e(number_format($item->jml_setor, 0, ',', '.')); ?>

                                    </td>
                                    <td class="p-3">
                                        <div class="text-[11px] font-semibold text-slate-700"><?php echo e($item->nama_ar ?? '-'); ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="p-6 text-center text-slate-400 italic">Data Transaksi tidak ditemukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="p-4 border-t border-slate-100">
                <?php echo e($results->links()); ?>

            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mpnweb\resources\views/search/index.blade.php ENDPATH**/ ?>