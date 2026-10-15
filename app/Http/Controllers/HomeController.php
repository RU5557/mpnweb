<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // Mengambil data ringkasan sederhana
        $totalWp = DB::table('mfwp')->count();
        $totalPegawai = DB::table('pegawai')->where('tahun', date('Y'))->count();

        return view('home', compact('totalWp', 'totalPegawai'));
    }
}
