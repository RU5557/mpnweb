@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header & Filter -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Penerimaan PPM</h1>
            <p class="text-xs text-slate-500">Breakdown Top 10 Kontributor PPM</p>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('penerimaan.ppm') }}" class="flex items-center gap-2 bg-white p-2 rounded-xl shadow-sm border border-slate-200">
            <select name="bulan" class="text-xs border-slate-200 rounded-lg focus:ring-blue-500">
                @foreach(range(1, 12) as $m)
                    <option value="{{ sprintf('%02d', $m) }}" {{ $blnIni == $m ? 'selected' : '' }}>
                        {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                    </option>
                @endforeach
            </select>
            <select name="tahun" class="text-xs border-slate-200 rounded-lg focus:ring-blue-500">
                @foreach([2024, 2025, 2026] as $y)
                    <option value="{{ $y }}" {{ $thnIni == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-blue-600 text-white text-xs px-3 py-1.5 rounded-lg font-semibold hover:bg-blue-700">Terapkan</button>
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
                        <div class="truncate max-w-[170px]">
                            <span class="font-bold text-slate-400 mr-1">{{ $index + 1 }}.</span>
                            <span class="font-medium text-slate-700" title="{{ $item->nama_wp }}">{{ $item->nama_wp }}</span>
                        </div>
                        <span class="font-bold text-slate-800">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
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
                        <div class="truncate max-w-[170px]">
                            <span class="font-bold text-slate-400 mr-1">{{ $index + 1 }}.</span>
                            <span class="font-medium text-slate-700" title="{{ $item->nm_kategori }}">{{ $item->nm_kategori }}</span>
                        </div>
                        <span class="font-bold text-slate-800">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
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
                        <div class="truncate max-w-[170px]">
                            <span class="font-bold text-slate-400 mr-1">{{ $index + 1 }}.</span>
                            <span class="font-medium text-slate-700" title="{{ $item->jenis_pajak }}">{{ $item->jenis_pajak }}</span>
                        </div>
                        <span class="font-bold text-slate-800">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="text-slate-400 text-center py-4">Tidak ada data</p>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Baris 2: 4 Card WP per Jenis Pajak Spesifik (2x2 Grid) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        <!-- Top WP PPN DN -->
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100">
            <h3 class="font-bold text-slate-800 text-sm mb-3 border-b pb-2">10 WP PPN DN Terbesar</h3>
            <div class="space-y-2 text-xs">
                @forelse($topWpPpnDn as $index => $item)
                    <div class="flex justify-between items-center py-1 border-b border-slate-50 last:border-none">
                        <div class="truncate max-w-[220px]">
                            <span class="font-bold text-slate-400 mr-1">{{ $index + 1 }}.</span>
                            <span class="font-medium text-slate-700">{{ $item->nama_wp }}</span>
                        </div>
                        <span class="font-bold text-slate-800">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
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
                        <div class="truncate max-w-[220px]">
                            <span class="font-bold text-slate-400 mr-1">{{ $index + 1 }}.</span>
                            <span class="font-medium text-slate-700">{{ $item->nama_wp }}</span>
                        </div>
                        <span class="font-bold text-slate-800">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
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
                        <div class="truncate max-w-[220px]">
                            <span class="font-bold text-slate-400 mr-1">{{ $index + 1 }}.</span>
                            <span class="font-medium text-slate-700">{{ $item->nama_wp }}</span>
                        </div>
                        <span class="font-bold text-slate-800">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
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
                        <div class="truncate max-w-[220px]">
                            <span class="font-bold text-slate-400 mr-1">{{ $index + 1 }}.</span>
                            <span class="font-medium text-slate-700">{{ $item->nama_wp }}</span>
                        </div>
                        <span class="font-bold text-slate-800">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="text-slate-400 text-center py-4">Tidak ada data</p>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection