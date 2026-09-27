@extends('layouts.app')

@section('title', 'Pencarian Detil Transaksi / DRM')

@section('content')
    <div x-data="{ loading: false }">

        <div class="mb-4">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Pencarian Detil Transaksi / DRM</h1>
            <p class="text-sm text-slate-500 mt-0.5">Saring dan telusuri transaksi pembayaran pajak secara real-time</p>
        </div>

        <!-- LAYOUT SIDE-BY-SIDE (KIRI: FILTER, KANAN: TABEL HASIL) -->
        <div class="flex flex-col lg:flex-row gap-5 items-start">

            <!-- ==================== SIDEBAR FILTER (SEBELAH KIRI) ==================== -->
            <div class="w-full lg:w-80 flex-shrink-0 bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2 text-slate-800 font-bold text-sm">
                        <i class="fa-solid fa-filter text-blue-600"></i>
                        <span>Filter DRM</span>
                    </div>
                    <a href="{{ route('pencarian.transaksi') }}"
                        class="text-xs text-slate-400 hover:text-slate-600 flex items-center gap-1 transition">
                        <i class="fa-solid fa-rotate-left text-[10px]"></i>
                        <span>Reset</span>
                    </a>
                </div>

                <form id="searchTransaksiForm" action="{{ route('pencarian.transaksi') }}" method="GET"
                    @submit="loading = true" class="space-y-3.5">
                    <input type="hidden" name="has_search" value="1">
                    <input type="hidden" name="sort_by" value="{{ $sortBy ?? 'tgl_setor' }}">
                    <input type="hidden" name="sort_order" value="{{ $sortOrder ?? 'desc' }}">

                    <!-- NPWP (9/15) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">NPWP (9 / 15 Digit)</label>
                        <input type="text" name="npwp" value="{{ $npwpInput ?? '' }}" placeholder="Masukkan NPWP..."
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- Nama WP -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Wajib Pajak</label>
                        <input type="text" name="nama" value="{{ $namaWp ?? '' }}" placeholder="Masukkan Nama WP..."
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- Kode MAP -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Kode MAP</label>
                        <input type="text" name="kd_map" value="{{ $kdMap ?? '' }}" placeholder="Contoh: 411121"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- Kode Bayar -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Kode Bayar</label>
                        <input type="text" name="kd_bayar" value="{{ $kdBayar ?? '' }}" placeholder="Contoh: 100"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- Tanggal Bayar/Setor (Start) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Setor (Mulai)</label>
                        <input type="date" name="tgl_setor_start" value="{{ $tglSetorStart ?? '' }}"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- Tanggal Bayar/Setor (End) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Setor (Sampai)</label>
                        <input type="date" name="tgl_setor_end" value="{{ $tglSetorEnd ?? '' }}"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- NTPN -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">NTPN</label>
                        <input type="text" name="ntpn" value="{{ $ntpn ?? '' }}" placeholder="16 Karakter NTPN"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- Kota -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Kota / Kabupaten</label>
                        <select name="kota"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua Kota --</option>
                            @foreach ($listKota ?? [] as $k)
                                <option value="{{ $k }}" {{ ($kotaSelected ?? '') === $k ? 'selected' : '' }}>
                                    {{ $k }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Jenis WP -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis Wajib Pajak</label>
                        <select name="jenis_wp"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua Jenis WP --</option>
                            @foreach ($listJenisWp ?? [] as $j)
                                <option value="{{ $j }}"
                                    {{ ($jenisWpSelected ?? '') === $j ? 'selected' : '' }}>{{ $j }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sektor Usaha (KLU) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Sektor Usaha (KLU)</label>
                        <select name="sektor"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua Sektor Usaha --</option>
                            @foreach ($listSektor ?? [] as $s)
                                <option value="{{ $s }}"
                                    {{ ($sektorSelected ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Seksi -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Seksi</label>
                        <select name="seksi_id"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua Seksi --</option>
                            @foreach ($listSeksi ?? [] as $sek)
                                <option value="{{ $sek->id }}"
                                    {{ ($seksiSelected ?? '') == $sek->id ? 'selected' : '' }}>{{ $sek->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Multi-select AR -->
                    @php
                        $listArArr = collect($listAr ?? [])
                            ->pluck('nip')
                            ->toArray();
                        $currentAr = array_values((array) ($nipAr ?? []));
                        $isAllAr = count($currentAr) === count($listArArr) && count($listArArr) > 0;
                    @endphp
                    <div x-data="{ open: false, selectAll: {{ $isAllAr ? 'true' : 'false' }}, selected: {{ json_encode($currentAr) }}, options: {{ json_encode($listArArr) }} }" class="relative">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama AR</label>
                        <button type="button" @click="open = !open"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2 text-left text-xs text-slate-800 flex justify-between items-center">
                            <span
                                x-text="selected.length === options.length && options.length > 0 ? 'Semua AR Terpilih' : (selected.length ? selected.length + ' AR Dipilih' : 'Semua AR')"></span>
                            <i class="fa-solid fa-chevron-down text-slate-400 text-xs"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                            class="absolute z-50 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-xl p-3 max-h-52 overflow-y-auto">
                            <label
                                class="flex items-center gap-2 font-bold text-xs pb-2 border-b border-slate-100 cursor-pointer">
                                <input type="checkbox" x-model="selectAll"
                                    @change="selected = selectAll ? [...options] : []"> Pilih Semua
                            </label>
                            @foreach ($listAr ?? [] as $ar)
                                <label
                                    class="flex items-center gap-2 text-xs py-1 cursor-pointer hover:bg-slate-50 px-1 rounded">
                                    <input type="checkbox" name="nip_ar[]" value="{{ $ar->nip }}"
                                        x-model="selected">
                                    <span class="truncate">{{ $ar->nama }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Multi-select JS -->
                    @php
                        $listJsArr = collect($listJs ?? [])
                            ->pluck('nip')
                            ->toArray();
                        $currentJs = array_values((array) ($nipJs ?? []));
                        $isAllJs = count($currentJs) === count($listJsArr) && count($listJsArr) > 0;
                    @endphp
                    <div x-data="{ open: false, selectAll: {{ $isAllJs ? 'true' : 'false' }}, selected: {{ json_encode($currentJs) }}, options: {{ json_encode($listJsArr) }} }" class="relative">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama JS</label>
                        <button type="button" @click="open = !open"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2 text-left text-xs text-slate-800 flex justify-between items-center">
                            <span
                                x-text="selected.length === options.length && options.length > 0 ? 'Semua JS Terpilih' : (selected.length ? selected.length + ' JS Dipilih' : 'Semua JS')"></span>
                            <i class="fa-solid fa-chevron-down text-slate-400 text-xs"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                            class="absolute z-50 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-xl p-3 max-h-52 overflow-y-auto">
                            <label
                                class="flex items-center gap-2 font-bold text-xs pb-2 border-b border-slate-100 cursor-pointer">
                                <input type="checkbox" x-model="selectAll"
                                    @change="selected = selectAll ? [...options] : []"> Pilih Semua
                            </label>
                            @foreach ($listJs ?? [] as $js)
                                <label
                                    class="flex items-center gap-2 text-xs py-1 cursor-pointer hover:bg-slate-50 px-1 rounded">
                                    <input type="checkbox" name="nip_js[]" value="{{ $js->nip }}"
                                        x-model="selected">
                                    <span class="truncate">{{ $js->nama }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Tombol Cari -->
                    <div class="pt-2">
                        <button type="submit" :disabled="loading"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs px-4 py-2.5 rounded-xl transition shadow-md shadow-blue-500/20 flex items-center justify-center gap-2">
                            <i x-show="loading" class="fa-solid fa-circle-notch fa-spin text-xs" x-cloak></i>
                            <i x-show="!loading" class="fa-solid fa-search text-xs"></i>
                            <span x-text="loading ? 'Mencari...' : 'Cari DRM'"></span>
                        </button>
                    </div>

                </form>
            </div>

            <!-- ==================== TABEL HASIL (SEBELAH KANAN) ==================== -->
            <div class="flex-1 w-full min-w-0">
                @if (isset($results) && $results)
                    <div x-show="!loading"
                        class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-visible">
                        <div
                            class="p-4 border-b border-slate-100 flex flex-wrap justify-between items-center bg-slate-50/70 gap-3 rounded-t-2xl">
                            <span class="text-xs font-medium text-slate-600">
                                Hasil Pencarian (Total: <span
                                    class="text-blue-600 font-bold">{{ number_format($results->total(), 0, ',', '.') }}</span>
                                data)
                            </span>

                            @if ($results->total() > 0)
                                <a href="{{ route('pencarian.transaksi.export', request()->all()) }}"
                                    class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs px-3.5 py-2 rounded-xl transition shadow-sm shadow-emerald-600/20">
                                    <i class="fa-solid fa-file-csv text-sm"></i>
                                    <span>Export CSV</span>
                                </a>
                            @endif
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-slate-700">
                                <thead
                                    class="bg-slate-100/80 text-slate-600 font-bold uppercase text-[11px] tracking-wider border-b border-slate-200">
                                    <tr>
                                        <th class="p-3.5 whitespace-nowrap">Tgl Setor</th>
                                        <th class="p-3.5">NPWP15 / Nama WP</th>
                                        <th class="p-3.5">Fungsi</th>
                                        <th class="p-3.5">MAP / Bayar</th>
                                        <th class="p-3.5 whitespace-nowrap">Masa / Thn Pajak</th>
                                        <th class="p-3.5 text-right whitespace-nowrap">Jumlah Setor (Rp)</th>
                                        <th class="p-3.5 whitespace-nowrap">AR / JS</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($results as $item)
                                        <tr class="hover:bg-blue-50/40 transition odd:bg-white even:bg-slate-50/50">
                                            <td class="p-3.5 whitespace-nowrap text-xs font-medium text-slate-600">
                                                {{ $item->tgl_setor ? \Carbon\Carbon::parse($item->tgl_setor)->format('d/m/Y') : '-' }}
                                            </td>
                                            <td class="p-3.5">
                                                <div class="font-mono font-bold text-slate-900">{{ $item->npwp15 }}</div>
                                                <div class="text-xs text-slate-600 font-medium">
                                                    {{ $item->nama_wp ?? $item->nama_master }}</div>
                                            </td>
                                            <td class="p-3.5 whitespace-nowrap">
                                                <span
                                                    class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md font-mono text-xs font-semibold border border-slate-200/80">
                                                    {{ $item->fungsi ?? '-' }}
                                                </span>
                                            </td>
                                            <td class="p-3.5 whitespace-nowrap">
                                                <div class="font-mono font-bold text-slate-800">{{ $item->kd_map }} /
                                                    {{ $item->kd_bayar }}</div>
                                                <div class="text-[11px] text-slate-500">{{ $item->jenis_pajak ?? '-' }}
                                                </div>
                                            </td>
                                            <td class="p-3.5 whitespace-nowrap text-xs text-slate-600">
                                                Masa {{ $item->masa_pajak ?? '-' }} / {{ $item->thn_pajak ?? '-' }}
                                            </td>
                                            <td
                                                class="p-3.5 text-right font-semibold text-emerald-600 whitespace-nowrap text-sm">
                                                Rp {{ number_format($item->jml_setor, 0, ',', '.') }}
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
                                            <td colspan="7" class="p-8 text-center text-slate-400 italic">Data
                                                Transaksi tidak ditemukan.</td>
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
                    <!-- Tampilan Placeholder / Sebelum Cari -->
                    <div class="bg-white p-12 rounded-2xl shadow-sm border border-slate-200/80 text-center">
                        <div
                            class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-magnifying-glass text-2xl"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-800">Gunakan Filter di Sebelah Kiri</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                            Pilih parameter pencarian lalu klik tombol <span class="font-semibold text-blue-600">"Cari
                                DRM"</span> untuk menampilkan data DRM.
                        </p>
                    </div>
                @endif
            </div>

        </div>
    </div>
@endsection
