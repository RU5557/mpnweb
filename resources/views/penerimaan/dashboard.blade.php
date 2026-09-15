@extends('layouts.app')

@section('content')
<!-- HEADER & FILTER CONTAINER -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
    
    <!-- Judul & Subjudul -->
    <div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Dashboard Ringkasan</h1>
        <p class="text-base text-slate-500 mt-1 font-medium">Overview penerimaan, capaian target, dan performa PKM</p>
    </div>

<!-- KODE BARU (Aman & Hemat Memori) -->
@php
    $daftarBulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $selectedBulan = (int) request('bulan', date('n'));
    $selectedTahun = (int) request('tahun', date('Y'));
@endphp

<form action="{{ route('penerimaan.dashboard') }}" method="GET" class="bg-white border border-slate-200 rounded-xl p-2 px-3.5 shadow-sm flex items-center gap-2.5">
    <select name="bulan" class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg px-3 py-1.5 font-medium focus:ring-2 focus:ring-blue-500 outline-none transition cursor-pointer">
        @foreach($daftarBulan as $key => $namaBulan)
            <option value="{{ $key }}" {{ $selectedBulan === $key ? 'selected' : '' }}>
                {{ $namaBulan }}
            </option>
        @endforeach
    </select>

    <select name="tahun" class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg px-3 py-1.5 font-medium focus:ring-2 focus:ring-blue-500 outline-none transition cursor-pointer">
        @for($y = date('Y'); $y >= date('Y') - 3; $y--)
            <option value="{{ $y }}" {{ $selectedTahun === $y ? 'selected' : '' }}>
                {{ $y }}
            </option>
        @endfor
    </select>

    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-1.5 rounded-lg transition shadow-sm flex items-center gap-2">
        Terapkan
    </button>

    @if(request()->has('bulan') || request()->has('tahun'))
        <a href="{{ route('penerimaan.dashboard') }}" class="text-slate-400 hover:text-slate-600 text-sm px-1.5 py-1.5 transition" title="Reset Filter">
            <i class="fa-solid fa-rotate-left"></i>
        </a>
    @endif
</form>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-5 py-2 rounded-xl transition shadow-md shadow-blue-600/20 flex items-center gap-2">
            Terapkan
        </button>

        @if(request()->has('bulan') || request()->has('tahun'))
            <a href="{{ route('penerimaan.dashboard') }}" class="text-slate-400 hover:text-slate-600 text-base px-2 py-2 transition" title="Reset Filter">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        @endif
    </form>
</div>

