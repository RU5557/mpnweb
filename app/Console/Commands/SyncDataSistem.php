<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Exception;

class SyncDataSistem extends Command
{
    protected $signature = 'sync:data-sistem 
                            {--only=all : Pilihan target: all, ref, master, tx}
                            {--thnsetor= : Filter tahun setor (contoh: 2026)}
                            {--blnsetor= : Filter bulan setor (contoh: 09 atau 9)}';

    protected $description = 'ETL data dari mpninfo ke mpnweb dengan auto rebuild mart dan cache invalidation';

    public function handle()
    {
        $target = $this->option('only');
        $thnSetor = $this->option('thnsetor') ? (int) $this->option('thnsetor') : null;
        $blnSetor = $this->option('blnsetor') ? (int) $this->option('blnsetor') : null;

        $this->info("====================================================");
        $this->info("  MEMULAI ETL DATA SINKRONISASI (Mode: {$target})");
        if ($thnSetor || $blnSetor) {
            $infoPeriode = array_filter(["Tahun: {$thnSetor}", "Bulan: {$blnSetor}"]);
            $this->info("  FILTER PERIODE -> " . implode(', ', $infoPeriode));
        }
        $this->info("====================================================");
        $startTime = microtime(true);

        DB::disableQueryLog();

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
            DB::statement('SET UNIQUE_CHECKS = 0;');

            // 1. Sinkronisasi Referensi
            if (in_array($target, ['all', 'ref'])) {
                $this->syncSeksi();
                $this->syncKlu();
                $this->syncKdmap();
                $this->syncPegawai();
            }

            // 2. Sinkronisasi Masterfile WP
            if (in_array($target, ['all', 'master'])) {
                $this->syncMasterfileWp();
            }

            // 3. Sinkronisasi Transaksi
            if (in_array($target, ['all', 'tx'])) {
                $this->syncDetilTransaksiWp($thnSetor, $blnSetor);
            }

            // Rekapitulasi Mart & Cache Invalidation
            $this->newLine();
            $this->comment('-> Memicu rekapitulasi Summary Mart Penerimaan...');
            Artisan::call('summary:rebuild');
            $this->info('   [OK] Summary Mart Penerimaan berhasil diperbarui!');

            $this->newLine();
            $this->comment('-> Memicu rekapitulasi Summary Mart PPM...');
            $ppmParams = $thnSetor ? ['--tahun' => $thnSetor] : [];
            Artisan::call('app:populate-summary-mart-ppm', $ppmParams);
            $this->info('   [OK] Summary Mart PPM berhasil diperbarui!');

            $this->newLine();
            $this->comment('-> Membersihkan Cache Aplikasi...');
            Cache::flush();
            $this->info('   [OK] Cache aplikasi berhasil dibersihkan!');

            $executionTime = round(microtime(true) - $startTime, 2);
            $this->newLine();
            $this->info("====================================================");
            $this->info("  ETL SINKRONISASI SELESAI DALAM {$executionTime} DETIK!");
            $this->info("====================================================");

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->newLine();
            $this->error("ETL ERROR DETECTED: " . $e->getMessage());
            return Command::FAILURE;
        } finally {
            // Gunakan FINALLY agar constraint selalu dinyalakan kembali walau ada error
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            DB::statement('SET UNIQUE_CHECKS = 1;');
        }
    }

    private function syncSeksi()
    {
        $this->comment('-> Synchronizing: seksi...');
        DB::statement('TRUNCATE TABLE seksi;');
        DB::statement("
            INSERT IGNORE INTO seksi (id, kantor, tipe, nama, kode, telp) 
            SELECT id, kantor, tipe, nama, kode, telp 
            FROM mpninfo.seksi
        ");
        $this->info('   [OK] Tabel seksi synchronized.');
    }

    private function syncKlu()
    {
        $this->comment('-> Synchronizing: klu...');
        DB::statement('TRUNCATE TABLE klu;');
        DB::statement("
            INSERT IGNORE INTO klu (kd_klu, nm_klu, kd_kategori, nm_kategori) 
            SELECT kd_klu, nm_klu, kd_kategori, nm_kategori 
            FROM mpninfo.klu_baru
        ");
        $this->info('   [OK] Tabel klu synchronized.');
    }

    private function syncKdmap()
    {
        $this->comment('-> Synchronizing: kdmap...');
        DB::statement('TRUNCATE TABLE kdmap;');
        DB::statement("
            INSERT IGNORE INTO kdmap (kd_map, kd_bayar, jenis_pajak, jenis_bayar, sektor_pajak) 
            SELECT COALESCE(kd_map, ''), COALESCE(kd_bayar, ''), jenis_pajak, jenis_bayar, sektor_pajak 
            FROM mpninfo.kdmap
        ");
        $this->info('   [OK] Tabel kdmap synchronized.');
    }

    private function syncPegawai()
    {
        $this->comment('-> Synchronizing: pegawai...');
        DB::statement('TRUNCATE TABLE pegawai;');
        DB::statement("
            INSERT IGNORE INTO pegawai (kantor, nip, nip2, nama, pangkat, seksi, jabatan, tahun, plh) 
            SELECT kantor, nip, nip2, nama, pangkat, seksi, jabatan, tahun, plh 
            FROM mpninfo.pegawai
        ");
        $this->info('   [OK] Tabel pegawai synchronized.');
    }

    private function syncMasterfileWp()
    {
        $this->comment('-> Synchronizing: masterfile_wp...');
        DB::statement('TRUNCATE TABLE masterfile_wp;');
        DB::statement("
            INSERT IGNORE INTO masterfile_wp (
                admin, npwp, kpp, cabang, npwp15, nama, alamat, kelurahan, kecamatan,
                kota, propinsi, jenis, bentuk_hukum, status, klu, tanggal_daftar,
                tanggal_pkp, tanggal_pkp_cabut, nik, telp, nip_ar, nip_eks, nip_js, npwp16
            )
            SELECT 
                admin, npwp, kpp, cabang,
                CONCAT(LPAD(npwp, 9, '0'), LPAD(kpp, 3, '0'), LPAD(cabang, 3, '0')) AS npwp15,
                nama, alamat, kelurahan, kecamatan, kota, propinsi, jenis, bentukhukum,
                status, klu, tanggaldaftar, tanggalpkp, tanggalpkpcabut, nik, telp,
                nipar, nipeks, nipjs, npwp16
            FROM mpninfo.masterfile
        ");
        $this->info('   [OK] Tabel masterfile_wp synchronized.');
    }

    private function syncDetilTransaksiWp(?int $thnSetor = null, ?int $blnSetor = null)
    {
        $this->comment('-> Synchronizing: detil_transaksi_wp...');

        $bindings = [];
        $whereConditions = [];

        if (!empty($thnSetor)) {
            $whereConditions[] = "thnsetor = ?";
            $bindings[] = $thnSetor;
        }

        if (!empty($blnSetor)) {
            $whereConditions[] = "blnsetor = ?";
            $bindings[] = $blnSetor;
        }

        if (count($whereConditions) > 0) {
            // Hapus data lokal dengan Prepared Statements Aman
            $deleteWhere = [];
            $deleteBindings = [];

            if (!empty($thnSetor)) {
                $deleteWhere[] = "thn_setor = ?";
                $deleteBindings[] = $thnSetor;
            }
            if (!empty($blnSetor)) {
                $deleteWhere[] = "bln_setor = ?";
                $deleteBindings[] = $blnSetor;
            }

            DB::delete("DELETE FROM detil_transaksi_wp WHERE " . implode(' AND ', $deleteWhere), $deleteBindings);
            $this->comment("   [i] Menghapus data periode tertentu di detil_transaksi_wp sebelum re-sync.");

            $whereSql = " WHERE " . implode(' AND ', $whereConditions);
        } else {
            DB::statement('TRUNCATE TABLE detil_transaksi_wp;');
            $whereSql = "";
        }

        // Jalankan Insert dengan Parameter Binding Safe
        DB::insert("
            INSERT IGNORE INTO detil_transaksi_wp (
                kd_kanwil, kpp_adm, npwp, kpp, cabang, npwp15, nama_wp, no_pbk, ntpn, 
                tgl_setor, thn_setor, bln_setor, thn_pajak, masa_pajak, jml_setor, 
                kd_map, kd_bayar, fungsi, jenis, flag_skp, id_sbr_data, tipe
            )
            SELECT 
                kdkanwil, kppadm, npwp, kpp, cabang,
                CONCAT(LPAD(npwp, 9, '0'), LPAD(kpp, 3, '0'), LPAD(cabang, 3, '0')) AS npwp15,
                nama_wp, nopbk, ntpn, tglsetor, thnsetor, blnsetor, thnpajak, masapajak, 
                jmlsetor, kdmap, kdbayar, fungsi, jenis, flag_skp, id_sbr_data, tipe
            FROM mpninfo.ppmpkm_drm
            {$whereSql}
        ", $bindings);

        $this->info('   [OK] Tabel detil_transaksi_wp synchronized.');
    }
}