<!DOCTYPE html>
<html lang="id" x-data="{
    sidebarOpen: false,
    isPinned: false,
    handleMouseEnter() { if (!this.isPinned) this.sidebarOpen = true; },
    handleMouseLeave() { if (!this.isPinned) this.sidebarOpen = false; }
}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'MPNWEB')</title>

    <!-- Global JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Alpine.js (Defer) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Pre-style x-cloak untuk mencegah FOUC -->
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-slate-100 font-sans text-slate-800 text-xs min-h-screen flex antialiased">

    <!-- ==================== SIDEBAR ==================== -->
    @include('layouts.partials.sidebar')

    <!-- ==================== CONTENT WRAPPER ==================== -->
    <div :class="sidebarOpen || isPinned ? 'ml-60' : 'ml-16'"
        class="flex-grow transition-all duration-300 flex flex-col min-h-screen">

        <!-- TOPBAR -->
        @include('layouts.partials.topbar')

        <!-- MAIN CONTENT CONTAINER -->
        <main class="px-4 py-6 flex-grow bg-slate-50">
            <div class="w-full">

                {{-- Flash Message Success --}}
                @if (session('success'))
                    <div
                        class="bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs p-3 rounded-xl mb-5 flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-2 font-medium">
                            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800"><i
                                class="fa-solid fa-xmark text-xs"></i></button>
                    </div>
                @endif

                {{-- View Content --}}
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Push Script Stack dari Child Views -->
    @stack('scripts')
</body>

</html>
