@extends('layouts.app')

@section('content')
<!-- HEADER & FILTER CONTAINER -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    
    <!-- Judul & Subjudul -->
    <div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Dashboard Ringkasan</h1>
        <p class="text-sm text-slate-500 mt-1">Overview penerimaan, capaian target, dan performa PPM</p>
    </div>

<!-- Form Filter Compact & Sejajar (Rentang Bulan) -->
<form action="{{ route('penerimaan.dashboard') }}" method="GET" class="bg-white border border-slate-200 rounded-xl p-2 px-3.5 shadow-sm flex items-center gap-2">
    
    <span class="text-xs font-medium text-slate-500">Periode:</span>

    <!-- Select Bulan Awal -->
    <select name="bulan_awal" class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg px-2.5 py-1.5 font-medium focus:ring-2 focus:ring-blue-500 outline-none transition cursor-pointer">
        @foreach(range(1, 12) as $m)
            @php
                $monthName = \Carbon\Carbon::create()->month($m)->translatedFormat('F');
            @endphp
            <option value="{{ $m }}" {{ $blnAwal == $m ? 'selected' : '' }}>
                {{ $monthName }}
            </option>
        @endforeach
    </select>

    <span class="text-xs font-semibold text-slate-400">s.d.</span>

    <!-- Select Bulan Akhir -->
    <select name="bulan_akhir" class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg px-2.5 py-1.5 font-medium focus:ring-2 focus:ring-blue-500 outline-none transition cursor-pointer">
        @foreach(range(1, 12) as $m)
            @php
                $monthName = \Carbon\Carbon::create()->month($m)->translatedFormat('F');
            @endphp
            <option value="{{ $m }}" {{ $blnAkhir == $m ? 'selected' : '' }}>
                {{ $monthName }}
            </option>
        @endforeach
    </select>

    <!-- Select Tahun -->
    <select name="tahun" class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg px-3 py-1.5 font-medium focus:ring-2 focus:ring-blue-500 outline-none transition cursor-pointer ml-1">
        @foreach(range(date('Y') - 3, date('Y')) as $year)
            <option value="{{ $year }}" {{ $thnIni == $year ? 'selected' : '' }}>
                {{ $year }}
            </option>
        @endforeach
    </select>

    <!-- Tombol Terapkan -->
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-3.5 py-1.5 rounded-lg transition shadow-sm flex items-center gap-1.5 ml-1">
        Terapkan
    </button>

    <!-- Tombol Reset -->
    @if(request()->has('bulan_awal') || request()->has('bulan_akhir') || request()->has('tahun') || request()->has('bulan'))
        <a href="{{ route('penerimaan.dashboard') }}" class="text-slate-400 hover:text-slate-600 text-sm px-1.5 py-1.5 transition" title="Reset Filter">
            <i class="fa-solid fa-rotate-left"></i>
        </a>
    @endif

    <!-- Tombol Export Detil Transaksi -->
    <a href="{{ route('dashboard.export-detil', request()->all()) }}" 
       class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-3.5 py-1.5 rounded-lg transition shadow-sm flex items-center gap-2 border border-emerald-600 ml-1">
        <i class="fa-solid fa-file-excel text-xs"></i>
        <span>Export CSV</span>
    </a>
</form>
</div>

<!-- BARIS 1: CARD RINGKASAN UTAMA (3 KOLOM) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    
    <!-- Penerimaan Saat Ini -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200/80 border-l-4 border-l-blue-600 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Penerimaan Saat Ini</span>
                <span class="bg-blue-50 text-blue-700 border border-blue-200/60 text-xs font-semibold px-3 py-1 rounded-md">
                    {{ number_format($capaianKantor, 1, ',', '.') }}% Capaian
                </span>
            </div>

            <div class="text-2xl md:text-3xl font-bold text-slate-900 my-2 tracking-normal tabular-nums">
                <span class="text-slate-500 text-xl font-normal">Rp</span> {{ number_format($penerimaanSaatIni ?? 0, 0, ',', '.') }}
            </div>
        </div>
        <div class="flex items-center gap-4 text-xs font-medium text-slate-600 pt-4 border-t border-slate-100">
            @php
                $growthMoM = ($penerimaanBlnLalu ?? 0) > 0 ? (($penerimaanSaatIni - $penerimaanBlnLalu) / $penerimaanBlnLalu) * 100 : 0;
                $growthYoY = ($penerimaanThnLalu ?? 0) > 0 ? (($penerimaanSaatIni - $penerimaanThnLalu) / $penerimaanThnLalu) * 100 : 0;
            @endphp
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
    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200/80 border-l-4 border-l-sky-500 flex flex-col justify-between">
        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Penerimaan Bulan Lalu</span>
        <div class="text-2xl md:text-3xl font-bold text-slate-900 my-2 tracking-normal tabular-nums">
            <span class="text-slate-500 text-xl font-normal">Rp</span> {{ number_format($penerimaanBlnLalu ?? 0, 0, ',', '.') }}
        </div>
        <div class="text-xs font-normal text-slate-400 border-t border-slate-100 pt-4">
            Pembanding bulan sebelumnya
        </div>
    </div>

    <!-- Penerimaan Tahun Lalu -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200/80 border-l-4 border-l-amber-500 flex flex-col justify-between">
        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Penerimaan Tahun Lalu</span>
        <div class="text-2xl md:text-3xl font-bold text-slate-900 my-2 tracking-normal tabular-nums">
            <span class="text-slate-500 text-xl font-normal">Rp</span> {{ number_format($penerimaanThnLalu ?? 0, 0, ',', '.') }}
        </div>
        <div class="text-xs font-normal text-slate-400 border-t border-slate-100 pt-4">
            Pembanding tahun lalu (YoY)
        </div>
    </div>

