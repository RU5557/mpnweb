<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        if (session('is_admin')) {
            return redirect()->route('admin.index');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Mengambil nilai username & password langsung dari file .env
        // Parameter kedua ('admin') adalah nilai fallback jika .env tidak terbaca
        $adminUser = env('ADMIN_USERNAME', 'admin');
        $adminPass = env('ADMIN_PASSWORD', 'admin');

        if ($request->username === $adminUser && $request->password === $adminPass) {
            // Simpan session status login admin
            session(['is_admin' => true]);

            return redirect()->route('admin.index')->with('success', 'Berhasil login sebagai Admin!');
        }

        return back()->withErrors(['username' => 'Username atau password admin salah!'])->withInput();
    }

    public function logout(Request $request)
    {
        session()->forget('is_admin');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('penerimaan.dashboard')->with('success', 'Berhasil keluar dari Panel Admin.');
    }
}
