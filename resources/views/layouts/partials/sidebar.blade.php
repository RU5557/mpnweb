<aside 
    @mouseenter="handleMouseEnter()"
    @mouseleave="handleMouseLeave()"
    :class="sidebarOpen || isPinned ? 'w-60' : 'w-20'" 
    class="bg-slate-900 text-slate-300 min-h-screen transition-all duration-300 flex flex-col justify-between fixed left-0 top-0 bottom-0 z-40 border-r border-slate-800 shadow-xl">
    
    <div>
        <!-- Sidebar Header -->
        <div class="h-12 flex items-center justify-between px-4 border-b border-slate-800">
            <div x-show="sidebarOpen || isPinned" x-cloak class="flex items-center gap-2.5 overflow-hidden">
                <div class="bg-blue-600 text-white p-1.5 rounded-lg font-bold flex items-center justify-center w-7 h-7 shadow-md shadow-blue-500/20 shrink-0">
                    <i class="fa-solid fa-chart-pie text-xs"></i>
                </div>
                <span class="font-extrabold text-white text-base tracking-wide">MPNWEB</span>
            </div>
            
            <button @click="isPinned = !isPinned; sidebarOpen = isPinned" 
                    class="p-1.5 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition mx-auto flex items-center justify-center"
                    :title="isPinned ? 'Matikan Pin' : 'Kunci Sidebar'">
                <i class="fa-solid text-xs" :class="isPinned ? 'fa-thumbtack text-blue-400' : (sidebarOpen ? 'fa-bars-staggered' : 'fa-bars')"></i>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="p-3 space-y-1">
            {{-- Dashboard --}}
            @php $isDashboard = request()->routeIs('penerimaan.dashboard'); @endphp
            <a href="{{ route('penerimaan.dashboard') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition-colors {{ $isDashboard ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                <i class="fa-solid fa-border-all text-sm w-5 text-center shrink-0"></i>
                <span x-show="sidebarOpen || isPinned" x-cloak class="truncate">Dashboard</span>
            </a>

            {{-- Penjagaan Dropdown --}}
            @php $isPenjagaan = request()->routeIs('penerimaan.penjagaan.*'); @endphp
            <div x-data="{ open: {{ $isPenjagaan ? 'true' : 'false' }} }" class="space-y-1">
                <button @click="if(!sidebarOpen && !isPinned) { sidebarOpen = true; open = true; } else { open = !open; }" 
                        :class="open ? 'bg-slate-800/80 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition-colors">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-chart-line text-sm w-5 text-center shrink-0"></i>
                        <span x-show="sidebarOpen || isPinned" x-cloak class="truncate">Penjagaan</span>
                    </div>
                    <i x-show="sidebarOpen || isPinned" x-cloak class="fa-solid text-[10px] transition-transform duration-200" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                </button>

                <div x-show="open && (sidebarOpen || isPinned)" 
                     x-cloak
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     class="pl-8 space-y-1">
                    
                    <a href="{{ route('penerimaan.penjagaan.bulanan') }}" 
                       class="block px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs('penerimaan.penjagaan.bulanan') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                        Bulanan
                    </a>
                    <a href="{{ route('penerimaan.penjagaan.harian') }}" 
                       class="block px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs('penerimaan.penjagaan.harian') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                        Harian
                    </a>
                    <a href="{{ route('penerimaan.penjagaan.vs-bulan-lalu') }}" 
                       class="block px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs('penerimaan.penjagaan.vs-bulan-lalu') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                        Vs Bulan Lalu
                    </a>
                </div>
            </div>

            {{-- PKM Links --}}
            <a href="{{ route('penerimaan.pkmpengawasan') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('penerimaan.pkmpengawasan') ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                <i class="fa-solid fa-user-check text-sm w-5 text-center shrink-0"></i>
                <span x-show="sidebarOpen || isPinned" x-cloak class="truncate">PKM Pengawasan</span>
            </a>

            <a href="{{ route('penerimaan.pkmpemeriksaan') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('penerimaan.pkmpemeriksaan') ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                <i class="fa-solid fa-magnifying-glass-chart text-sm w-5 text-center shrink-0"></i>
                <span x-show="sidebarOpen || isPinned" x-cloak class="truncate">PKM Pemeriksaan</span>
            </a>

            <a href="{{ route('penerimaan.pkmpenagihan') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('penerimaan.pkmpenagihan') ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                <i class="fa-solid fa-gavel text-sm w-5 text-center shrink-0"></i>
                <span x-show="sidebarOpen || isPinned" x-cloak class="truncate">PKM Penagihan</span>
            </a>

            <a href="{{ route('wp.search') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('wp.search') ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                <i class="fa-solid fa-magnifying-glass text-sm w-5 text-center shrink-0"></i>
                <span x-show="sidebarOpen || isPinned" x-cloak class="truncate">Pencarian</span>
            </a>
        </nav>
    </div>

    <!-- Sidebar Footer -->
    <div class="p-3 border-t border-slate-800 text-[11px] text-slate-400 flex justify-between items-center">
        <span x-show="sidebarOpen || isPinned" x-cloak class="font-medium">© {{ date('Y') }} MPNWEB</span>
        <span class="bg-slate-800 text-slate-300 px-2 py-0.5 rounded font-mono text-[10px]">v1.0</span>
    </div>
</aside>