<x-app-layout title="Pencarian Wajib Pajak (MFWP)">
    <div x-data="mfwpSearchComponent()" class="space-y-6">

        <!-- Page Title -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Pencarian Wajib Pajak (MFWP)</h1>
            <span class="text-xs bg-slate-200 text-slate-700 font-semibold px-2.5 py-1 rounded">
                Total Data: {{ number_format($wajibPajakList->total()) }}
            </span>
        </div>

        <!-- Filter Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <form action="{{ route('mfwp.index') }}" method="GET" id="searchForm" class="space-y-4">

                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">

                    <!-- 1. NPWP (9 / 15 / 16) -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">NPWP / NIK</label>
                        <input type="text" name="npwp" value="{{ $filters['npwp'] ?? '' }}"
                            placeholder="NPWP 9, 15, 16 digit / NIK"
                            class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-slate-500 focus:border-slate-500 border p-2">
                    </div>

                    <!-- 2. Nama WP -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Wajib Pajak</label>
                        <input type="text" name="nama" value="{{ $filters['nama'] ?? '' }}" placeholder="Nama WP"
                            class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-slate-500 focus:border-slate-500 border p-2">
                    </div>

                    <!-- 3. KLU Lookup -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">KLU</label>
                        <div class="flex space-x-1">
                            <input type="text" name="kd_klu" x-model="selectedKlu" readonly placeholder="Kode KLU"
                                class="w-full text-sm bg-gray-50 border-gray-300 rounded-md shadow-sm border p-2">
                            <button type="button" @click="openKluModal = true"
                                class="bg-slate-700 text-white text-xs px-3 py-2 rounded-md hover:bg-slate-800 transition">
                                Cari
                            </button>
                            <button type="button" @click="selectedKlu = ''" x-show="selectedKlu"
                                class="bg-red-100 text-red-600 text-xs px-2 py-2 rounded-md hover:bg-red-200">
                                X
                            </button>
                        </div>
                    </div>

                    <!-- 4. Kecamatan -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Kecamatan</label>
                        <select name="kecamatan" class="w-full text-sm border-gray-300 rounded-md shadow-sm border p-2">
                            <option value="">-- Semua Kecamatan --</option>
                            @foreach ($filterOptions['kecamatan'] as $kec)
                                <option value="{{ $kec }}"
                                    {{ ($filters['kecamatan'] ?? '') == $kec ? 'selected' : '' }}>
                                    {{ $kec }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 5. Kelurahan -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Kelurahan</label>
                        <select name="kelurahan" class="w-full text-sm border-gray-300 rounded-md shadow-sm border p-2">
                            <option value="">-- Semua Kelurahan --</option>
                            @foreach ($filterOptions['kelurahan'] as $kel)
                                <option value="{{ $kel }}"
                                    {{ ($filters['kelurahan'] ?? '') == $kel ? 'selected' : '' }}>
                                    {{ $kel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 6. Jenis WP -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Jenis WP</label>
                        <select name="jenis" class="w-full text-sm border-gray-300 rounded-md shadow-sm border p-2">
                            <option value="">-- Semua Jenis --</option>
                            @foreach ($filterOptions['jenis'] as $jns)
                                <option value="{{ $jns }}"
                                    {{ ($filters['jenis'] ?? '') == $jns ? 'selected' : '' }}>
                                    {{ $jns }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 7. Status WP -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Status WP</label>
                        <select name="status" class="w-full text-sm border-gray-300 rounded-md shadow-sm border p-2">
                            <option value="">-- Semua Status --</option>
                            @foreach ($filterOptions['status'] as $st)
                                <option value="{{ $st }}"
                                    {{ ($filters['status'] ?? '') == $st ? 'selected' : '' }}>
                                    {{ $st }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 8. Range Tanggal Daftar -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tgl Daftar (Awal -
                            Akhir)</label>
                        <div class="flex space-x-1">
                            <input type="date" name="tgl_daftar_awal"
                                value="{{ $filters['tgl_daftar_awal'] ?? '' }}"
                                class="w-1/2 text-xs border-gray-300 rounded-md border p-1.5">
                            <input type="date" name="tgl_daftar_akhir"
                                value="{{ $filters['tgl_daftar_akhir'] ?? '' }}"
                                class="w-1/2 text-xs border-gray-300 rounded-md border p-1.5">
                        </div>
                    </div>

                    <!-- 9. Account Representative (AR) -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Account Representative
                            (AR)</label>
                        <select name="nip_ar" x-model="selectedAr" @change="onArChange()"
                            class="w-full text-sm border-gray-300 rounded-md shadow-sm border p-2">
                            <option value="">-- Semua AR --</option>
                            @foreach ($filterOptions['arList'] as $ar)
                                <option value="{{ $ar->nip }}" data-seksi="{{ $ar->seksi }}">
                                    {{ $ar->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 10. Seksi Pengawasan -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Seksi</label>
                        <select name="id_seksi" x-model="selectedSeksi"
                            class="w-full text-sm border-gray-300 rounded-md shadow-sm border p-2 bg-gray-50">
                            <option value="">-- Semua Seksi Pengawasan --</option>
                            @foreach ($filterOptions['seksiList'] as $seksi)
                                <option value="{{ $seksi->id }}">
                                    {{ $seksi->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 11. JSPN -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">JSPN</label>
                        <select name="nip_js" class="w-full text-sm border-gray-300 rounded-md shadow-sm border p-2">
                            <option value="">-- Semua JSPN --</option>
                            @foreach ($filterOptions['jspnList'] as $js)
                                <option value="{{ $js->nip }}"
                                    {{ ($filters['nip_js'] ?? '') == $js->nip ? 'selected' : '' }}>
                                    {{ $js->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-end space-x-2 pt-2 border-t">
                    <a href="{{ route('mfwp.index') }}"
                        class="px-4 py-2 text-xs text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200">
                        Reset Filter
                    </a>
                    <button type="submit"
                        class="px-5 py-2 text-xs font-semibold text-white bg-slate-800 rounded-md hover:bg-slate-900 shadow">
                        Cari Data
                    </button>
                    <button type="button" @click="exportCsv()"
                        class="px-5 py-2 text-xs font-semibold text-slate-800 bg-yellow-400 rounded-md hover:bg-yellow-500 shadow">
                        Export CSV
                    </button>
                </div>

            </form>
        </div>

        <!-- Table Result Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-900 text-white uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="p-3">NPWP15 / NPWP16</th>
                            <th class="p-3">Nama Wajib Pajak</th>
                            <th class="p-3">KLU</th>
                            <th class="p-3">Kecamatan / Kelurahan</th>
                            <th class="p-3">Jenis / Status</th>
                            <th class="p-3">AR / Seksi</th>
                            <th class="p-3">JSPN</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-gray-700">
                        @forelse($wajibPajakList as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 font-mono">
                                    <div class="font-semibold text-slate-800">{{ $row->npwp15 }}</div>
                                    <div class="text-gray-400 text-[10px]">{{ $row->npwp16 ?? '-' }}</div>
                                </td>
                                <td class="p-3">
                                    <div class="font-bold text-gray-900">{{ $row->nama }}</div>
                                    <div class="text-gray-500 text-[11px] truncate max-w-xs">{{ $row->alamat }}
                                    </div>
                                </td>
                                <td class="p-3 font-mono">{{ $row->klu }}</td>
                                <td class="p-3">
                                    <div>{{ $row->kecamatan }}</div>
                                    <div class="text-gray-400 text-[10px]">{{ $row->kelurahan }}</div>
                                </td>
                                <td class="p-3">
                                    <span
                                        class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-800">
                                        {{ $row->jenis }}
                                    </span>
                                    <span class="ml-1 text-[10px] text-gray-500">({{ $row->status }})</span>
                                </td>
                                <td class="p-3">
                                    <div class="font-medium text-slate-900">{{ $row->nama_ar ?? '-' }}</div>
                                    <div class="text-gray-400 text-[10px]">{{ $row->nama_seksi ?? '-' }}</div>
                                </td>
                                <td class="p-3">{{ $row->nama_jspn ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-gray-400">
                                    Tidak ada data yang ditemukan sesuai kriteria pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t bg-gray-50">
                {{ $wajibPajakList->links() }}
            </div>
        </div>

        <!-- Modal Lookup KLU -->
        <div x-show="openKluModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            x-cloak>
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-5 space-y-4">
                <div class="flex justify-between items-center border-b pb-2">
                    <h3 class="font-bold text-sm text-gray-800">Lookup Master KLU</h3>
                    <button @click="openKluModal = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>

                <input type="text" x-model="kluQuery" @input.debounce.300ms="fetchKlu()"
                    placeholder="Cari Kode atau Nama KLU..."
                    class="w-full text-sm border-gray-300 rounded-md border p-2">

                <div class="max-h-60 overflow-y-auto divide-y text-xs">
                    <template x-for="item in kluResults" :key="item.kd_klu">
                        <div @click="selectKlu(item.kd_klu)"
                            class="p-2 hover:bg-slate-100 cursor-pointer flex justify-between">
                            <span class="font-bold font-mono" x-text="item.kd_klu"></span>
                            <span class="text-gray-600 truncate max-w-[220px]" x-text="item.nm_klu"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>

    <script>
        function mfwpSearchComponent() {
            return {
                selectedAr: '{{ $filters['nip_ar'] ?? '' }}',
                selectedSeksi: '{{ $filters['id_seksi'] ?? '' }}',
                selectedKlu: '{{ $filters['kd_klu'] ?? '' }}',
                openKluModal: false,
                kluQuery: '',
                kluResults: [],

                onArChange() {
                    if (!this.selectedAr) return;
                    const arSelect = document.querySelector('select[name="nip_ar"]');
                    const selectedOption = arSelect.options[arSelect.selectedIndex];
                    const seksiId = selectedOption.getAttribute('data-seksi');
                    if (seksiId) {
                        this.selectedSeksi = seksiId;
                    }
                },

                async fetchKlu() {
                    if (this.kluQuery.length < 2) return;
                    const res = await fetch(`{{ route('mfwp.search-klu') }}?q=${this.kluQuery}`);
                    this.kluResults = await res.json();
                },

                selectKlu(kdKlu) {
                    this.selectedKlu = kdKlu;
                    this.openKluModal = false;
                },

                exportCsv() {
                    const form = document.getElementById('searchForm');
                    const formData = new FormData(form);
                    const params = new URLSearchParams(formData).toString();
                    window.location.href = `{{ route('mfwp.export') }}?${params}`;
                }
            }
        }
    </script>
</x-app-layout>
