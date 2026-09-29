@extends('layouts.app')

@section('title', 'Pencarian Masterfile WP')

@section('content')
    <div class="space-y-6" x-data="{ loading: false }">

        <div class="mb-3">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Pencarian Masterfile WP</h1>
            <p class="text-sm text-slate-500 mt-1">Filter profil, Wilayah, KLU, dan pengawasan AR/JS Wajib Pajak</p>
        </div>

        <!-- ==================== MAIN LAYOUT (GRID 2 KOLOM) ==================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- SIDEBAR FILTER (KOLOM KIRI) -->
            <div class="lg:col-span-3 bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 space-y-4 sticky top-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2 text-slate-800 font-bold text-sm">
                        <i class="fa-solid fa-filter text-blue-600"></i>
                        <span>Filter Masterfile</span>
                    </div>
                    <a href="{{ route('pencarian.masterfile') }}"
                        class="text-xs text-slate-400 hover:text-blue-600 transition flex items-center gap-1"
                        title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </a>
                </div>

                <form id="searchMasterfileForm" action="{{ route('pencarian.masterfile') }}" method="GET"
                    @submit="loading = true" class="space-y-3">
                    <input type="hidden" name="has_search" value="1">
                    <input type="hidden" name="sort_by" value="{{ $sortBy ?? '' }}">
                    <input type="hidden" name="sort_order" value="{{ $sortOrder ?? 'asc' }}">

                    <!-- NPWP -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">NPWP (9/15/16 Digit)</label>
                        <input type="text" name="npwp" value="{{ $npwpInput ?? '' }}" placeholder="Masukkan NPWP..."
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- Nama WP -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Wajib Pajak</label>
                        <input type="text" name="nama" value="{{ $namaWp ?? '' }}" placeholder="Masukkan Nama WP..."
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <hr class="border-slate-100">

                    <!-- KLU -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">KLU</label>
                        <select name="klu"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-2.5 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua KLU --</option>
                            @foreach ($listKlu as $itemKlu)
                                <option value="{{ $itemKlu }}" {{ ($klu ?? '') == $itemKlu ? 'selected' : '' }}>
                                    {{ $itemKlu }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Kelurahan -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Kelurahan</label>
                        <select name="kelurahan"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-2.5 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua Kelurahan --</option>
                            @foreach ($listKelurahan as $itemKel)
                                <option value="{{ $itemKel }}" {{ ($kelurahan ?? '') == $itemKel ? 'selected' : '' }}>
                                    {{ $itemKel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Kecamatan -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Kecamatan</label>
                        <select name="kecamatan"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-2.5 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua Kecamatan --</option>
                            @foreach ($listKecamatan as $itemKec)
                                <option value="{{ $itemKec }}"
                                    {{ ($kecamatan ?? '') == $itemKec ? 'selected' : '' }}>
                                    {{ $itemKec }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Jenis WP -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis WP</label>
                        <select name="jenis"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-2.5 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua Jenis --</option>
                            @foreach ($listJenis as $itemJenis)
                                <option value="{{ $itemJenis }}" {{ ($jenis ?? '') == $itemJenis ? 'selected' : '' }}>
                                    {{ $itemJenis }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status WP -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Status WP</label>
                        <select name="status"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-2.5 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua Status --</option>
                            @foreach ($listStatus as $itemStatus)
                                <option value="{{ $itemStatus }}"
                                    {{ ($status ?? '') == $itemStatus ? 'selected' : '' }}>
                                    {{ $itemStatus }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <hr class="border-slate-100">

                    <!-- Tgl Daftar (Awal & Akhir) -->
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Tgl Daftar (Awal)</label>
                            <input type="date" name="tgl_daftar_awal" value="{{ $tglDaftarAwal ?? '' }}"
                                class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-2 py-1.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Tgl Daftar (Akhir)</label>
                            <input type="date" name="tgl_daftar_akhir" value="{{ $tglDaftarAkhir ?? '' }}"
                                class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-2 py-1.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- Account Officer (AR) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Account Officer (AR)</label>
                        <select name="nip_ar"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-2.5 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua AR --</option>
                            @foreach ($listAr as $ar)
                                <option value="{{ $ar->nip }}" {{ ($nipAr ?? '') == $ar->nip ? 'selected' : '' }}>
                                    {{ $ar->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Juru Sita (JS) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Juru Sita (JS)</label>
                        <select name="nip_js"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-2.5 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua JS --</option>
                            @foreach ($listJs as $js)
                                <option value="{{ $js->nip }}" {{ ($nipJs ?? '') == $js->nip ? 'selected' : '' }}>
                                    {{ $js->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tombol Cari -->
                    <div class="pt-2">
                        <button type="submit" :disabled="loading"
                            :class="loading ? 'opacity-75 cursor-not-allowed' : ''"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs py-2.5 rounded-xl transition shadow-md shadow-blue-500/20 flex items-center justify-center gap-1.5">
                            <i x-show="loading" class="fa-solid fa-circle-notch fa-spin text-xs" x-cloak></i>
                            <i x-show="!loading" class="fa-solid fa-search text-xs"></i>
                            <span x-text="loading ? 'Mencari...' : 'Cari WP'"></span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- TABEL HASIL (KOLOM KANAN) -->
            <div class="lg:col-span-9">
                @if ($hasSearch && isset($results))
                    <div x-show="!loading" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-visible">

                        <!-- HEADER TABEL & EXPORT -->
                        <div
                            class="p-4 border-b border-slate-100 flex flex-wrap justify-between items-center bg-slate-50/70 gap-3 rounded-t-2xl">
                            <span class="text-xs font-medium text-slate-600">
                                Hasil Pencarian (Total: <span
                                    class="text-blue-600 font-bold">{{ number_format($results->total(), 0, ',', '.') }}</span>
                                data)
                            </span>

                            @if ($results->total() > 0)
                                <a href="{{ route('pencarian.masterfile.export', request()->all()) }}"
                                    class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs px-3.5 py-2 rounded-xl transition shadow-sm shadow-emerald-600/20">
                                    <i class="fa-solid fa-file-csv text-sm"></i>
                                    <span>Export CSV</span>
                                </a>
                            @endif
                        </div>

                        @php
                            function sortUrlMf($field, $currentSortBy, $currentSortOrder)
                            {
                                $nextOrder = $currentSortBy === $field && $currentSortOrder === 'asc' ? 'desc' : 'asc';
                                return request()->fullUrlWithQuery(['sort_by' => $field, 'sort_order' => $nextOrder]);
                            }
                        @endphp

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-slate-700">
                                <thead
                                    class="bg-slate-100/80 text-slate-600 font-bold uppercase text-[11px] tracking-wider border-b border-slate-200">
                                    <tr>
                                        <th class="p-3.5">
                                            <a href="{{ sortUrlMf('npwp15', $sortBy ?? '', $sortOrder ?? 'asc') }}"
                                                class="flex items-center gap-1.5 hover:text-blue-600">
                                                NPWP / NPWP16
                                                @if (($sortBy ?? '') === 'npwp15')
                                                    <i
                                                        class="fa-solid fa-arrow-{{ ($sortOrder ?? 'asc') === 'asc' ? 'up' : 'down' }} text-blue-600"></i>
                                                @endif
                                            </a>
                                        </th>
                                        <th class="p-3.5">
                                            <a href="{{ sortUrlMf('nama', $sortBy ?? '', $sortOrder ?? 'asc') }}"
                                                class="flex items-center gap-1.5 hover:text-blue-600">
                                                Nama WP / KLU
                                                @if (($sortBy ?? '') === 'nama')
                                                    <i
                                                        class="fa-solid fa-arrow-{{ ($sortOrder ?? 'asc') === 'asc' ? 'up' : 'down' }} text-blue-600"></i>
                                                @endif
                                            </a>
                                        </th>
                                        <th class="p-3.5">Alamat / Wilayah</th>
                                        <th class="p-3.5">Jenis / Status</th>
                                        <th class="p-3.5">
                                            <a href="{{ sortUrlMf('tanggal_daftar', $sortBy ?? '', $sortOrder ?? 'asc') }}"
                                                class="flex items-center gap-1.5 hover:text-blue-600">
                                                Tgl Daftar
                                                @if (($sortBy ?? '') === 'tanggal_daftar')
                                                    <i
                                                        class="fa-solid fa-arrow-{{ ($sortOrder ?? 'asc') === 'asc' ? 'up' : 'down' }} text-blue-600"></i>
                                                @endif
                                            </a>
                                        </th>
                                        <th class="p-3.5">AR / JS</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($results as $item)
                                        <tr class="hover:bg-blue-50/40 transition odd:bg-white even:bg-slate-50/50">
                                            <td class="p-3.5 font-mono font-semibold text-slate-900 whitespace-nowrap">
                                                <div>{{ $item->npwp15 ?? $item->npwp }}</div>
                                                @if (!empty($item->npwp16))
                                                    <div class="text-[11px] text-slate-400 font-normal">NIK/16:
                                                        {{ $item->npwp16 }}</div>
                                                @endif
                                            </td>
                                            <td class="p-3.5">
                                                <div class="text-[13px] font-semibold text-slate-800">{{ $item->nama }}
                                                </div>
                                                <div class="text-[11px] text-slate-400">KLU: {{ $item->klu ?? '-' }}</div>
                                            </td>
                                            <td class="p-3.5 text-xs leading-relaxed max-w-xs">
                                                {{ $item->alamat }}
                                                @if ($item->kelurahan || $item->kecamatan)
                                                    <div class="text-slate-400 text-[11px]">Kel.
                                                        {{ $item->kelurahan ?? '-' }}, Kec. {{ $item->kecamatan ?? '-' }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="p-3.5 whitespace-nowrap">
                                                <div class="font-semibold text-slate-700 text-xs mb-0.5">
                                                    {{ $item->jenis ?? '-' }}</div>
                                                <span
                                                    class="bg-slate-200/80 text-slate-700 px-2 py-0.5 rounded-md text-[10px] font-bold">{{ $item->status ?? '-' }}</span>
                                            </td>
                                            <td class="p-3.5 whitespace-nowrap text-xs text-slate-600">
                                                {{ $item->tanggal_daftar ? date('d-m-Y', strtotime($item->tanggal_daftar)) : '-' }}
                                            </td>
                                            <td class="p-3.5 whitespace-nowrap">
                                                <div class="text-xs font-semibold text-slate-800">AR: <span
                                                        class="text-slate-600 font-normal">{{ $item->nama_ar ?? '-' }}</span>
                                                </div>
                                                <div class="text-[11px] font-semibold text-slate-500">JS: <span
                                                        class="text-slate-500 font-normal">{{ $item->nama_js ?? '-' }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="p-8 text-center text-slate-400 italic">Data
                                                Masterfile tidak ditemukan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="p-4 border-t border-slate-100 bg-slate-50/50 rounded-b-2xl">
                            {{ $results->links() }}
                        </div>
                    </div>
                @else
                    <!-- CARD PANDUAN / PLACEHOLDER SEBELUM FILTER DITERAPKAN -->
                    <div class="bg-white p-12 rounded-2xl shadow-sm border border-slate-200/80 text-center">
                        <div
                            class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-magnifying-glass text-2xl"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-800">Gunakan Filter di Sebelah Kiri</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                            Pilih parameter pencarian lalu klik tombol <span class="font-semibold text-blue-600">"Cari
                                WP"</span> untuk menampilkan data Masterfile.
                        </p>
                    </div>
                @endif
            </div>

        </div>
    </div>
@endsection
