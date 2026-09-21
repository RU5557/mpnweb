@echo off
title ETL Sinkronisasi Data MPNWEB
:: Berpindah otomatis ke direktori project Laravel
cd /d "C:\xampp\htdocs\mpnweb"

:menu
cls
echo ========================================================
echo         PANEL SINKRONISASI DATA (ETL MPNWEB)
echo ========================================================
echo.
echo  [1] Sinkronisasi SEMUA Tabel (Full Sync)
echo  [2] Sinkronisasi Detil Transaksi WP (Harian / Per-Bulan)
echo  [3] Sinkronisasi Masterfile WP Saja (Mingguan)
echo  [4] Sinkronisasi Tabel Referensi Saja (Pegawai, Seksi, KLU, MAP)
echo  [5] Keluar
echo.
echo ========================================================
set /p pilihan="Pilih opsi menu [1-5]: "

if "%pilihan%"=="1" goto sync_all
if "%pilihan%"=="2" goto sync_tx_menu
if "%pilihan%"=="3" goto sync_master
if "%pilihan%"=="4" goto sync_ref
if "%pilihan%"=="5" goto keluar

echo.
echo [ERROR] Pilihan tidak valid! Silakan tekan tombol apa saja untuk mencoba lagi.
pause >nul
goto menu

:sync_all
echo.
echo [+] Memproses Sinkronisasi SEMUA Tabel...
php artisan sync:data-sistem --only=all
echo.
pause
goto menu

:sync_tx_menu
echo.
echo --------------------------------------------------------
echo  FILTER PERIODE TRANSAKSI (Kosongkan jika ingin Sync ALL)
echo --------------------------------------------------------
set /p thn="Masukkan Tahun Setor (Contoh: 2026 / tekan Enter untuk Semua): "
set /p bln="Masukkan Bulan Setor (Contoh: 09 / tekan Enter untuk Semua): "

set cmd_args=--only=tx
if not "%thn%"=="" set cmd_args=%cmd_args% --thnsetor=%thn%
if not "%bln%"=="" set cmd_args=%cmd_args% --blnsetor=%bln%

echo.
echo [+] Memproses Sinkronisasi Detil Transaksi WP...
php artisan sync:data-sistem %cmd_args%
echo.
pause
goto menu

:sync_master
echo.
echo [+] Memproses Sinkronisasi Masterfile WP (Mingguan)...
php artisan sync:data-sistem --only=master
echo.
pause
goto menu

:sync_ref
echo.
echo [+] Memproses Sinkronisasi Tabel Referensi...
php artisan sync:data-sistem --only=ref
echo.
pause
goto menu

:keluar
exit