<header class="h-12 bg-white border-b border-slate-200 px-5 flex items-center justify-between sticky top-0 z-30 shadow-sm">
    <div class="flex items-center gap-3 text-[13px] font-medium text-slate-700 overflow-hidden max-w-4xl">
        <span class="bg-pink-100 text-pink-700 text-xs px-2.5 py-0.5 rounded-full font-bold flex items-center gap-1.5 whitespace-nowrap shadow-sm">
            <i class="fa-solid fa-bullhorn text-pink-500"></i> Informasi
        </span>
        
        <div class="truncate text-[13px] md:text-sm">
            @if(isset($rollingText) && $rollingText)
                <span class="text-slate-500">
                    Update: {{ \Carbon\Carbon::parse($rollingText->tanggal)->format('d-m-Y') }} |
                </span> 
                NKO: <strong class="text-slate-900">{{ number_format($rollingText->nko, 2) }}%</strong> | 
                Rank Nasional: <strong class="text-slate-900">#{{ $rollingText->ranking_nasional }}</strong> | 
                Rank Kanwil: <strong class="text-slate-900">#{{ $rollingText->ranking_kanwil }}</strong>
            @else
                <span class="text-slate-400 italic">Belum ada data info harian.</span>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.index') }}" class="flex items-center gap-2.5 hover:bg-slate-100 py-1 px-2.5 rounded-lg transition border border-slate-200/60">
            <div class="text-right">
                <div class="text-xs font-bold text-slate-800 leading-tight">Admin KPP</div>
                <div class="text-[10px] text-slate-500 leading-none">Seksi Pengolahan Data</div>
            </div>
            <div class="w-7 h-7 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold shadow-sm shrink-0">
                <i class="fa-solid fa-user-gear text-xs"></i>
            </div>
        </a>
    </div>
</header>