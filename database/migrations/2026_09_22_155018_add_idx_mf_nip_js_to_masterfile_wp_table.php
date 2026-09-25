<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('masterfile_wp', function (Blueprint $table) {
            // Menambahkan index idx_mf_nip_js pada kolom nip_js
            $table->index('nip_js', 'idx_mf_nip_js');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('masterfile_wp', function (Blueprint $table) {
            // Menghapus index jika migration di-rollback
            $table->dropIndex('idx_mf_nip_js');
        });
    }
};
