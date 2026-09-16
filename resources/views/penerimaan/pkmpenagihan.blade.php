@extends('layouts.app')

@section('title', 'PKM Penagihan - MPNWEB')

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
            ? '<i class="fa-solid fa-sort-up text-amber-600 ml-1 text-xs"></i>' 
            : '<i class="fa-solid fa-sort-down text-amber-600 ml-1 text-xs"></i>';
    }
@endphp

<!-- HEADER & FILTER CONTAINER (COMPACT & SEJAJAR) -->
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-5">
    
    <!-- Judul & Subjudul -->
    <div>
        <h1 class="text-2xl md:text-3xl font-bold text-slate-800 tracking-tight">Penerimaan PKM Penagihan</h1>
        <p class="text-xs md:text-sm text-slate-500 mt-0.5">Rincian realisasi PKM Penagihan s.d. bulan terpilih per Juru Sita Pajak Negara (JSPN)</p>
    </div>

    <!-- Form Filter Compact -->
    <form action="{{ route('penerimaan.pkmpenagihan') }}" method="GET" class="bg-white border border-slate-200 rounded-xl p-2 px-3.5 shadow-sm flex flex-wrap items-center gap-2.5">
        <!-- State Preserve Sort -->
        <input type="hidden" name="sort" value="{{ request('sort', 'nip_jspn') }}">
        <input type="hidden" name="direction" value="{{ request('direction', 'asc') }}">

        <!-- Filter Dropdown DSPC / NON-DSPC -->
        <select name="dspc_filter" class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg px-3 py-1.5 font-medium focus:ring-2 focus:ring-amber-500 outline-none transition cursor-pointer">
            <option value="">DSPC/NON-DSPC</option>
            <option value="DSPC" {{ request('dspc_filter') == 'DSPC' ? 'selected' : '' }}>DSPC</option>
            <option value="NON-DSPC" {{ request('dspc_filter') == 'NON-DSPC' ? 'selected' : '' }}>NON-DSPC</option>
        </select>

        <!-- Filter Bulan -->
        <select name="bulan" class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg px-3 py-1.5 font-medium focus:ring-2 focus:ring-amber-500 outline-none transition cursor-pointer">
            @foreach(range(1, 12) as $m)
                @php
                    $monthName = \Carbon\Carbon::create()->month($m)->translatedFormat('F');
                @endphp
                <option value="{{ $m }}" {{ request('bulan', date('m')) == $m ? 'selected' : '' }}>
                    {{ $monthName }}
                </option>
            @endforeach
        </select>

        <!-- Filter Tahun -->
        <select name="tahun" class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg px-3 py-1.5 font-medium focus:ring-2 focus:ring-amber-500 outline-none transition cursor-pointer">
            @foreach(range(date('Y') - 3, date('Y')) as $year)
                <option value="{{ $year }}" {{ request('tahun', date('Y')) == $year ? 'selected' : '' }}>
                    {{ $year }}
                </option>
            @endforeach
        </select>

        <!-- Tombol Terapkan -->
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-1.5 rounded-lg transition shadow-sm flex items-center gap-2">
            Terapkan
        </button>

        <!-- Tombol Reset -->
        @if(request()->has('bulan') || request()->has('tahun') || request()->has('dspc_filter') || request()->has('sort'))
            <a href="{{ route('penerimaan.pkmpenagihan') }}" class="text-slate-400 hover:text-slate-600 text-sm px-1.5 py-1.5 transition" title="Reset Filter">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        @endif
        <!-- Tombol Export Detil Transaksi (Tailwind Style) -->
<a href="{{ route('pkm.penagihan.export-detil', request()->all()) }}" 
   class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-3.5 py-1.5 rounded-lg transition shadow-sm flex items-center gap-2 border border-emerald-600">
    <i class="fa-solid fa-file-excel text-xs"></i>
    <span>Export CSV</span>
</a>
    </form>
</div>

