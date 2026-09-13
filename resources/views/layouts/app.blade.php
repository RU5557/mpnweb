<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Ringkasan - MPNWEB</title>
    <!-- Tailwind CSS & FontAwesome CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans flex antialiased min-h-screen">

    <!-- SIDEBAR NAVIGASI -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col flex-shrink-0 shadow-xl min-h-screen">
        <!-- Logo / Brand Header -->
        <div class="p-5 font-bold text-xl text-white border-b border-slate-800 flex items-center gap-3">
            <div class="bg-blue-600 p-2 rounded-lg text-white flex items-center justify-center w-9 h-9">
                <i class="fa-solid fa-chart-pie text-lg"></i>
            </div>
            <span class="tracking-wide">MPNWEB</span>
        </div>
        
        <!-- Menu Items -->
        <nav class="flex-1 p-4 space-y-1.5 text-sm">
            <a href="#" class="flex items-center gap-3 px-4 py-3 text-white bg-blue-600 rounded-lg font-medium shadow-sm transition">
                <i class="fa-solid fa-border-all w-5 text-center"></i> Dashboard
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-800 hover:text-white rounded-lg transition">
                <i class="fa-solid fa-wallet w-5 text-center"></i> Penerimaan
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-800 hover:text-white rounded-lg transition">
                <i class="fa-solid fa-users w-5 text-center"></i> Masterfile WP
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-800 hover:text-white rounded-lg transition">
                <i class="fa-solid fa-list-check w-5 text-center"></i> Kinerja PKM
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-800 hover:text-white rounded-lg transition">
                <i class="fa-solid fa-gears w-5 text-center"></i> ETL Data Sync
            </a>
        </nav>

        <!-- Footer Sidebar -->
        <div class="p-4 border-t border-slate-800 text-xs text-slate-500 flex items-center justify-between">
            <span>&copy; 2026 MPNWEB System</span>
            <span class="bg-slate-800 text-slate-400 px-2 py-0.5 rounded text-[10px]">v2.0</span>
        </div>
    </aside>

    <!-- AREA KONTEN UTAMA -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <!-- Top Utility Header -->
        <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between">
            <div class="text-sm text-slate-500 font-medium">
                Sistem Informasi Penerimaan & Kinerja PKM
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <div class="text-sm font-semibold text-slate-800">Admin KPP</div>
                    <div class="text-xs text-slate-400">Seksi Pengolahan Data</div>
                </div>
                <div class="w-9 h-9 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold">
                    A
                </div>
            </div>
        </header>

        <!-- Main Content Scroll Area -->
        <main class="flex-1 p-8 overflow-y-auto">
            @yield('content')
        </main>
    </div>

</body>
</html>