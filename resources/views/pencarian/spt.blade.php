@extends('layouts.app')

@section('title', 'Pencarian Tanda Terima SPT')

@section('content')
    <div x-data="{ loading: false }">

        <!-- HEADER PAGE -->
        <div class="mb-4">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Pencarian Tanda Terima SPT</h1>
            <p class="text-sm text-slate-500 mt-0.5">Cari dan filter data Tanda Terima SPT Coretax beserta profil AR Wajib
                Pajak</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-5 items-start">

            <div class="w-full lg:w-80 flex-shrink-0 bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2 text-slate-800 font-bold text-sm">
                        <i class="fa-solid fa-filter text-blue-600"></i>
                        <span>Filter SPT</span>
                    </div>
                    <a href="{{ route('pencarian.spt') }}"
                        class="text-xs text-slate-400 hover:text-slate-600 flex items-center gap-1 transition">
                        <i class="fa-solid fa-rotate-left text-[10px]"></i>
                        <span>Reset</span>
                    </a>
                </div>

                <form id="searchSptForm" action="{{ route('pencarian.spt') }}" method="GET" @submit="loading = true"
                    class="space-y-3.5">
                    <input type="hidden" name="has_search" value="1">

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">NPWP (16 / 15 / 9 Digit)</label>
                        <input type="text" name="npwp" value="{{ request('npwp') }}" placeholder="Masukkan NPWP..."
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Wajib Pajak</label>
                        <input type="text" name="nama" value="{{ request('nama') }}" placeholder="Masukkan Nama WP..."
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Masa Awal</label>
                            <input type="number" min="1" max="12" name="masa1"
                                value="{{ request('masa1') }}" placeholder="1"
                                class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Masa Akhir</label>
                            <input type="number" min="1" max="12" name="masa2"
                                value="{{ request('masa2') }}" placeholder="12"
                                class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Thn Pajak</label>
                            <input type="text" name="thn_pajak" value="{{ request('thn_pajak') }}" placeholder="2026"
                                class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Pembetulan</label>
                            <select name="pembetulan"
                                class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="">-- Semua --</option>
                                @foreach ($pembetulanList as $pem)
                                    <option value="{{ $pem }}"
                                        {{ request('pembetulan') == $pem ? 'selected' : '' }}>{{ $pem }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Jenis SPT - FIXED INCLUDE NULL -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis SPT</label>
                        <select name="jenis_spt"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua Jenis SPT --</option>
                            @foreach ($jenisSptList as $jenis)
                                @php
                                    $isNull = is_null($jenis) || trim((string) $jenis) === '';
                                    $value = $isNull ? '__NULL__' : $jenis;
                                    $label = $isNull ? '(Tanpa Jenis / Kosong)' : $jenis;
                                @endphp
                                <option value="{{ $value }}"
                                    {{ request('jenis_spt') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Status SPT</label>
                        <select name="status_spt"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua Status --</option>
                            @foreach ($statusSptList as $status)
                                <option value="{{ $status }}"
                                    {{ request('status_spt') == $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Terima (Mulai)</label>
                        <input type="date" name="tgl_terima_mulai" value="{{ request('tgl_terima_mulai') }}"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Terima (Sampai)</label>
                        <input type="date" name="tgl_terima_selesai" value="{{ request('tgl_terima_selesai') }}"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Account Representative (AR)</label>
                        <select name="nip_ar"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua AR --</option>
                            @foreach ($arList as $ar)
                                <option value="{{ $ar->nip }}" {{ request('nip_ar') == $ar->nip ? 'selected' : '' }}>
                                    {{ $ar->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-1">
                        <button type="submit" :disabled="loading"
                            class="w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white rounded-xl text-xs font-bold transition shadow-sm">
                            <i class="fa-solid fa-magnifying-glass text-[11px]" x-show="!loading"></i>
                            <i class="fa-solid fa-spinner fa-spin text-[11px]" x-show="loading" x-cloak></i>
                            <span x-text="loading ? 'Memuat...' : 'Cari SPT'"></span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="flex-1 min-w-0">
                @if ($hasSearch)
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-white">
                            <div class="text-xs text-slate-500">Menampilkan <span
                                    class="font-bold text-slate-800">{{ $sptList->total() }}</span> data SPT</div>
                            @if ($sptList->total() > 0)
                                <a href="{{ route('pencarian.spt.export', request()->query()) }}"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-sm">
                                    <i class="fa-solid fa-file-excel text-xs"></i>
                                    <span>Export CSV</span>
                                </a>
                            @endif
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-slate-700">
                                <thead
                                    class="bg-slate-100/80 text-slate-600 font-bold uppercase text-[11px] tracking-wider border-b border-slate-200">
                                    <tr>
                                        <th class="p-3.5 whitespace-nowrap">No. BPE / Tanda Terima</th>
                                        <th class="p-3.5">NPWP & Wajib Pajak</th>
                                        <th class="p-3.5">Jenis & Status SPT</th>
                                        <th class="p-3.5 text-center whitespace-nowrap">Masa / Thn Pajak</th>
                                        <th class="p-3.5 text-center whitespace-nowrap">Pembetulan</th>
                                        <th class="p-3.5 whitespace-nowrap">Tgl Terima</th>
                                        <th class="p-3.5 whitespace-nowrap">Account Representative</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($sptList as $item)
                                        <tr class="hover:bg-blue-50/40 transition odd:bg-white even:bg-slate-50/50">
                                            <td class="p-3.5 font-mono text-xs font-bold text-blue-700 whitespace-nowrap">
                                                {{ $item->nomor_tanda_terima ?? '-' }}<div
                                                    class="text-[11px] text-slate-400 font-normal mt-0.5">Kanal:
                                                    {{ $item->kanal_pelaporan ?? '-' }}</div>
                                            </td>
                                            <td class="p-3.5">
                                                <div class="font-bold text-slate-900 text-xs">{{ $item->nama }}</div>
                                                <div class="font-mono text-xs text-slate-500 mt-0.5">{{ $item->npwp }}
                                                </div>
                                            </td>
                                            <td class="p-3.5 whitespace-nowrap">
                                                <div class="font-semibold text-xs text-slate-800">
                                                    @if (is_null($item->jenis_spt) || trim($item->jenis_spt) === '')
                                                        <span class="text-slate-400 italic">(Tanpa Jenis)</span>
                                                    @else
                                                        {{ $item->jenis_spt }}
                                                    @endif
                                                </div>
                                                <span
                                                    class="inline-block mt-1 px-2 py-0.5 text-[10px] rounded-md font-semibold {{ strtolower($item->status_spt) == 'nihil' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-amber-100 text-amber-800 border border-amber-200' }}">{{ $item->status_spt ?? 'N/A' }}</span>
                                            </td>
                                            <td
                                                class="p-3.5 text-center font-mono text-xs text-slate-700 whitespace-nowrap">
                                                {{ $item->masa1 }}-{{ $item->masa2 }} / {{ $item->thn_pajak }}</td>
                                            <td class="p-3.5 text-center whitespace-nowrap"><span
                                                    class="px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200/80 font-mono">{{ $item->pembetulan }}</span>
                                            </td>
                                            <td class="p-3.5 text-xs text-slate-600 whitespace-nowrap">
                                                {{ $item->tgl_terima ? \Carbon\Carbon::parse($item->tgl_terima)->format('d/m/Y') : '-' }}
                                            </td>
                                            <td class="p-3.5 whitespace-nowrap">
                                                <div class="text-xs font-semibold text-slate-800">
                                                    {{ $item->nama_ar ?? 'Unassigned' }}</div>
                                                <div class="text-[11px] font-mono text-slate-400 mt-0.5">
                                                    {{ $item->nip_ar ?? '-' }}</div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="p-8 text-center text-slate-400 italic">Data Tanda
                                                Terima SPT tidak ditemukan berdasarkan kriteria pencarian ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 border-t border-slate-100 bg-slate-50/50 rounded-b-2xl">{{ $sptList->links() }}
                        </div>
                    </div>
                @else
                    <div class="bg-white p-12 rounded-2xl shadow-sm border border-slate-200/80 text-center">
                        <div
                            class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-magnifying-glass text-2xl"></i></div>
                        <h3 class="text-base font-bold text-slate-800">Gunakan Filter di Sebelah Kiri</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Pilih parameter pencarian lalu klik tombol
                            <span class="font-semibold text-blue-600">"Cari SPT"</span> untuk menampilkan data Tanda Terima
                            SPT Coretax.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
