@echo off
:: ============================================================================
:: Skrip Otomatis Pendaftaran Windows Task Scheduler untuk Laravel Scheduler
:: Proyek: mpnweb (ETL Synchronization)
:: ============================================================================
:: PERHATIAN: Jalankan skrip ini dengan Klik Kanan -> "Run as Administrator"
:: ============================================================================

TITLE Registrasi Laravel Task Scheduler - mpnweb

:: ----------------------------------------------------------------------------
:: KONFIGURASI PATH (Sesuaikan variabel jika lokasi PHP atau Proyek berbeda)
:: ----------------------------------------------------------------------------
set "TASK_NAME=LaravelScheduler_mpnweb"
set "PHP_PATH=C:\xampp\php\php.exe"
set "PROJECT_PATH=C:\xampp\htdocs\mpnweb"
set "ARTISAN_PATH=%PROJECT_PATH%\artisan"

echo ============================================================================
echo          PEMBUATAN TASKS SCHEDULER OTOMATIS (LARAVEL ETL)
echo ============================================================================
echo Nama Task    : %TASK_NAME%
echo Path PHP     : %PHP_PATH%
echo Path Artisan : %ARTISAN_PATH%
echo ============================================================================
echo.

:: Check hak akses Administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERROR] Skrip ini memerlukan Hak Akses Administrator!
    echo.
    echo Harap tutup jendela ini, klik kanan pada file .bat ini,
    echo lalu pilih "Run as administrator" / "Jalankan sebagai administrator".
    echo.
    pause
    exit /b 1
)

:: Pendaftaran Task Scheduler menggunakan schtasks.exe
:: /TN : Nama Task
:: /TR : Command eksekusi lengkap (php.exe "path\to\artisan" schedule:run)
:: /SC : Skedul berbasis menit (MINUTE)
:: /MO : Frekuensi pemicuan (setiap 5 menit)
:: /RL : Level hak akses tertinggi (HIGHEST)
:: /RU : Berjalan di latar belakang menggunakan akun SYSTEM
:: /F  : Timpa (overwrite) jika task dengan nama sama sudah ada

schtasks /create /f ^
    /tn "%TASK_NAME%" ^
    /tr "\"%PHP_PATH%\" \"%ARTISAN_PATH%\" schedule:run" ^
    /sc MINUTE ^
    /mo 5 ^
    /rl HIGHEST ^
    /ru "SYSTEM"

if %errorLevel% equ 0 (
    echo.
    echo ============================================================================
    echo [BERHASIL] Task Scheduler "%TASK_NAME%" berhasil terpasang!
    echo.
    echo Rincian Otomatisasi:
    echo  - Interval Eksekusi : Setiap 5 Menit
    echo  - Perintah          : %PHP_PATH% %ARTISAN_PATH% schedule:run
    echo  - Akun Eksekutor    : NT AUTHORITY\SYSTEM (Latar Belakang)
    echo  - Kebijakan         : Overwrite jika nama task sudah ada
    echo ============================================================================
) else (
    echo.
    echo [GAGAL] Terjadi kesalahan saat mendaftarkan Task Scheduler.
    echo Pastikan path PHP dan Artisan sudah sesuai dengan lokasi server Anda.
)

echo.
pause
