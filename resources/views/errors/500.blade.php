<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Mengalami Kendala - MPNWEB</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">

    <!-- Card Container Error Fullscreen Overlay -->
    <div class="max-w-xl w-full bg-slate-800 border border-slate-700 rounded-2xl p-6 md:p-8 shadow-2xl text-center space-y-6">
        
        <!-- Ikon Alert -->
        <div class="mx-auto w-16 h-16 bg-red-500/10 text-red-500 rounded-full flex items-center justify-center text-3xl">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>

        <!-- Judul & Pesan Ramah -->
        <div class="space-y-2">
            <h1 class="text-2xl font-bold text-white">Terjadi Kendala Sistem / Timeout</h1>
            <p class="text-sm text-slate-400">
                Aplikasi MPNWEB mengalami gangguan teknis sementara pada 
                <span id="error-time" class="text-slate-200 font-semibold" data-utc="{{ \Carbon\Carbon::parse($timestamp ?? now())->toISOString() }}">
                    <!-- Fallback jika JS tidak aktif -->
                    {{ \Carbon\Carbon::parse($timestamp ?? now())->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H.i.s') }} WIB
                </span>. Tim sistem administrator kami siap membantu memulihkannya.
            </p>
        </div>

        <!-- Informasi Langkah Tindakan -->
        <div class="bg-slate-900/60 border border-slate-700/50 rounded-xl p-4 text-left text-xs text-slate-300 space-y-2">
            <p class="font-semibold text-slate-200">Langkah Penyelesaian:</p>
            <ol class="list-decimal list-inside space-y-1 text-slate-400">
                <li>Klik tombol <strong class="text-blue-400">Unduh Log Error (.txt)</strong> di bawah.</li>
                <li>Klik tombol <strong class="text-emerald-400">Hubungi Admin WhatsApp</strong>.</li>
                <li>Lampirkan (attach/drag & drop) file <strong class="text-slate-200">.txt</strong> yang terunduh ke dalam chat WhatsApp Admin.</li>
            </ol>
        </div>

        <!-- Group Tombol Aksi -->
        <div class="flex flex-col sm:flex-row gap-3 pt-2">
            
            <!-- 1. Tombol Unduh Log Error (.txt) via Base64 Data Stream -->
            @if(isset($logBase64))
            <a href="data:text/plain;base64,{{ $logBase64 }}" 
               download="{{ $logFileName ?? 'Error_Log_MPNWEB.txt' }}"
               class="flex-1 inline-flex items-center justify-center gap-2 bg-slate-700 hover:bg-slate-600 text-white font-semibold text-sm px-4 py-3 rounded-xl transition shadow-md">
                <i class="fa-solid fa-file-arrow-down text-base text-blue-400"></i>
                Unduh Log Error (.txt)
            </a>
            @endif

            <!-- 2. Tombol Hubungi Admin via WhatsApp -->
            @php
                // Ganti nomor WhatsApp Admin di bawah (Gunakan format internasional tanpa +/0, misal: 628123456789)
                $adminWaNumber = "628123456789"; 
                
                $waText = rawurlencode("Halo Admin, aplikasi MPNWEB saya mengalami kendala pada " . ($timestamp ?? now()->format('Y-m-d H:i:s')) . ". Berikut saya lampirkan file log error (.txt) terkait kendala tersebut.");
                $waUrl  = "https://wa.me/{$adminWaNumber}?text={$waText}";
            @endphp

            <a href="{{ $waUrl }}" 
               target="_blank" 
               class="flex-1 inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm px-4 py-3 rounded-xl transition shadow-md">
                <i class="fa-brands fa-whatsapp text-lg"></i>
                Hubungi Admin WhatsApp
            </a>

        </div>

        <!-- Tombol Kembali ke Dashboard -->
        <div class="pt-2">
            <a href="{{ url('/') }}" class="text-xs text-slate-400 hover:text-slate-200 underline">
                Coba kembali ke Halaman Utama
            </a>
        </div>

    </div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const timeElement = document.getElementById('error-time');
        
        if (timeElement && timeElement.dataset.utc) {
            const utcString = timeElement.dataset.utc;
            const date = new Date(utcString);

            if (!isNaN(date.getTime())) {
                // Mengambil nama zona waktu pengguna secara otomatis (contoh: WIB, WITA, WIT)
                const timeZoneName = new Intl.DateTimeFormat('id-ID', { timeZoneName: 'short' })
                    .formatToParts(date)
                    .find(part => part.type === 'timeZoneName')?.value || '';

                // Format tanggal dan jam sesuai standar lokal pengguna
                const formattedDate = date.toLocaleString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                }).replace(/\./g, ':'); // Ganti separator jika diperlukan

                timeElement.innerText = `${formattedDate} ${timeZoneName}`;
            }
        }
    });
</script>
</body>
</html>