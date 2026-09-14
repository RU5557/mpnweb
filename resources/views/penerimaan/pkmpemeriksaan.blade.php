@extends('layouts.app')

@section('title', 'PKM Pemeriksaan - MPNWEB')

@section('content')

<!-- HEADER & FILTER (SEJAJAR) -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Penerimaan PKM Pemeriksaan</h1>
        <p class="text-xs text-slate-500 mt-0.5">Top 10 Wajib Pajak kontributor terbesar hasil Pemeriksaan</p>
    </div>

    <!-- Filter Form -->
    <form action="{{ route('penerimaan.pkmpemeriksaan') }}" method="GET" class="bg-white border border-slate-200/80 rounded-2xl p-2 px-3 shadow-sm flex items-center gap-2">
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

<!-- TABEL TOP 10 WP PKM PEMERIKSAAN -->
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-full bg-blue-600"></div>
            <h2 class="text-sm font-bold text-slate-800">10 Wajib Pajak Terbesar PKM Pemeriksaan</h2>
        </div>
        <span class="bg-blue-100 text-blue-700 text-[11px] px-2.5 py-0.5 rounded-full font-bold">Top 10</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                <tr>
                    <th class="py-3.5 px-4 w-12 text-center">Rank</th>
                    <th class="py-3.5 px-4">NPWP</th>
                    <th class="py-3.5 px-4">Nama Wajib Pajak</th>
                    <th class="py-3.5 px-4">Sektor / KBLI</th>
                    <th class="py-3.5 px-4 text-right">Realisasi SKP (Rp)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                @forelse($topWpPemeriksaan as $index => $row)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 text-center font-bold text-slate-500 font-mono">#{{ $index + 1 }}</td>
                        <td class="py-3 px-4 font-mono text-slate-600">{{ $row->npwp ?? '-' }}</td>
                        <td class="py-3 px-4 text-slate-900 font-bold">{{ $row->nama_wp }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $row->sektor ?? 'Sektor Umum' }}</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-blue-600">Rp {{ number_format($row->total_realisasi ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-400 italic">Tidak ada data PKM Pemeriksaan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection