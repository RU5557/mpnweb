@extends('layouts.app')

@section('title', 'PKM Pemeriksaan - MPNWEB')

@section('content')

<!-- HEADER & FILTER CONTAINER -->
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-5">
    
    <!-- Judul & Subjudul -->
    <div>
        <h1 class="text-2xl md:text-3xl font-bold text-slate-800 tracking-tight">Penerimaan PKM Pemeriksaan</h1>
        <p class="text-xs md:text-sm text-slate-500 mt-0.5">Rincian realisasi PKM Pemeriksaan s.d. bulan terpilih per Wajib Pajak (WP)</p>
    </div>

    <!-- Form Filter + Search Bar Compact -->
    <form action="{{ route('penerimaan.pkmpemeriksaan') }}" method="GET" class="bg-white border border-slate-200 rounded-xl p-2 px-3.5 shadow-sm flex flex-wrap items-center gap-2.5">
        <!-- Preserve Sort State -->
        <input type="hidden" name="sort" value="{{ $sortColumn }}">
        <input type="hidden" name="direction" value="{{ $sortDirection }}">

        <!-- Input Search Bar Compact -->
        <div class="relative flex-1 min-w-[220px]">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400 text-xs">
                <i class="fa-solid fa-magnifying-glass"></i>
            </span>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NPWP / WP / KLU..." 
                   class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition placeholder:text-slate-400 font-medium">
        </div>

        <!-- Filter Bulan -->
        <select name="bulan" class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg px-3 py-1.5 font-medium focus:ring-2 focus:ring-indigo-500 outline-none transition cursor-pointer">
            @foreach(range(1, 12) as $m)
                @php
                    $monthName = \Carbon\Carbon::create()->month($m)->translatedFormat('F');
                @endphp
                <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                    s.d. {{ $monthName }}
                </option>
            @endforeach
        </select>

        <!-- Filter Tahun -->
        <select name="tahun" class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg px-3 py-1.5 font-medium focus:ring-2 focus:ring-indigo-500 outline-none transition cursor-pointer">
            @foreach(range(date('Y') - 3, date('Y')) as $year)
                <option value="{{ $year }}" {{ $tahun == $year ? 'selected' : '' }}>
                    {{ $year }}
                </option>
            @endforeach
        </select>

        <!-- Tombol Terapkan -->
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-1.5 rounded-lg transition shadow-sm flex items-center gap-2">
            Terapkan
        </button>

        <!-- Tombol Reset -->
        @if(request()->has('bulan') || request()->has('tahun') || request()->has('search') || request()->has('sort'))
            <a href="{{ route('penerimaan.pkmpemeriksaan') }}" class="text-slate-400 hover:text-slate-600 text-sm px-1.5 py-1.5 transition" title="Reset Filter">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        @endif

        <!-- Tombol Export Detil Transaksi -->
        <a href="{{ route('pkm.pemeriksaan.export-detil', request()->only(['tahun', 'bulan', 'search'])) }}" 
           class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-3.5 py-1.5 rounded-lg transition shadow-sm flex items-center gap-2 border border-emerald-600">
            <i class="fa-solid fa-file-excel text-xs"></i>
            <span>Export CSV</span>
        </a>
    </form>
</div>

<!-- TABEL PKM PEMERIKSAAN -->
<div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-3 h-3 rounded-full bg-indigo-600"></div>
            <h2 class="text-base font-bold text-slate-800">Tabel Realisasi PKM Pemeriksaan</h2>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200 text-xs">
                <tr>
                    <th class="py-3 px-3.5 w-12 text-center whitespace-nowrap">No</th>
                    
                    @php
                        // Helper sederhana pencetak header sortable
                        $columns = [
                            'npwp'                  => ['label' => 'NPWP', 'align' => 'left'],
                            'nama_wp'               => ['label' => 'Nama WP', 'align' => 'left'],
                            'kd_klu'                => ['label' => 'KODE KLU', 'align' => 'center'],
                            'nm_klu'                => ['label' => 'Nama KLU', 'align' => 'left'],
                            'total_akt_pemeriksaan' => ['label' => 'TOTAL PKM PEMERIKSAAN', 'align' => 'right'],
                        ];
                    @endphp

                    @foreach($columns as $colKey => $colMeta)
                        @php
                            $nextDirection = ($sortColumn === $colKey && $sortDirection === 'asc') ? 'desc' : 'asc';
                            $sortUrl = request()->fullUrlWithQuery(['sort' => $colKey, 'direction' => $nextDirection]);
                            $alignClass = match($colMeta['align']) {
                                'center' => 'text-center justify-center',
                                'right'  => 'text-right justify-end',
                                default  => 'justify-start'
                            };
                        @endphp
                        <th class="py-3 px-3.5 whitespace-nowrap {{ $colMeta['align'] === 'center' ? 'text-center' : ($colMeta['align'] === 'right' ? 'text-right' : '') }}">
                            <a href="{{ $sortUrl }}" class="flex items-center {{ $alignClass }} gap-1 hover:text-indigo-600 transition select-none">
                                <span>{{ $colMeta['label'] }}</span>
                                @if($sortColumn !== $colKey)
                                    <i class="fa-solid fa-sort text-slate-300 ml-1 text-xs"></i>
                                @elseif($sortDirection === 'asc')
                                    <i class="fa-solid fa-sort-up text-indigo-600 ml-1 text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort-down text-indigo-600 ml-1 text-xs"></i>
                                @endif
                            </a>
                        </th>
                    @endforeach
                </tr>
            </thead>
            
            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                @forelse($pkmData as $index => $row)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-2.5 px-3.5 text-center text-slate-400 font-mono text-xs">
                            {{ $pkmData->firstItem() + $index }}
                        </td>
                        <td class="py-2.5 px-3.5 font-mono font-semibold text-slate-800 whitespace-nowrap text-xs">
                            {{ $row->npwp15 }}
                        </td>
                        <td class="py-2.5 px-3.5 text-slate-900 font-semibold text-[13px]">
                            @if($row->nama_wp === 'WP Tidak Terdaftar')
                                <span class="text-rose-600 italic">WP Tidak Terdaftar</span>
                            @else
                                {{ $row->nama_wp }}
                            @endif
                        </td>
                        <td class="py-2.5 px-3.5 text-center font-mono text-slate-600 text-xs whitespace-nowrap">
                            {{ $row->kd_klu }}
                        </td>
                        <td class="py-2.5 px-3.5 text-slate-600 max-w-xs truncate text-xs" title="{{ $row->nm_klu }}">
                            {{ $row->nm_klu }}
                        </td>
                        <td class="py-2.5 px-3.5 text-right font-mono font-bold text-indigo-600 whitespace-nowrap">
                            Rp {{ number_format($row->total_akt_pemeriksaan ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-slate-400 italic text-sm">
                            Tidak ada data PKM Pemeriksaan yang sesuai dengan filter/pencarian.
                        </td>
                    </tr>
                @endforelse
            </tbody>

            @if($pkmData->count() > 0)
                <tfoot class="bg-slate-100/90 font-bold text-slate-900 border-t-2 border-slate-200 text-sm">
                    <tr>
                        <td colspan="5" class="py-3 px-3.5 text-center tracking-wider">TOTAL SUBHALAMAN INI</td>
                        <td class="py-3 px-3.5 text-right font-mono text-indigo-700 whitespace-nowrap">
                            Rp {{ number_format($pkmData->sum('total_akt_pemeriksaan'), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <!-- Paginasi -->
    <div class="p-3 border-t border-slate-100 bg-slate-50/50">
        {{ $pkmData->links() }}
    </div>
</div>

@endsection