<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Kelola Target & Info</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 font-sans min-h-screen text-slate-800 antialiased">

    <header class="bg-slate-900 text-white px-8 py-4 flex items-center justify-between shadow-md">
        <div class="flex items-center gap-3">
            <div class="bg-blue-600 p-2 rounded-lg text-white flex items-center justify-center w-8 h-8 shadow-sm">
                <i class="fa-solid fa-gears text-sm"></i>
            </div>
            <span class="font-bold text-lg tracking-wide">Panel Pengelola Data MPNWEB</span>
        </div>

        <!-- Tombol Aksi Kanan Topbar -->
        <div class="flex items-center gap-3">
            <a href="{{ route('penerimaan.dashboard') }}" class="flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm px-4 py-2 rounded-lg transition border border-slate-700 font-medium">
                <i class="fa-solid fa-arrow-left text-xs"></i> Kembali ke Dashboard
            </a>

            <!-- Form Logout Admin -->
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg transition font-medium shadow-sm">
                    <i class="fa-solid fa-right-from-bracket text-xs"></i> Logout
                </button>
            </form>
        </div>
    </header>

    <main class="max-w-6xl mx-auto p-8 space-y-8">

        {{-- Flash Success Message --}}
        @if(session('success'))
            <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded-lg shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-xl"></i>
                    <span class="font-medium text-sm">{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        {{-- Validation Error Alert --}}
        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-4 rounded-lg shadow-sm space-y-1">
                <div class="flex items-center gap-2 font-bold text-sm">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Terdapat kesalahan pada inputan Anda:</span>
                </div>
                <ul class="list-disc list-inside text-xs pl-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- FORM 1: UPDATE ROLLING TEXT HARIAN -->
        <section class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-bullhorn text-blue-600 text-lg"></i>
                    <h2 class="font-bold text-slate-800 text-base">Update Rolling Text & Performa Harian</h2>
                </div>
                <span class="bg-blue-100 text-blue-800 text-xs px-2.5 py-1 rounded-full font-semibold">Update Harian</span>
            </div>

            <form action="{{ route('admin.rolling-text.update') }}" method="POST" class="p-6 space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Tanggal Info</label>
                        <input type="date" name="tanggal" value="{{ old('tanggal', isset($rollingText) ? $rollingText->tanggal : date('Y-m-d')) }}" required
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500 font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Nilai NKO (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="nko" value="{{ old('nko', isset($rollingText) ? $rollingText->nko : 0) }}" placeholder="Contoh: 95.40" required
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500 font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Ranking Nasional</label>
                        <input type="number" min="1" name="ranking_nasional" value="{{ old('ranking_nasional', isset($rollingText) ? $rollingText->ranking_nasional : 0) }}" placeholder="Contoh: 12" required
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500 font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Ranking Kanwil</label>
                        <input type="number" min="1" name="ranking_kanwil" value="{{ old('ranking_kanwil', isset($rollingText) ? $rollingText->ranking_kanwil : 0) }}" placeholder="Contoh: 2" required
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500 font-medium">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-6 py-2.5 rounded-lg shadow-sm transition flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Info Harian
                    </button>
                </div>
            </form>
        </section>

        <!-- FORM 2: UPDATE TARGET TAHUNAN (DENGAN MASKING RUPIAH OTOMATIS) -->
        <section class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-bullseye text-emerald-600 text-lg"></i>
                    <h2 class="font-bold text-slate-800 text-base">Update Target Tahunan (Tahun {{ $tahunSekarang }})</h2>
                </div>
                <span class="bg-emerald-100 text-emerald-800 text-xs px-2.5 py-1 rounded-full font-semibold">Statik Tahunan</span>
            </div>

            <form action="{{ route('admin.target.update') }}" method="POST" class="p-6 space-y-6">
                @csrf
                <input type="hidden" name="tahun" value="{{ $tahunSekarang }}">

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Target Kantor (Global)</label>
                        <input type="text" name="target_kantor" value="{{ old('target_kantor', isset($target->target_kantor) ? number_format($target->target_kantor, 0, ',', '.') : 0) }}" required
                            class="rupiah-input w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500 font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Target PPM</label>
                        <input type="text" name="target_ppm" value="{{ old('target_ppm', isset($target->target_ppm) ? number_format($target->target_ppm, 0, ',', '.') : 0) }}" required
                            class="rupiah-input w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500 font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Target PKM (Total)</label>
                        <input type="text" name="target_pkm" value="{{ old('target_pkm', isset($target->target_pkm) ? number_format($target->target_pkm, 0, ',', '.') : 0) }}" required
                            class="rupiah-input w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500 font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Target PBP</label>
                        <input type="text" name="target_pbp" value="{{ old('target_pbp', isset($target->target_pbp) ? number_format($target->target_pbp, 0, ',', '.') : 0) }}" required
                            class="rupiah-input w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500 font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Target PKM Pengawasan</label>
                        <input type="text" name="target_pkm_pengawasan" value="{{ old('target_pkm_pengawasan', isset($target->target_pkm_pengawasan) ? number_format($target->target_pkm_pengawasan, 0, ',', '.') : 0) }}" required
                            class="rupiah-input w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500 font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Target PKM Pemeriksaan</label>
                        <input type="text" name="target_pkm_pemeriksaan" value="{{ old('target_pkm_pemeriksaan', isset($target->target_pkm_pemeriksaan) ? number_format($target->target_pkm_pemeriksaan, 0, ',', '.') : 0) }}" required
                            class="rupiah-input w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500 font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Target PKM Penagihan</label>
                        <input type="text" name="target_pkm_penagihan" value="{{ old('target_pkm_penagihan', isset($target->target_pkm_penagihan) ? number_format($target->target_pkm_penagihan, 0, ',', '.') : 0) }}" required
                            class="rupiah-input w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500 font-medium">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm px-6 py-2.5 rounded-lg shadow-sm transition flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Target Tahunan
                    </button>
                </div>
            </form>
        </section>

    </main>

    <!-- SCRIPT FORMATTING MASKING RUPIAH -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const rupiahInputs = document.querySelectorAll('.rupiah-input');

            rupiahInputs.forEach(function (input) {
                input.addEventListener('input', function (e) {
                    let value = this.value.replace(/[^,\d]/g, '').toString();
                    let split = value.split(',');
                    let sisa = split[0].length % 3;
                    let rupiah = split[0].substr(0, sisa);
                    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                    if (ribuan) {
                        let separator = sisa ? '.' : '';
                        rupiah += separator + ribuan.join('.');
                    }

                    rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
                    this.value = rupiah;
                });
            });
        });
    </script>

</body>
</html>