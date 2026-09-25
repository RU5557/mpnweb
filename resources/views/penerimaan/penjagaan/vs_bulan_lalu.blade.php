@extends('layouts.app')

@section('title', 'Penjagaan vs Bulan Lalu - MPNWEB')

@section('content')
<div class="space-y-6"
     x-data="{
        allFungsiOptions: {{ json_encode($fungsiOptions->toArray()) }},
        selectedFungsi: {{ json_encode(array_values($fungsi)) }},
        toggleAllFungsi(checked) {
            this.selectedFungsi = checked ? [...this.allFungsiOptions] : [];
        }
     }">
    
    <!-- HEADER & FILTER CONTAINER -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Penjagaan vs Bulan Lalu</h1>
            <p class="text-xs text-slate-500 mt-1">Perbandingan harian {{ $tahunIni }} vs Bulan Sebelumnya (s.d 31 hari)</p>
        </div>
        
        <form method="GET" action="{{ route('penerimaan.penjagaan.vs-bulan-lalu') }}" class="bg-white border border-slate-200 rounded-xl p-2 px-3 shadow-sm flex flex-wrap items-center gap-2">
            <select name="bulan" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-lg px-2.5 py-1.5 font-medium focus:ring-2 focus:ring-blue-500 outline-none transition cursor-pointer">
                @for($m=1; $m<=12; $m++)
                    <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                        {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                    </option>
                @endfor
            </select>

            <!-- Alpine Multi-select Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button type="button" @click="open = !open" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-lg px-2.5 py-1.5 flex items-center gap-2 hover:bg-slate-100 focus:ring-2 focus:ring-blue-500 font-medium">
                    <span>Fungsi</span>
                    <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-1.5 py-0.5 rounded-full" x-text="selectedFungsi.length"></span>
                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                </button>

                <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-xl shadow-lg z-50 p-3 space-y-2">
                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider pb-1 border-b border-slate-100">Pilih Fungsi</div>
                    <div class="max-h-56 overflow-y-auto space-y-1.5 pr-1">
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
                </div>
            </div>

            <!-- Tombol Terapkan -->
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3.5 py-1.5 rounded-lg transition shadow-sm flex items-center gap-1.5">
                <span>Terapkan</span>
            </button>

            <!-- Tombol Reset -->
            @if(request()->has('fungsi'))
                <a href="{{ route('penerimaan.penjagaan.vs-bulan-lalu', ['bulan' => $bulan]) }}" class="text-slate-400 hover:text-slate-600 text-xs px-1.5 py-1.5 transition" title="Reset Filter">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif

            <!-- Tombol Export -->
            <a href="{{ route('penerimaan.penjagaan.vs-bulan-lalu.export-detil', request()->all()) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3.5 py-1.5 rounded-lg transition shadow-sm flex items-center gap-1.5 border border-emerald-600">
                <i class="fa-solid fa-file-excel text-xs"></i>
                <span>Export CSV</span>
            </a>
        </form>
    </div>

    <!-- Container Chart -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
        <div class="relative w-full h-[400px]">
            <canvas id="chartVsBulanLalu"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const el = document.getElementById('chartVsBulanLalu');
        if (!el) return;

        const ctx = el.getContext('2d');

        const bgBulanIni = ctx.createLinearGradient(0, 0, 0, 300);
        bgBulanIni.addColorStop(0, 'rgba(16, 185, 129, 0.25)');
        bgBulanIni.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_map(fn($d) => "$d", $days)) !!},
                datasets: [
                    {
                        label: 'Bulan Ini ({{ $tahunIni }})',
                        data: {!! json_encode($dataBulanIni) !!},
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: bgBulanIni,
                        borderWidth: 2.5,
                        pointRadius: 2,
                        pointHoverRadius: 6,
                        tension: 0.35,
                        fill: true
                    },
                    {
                        label: 'Bulan Lalu ({{ $tahunBulanLalu }})',
                        data: {!! json_encode($dataBulanLalu) !!},
                        borderColor: 'rgb(245, 158, 11)',
                        backgroundColor: 'rgba(245, 158, 11, 0.05)',
                        borderWidth: 2,
                        pointRadius: 2,
                        pointHoverRadius: 6,
                        tension: 0.35,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: 'top',
                        labels: { usePointStyle: true, boxWidth: 8, font: { size: 12, weight: '600' } }
                    },
                    tooltip: {
                        padding: 12,
                        cornerRadius: 8,
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
    });
</script>
@endpush