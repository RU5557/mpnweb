@extends('layouts.app')

@section('title', 'Penjagaan vs Bulan Lalu')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Penjagaan vs Bulan Lalu</h1>
            <p class="text-xs text-slate-500">Perbandingan harian 2026 vs Bulan Sebelumnya (s.d 31 hari)</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <!-- Filter Form -->
            <form method="GET" action="{{ route('penerimaan.penjagaan.vsbulanlalu') }}" class="flex flex-wrap items-center gap-3">
                <select name="bulan" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 text-slate-700 text-xs rounded-xl focus:ring-blue-500 focus:border-blue-500 p-2.5 font-medium">
                    @for($m=1; $m<=12; $m++)
                        <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                            Bulan {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                        </option>
                    @endfor
                </select>

                <!-- Checkbox Filter: Fungsi -->
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open" class="bg-slate-50 border border-slate-300 text-slate-700 text-xs rounded-xl p-2.5 flex items-center gap-2 hover:bg-slate-100 focus:ring-2 focus:ring-blue-500">
                        <span class="font-medium">Fungsi</span>
                        <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                            {{ count((array) $fungsi) }}
                        </span>
                        <i class="fa-solid fa-chevron-down text-[10px]"></i>
                    </button>

                    <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-xl shadow-lg z-50 p-3 space-y-2">
                        <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider pb-1 border-b border-slate-100">Pilih Fungsi</div>
                        <div class="max-h-48 overflow-y-auto space-y-1.5 pr-1">
                            @foreach($fungsiOptions as $opt)
                                @php 
                                    $checked = in_array($opt, (array) $fungsi); 
                                @endphp
                                <label class="flex items-center gap-2 text-xs text-slate-700 hover:bg-slate-50 p-1.5 rounded-lg cursor-pointer">
                                    <input type="checkbox" name="fungsi[]" value="{{ $opt }}" {{ $checked ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span class="truncate">{{ $opt }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="pt-2 border-t border-slate-100 text-right">
                            <button type="submit" class="bg-blue-600 text-white text-xs px-3 py-1.5 rounded-lg font-medium hover:bg-blue-700">Terapkan</button>
                        </div>
                    </div>
                </div>

                @if(request()->has('fungsi'))
                    <a href="{{ route('penerimaan.penjagaan.vsbulanlalu', ['bulan' => $bulan]) }}" class="text-xs text-rose-500 hover:underline font-medium">Reset Filter</a>
                @endif
            </form>

            <!-- Tombol Export CSV -->
            <a href="{{ route('penerimaan.penjagaan.export-detil', request()->all()) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium px-3.5 py-2.5 rounded-xl flex items-center gap-2 transition-colors shadow-sm">
                <i class="fa-solid fa-file-csv text-sm"></i>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Chart Container -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <canvas id="chartVsBulanLalu" class="max-h-[420px]"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('chartVsBulanLalu').getContext('2d');

    const bgBulanIni = ctx.createLinearGradient(0, 0, 0, 300);
    bgBulanIni.addColorStop(0, 'rgba(16, 185, 129, 0.25)');
    bgBulanIni.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_map(fn($d) => "$d", $days)) !!},
            datasets: [
                {
                    label: 'Bulan Ini (2026)',
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
                    label: 'Bulan Lalu',
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