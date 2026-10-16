<x-app-layout title="MPN Web">
    <x-slot name="statusText">Dashboard • {{ number_format($totalWp) }} Wajib Pajak</x-slot>

    <div class="w-full p-4 overflow-auto">
        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-[16px] font-bold">Dashboard</h1>
                    <p class="text-[11px] text-slate-500">Ringkasan data Wajib Pajak</p>
                </div>
                <a href="{{ route('mfwp.index') }}" class="btn-primary">Cari WP</a>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div class="card p-4">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Total Wajib Pajak</div>
                    <div class="text-[22px] font-bold mt-1">{{ number_format($totalWp) }}</div>
                </div>
                <div class="card p-4">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Total Pegawai</div>
                    <div class="text-[22px] font-bold mt-1">{{ number_format($totalPegawai) }}</div>
                </div>
                <div class="card p-4">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Kantor</div>
                    <div class="text-[13px] font-semibold mt-1">KPP Pratama Pondok Gede</div>
                    <div class="text-[11px] text-slate-500">Bekasi - Jawa Barat</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
