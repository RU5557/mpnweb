@extends('layouts.app')

@section('content')
<!-- Header Halaman -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Dashboard Ringkasan</h1>
    <p class="text-slate-500 text-sm">Overview penerimaan, capaian target, dan performa PKM</p>
</div>

<!-- BARIS 1: CARD RINGKASAN UTAMA (3 KOLOM) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    
    <!-- Penerimaan Saat Ini -->
    <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-blue-600">
        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Penerimaan Saat Ini</span>
        <div class="text-2xl font-extrabold text-blue-600 my-2">Rp 738.830.000.000</div>
        <div class="flex items-center gap-4 text-xs font-semibold text-slate-600 pt-3 border-t border-slate-100">
            <span>Bulan Lalu: <span class="text-emerald-600 font-bold"><i class="fa-solid fa-arrow-trend-up text-[10px]"></i> 3,14%</span></span>
            <span>Tahun Lalu: <span class="text-emerald-600 font-bold"><i class="fa-solid fa-arrow-trend-up text-[10px]"></i> 14,48%</span></span>
        </div>
    </div>

    <!-- Penerimaan Bulan Lalu -->
    <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-sky-400">
        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Penerimaan Bulan Lalu</span>
        <div class="text-2xl font-extrabold text-sky-400 my-2">Rp 716.320.000.000</div>
    </div>

    <!-- Penerimaan Tahun Lalu -->
    <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-amber-400">
        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Penerimaan Tahun Lalu</span>
        <div class="text-2xl font-extrabold text-amber-400 my-2">Rp 645.400.000.000</div>
    </div>

</div>

<!-- BARIS 2 & 3: GRID PERFORMANCE CARD (3 KOLOM x 2 BARIS) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-5">

    <!-- Card 1: PPM -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-base">PPM</h3>
                <p class="text-xs text-slate-400">Capaian Target</p>
            </div>
            <span class="bg-blue-600 text-white font-bold text-xs px-2.5 py-1 rounded-md shadow-sm">78,5%</span>
        </div>
        <div class="space-y-2 text-xs text-slate-600 border-t pt-3 border-slate-100">
            <div class="flex justify-between"><span>Target:</span> <span class="font-bold text-slate-800">Rp 1.000.000.000.000</span></div>
            <div class="flex justify-between"><span>Realisasi:</span> <span class="font-bold text-slate-800">Rp 785.000.000.000</span></div>
            <div class="flex justify-between text-emerald-600 pt-1 font-semibold"><span>Pertumbuhan:</span> <span>&uarr; 12,4%</span></div>
        </div>
    </div>

    <!-- Card 2: PKM -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-base">PKM</h3>
                <p class="text-xs text-slate-400">Capaian Target</p>
            </div>
            <span class="bg-sky-400 text-white font-bold text-xs px-2.5 py-1 rounded-md shadow-sm">65,2%</span>
        </div>
        <div class="space-y-2 text-xs text-slate-600 border-t pt-3 border-slate-100">
            <div class="flex justify-between"><span>Target:</span> <span class="font-bold text-slate-800">Rp 500.000.000.000</span></div>
            <div class="flex justify-between"><span>Realisasi:</span> <span class="font-bold text-slate-800">Rp 326.000.000.000</span></div>
            <div class="flex justify-between text-emerald-600 pt-1 font-semibold"><span>Pertumbuhan:</span> <span>&uarr; 8,7%</span></div>
        </div>
    </div>

    <!-- Card 3: PBP -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-base">PBP</h3>
                <p class="text-xs text-slate-400">Capaian Target</p>
            </div>
            <span class="bg-amber-400 text-white font-bold text-xs px-2.5 py-1 rounded-md shadow-sm">82,0%</span>
        </div>
        <div class="space-y-2 text-xs text-slate-600 border-t pt-3 border-slate-100">
            <div class="flex justify-between"><span>Target:</span> <span class="font-bold text-slate-800">Rp 250.000.000.000</span></div>
            <div class="flex justify-between"><span>Realisasi:</span> <span class="font-bold text-slate-800">Rp 205.000.000.000</span></div>
            <div class="flex justify-between text-rose-500 pt-1 font-semibold"><span>Sisa Target:</span> <span>Rp 45.000.000.000</span></div>
        </div>
    </div>

    <!-- Card 4: PKM Pengawasan -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-base">PKM Pengawasan</h3>
                <p class="text-xs text-slate-400">Capaian Target</p>
            </div>
            <span class="bg-emerald-600 text-white font-bold text-xs px-2.5 py-1 rounded-md shadow-sm">73,4%</span>
        </div>
        <div class="space-y-2 text-xs text-slate-600 border-t pt-3 border-slate-100">
            <div class="flex justify-between"><span>Target:</span> <span class="font-bold text-slate-800">Rp 120.000.000.000</span></div>
            <div class="flex justify-between"><span>Realisasi:</span> <span class="font-bold text-slate-800">Rp 88.173.474.588</span></div>
            <div class="flex justify-between text-emerald-600 pt-1 font-semibold"><span>Pertumbuhan:</span> <span>&uarr; 15,3%</span></div>
        </div>
    </div>

    <!-- Card 5: PKM Pemeriksaan -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-base">PKM Pemeriksaan</h3>
                <p class="text-xs text-slate-400">Capaian Target</p>
            </div>
            <span class="bg-slate-600 text-white font-bold text-xs px-2.5 py-1 rounded-md shadow-sm">58,1%</span>
        </div>
        <div class="space-y-2 text-xs text-slate-600 border-t pt-3 border-slate-100">
            <div class="flex justify-between"><span>Target:</span> <span class="font-bold text-slate-800">Rp 90.000.000.000</span></div>
            <div class="flex justify-between"><span>Realisasi:</span> <span class="font-bold text-slate-800">Rp 52.290.000.000</span></div>
            <div class="flex justify-between text-emerald-600 pt-1 font-semibold"><span>Pertumbuhan:</span> <span>&uarr; 5,8%</span></div>
        </div>
    </div>

    <!-- Card 6: PKM Penagihan -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-base">PKM Penagihan</h3>
                <p class="text-xs text-slate-400">Capaian Target</p>
            </div>
            <span class="bg-slate-900 text-white font-bold text-xs px-2.5 py-1 rounded-md shadow-sm">61,0%</span>
        </div>
        <div class="space-y-2 text-xs text-slate-600 border-t pt-3 border-slate-100">
            <div class="flex justify-between"><span>Target:</span> <span class="font-bold text-slate-800">Rp 60.000.000.000</span></div>
            <div class="flex justify-between"><span>Realisasi:</span> <span class="font-bold text-slate-800">Rp 36.600.000.000</span></div>
            <div class="flex justify-between text-rose-500 pt-1 font-semibold"><span>Pertumbuhan:</span> <span>&darr; 2,1%</span></div>
        </div>
    </div>

</div>
@endsection