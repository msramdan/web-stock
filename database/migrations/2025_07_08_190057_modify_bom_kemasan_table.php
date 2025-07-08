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
        Schema::table('bom_kemasan', function (Blueprint $table) {
            // Hapus kolom jumlah karena sudah tidak relevan
            $table->dropColumn('jumlah');
            $table->integer('kapasitas')->nullable()->after('barang_id')->comment('Kapasitas kemasan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bom_kemasan', function (Blueprint $table) {
            // Jika perlu rollback, tambahkan kembali kolomnya
            $table->decimal('jumlah', 15, 4)->after('barang_id');
            $table->dropColumn('kapasitas');
        });
    }
};
