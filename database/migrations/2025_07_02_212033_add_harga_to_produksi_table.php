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
        Schema::table('produksi', function (Blueprint $table) {
            $table->decimal('harga_perunit', 15, 2)->nullable()->after('keterangan');
            $table->decimal('total_biaya', 15, 2)->nullable()->after('harga_perunit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produksi', function (Blueprint $table) {
            $table->dropColumn('harga_perunit');
            $table->dropColumn('total_biaya');
        });
    }
};
