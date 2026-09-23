<header class="h-12 bg-white border-b border-slate-200 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-30 shadow-sm">
    <div class="flex items-center gap-3 text-xs font-medium text-slate-700 overflow-hidden max-w-4xl">
        <span class="bg-pink-100 text-pink-700 text-[11px] px-2.5 py-0.5 rounded-full font-bold flex items-center gap-1.5 whitespace-nowrap shadow-sm">
            <i class="fa-solid fa-bullhorn text-pink-500 text-[10px]"></i> Informasi
        </span>
        
        <div class="truncate text-xs">
            @if(isset($rollingText) && $rollingText)
                <span class="text-slate-500">
                    Update: {{ \Carbon\Carbon::parse($rollingText->tanggal)->format('d-m-Y') }} |
                </span> 
                NKO: <strong class="text-slate-900 font-bold">{{ number_format($rollingText->nko, 2) }}%</strong> | 
                Rank Nasional: <strong class="text-slate-900 font-bold">#{{ $rollingText->ranking_nasional }}</strong> | 
                Rank Kanwil: <strong class="text-slate-900 font-bold">#{{ $rollingText->ranking_kanwil }}</strong>
            @else
                <span class="text-slate-400 italic">Belum ada data info harian.</span>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.index') }}" class="flex items-center gap-2 hover:bg-slate-100 py-1 px-2.5 rounded-lg transition border border-slate-200/80">
            <div class="text-right hidden sm:block">
                <div class="text-xs font-bold text-slate-800 leading-tight">Admin KPP</div>
                <div class="text-[10px] text-slate-500 leading-none">Seksi PDI</div>
            </div>
            <div class="w-6 h-6 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold shadow-sm shrink-0">
                <i class="fa-solid fa-user-gear text-[11px]"></i>
            </div>
        </a>
    </div>
</header>