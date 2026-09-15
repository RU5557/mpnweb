@extends('layouts.app')

@section('title', 'PKM Pengawasan - MPNWEB')

@section('content')

<!-- HEADER & FILTER (SEJAJAR) -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Penerimaan PKM Pengawasan</h1>
        <p class="text-base text-slate-500 mt-1">Rincian realisasi PKM Pengawasan s.d. bulan terpilih per Seksi dan Account Representative (AR)</p>
    </div>

    <!-- Filter Form (Diperbesar font & padding) -->
    <form action="{{ route('penerimaan.pkmpengawasan') }}" method="GET" class="bg-white border border-slate-200/80 rounded-2xl p-2.5 px-4 shadow-sm flex items-center gap-3">
        <!-- Filter Seksi Sumber Query -->
        <select name="seksi" class="bg-slate-50 border border-slate-200 text-slate-700 text-base rounded-xl px-4 py-2.5 font-medium outline-none">
            <option value="">-- Semua Seksi --</option>
            @foreach($daftarSeksi as $seksi)
                <option value="{{ $seksi }}" {{ request('seksi') == $seksi ? 'selected' : '' }}>{{ $seksi }}</option>
            @endforeach
        </select>

        <select name="bulan" class="bg-slate-50 border border-slate-200 text-slate-700 text-base rounded-xl px-4 py-2.5 font-medium outline-none">
            @foreach(range(1, 12) as $m)
                <option value="{{ $m }}" {{ request('bulan', date('m')) == $m ? 'selected' : '' }}>
                    s.d. {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                </option>
            @endforeach
        </select>

        <select name="tahun" class="bg-slate-50 border border-slate-200 text-slate-700 text-base rounded-xl px-4 py-2.5 font-medium outline-none">
            @foreach(range(date('Y') - 3, date('Y')) as $year)
                <option value="{{ $year }}" {{ request('tahun', date('Y')) == $year ? 'selected' : '' }}>{{ $year }}</option>
            @endforeach
        </select>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-base font-semibold px-5 py-2.5 rounded-xl transition shadow-sm">
            Terapkan
        </button>

        @if(request()->has('bulan') || request()->has('tahun') || request()->has('seksi'))
            <a href="{{ route('penerimaan.pkmpengawasan') }}" class="text-slate-400 hover:text-slate-600 text-base px-2 py-2 transition" title="Reset Filter">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        @endif
    </form>
</div>

<!-- TABEL PKM PENGAWASAN PER SEKSI & AR -->
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-3.5 h-3.5 rounded-full bg-emerald-500"></div>
            <h2 class="text-lg font-bold text-slate-800">Tabel Penerimaan PKM Pengawasan</h2>
        </div>
        <!-- Perbaikan 2: Tulisan Total AR dihapus -->
    </div>

    <div class="overflow-x-auto">
        <!-- Perbaikan 3: Font tabel dinaikkan ke text-base (16px) -->
        <table class="w-full text-left text-base">
            <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200 text-sm">
                <tr>
                    <th class="py-4 px-4 w-14 text-center">No</th>
                    <th class="py-4 px-4">Nama Seksi</th>
                    <th class="py-4 px-4">Nama AR</th>
                    <th class="py-4 px-4 text-right">Akt Pengawasan (Rp)</th>
                    <th class="py-4 px-4 text-right">Lainnya (Rp)</th>
                    <th class="py-4 px-4 text-right">WRA Pengawasan (Rp)</th>
                    <th class="py-4 px-4 text-right">Total PKM Pengawasan (Rp)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                @forelse($pkmData as $index => $row)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-4 px-4 text-center text-slate-400 font-mono text-sm">{{ $index + 1 }}</td>
                        <td class="py-4 px-4 font-semibold text-slate-800">
                            @if($row->nama_seksi === 'Unassign')
                                <span class="bg-rose-100 text-rose-700 px-3 py-1.5 rounded-lg text-sm border border-rose-200 inline-block font-bold">
                                    Unassign
                                </span>
                            @else
                                <span class="bg-slate-100 text-slate-700 px-3 py-1.5 rounded-lg text-sm border border-slate-200 inline-block">
                                    {{ $row->nama_seksi }}
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-slate-900 font-bold">
                            @if($row->nama_ar === 'Unassign')
                                <span class="text-rose-600 italic">Unassign</span>
                            @else
                                {{ $row->nama_ar }}
                            @endif
                        </td>
                        <td class="py-4 px-4 text-right font-mono text-slate-600">
                            Rp {{ number_format($row->total_akt_pengawasan ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 text-right font-mono text-slate-600">
                            Rp {{ number_format($row->total_lainnya ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 text-right font-mono text-slate-600">
                            Rp {{ number_format($row->total_wra_pengawasan ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 text-right font-mono font-bold text-emerald-600">
                            Rp {{ number_format($row->total_pkm_pengawasan ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400 italic text-base">
                            Tidak ada data PKM Pengawasan untuk filter bulan/tahun ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <!-- FOOTER TOTAL / SUMMARY -->
            @if($pkmData->count() > 0)
                <tfoot class="bg-slate-100/90 font-bold text-slate-900 border-t-2 border-slate-200 text-base">
                    <tr>
                        <td colspan="3" class="py-4 px-4 text-center tracking-wider">TOTAL KESELURUHAN</td>
                        <td class="py-4 px-4 text-right font-mono">
                            Rp {{ number_format($pkmData->sum('total_akt_pengawasan'), 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 text-right font-mono">
                            Rp {{ number_format($pkmData->sum('total_lainnya'), 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 text-right font-mono">
                            Rp {{ number_format($pkmData->sum('total_wra_pengawasan'), 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 text-right font-mono text-emerald-700">
                            Rp {{ number_format($pkmData->sum('total_pkm_pengawasan'), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection