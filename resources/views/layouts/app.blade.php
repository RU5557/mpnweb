<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard Monitoring Pajak' }}</title>
    <!-- Tailwind CSS CDN (atau build via Vite) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-100 text-gray-800 font-sans antialiased">

    <!-- Top Bar Navigation -->
    <header class="bg-slate-900 text-white shadow-lg sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo / Brand -->
                <div class="flex items-center space-x-3">
                    <div class="bg-yellow-500 text-slate-900 font-bold px-2.5 py-1 rounded text-lg">DJP</div>
                    <span class="font-semibold text-lg tracking-wide">Monitoring KPP</span>
                </div>

                <!-- Navigation Links Bar -->
                <nav class="hidden md:flex space-x-1">
                    <a href="#"
                        class="px-3 py-2 rounded-md text-sm font-medium hover:bg-slate-800 transition">Dashboard</a>
                    <a href="{{ route('mfwp.index') }}"
                        class="px-3 py-2 rounded-md text-sm font-medium bg-slate-800 text-yellow-400">Pencarian MFWP</a>
                    <a href="#"
                        class="px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:bg-slate-800 hover:text-white transition">Pencarian
                        DRM</a>
                    <a href="#"
                        class="px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:bg-slate-800 hover:text-white transition">Monitoring
                        SPT</a>
                </nav>

                <!-- User Info -->
                <div class="text-xs text-gray-300">
                    Server Internal KPP
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Shell -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

</body>

</html>
