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
        // Ambil data rolling text terbaru agar form terisi data eksisting
        $rollingText = RollingText::latest('id')->first();

        return view('admin.index', compact('target', 'rollingText', 'tahunSekarang'));
    }

    public function updateTarget(Request $request)
    {
        $request->validate([
            'tahun'                  => 'required|numeric',
            'target_kantor'          => 'nullable|string',
            'target_ppm'             => 'nullable|string',
            'target_pkm'             => 'nullable|string',
            'target_pbp'             => 'nullable|string',
            'target_pkm_pengawasan'  => 'nullable|string',
            'target_pkm_pemeriksaan' => 'nullable|string',
            'target_pkm_penagihan'   => 'nullable|string',
        ]);

        // Daftar field terizinkan untuk mencegah mass assignment
        $allowedFields = [
            'target_kantor',
            'target_ppm',
            'target_pkm',
            'target_pbp',
            'target_pkm_pengawasan',
            'target_pkm_pemeriksaan',
            'target_pkm_penagihan',
        ];

        $cleanedData = [];

        foreach ($allowedFields as $field) {
            $value = $request->input($field);
            if (!is_null($value)) {
                // Hapus titik ribuan, ubah koma desimal jadi titik desimal jika ada
                $cleaned = str_replace('.', '', $value);
                $cleaned = str_replace(',', '.', $cleaned);
                $cleanedData[$field] = (float) $cleaned;
            } else {
                $cleanedData[$field] = 0;
            }
        }

        Target::updateOrCreate(
            ['tahun' => $request->tahun],
            $cleanedData
        );

        return redirect()->back()->with('success', 'Target tahunan berhasil diperbarui!');
    }

    public function updateRollingText(Request $request)
    {
        $request->validate([
            'tanggal'          => 'required|date',
            'nko'              => 'required|numeric|min:0|max:100',
            'ranking_nasional' => 'required|integer|min:1',
            'ranking_kanwil'   => 'required|integer|min:1',
        ]);

        // Buat record baru untuk riwayat info harian
        RollingText::create([
            'tanggal'          => $request->tanggal,
            'nko'              => $request->nko,
            'ranking_nasional' => $request->ranking_nasional,
            'ranking_kanwil'   => $request->ranking_kanwil,
        ]);

        return redirect()->back()->with('success', 'Rolling text harian berhasil diperbarui!');
    }
}