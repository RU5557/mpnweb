@extends('layouts.app')

@section('content')
    <div class="p-6">
        <!-- Title Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Matriks Kepatuhan Pelaporan SPT Coretax</h1>
                <p class="text-xs text-slate-500 mt-1">Status: Submitted | Pembetulan: Normal (0)</p>
            </div>
            <a href="{{ route('kepatuhan.pelaporan.export', request()->query()) }}"
                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-medium transition flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export CSV
            </a>
        </div>

        <!-- Layout Kiri - Kanan -->
        <div class="flex flex-col lg:flex-row gap-6 items-start">

            <!-- PANEL KIRI: FILTER (FIXED/STICKY) -->
            <div
                class="w-full lg:w-80 bg-white rounded-xl shadow-sm border border-slate-200 p-5 lg:sticky lg:top-6 shrink-0">
                <h2 class="text-sm font-bold text-slate-700 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter Data
                </h2>

                <form method="GET" action="{{ route('kepatuhan.pelaporan.index') }}" class="space-y-4">
                    <!-- NPWP -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">NPWP (9/15/16 Digit)</label>
                        <input type="text" name="npwp" value="{{ $filters['npwp'] ?? '' }}" placeholder="NPWP..."
                            class="w-full text-xs rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                    </div>

                    <!-- Nama WP -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Wajib Pajak</label>
                        <input type="text" name="nama" value="{{ $filters['nama'] ?? '' }}"
                            placeholder="Nama Wajib Pajak..."
                            class="w-full text-xs rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                    </div>

                    <!-- Jenis SPT -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis SPT</label>
                        <select name="jenis_spt"
                            class="w-full text-xs rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            <option value="">-- Semua Jenis SPT --</option>
                            @foreach ($optJenisSpt as $jenis)
                                <option value="{{ $jenis }}"
                                    {{ ($filters['jenis_spt'] ?? '') === $jenis ? 'selected' : '' }}>{{ $jenis }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tahun Pajak -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tahun Pajak</label>
                        <input type="number" name="thn_pajak" value="{{ $filters['thn_pajak'] ?? date('Y') }}"
                            placeholder="2025"
                            class="w-full text-xs rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                    </div>

                    <!-- Range Tgl Terima -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tgl Terima (Mulai)</label>
                        <input type="date" name="tgl_terima_mulai" value="{{ $filters['tgl_terima_mulai'] ?? '' }}"
                            class="w-full text-xs rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tgl Terima (Selesai)</label>
                        <input type="date" name="tgl_terima_selesai" value="{{ $filters['tgl_terima_selesai'] ?? '' }}"
                            class="w-full text-xs rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                    </div>

                    <!-- Account Representative -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Account Representative (AR)</label>
                        <select name="nip_ar"
                            class="w-full text-xs rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            <option value="">-- Semua AR --</option>
                            @foreach ($optAr as $ar)
                                <option value="{{ $ar->nip }}"
                                    {{ ($filters['nip_ar'] ?? '') === $ar->nip ? 'selected' : '' }}>
                                    {{ $ar->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-2 flex gap-2">
                        <a href="{{ route('kepatuhan.pelaporan.index') }}"
                            class="w-1/2 text-center py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-md transition">Reset</a>
                        <button type="submit"
                            class="w-1/2 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-md shadow-sm transition">Terapkan</button>
                    </div>
                </form>
            </div>

            <!-- PANEL KANAN: TABEL MATRIX HASSIL -->
            <div class="flex-1 w-full bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-[11px] text-left text-slate-600 border-collapse">
                        <thead class="uppercase bg-slate-50 text-slate-700 border-b border-slate-200">
                            <tr>
                                <th class="px-3 py-3 font-semibold border-r border-slate-200 min-w-[120px]">NPWP</th>
                                <th class="px-3 py-3 font-semibold border-r border-slate-200 min-w-[180px]">Nama WP</th>
                                <th class="px-3 py-3 font-semibold border-r border-slate-200 min-w-[130px]">Jenis SPT</th>
                                @for ($m = 1; $m <= 12; $m++)
                                    <th class="px-2 py-3 font-semibold text-center border-r border-slate-200 min-w-[75px]">
                                        Masa {{ $m }}
                                    </th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($matrixData as $row)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-3 py-2.5 font-mono text-slate-800 border-r border-slate-100">
                                        {{ $row->npwp ?? '-' }}</td>
                                    <td class="px-3 py-2.5 font-medium text-slate-900 border-r border-slate-100 uppercase truncate max-w-[200px]"
                                        title="{{ $row->nama }}">
                                        {{ $row->nama ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2.5 text-slate-700 border-r border-slate-100 font-semibold">
                                        {{ $row->jenis_spt ?? '-' }}</td>

                                    {{-- Kolom Masa 1 s.d. 12 --}}
                                    @for ($m = 1; $m <= 12; $m++)
                                        @php
                                            $colKey = 'm_' . sprintf('%02d', $m);
                                            $valDate = $row->$colKey;
                                        @endphp
                                        <td class="px-1 py-2 text-center border-r border-slate-100">
                                            @if ($valDate)
                                                <span
                                                    class="inline-block px-1.5 py-0.5 bg-emerald-50 text-emerald-700 font-mono text-[10px] rounded font-semibold border border-emerald-200/60"
                                                    title="Tanggal Terima: {{ $valDate }}">
                                                    {{ date('d/m/y', strtotime($valDate)) }}
                                                </span>
                                            @else
                                                <span class="text-slate-300 font-mono">-</span>
                                            @endif
                                        </td>
                                    @endfor
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="15" class="px-6 py-12 text-center text-slate-400">
                                        <svg class="w-8 h-8 mx-auto text-slate-300 mb-2" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Data tidak ditemukan untuk kriteria filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Links -->
                @if ($matrixData->hasPages())
                    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $matrixData->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
@endsection
