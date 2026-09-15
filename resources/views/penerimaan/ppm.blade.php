@extends('layouts.app')

@section('title', 'Penerimaan PPM - MPNWEB')

@section('content')

<!-- HEADER & FILTER CONTAINER -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    
    <!-- Judul & Subjudul -->
    <div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Penerimaan PPM</h1>
        <p class="text-sm text-slate-500 mt-1">Overview penerimaan, capaian target, dan performa PPM</p>
    </div>

<!-- Form Filter versi Proporsional / Sama Besar -->
<form action="{{ route('penerimaan.ppm') }}" method="GET" class="bg-white border border-slate-200/80 rounded-2xl p-2.5 px-4 shadow-sm flex flex-wrap items-center gap-3">
    
    <!-- Select Bulan -->
    <select name="bulan" class="bg-slate-50 border border-slate-200 text-slate-700 text-base rounded-xl px-4 py-2.5 font-medium outline-none transition cursor-pointer">
        @foreach(range(1, 12) as $m)
            @php
                $monthName = \Carbon\Carbon::create()->month($m)->translatedFormat('F');
            @endphp
            <option value="{{ $m }}" {{ request('bulan', date('m')) == $m ? 'selected' : '' }}>
                s.d. {{ $monthName }}
            </option>
        @endforeach
    </select>

    <!-- Select Tahun -->
    <select name="tahun" class="bg-slate-50 border border-slate-200 text-slate-700 text-base rounded-xl px-4 py-2.5 font-medium outline-none transition cursor-pointer">
        @foreach(range(date('Y') - 3, date('Y')) as $year)
            <option value="{{ $year }}" {{ request('tahun', date('Y')) == $year ? 'selected' : '' }}>
                {{ $year }}
            </option>
        @endforeach
    </select>

    <!-- Tombol Terapkan -->
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-base font-semibold px-5 py-2.5 rounded-xl transition shadow-sm">
        Terapkan
    </button>

    <!-- Tombol Reset -->
    @if(request()->has('bulan') || request()->has('tahun'))
        <a href="{{ route('penerimaan.ppm') }}" class="text-slate-400 hover:text-slate-600 text-base px-2 py-2 transition" title="Reset Filter">
            <i class="fa-solid fa-rotate-left"></i>
        </a>
    @endif
</form>
</div>

<!-- Baris 1: 3 Card Utama (WP, Kategori/Sektor, Jenis Pajak) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-5">

    <!-- Card Top 10 WP -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100">
        <h3 class="font-bold text-slate-800 text-sm mb-3 border-b pb-2 flex justify-between items-center">
            <span>10 WP Terbesar</span>
            <span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">Top 10</span>
        </h3>
        <div class="space-y-2 text-xs">
            @forelse($topWp as $index => $item)
                <div class="flex justify-between items-center py-1 border-b border-slate-50 last:border-none">
                    <div class="flex-1 min-w-0 pr-2 truncate">
                        <span class="font-bold text-slate-400 mr-1">{{ $index + 1 }}.</span>
                        <span class="font-medium text-slate-700" title="{{ $item->nama_wp }}">{{ $item->nama_wp }}</span>
                    </div>
                    <span class="font-bold text-slate-800 shrink-0">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
                </div>
            @empty
                <p class="text-slate-400 text-center py-4">Tidak ada data</p>
            @endforelse
        </div>
    </div>

    <!-- Card Top 10 Kategori / Sektor -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100">
        <h3 class="font-bold text-slate-800 text-sm mb-3 border-b pb-2 flex justify-between items-center">
            <span>10 Sektor Terbesar</span>
            <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">Top 10</span>
        </h3>
        <div class="space-y-2 text-xs">
            @forelse($topKategori as $index => $item)
                <div class="flex justify-between items-center py-1 border-b border-slate-50 last:border-none">
                    <div class="flex-1 min-w-0 pr-2 truncate">
                        <span class="font-bold text-slate-400 mr-1">{{ $index + 1 }}.</span>
                        <span class="font-medium text-slate-700" title="{{ $item->nm_kategori }}">{{ $item->nm_kategori }}</span>
                    </div>
                    <span class="font-bold text-slate-800 shrink-0">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
                </div>
            @empty
                <p class="text-slate-400 text-center py-4">Tidak ada data</p>
            @endforelse
        </div>
    </div>

    <!-- Card Top 10 Jenis Pajak -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100">
        <h3 class="font-bold text-slate-800 text-sm mb-3 border-b pb-2 flex justify-between items-center">
            <span>10 Jenis Pajak Terbesar</span>
            <span class="text-[10px] bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full font-medium">Top 10</span>
        </h3>
        <div class="space-y-2 text-xs">
            @forelse($topJenisPajak as $index => $item)
                <div class="flex justify-between items-center py-1 border-b border-slate-50 last:border-none">
                    <div class="flex-1 min-w-0 pr-2 truncate">
                        <span class="font-bold text-slate-400 mr-1">{{ $index + 1 }}.</span>
                        <span class="font-medium text-slate-700" title="{{ $item->jenis_pajak }}">{{ $item->jenis_pajak }}</span>
                    </div>
                    <span class="font-bold text-slate-800 shrink-0">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
                </div>
            @empty
                <p class="text-slate-400 text-center py-4">Tidak ada data</p>
            @endforelse
        </div>
    </div>

</div>

<!-- Baris 2: 4 Card WP per Jenis Pajak Spesifik (2x2 Grid) -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">

    <!-- Top WP PPN DN -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100">
        <h3 class="font-bold text-slate-800 text-sm mb-3 border-b pb-2">10 WP PPN DN Terbesar</h3>
        <div class="space-y-2 text-xs">
            @forelse($topWpPpnDn as $index => $item)
                <div class="flex justify-between items-center py-1 border-b border-slate-50 last:border-none">
                    <div class="flex-1 min-w-0 pr-2 truncate">
                        <span class="font-bold text-slate-400 mr-1">{{ $index + 1 }}.</span>
                        <span class="font-medium text-slate-700" title="{{ $item->nama_wp }}">{{ $item->nama_wp }}</span>
                    </div>
                    <span class="font-bold text-slate-800 shrink-0">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
                </div>
            @empty
                <p class="text-slate-400 text-center py-4">Tidak ada data</p>
            @endforelse
        </div>
    </div>

    <!-- Top WP PPN Impor -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100">
        <h3 class="font-bold text-slate-800 text-sm mb-3 border-b pb-2">10 WP PPN Impor Terbesar</h3>
        <div class="space-y-2 text-xs">
            @forelse($topWpPpnImpor as $index => $item)
                <div class="flex justify-between items-center py-1 border-b border-slate-50 last:border-none">
                    <div class="flex-1 min-w-0 pr-2 truncate">
                        <span class="font-bold text-slate-400 mr-1">{{ $index + 1 }}.</span>
                        <span class="font-medium text-slate-700" title="{{ $item->nama_wp }}">{{ $item->nama_wp }}</span>
                    </div>
                    <span class="font-bold text-slate-800 shrink-0">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
                </div>
            @empty
                <p class="text-slate-400 text-center py-4">Tidak ada data</p>
            @endforelse
        </div>
    </div>

    <!-- Top WP PPh Pasal 25/29 Badan -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100">
        <h3 class="font-bold text-slate-800 text-sm mb-3 border-b pb-2">10 WP PPh 25/29 Badan Terbesar</h3>
        <div class="space-y-2 text-xs">
            @forelse($topWpPphBadan as $index => $item)
                <div class="flex justify-between items-center py-1 border-b border-slate-50 last:border-none">
                    <div class="flex-1 min-w-0 pr-2 truncate">
                        <span class="font-bold text-slate-400 mr-1">{{ $index + 1 }}.</span>
                        <span class="font-medium text-slate-700" title="{{ $item->nama_wp }}">{{ $item->nama_wp }}</span>
                    </div>
                    <span class="font-bold text-slate-800 shrink-0">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
                </div>
            @empty
                <p class="text-slate-400 text-center py-4">Tidak ada data</p>
            @endforelse
        </div>
    </div>

    <!-- Top WP PPh Pasal 21 -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100">
        <h3 class="font-bold text-slate-800 text-sm mb-3 border-b pb-2">10 WP PPh Pasal 21 Terbesar</h3>
        <div class="space-y-2 text-xs">
            @forelse($topWpPph21 as $index => $item)
                <div class="flex justify-between items-center py-1 border-b border-slate-50 last:border-none">
                    <div class="flex-1 min-w-0 pr-2 truncate">
                        <span class="font-bold text-slate-400 mr-1">{{ $index + 1 }}.</span>
                        <span class="font-medium text-slate-700" title="{{ $item->nama_wp }}">{{ $item->nama_wp }}</span>
                    </div>
                    <span class="font-bold text-slate-800 shrink-0">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
                </div>
            @empty
                <p class="text-slate-400 text-center py-4">Tidak ada data</p>
            @endforelse
        </div>
    </div>

</div>

@endsection