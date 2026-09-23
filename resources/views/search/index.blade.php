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
        <form id="searchForm" action="{{ route('wp.search') }}" method="GET" @submit="loading = true" class="space-y-4">
            
            <!-- Flag Penanda Bahwa Form Telah Disubmit -->
            <input type="hidden" name="has_search" value="1">

            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                
                <!-- Selector Target Tabel -->
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Target Tabel</label>
                    <select name="target_table" 
                            x-model="targetTable"
                            @change="$nextTick(() => $el.form.submit())"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl p-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="masterfile" {{ $targetTable === 'masterfile' ? 'selected' : '' }}>Masterfile WP</option>
                        <option value="detil_transaksi" {{ $targetTable === 'detil_transaksi' ? 'selected' : '' }}>Detil DRM</option>
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
                 
                <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-center">
                    
                    <!-- 1. TAHUN SETOR (Checkbox Popover Dropdown) -->
                    @php
                        $listTahunArr = array_values(collect($listTahunSetor ?? [])->toArray());
                        $currentThn = array_values((array)($thnSetor ?? []));
                        $isAllThn = count($currentThn) === count($listTahunArr) && count($listTahunArr) > 0;
                    @endphp
                    <div x-data="{ 
                            open: false, 
                            selectAll: {{ $isAllThn ? 'true' : 'false' }}, 
                            selected: {{ json_encode($currentThn) }}, 
                            options: {{ json_encode($listTahunArr) }} 
                        }" 
                        x-init="$watch('selected', value => selectAll = (value.length === options.length && options.length > 0))"
                        class="relative">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tahun Setor</label>
                        <button type="button" @click="open = !open" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2 text-left text-xs text-slate-800 flex justify-between items-center">
                            <span x-text="selected.length === options.length && options.length > 0 ? 'Semua Tahun Terpilih' : (selected.length ? selected.length + ' Tahun Dipilih' : 'Tidak Ada Dipilih')"></span>
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-xl p-3 max-h-60 overflow-y-auto">
                            <label class="flex items-center gap-2 font-bold text-xs pb-2 border-b border-slate-100 cursor-pointer">
                                <input type="checkbox" x-model="selectAll" @change="selected = selectAll ? [...options] : []">
                                Pilih Semua
                            </label>
                            @foreach($listTahunSetor ?? [] as $thn)
                                <label class="flex items-center gap-2 text-xs py-1 cursor-pointer hover:bg-slate-50 px-1 rounded">
                                    <input type="checkbox" name="thn_setor[]" value="{{ $thn }}" x-model="selected">
                                    <span>{{ $thn }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- 2. BULAN SETOR (Checkbox Popover Dropdown) -->
                    @php
                        $bulanList = ['01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'];
                        $allBulanKeys = array_keys($bulanList);
                        $currentBln = array_values((array)($blnSetor ?? []));
                        $isAllBulan = count($currentBln) === count($allBulanKeys);
                    @endphp
                    <div x-data="{ 
                            open: false, 
                            selectAll: {{ $isAllBulan ? 'true' : 'false' }}, 
                            selected: {{ json_encode($currentBln) }},
                            allKeys: {{ json_encode($allBulanKeys) }}
                        }" 
                        x-init="$watch('selected', value => selectAll = value.length === allKeys.length)"
                        class="relative">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Bulan Setor</label>
                        <button type="button" @click="open = !open" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2 text-left text-xs text-slate-800 flex justify-between items-center">
                            <span x-text="selected.length === allKeys.length ? 'Semua Bulan Terpilih' : (selected.length ? selected.length + ' Bulan Dipilih' : 'Tidak Ada Dipilih')"></span>
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-xl p-3 max-h-60 overflow-y-auto">
                            <label class="flex items-center gap-2 font-bold text-xs pb-2 border-b border-slate-100 cursor-pointer">
                                <input type="checkbox" x-model="selectAll" @change="selected = selectAll ? [...allKeys] : []">
                                Pilih Semua
                            </label>
                            @foreach($bulanList as $key => $val)
                                <label class="flex items-center gap-2 text-xs py-1 cursor-pointer hover:bg-slate-50 px-1 rounded">
                                    <input type="checkbox" name="bln_setor[]" value="{{ $key }}" x-model="selected">
                                    <span>{{ $val }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- 3. FUNGSI (Checkbox Popover Dropdown) -->
                    @php
                        $listFungsiArr = array_values(collect($listFungsi ?? [])->toArray());
                        $currentFungsi = array_values((array)($fungsi ?? []));
                        $isAllFungsi = count($currentFungsi) === count($listFungsiArr) && count($listFungsiArr) > 0;
                    @endphp
                    <div x-data="{ 
                            open: false, 
                            selectAll: {{ $isAllFungsi ? 'true' : 'false' }}, 
                            selected: {{ json_encode($currentFungsi) }}, 
                            options: {{ json_encode($listFungsiArr) }} 
                        }" 
                        x-init="$watch('selected', value => selectAll = (value.length === options.length && options.length > 0))"
                        class="relative">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Fungsi</label>
                        <button type="button" @click="open = !open" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2 text-left text-xs text-slate-800 flex justify-between items-center">
                            <span x-text="selected.length === options.length && options.length > 0 ? 'Semua Fungsi Terpilih' : (selected.length ? selected.length + ' Fungsi Dipilih' : 'Tidak Ada Dipilih')"></span>
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-xl p-3 max-h-60 overflow-y-auto">
                            <label class="flex items-center gap-2 font-bold text-xs pb-2 border-b border-slate-100 cursor-pointer">
                                <input type="checkbox" x-model="selectAll" @change="selected = selectAll ? [...options] : []">
                                Pilih Semua
                            </label>
                            @foreach($listFungsi ?? [] as $f)
                                <label class="flex items-center gap-2 text-xs py-1 cursor-pointer hover:bg-slate-50 px-1 rounded">
                                    <input type="checkbox" name="fungsi[]" value="{{ $f }}" x-model="selected">
                                    <span>{{ $f }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- 4. NAMA AR (Checkbox Popover Dropdown) -->
                    @php
                        $listArArr = collect($listAr ?? [])->pluck('nip')->toArray();
                        $currentAr = array_values((array)($nipAr ?? []));
                        $isAllAr = count($currentAr) === count($listArArr) && count($listArArr) > 0;
                    @endphp
                    <div x-data="{ 
                            open: false, 
                            selectAll: {{ $isAllAr ? 'true' : 'false' }}, 
                            selected: {{ json_encode($currentAr) }}, 
                            options: {{ json_encode($listArArr) }} 
                        }" 
                        x-init="$watch('selected', value => selectAll = (value.length === options.length && options.length > 0))"
                        class="relative">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Nama AR</label>
                        <button type="button" @click="open = !open" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2 text-left text-xs text-slate-800 flex justify-between items-center">
                            <span x-text="selected.length === options.length && options.length > 0 ? 'Semua AR Terpilih' : (selected.length ? selected.length + ' AR Dipilih' : 'Tidak Ada Dipilih')"></span>
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute z-50 mt-1 w-72 bg-white border border-slate-200 rounded-xl shadow-xl p-3 max-h-60 overflow-y-auto">
                            <label class="flex items-center gap-2 font-bold text-xs pb-2 border-b border-slate-100 cursor-pointer">
                                <input type="checkbox" x-model="selectAll" @change="selected = selectAll ? [...options] : []">
                                Pilih Semua
                            </label>
                            @foreach($listAr ?? [] as $ar)
                                <label class="flex items-center gap-2 text-xs py-1 cursor-pointer hover:bg-slate-50 px-1 rounded">
                                    <input type="checkbox" name="nip_ar[]" value="{{ $ar->nip }}" x-model="selected">
                                    <span>{{ $ar->nama }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- 5. NAMA JS (Checkbox Popover Dropdown) -->
                    @php
                        $listJsArr = collect($listJs ?? [])->pluck('nip')->toArray();
                        $currentJs = array_values((array)($nipJs ?? []));
                        $isAllJs = count($currentJs) === count($listJsArr) && count($listJsArr) > 0;
                    @endphp
                    <div x-data="{ 
                            open: false, 
                            selectAll: {{ $isAllJs ? 'true' : 'false' }}, 
                            selected: {{ json_encode($currentJs) }}, 
                            options: {{ json_encode($listJsArr) }} 
                        }" 
                        x-init="$watch('selected', value => selectAll = (value.length === options.length && options.length > 0))"
                        class="relative">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Nama JS</label>
                        <button type="button" @click="open = !open" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2 text-left text-xs text-slate-800 flex justify-between items-center">
                            <span x-text="selected.length === options.length && options.length > 0 ? 'Semua JS Terpilih' : (selected.length ? selected.length + ' JS Dipilih' : 'Tidak Ada Dipilih')"></span>
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute z-50 mt-1 w-72 bg-white border border-slate-200 rounded-xl shadow-xl p-3 max-h-60 overflow-y-auto">
                            <label class="flex items-center gap-2 font-bold text-xs pb-2 border-b border-slate-100 cursor-pointer">
                                <input type="checkbox" x-model="selectAll" @change="selected = selectAll ? [...options] : []">
                                Pilih Semua
                            </label>
                            @foreach($listJs ?? [] as $js)
                                <label class="flex items-center gap-2 text-xs py-1 cursor-pointer hover:bg-slate-50 px-1 rounded">
                                    <input type="checkbox" name="nip_js[]" value="{{ $js->nip }}" x-model="selected">
                                    <span>{{ $js->nama }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <!-- ==================== HASIL TABEL ==================== -->
    @if(isset($results) && $results)
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
                                <th class="p-3">AR / JS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($results as $item)
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 font-mono font-semibold text-slate-800">
                                        <div>{{ $item->npwp15 }}</div>
                                        @if(!empty($item->npwp16))
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
                                    <td class="p-3">
                                        <div class="text-[11px] font-semibold text-slate-700">AR: {{ $item->nama_ar ?? '-' }}</div>
                                        <div class="text-[10px] text-slate-500">JS: {{ $item->nama_js ?? '-' }}</div>
                                    </td>
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