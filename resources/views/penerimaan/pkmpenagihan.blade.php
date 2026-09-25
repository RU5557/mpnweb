@extends('layouts.app')

@section('title', 'PKM Penagihan - MPNWEB')

@section('content')

<!-- HEADER & FILTER CONTAINER -->
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
    
    <!-- Judul & Subjudul -->
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Penerimaan PKM Penagihan</h1>
        <p class="text-xs text-slate-500 mt-1">Rincian realisasi PKM Penagihan s.d. bulan terpilih per Juru Sita Pajak Negara (JSPN)</p>
    </div>

    <!-- Form Filter Compact -->
    <form action="{{ route('penerimaan.pkmpenagihan') }}" method="GET" class="bg-white border border-slate-200 rounded-xl p-2 px-3 shadow-sm flex flex-wrap items-center gap-2">
        <!-- State Preserve Sort -->
        <input type="hidden" name="sort" value="{{ $sortColumn }}">
        <input type="hidden" name="direction" value="{{ $sortDirection }}">

        <!-- Filter Dropdown DSPC / NON-DSPC -->
        <select name="dspc_filter" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-lg px-2.5 py-1.5 font-medium focus:ring-2 focus:ring-blue-500 outline-none transition cursor-pointer">
            <option value="">Semua Flag SKP</option>
            <option value="DSPC" {{ $dspcFilter === 'DSPC' ? 'selected' : '' }}>DSPC</option>
            <option value="NON-DSPC" {{ $dspcFilter === 'NON-DSPC' ? 'selected' : '' }}>NON-DSPC</option>
        </select>

        <!-- Filter Bulan -->
        <select name="bulan" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-lg px-2.5 py-1.5 font-medium focus:ring-2 focus:ring-blue-500 outline-none transition cursor-pointer">
            @foreach(range(1, 12) as $m)
                <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                    s.d. {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                </option>
            @endforeach
        </select>

        <!-- Filter Tahun -->
        <select name="tahun" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-lg px-2.5 py-1.5 font-medium focus:ring-2 focus:ring-blue-500 outline-none transition cursor-pointer">
            @foreach(range(date('Y') - 3, date('Y')) as $year)
                <option value="{{ $year }}" {{ $tahun == $year ? 'selected' : '' }}>
                    {{ $year }}
                </option>
            @endforeach
        </select>

        <!-- Tombol Terapkan -->
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3.5 py-1.5 rounded-lg transition shadow-sm flex items-center gap-1.5">
            <span>Terapkan</span>
        </button>

        <!-- Tombol Reset -->
        @if(request()->has('bulan') || request()->has('tahun') || request()->has('dspc_filter') || request()->has('sort'))
            <a href="{{ route('penerimaan.pkmpenagihan') }}" class="text-slate-400 hover:text-slate-600 text-xs px-1.5 py-1.5 transition" title="Reset Filter">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        @endif

        <!-- Tombol Export Detil Transaksi -->
        <a href="{{ route('pkm.penagihan.export-detil', request()->all()) }}" 
           class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3.5 py-1.5 rounded-lg transition shadow-sm flex items-center gap-1.5 border border-emerald-600">
            <i class="fa-solid fa-file-excel text-xs"></i>
            <span>Export CSV</span>
        </a>
    </form>
</div>

<!-- TABEL PKM PENAGIHAN -->
<div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-full bg-amber-500"></div>
            <h2 class="text-sm font-bold text-slate-800">Tabel Realisasi PKM Penagihan</h2>
        </div>
        <span class="text-xs font-medium text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-md">
            Total: {{ $pkmData->count() }} Baris
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                <tr>
                    <th class="py-3 px-3.5 w-12 text-center whitespace-nowrap">No</th>
                    
                    <!-- Header Sort NIP JSPN -->
                    <th class="py-3 px-3.5 whitespace-nowrap">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'nip_jspn', 'direction' => ($sortColumn === 'nip_jspn' && $sortDirection === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-blue-600 transition select-none">
                            <span>NIP JSPN</span>
                            @if($sortColumn === 'nip_jspn')
                                <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-600 text-xs ml-0.5"></i>
                            @else
                                <i class="fa-solid fa-sort text-slate-300 text-xs ml-0.5"></i>
                            @endif
                        </a>
                    </th>

                    <!-- Header Sort NAMA JSPN -->
                    <th class="py-3 px-3.5 whitespace-nowrap">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'nama_jspn', 'direction' => ($sortColumn === 'nama_jspn' && $sortDirection === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-blue-600 transition select-none">
                            <span>Nama JSPN</span>
                            @if($sortColumn === 'nama_jspn')
                                <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-600 text-xs ml-0.5"></i>
                            @else
                                <i class="fa-solid fa-sort text-slate-300 text-xs ml-0.5"></i>
                            @endif
                        </a>
                    </th>

                    <!-- Header Sort DSPC / NON-DSPC -->
                    <th class="py-3 px-3.5 text-center whitespace-nowrap">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'flag_skp', 'direction' => ($sortColumn === 'flag_skp' && $sortDirection === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center justify-center gap-1 hover:text-blue-600 transition select-none">
                            <span>Flag SKP</span>
                            @if($sortColumn === 'flag_skp')
                                <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-600 text-xs ml-0.5"></i>
                            @else
                                <i class="fa-solid fa-sort text-slate-300 text-xs ml-0.5"></i>
                            @endif
                        </a>
                    </th>

                    <!-- Header Sort AKT PENAGIHAN -->
                    <th class="py-3 px-3.5 text-right whitespace-nowrap">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'akt_penagihan', 'direction' => ($sortColumn === 'akt_penagihan' && $sortDirection === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center justify-end gap-1 hover:text-blue-600 transition select-none">
                            <span>Total PKM Penagihan</span>
                            @if($sortColumn === 'akt_penagihan')
                                <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-600 text-xs ml-0.5"></i>
                            @else
                                <i class="fa-solid fa-sort text-slate-300 text-xs ml-0.5"></i>
                            @endif
                        </a>
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                @forelse($pkmData as $index => $row)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-2.5 px-3.5 text-center text-slate-400 font-mono">
                            {{ $index + 1 }}
                        </td>
                        
                        <!-- NIP JSPN -->
                        <td class="py-2.5 px-3.5 font-mono font-semibold text-slate-800 whitespace-nowrap">
                            @if($row->nip_jspn === 'Unassign')
                                <span class="text-rose-600 italic font-sans font-medium">Unassign</span>
                            @else
                                {{ $row->nip_jspn }}
                            @endif
                        </td>

                        <!-- NAMA JSPN -->
                        <td class="py-2.5 px-3.5 text-slate-900 font-semibold">
                            @if($row->nama_jspn === 'Unassign')
                                <span class="text-rose-600 italic font-medium">Unassign</span>
                            @else
                                {{ $row->nama_jspn }}
                            @endif
                        </td>

                        <!-- DSPC / NON-DSPC -->
                        <td class="py-2.5 px-3.5 text-center whitespace-nowrap">
                            @if(strtoupper($row->flag_skp) === 'DSPC')
                                <span class="bg-amber-50 text-amber-700 border border-amber-200 text-[11px] px-2 py-0.5 rounded-md font-semibold">
                                    DSPC
                                </span>
                            @else
                                <span class="bg-slate-100 text-slate-600 border border-slate-200 text-[11px] px-2 py-0.5 rounded-md font-semibold">
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
                        <td colspan="5" class="py-10 text-center text-slate-400 italic text-xs">
                            Tidak ada data PKM Penagihan yang sesuai dengan filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>

            @if($pkmData->count() > 0)
                <tfoot class="bg-slate-100/80 font-bold text-slate-900 border-t-2 border-slate-200 text-xs">
                    <tr>
                        <td colspan="4" class="py-3 px-3.5 text-center tracking-wider uppercase">Total Keseluruhan</td>
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