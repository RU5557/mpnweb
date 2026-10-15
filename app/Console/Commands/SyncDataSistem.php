<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SyncDataSistem extends Command
{
    /**
     * Tanda tangan command mendukung target: all, ref, users, master, tx, drm, spt
     */
    protected $signature = 'sync:data-sistem 
                            {--only=all : Pilihan target: all, ref, users, master, tx, drm, spt}
                            {--thnsetor= : Filter tahun (contoh: 2026)}
                            {--blnsetor= : Filter bulan (contoh: 09 atau 9)}
                            {--force : Paksa jalankan sync tanpa cek perubahan checksum}
                            {--maintenance : Aktifkan mode maintenance selama sync}';

    /**
     * Deskripsi console command.
     */
    protected $description = 'ETL data dari mpninfo ke mpnweb - HIGH PERFORMANCE (Smart Checksum, Table Swapping, Batching, Cursor)';

    public function handle()
    {
        $target = $this->option('only');
        $thnSetor = $this->option('thnsetor');
        $blnSetor = $this->option('blnsetor');
        $force = $this->option('force');
        $useMaintenance = $this->option('maintenance');

        $this->info('====================================================');
        $this->info("  MEMULAI ETL DATA SINKRONISASI (Mode Target: {$target})");
        $this->info('  SUMBER: mpninfo  --->  TUJUAN: mpnweb');
        if ($thnSetor || $blnSetor) {
            $infoPeriode = [];
            if ($thnSetor) {
                $infoPeriode[] = "Tahun: {$thnSetor}";
            }
            if ($blnSetor) {
                $infoPeriode[] = "Bulan: {$blnSetor}";
            }
            $this->info('  FILTER PERIODE -> '.implode(', ', $infoPeriode));
        } else {
            $this->info('  FILTER PERIODE -> FULL REFRESH (Semua Data)');
        }
        $this->info('  MODE OPTIMASI: SMART CHECKSUM + SWAPPING + BATCH 2000 + CURSOR STREAMING');
        $this->info('====================================================');
        $startTime = microtime(true);

        // 1. Matikan Query Log Laravel untuk menghemat RAM PHP secara drastis
        DB::disableQueryLog();

        // 2. Mode Maintenance (opsional)
        if ($useMaintenance) {
            $this->comment('-> Mengaktifkan Mode Maintenance...');
            Artisan::call('down', ['--secret' => 'etl-sync-mode']);
        }

        try {
            // 3. Matikan pemeriksaan constraint & autocommit sementara di session MySQL
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
            DB::statement('SET UNIQUE_CHECKS = 0;');
            DB::statement('SET AUTOCOMMIT = 0;');

            $anyTableSynced = false;

            // A. Sinkronisasi User & Referensi (Users, Seksi, KLU, MAP, Pegawai)
            if (in_array($target, ['all', 'ref', 'users'])) {
                if ($force || $this->hasTableChanged('users')) {
                    $this->syncUsers();
                    $anyTableSynced = true;
                } else {
                    $this->info('   [SKIP] Tabel users tidak ada perubahan.');
                }
            }

            if (in_array($target, ['all', 'ref'])) {
                if ($force || $this->hasTableChanged('seksi')) {
                    $this->syncSeksi();
                    $anyTableSynced = true;
                } else {
                    $this->info('   [SKIP] Tabel seksi tidak ada perubahan.');
                }

                if ($force || $this->hasTableChanged('klu_baru', "SELECT CONCAT(COUNT(*), '-', COALESCE(MAX(kd_klu), '')) as checksum FROM mpninfo.klu_baru")) {
                    $this->syncKlu();
                    $anyTableSynced = true;
                } else {
                    $this->info('   [SKIP] Tabel klu_baru tidak ada perubahan.');
                }

                if ($force || $this->hasTableChanged('map_baru')) {
                    $this->syncKdmap();
                    $anyTableSynced = true;
                } else {
                    $this->info('   [SKIP] Tabel map_baru tidak ada perubahan.');
                }

                if ($force || $this->hasTableChanged('pegawai', "SELECT CONCAT(COUNT(*), '-', COALESCE(MAX(nip), '')) as checksum FROM mpninfo.pegawai")) {
                    $this->syncPegawai();
                    $anyTableSynced = true;
                } else {
                    $this->info('   [SKIP] Tabel pegawai tidak ada perubahan.');
                }
            }

            // B. Sinkronisasi Masterfile WP (mpninfo.masterfile -> mpnweb.mfwp)
            if (in_array($target, ['all', 'master'])) {
                $checkQueryMaster = "SELECT CONCAT(COUNT(*), '-', COALESCE(MAX(npwp), ''), '-', COALESCE(MAX(tanggaldaftar), '')) as checksum FROM mpninfo.masterfile";
                if ($force || $this->hasTableChanged('masterfile', $checkQueryMaster)) {
                    $this->syncMasterfileWpBatch();
                    $anyTableSynced = true;
                } else {
                    $this->info('   [SKIP] Tabel masterfile WP tidak ada perubahan.');
                }
            }

            // C. Sinkronisasi Detil Transaksi WP / DRM (mpninfo.ppmpkm_drm -> mpnweb.drm)
            if (in_array($target, ['all', 'tx', 'drm'])) {
                $checkQueryDrm = "SELECT CONCAT(COUNT(*), '-', COALESCE(MAX(id_sbr_data), 0), '-', COALESCE(MAX(tglsetor), '')) as checksum FROM mpninfo.ppmpkm_drm";
                if (! empty($thnSetor)) {
                    $checkQueryDrm .= ' WHERE thnsetor = '.(int) $thnSetor;
                }

                if ($force || $this->hasTableChanged('ppmpkm_drm'.($thnSetor ? "_{$thnSetor}" : ''), $checkQueryDrm)) {
                    $this->syncDetilTransaksiWp($thnSetor, $blnSetor);
                    $anyTableSynced = true;
                } else {
                    $this->info('   [SKIP] Tabel ppmpkm_drm tidak ada perubahan.');
                }
            }

            // D. Sinkronisasi SPT Coretax (mpninfo.spt_coretax -> mpnweb.spt)
            if (in_array($target, ['all', 'tx', 'spt'])) {
                $checkQuerySpt = "SELECT CONCAT(COUNT(*), '-', COALESCE(MAX(tgl_terima), '')) as checksum FROM mpninfo.spt_coretax";
                if (! empty($thnSetor)) {
                    $checkQuerySpt .= ' WHERE tahun = '.(int) $thnSetor;
                }

                if ($force || $this->hasTableChanged('spt_coretax'.($thnSetor ? "_{$thnSetor}" : ''), $checkQuerySpt)) {
                    $this->syncSptCoretax($thnSetor, $blnSetor);
                    $anyTableSynced = true;
                } else {
                    $this->info('   [SKIP] Tabel spt_coretax tidak ada perubahan.');
                }
            }

            // 4. Commit Transaksi Utama
            $this->comment('-> Menyimpan perubahan ke database (Commit Transaction)...');
            $commitStart = microtime(true);

            DB::statement('COMMIT;');

            $commitTime = round(microtime(true) - $commitStart, 2);
            $this->info("   [OK] Transaction committed ({$commitTime}s).");

            // 5. Kembalikan Setting Constraint
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            DB::statement('SET UNIQUE_CHECKS = 1;');
            DB::statement('SET AUTOCOMMIT = 1;');

            // 6. Rebuild Summary Mart & Cache jika ada tabel yang di-sync
            if ($anyTableSynced) {
                if (in_array($target, ['all', 'tx', 'drm', 'spt'])) {
                    $this->newLine();
                    $this->comment('-> Memicu rekapitulasi Summary Mart, Penjagaan & PKM...');

                    $summaryOptions = [];
                    if ($thnSetor) {
                        $summaryOptions['--thnsetor'] = $thnSetor;
                    }
                    if ($blnSetor) {
                        $summaryOptions['--blnsetor'] = $blnSetor;
                    }

                    if (in_array($target, ['all', 'tx', 'drm'])) {
                        Artisan::call('summary:rebuild', $summaryOptions, $this->output);
                        $this->syncSummaryPenjagaan($thnSetor, $blnSetor);
                        $this->syncSummaryPkm($thnSetor, $blnSetor);
                    }
                }

                $this->invalidateDashboardCache($thnSetor, $blnSetor);
            } else {
                $this->newLine();
                $this->info('-> Seluruh data sudah yang terbaru. Tidak ada proses ETL yang dijalankan.');
            }

            $executionTime = round(microtime(true) - $startTime, 2);

            if ($useMaintenance) {
                Artisan::call('up');
                $this->comment('-> Mode Maintenance dinonaktifkan.');
            }

            $this->newLine();
            $this->info('====================================================');
            $this->info("  ETL SINKRONISASI SELESAI DALAM {$executionTime} DETIK!");
            $this->info('====================================================');

            return Command::SUCCESS;

        } catch (Exception $e) {
            DB::statement('ROLLBACK;');
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            DB::statement('SET UNIQUE_CHECKS = 1;');
            DB::statement('SET AUTOCOMMIT = 1;');

            // Bersihkan tabel temporary jika proses swapping mengalami error di tengah jalan
            DB::statement('DROP TABLE IF EXISTS mfwp_temp;');
            DB::statement('DROP TABLE IF EXISTS mfwp_old;');
            DB::statement('DROP TABLE IF EXISTS summary_penjagaan_temp;');
            DB::statement('DROP TABLE IF EXISTS summary_penjagaan_old;');
            DB::statement('DROP TABLE IF EXISTS summary_pkm_temp;');
            DB::statement('DROP TABLE IF EXISTS summary_pkm_old;');

            if ($useMaintenance) {
                Artisan::call('up');
            }

            $this->newLine();
            $this->error('ETL ERROR DETECTED: '.$e->getMessage());
            $this->error($e->getTraceAsString());

            return Command::FAILURE;
        }
    }

    /**
     * Checksum Tracker untuk mendeteksi perubahan data di mpninfo
     */
    private function hasTableChanged(string $tableName, ?string $customCheckQuery = null): bool
    {
        $query = $customCheckQuery ?? "SELECT CONCAT(COUNT(*), '-', COALESCE(MAX(id), 0)) as checksum FROM mpninfo.{$tableName}";

        try {
            $result = DB::selectOne($query);
            $currentChecksum = $result->checksum ?? '';

            $tracker = DB::table('sync_trackers')->where('table_name', $tableName)->first();

            if (! $tracker || $tracker->last_checksum !== $currentChecksum) {
                DB::table('sync_trackers')->updateOrInsert(
                    ['table_name' => $tableName],
                    [
                        'last_checksum' => $currentChecksum,
                        'last_synced_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                return true;
            }

            return false;
        } catch (Exception $e) {
            // Jika query check gagal, paksa tetap sync demi keamanan data
            return true;
        }
    }

    /**
     * Sumber: mpninfo.users -> mpnweb.users
     */
    private function syncUsers()
    {
        $this->comment('-> Synchronizing: users (Hashing Passwords via Bcrypt)...');
        $t0 = microtime(true);

        DB::statement('TRUNCATE TABLE users;');

        $batchSize = 200;
        $batchData = [];
        $total = 0;

        $query = DB::table('mpninfo.users')->orderBy('id');

        foreach ($query->cursor() as $r) {
            $rawPassword = (string) ($r->password ?? '');
            $isAlreadyHashed = str_starts_with($rawPassword, '$2y$') || str_starts_with($rawPassword, '$2a$');
            $hashedPassword = $isAlreadyHashed ? $rawPassword : Hash::make($rawPassword);

            $batchData[] = [
                'id' => $r->id,
                'username' => $r->username ?? '',
                'password' => $hashedPassword,
                'fullname' => $r->fullname ?? '',
                'group' => $r->group ?? 0,
            ];

            if (count($batchData) >= $batchSize) {
                DB::table('users')->insertOrIgnore($batchData);
                $total += count($batchData);
                $batchData = [];
            }
        }

        if (! empty($batchData)) {
            DB::table('users')->insertOrIgnore($batchData);
            $total += count($batchData);
        }

        $elapsed = round(microtime(true) - $t0, 2);
        $this->info("   [OK] Tabel users synchronized ({$elapsed}s) - Total {$total} users di-hash.");
    }

    /**
     * Sumber: mpninfo.seksi -> mpnweb.seksi
     */
    private function syncSeksi()
    {
        $this->comment('-> Synchronizing: seksi...');
        DB::statement('TRUNCATE TABLE seksi;');
        DB::statement('
            INSERT IGNORE INTO seksi (id, kantor, tipe, nama, kode, telp) 
            SELECT id, kantor, tipe, nama, kode, telp 
            FROM mpninfo.seksi
        ');
        $this->info('   [OK] Tabel seksi synchronized.');
    }

    /**
     * Sumber: mpninfo.klu_baru -> mpnweb.klu
     */
    private function syncKlu()
    {
        $this->comment('-> Synchronizing: klu...');
        DB::statement('TRUNCATE TABLE klu;');
        DB::statement('
            INSERT IGNORE INTO klu (kd_klu, nm_klu, kd_kategori, nm_kategori) 
            SELECT kd_klu, nm_klu, kd_kategori, nm_kategori 
            FROM mpninfo.klu_baru
        ');
        $this->info('   [OK] Tabel klu synchronized.');
    }

    /**
     * Sumber: mpninfo.map_baru -> mpnweb.map
     */
    private function syncKdmap()
    {
        $this->comment('-> Synchronizing: map...');
        DB::statement('TRUNCATE TABLE map;');
        DB::statement("
            INSERT IGNORE INTO map (id, kd_map, kd_bayar, jenis_pajak, jenis_bayar, sektor_pajak) 
            SELECT id, COALESCE(kd_map, ''), COALESCE(kd_bayar, ''), jenis_pajak, jenis_bayar, sektor_pajak 
            FROM mpninfo.map_baru
        ");
        $this->info('   [OK] Tabel map synchronized.');
    }

    /**
     * Sumber: mpninfo.pegawai -> mpnweb.pegawai
     */
    private function syncPegawai()
    {
        $this->comment('-> Synchronizing: pegawai...');
        DB::statement('TRUNCATE TABLE pegawai;');
        DB::statement('
            INSERT IGNORE INTO pegawai (kantor, nip, nip2, nama, pangkat, seksi, jabatan, tahun, plh) 
            SELECT kantor, nip, nip2, nama, pangkat, seksi, jabatan, tahun, plh 
            FROM mpninfo.pegawai
        ');
        $this->info('   [OK] Tabel pegawai synchronized.');
    }

    /**
     * Sumber: mpninfo.masterfile -> mpnweb.mfwp
     */
    private function syncMasterfileWpBatch()
    {
        $this->comment('-> Synchronizing: mfwp via Atomic Table Swapping (BATCH 2000)...');
        $t0 = microtime(true);

        DB::statement('DROP TABLE IF EXISTS mfwp_temp;');
        DB::statement('CREATE TABLE mfwp_temp LIKE mfwp;');

        $total = 0;
        $batchSize = 2000;
        $batchData = [];

        $query = DB::table('mpninfo.masterfile')->orderBy('npwp')->orderBy('kpp')->orderBy('cabang');

        foreach ($query->cursor() as $r) {
            $npwpRaw = trim((string) ($r->npwp ?? ''));
            $kppRaw = trim((string) ($r->kpp ?? ''));
            $cabangRaw = trim((string) ($r->cabang ?? ''));

            if ($npwpRaw === '' && $kppRaw === '' && $cabangRaw === '') {
                continue;
            }

            $npwp15 = str_pad($npwpRaw, 9, '0', STR_PAD_LEFT).str_pad($kppRaw, 3, '0', STR_PAD_LEFT).str_pad($cabangRaw, 3, '0', STR_PAD_LEFT);
            if ($npwp15 === '000000000000000') {
                continue;
            }

            $batchData[] = [
                'admin' => substr((string) ($r->admin ?? ''), 0, 10),
                'npwp' => substr($npwpRaw, 0, 20),
                'kpp' => substr($kppRaw, 0, 10),
                'cabang' => substr($cabangRaw, 0, 10),
                'npwp15' => $npwp15,
                'nama' => $r->nama ?? null,
                'alamat' => $r->alamat ?? null,
                'kelurahan' => substr((string) ($r->kelurahan ?? ''), 0, 50),
                'kecamatan' => substr((string) ($r->kecamatan ?? ''), 0, 100),
                'kota' => substr((string) ($r->kota ?? ''), 0, 100),
                'propinsi' => substr((string) ($r->propinsi ?? ''), 0, 100),
                'jenis' => substr((string) ($r->jenis ?? ''), 0, 50),
                'bentuk_hukum' => substr((string) ($r->bentukhukum ?? ''), 0, 50),
                'status' => substr((string) ($r->status ?? ''), 0, 50),
                'klu' => substr((string) ($r->klu ?? ''), 0, 10),
                'tanggal_daftar' => $r->tanggaldaftar ?? null,
                'tanggal_pkp' => $r->tanggalpkp ?? null,
                'tanggal_pkp_cabut' => $r->tanggalpkpcabut ?? null,
                'nik' => substr((string) ($r->nik ?? ''), 0, 30),
                'telp' => substr((string) ($r->telp ?? ''), 0, 50),
                'nip_ar' => substr((string) ($r->nipar ?? ''), 0, 30),
                'nip_eks' => substr((string) ($r->nipeks ?? ''), 0, 30),
                'nip_js' => substr((string) ($r->nipjs ?? ''), 0, 30),
                'npwp16' => $r->npwp16 ?? null,
            ];

            if (count($batchData) >= $batchSize) {
                DB::table('mfwp_temp')->insertOrIgnore($batchData);
                DB::statement('COMMIT;');
                DB::statement('SET AUTOCOMMIT = 0;');
                $total += count($batchData);
                $this->output->write("\r   [WAIT] Memproses {$total} rows ke mfwp_temp... ");
                $batchData = [];
            }
        }

        if (! empty($batchData)) {
            DB::table('mfwp_temp')->insertOrIgnore($batchData);
            DB::statement('COMMIT;');
            DB::statement('SET AUTOCOMMIT = 0;');
            $total += count($batchData);
        }

        DB::statement('RENAME TABLE mfwp TO mfwp_old, mfwp_temp TO mfwp;');
        DB::statement('DROP TABLE IF EXISTS mfwp_old;');

        $elapsed = round(microtime(true) - $t0, 2);
        $this->output->write("\r");
        $this->info("   [OK] Tabel mfwp synchronized via Swapping ({$elapsed}s) - Total {$total} rows.                   ");
    }

    /**
     * Sumber: mpninfo.ppmpkm_drm -> mpnweb.drm
     */
    private function syncDetilTransaksiWp($thnSetor = null, $blnSetor = null)
    {
        $this->comment('-> Synchronizing: drm...');

        $whereConditions = [];
        $deleteConditions = [];

        if (! empty($thnSetor)) {
            $whereConditions[] = 'thnsetor = '.(int) $thnSetor;
            $deleteConditions[] = 'thn_setor = '.(int) $thnSetor;
        }
        if (! empty($blnSetor)) {
            $whereConditions[] = 'blnsetor = '.(int) $blnSetor;
            $deleteConditions[] = 'bln_setor = '.(int) $blnSetor;
        }

        if (count($deleteConditions) > 0) {
            $deleteWhereSql = ' WHERE '.implode(' AND ', $deleteConditions);
            DB::statement("DELETE FROM drm{$deleteWhereSql};");
            $this->comment('   [i] Menghapus data periode terpilih sebelum re-sync.');
        } else {
            DB::statement('TRUNCATE TABLE drm;');
            $this->comment('   [i] Melakukan TRUNCATE pada drm.');
        }

        $this->output->write('   [WAIT] Memproses salinan data transaksi...');
        $t0 = microtime(true);

        $whereSql = count($whereConditions) > 0 ? ' WHERE '.implode(' AND ', $whereConditions) : '';

        DB::statement("
            INSERT INTO drm (
                kd_kanwil, kpp_adm, npwp, kpp, cabang, no_produk_hukum, npwp15, nama_wp, no_pbk, ntpn, 
                tgl_setor, thn_setor, bln_setor, thn_pajak, masa1, masa2, jml_setor, 
                kd_map, kd_bayar, fungsi, jenis, flag_skp, id_sbr_data, tipe
            )
            SELECT 
                kdkanwil, kppadm, npwp, kpp, cabang, no_produk_hukum,
                CONCAT(LPAD(TRIM(npwp), 9, '0'), LPAD(TRIM(kpp), 3, '0'), LPAD(TRIM(cabang), 3, '0')) AS npwp15,
                nama_wp, nopbk, ntpn, tglsetor, thnsetor, blnsetor,
                CAST(SUBSTRING(LPAD(TRIM(COALESCE(masapajak, '00000000')), 8, '0'), 5, 4) AS UNSIGNED) AS thn_pajak,
                CAST(SUBSTRING(LPAD(TRIM(COALESCE(masapajak, '00000000')), 8, '0'), 1, 2) AS UNSIGNED) AS masa1,
                CAST(SUBSTRING(LPAD(TRIM(COALESCE(masapajak, '00000000')), 8, '0'), 3, 2) AS UNSIGNED) AS masa2,
                COALESCE(jmlsetor, 0) AS jml_setor, 
                kdmap, kdbayar, fungsi, jenis, flag_skp, id_sbr_data, tipe
            FROM mpninfo.ppmpkm_drm
            {$whereSql}
        ");

        $elapsed = round(microtime(true) - $t0, 2);
        $this->output->write("\r");
        $this->info("   [OK] Tabel drm synchronized ({$elapsed}s).                   ");
    }

    /**
     * Sumber: mpninfo.spt_coretax -> mpnweb.spt
     */
    private function syncSptCoretax($thnSetor = null, $blnSetor = null)
    {
        $this->comment('-> Synchronizing: spt...');

        $whereConditions = [];
        $deleteConditions = [];

        if (! empty($thnSetor)) {
            $whereConditions[] = 'tahun = '.(int) $thnSetor;
            $deleteConditions[] = 'tahun = '.(int) $thnSetor;
        }
        if (! empty($blnSetor)) {
            $whereConditions[] = 'bulan = '.(int) $blnSetor;
            $deleteConditions[] = 'bulan = '.(int) $blnSetor;
        }

        if (count($deleteConditions) > 0) {
            $deleteWhereSql = ' WHERE '.implode(' AND ', $deleteConditions);
            DB::statement("DELETE FROM spt{$deleteWhereSql};");
            $this->comment('   [i] Menghapus data spt periode terpilih sebelum re-sync.');
        } else {
            DB::statement('TRUNCATE TABLE spt;');
            $this->comment('   [i] Melakukan TRUNCATE pada spt.');
        }

        $this->output->write('   [WAIT] Memproses salinan data spt...');
        $t0 = microtime(true);

        $whereSql = count($whereConditions) > 0 ? ' WHERE '.implode(' AND ', $whereConditions) : '';

        DB::statement("
            INSERT INTO spt (
                tahun, bulan, bulan_data, kanwil, kpp, npwp, nama, 
                masa1, masa2, thn_pajak, 
                jenis_spt, nomor_tanda_terima, tgl_terima, nop, 
                status_spt, pembetulan, kanal_pelaporan, 
                kd_kpp_administrasi, kpp_administrasi, kd_kpp_penerima, kpp_penerima
            )
            SELECT 
                tahun, bulan, bulan_data, kanwil, kpp, npwp, nama,
                CAST(SUBSTRING(LPAD(TRIM(COALESCE(masa_pajak, '00000000')), 8, '0'), 1, 2) AS UNSIGNED) AS masa1,
                CAST(SUBSTRING(LPAD(TRIM(COALESCE(masa_pajak, '00000000')), 8, '0'), 3, 2) AS UNSIGNED) AS masa2,
                CAST(SUBSTRING(LPAD(TRIM(COALESCE(masa_pajak, '00000000')), 8, '0'), 5, 4) AS UNSIGNED) AS thn_pajak,
                jenis_spt, nomor_tanda_terima, tgl_terima, nop, 
                status_spt, pembetulan, kanal_pelaporan, 
                kd_kpp_administrasi, kpp_administrasi, kd_kpp_penerima, kpp_penerima
            FROM mpninfo.spt_coretax
            {$whereSql}
        ");

        $elapsed = round(microtime(true) - $t0, 2);
        $this->output->write("\r");
        $this->info("   [OK] Tabel spt synchronized ({$elapsed}s).                   ");
    }

    /**
     * Sumber: mpnweb.drm -> mpnweb.summary_penjagaan
     */
    private function syncSummaryPenjagaan($thnSetor = null, $blnSetor = null)
    {
        $this->comment('-> Memicu rekapitulasi Summary Penjagaan...');
        $t0 = microtime(true);
        $now = now()->toDateTimeString();

        $whereConditions = [];
        if (! empty($thnSetor)) {
            $whereConditions[] = 'thn_setor = '.(int) $thnSetor;
        }
        if (! empty($blnSetor)) {
            $whereConditions[] = 'bln_setor = '.(int) $blnSetor;
        }

        if (count($whereConditions) > 0) {
            $whereSql = ' WHERE '.implode(' AND ', $whereConditions);

            DB::statement("DELETE FROM summary_penjagaan{$whereSql};");

            DB::statement("
                INSERT INTO summary_penjagaan (
                    thn_setor, bln_setor, tgl_setor, fungsi, total_setor, total_transaksi, created_at, updated_at
                )
                SELECT 
                    thn_setor,
                    bln_setor,
                    tgl_setor,
                    fungsi,
                    COALESCE(SUM(jml_setor), 0) as total_setor,
                    COUNT(id) as total_transaksi,
                    '{$now}' as created_at,
                    '{$now}' as updated_at
                FROM drm
                WHERE tgl_setor IS NOT NULL AND ".implode(' AND ', $whereConditions).'
                GROUP BY thn_setor, bln_setor, tgl_setor, fungsi
            ');

            $elapsed = round(microtime(true) - $t0, 2);
            $this->info("   [OK] Summary Penjagaan PARTIAL synchronized ({$elapsed}s).");
        } else {
            DB::statement('DROP TABLE IF EXISTS summary_penjagaan_temp;');
            DB::statement('CREATE TABLE summary_penjagaan_temp LIKE summary_penjagaan;');

            DB::statement("
                INSERT INTO summary_penjagaan_temp (
                    thn_setor, bln_setor, tgl_setor, fungsi, total_setor, total_transaksi, created_at, updated_at
                )
                SELECT 
                    thn_setor,
                    bln_setor,
                    tgl_setor,
                    fungsi,
                    COALESCE(SUM(jml_setor), 0) as total_setor,
                    COUNT(id) as total_transaksi,
                    '{$now}' as created_at,
                    '{$now}' as updated_at
                FROM drm
                WHERE tgl_setor IS NOT NULL
                GROUP BY thn_setor, bln_setor, tgl_setor, fungsi
            ");

            DB::statement('DROP TABLE IF EXISTS summary_penjagaan_old;');
            DB::statement('RENAME TABLE summary_penjagaan TO summary_penjagaan_old, summary_penjagaan_temp TO summary_penjagaan;');
            DB::statement('DROP TABLE IF EXISTS summary_penjagaan_old;');

            $elapsed = round(microtime(true) - $t0, 2);
            $this->info("   [OK] Summary Penjagaan FULL synchronized via Swapping ({$elapsed}s).");
        }
    }

    /**
     * Sumber: mpnweb.drm -> mpnweb.summary_pkm
     */
    private function syncSummaryPkm($thnSetor = null, $blnSetor = null)
    {
        $this->comment('-> Memicu rekapitulasi Summary PKM Mart...');
        $t0 = microtime(true);
        $now = now()->toDateTimeString();

        $whereConditions = [];
        if (! empty($thnSetor)) {
            $whereConditions[] = 'thn_setor = '.(int) $thnSetor;
        }
        if (! empty($blnSetor)) {
            $whereConditions[] = 'bln_setor = '.(int) $blnSetor;
        }

        if (count($whereConditions) > 0) {
            $whereSql = ' WHERE '.implode(' AND ', $whereConditions);

            DB::statement("DELETE FROM summary_pkm{$whereSql};");

            DB::statement("
                INSERT INTO summary_pkm (
                    thn_setor, bln_setor, npwp15, fungsi, flag_skp, total_setor, total_transaksi, created_at, updated_at
                )
                SELECT 
                    thn_setor,
                    bln_setor,
                    npwp15,
                    fungsi,
                    flag_skp,
                    COALESCE(SUM(jml_setor), 0) as total_setor,
                    COUNT(id) as total_transaksi,
                    '{$now}' as created_at,
                    '{$now}' as updated_at
                FROM drm
                WHERE thn_setor IS NOT NULL AND bln_setor IS NOT NULL AND ".implode(' AND ', $whereConditions).'
                GROUP BY thn_setor, bln_setor, npwp15, fungsi, flag_skp
            ');

            $elapsed = round(microtime(true) - $t0, 2);
            $this->info("   [OK] Summary PKM PARTIAL synchronized ({$elapsed}s).");
        } else {
            DB::statement('DROP TABLE IF EXISTS summary_pkm_temp;');
            DB::statement('CREATE TABLE summary_pkm_temp LIKE summary_pkm;');

            DB::statement("
                INSERT INTO summary_pkm_temp (
                    thn_setor, bln_setor, npwp15, fungsi, flag_skp, total_setor, total_transaksi, created_at, updated_at
                )
                SELECT 
                    thn_setor,
                    bln_setor,
                    npwp15,
                    fungsi,
                    flag_skp,
                    COALESCE(SUM(jml_setor), 0) as total_setor,
                    COUNT(id) as total_transaksi,
                    '{$now}' as created_at,
                    '{$now}' as updated_at
                FROM drm
                WHERE thn_setor IS NOT NULL AND bln_setor IS NOT NULL
                GROUP BY thn_setor, bln_setor, npwp15, fungsi, flag_skp
            ");

            DB::statement('DROP TABLE IF EXISTS summary_pkm_old;');
            DB::statement('RENAME TABLE summary_pkm TO summary_pkm_old, summary_pkm_temp TO summary_pkm;');
            DB::statement('DROP TABLE IF EXISTS summary_pkm_old;');

            $elapsed = round(microtime(true) - $t0, 2);
            $this->info("   [OK] Summary PKM FULL synchronized via Swapping ({$elapsed}s).");
        }
    }

    /**
     * Invalidation Cache terarah berdasarkan target ETL dan parameter periode.
     */
    private function invalidateDashboardCache(?string $thnSetor = null, ?string $blnSetor = null)
    {
        $this->newLine();
        $this->comment('-> Membersihkan Cache terkait hasil sinkronisasi...');

        $prefix = config('cache.prefix', '');
        $target = $this->option('only');

        if (in_array($target, ['all', 'ref', 'master'])) {
            Cache::forget('penjagaan_fungsi_options');
            Cache::forget('daftar_seksi_pengawasan_v8');
            Cache::forget('mf_filter_klu');
            Cache::forget('mf_filter_kelurahan');
            Cache::forget('mf_filter_kecamatan');
            Cache::forget('mf_filter_jenis');
            Cache::forget('mf_filter_status');

            DB::table('cache')->where('key', 'LIKE', "{$prefix}filter_ar_%")->delete();
            DB::table('cache')->where('key', 'LIKE', "{$prefix}filter_js_%")->delete();
            DB::table('cache')->where('key', 'LIKE', "{$prefix}transaksi_filter_options_%")->delete();
        }

        if (in_array($target, ['all', 'spt', 'tx'])) {
            Cache::forget('distinct_jenis_spt');
            Cache::forget('distinct_status_spt');
            Cache::forget('distinct_pembetulan_spt');
            DB::table('cache')->where('key', 'LIKE', "{$prefix}distinct_ar_%")->delete();
        }

        if (in_array($target, ['all', 'tx', 'drm', 'spt'])) {
            if (! empty($thnSetor)) {
                $patternDash = "{$prefix}dashboard_summary_v4_{$thnSetor}_%";
                $deletedDash = DB::table('cache')->where('key', 'LIKE', $patternDash)->delete();

                DB::table('cache')->where('key', 'LIKE', "{$prefix}penjagaan_%")->delete();
                DB::table('cache')->where('key', 'LIKE', "{$prefix}pkm_%")->delete();

                Cache::forget("dashboard_target_{$thnSetor}");

                $this->info("   [OK] Cache dashboard, penjagaan & PKM tahun {$thnSetor} berhasil dibersihkan ({$deletedDash} keys).");
            } else {
                $patternDash = "{$prefix}dashboard_summary_v4_%";
                $deletedDash = DB::table('cache')->where('key', 'LIKE', $patternDash)->delete();

                DB::table('cache')->where('key', 'LIKE', "{$prefix}penjagaan_%")->delete();
                DB::table('cache')->where('key', 'LIKE', "{$prefix}pkm_%")->delete();
                DB::table('cache')->where('key', 'LIKE', "{$prefix}dashboard_target_%")->delete();

                $this->info("   [OK] Seluruh cache dashboard, penjagaan & PKM berhasil dibersihkan ({$deletedDash} keys).");
            }
        }
    }
}