<!-- TABEL PKM PENAGIHAN -->
<div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
            <h2 class="text-base font-bold text-slate-800">Tabel Realisasi PKM Penagihan</h2>
        </div>
        <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-md">
            Total Data: {{ $pkmData->count() }} Baris
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200 text-xs">
                <tr>
                    <th class="py-3 px-3.5 w-12 text-center whitespace-nowrap">No</th>
                    
                    <!-- Header Sort NIP JSPN -->
                    <th class="py-3 px-3.5 whitespace-nowrap">
                        <a href="{{ sortUrl('nip_jspn', $sortColumn, $sortDirection) }}" class="flex items-center gap-1 hover:text-amber-600 transition select-none">
                            NIP JSPN {!! sortIcon('nip_jspn', $sortColumn, $sortDirection) !!}
                        </a>
                    </th>

                    <!-- Header Sort NAMA JSPN -->
                    <th class="py-3 px-3.5 whitespace-nowrap">
                        <a href="{{ sortUrl('nama_jspn', $sortColumn, $sortDirection) }}" class="flex items-center gap-1 hover:text-amber-600 transition select-none">
                            NAMA JSPN {!! sortIcon('nama_jspn', $sortColumn, $sortDirection) !!}
                        </a>
                    </th>

                    <!-- Header Sort DSPC / NON-DSPC -->
                    <th class="py-3 px-3.5 text-center whitespace-nowrap">
                        <a href="{{ sortUrl('flag_skp', $sortColumn, $sortDirection) }}" class="flex items-center justify-center gap-1 hover:text-amber-600 transition select-none">
                            DSPC / NON-DSPC {!! sortIcon('flag_skp', $sortColumn, $sortDirection) !!}
                        </a>
                    </th>

                    <!-- Header Sort AKT PENAGIHAN -->
                    <th class="py-3 px-3.5 text-right whitespace-nowrap">
                        <a href="{{ sortUrl('akt_penagihan', $sortColumn, $sortDirection) }}" class="flex items-center justify-end gap-1 hover:text-amber-600 transition select-none">
                            TOTAL PKM PENAGIHAN {!! sortIcon('akt_penagihan', $sortColumn, $sortDirection) !!}
                        </a>
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                @forelse($pkmData as $index => $row)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-2.5 px-3.5 text-center text-slate-400 font-mono text-xs">
                            {{ $index + 1 }}
                        </td>
                        
                        <!-- NIP JSPN -->
                        <td class="py-2.5 px-3.5 font-mono font-semibold text-slate-800 whitespace-nowrap text-xs">
                            @if($row->nip_jspn === 'Unassign')
                                <span class="text-rose-600 italic font-sans font-medium">Unassign</span>
                            @else
                                {{ $row->nip_jspn }}
                            @endif
                        </td>

                        <!-- NAMA JSPN: Font dikecilkan sedikit ke 13px -->
                        <td class="py-2.5 px-3.5 text-slate-900 font-semibold text-[13px]">
                            @if($row->nama_jspn === 'Unassign')
                                <span class="text-rose-600 italic font-medium">Unassign</span>
                            @else
                                {{ $row->nama_jspn }}
                            @endif
                        </td>

                        <!-- DSPC / NON-DSPC -->
                        <td class="py-2.5 px-3.5 text-center whitespace-nowrap">
                            @if(strtoupper($row->flag_skp) === 'DSPC')
                                <span class="bg-amber-100 text-amber-800 border border-amber-300 text-[11px] px-2.5 py-0.5 rounded-full font-bold">
                                    DSPC
                                </span>
                            @else
                                <span class="bg-slate-100 text-slate-600 border border-slate-200 text-[11px] px-2.5 py-0.5 rounded-full font-semibold">
                                    NON-DSPC
                                </span>
                            @endif
                        </td>

                        <!-- AKT PENAGIHAN -->
                        <td class="py-2.5 px-3.5 text-right font-mono font-bold text-amber-600 whitespace-nowrap">
                            Rp {{ number_format($row->akt_penagihan ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-slate-400 italic text-sm">
                            Tidak ada data PKM Penagihan yang sesuai dengan filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>

            @if($pkmData->count() > 0)
                <tfoot class="bg-slate-100/90 font-bold text-slate-900 border-t-2 border-slate-200 text-sm">
                    <tr>
                        <td colspan="4" class="py-3 px-3.5 text-center tracking-wider">TOTAL KESELURUHAN</td>
                        <td class="py-3 px-3.5 text-right font-mono text-amber-700 whitespace-nowrap">
                            Rp {{ number_format($pkmData->sum('akt_penagihan'), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection