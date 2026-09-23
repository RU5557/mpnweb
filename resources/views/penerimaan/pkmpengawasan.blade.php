@extends('layouts.app')

@section('title', 'PKM Pengawasan - MPNWEB')

@section('content')

<!-- HEADER & FILTER CONTAINER -->
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
    
    <!-- Judul & Subjudul -->
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Penerimaan PKM Pengawasan</h1>
        <p class="text-xs text-slate-500 mt-1">Rincian realisasi PKM Pengawasan s.d. bulan terpilih per Seksi dan AR</p>
    </div>

    <!-- Form Filter Compact -->
    <form action="{{ route('penerimaan.pkmpengawasan') }}" method="GET" class="bg-white border border-slate-200 rounded-xl p-2 px-3 shadow-sm flex flex-wrap items-center gap-2">
        <!-- Preserve Current Sort State -->
        <input type="hidden" name="sort" value="{{ $sortColumn }}">
        <input type="hidden" name="direction" value="{{ $sortDirection }}">

        <!-- Select Seksi -->
        <select name="seksi" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-lg px-2.5 py-1.5 font-medium focus:ring-2 focus:ring-blue-500 outline-none transition cursor-pointer">
            <option value="">-- Semua Seksi --</option>
            @foreach($daftarSeksi as $seksi)
                <option value="{{ $seksi }}" {{ $seksiFilter === $seksi ? 'selected' : '' }}>{{ $seksi }}</option>
            @endforeach
        </select>

        <!-- Select Bulan -->
        <select name="bulan" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-lg px-2.5 py-1.5 font-medium focus:ring-2 focus:ring-blue-500 outline-none transition cursor-pointer">
            @foreach(range(1, 12) as $m)
                @php
                    $monthName = \Carbon\Carbon::create()->month($m)->translatedFormat('F');
                @endphp
                <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                    s.d. {{ $monthName }}
                </option>
            @endforeach
        </select>

        <!-- Select Tahun -->
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
        @if(request()->has('bulan') || request()->has('tahun') || request()->has('seksi') || request()->has('sort'))
            <a href="{{ route('penerimaan.pkmpengawasan') }}" class="text-slate-400 hover:text-slate-600 text-xs px-1.5 py-1.5 transition" title="Reset Filter">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        @endif

        <!-- Tombol Export Detil Transaksi -->
        <a href="{{ route('pkm.pengawasan.export-detil', request()->all()) }}" 
           class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3.5 py-1.5 rounded-lg transition shadow-sm flex items-center gap-1.5 border border-emerald-600">
            <i class="fa-solid fa-file-excel text-xs"></i>
            <span>Export CSV</span>
        </a>
    </form>
</div>

<!-- TABEL PKM PENGAWASAN -->
<div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
            <h2 class="text-sm font-bold text-slate-800">Tabel Penerimaan PKM Pengawasan</h2>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                <tr>
                    <th class="py-3 px-3.5 w-12 text-center whitespace-nowrap">No</th>

                    @php
                        $cols = [
                            'nama_seksi'           => ['label' => 'Nama Seksi', 'align' => 'left'],
                            'nama_ar'              => ['label' => 'Nama AR', 'align' => 'left'],
                            'total_akt_pengawasan' => ['label' => 'Akt Pengawasan', 'align' => 'right'],
                            'total_lainnya'        => ['label' => 'Lainnya', 'align' => 'right'],
                            'total_wra_pengawasan' => ['label' => 'WRA Pengawasan', 'align' => 'right'],
                            'total_pkm_pengawasan' => ['label' => 'Total PKM Pengawasan', 'align' => 'right'],
                        ];
                    @endphp

                    @foreach($cols as $colKey => $colMeta)
                        @php
                            $nextDir = ($sortColumn === $colKey && $sortDirection === 'asc') ? 'desc' : 'asc';
                            $sortUrl = request()->fullUrlWithQuery(['sort' => $colKey, 'direction' => $nextDir]);
                        @endphp
                        <th class="py-3 px-3.5 whitespace-nowrap {{ $colMeta['align'] === 'right' ? 'text-right' : '' }}">
                            <a href="{{ $sortUrl }}" class="flex items-center {{ $colMeta['align'] === 'right' ? 'justify-end' : '' }} gap-1 hover:text-blue-600 transition select-none">
                                <span>{{ $colMeta['label'] }}</span>
                                @if($sortColumn !== $colKey)
                                    <i class="fa-solid fa-sort text-slate-300 text-xs ml-0.5"></i>
                                @elseif($sortDirection === 'asc')
                                    <i class="fa-solid fa-sort-up text-blue-600 text-xs ml-0.5"></i>
                                @else
                                    <i class="fa-solid fa-sort-down text-blue-600 text-xs ml-0.5"></i>
                                @endif
                            </a>
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                @forelse($pkmData as $index => $row)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-2.5 px-3.5 text-center text-slate-400 font-mono">{{ $index + 1 }}</td>
                        <td class="py-2.5 px-3.5 font-semibold text-slate-800">
                            @if($row->nama_seksi === 'Unassign')
                                <span class="bg-rose-50 text-rose-700 px-2 py-0.5 rounded-md text-[11px] border border-rose-200 inline-block font-bold">
                                    Unassign
                                </span>
                            @else
                                <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded-md text-[11px] border border-slate-200 inline-block">
                                    {{ $row->nama_seksi }}
                                </span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3.5 text-slate-900 font-semibold">
                            @if($row->nama_ar === 'Unassign')
                                <span class="text-rose-600 italic">Unassign</span>
                            @else
                                {{ $row->nama_ar }}
                            @endif
                        </td>
                        <td class="py-2.5 px-3.5 text-right font-mono text-slate-600">
                            Rp {{ number_format($row->total_akt_pengawasan ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="py-2.5 px-3.5 text-right font-mono text-slate-600">
                            Rp {{ number_format($row->total_lainnya ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="py-2.5 px-3.5 text-right font-mono text-slate-600">
                            Rp {{ number_format($row->total_wra_pengawasan ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="py-2.5 px-3.5 text-right font-mono font-bold text-emerald-600">
                            Rp {{ number_format($row->total_pkm_pengawasan ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-slate-400 italic text-xs">
                            Tidak ada data PKM Pengawasan untuk filter bulan/tahun ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>

            @if($pkmData->count() > 0)
                <tfoot class="bg-slate-100/80 font-bold text-slate-900 border-t-2 border-slate-200 text-xs">
                    <tr>
                        <td colspan="3" class="py-3 px-3.5 text-center tracking-wider uppercase">Total Keseluruhan</td>
                        <td class="py-3 px-3.5 text-right font-mono">
                            Rp {{ number_format($pkmData->sum('total_akt_pengawasan'), 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-3.5 text-right font-mono">
                            Rp {{ number_format($pkmData->sum('total_lainnya'), 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-3.5 text-right font-mono">
                            Rp {{ number_format($pkmData->sum('total_wra_pengawasan'), 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-3.5 text-right font-mono text-emerald-700">
                            Rp {{ number_format($pkmData->sum('total_pkm_pengawasan'), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection