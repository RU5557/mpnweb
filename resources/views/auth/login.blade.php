<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - MPNWEB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4 text-xs antialiased">

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 sm:p-8 border border-slate-200">
        <div class="text-center mb-6">
            <div class="w-10 h-10 bg-blue-600 rounded-xl text-white flex items-center justify-center mx-auto mb-3 shadow-md shadow-blue-500/20">
                <i class="fa-solid fa-user-shield text-lg"></i>
            </div>
            <h1 class="text-lg font-bold text-slate-900">Login Panel Admin</h1>
            <p class="text-xs text-slate-500 mt-0.5">Seksi Pengolahan Data dan Informasi (PDI)</p>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-300 text-red-700 text-xs p-3 rounded-xl mb-5 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-sm shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1.5">Username Admin</label>
                <input type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username"
                    class="w-full bg-slate-50 border @error('username') border-red-500 @else border-slate-300 @enderror text-slate-800 text-xs rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition font-medium">
                @error('username')
                    <span class="text-red-500 text-[11px] mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1.5">Password</label>
                <input type="password" name="password" required autocomplete="current-password"
                    class="w-full bg-slate-50 border @error('password') border-red-500 @else border-slate-300 @enderror text-slate-800 text-xs rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition font-medium">
                @error('password')
                    <span class="text-red-500 text-[11px] mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg text-xs shadow-sm transition">
                Masuk ke Panel Admin
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('penerimaan.dashboard') }}" class="text-xs text-slate-500 hover:text-slate-800 font-medium transition inline-flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard Utama
            </a>
        </div>
    </div>

</body>
</html>