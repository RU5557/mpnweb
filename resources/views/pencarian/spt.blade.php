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

        <!-- LAYOUT SIDE-BY-SIDE (KIRI: FILTER, KANAN: TABEL HASIL) -->
        <div class="flex flex-col lg:flex-row gap-5 items-start">

            <!-- ==================== SIDEBAR FILTER (SEBELAH KIRI) ==================== -->
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

                    <!-- NPWP -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">NPWP (16 / 15 / 9 Digit)</label>
                        <input type="text" name="npwp" value="{{ request('npwp') }}" placeholder="Masukkan NPWP..."
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- Nama WP -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Wajib Pajak</label>
                        <input type="text" name="nama" value="{{ request('nama') }}" placeholder="Masukkan Nama WP..."
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- Range Masa Pajak -->
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

                    <!-- Tahun Pajak & Pembetulan -->
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Thn Pajak</label>
                            <input type="text" name="thn_pajak" value="{{ request('thn_pajak') }}" placeholder="2026"
                                class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Pembetulan</label>
                            <input type="number" min="0" name="pembetulan" value="{{ request('pembetulan') }}"
                                placeholder="0"
                                class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- Jenis SPT -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis SPT</label>
                        <select name="jenis_spt"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua Jenis SPT --</option>
                            @foreach ($jenisSptList as $jenis)
                                <option value="{{ $jenis }}" {{ request('jenis_spt') == $jenis ? 'selected' : '' }}>
                                    {{ $jenis }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status SPT -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Status SPT</label>
                        <select name="status_spt"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua Status --</option>
                            @foreach ($statusSptList as $status)
                                <option value="{{ $status }}"
                                    {{ request('status_spt') == $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Range Tanggal Terima -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Terima (Mulai)</label>
                        <input type="date" name="tgl_terima_mulai" value="{{ request('tgl_terima_mulai') }}"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none mb-2">

                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Terima (Sampai)</label>
                        <input type="date" name="tgl_terima_selesai" value="{{ request('tgl_terima_selesai') }}"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- Account Representative -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Account Representative (AR)</label>
                        <select name="nip_ar"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua AR --</option>
                            @foreach ($arList as $ar)
                                <option value="{{ $ar->nip }}" {{ request('nip_ar') == $ar->nip ? 'selected' : '' }}>
                                    {{ $ar->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tombol Cari -->
                    <div class="pt-2">
                        <button type="submit" :disabled="loading"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs px-4 py-2.5 rounded-xl transition shadow-md shadow-blue-500/20 flex items-center justify-center gap-2">
                            <i x-show="loading" class="fa-solid fa-circle-notch fa-spin text-xs" x-cloak></i>
                            <i x-show="!loading" class="fa-solid fa-search text-xs"></i>
                            <span x-text="loading ? 'Mencari...' : 'Cari SPT'"></span>
                        </button>
                    </div>

                </form>
            </div>

            <!-- ==================== TABEL HASIL (SEBELAH KANAN) ==================== -->
            <div class="flex-1 w-full min-w-0">
                @if (isset($sptList) && $hasSearch)
                    <div x-show="!loading" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-visible">
                        <div
                            class="p-4 border-b border-slate-100 flex flex-wrap justify-between items-center bg-slate-50/70 gap-3 rounded-t-2xl">
                            <span class="text-xs font-medium text-slate-600">
                                Hasil Pencarian (Total: <span
                                    class="text-blue-600 font-bold">{{ number_format($sptList->total(), 0, ',', '.') }}</span>
                                BPE/SPT)
                            </span>
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
                                                {{ $item->nomor_tanda_terima ?? '-' }}
                                                <div class="text-[11px] text-slate-400 font-normal mt-0.5">Kanal:
                                                    {{ $item->kanal_pelaporan ?? '-' }}</div>
                                            </td>
                                            <td class="p-3.5">
                                                <div class="font-bold text-slate-900 text-xs">{{ $item->nama }}</div>
                                                <div class="font-mono text-xs text-slate-500 mt-0.5">{{ $item->npwp }}
                                                </div>
                                            </td>
                                            <td class="p-3.5 whitespace-nowrap">
                                                <div class="font-semibold text-xs text-slate-800">{{ $item->jenis_spt }}
                                                </div>
                                                <span
                                                    class="inline-block mt-1 px-2 py-0.5 text-[10px] rounded-md font-semibold {{ strtolower($item->status_spt) == 'nihil' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-amber-100 text-amber-800 border border-amber-200' }}">
                                                    {{ $item->status_spt ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td
                                                class="p-3.5 text-center font-mono text-xs text-slate-700 whitespace-nowrap">
                                                {{ $item->masa1 }}-{{ $item->masa2 }} / {{ $item->thn_pajak }}
                                            </td>
                                            <td class="p-3.5 text-center whitespace-nowrap">
                                                <span
                                                    class="px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200/80 font-mono">
                                                    {{ $item->pembetulan }}
                                                </span>
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
                                            <td colspan="7" class="p-8 text-center text-slate-400 italic">
                                                Data Tanda Terima SPT tidak ditemukan berdasarkan kriteria pencarian ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="p-4 border-t border-slate-100 bg-slate-50/50 rounded-b-2xl">
                            {{ $sptList->links() }}
                        </div>
                    </div>
                @else
                    <!-- Tampilan Placeholder / Sebelum Cari -->
                    <div class="bg-white p-12 rounded-2xl shadow-sm border border-slate-200/80 text-center">
                        <div
                            class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-magnifying-glass text-2xl"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-800">Gunakan Filter di Sebelah Kiri</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                            Pilih parameter pencarian lalu klik tombol <span class="font-semibold text-blue-600">"Cari
                                SPT"</span> untuk menampilkan data Tanda Terima SPT Coretax.
                        </p>
                    </div>
                @endif
            </div>

        </div>
    </div>
@endsection
