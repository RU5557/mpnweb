<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Target;
use App\Models\RollingText;

class AdminController extends Controller
{
    public function index()
    {
        $tahunSekarang = date('Y');
        
        $target = Target::where('tahun', $tahunSekarang)->first();
        $rollingText = RollingText::latest('tanggal')->first();

        return view('admin.index', compact('target', 'rollingText', 'tahunSekarang'));
    }

    public function updateTarget(Request $request)
    {
        $request->validate([
            'tahun' => 'required|numeric',
        ]);

        $data = $request->except('_token');

        // Clean format titik rupiah/ribuan menjadi angka murni untuk DB
        foreach ($data as $key => $value) {
            if ($key !== 'tahun' && !is_null($value)) {
                // Hapus titik ribuan, ubah koma desimal jadi titik desimal jika ada
                $cleaned = str_replace('.', '', $value);
                $cleaned = str_replace(',', '.', $cleaned);
                $data[$key] = (float) $cleaned;
            }
        }

        Target::updateOrCreate(
            ['tahun' => $request->tahun],
            $data
        );

        return redirect()->back()->with('success', 'Target tahunan berhasil diperbarui!');
    }

    public function updateRollingText(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'nko' => 'required|numeric',
            'ranking_nasional' => 'required|numeric',
            'ranking_kanwil' => 'required|numeric',
            'pesan_tambahan' => 'nullable|string',
        ]);

        RollingText::updateOrCreate(
            ['tanggal' => $request->tanggal],
            $request->except('_token')
        );

        return redirect()->back()->with('success', 'Rolling text harian berhasil diperbarui!');
    }
}