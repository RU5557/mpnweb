@extends('layouts.app')

@section('title', 'Dashboard Penerimaan')

@section('content')
<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard Realisasi Penerimaan</h1>
            <p class="text-sm text-gray-500">Data penerimaan teragregasi dari tabel summary mart.</p>
        </div>
        
        <!-- Filter Tahun -->
        <form method="GET" action="{{ route('dashboard') }}" class="flex items-center space-x-2">
            <label for="tahun" class="text-sm font-medium text-gray-700">Tahun:</label>
            <select name="tahun" id="tahun" onchange="this.form.submit()" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 shadow-sm">
                @foreach(range(date('Y'), date('Y') - 3) as $y)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Realisasi ({{ $tahun }})</span>
            <div class="text-2xl font-extrabold text-blue-600 mt-2">
                Rp {{ number_format($totalTahunIni, 0, ',', '.') }}
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Transaksi (Setoran)</span>
            <div class="text-2xl font-extrabold text-emerald-600 mt-2">
                {{ number_format($penerimaanBulanan->sum('total_tx'), 0, ',', '.') }} Spt/NTPN
            </div>
        </div>
    </div>

    <!-- Tabel Realisasi Bulanan -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-base font-semibold text-gray-800">Rincian Penerimaan Per Bulan</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3">Bulan</th>
                        <th class="px-6 py-3 text-right">Jumlah Transaksi</th>
                        <th class="px-6 py-3 text-right">Total Realisasi (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($penerimaanBulanan as $row)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ DateTime::createFromFormat('!m', $row->bln_setor)->format('F') }} ({{ $row->bln_setor }})
                            </td>
                            <td class="px-6 py-4 text-right">{{ number_format($row->total_tx, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                Rp {{ number_format($row->total_penerimaan, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-400">
                                Belum ada data summary penerimaan untuk tahun {{ $tahun }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection