@extends('layouts.app')

@section('content')
<!-- HEADER & FILTER CONTAINER -->
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
    
    <!-- Judul & Subjudul -->
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Dashboard Ringkasan</h1>
        <p class="text-xs text-slate-500 mt-1">Overview penerimaan, capaian target, dan performa PPM</p>
    </div>

    <!-- Form Filter Compact & Sejajar -->
    <form action="{{ route('penerimaan.dashboard') }}" method="GET" class="bg-white border border-slate-200 rounded-xl p-2 px-3 shadow-sm flex flex-wrap items-center gap-2">
        
        <span class="text-xs font-medium text-slate-500">Periode:</span>

        <!-- Select Bulan Awal -->
        <select name="bulan_awal" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-lg px-2.5 py-1.5 font-medium focus:ring-2 focus:ring-blue-500 outline-none transition cursor-pointer">
            @foreach($listBulan as $m => $namaBulan)
                <option value="{{ $m }}" {{ $blnAwal == $m ? 'selected' : '' }}>
                    {{ $namaBulan }}
                </option>
            @endforeach
        </select>

        <span class="text-xs font-semibold text-slate-400">s.d.</span>

        <!-- Select Bulan Akhir -->
        <select name="bulan_akhir" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-lg px-2.5 py-1.5 font-medium focus:ring-2 focus:ring-blue-500 outline-none transition cursor-pointer">
            @foreach($listBulan as $m => $namaBulan)
                <option value="{{ $m }}" {{ $blnAkhir == $m ? 'selected' : '' }}>
                    {{ $namaBulan }}
                </option>
            @endforeach
        </select>

        <!-- Select Tahun -->
        <select name="tahun" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-lg px-2.5 py-1.5 font-medium focus:ring-2 focus:ring-blue-500 outline-none transition cursor-pointer">
            @foreach(range(date('Y') - 3, date('Y')) as $year)
                <option value="{{ $year }}" {{ $thnIni == $year ? 'selected' : '' }}>
                    {{ $year }}
                </option>
            @endforeach
        </select>

        <!-- Tombol Terapkan -->
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3.5 py-1.5 rounded-lg transition shadow-sm flex items-center gap-1.5">
            <span>Terapkan</span>
        </button>

        <!-- Tombol Reset -->
        @if(request()->has('bulan_awal') || request()->has('bulan_akhir') || request()->has('tahun') || request()->has('bulan'))
            <a href="{{ route('penerimaan.dashboard') }}" class="text-slate-400 hover:text-slate-600 text-xs px-1.5 py-1.5 transition" title="Reset Filter">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        @endif

        <!-- Tombol Export Detil Transaksi -->
        <a href="{{ route('dashboard.export-detil', request()->all()) }}" 
           class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3.5 py-1.5 rounded-lg transition shadow-sm flex items-center gap-1.5 border border-emerald-600">
            <i class="fa-solid fa-file-excel text-xs"></i>
            <span>Export CSV</span>
        </a>
    </form>
</div>

