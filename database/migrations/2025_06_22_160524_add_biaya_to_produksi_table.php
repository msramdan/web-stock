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
            // Menambahkan kolom untuk total biaya setelah kolom bom_id
            $table->decimal('total_biaya', 20, 2)->default(0)->after('bom_id');

            // Menambahkan kolom untuk harga satuan/HPP setelah total_biaya
            $table->decimal('harga_satuan_jadi', 20, 2)->default(0)->after('total_biaya');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produksi', function (Blueprint $table) {
            $table->dropColumn('total_biaya');
            $table->dropColumn('harga_satuan_jadi');
        });
    }
};
