<!DOCTYPE html>
<html lang="id" x-data="{ sidebarOpen: true }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MPNWEB')</title>

    <!-- Tailwind CSS & Font Awesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js untuk Sidebar Toggle & Modals -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-100 font-sans text-slate-800 min-h-screen flex antialiased">

    <!-- ==================== SIDEBAR ==================== -->
    <aside 
        :class="sidebarOpen ? 'w-64' : 'w-20'" 
        class="bg-slate-900 text-slate-300 min-h-screen transition-all duration-300 flex flex-col justify-between fixed left-0 top-0 bottom-0 z-40 border-r border-slate-800 shadow-lg">
        
        <div>
            <!-- Sidebar Header / Logo & Collapse Button -->
            <div class="h-16 flex items-center justify-between px-4 border-b border-slate-800">
                <div x-show="sidebarOpen" class="flex items-center gap-3">
                    <div class="bg-blue-600 text-white p-2 rounded-lg font-bold flex items-center justify-center w-9 h-9 shadow-md shadow-blue-500/20">
                        <i class="fa-solid fa-chart-pie text-base"></i>
                    </div>
                    <span class="font-bold text-white text-lg tracking-wide">MPNWEB</span>
                </div>
                
                <!-- Toggle Button -->
                <button @click="sidebarOpen = !sidebarOpen" 
                        class="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition mx-auto"
                        title="Sembunyikan/Tampilkan Sidebar">
                    <i class="fa-solid" :class="sidebarOpen ? 'fa-angles-left' : 'fa-bars'"></i>
                </button>
            </div>

<nav class="p-4 space-y-2">
    <!-- 1. Dashboard -->
    <a href="{{ route('penerimaan.dashboard') }}" 
       class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('penerimaan.dashboard') ? 'bg-blue-600 text-white font-semibold' : 'hover:bg-slate-800 hover:text-white' }}">
        <i class="fa-solid fa-border-all text-base w-6 text-center"></i>
        <span x-show="sidebarOpen">Dashboard</span>
    </a>

    <!-- 2. PPM -->
    <a href="{{ route('penerimaan.ppm') }}" 
       class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('penerimaan.ppm') ? 'bg-blue-600 text-white font-semibold' : 'hover:bg-slate-800 hover:text-white' }}">
        <i class="fa-solid fa-wallet text-base w-6 text-center"></i>
        <span x-show="sidebarOpen">Penerimaan PPM</span>
    </a>

    <!-- 3. PKM Pengawasan -->
    <a href="{{ route('penerimaan.pkmpengawasan') }}" 
       class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('penerimaan.pkmpengawasan') ? 'bg-blue-600 text-white font-semibold' : 'hover:bg-slate-800 hover:text-white' }}">
        <i class="fa-solid fa-user-check text-base w-6 text-center"></i>
        <span x-show="sidebarOpen">PKM Pengawasan</span>
    </a>

    <!-- 4. PKM Pemeriksaan -->
    <a href="{{ route('penerimaan.pkmpemeriksaan') }}" 
       class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('penerimaan.pkmpemeriksaan') ? 'bg-blue-600 text-white font-semibold' : 'hover:bg-slate-800 hover:text-white' }}">
        <i class="fa-solid fa-magnifying-glass-chart text-base w-6 text-center"></i>
        <span x-show="sidebarOpen">PKM Pemeriksaan</span>
    </a>

    <!-- 5. PKM Penagihan -->
    <a href="{{ route('penerimaan.pkmpenagihan') }}" 
       class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('penerimaan.pkmpenagihan') ? 'bg-blue-600 text-white font-semibold' : 'hover:bg-slate-800 hover:text-white' }}">
        <i class="fa-solid fa-gavel text-base w-6 text-center"></i>
        <span x-show="sidebarOpen">PKM Penagihan</span>
    </a>
</nav>
        </div>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-slate-800 text-xs text-slate-500 flex justify-between items-center">
            <span x-show="sidebarOpen">© {{ date('Y') }} MPNWEB System</span>
            <span class="bg-slate-800 text-slate-400 px-2 py-0.5 rounded text-[10px] font-mono">v2.0</span>
        </div>
    </aside>

    <!-- ==================== CONTENT WRAPPER ==================== -->
    <div :class="sidebarOpen ? 'ml-64' : 'ml-20'" class="flex-grow transition-all duration-300 flex flex-col min-h-screen">
        
<!-- TOPBAR NAV -->
<header class="h-16 bg-white border-b border-slate-200 px-8 flex items-center justify-between sticky top-0 z-30 shadow-sm">
    
    <!-- Info Harian Statis dengan Tanggal Update -->
    <div class="flex items-center gap-3 text-sm font-medium text-slate-700 overflow-hidden max-w-3xl">
        <!-- Badge Informasi -->
        <span class="bg-pink-100 text-pink-700 text-xs px-3 py-1 rounded-full font-bold flex items-center gap-1.5 whitespace-nowrap shadow-sm">
            <i class="fa-solid fa-bullhorn text-pink-500"></i> Informasi
        </span>
        
        <!-- Teks Statis -->
        <div class="truncate">
            @if(isset($rollingText) && $rollingText)
                <span class="text-slate-500 font-normal">
                    (Tanggal update: {{ \Carbon\Carbon::parse($rollingText->tanggal)->translatedFormat('d M Y') }}):
                </span> 
                NKO: <strong class="text-slate-900">{{ number_format($rollingText->nko, 2) }}%</strong> | 
                Rank Nasional: <strong class="text-slate-900">#{{ $rollingText->ranking_nasional }}</strong> | 
                Rank Kanwil: <strong class="text-slate-900">#{{ $rollingText->ranking_kanwil }}</strong>
                @if($rollingText->pesan_tambahan)
                    | <span class="text-slate-600">{{ $rollingText->pesan_tambahan }}</span>
                @endif
            @else
                <span class="text-slate-400 italic">Belum ada data info harian.</span>
            @endif
        </div>
    </div>

    <!-- Profile & Link Admin Kanan Atas -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.index') }}" class="flex items-center gap-3 hover:bg-slate-100 p-1.5 px-3 rounded-xl transition border border-slate-200/60">
            <div class="text-right">
                <div class="text-sm font-bold text-slate-800 leading-none">Admin KPP</div>
                <div class="text-[11px] text-slate-400 mt-1">Seksi Pengolahan Data</div>
            </div>
            <div class="w-9 h-9 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold shadow-sm">
                <i class="fa-solid fa-user-gear text-sm"></i>
            </div>
        </a>
    </div>
</header>

        <!-- MAIN CONTENT CONTAINER -->
        <main class="p-8 flex-grow bg-slate-50">
            <!-- Flash Alert (Sukses/Gagal) -->
            @if(session('success'))
                <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 text-sm p-4 rounded-xl mb-6 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Dynamic Content -->
            @yield('content')
        </main>
    </div>

</body>
</html>