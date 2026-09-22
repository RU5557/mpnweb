@extends('layouts.app')

@section('title', 'Pencarian Data WP')

@section('content')
<div class="space-y-6" 
     x-data="{ 
         targetTable: '{{ $targetTable ?? 'masterfile' }}',
         loading: false 
     }">
    
    <div>
        <h1 class="text-xl font-bold text-slate-800">Pencarian Data Wajib Pajak</h1>
        <p class="text-xs text-slate-500">Cari informasi masterfile WP atau riwayat transaksi DRM penerimaan pajak.</p>
    </div>

    <!-- ==================== FORM PENCARIAN ==================== -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
        <form action="{{ route('wp.search') }}" 
              method="GET" 
              @submit="loading = true" 
              class="space-y-4">
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                
                <!-- Selector Target Tabel -->
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Target Tabel</label>
                    <select name="target_table" 
                            x-model="targetTable"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl p-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="masterfile">Masterfile WP</option>
                        <option value="detil_transaksi">Detil DRM</option>
                    </select>
                </div>

                <!-- Input Keyword Dinamis -->
                <div class="md:col-span-7">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">
                        Kata Kunci 
                        <span x-text="targetTable === 'masterfile' ? '(NPWP15 / NPWP16 / Nama WP)' : '(NPWP15 / Nama WP)'" class="text-slate-400 font-normal"></span>
                    </label>
                    <div class="relative">
                        <input type="text" name="q" value="{{ $keyword ?? '' }}" 
                               :placeholder="targetTable === 'masterfile' ? 'Masukkan NPWP15, NPWP16, atau Nama WP...' : 'Masukkan NPWP15 atau Nama WP...'" 
                               class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl pl-9 pr-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
                    </div>
                </div>

                <!-- Tombol Cari -->
                <div class="md:col-span-2">
                    <button type="submit" 
                            :disabled="loading"
                            :class="loading ? 'opacity-75 cursor-not-allowed' : ''"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs px-4 py-2.5 rounded-xl transition shadow-md shadow-blue-500/20 flex items-center justify-center gap-2">
                        <i x-show="loading" class="fa-solid fa-circle-notch fa-spin text-xs" x-cloak></i>
                        <i x-show="!loading" class="fa-solid fa-search text-xs"></i>
                        <span x-text="loading ? 'Mencari...' : 'Cari Data'"></span>
                    </button>
                </div>
            </div>

            <!-- Filter Transaksi Khusus Detil DRM -->
            <div x-show="targetTable === 'detil_transaksi'" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="pt-3 border-t border-slate-100">
                 
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3 items-center">
                    
                    <!-- Filter Tahun -->
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Tahun Setor</label>
                        <select name="thn_setor" class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua Tahun --</option>
                            @for($y = date('Y'); $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ ($thnSetor ?? '') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <!-- Filter Bulan -->
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Bulan Setor</label>
                        <select name="bln_setor" class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua Bulan --</option>
                            @php
                                $bulan = [
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ];
                            @endphp
                            @foreach($bulan as $key => $val)
                                <option value="{{ $key }}" {{ ($blnSetor ?? '') == $key ? 'selected' : '' }}>{{ $val }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Fungsi -->
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Fungsi</label>
                        <select name="fungsi" class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua Fungsi --</option>
                            @foreach($listFungsi ?? [] as $f)
                                <option value="{{ $f }}" {{ ($fungsi ?? '') == $f ? 'selected' : '' }}>{{ $f }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Nama AR -->
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Nama AR</label>
                        <select name="nip_ar" class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua AR --</option>
                            @foreach($listAr ?? [] as $ar)
                                <option value="{{ $ar->nip }}" {{ ($nipAr ?? '') == $ar->nip ? 'selected' : '' }}>{{ $ar->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Nama JS -->
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Nama JS</label>
                        <select name="nip_js" class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Semua JS --</option>
                            @foreach($listJs ?? [] as $js)
                                <option value="{{ $js->nip }}" {{ ($nipJs ?? '') == $js->nip ? 'selected' : '' }}>{{ $js->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <!-- ==================== HASIL TABEL ==================== -->
    @if($results)
        <div x-show="!loading" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <span class="text-xs font-semibold text-slate-600">
                    Hasil Pencarian di <strong class="text-slate-900">{{ $targetTable === 'masterfile' ? 'Masterfile WP' : 'Detil DRM' }}</strong> 
                    (Total: {{ $results->total() }} data)
                </span>
            </div>

            <div class="overflow-x-auto">
                @if($targetTable === 'masterfile')
                    <!-- Tabel Masterfile WP -->
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[11px] border-b">
                            <tr>
                                <th class="p-3">NPWP15 / NPWP16</th>
                                <th class="p-3">Nama Wajib Pajak</th>
                                <th class="p-3">Alamat</th>
                                <th class="p-3">Jenis / Status</th>
                                <th class="p-3">No. Telepon</th>
                                <th class="p-3">Account Representative</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($results as $item)
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 font-mono font-semibold text-slate-800">
                                        <div>{{ $item->npwp15 }}</div>
                                        @if($item->npwp16)
                                            <div class="text-[10px] text-slate-400 font-normal">NIK/16: {{ $item->npwp16 }}</div>
                                        @endif
                                    </td>
                                    <td class="p-3 font-medium text-slate-800">{{ $item->nama_wp }}</td>
                                    <td class="p-3 text-[11px]">
                                        {{ $item->alamat }}
                                        @if($item->kecamatan || $item->kota)
                                            <div class="text-slate-400 text-[10px]">{{ $item->kecamatan }}, {{ $item->kota }}</div>
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        <div class="font-semibold text-slate-700">{{ $item->jenis_wp ?? '-' }}</div>
                                        <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full text-[10px] font-bold">{{ $item->status_wp ?? '-' }}</span>
                                    </td>
                                    <td class="p-3">{{ $item->telp ?? '-' }}</td>
                                    <td class="p-3 font-medium text-slate-700">{{ $item->nama_ar ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-slate-400 italic">Data Masterfile tidak ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    <!-- Tabel Detil DRM -->
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[11px] border-b">
                            <tr>
                                <th class="p-3">Tgl Setor</th>
                                <th class="p-3">NPWP15 / Nama WP</th>
                                <th class="p-3">Fungsi</th>
                                <th class="p-3">MAP / Bayar</th>
                                <th class="p-3">Masa / Thn Pajak</th>
                                <th class="p-3 text-right">Jumlah Setor (Rp)</th>
                                <th class="p-3">AR / JS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($results as $item)
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 whitespace-nowrap">
                                        {{ $item->tgl_setor ? \Carbon\Carbon::parse($item->tgl_setor)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="p-3">
                                        <div class="font-mono font-semibold text-slate-800">{{ $item->npwp15 }}</div>
                                        <div class="text-[11px] text-slate-600">{{ $item->nama_wp ?? $item->nama_master }}</div>
                                    </td>
                                    <td class="p-3">
                                        <span class="bg-slate-100 text-slate-700 px-2 py-1 rounded font-mono text-[11px] font-semibold border border-slate-200">
                                            {{ $item->fungsi ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <div class="font-mono font-bold text-slate-700">{{ $item->kd_map }} / {{ $item->kd_bayar }}</div>
                                        <div class="text-[10px] text-slate-500">{{ $item->jenis_pajak ?? '-' }}</div>
                                    </td>
                                    <td class="p-3 whitespace-nowrap">
                                        Masa {{ $item->masa_pajak ?? '-' }} / {{ $item->thn_pajak ?? '-' }}
                                    </td>
                                    <td class="p-3 text-right font-semibold text-emerald-600 whitespace-nowrap">
                                        Rp {{ number_format($item->jml_setor, 0, ',', '.') }}
                                    </td>
                                    <td class="p-3">
                                        <div class="text-[11px] font-semibold text-slate-700">AR: {{ $item->nama_ar ?? '-' }}</div>
                                        <div class="text-[10px] text-slate-500">JS: {{ $item->nama_js ?? '-' }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-6 text-center text-slate-400 italic">Data Transaksi tidak ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $results->links() }}
            </div>
        </div>
    @endif
</div>
@endsection