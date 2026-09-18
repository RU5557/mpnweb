<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rolling_texts', function (Blueprint $table) {$table->dropColumn('pesan_tambahan');
        });
    }

    public function down(): void
    {
        Schema::table('rolling_texts', function (Blueprint $table) {$table->text('pesan_tambahan')->nullable()->after('ranking_kanwil');
        });
    }
};