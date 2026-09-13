<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Target;
use App\Models\RollingText;

class AdminController extends Controller
{
    // Tampilkan Form Admin
    public function index()
    {
        $tahunSekarang = date('Y');
        
        // Ambil target tahun berjalan atau buat default jika belum ada
        $target = Target::where('tahun', $tahunSekarang)->first();
        
        // Ambil data rolling text harian paling baru
        $rollingText = RollingText::latest('tanggal')->first();

        return view('admin.index', compact('target', 'rollingText', 'tahunSekarang'));
    }

    // Simpan / Update Target Tahunan
    public function updateTarget(Request $request)
    {
        $request->validate([
            'tahun' => 'required|numeric',
            'target_kantor' => 'required|numeric',
            'target_ppm' => 'required|numeric',
            'target_pkm' => 'required|numeric',
            'target_pbp' => 'required|numeric',
            'target_pkm_pengawasan' => 'required|numeric',
            'target_pkm_pemeriksaan' => 'required|numeric',
            'target_pkm_penagihan' => 'required|numeric',
        ]);

        Target::updateOrCreate(
            ['tahun' => $request->tahun],
            $request->except('_token')
        );

        return redirect()->back()->with('success', 'Target tahunan berhasil diperbarui!');
    }

    // Simpan / Update Rolling Text Harian
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