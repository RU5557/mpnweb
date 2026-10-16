<x-app-layout title="Cari Wajib Pajak">
    <x-slot name="statusText">
        {{ $wajibPajakList->firstItem() ?? 0 }}-{{ $wajibPajakList->lastItem() ?? 0 }} dari
        {{ number_format($wajibPajakList->total()) }} data
    </x-slot>

    <div x-data="mfwpApp()" class="w-full flex h-full overflow-hidden gap-2 p-2">

        {{-- Filter --}}
        <div x-show="showFilter" x-transition class="w-[280px] shrink-0 flex flex-col gap-2 overflow-auto">
            <div class="card p-3">
                <div class="flex items-center justify-between mb-3">
                    <div class="font-semibold text-[12px]">Filter Pencarian</div>
                    <button @click="showFilter=false"
                        class="w-5 h-5 rounded bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-[10px]">◀</button>
                </div>

                <form id="searchForm" action="{{ route('mfwp.index') }}" method="GET" class="space-y-3">
                    <div class="space-y-2.5">
                        <div>
                            <label class="flex items-center gap-1.5 text-[11px] font-medium">
                                <input type="checkbox" x-model="c.npwp" class="w-3 h-3 rounded"> NPWP
                            </label>
                            <input type="text" name="npwp" :disabled="!c.npwp"
                                value="{{ $filters['npwp'] ?? '' }}" placeholder="Masukkan NPWP" class="field mt-1">
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-[11px] font-medium">
                                <input type="checkbox" x-model="c.nama" class="w-3 h-3 rounded"> Nama
                            </label>
                            <input type="text" name="nama" :disabled="!c.nama"
                                value="{{ $filters['nama'] ?? '' }}" placeholder="Masukkan nama" class="field mt-1">
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-[11px] font-medium">
                                <input type="checkbox" x-model="c.klu" class="w-3 h-3 rounded"> Klasifikasi Usaha
                            </label>
                            <div class="flex gap-1 mt-1">
                                <input type="text" name="kd_klu" x-model="klu" :disabled="!c.klu" readonly
                                    placeholder="Pilih KLU" class="field flex-1">
                                <button type="button" @click="openKluModal()" class="btn-secondary !px-2">🔍</button>
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-[11px] font-medium">
                                <input type="checkbox" x-model="c.alamat" class="w-3 h-3 rounded"> Kelurahan
                            </label>
                            <input type="text" name="kelurahan" :disabled="!c.alamat"
                                value="{{ $filters['kelurahan'] ?? '' }}" placeholder="Nama kelurahan"
                                class="field mt-1">
                            <input type="hidden" name="kecamatan" value="{{ $filters['kecamatan'] ?? '' }}">
                        </div>

                        <div class="grid grid-cols-2 gap-1.5">
                            <div>
                                <label class="flex items-center gap-1 text-[10px] font-medium">
                                    <input type="checkbox" x-model="c.jenis" class="w-3 h-3 rounded"> Jenis WP
                                </label>
                                <select name="jenis" :disabled="!c.jenis" class="field mt-1">
                                    <option value="">Semua</option>
                                    @foreach ($filterOptions['jenis'] as $j)
                                        <option value="{{ $j }}"
                                            {{ ($filters['jenis'] ?? '') == $j ? 'selected' : '' }}>{{ $j }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="flex items-center gap-1 text-[10px] font-medium">
                                    <input type="checkbox" x-model="c.status" class="w-3 h-3 rounded"> Status
                                </label>
                                <select name="status" :disabled="!c.status" class="field mt-1">
                                    <option value="">Semua</option>
                                    @foreach ($filterOptions['status'] as $st)
                                        <option value="{{ $st }}"
                                            {{ ($filters['status'] ?? '') == $st ? 'selected' : '' }}>{{ $st }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-[11px] font-medium">
                                <input type="checkbox" x-model="c.tgl" class="w-3 h-3 rounded"> Tanggal Daftar
                            </label>
                            <div class="grid grid-cols-2 gap-1 mt-1">
                                <input type="date" name="tgl_daftar_awal" :disabled="!c.tgl"
                                    value="{{ $filters['tgl_daftar_awal'] ?? '' }}" class="field">
                                <input type="date" name="tgl_daftar_akhir" :disabled="!c.tgl"
                                    value="{{ $filters['tgl_daftar_akhir'] ?? '' }}" class="field">
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-[11px] font-medium">
                                <input type="checkbox" x-model="c.pj" class="w-3 h-3 rounded"> Account Representative
                            </label>
                            <select name="nip_ar" x-model="ar" :disabled="!c.pj" @change="onArChange()"
                                class="field mt-1">
                                <option value="">Semua AR</option>
                                @foreach ($filterOptions['arList'] as $ar)
                                    <option value="{{ $ar->nip }}" data-seksi="{{ $ar->seksi }}">
                                        {{ $ar->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-[11px] font-medium">
                                <input type="checkbox" x-model="c.seksi" class="w-3 h-3 rounded"> Seksi Pengawasan
                            </label>
                            <select name="id_seksi" x-model="seksi" :disabled="!c.seksi" class="field mt-1">
                                <option value="">Semua Seksi</option>
                                @foreach ($filterOptions['seksiList'] as $sk)
                                    <option value="{{ $sk->id }}"
                                        {{ ($filters['id_seksi'] ?? '') == $sk->id ? 'selected' : '' }}>{{ $sk->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-[11px] font-medium">
                                <input type="checkbox" x-model="c.jspn" class="w-3 h-3 rounded"> Juru Sita
                            </label>
                            <select name="nip_js" x-model="jspn" :disabled="!c.jspn" class="field mt-1">
                                <option value="">Semua Juru Sita</option>
                                @foreach ($filterOptions['jspnList'] as $js)
                                    <option value="{{ $js->nip }}"
                                        {{ ($filters['nip_js'] ?? '') == $js->nip ? 'selected' : '' }}>{{ $js->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-1.5 pt-2">
                        <button type="submit" class="btn-primary flex-1 justify-center">Cari</button>
                        <button type="button" @click="exportCsv()"
                            class="btn-secondary flex-1 justify-center">Export</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="flex-1 flex flex-col card overflow-hidden">
            <div class="px-3 py-2 border-b border-slate-100 flex items-center justify-between gap-2 shrink-0">
                <div class="flex items-center gap-2">
                    <button x-show="!showFilter" @click="showFilter=true" class="btn-secondary !px-2 !py-1">▶
                        Filter</button>
                    <button x-show="showFilter" @click="showFilter=false"
                        class="btn-secondary !px-2 !py-1 hidden md:flex">◀ Sembunyikan</button>
                    <div class="text-[11px] font-semibold">{{ number_format($wajibPajakList->total()) }} data
                        ditemukan</div>
                </div>
                <div class="text-[10px] text-slate-500">Hal
                    {{ $wajibPajakList->currentPage() }}/{{ $wajibPajakList->lastPage() }}</div>
            </div>

            <div class="flex-1 overflow-auto">
                <table class="w-full text-[11px] border-collapse" style="min-width:2300px">
                    <thead class="sticky top-0 bg-slate-50 border-b border-slate-200 z-20 text-[10px] text-slate-600">
                        <tr>
                            <th class="sticky left-0 bg-slate-50 z-30 px-2 py-1.5 w-8 border-r text-left font-medium">
                                No</th>
                            <th
                                class="sticky left-8 bg-slate-50 z-30 px-2 py-1.5 w-[110px] border-r text-left font-medium">
                                NPWP</th>
                            <th class="px-2 py-1.5 w-[115px] border-r text-left font-medium">NPWP 16</th>
                            <th class="px-2 py-1.5 w-[200px] border-r text-left font-medium">Nama</th>
                            <th class="px-2 py-1.5 w-[180px] border-r text-left font-medium">Alamat</th>
                            <th class="px-2 py-1.5 w-[80px] border-r text-left font-medium">Kelurahan</th>
                            <th class="px-2 py-1.5 w-[90px] border-r text-left font-medium">Kecamatan</th>
                            <th class="px-2 py-1.5 w-[80px] border-r text-left font-medium">Kota</th>
                            <th class="px-2 py-1.5 w-[80px] border-r text-left font-medium">Provinsi</th>
                            <th class="px-2 py-1.5 w-[70px] border-r text-left font-medium">Jenis</th>
                            <th class="px-2 py-1.5 w-[80px] border-r text-left font-medium">Bentuk Usaha</th>
                            <th class="px-2 py-1.5 w-[60px] border-r text-left font-medium">Status</th>
                            <th class="px-2 py-1.5 w-[50px] border-r text-left font-medium">KLU</th>
                            <th class="px-2 py-1.5 w-[85px] border-r text-left font-medium">Tgl Daftar</th>
                            <th class="px-2 py-1.5 w-[85px] border-r text-left font-medium">Tgl PKP</th>
                            <th class="px-2 py-1.5 w-[85px] border-r text-left font-medium">Tgl Cabut</th>
                            <th class="px-2 py-1.5 w-[110px] border-r text-left font-medium">NIK</th>
                            <th class="px-2 py-1.5 w-[90px] border-r text-left font-medium">Telepon</th>
                            <th class="px-2 py-1.5 w-[110px] border-r text-left font-medium">AR</th>
                            <th class="px-2 py-1.5 w-[110px] border-r text-left font-medium">Seksi</th>
                            <th class="px-2 py-1.5 w-[85px] border-r text-left font-medium">Eks AR</th>
                            <th class="px-2 py-1.5 w-[110px] text-left font-medium">Juru Sita</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($wajibPajakList as $idx => $wp)
                            <tr class="hover:bg-slate-50">
                                <td class="sticky left-0 bg-white px-2 py-1 border-r text-slate-500"
                                    title="{{ $wajibPajakList->firstItem() + $idx }}">
                                    {{ $wajibPajakList->firstItem() + $idx }}</td>
                                <td class="sticky left-8 bg-white px-2 py-1 border-r font-mono text-[10px] font-medium"
                                    title="{{ $wp->npwp15 }}">{{ $wp->npwp15 }}</td>
                                <td class="px-2 py-1 border-r font-mono" title="{{ $wp->npwp16 }}">
                                    {{ $wp->npwp16 }}</td>
                                <td class="px-2 py-1 border-r" title="{{ $wp->nama }}">
                                    {{ mb_strimwidth($wp->nama ?? '', 0, 40, '...') }}</td>
                                <td class="px-2 py-1 border-r" title="{{ $wp->alamat }}">
                                    {{ mb_strimwidth($wp->alamat ?? '', 0, 40, '...') }}</td>
                                <td class="px-2 py-1 border-r" title="{{ $wp->kelurahan }}">
                                    {{ mb_strimwidth($wp->kelurahan ?? '', 0, 18, '...') }}</td>
                                <td class="px-2 py-1 border-r" title="{{ $wp->kecamatan }}">{{ $wp->kecamatan }}
                                </td>
                                <td class="px-2 py-1 border-r" title="{{ $wp->kota }}">
                                    {{ mb_strimwidth($wp->kota ?? '', 0, 18, '...') }}</td>
                                <td class="px-2 py-1 border-r" title="{{ $wp->propinsi }}">
                                    {{ mb_strimwidth($wp->propinsi ?? '', 0, 18, '...') }}</td>
                                <td class="px-2 py-1 border-r">{{ $wp->jenis }}</td>
                                <td class="px-2 py-1 border-r" title="{{ $wp->bentuk_hukum }}">
                                    {{ mb_strimwidth($wp->bentuk_hukum ?? '', 0, 18, '...') }}</td>
                                <td class="px-2 py-1 border-r">{{ $wp->status }}</td>
                                <td class="px-2 py-1 border-r"><span
                                        class="bg-slate-100 px-1.5 py-0.5 rounded text-[10px]">{{ $wp->klu }}</span>
                                </td>
                                <td class="px-2 py-1 border-r">
                                    {{ $wp->tanggal_daftar ? \Carbon\Carbon::parse($wp->tanggal_daftar)->format('d/m/Y') : '' }}
                                </td>
                                <td class="px-2 py-1 border-r">
                                    {{ $wp->tanggal_pkp ? \Carbon\Carbon::parse($wp->tanggal_pkp)->format('d/m/Y') : '' }}
                                </td>
                                <td class="px-2 py-1 border-r">
                                    {{ $wp->tanggal_pkp_cabut ? \Carbon\Carbon::parse($wp->tanggal_pkp_cabut)->format('d/m/Y') : '' }}
                                </td>
                                <td class="px-2 py-1 border-r font-mono text-[10px]" title="{{ $wp->nik }}">
                                    {{ mb_strimwidth($wp->nik ?? '', 0, 20, '...') }}</td>
                                <td class="px-2 py-1 border-r">{{ mb_strimwidth($wp->telp ?? '', 0, 18, '...') }}</td>
                                <td class="px-2 py-1 border-r" title="{{ $wp->nama_ar }}">
                                    {{ mb_strimwidth($wp->nama_ar ?? ($wp->nip_ar ?? ''), 0, 22, '...') }}</td>
                                <td class="px-2 py-1 border-r" title="{{ $wp->nama_seksi }}">
                                    {{ mb_strimwidth($wp->nama_seksi ?? '', 0, 22, '...') }}</td>
                                <td class="px-2 py-1 border-r font-mono text-[10px]" title="{{ $wp->nip_eks }}">
                                    {{ $wp->nip_eks }}</td>
                                <td class="px-2 py-1" title="{{ $wp->nama_jspn }}">
                                    {{ mb_strimwidth($wp->nama_jspn ?? ($wp->nip_js ?? ''), 0, 22, '...') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="22" class="px-3 py-12 text-center text-slate-400">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div
                class="border-t border-slate-100 px-2.5 py-1.5 flex items-center justify-between bg-slate-50/50 shrink-0">
                <div class="text-[10px] text-slate-500">
                    {{ $wajibPajakList->firstItem() ?? 0 }}-{{ $wajibPajakList->lastItem() ?? 0 }} /
                    {{ number_format($wajibPajakList->total()) }}</div>
                <div class="scale-[0.85] origin-right">{{ $wajibPajakList->onEachSide(1)->links() }}</div>
            </div>
        </div>

        {{-- KLU Modal --}}
        <div x-show="openKlu" x-transition
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/20 backdrop-blur-sm p-4" x-cloak>
            <div class="bg-white rounded-lg shadow-xl w-[400px] border border-slate-200 overflow-hidden">
                <div class="px-3 py-2 border-b border-slate-100 flex justify-between items-center">
                    <div class="font-semibold text-[11px]">Pilih Klasifikasi Usaha</div>
                    <button @click="openKlu=false"
                        class="w-6 h-6 rounded bg-slate-100 hover:bg-slate-200 flex items-center justify-center">✕</button>
                </div>
                <div class="p-3 space-y-2">
                    <input type="text" x-model="q" @input.debounce.300ms="fetchKlu()"
                        placeholder="Cari kode atau nama usaha..." class="field" autofocus>
                    <div class="border border-slate-200 rounded-md h-[220px] overflow-auto divide-y divide-slate-100">
                        <div x-show="loading" class="px-2.5 py-8 text-center text-slate-400 text-[11px]">Memuat
                            data...</div>
                        <div x-show="!loading && results.length===0"
                            class="px-2.5 py-8 text-center text-slate-400 text-[11px]">Tidak ada data. Coba kata kunci
                            lain.</div>
                        <template x-for="it in results" :key="it.kd_klu">
                            <div @click="selectKlu(it.kd_klu)"
                                class="px-2.5 py-1.5 hover:bg-slate-900 hover:text-white cursor-pointer flex justify-between text-[11px]">
                                <span class="font-mono font-bold" x-text="it.kd_klu"></span>
                                <span class="truncate max-w-[220px]" x-text="it.nm_klu"></span>
                            </div>
                        </template>
                    </div>
                    <div class="text-[10px] text-slate-400" x-text="results.length + ' data ditemukan'"></div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function mfwpApp() {
            return {
                showFilter: true,
                c: {
                    npwp: !!'{{ $filters['npwp'] ?? '' }}',
                    nama: !!'{{ $filters['nama'] ?? '' }}',
                    klu: !!'{{ $filters['kd_klu'] ?? '' }}',
                    alamat: !!'{{ $filters['kelurahan'] ?? '' }}',
                    jenis: !!'{{ $filters['jenis'] ?? '' }}',
                    status: !!'{{ $filters['status'] ?? '' }}',
                    tgl: !!'{{ $filters['tgl_daftar_awal'] ?? '' }}',
                    pj: !!'{{ $filters['nip_ar'] ?? '' }}',
                    seksi: !!'{{ $filters['id_seksi'] ?? '' }}',
                    jspn: !!'{{ $filters['nip_js'] ?? '' }}'
                },
                ar: '{{ $filters['nip_ar'] ?? '' }}',
                seksi: '{{ $filters['id_seksi'] ?? '' }}',
                klu: '{{ $filters['kd_klu'] ?? '' }}',
                jspn: '{{ $filters['nip_js'] ?? '' }}',
                openKlu: false,
                q: '',
                results: [],
                loading: false,
                init() {
                    const saved = localStorage.getItem('mpn_filter_open');
                    if (saved !== null) this.showFilter = saved === 'true';
                    this.$watch('showFilter', v => localStorage.setItem('mpn_filter_open', v));
                    // preload KLU untuk modal
                    this.fetchKlu();
                },
                onArChange() {
                    const sel = document.querySelector('select[name="nip_ar"]');
                    const opt = sel?.options[sel.selectedIndex];
                    const s = opt?.getAttribute('data-seksi');
                    if (s) {
                        this.seksi = s;
                        this.c.seksi = true;
                    }
                },
                openKluModal() {
                    this.openKlu = true;
                    if (this.results.length === 0) this.fetchKlu();
                },
                async fetchKlu() {
                    this.loading = true;
                    try {
                        const query = this.q ? `?q=${encodeURIComponent(this.q)}` : '';
                        const r = await fetch(`{{ route('mfwp.search-klu') }}${query}`);
                        if (!r.ok) throw new Error('Network error');
                        this.results = await r.json();
                    } catch (e) {
                        console.error(e);
                        this.results = [];
                    } finally {
                        this.loading = false;
                    }
                },
                selectKlu(k) {
                    this.klu = k;
                    this.c.klu = true;
                    this.openKlu = false;
                },
                exportCsv() {
                    const f = document.getElementById('searchForm');
                    const p = new URLSearchParams(new FormData(f)).toString();
                    window.location.href = `{{ route('mfwp.export') }}?${p}`;
                }
            }
        }
    </script>
</x-app-layout>
