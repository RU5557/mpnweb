<x-app-layout title="Dashboard Ringkasan">
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard Ringkasan</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <div class="text-sm font-semibold text-gray-500">Total Wajib Pajak (MFWP)</div>
                <div class="text-3xl font-bold text-slate-800 mt-2">{{ number_format($totalWp) }}</div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <div class="text-sm font-semibold text-gray-500">Total Pegawai (Tahun {{ date('Y') }})</div>
                <div class="text-3xl font-bold text-slate-800 mt-2">{{ number_format($totalPegawai) }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