<!-- BARIS 1: CARD RINGKASAN UTAMA (3 KOLOM) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    
    <!-- Penerimaan Saat Ini -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200 border-l-4 border-l-blue-600 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Penerimaan Saat Ini</span>
                <span class="bg-blue-50 text-blue-700 border border-blue-200 text-xs font-semibold px-2.5 py-0.5 rounded-md">
                    {{ number_format($capaianKantor, 1, ',', '.') }}% Capaian
                </span>
            </div>

            <div class="text-2xl font-bold text-slate-900 my-2 tracking-normal tabular-nums">
                <span class="text-slate-500 text-lg font-normal">Rp</span> {{ number_format($penerimaanSaatIni, 0, ',', '.') }}
            </div>
        </div>
        <div class="flex items-center gap-4 text-xs font-medium text-slate-600 pt-3 border-t border-slate-100">
            <span>MoM: 
                <span class="{{ $growthMoM >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-semibold tabular-nums">
                    <i class="fa-solid {{ $growthMoM >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i> 
                    {{ number_format(abs($growthMoM), 2, ',', '.') }}%
                </span>
            </span>
            <span>YoY: 
                <span class="{{ $growthYoY >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-semibold tabular-nums">
                    <i class="fa-solid {{ $growthYoY >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i> 
                    {{ number_format(abs($growthYoY), 2, ',', '.') }}%
                </span>
            </span>
        </div>
    </div>

    <!-- Penerimaan Bulan Lalu -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200 border-l-4 border-l-sky-500 flex flex-col justify-between">
        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Penerimaan Bulan Lalu</span>
        <div class="text-2xl font-bold text-slate-900 my-2 tracking-normal tabular-nums">
            <span class="text-slate-500 text-lg font-normal">Rp</span> {{ number_format($penerimaanBlnLalu, 0, ',', '.') }}
        </div>
        <div class="text-xs font-normal text-slate-400 border-t border-slate-100 pt-3">
            Pembanding bulan sebelumnya
        </div>
    </div>

    <!-- Penerimaan Tahun Lalu -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200 border-l-4 border-l-amber-500 flex flex-col justify-between">
        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Penerimaan Tahun Lalu</span>
        <div class="text-2xl font-bold text-slate-900 my-2 tracking-normal tabular-nums">
            <span class="text-slate-500 text-lg font-normal">Rp</span> {{ number_format($penerimaanThnLalu, 0, ',', '.') }}
        </div>
        <div class="text-xs font-normal text-slate-400 border-t border-slate-100 pt-3">
            Pembanding tahun lalu (YoY)
        </div>
    </div>

</div>

