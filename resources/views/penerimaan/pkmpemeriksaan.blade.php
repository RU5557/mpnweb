@extends('layouts.app')

@section('title', 'PKM Pemeriksaan - MPNWEB')

@section('content')

@php
    // Helper function untuk generate URL sort
    function sortUrl($column, $currentSort, $currentDir) {
        $direction = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
        return request()->fullUrlWithQuery(['sort' => $column, 'direction' => $direction]);
    }

    // Helper icon sort
    function sortIcon($column, $currentSort, $currentDir) {
        if ($currentSort !== $column) {
            return '<i class="fa-solid fa-sort text-slate-300 ml-1 text-xs"></i>';
        }
        return $currentDir === 'asc' 
            ? '<i class="fa-solid fa-sort-up text-indigo-600 ml-1 text-xs"></i>' 
            : '<i class="fa-solid fa-sort-down text-indigo-600 ml-1 text-xs"></i>';
    }
@endphp

<!-- HEADER & FILTER (SEJAJAR BERSAMA SEARCH) -->
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Penerimaan PKM Pemeriksaan</h1>
        <p class="text-base text-slate-500 mt-1">Rincian realisasi PKM Pemeriksaan s.d. bulan terpilih per Wajib Pajak (WP)</p>
    </div>

    <!-- Form Filter + Search Bar -->
    <form action="{{ route('penerimaan.pkmpemeriksaan') }}" method="GET" class="bg-white border border-slate-200/80 rounded-2xl p-2.5 px-4 shadow-sm flex flex-wrap items-center gap-3">
        <!-- Preserve Sort State -->
        <input type="hidden" name="sort" value="{{ request('sort', 'total_akt_pemeriksaan') }}">
        <input type="hidden" name="direction" value="{{ request('direction', 'desc') }}">

        <!-- Input Search Bar -->
        <div class="relative flex-1 min-w-[260px]">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                <i class="fa-solid fa-magnifying-glass"></i>
            </span>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NPWP / WP / KLU..." 
                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-700 text-base rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition placeholder:text-slate-400 font-medium">
        </div>

        <!-- Filter Bulan -->
        <select name="bulan" class="bg-slate-50 border border-slate-200 text-slate-700 text-base rounded-xl px-4 py-2.5 font-medium outline-none">
            @foreach(range(1, 12) as $m)
                <option value="{{ $m }}" {{ request('bulan', date('m')) == $m ? 'selected' : '' }}>
                    s.d. {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                </option>
            @endforeach
        </select>

        <!-- Filter Tahun -->
        <select name="tahun" class="bg-slate-50 border border-slate-200 text-slate-700 text-base rounded-xl px-4 py-2.5 font-medium outline-none">
            @foreach(range(date('Y') - 3, date('Y')) as $year)
                <option value="{{ $year }}" {{ request('tahun', date('Y')) == $year ? 'selected' : '' }}>{{ $year }}</option>
            @endforeach
        </select>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-base font-semibold px-5 py-2.5 rounded-xl transition shadow-sm">
            Terapkan
        </button>

        @if(request()->has('bulan') || request()->has('tahun') || request()->has('search') || request()->has('sort'))
            <a href="{{ route('penerimaan.pkmpemeriksaan') }}" class="text-slate-400 hover:text-slate-600 text-base px-2 py-2 transition" title="Reset Filter">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        @endif
    </form>
</div>

<!-- TABEL PKM PEMERIKSAAN -->
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-3.5 h-3.5 rounded-full bg-indigo-600"></div>
            <h2 class="text-lg font-bold text-slate-800">Tabel Realisasi PKM Pemeriksaan</h2>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-base">
            <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200 text-sm">
                <tr>
                    <th class="py-4 px-4 w-14 text-center">No</th>
                    
                    <!-- Header Sort NPWP -->
                    <th class="py-4 px-4 whitespace-nowrap">
                        <a href="{{ sortUrl('npwp', $sortColumn, $sortDirection) }}" class="flex items-center gap-1 hover:text-indigo-600 transition select-none">
                            NPWP {!! sortIcon('npwp', $sortColumn, $sortDirection) !!}
                        </a>
                    </th>
                    
                    <!-- Header Sort Nama WP -->
                    <th class="py-4 px-4">
                        <a href="{{ sortUrl('nama_wp', $sortColumn, $sortDirection) }}" class="flex items-center gap-1 hover:text-indigo-600 transition select-none">
                            Nama WP {!! sortIcon('nama_wp', $sortColumn, $sortDirection) !!}
                        </a>
                    </th>
                    
                    <!-- Header Sort KODE KLU -->
                    <th class="py-4 px-4 text-center whitespace-nowrap">
                        <a href="{{ sortUrl('kd_klu', $sortColumn, $sortDirection) }}" class="flex items-center justify-center gap-1 hover:text-indigo-600 transition select-none">
                            KODE KLU {!! sortIcon('kd_klu', $sortColumn, $sortDirection) !!}
                        </a>
                    </th>
                    
                    <!-- Header Sort Nama KLU -->
                    <th class="py-4 px-4 max-w-xs">
                        <a href="{{ sortUrl('nm_klu', $sortColumn, $sortDirection) }}" class="flex items-center gap-1 hover:text-indigo-600 transition select-none">
                            Nama KLU {!! sortIcon('nm_klu', $sortColumn, $sortDirection) !!}
                        </a>
                    </th>
                    
                    <!-- Header Sort TOTAL PKM PEMERIKSAAN -->
                    <th class="py-4 px-4 text-right whitespace-nowrap">
                        <a href="{{ sortUrl('total_akt_pemeriksaan', $sortColumn, $sortDirection) }}" class="flex items-center justify-end gap-1 hover:text-indigo-600 transition select-none">
                            TOTAL PKM PEMERIKSAAN {!! sortIcon('total_akt_pemeriksaan', $sortColumn, $sortDirection) !!}
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                @forelse($pkmData as $index => $row)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-4 px-4 text-center text-slate-400 font-mono text-sm">
                            {{ $pkmData->firstItem() + $index }}
                        </td>
                        <td class="py-4 px-4 font-mono font-bold text-slate-800 whitespace-nowrap">
                            {{ $row->npwp15 }}
                        </td>
                        <td class="py-4 px-4 text-slate-900 font-bold">
                            @if($row->nama_wp === 'WP Tidak Terdaftar')
                                <span class="text-rose-600 italic">WP Tidak Terdaftar</span>
                            @else
                                {{ $row->nama_wp }}
                            @endif
                        </td>
                        <td class="py-4 px-4 text-center font-mono text-slate-600 whitespace-nowrap">
                            {{ $row->kd_klu }}
                        </td>
                        <td class="py-4 px-4 text-slate-600 max-w-xs truncate" title="{{ $row->nm_klu }}">
                            {{ $row->nm_klu }}
                        </td>
                        <td class="py-4 px-4 text-right font-mono font-bold text-indigo-600 whitespace-nowrap">
                            Rp {{ number_format($row->total_akt_pemeriksaan ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400 italic text-base">
                            Tidak ada data PKM Pemeriksaan yang sesuai dengan filter/pencarian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($pkmData->count() > 0)
                <tfoot class="bg-slate-100/90 font-bold text-slate-900 border-t-2 border-slate-200 text-base">
                    <tr>
                        <td colspan="5" class="py-4 px-4 text-center tracking-wider">TOTAL SUBHALAMAN INI</td>
                        <td class="py-4 px-4 text-right font-mono text-indigo-700 whitespace-nowrap">
                            Rp {{ number_format($pkmData->sum('total_akt_pemeriksaan'), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <!-- Paginasi (10 Data Per Halaman) -->
    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
        {{ $pkmData->links() }}
    </div>
</div>

@endsection