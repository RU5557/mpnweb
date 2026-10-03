@extends('layouts.app')

@section('title', 'Matriks Kepatuhan Pelaporan SPT')

@section('content')
    <div x-data="{ loading: false }">
        <!-- HEADER PAGE - SAMA KAYA PENCARIAN SPT -->
        <div class="mb-4">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Matriks Kepatuhan Pelaporan SPT Coretax</h1>
            <p class="text-sm text-slate-500 mt-0.5">Status: Submitted | Pembetulan: Normal (0)</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-5 items-start">

            <div class="w-full lg:w-80 flex-shrink-0 bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2 text-slate-800 font-bold text-sm">
                        <i class="fa-solid fa-filter text-blue-600"></i>
                        <span>Filter Data</span>
                    </div>
                    <a href="{{ route('kepatuhan.pelaporan.index') }}"
                        class="text-xs text-slate-400 hover:text-slate-600 flex items-center gap-1 transition">
                        <i class="fa-solid fa-rotate-left text-[10px]"></i>
                        <span>Reset</span>
                    </a>
                </div>

                <form method="GET" action="{{ route('kepatuhan.pelaporan.index') }}" @submit="loading = true"
                    class="space-y-3.5">

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">NPWP (9/15/16 Digit)</label>
                        <input type="text" name="npwp" value="{{ $filters['npwp'] ?? '' }}" placeholder="NPWP..."
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Wajib Pajak</label>
                        <input type="text" name="nama" value="{{ $filters['nama'] ?? '' }}"
                            placeholder="Nama Wajib Pajak..."
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis SPT</label>
                        <select name="jenis_spt"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua Jenis SPT --</option>
                            @foreach ($optJenisSpt as $jenis)
                                @php
                                    $isNull = is_null($jenis) || trim((string) $jenis) === '';
                                    $value = $isNull ? '__NULL__' : $jenis;
                                    $label = $isNull ? '(Tanpa Jenis / Kosong)' : $jenis;
                                    $selected = ($filters['jenis_spt'] ?? '') === $value ? 'selected' : '';
                                @endphp
                                <option value="{{ $value }}" {{ $selected }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tahun Pajak</label>
                        <input type="number" name="thn_pajak" value="{{ $filters['thn_pajak'] ?? '' }}"
                            placeholder="Semua Tahun (contoh: {{ date('Y') }})"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <p class="text-[10px] text-slate-400 mt-1">Kosongkan untuk tampil semua tahun</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tgl Terima (Mulai)</label>
                        <input type="date" name="tgl_terima_mulai" value="{{ $filters['tgl_terima_mulai'] ?? '' }}"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tgl Terima (Selesai)</label>
                        <input type="date" name="tgl_terima_selesai" value="{{ $filters['tgl_terima_selesai'] ?? '' }}"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Account Representative (AR)</label>
                        <select name="nip_ar"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua AR --</option>
                            @foreach ($optAr as $ar)
                                <option value="{{ $ar->nip }}"
                                    {{ ($filters['nip_ar'] ?? '') === $ar->nip ? 'selected' : '' }}>{{ $ar->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-1">
                        <button type="submit" :disabled="loading"
                            class="w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white rounded-xl text-xs font-bold transition shadow-sm">
                            <i class="fa-solid fa-magnifying-glass text-[11px]" x-show="!loading"></i>
                            <i class="fa-solid fa-spinner fa-spin text-[11px]" x-show="loading" x-cloak></i>
                            <span x-text="loading ? 'Memuat...' : 'Terapkan Filter'"></span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="flex-1 min-w-0">
                @if (isset($matrixData) && $matrixData->count() > 0)
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                        <!-- HEADER HASIL - SAMA KAYA PENCARIAN SPT -->
                        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-white">
                            <div class="text-xs text-slate-500">Menampilkan <span
                                    class="font-bold text-slate-800">{{ $matrixData->total() }}</span> data kepatuhan</div>
                            <a href="{{ route('kepatuhan.pelaporan.export', request()->query()) }}"
                                class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-sm">
                                <i class="fa-solid fa-file-excel text-xs"></i>
                                <span>Export CSV</span>
                            </a>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-slate-700">
                                <thead
                                    class="bg-slate-100/80 text-slate-600 font-bold uppercase text-[11px] tracking-wider border-b border-slate-200">
                                    <tr>
                                        <th class="p-3.5 whitespace-nowrap border-r border-slate-200/60">NPWP</th>
                                        <th class="p-3.5 whitespace-nowrap border-r border-slate-200/60 min-w-[180px]">Nama
                                            WP</th>
                                        <th class="p-3.5 whitespace-nowrap border-r border-slate-200/60 min-w-[130px]">Jenis
                                            SPT</th>
                                        @for ($m = 1; $m <= 12; $m++)
                                            <th
                                                class="p-3.5 font-bold text-center border-r border-slate-200/60 min-w-[75px] whitespace-nowrap">
                                                Masa {{ $m }}</th>
                                        @endfor
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($matrixData as $row)
                                        <tr class="hover:bg-blue-50/40 transition odd:bg-white even:bg-slate-50/50">
                                            <td
                                                class="p-3.5 font-mono text-xs font-bold text-slate-800 whitespace-nowrap border-r border-slate-100">
                                                {{ $row->npwp ?? '-' }}</td>
                                            <td class="p-3.5 border-r border-slate-100 max-w-[220px]">
                                                <div class="font-bold text-slate-900 text-xs uppercase truncate"
                                                    title="{{ $row->nama }}">{{ $row->nama ?? '-' }}</div>
                                            </td>
                                            <td
                                                class="p-3.5 text-xs font-semibold text-slate-700 border-r border-slate-100">
                                                @if (is_null($row->jenis_spt) || trim($row->jenis_spt) === '')
                                                    <span class="text-slate-400 italic">(Tanpa
                                                        Jenis)</span>@else{{ $row->jenis_spt }}
                                                @endif
                                            </td>
                                            @for ($m = 1; $m <= 12; $m++)
                                                @php
                                                    $colKey = 'm_' . sprintf('%02d', $m);
                                                    $valDate = $row->$colKey;
                                                @endphp<td class="p-2.5 text-center border-r border-slate-100">
                                                    @if ($valDate)
                                                        <span
                                                            class="inline-block px-2 py-1 bg-emerald-50 text-emerald-700 font-mono text-[10px] rounded-md font-bold border border-emerald-200/60"
                                                        title="{{ $valDate }}">{{ date('d/m/y', strtotime($valDate)) }}</span>@else<span
                                                            class="text-slate-300 font-mono text-xs">-</span>
                                                    @endif
                                                </td>
                                            @endfor
                                        </tr>
                                    @empty<tr>
                                            <td colspan="15" class="p-8 text-center text-slate-400 italic">Data tidak
                                                ditemukan untuk kriteria filter ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($matrixData->hasPages())
                            <div class="p-4 border-t border-slate-100 bg-slate-50/50 rounded-b-2xl">
                                {{ $matrixData->appends(request()->query())->links() }}</div>
                        @endif
                    </div>
                @else
                    <div class="bg-white p-12 rounded-2xl shadow-sm border border-slate-200/80 text-center">
                        <div
                            class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-magnifying-glass text-2xl"></i></div>
                        <h3 class="text-base font-bold text-slate-800">Gunakan Filter di Sebelah Kiri</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Pilih parameter filter lalu klik tombol
                            <span class="font-semibold text-blue-600">"Terapkan Filter"</span> untuk menampilkan data.</p>
                        @if (request()->hasAny(['npwp', 'nama', 'jenis_spt', 'thn_pajak', 'tgl_terima_mulai', 'tgl_terima_selesai', 'nip_ar']))
                            <p class="text-xs text-amber-600 mt-3 italic">Data tidak ditemukan untuk kriteria filter ini.
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
