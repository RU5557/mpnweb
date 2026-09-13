<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Aplikasi Operasional KPP')</title>
    <!-- CDN Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans text-gray-800 antialiased min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="bg-slate-900 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16">
            <div class="flex items-center space-x-3">
                <span class="text-xl font-bold tracking-wide text-blue-400">OPERASIONAL KPP</span>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-slate-800 {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-blue-400 font-semibold' : 'text-gray-300' }}">Dashboard</a>
                <a href="{{ route('wp.search') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-slate-800 {{ request()->routeIs('wp.search') ? 'bg-slate-800 text-blue-400 font-semibold' : 'text-gray-300' }}">Cari WP</a>
            </div>
        </div>
    </nav>

    <!-- Content Container -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-4 text-center text-xs text-gray-500">
        &copy; {{ date('Y') }} Sistem Informasi Operasional KPP.
    </footer>

</body>
</html>