</div>

<!-- BARIS 2 & 3: GRID PERFORMANCE CARD (3 KOLOM x 2 BARIS) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <!-- Card 1: PPM -->
    @php
        $targetPpm = $target->target_ppm ?? 0;
        $persenPPM = $targetPpm > 0 ? (($realisasiPPM ?? 0) / $targetPpm) * 100 : 0;
        $growthPPM = ($realisasiPPMLalu ?? 0) > 0 ? ((($realisasiPPM ?? 0) - $realisasiPPMLalu) / $realisasiPPMLalu) * 100 : 0;
    @endphp
    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">PPM</h3>
                    <p class="text-xs text-slate-400 font-normal">Capaian Target</p>
                </div>
                <span class="bg-blue-50 text-blue-700 border border-blue-200/60 font-semibold text-sm px-3 py-1 rounded-md tracking-normal tabular-nums">
                    {{ number_format($persenPPM, 1, ',', '.') }}%
                </span>
            </div>
            <div class="space-y-2.5 text-sm text-slate-600 border-t pt-4 border-slate-100">
                <div class="flex justify-between">
                    <span>Target:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($targetPpm, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Realisasi:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($realisasiPPM ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="text-xs pt-4 mt-4 border-t border-slate-100 flex justify-between items-center font-medium">
            <span class="text-slate-500">Pertumbuhan YoY:</span>
            <span class="{{ $growthPPM >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-semibold tabular-nums">
                <i class="fa-solid {{ $growthPPM >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i> 
                {{ number_format(abs($growthPPM), 2, ',', '.') }}%
            </span>
        </div>
    </div>

    <!-- Card 2: PKM -->
    @php
        $targetPkm = $target->target_pkm ?? 0;
        $persenPKM = $targetPkm > 0 ? (($realisasiPKM ?? 0) / $targetPkm) * 100 : 0;
        $growthPKM = ($realisasiPKMLalu ?? 0) > 0 ? ((($realisasiPKM ?? 0) - $realisasiPKMLalu) / $realisasiPKMLalu) * 100 : 0;
    @endphp
    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">PKM</h3>
                    <p class="text-xs text-slate-400 font-normal">Capaian Target</p>
                </div>
                <span class="bg-sky-50 text-sky-700 border border-sky-200/60 font-semibold text-sm px-3 py-1 rounded-md tracking-normal tabular-nums">
                    {{ number_format($persenPKM, 1, ',', '.') }}%
                </span>
            </div>
            <div class="space-y-2.5 text-sm text-slate-600 border-t pt-4 border-slate-100">
                <div class="flex justify-between">
                    <span>Target:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($targetPkm, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Realisasi:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($realisasiPKM ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="text-xs pt-4 mt-4 border-t border-slate-100 flex justify-between items-center font-medium">
            <span class="text-slate-500">Pertumbuhan YoY:</span>
            <span class="{{ $growthPKM >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-semibold tabular-nums">
                <i class="fa-solid {{ $growthPKM >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i> 
                {{ number_format(abs($growthPKM), 2, ',', '.') }}%
            </span>
        </div>
    </div>

    <!-- Card 3: PBP -->
    @php
        $targetPbp = $target->target_pbp ?? 0;
        $persenPBP = $targetPbp > 0 ? (($realisasiPBP ?? 0) / $targetPbp) * 100 : 0;
        $sisaPBP = max(0, $targetPbp - ($realisasiPBP ?? 0));
    @endphp
    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">PBP</h3>
                    <p class="text-xs text-slate-400 font-normal">Capaian Target</p>
                </div>
                <span class="bg-amber-50 text-amber-700 border border-amber-200/60 font-semibold text-sm px-3 py-1 rounded-md tracking-normal tabular-nums">
                    {{ number_format($persenPBP, 1, ',', '.') }}%
                </span>
            </div>
            <div class="space-y-2.5 text-sm text-slate-600 border-t pt-4 border-slate-100">
                <div class="flex justify-between">
                    <span>Target:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($targetPbp, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Realisasi:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($realisasiPBP ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        
        <div class="text-xs pt-4 mt-4 border-t border-slate-100 flex justify-between items-center font-medium">
            <span class="text-slate-500">Sisa Target:</span>
            <span class="text-rose-600 font-semibold tabular-nums">
                Rp {{ number_format($sisaPBP, 0, ',', '.') }}
            </span>
        </div>
    </div>

    <!-- Card 4: PKM Pengawasan -->
    @php
        $targetPengawasan = $target->target_pkm_pengawasan ?? 0;
        $persenPengawasan = $targetPengawasan > 0 ? (($realisasiPengawasan ?? 0) / $targetPengawasan) * 100 : 0;
        $growthPengawasan = ($realisasiPengawasanLalu ?? 0) > 0 ? ((($realisasiPengawasan ?? 0) - $realisasiPengawasanLalu) / $realisasiPengawasanLalu) * 100 : 0;
    @endphp
    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">PKM Pengawasan</h3>
                    <p class="text-xs text-slate-400 font-normal">Capaian Target</p>
                </div>
                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200/60 font-semibold text-sm px-3 py-1 rounded-md tracking-normal tabular-nums">
                    {{ number_format($persenPengawasan, 1, ',', '.') }}%
                </span>
            </div>
            <div class="space-y-2.5 text-sm text-slate-600 border-t pt-4 border-slate-100">
                <div class="flex justify-between">
                    <span>Target:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($targetPengawasan, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Realisasi:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($realisasiPengawasan ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="text-xs pt-4 mt-4 border-t border-slate-100 flex justify-between items-center font-medium">
            <span class="text-slate-500">Pertumbuhan YoY:</span>
            <span class="{{ $growthPengawasan >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-semibold tabular-nums">
                <i class="fa-solid {{ $growthPengawasan >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i> 
                {{ number_format(abs($growthPengawasan), 2, ',', '.') }}%
            </span>
        </div>
    </div>

    <!-- Card 5: PKM Pemeriksaan -->
    @php
        $targetPemeriksaan = $target->target_pkm_pemeriksaan ?? 0;
        $persenPemeriksaan = $targetPemeriksaan > 0 ? (($realisasiPemeriksaan ?? 0) / $targetPemeriksaan) * 100 : 0;
        $growthPemeriksaan = ($realisasiPemeriksaanLalu ?? 0) > 0 ? ((($realisasiPemeriksaan ?? 0) - $realisasiPemeriksaanLalu) / $realisasiPemeriksaanLalu) * 100 : 0;
    @endphp
    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">PKM Pemeriksaan</h3>
                    <p class="text-xs text-slate-400 font-normal">Capaian Target</p>
                </div>
                <span class="bg-slate-100 text-slate-700 border border-slate-200 font-semibold text-sm px-3 py-1 rounded-md tracking-normal tabular-nums">
                    {{ number_format($persenPemeriksaan, 1, ',', '.') }}%
                </span>
            </div>
            <div class="space-y-2.5 text-sm text-slate-600 border-t pt-4 border-slate-100">
                <div class="flex justify-between">
                    <span>Target:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($targetPemeriksaan, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Realisasi:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($realisasiPemeriksaan ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="text-xs pt-4 mt-4 border-t border-slate-100 flex justify-between items-center font-medium">
            <span class="text-slate-500">Pertumbuhan YoY:</span>
            <span class="{{ $growthPemeriksaan >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-semibold tabular-nums">
                <i class="fa-solid {{ $growthPemeriksaan >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i> 
                {{ number_format(abs($growthPemeriksaan), 2, ',', '.') }}%
            </span>
        </div>
    </div>

    <!-- Card 6: PKM Penagihan -->
    @php
        $targetPenagihan = $target->target_pkm_penagihan ?? 0;
        $persenPenagihan = $targetPenagihan > 0 ? (($realisasiPenagihan ?? 0) / $targetPenagihan) * 100 : 0;
        $growthPenagihan = ($realisasiPenagihanLalu ?? 0) > 0 ? ((($realisasiPenagihan ?? 0) - $realisasiPenagihanLalu) / $realisasiPenagihanLalu) * 100 : 0;
    @endphp
    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">PKM Penagihan</h3>
                    <p class="text-xs text-slate-400 font-normal">Capaian Target</p>
                </div>
                <span class="bg-slate-100 text-slate-800 border border-slate-200 font-semibold text-sm px-3 py-1 rounded-md tracking-normal tabular-nums">
                    {{ number_format($persenPenagihan, 1, ',', '.') }}%
                </span>
            </div>
            <div class="space-y-2.5 text-sm text-slate-600 border-t pt-4 border-slate-100">
                <div class="flex justify-between">
                    <span>Target:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($targetPenagihan, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Realisasi:</span> 
                    <span class="font-semibold text-slate-800 tabular-nums">Rp {{ number_format($realisasiPenagihan ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="text-xs pt-4 mt-4 border-t border-slate-100 flex justify-between items-center font-medium">
            <span class="text-slate-500">Pertumbuhan YoY:</span>
            <span class="{{ $growthPenagihan >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-semibold tabular-nums">
                <i class="fa-solid {{ $growthPenagihan >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i> 
                {{ number_format(abs($growthPenagihan), 2, ',', '.') }}%
            </span>
        </div>
    </div>

</div>
@endsection