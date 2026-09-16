<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Exception;

class SyncDataSistem extends Command
{
    /**
     * Opsi runner:
     *   php artisan sync:data-sistem
     *   php artisan sync:data-sistem --only=tx --thnsetor=2026 --blnsetor=09
     *   php artisan sync:data-sistem --only=tx --thnsetor=2026
     */
    protected $signature = 'sync:data-sistem 
                            {--only=all : Pilihan target: all, ref, master, tx}
                            {--thnsetor= : Filter tahun setor (contoh: 2026)}
                            {--blnsetor= : Filter bulan setor (contoh: 09 atau 9)}';

    protected $description = 'ETL data dari mpninfo (legacy) ke mpnweb (operasional) dengan auto rebuild mart dan cache invalidation';

    public function handle()
    {
        $target = $this->option('only');
        $thnSetor = $this->option('thnsetor');
        $blnSetor = $this->option('blnsetor');

        $this->info("====================================================");
        $this->info("  MEMULAI ETL DATA SINKRONISASI (Mode: {$target})");
        if ($thnSetor || $blnSetor) {
            $infoPeriode = [];
            if ($thnSetor) $infoPeriode[] = "Tahun: {$thnSetor}";
            if ($blnSetor) $infoPeriode[] = "Bulan: {$blnSetor}";
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

            // 3. Sinkronisasi Transaksi & Auto-Pipeline (Rebuild Summary Marts + Clear Cache)
            if (in_array($target, ['all', 'tx'])) {
                $this->syncDetilTransaksiWp($thnSetor, $blnSetor);

                $this->newLine();
                $this->comment('-> Memicu rekapitulasi Summary Mart Penerimaan...');
                Artisan::call('summary:rebuild');
                $this->info('   [OK] Summary Mart Penerimaan berhasil diperbarui!');

                $this->newLine();
                $this->comment('-> Memicu rekapitulasi Summary Mart PPM...');
                
                // Meneruskan parameter --tahun jika filter tahun diset
                $ppmParams = [];
                if (!empty($thnSetor)) {
                    $ppmParams['--tahun'] = $thnSetor;
                }
                Artisan::call('app:populate-summary-mart-ppm', $ppmParams);
                $this->info('   [OK] Summary Mart PPM berhasil diperbarui!');
            }

            // 4. Optimalisasi & Invalidation Cache Laravel (Terjadi pada SEMUA mode sync)
            $this->newLine();
            $this->comment('-> Membersihkan dan mengoptimalkan Cache Laravel...');
            Cache::flush();
            Artisan::call('cache:clear');
            $this->info('   [OK] Cache aplikasi berhasil dibersihkan!');

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

    private function syncDetilTransaksiWp($thnSetor = null, $blnSetor = null)
    {
        $this->comment('-> Synchronizing: detil_transaksi_wp...');

        $whereConditions = [];
        
        if (!empty($thnSetor)) {
            $whereConditions[] = "thnsetor = " . (int)$thnSetor;
        }

        if (!empty($blnSetor)) {
            $whereConditions[] = "blnsetor = " . (int)$blnSetor;
        }

        $whereSql = "";
        if (count($whereConditions) > 0) {
            $whereSql = " WHERE " . implode(' AND ', $whereConditions);

            $deleteWhereConditions = [];
            if (!empty($thnSetor)) $deleteWhereConditions[] = "thn_setor = " . (int)$thnSetor;
            if (!empty($blnSetor)) $deleteWhereConditions[] = "bln_setor = " . (int)$blnSetor;
            $deleteWhereSql = " WHERE " . implode(' AND ', $deleteWhereConditions);

            DB::statement("DELETE FROM mpnweb.detil_transaksi_wp{$deleteWhereSql};");
            $this->comment("   [i] Menghapus data periode tertentu di mpnweb.detil_transaksi_wp sebelum re-sync.");
        } else {
            DB::statement('TRUNCATE TABLE mpnweb.detil_transaksi_wp;');
        }

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
            {$whereSql}
        ");

        $this->info('   [OK] Tabel detil_transaksi_wp synchronized.');
    }
}