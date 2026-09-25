@echo off
title ETL Sinkronisasi Data MPNWEB
:: Berpindah otomatis ke direktori project Laragon Anda
cd /d "C:\laragon\www\mpnweb"

:menu
cls
echo ========================================================
echo         PANEL SINKRONISASI DATA (ETL MPNWEB)
echo ========================================================
echo.
echo  [1] Semua Tabel (Full Sync All Data)
echo  [2] Detil Transaksi WP (Bisa Filter Periode)
echo  [3] Masterfile WP Saja
echo  [4] Tabel Referensi Saja (Pegawai, Seksi, KLU, MAP)
echo  [5] Keluar
echo.
echo * Catatan: Semua opsi otomatis memicu Maintenance Mode,
echo           Rebuild Summary Mart, dan Pembersihan Cache.
echo ========================================================
set /p pilihan="Pilih opsi menu [1-5]: "

if "%pilihan%"=="1" goto sync_all
if "%pilihan%"=="2" goto sync_tx
if "%pilihan%"=="3" goto sync_master
if "%pilihan%"=="4" goto sync_ref
if "%pilihan%"=="5" goto keluar

echo.
echo [ERROR] Pilihan tidak valid! Silakan tekan tombol apa saja untuk mencoba lagi.
pause >nul
goto menu

:: ========================================================
:: OPSI 1: SEMUA TABEL
:: ========================================================
:sync_all
echo.
echo [+] Memproses Sinkronisasi SEMUA Tabel...
php artisan sync:data-sistem --only=all --maintenance
echo.
pause
goto menu

:: ========================================================
:: OPSI 2: DETIL TRANSAKSI WP (DENGAN FILTER PERIODE)
:: ========================================================
:sync_tx
echo.
echo --------------------------------------------------------
echo  FILTER PERIODE TRANSAKSI (Kosongkan jika ingin Sync ALL)
echo --------------------------------------------------------
set /p thn="Masukkan Tahun Setor (Contoh: 2026 / tekan Enter untuk Semua): "
set /p bln="Masukkan Bulan Setor (Contoh: 09 / tekan Enter untuk Semua): "

set cmd_args=--only=tx --maintenance
if not "%thn%"=="" set cmd_args=%cmd_args% --thnsetor=%thn%
if not "%bln%"=="" set cmd_args=%cmd_args% --blnsetor=%bln%

echo.
echo [+] Memproses Sinkronisasi Detil Transaksi WP...
php artisan sync:data-sistem %cmd_args%
echo.
pause
goto menu

:: ========================================================
:: OPSI 3: MASTERFILE WP
:: ========================================================
:sync_master
echo.
echo [+] Memproses Sinkronisasi Masterfile WP...
php artisan sync:data-sistem --only=master --maintenance
echo.
pause
goto menu

:: ========================================================
:: OPSI 4: TABEL REFERENSI
:: ========================================================
:sync_ref
echo.
echo [+] Memproses Sinkronisasi Tabel Referensi...
php artisan sync:data-sistem --only=ref --maintenance
echo.
pause
goto menu

:keluar
exit