<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Exception;

class SyncDataSistem extends Command
{
    /**
     * Opsi runner:
     *   php artisan sync:data-sistem
     *   php artisan sync:data-sistem --only=ref
     *   php artisan sync:data-sistem --only=master
     *   php artisan sync:data-sistem --only=tx
     */
    protected $signature = 'sync:data-sistem {--only=all : Pilihan target: all, ref, master, tx}';

    protected $description = 'ETL data dari mpninfo (legacy) ke mpnweb (operasional)';

    public function handle()
    {
        $target = $this->option('only');

        $this->info("====================================================");
        $this->info("  MEMULAI ETL DATA SINKRONISASI (Mode: {$target})");
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

            // 3. Sinkronisasi Transaksi & Rebuild Summary Mart
            if (in_array($target, ['all', 'tx'])) {
                $this->syncDetilTransaksiWp();

                $this->newLine();
                $this->comment('-> Memicu otomatis rekapitulasi summary mart...');
                Artisan::call('summary:rebuild');
                $this->info('   [OK] Summary Mart berhasil diperbarui!');
            }

            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            DB::statement('SET UNIQUE_CHECKS = 1;');

            $executionTime = round(microtime(true) - $startTime, 2);
            $this->newLine();
            $this->info("====================================================");
            $this->info("  ETL SINKRONISASI SELESAI DALAM {$executionTime} DETIK!");
            $this->info("====================================================");

            return Command::SUCCESS;

        } catch (Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            DB::statement('SET UNIQUE_CHECKS = 1;');

            $this->newLine();
            $this->error("ETL ERROR DETECTED: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function syncSeksi()
    {
        $this->comment('-> Synchronizing: seksi...');
        DB::statement('TRUNCATE TABLE mpnweb.seksi;');
        DB::statement("
            INSERT IGNORE INTO mpnweb.seksi (id, kantor, tipe, nama, kode, telp) 
            SELECT id, kantor, tipe, nama, kode, telp 
            FROM mpninfo.seksi
        ");
        $this->info('   [OK] Tabel seksi synchronized.');
    }

    private function syncKlu()
    {
        $this->comment('-> Synchronizing: klu...');
        DB::statement('TRUNCATE TABLE mpnweb.klu;');
        DB::statement("
            INSERT IGNORE INTO mpnweb.klu (kd_klu, nm_klu, kd_kategori, nm_kategori) 
            SELECT kd_klu, nm_klu, kd_kategori, nm_kategori 
            FROM mpninfo.klu_baru
        ");
        $this->info('   [OK] Tabel klu synchronized.');
    }

    private function syncKdmap()
    {
        $this->comment('-> Synchronizing: kdmap...');
        DB::statement('TRUNCATE TABLE mpnweb.kdmap;');
        DB::statement("
            INSERT IGNORE INTO mpnweb.kdmap (kd_map, kd_bayar, jenis_pajak, jenis_bayar, sektor_pajak) 
            SELECT COALESCE(kd_map, ''), COALESCE(kd_bayar, ''), jenis_pajak, jenis_bayar, sektor_pajak 
            FROM mpninfo.kdmap
        ");
        $this->info('   [OK] Tabel kdmap synchronized.');
    }

    private function syncPegawai()
    {
        $this->comment('-> Synchronizing: pegawai...');
        DB::statement('TRUNCATE TABLE mpnweb.pegawai;');
        DB::statement("
            INSERT IGNORE INTO mpnweb.pegawai (kantor, nip, nip2, nama, pangkat, seksi, jabatan, tahun, plh) 
            SELECT kantor, nip, nip2, nama, pangkat, seksi, jabatan, tahun, plh 
            FROM mpninfo.pegawai
        ");
        $this->info('   [OK] Tabel pegawai synchronized.');
    }

    private function syncMasterfileWp()
    {
        $this->comment('-> Synchronizing: masterfile_wp...');
        DB::statement('TRUNCATE TABLE mpnweb.masterfile_wp;');
        DB::statement("
            INSERT IGNORE INTO mpnweb.masterfile_wp (
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

    private function syncDetilTransaksiWp()
    {
        $this->comment('-> Synchronizing: detil_transaksi_wp...');
        DB::statement('TRUNCATE TABLE mpnweb.detil_transaksi_wp;');
        DB::statement("
            INSERT IGNORE INTO mpnweb.detil_transaksi_wp (
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
        ");
        $this->info('   [OK] Tabel detil_transaksi_wp synchronized.');
    }
}