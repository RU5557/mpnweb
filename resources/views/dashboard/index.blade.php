@extends('layouts.app')

@section('content')
<!-- Header Halaman -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Dashboard Ringkasan</h1>
    <p class="text-slate-500 text-sm">Overview penerimaan, capaian target, dan performa PKM</p>
</div>

<!-- FILTER MONTH & YEAR (TAILWIND VERSION) -->
<div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 mb-6">
    <form action="{{ route('dashboard') }}" method="GET" class="flex flex-wrap items-end gap-4">
        
        <!-- Filter Bulan -->
        <div class="w-full sm:w-48">
            <label for="bulan" class="block text-xs font-bold text-slate-700 mb-1">Bulan</label>
            <select name="bulan" id="bulan" class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2 font-medium">
                @php
                    $namaBulan = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                @endphp
                @foreach($namaBulan as $k => $v)
                    <option value="{{ $k }}" {{ (request('bulan', $blnIni) == $k) ? 'selected' : '' }}>
                        {{ $v }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Filter Tahun -->
        <div class="w-full sm:w-36">
            <label for="tahun" class="block text-xs font-bold text-slate-700 mb-1">Tahun</label>
            <select name="tahun" id="tahun" class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2 font-medium">
                @php
                    $tahunSekarang = (int) date('Y');
                @endphp
                @for($i = $tahunSekarang; $i >= $tahunSekarang - 5; $i--)
                    <option value="{{ $i }}" {{ (request('tahun', $thnIni) == $i) ? 'selected' : '' }}>
                        {{ $i }}
                    </option>
                @endfor
            </select>
        </div>

        <!-- Tombol Action -->
        <div class="flex items-center gap-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-filter text-xs"></i> Terapkan
            </button>
            <a href="{{ route('dashboard') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
                Reset
            </a>
        </div>

    </form>
</div>

<!-- BARIS 1: CARD RINGKASAN UTAMA (3 KOLOM) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    
    <!-- Penerimaan Saat Ini -->
    <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-blue-600">
        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Penerimaan Saat Ini</span>
        <div class="text-2xl font-extrabold text-blue-600 my-2">
            Rp {{ number_format($penerimaanSaatIni ?? 0, 0, ',', '.') }}
        </div>
        <div class="flex items-center gap-4 text-xs font-semibold text-slate-600 pt-3 border-t border-slate-100">
            @php
                $growthMoM = ($penerimaanBlnLalu ?? 0) > 0 ? (($penerimaanSaatIni - $penerimaanBlnLalu) / $penerimaanBlnLalu) * 100 : 0;
                $growthYoY = ($penerimaanThnLalu ?? 0) > 0 ? (($penerimaanSaatIni - $penerimaanThnLalu) / $penerimaanThnLalu) * 100 : 0;
            @endphp
            <span>Bulan Lalu: 
                <span class="{{ $growthMoM >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-bold">
                    <i class="fa-solid {{ $growthMoM >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }} text-[10px]"></i> 
                    {{ number_format(abs($growthMoM), 2, ',', '.') }}%
                </span>
            </span>
            <span>Tahun Lalu: 
                <span class="{{ $growthYoY >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-bold">
                    <i class="fa-solid {{ $growthYoY >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }} text-[10px]"></i> 
                    {{ number_format(abs($growthYoY), 2, ',', '.') }}%
                </span>
            </span>
        </div>
    </div>

    <!-- Penerimaan Bulan Lalu -->
    <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-sky-400">
        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Penerimaan Bulan Lalu</span>
        <div class="text-2xl font-extrabold text-sky-400 my-2">
            Rp {{ number_format($penerimaanBlnLalu ?? 0, 0, ',', '.') }}
        </div>
    </div>

    <!-- Penerimaan Tahun Lalu -->
    <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-amber-400">
        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Penerimaan Tahun Lalu</span>
        <div class="text-2xl font-extrabold text-amber-400 my-2">
            Rp {{ number_format($penerimaanThnLalu ?? 0, 0, ',', '.') }}
        </div>
    </div>

</div>

<!-- BARIS 2 & 3: GRID PERFORMANCE CARD (3 KOLOM x 2 BARIS) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-5">

    <!-- Card 1: PPM -->
    @php
        $targetPpm = $target->target_ppm ?? 0;
        $persenPPM = $targetPpm > 0 ? (($realisasiPPM ?? 0) / $targetPpm) * 100 : 0;
    @endphp
<div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-base">PPM</h3>
                <p class="text-xs text-slate-400">Capaian Target</p>
            </div>
            <span class="bg-blue-600 text-white font-bold text-xs px-2.5 py-1 rounded-md shadow-sm">
                {{ number_format($persenPPM, 1, ',', '.') }}%
            </span>
        </div>
        <div class="space-y-2 text-xs text-slate-600 border-t pt-3 border-slate-100">
            <div class="flex justify-between"><span>Target:</span> <span class="font-bold text-slate-800">Rp {{ number_format($targetPpm, 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span>Realisasi:</span> <span class="font-bold text-slate-800">Rp {{ number_format($realisasiPPM ?? 0, 0, ',', '.') }}</span></div>
        </div>
    </div>

    <!-- Card 2: PKM -->
    @php
        $targetPkm = $target->target_pkm ?? 0;
        $persenPKM = $targetPkm > 0 ? (($realisasiPKM ?? 0) / $targetPkm) * 100 : 0;
    @endphp
<div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-base">PKM</h3>
                <p class="text-xs text-slate-400">Capaian Target</p>
            </div>
            <span class="bg-sky-400 text-white font-bold text-xs px-2.5 py-1 rounded-md shadow-sm">
                {{ number_format($persenPKM, 1, ',', '.') }}%
            </span>
        </div>
        <div class="space-y-2 text-xs text-slate-600 border-t pt-3 border-slate-100">
            <div class="flex justify-between"><span>Target:</span> <span class="font-bold text-slate-800">Rp {{ number_format($targetPkm, 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span>Realisasi:</span> <span class="font-bold text-slate-800">Rp {{ number_format($realisasiPKM ?? 0, 0, ',', '.') }}</span></div>
        </div>
    </div>

    <!-- Card 3: PBP -->
    @php
        $targetPbp = $target->target_pbp ?? 0;
        $persenPBP = $targetPbp > 0 ? (($realisasiPBP ?? 0) / $targetPbp) * 100 : 0;
        $sisaPBP = $targetPbp - ($realisasiPBP ?? 0);
    @endphp
<div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-base">PBP</h3>
                <p class="text-xs text-slate-400">Capaian Target</p>
            </div>
            <span class="bg-amber-400 text-white font-bold text-xs px-2.5 py-1 rounded-md shadow-sm">
                {{ number_format($persenPBP, 1, ',', '.') }}%
            </span>
        </div>
        <div class="space-y-2 text-xs text-slate-600 border-t pt-3 border-slate-100">
            <div class="flex justify-between"><span>Target:</span> <span class="font-bold text-slate-800">Rp {{ number_format($targetPbp, 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span>Realisasi:</span> <span class="font-bold text-slate-800">Rp {{ number_format($realisasiPBP ?? 0, 0, ',', '.') }}</span></div>
            <div class="flex justify-between text-rose-500 pt-1 font-semibold">
                <span>Sisa Target:</span> 
                <span>Rp {{ number_format(max(0, $targetPbp - ($realisasiPBP ?? 0)), 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Card 4: PKM Pengawasan -->
    @php
        $targetPengawasan = $target->target_pkm_pengawasan ?? 0;
        $persenPengawasan = $targetPengawasan > 0 ? (($realisasiPengawasan ?? 0) / $targetPengawasan) * 100 : 0;
    @endphp
<div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-base">PKM Pengawasan</h3>
                <p class="text-xs text-slate-400">Capaian Target</p>
            </div>
            <span class="bg-emerald-600 text-white font-bold text-xs px-2.5 py-1 rounded-md shadow-sm">
                {{ number_format($persenPengawasan, 1, ',', '.') }}%
            </span>
        </div>
        <div class="space-y-2 text-xs text-slate-600 border-t pt-3 border-slate-100">
            <div class="flex justify-between"><span>Target:</span> <span class="font-bold text-slate-800">Rp {{ number_format($targetPengawasan, 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span>Realisasi:</span> <span class="font-bold text-slate-800">Rp {{ number_format($realisasiPengawasan ?? 0, 0, ',', '.') }}</span></div>
        </div>
    </div>

    <!-- Card 5: PKM Pemeriksaan -->
    @php
        $targetPemeriksaan = $target->target_pkm_pemeriksaan ?? 0;
        $persenPemeriksaan = $targetPemeriksaan > 0 ? (($realisasiPemeriksaan ?? 0) / $targetPemeriksaan) * 100 : 0;
    @endphp
<div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-base">PKM Pemeriksaan</h3>
                <p class="text-xs text-slate-400">Capaian Target</p>
            </div>
            <span class="bg-slate-600 text-white font-bold text-xs px-2.5 py-1 rounded-md shadow-sm">
                {{ number_format($persenPemeriksaan, 1, ',', '.') }}%
            </span>
        </div>
        <div class="space-y-2 text-xs text-slate-600 border-t pt-3 border-slate-100">
            <div class="flex justify-between"><span>Target:</span> <span class="font-bold text-slate-800">Rp {{ number_format($targetPemeriksaan, 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span>Realisasi:</span> <span class="font-bold text-slate-800">Rp {{ number_format($realisasiPemeriksaan ?? 0, 0, ',', '.') }}</span></div>
        </div>
    </div>

    <!-- Card 6: PKM Penagihan -->
    @php
        $targetPenagihan = $target->target_pkm_penagihan ?? 0;
        $persenPenagihan = $targetPenagihan > 0 ? (($realisasiPenagihan ?? 0) / $targetPenagihan) * 100 : 0;
    @endphp
<div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-base">PKM Penagihan</h3>
                <p class="text-xs text-slate-400">Capaian Target</p>
            </div>
            <span class="bg-slate-900 text-white font-bold text-xs px-2.5 py-1 rounded-md shadow-sm">
                {{ number_format($persenPenagihan, 1, ',', '.') }}%
            </span>
        </div>
        <div class="space-y-2 text-xs text-slate-600 border-t pt-3 border-slate-100">
            <div class="flex justify-between"><span>Target:</span> <span class="font-bold text-slate-800">Rp {{ number_format($targetPenagihan, 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span>Realisasi:</span> <span class="font-bold text-slate-800">Rp {{ number_format($realisasiPenagihan ?? 0, 0, ',', '.') }}</span></div>
        </div>
    </div>

</div>

</div>
@endsection