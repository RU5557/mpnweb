

<?php $__env->startSection('title', 'Penjagaan Bulanan'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Penjagaan Bulanan</h1>
            <p class="text-xs text-slate-500">Perbandingan penerimaan per bulan 2026 vs 2025</p>
        </div>
        
        <!-- Filter Form dengan Checkbox Dropdown -->
        <form method="GET" action="<?php echo e(route('penerimaan.penjagaan.bulanan')); ?>" class="flex flex-wrap items-center gap-3">
            
            <!-- Checkbox Filter: Fungsi -->
            <div x-data="{ open: false }" class="relative">
                <button type="button" @click="open = !open" class="bg-slate-50 border border-slate-300 text-slate-700 text-xs rounded-xl p-2.5 flex items-center gap-2 hover:bg-slate-100 focus:ring-2 focus:ring-blue-500">
                    <span class="font-medium">Fungsi</span>
                    <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                        <?php echo e(count((array)request('fungsi', []))); ?>

                    </span>
                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                </button>

                <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-xl shadow-lg z-50 p-3 space-y-2">
                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider pb-1 border-b border-slate-100">Pilih Fungsi</div>
                    <div class="max-h-48 overflow-y-auto space-y-1.5 pr-1">
                        <?php $__currentLoopData = $fungsiOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $checked = in_array($opt, (array)request('fungsi', [])); ?>
                            <label class="flex items-center gap-2 text-xs text-slate-700 hover:bg-slate-50 p-1.5 rounded-lg cursor-pointer">
                                <input type="checkbox" name="fungsi[]" value="<?php echo e($opt); ?>" <?php echo e($checked ? 'checked' : ''); ?> class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="truncate"><?php echo e($opt); ?></span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div class="pt-2 border-t border-slate-100 text-right">
                        <button type="submit" class="bg-blue-600 text-white text-xs px-3 py-1.5 rounded-lg font-medium hover:bg-blue-700">Terapkan</button>
                    </div>
                </div>
            </div>

            <!-- Checkbox Filter: Jenis -->
            <div x-data="{ open: false }" class="relative">
                <button type="button" @click="open = !open" class="bg-slate-50 border border-slate-300 text-slate-700 text-xs rounded-xl p-2.5 flex items-center gap-2 hover:bg-slate-100 focus:ring-2 focus:ring-blue-500">
                    <span class="font-medium">Jenis</span>
                    <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                        <?php echo e(count((array)request('jenis', []))); ?>

                    </span>
                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                </button>

                <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-xl shadow-lg z-50 p-3 space-y-2">
                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider pb-1 border-b border-slate-100">Pilih Jenis</div>
                    <div class="max-h-48 overflow-y-auto space-y-1.5 pr-1">
                        <?php $__currentLoopData = $jenisOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $checked = in_array($opt, (array)request('jenis', [])); ?>
                            <label class="flex items-center gap-2 text-xs text-slate-700 hover:bg-slate-50 p-1.5 rounded-lg cursor-pointer">
                                <input type="checkbox" name="jenis[]" value="<?php echo e($opt); ?>" <?php echo e($checked ? 'checked' : ''); ?> class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="truncate"><?php echo e($opt); ?></span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div class="pt-2 border-t border-slate-100 text-right">
                        <button type="submit" class="bg-blue-600 text-white text-xs px-3 py-1.5 rounded-lg font-medium hover:bg-blue-700">Terapkan</button>
                    </div>
                </div>
            </div>

            <?php if(request('fungsi') || request('jenis')): ?>
                <a href="<?php echo e(route('penerimaan.penjagaan.bulanan')); ?>" class="text-xs text-rose-500 hover:underline font-medium">Reset Filter</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Chart Container -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <canvas id="chartBulanan" class="max-h-[420px]"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('chartBulanan').getContext('2d');

    const grad2026 = ctx.createLinearGradient(0, 0, 0, 400);
    grad2026.addColorStop(0, 'rgba(37, 99, 235, 1)');
    grad2026.addColorStop(1, 'rgba(96, 165, 250, 0.8)');

    const grad2025 = ctx.createLinearGradient(0, 0, 0, 400);
    grad2025.addColorStop(0, 'rgba(148, 163, 184, 0.9)');
    grad2025.addColorStop(1, 'rgba(203, 213, 225, 0.6)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [
                {
                    label: 'Tahun 2026',
                    data: <?php echo json_encode($data2026); ?>,
                    backgroundColor: grad2026,
                    borderRadius: 8,
                    borderSkipped: false,
                    categoryPercentage: 0.6,
                    barPercentage: 0.7
                },
                {
                    label: 'Tahun 2025',
                    data: <?php echo json_encode($data2025); ?>,
                    backgroundColor: grad2025,
                    borderRadius: 8,
                    borderSkipped: false,
                    categoryPercentage: 0.6,
                    barPercentage: 0.7
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { 
                    position: 'top',
                    labels: { usePointStyle: true, boxWidth: 8, font: { size: 12, weight: '600' } }
                },
                tooltip: {
                    padding: 12,
                    cornerRadius: 10,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false } },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(226, 232, 240, 0.6)' },
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: "compact" }).format(value);
                        }
                    }
                }
            }
        }
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mpnweb\resources\views/penerimaan/penjagaan/bulanan.blade.php ENDPATH**/ ?>