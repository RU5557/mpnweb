@extends('layouts.app')

@section('title', 'Penjagaan Bulanan')

@section('content')
<div class="space-y-6" 
     x-data="{
        // Mapping relasi Jenis -> Fungsi
        mapping: {
            'PKM AKTIVITAS': ['AKT PEMERIKSAAN', 'AKT PENAGIHAN', 'AKT PENEGAKAN HUKUM', 'AKT PENGAWASAN'],
            'PKM LAINNYA': ['LAINNYA'],
            'PBP': ['PBP'],
            'PPM': ['PPM BRUTO', 'SPMKP'],
            'PKM WRA': ['WRA PENGAWASAN', 'WRA EDUKASI', 'WRA PENEGAKAN HUKUM']
        },
        allJenisOptions: {{ json_encode($jenisOptions->toArray()) }},
        allFungsiOptions: {{ json_encode($fungsiOptions->toArray()) }},
        selectedJenis: {{ json_encode($jenis) }},
        selectedFungsi: {{ json_encode($fungsi) }},

        // Toggle Pilih Semua - Jenis
        toggleAllJenis(checked) {
            if (checked) {
                this.selectedJenis = [...this.allJenisOptions];
                this.selectedFungsi = [...this.allFungsiOptions];
            } else {
                this.selectedJenis = [];
                this.selectedFungsi = [];
            }
        },

        // Toggle Pilih Semua - Fungsi
        toggleAllFungsi(checked) {
            if (checked) {
                this.selectedFungsi = [...this.allFungsiOptions];
            } else {
                this.selectedFungsi = [];
            }
        },

        // Fungsi memicu toggle pada item Jenis individual
        toggleJenis(item) {
            let index = this.selectedJenis.indexOf(item);
            let childFungsi = this.mapping[item] || [];

            if (index > -1) {
                // Uncheck Jenis -> Otomatis Uncheck semua Fungsi turunannya
                this.selectedJenis.splice(index, 1);
                this.selectedFungsi = this.selectedFungsi.filter(f => !childFungsi.includes(f));
            } else {
                // Check Jenis -> Otomatis Check semua Fungsi turunannya
                this.selectedJenis.push(item);
                childFungsi.forEach(f => {
                    if (!this.selectedFungsi.includes(f)) {
                        this.selectedFungsi.push(f);
                    }
                });
            }
        }
     }">

    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Penjagaan Bulanan</h1>
            <p class="text-xs text-slate-500">Perbandingan penerimaan per bulan 2026 vs 2025</p>
        </div>
        
        <!-- Filter Form -->
        <form method="GET" action="{{ route('penerimaan.penjagaan.bulanan') }}" class="flex flex-wrap items-center gap-3">
            
            <!-- Checkbox Filter: Jenis -->
            <div x-data="{ open: false }" class="relative">
                <button type="button" @click="open = !open" class="bg-slate-50 border border-slate-300 text-slate-700 text-xs rounded-xl p-2.5 flex items-center gap-2 hover:bg-slate-100 focus:ring-2 focus:ring-blue-500">
                    <span class="font-medium">Jenis</span>
                    <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-1.5 py-0.5 rounded-full" x-text="selectedJenis.length"></span>
                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                </button>

                <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-xl shadow-lg z-50 p-3 space-y-2">
                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider pb-1 border-b border-slate-100">Pilih Jenis</div>
                    <div class="max-h-56 overflow-y-auto space-y-1.5 pr-1">
                        
                        <!-- Checkbox Pilih Semua - Jenis -->
                        <label class="flex items-center gap-2 text-xs font-semibold text-blue-700 hover:bg-blue-50 p-1.5 rounded-lg cursor-pointer border-b border-slate-100 mb-1">
                            <input type="checkbox" 
                                :checked="selectedJenis.length === allJenisOptions.length && allJenisOptions.length > 0"
                                @change="toggleAllJenis($event.target.checked)"
                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span>Pilih Semua</span>
                        </label>

                        @foreach($jenisOptions as $opt)
                            <label class="flex items-center gap-2 text-xs text-slate-700 hover:bg-slate-50 p-1.5 rounded-lg cursor-pointer select-none">
                                <input type="checkbox" 
                                    name="jenis[]" 
                                    value="{{ $opt }}" 
                                    :checked="selectedJenis.includes('{{ $opt }}')"
                                    @change="toggleJenis('{{ $opt }}')"
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="truncate">{{ $opt }}</span>
                            </label>
                        @endforeach
                    </div>
                    <!-- Bagian footer tombol 'Terapkan' dihapus agar UI lebih bersih -->
                </div>
            </div>

            <!-- Checkbox Filter: Fungsi -->
            <div x-data="{ open: false }" class="relative">
                <button type="button" @click="open = !open" class="bg-slate-50 border border-slate-300 text-slate-700 text-xs rounded-xl p-2.5 flex items-center gap-2 hover:bg-slate-100 focus:ring-2 focus:ring-blue-500">
                    <span class="font-medium">Fungsi</span>
                    <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-1.5 py-0.5 rounded-full" x-text="selectedFungsi.length"></span>
                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                </button>

                <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-xl shadow-lg z-50 p-3 space-y-2">
                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider pb-1 border-b border-slate-100">Pilih Fungsi</div>
                    <div class="max-h-56 overflow-y-auto space-y-1.5 pr-1">
                        
                        <!-- Checkbox Pilih Semua - Fungsi -->
                        <label class="flex items-center gap-2 text-xs font-semibold text-blue-700 hover:bg-blue-50 p-1.5 rounded-lg cursor-pointer border-b border-slate-100 mb-1">
                            <input type="checkbox" 
                                :checked="selectedFungsi.length === allFungsiOptions.length && allFungsiOptions.length > 0"
                                @change="toggleAllFungsi($event.target.checked)"
                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span>Pilih Semua</span>
                        </label>

                        @foreach($fungsiOptions as $opt)
                            <label class="flex items-center gap-2 text-xs text-slate-700 hover:bg-slate-50 p-1.5 rounded-lg cursor-pointer select-none">
                                <input type="checkbox" 
                                    name="fungsi[]" 
                                    value="{{ $opt }}" 
                                    x-model="selectedFungsi"
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="truncate">{{ $opt }}</span>
                            </label>
                        @endforeach
                    </div>
                    <!-- Bagian footer tombol 'Terapkan' dihapus agar UI lebih bersih -->
                </div>
            </div>

            <!-- Satu Tombol Submit Utama di Luar -->
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-2.5 rounded-xl font-medium shadow-sm transition flex items-center gap-1.5">
                <i class="fa-solid fa-filter text-xs"></i>
                <span>Filter</span>
            </button>

            <!-- Tombol Export CSV -->
            <a href="{{ route('penerimaan.penjagaan.bulanan.export-detil', request()->all()) }}"            class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs px-3 py-2.5 rounded-xl font-medium shadow-sm transition flex items-center gap-1.5">
                <i class="fa-solid fa-file-csv text-sm"></i>
                <span>Export</span>
            </a>

            @if(request('fungsi') || request('jenis'))
                <a href="{{ route('penerimaan.penjagaan.bulanan') }}" class="text-xs text-rose-500 hover:underline font-medium">Reset Filter</a>
            @endif
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
            labels: {!! json_encode($months) !!},
            datasets: [
                {
                    label: 'Tahun 2026',
                    data: {!! json_encode($data2026) !!},
                    backgroundColor: grad2026,
                    borderRadius: 8,
                    borderSkipped: false,
                    categoryPercentage: 0.6,
                    barPercentage: 0.7
                },
                {
                    label: 'Tahun 2025',
                    data: {!! json_encode($data2025) !!},
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
@endsection