<!-- BARIS 1: CARD RINGKASAN UTAMA (3 KOLOM) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    
    <!-- Penerimaan Saat Ini -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border-l-4 border-blue-600 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-center mb-1">
                <span class="text-sm font-extrabold text-slate-500 uppercase tracking-wider">Penerimaan Saat Ini</span>
                <!-- Badge Capaian Utama dibuat mencolok -->
                <span class="bg-blue-600 text-white text-sm font-black px-4 py-1.5 rounded-full shadow-md shadow-blue-500/30">
                    {{ number_format($capaianKantor, 1, ',', '.') }}% Capaian
                </span>
            </div>

            <div class="text-3xl md:text-4xl font-black text-blue-600 my-3 tracking-tight">
                Rp {{ number_format($penerimaanSaatIni ?? 0, 0, ',', '.') }}
            </div>
        </div>
        <div class="flex items-center gap-5 text-sm font-bold text-slate-600 pt-4 border-t border-slate-100">
            @php
                $growthMoM = ($penerimaanBlnLalu ?? 0) > 0 ? (($penerimaanSaatIni - $penerimaanBlnLalu) / $penerimaanBlnLalu) * 100 : 0;
                $growthYoY = ($penerimaanThnLalu ?? 0) > 0 ? (($penerimaanSaatIni - $penerimaanThnLalu) / $penerimaanThnLalu) * 100 : 0;
            @endphp
            <span>Bulan Lalu: 
                <span class="{{ $growthMoM >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-extrabold">
                    <i class="fa-solid {{ $growthMoM >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i> 
                    {{ number_format(abs($growthMoM), 2, ',', '.') }}%
                </span>
            </span>
            <span>Tahun Lalu: 
                <span class="{{ $growthYoY >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-extrabold">
                    <i class="fa-solid {{ $growthYoY >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i> 
                    {{ number_format(abs($growthYoY), 2, ',', '.') }}%
                </span>
            </span>
        </div>
    </div>

    <!-- Penerimaan Bulan Lalu -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border-l-4 border-sky-500 flex flex-col justify-between">
        <span class="text-sm font-extrabold text-slate-500 uppercase tracking-wider">Penerimaan Bulan Lalu</span>
        <div class="text-3xl md:text-4xl font-black text-sky-600 my-3 tracking-tight">
            Rp {{ number_format($penerimaanBlnLalu ?? 0, 0, ',', '.') }}
        </div>
        <div class="text-sm font-medium text-slate-400 border-t border-slate-100 pt-4">
            Pembanding bulan sebelumnya
        </div>
    </div>

    <!-- Penerimaan Tahun Lalu -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border-l-4 border-amber-600 flex flex-col justify-between">
        <span class="text-sm font-extrabold text-slate-500 uppercase tracking-wider">Penerimaan Tahun Lalu</span>
        <div class="text-3xl md:text-4xl font-black text-amber-700 my-3 tracking-tight">
            Rp {{ number_format($penerimaanThnLalu ?? 0, 0, ',', '.') }}
        </div>
        <div class="text-sm font-medium text-slate-400 border-t border-slate-100 pt-4">
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
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-black text-slate-900 text-xl tracking-tight">PPM</h3>
                    <p class="text-xs text-slate-400 font-semibold">Capaian Target</p>
                </div>
                <!-- Badge Capaian Diperbesar & Dipertebal -->
                <span class="bg-blue-600 text-white font-black text-base px-3.5 py-1 rounded-xl shadow-md shadow-blue-500/20 tracking-wide">
                    {{ number_format($persenPPM, 1, ',', '.') }}%
                </span>
            </div>
            <div class="space-y-3 text-base text-slate-600 border-t pt-4 border-slate-100 font-medium">
                <div class="flex justify-between"><span>Target:</span> <span class="font-extrabold text-slate-900">Rp {{ number_format($targetPpm, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span>Realisasi:</span> <span class="font-extrabold text-slate-900">Rp {{ number_format($realisasiPPM ?? 0, 0, ',', '.') }}</span></div>
            </div>
        </div>
        <div class="text-sm pt-4 mt-4 border-t border-slate-100 flex justify-between items-center font-bold">
            <span class="text-slate-500">Pertumbuhan YoY:</span>
            <span class="{{ $growthPPM >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-extrabold">
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
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-black text-slate-900 text-xl tracking-tight">PKM</h3>
                    <p class="text-xs text-slate-400 font-semibold">Capaian Target</p>
                </div>
                <span class="bg-sky-500 text-white font-black text-base px-3.5 py-1 rounded-xl shadow-md shadow-sky-500/20 tracking-wide">
                    {{ number_format($persenPKM, 1, ',', '.') }}%
                </span>
            </div>
            <div class="space-y-3 text-base text-slate-600 border-t pt-4 border-slate-100 font-medium">
                <div class="flex justify-between"><span>Target:</span> <span class="font-extrabold text-slate-900">Rp {{ number_format($targetPkm, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span>Realisasi:</span> <span class="font-extrabold text-slate-900">Rp {{ number_format($realisasiPKM ?? 0, 0, ',', '.') }}</span></div>
            </div>
        </div>
        <div class="text-sm pt-4 mt-4 border-t border-slate-100 flex justify-between items-center font-bold">
            <span class="text-slate-500">Pertumbuhan YoY:</span>
            <span class="{{ $growthPKM >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-extrabold">
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
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-black text-slate-900 text-xl tracking-tight">PBP</h3>
                    <p class="text-xs text-slate-400 font-semibold">Capaian Target</p>
                </div>
                <span class="bg-amber-600 text-white font-black text-base px-3.5 py-1 rounded-xl shadow-md shadow-amber-600/20 tracking-wide">
                    {{ number_format($persenPBP, 1, ',', '.') }}%
                </span>
            </div>
            <div class="space-y-3 text-base text-slate-600 border-t pt-4 border-slate-100 font-medium">
                <div class="flex justify-between"><span>Target:</span> <span class="font-extrabold text-slate-900">Rp {{ number_format($targetPbp, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span>Realisasi:</span> <span class="font-extrabold text-slate-900">Rp {{ number_format($realisasiPBP ?? 0, 0, ',', '.') }}</span></div>
            </div>
        </div>
        
        <div class="text-sm pt-4 mt-4 border-t border-slate-100 flex justify-between items-center font-extrabold">
            <span class="text-rose-600">Sisa Target:</span>
            <span class="text-rose-600 text-base font-mono">
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
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-black text-slate-900 text-xl tracking-tight">PKM Pengawasan</h3>
                    <p class="text-xs text-slate-400 font-semibold">Capaian Target</p>
                </div>
                <span class="bg-emerald-600 text-white font-black text-base px-3.5 py-1 rounded-xl shadow-md shadow-emerald-600/20 tracking-wide">
                    {{ number_format($persenPengawasan, 1, ',', '.') }}%
                </span>
            </div>
            <div class="space-y-3 text-base text-slate-600 border-t pt-4 border-slate-100 font-medium">
                <div class="flex justify-between"><span>Target:</span> <span class="font-extrabold text-slate-900">Rp {{ number_format($targetPengawasan, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span>Realisasi:</span> <span class="font-extrabold text-slate-900">Rp {{ number_format($realisasiPengawasan ?? 0, 0, ',', '.') }}</span></div>
            </div>
        </div>
        <div class="text-sm pt-4 mt-4 border-t border-slate-100 flex justify-between items-center font-bold">
            <span class="text-slate-500">Pertumbuhan YoY:</span>
            <span class="{{ $growthPengawasan >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-extrabold">
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
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-black text-slate-900 text-xl tracking-tight">PKM Pemeriksaan</h3>
                    <p class="text-xs text-slate-400 font-semibold">Capaian Target</p>
                </div>
                <span class="bg-slate-700 text-white font-black text-base px-3.5 py-1 rounded-xl shadow-md shadow-slate-700/20 tracking-wide">
                    {{ number_format($persenPemeriksaan, 1, ',', '.') }}%
                </span>
            </div>
            <div class="space-y-3 text-base text-slate-600 border-t pt-4 border-slate-100 font-medium">
                <div class="flex justify-between"><span>Target:</span> <span class="font-extrabold text-slate-900">Rp {{ number_format($targetPemeriksaan, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span>Realisasi:</span> <span class="font-extrabold text-slate-900">Rp {{ number_format($realisasiPemeriksaan ?? 0, 0, ',', '.') }}</span></div>
            </div>
        </div>
        <div class="text-sm pt-4 mt-4 border-t border-slate-100 flex justify-between items-center font-bold">
            <span class="text-slate-500">Pertumbuhan YoY:</span>
            <span class="{{ $growthPemeriksaan >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-extrabold">
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
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-black text-slate-900 text-xl tracking-tight">PKM Penagihan</h3>
                    <p class="text-xs text-slate-400 font-semibold">Capaian Target</p>
                </div>
                <span class="bg-slate-900 text-white font-black text-base px-3.5 py-1 rounded-xl shadow-md shadow-slate-900/20 tracking-wide">
                    {{ number_format($persenPenagihan, 1, ',', '.') }}%
                </span>
            </div>
            <div class="space-y-3 text-base text-slate-600 border-t pt-4 border-slate-100 font-medium">
                <div class="flex justify-between"><span>Target:</span> <span class="font-extrabold text-slate-900">Rp {{ number_format($targetPenagihan, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span>Realisasi:</span> <span class="font-extrabold text-slate-900">Rp {{ number_format($realisasiPenagihan ?? 0, 0, ',', '.') }}</span></div>
            </div>
        </div>
        <div class="text-sm pt-4 mt-4 border-t border-slate-100 flex justify-between items-center font-bold">
            <span class="text-slate-500">Pertumbuhan YoY:</span>
            <span class="{{ $growthPenagihan >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-extrabold">
                <i class="fa-solid {{ $growthPenagihan >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i> 
                {{ number_format(abs($growthPenagihan), 2, ',', '.') }}%
            </span>
        </div>
    </div>

</div>
@endsection