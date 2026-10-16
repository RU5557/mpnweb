<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'MPN Web' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', system-ui, sans-serif
        }

        .tab {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 500;
            color: #64748b;
            transition: .15s
        }

        .tab:hover {
            background: #f1f5f9;
            color: #0f172a
        }

        .tab.active {
            background: #0f172a;
            color: #fff
        }

        .field {
            border: 1px solid #e2e8f0;
            background: #fff;
            border-radius: 6px;
            height: 28px;
            font-size: 11px;
            padding: 0 8px;
            width: 100%
        }

        .field:disabled {
            background: #f8fafc;
            color: #94a3b8
        }

        .btn-primary {
            background: #0f172a;
            color: #fff;
            border-radius: 6px;
            padding: 5px 12px;
            font-size: 11px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px
        }

        .btn-secondary {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px
        }

        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px
        }
    </style>
</head>

<body class="h-screen flex flex-col bg-[#f8fafc] text-slate-800 overflow-hidden text-[11px]">
    <header class="h-[44px] bg-white border-b border-slate-200 flex items-center justify-between px-3 shrink-0">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-md bg-slate-900 text-white flex items-center justify-center font-bold">M
                </div>
                <div class="font-semibold text-[12px]">MPN Web</div>
            </div>
            <div class="h-4 w-px bg-slate-200"></div>
            <nav class="flex items-center gap-1">
                <a href="{{ route('home') }}" class="tab {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('mfwp.index') }}" class="tab {{ request()->routeIs('mfwp.*') ? 'active' : '' }}">Cari
                    WP</a>
            </nav>
        </div>
    </header>
    <div class="flex-1 flex overflow-hidden">{{ $slot }}</div>
    <footer
        class="h-6 bg-white border-t border-slate-200 flex items-center justify-between px-3 text-[10px] text-slate-500 shrink-0">
        <span>{{ $statusText ?? 'Siap' }}</span>
        <span>{{ date('d/m/Y') }}</span>
    </footer>
</body>

</html>
