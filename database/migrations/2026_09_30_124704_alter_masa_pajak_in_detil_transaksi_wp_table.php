<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom masa1 dan masa2 setelah kolom thn_pajak
        Schema::table('detil_transaksi_wp', function (Blueprint $table) {
            $table->string('masa1', 2)->nullable()->after('thn_pajak');
            $table->string('masa2', 2)->nullable()->after('masa1');
        });

        // 2. Pindahkan data dari masa_pajak ke masa1 & masa2
        //    - SUBSTRING(masa_pajak, 1, 2) mengambil 2 digit pertama
        //    - SUBSTRING(masa_pajak, 3, 2) mengambil 2 digit tengah (karakter ke-3 & 4)
        //    - 4 digit terakhir tidak diambil (otomatis terbuang)
        DB::statement('
            UPDATE detil_transaksi_wp 
            SET 
                masa1 = SUBSTRING(masa_pajak, 1, 2),
                masa2 = SUBSTRING(masa_pajak, 3, 2)
            WHERE masa_pajak IS NOT NULL AND CHAR_LENGTH(masa_pajak) >= 4
        ');

        // 3. Hapus kolom lama masa_pajak
        Schema::table('detil_transaksi_wp', function (Blueprint $table) {
            $table->dropColumn('masa_pajak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Buat kembali kolom masa_pajak jika di-rollback
        Schema::table('detil_transaksi_wp', function (Blueprint $table) {
            $table->string('masa_pajak', 10)->nullable()->after('thn_pajak');
        });

        // 2. Gabungkan kembali masa1, masa2, dan thn_pajak ke masa_pajak
        DB::statement("
            UPDATE detil_transaksi_wp 
            SET masa_pajak = CONCAT(
                COALESCE(masa1, ''), 
                COALESCE(masa2, ''), 
                COALESCE(CAST(thn_pajak AS CHAR), '')
            )
            WHERE masa1 IS NOT NULL OR masa2 IS NOT NULL
        ");

        // 3. Hapus kolom masa1 dan masa2
        Schema::table('detil_transaksi_wp', function (Blueprint $table) {
            $table->dropColumn(['masa1', 'masa2']);
        });
    }
};
