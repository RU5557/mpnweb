

<?php $__env->startSection('title', 'Pencarian Wajib Pajak'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Search Form -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
        <h1 class="text-xl font-bold text-gray-900 mb-4">Pencarian Wajib Pajak & Transaksi</h1>
        <form method="GET" action="<?php echo e(route('wp.search')); ?>" class="flex gap-3">
            <input 
                type="text" 
                name="q" 
                value="<?php echo e($keyword ?? ''); ?>" 
                placeholder="Masukkan NPWP 15-digit atau Nama Wajib Pajak..." 
                class="flex-grow bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-3 shadow-sm"
                required
            >
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 rounded-lg text-sm shadow-sm transition">
                Cari Data
            </button>
        </form>
    </div>

    <!-- Results Table -->
    <?php if(isset($results)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h3 class="text-sm font-semibold text-gray-700">
                    Hasil Pencarian untuk: <span class="text-blue-600 font-bold">"<?php echo e($keyword); ?>"</span>
                </h3>
                <span class="text-xs text-gray-500">Menampilkan <?php echo e($results->total()); ?> total data</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3">NPWP 15</th>
                            <th class="px-4 py-3">Nama WP (Transaksi / Master)</th>
                            <th class="px-4 py-3">Tgl Setor</th>
                            <th class="px-4 py-3">MAP/Bayar</th>
                            <th class="px-4 py-3 text-right">Jml Setor (Rp)</th>
                            <th class="px-4 py-3">AR Penanggung Jawab</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 font-mono text-xs text-blue-700 font-semibold"><?php echo e($item->npwp15); ?></td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900"><?php echo e($item->nama_wp ?? $item->nama_master); ?></div>
                                    <div class="text-xs text-gray-400">Status: <?php echo e($item->status_wp ?? '-'); ?></div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap"><?php echo e($item->tgl_setor); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="bg-gray-100 text-gray-800 text-xs font-mono px-2 py-0.5 rounded border border-gray-300">
                                        <?php echo e($item->kd_map); ?>/<?php echo e($item->kd_bayar); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900 whitespace-nowrap">
                                    Rp <?php echo e(number_format($item->jml_setor, 0, ',', '.')); ?>

                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600"><?php echo e($item->nama_ar ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                    Data Wajib Pajak atau transaksi tidak ditemukan.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Link -->
            <div class="px-6 py-4 border-t border-gray-200">
                <?php echo e($results->appends(['q' => $keyword])->links()); ?>

            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mpnweb\resources\views\search\index.blade.php ENDPATH**/ ?>