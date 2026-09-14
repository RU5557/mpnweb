<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - MPNWEB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-8 border border-slate-200">
        <div class="text-center mb-8">
            <div class="w-12 h-12 bg-blue-600 rounded-xl text-white flex items-center justify-center mx-auto mb-3 shadow-lg shadow-blue-500/30">
                <i class="fa-solid fa-user-shield text-xl"></i>
            </div>
            <h1 class="text-xl font-bold text-slate-800">Login Panel Admin</h1>
            <p class="text-xs text-slate-500 mt-1">Seksi Pengolahan Data dan Informasi (PDI)</p>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-300 text-red-700 text-xs p-3 rounded-lg mb-6 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Username Admin</label>
                <input type="text" name="username" value="{{ old('username') }}" required autofocus
                    class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-lg p-3 focus:ring-2 focus:ring-blue-500 outline-none transition">
                @error('username')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Password</label>
                <input type="password" name="password" required
                    class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-lg p-3 focus:ring-2 focus:ring-blue-500 outline-none transition">
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg text-sm shadow-md transition">
                Masuk ke Panel Admin
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('penerimaan.dashboard') }}" class="text-xs text-slate-500 hover:text-slate-800 font-medium">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard Utama
            </a>
        </div>
    </div>

</body>
</html>