<!-- BARIS 2 & 3: GRID PERFORMANCE CARD (3 KOLOM x 2 BARIS) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <!-- Card 1: PPM -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">PPM</h3>
                    <p class="text-xs text-slate-400 font-normal">Capaian Target</p>
                </div>
                <span class="bg-blue-50 text-blue-700 border border-blue-200 font-semibold text-xs px-2.5 py-0.5 rounded-md tracking-normal tabular-nums">
                    {{ number_format($metrics['ppm']['persen'], 1, ',', '.') }}%
                </span>
            </div>
            <div class="space-y-2 text-xs text-slate-600 border-t pt-3 border-slate-100">
                <div class="flex justify-between">
                    <span>Target:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($metrics['ppm']['target'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Realisasi:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($metrics['ppm']['realisasi'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="text-xs pt-3 mt-3 border-t border-slate-100 flex justify-between items-center font-medium">
            <span class="text-slate-500">Pertumbuhan YoY:</span>
            <span class="{{ $metrics['ppm']['growthYoY'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-semibold tabular-nums">
                <i class="fa-solid {{ $metrics['ppm']['growthYoY'] >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i> 
                {{ number_format(abs($metrics['ppm']['growthYoY']), 2, ',', '.') }}%
            </span>
        </div>
    </div>

    <!-- Card 2: PKM -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">PKM</h3>
                    <p class="text-xs text-slate-400 font-normal">Capaian Target</p>
                </div>
                <span class="bg-sky-50 text-sky-700 border border-sky-200 font-semibold text-xs px-2.5 py-0.5 rounded-md tracking-normal tabular-nums">
                    {{ number_format($metrics['pkm']['persen'], 1, ',', '.') }}%
                </span>
            </div>
            <div class="space-y-2 text-xs text-slate-600 border-t pt-3 border-slate-100">
                <div class="flex justify-between">
                    <span>Target:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($metrics['pkm']['target'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Realisasi:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($metrics['pkm']['realisasi'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="text-xs pt-3 mt-3 border-t border-slate-100 flex justify-between items-center font-medium">
            <span class="text-slate-500">Pertumbuhan YoY:</span>
            <span class="{{ $metrics['pkm']['growthYoY'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-semibold tabular-nums">
                <i class="fa-solid {{ $metrics['pkm']['growthYoY'] >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i> 
                {{ number_format(abs($metrics['pkm']['growthYoY']), 2, ',', '.') }}%
            </span>
        </div>
    </div>

    <!-- Card 3: PBP -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">PBP</h3>
                    <p class="text-xs text-slate-400 font-normal">Capaian Target</p>
                </div>
                <span class="bg-amber-50 text-amber-700 border border-amber-200 font-semibold text-xs px-2.5 py-0.5 rounded-md tracking-normal tabular-nums">
                    {{ number_format($metrics['pbp']['persen'], 1, ',', '.') }}%
                </span>
            </div>
            <div class="space-y-2 text-xs text-slate-600 border-t pt-3 border-slate-100">
                <div class="flex justify-between">
                    <span>Target:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($metrics['pbp']['target'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Realisasi:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($metrics['pbp']['realisasi'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        
        <div class="text-xs pt-3 mt-3 border-t border-slate-100 flex justify-between items-center font-medium">
            <span class="text-slate-500">Sisa Target:</span>
            <span class="text-rose-600 font-semibold tabular-nums">
                Rp {{ number_format($metrics['pbp']['sisa'], 0, ',', '.') }}
            </span>
        </div>
    </div>

    <!-- Card 4: PKM Pengawasan -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">PKM Pengawasan</h3>
                    <p class="text-xs text-slate-400 font-normal">Capaian Target</p>
                </div>
                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold text-xs px-2.5 py-0.5 rounded-md tracking-normal tabular-nums">
                    {{ number_format($metrics['pengawasan']['persen'], 1, ',', '.') }}%
                </span>
            </div>
            <div class="space-y-2 text-xs text-slate-600 border-t pt-3 border-slate-100">
                <div class="flex justify-between">
                    <span>Target:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($metrics['pengawasan']['target'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Realisasi:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($metrics['pengawasan']['realisasi'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="text-xs pt-3 mt-3 border-t border-slate-100 flex justify-between items-center font-medium">
            <span class="text-slate-500">Pertumbuhan YoY:</span>
            <span class="{{ $metrics['pengawasan']['growthYoY'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-semibold tabular-nums">
                <i class="fa-solid {{ $metrics['pengawasan']['growthYoY'] >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i> 
                {{ number_format(abs($metrics['pengawasan']['growthYoY']), 2, ',', '.') }}%
            </span>
        </div>
    </div>

    <!-- Card 5: PKM Pemeriksaan -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">PKM Pemeriksaan</h3>
                    <p class="text-xs text-slate-400 font-normal">Capaian Target</p>
                </div>
                <span class="bg-slate-100 text-slate-700 border border-slate-200 font-semibold text-xs px-2.5 py-0.5 rounded-md tracking-normal tabular-nums">
                    {{ number_format($metrics['pemeriksaan']['persen'], 1, ',', '.') }}%
                </span>
            </div>
            <div class="space-y-2 text-xs text-slate-600 border-t pt-3 border-slate-100">
                <div class="flex justify-between">
                    <span>Target:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($metrics['pemeriksaan']['target'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Realisasi:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($metrics['pemeriksaan']['realisasi'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="text-xs pt-3 mt-3 border-t border-slate-100 flex justify-between items-center font-medium">
            <span class="text-slate-500">Pertumbuhan YoY:</span>
            <span class="{{ $metrics['pemeriksaan']['growthYoY'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-semibold tabular-nums">
                <i class="fa-solid {{ $metrics['pemeriksaan']['growthYoY'] >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i> 
                {{ number_format(abs($metrics['pemeriksaan']['growthYoY']), 2, ',', '.') }}%
            </span>
        </div>
    </div>

    <!-- Card 6: PKM Penagihan -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">PKM Penagihan</h3>
                    <p class="text-xs text-slate-400 font-normal">Capaian Target</p>
                </div>
                <span class="bg-slate-100 text-slate-800 border border-slate-200 font-semibold text-xs px-2.5 py-0.5 rounded-md tracking-normal tabular-nums">
                    {{ number_format($metrics['penagihan']['persen'], 1, ',', '.') }}%
                </span>
            </div>
            <div class="space-y-2 text-xs text-slate-600 border-t pt-3 border-slate-100">
                <div class="flex justify-between">
                    <span>Target:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($metrics['penagihan']['target'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Realisasi:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($metrics['penagihan']['realisasi'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="text-xs pt-3 mt-3 border-t border-slate-100 flex justify-between items-center font-medium">
            <span class="text-slate-500">Pertumbuhan YoY:</span>
            <span class="{{ $metrics['penagihan']['growthYoY'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-semibold tabular-nums">
                <i class="fa-solid {{ $metrics['penagihan']['growthYoY'] >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i> 
                {{ number_format(abs($metrics['penagihan']['growthYoY']), 2, ',', '.') }}%
            </span>
        </div>
    </div>

</div>
@endsection