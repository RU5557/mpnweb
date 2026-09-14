@extends('layouts.app')

@section('title', 'PKM Pengawasan - MPNWEB')

@section('content')

<!-- HEADER & FILTER (SEJAJAR) -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Penerimaan PKM Pengawasan</h1>
        <p class="text-xs text-slate-500 mt-0.5">Rincian realisasi dan capaian target PKM per Seksi & Account Representative (AR)</p>
    </div>

    <!-- Filter Form -->
    <form action="{{ route('penerimaan.pkmpengawasan') }}" method="GET" class="bg-white border border-slate-200/80 rounded-2xl p-2 px-3 shadow-sm flex items-center gap-2">
        <select name="seksi" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-xl px-3 py-2 font-medium outline-none">
            <option value="">-- Semua Seksi --</option>
            @foreach($daftarSeksi as $seksi)
                <option value="{{ $seksi }}" {{ request('seksi') == $seksi ? 'selected' : '' }}>{{ $seksi }}</option>
            @endforeach
        </select>

        <select name="bulan" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-xl px-3 py-2 font-medium outline-none">
            @foreach(range(1, 12) as $m)
                <option value="{{ $m }}" {{ request('bulan', date('m')) == $m ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                </option>
            @endforeach
        </select>

        <select name="tahun" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-xl px-3 py-2 font-medium outline-none">
            @foreach(range(date('Y') - 3, date('Y')) as $year)
                <option value="{{ $year }}" {{ request('tahun', date('Y')) == $year ? 'selected' : '' }}>{{ $year }}</option>
            @endforeach
        </select>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2 rounded-xl transition shadow-sm">
            Terapkan
        </button>
    </form>
</div>

<!-- TABEL PKM PER SEKSI PER AR -->
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
            <h2 class="text-sm font-bold text-slate-800">Capaian Realisasi PKM per Seksi & AR</h2>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                <tr>
                    <th class="py-3.5 px-4 w-12 text-center">No</th>
                    <th class="py-3.5 px-4">Seksi Pengawasan</th>
                    <th class="py-3.5 px-4">Nama AR</th>
                    <th class="py-3.5 px-4 text-right">Target (Rp)</th>
                    <th class="py-3.5 px-4 text-right">Realisasi (Rp)</th>
                    <th class="py-3.5 px-4 text-center">Capaian (%)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                @forelse($pkmData as $index => $row)
                    @php
                        $capaian = $row->target_ar > 0 ? ($row->total_realisasi / $row->target_ar) * 100 : 0;
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 text-center text-slate-400 font-mono">{{ $index + 1 }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-800">
                            <span class="bg-slate-100 text-slate-700 px-2 py-1 rounded-md text-[11px] border border-slate-200">
                                {{ $row->nama_seksi }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-900 font-semibold">{{ $row->nama_ar }}</td>
                        <td class="py-3 px-4 text-right font-mono text-slate-600">Rp {{ number_format($row->target_ar ?? 0, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">Rp {{ number_format($row->total_realisasi ?? 0, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $capaian >= 100 ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-amber-100 text-amber-800 border-amber-300' }}">
                                {{ number_format($capaian, 1) }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400 italic">Tidak ada data PKM Pengawasan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection