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
        Schema::table('barang', function (Blueprint $table) {
            // Tambahkan kolom kapasitas setelah harga
            $table->integer('kapasitas')->nullable()->after('harga')->comment('Kapasitas untuk barang tipe kemasan');
        });

        // Ubah tipe kolom enum untuk menambahkan 'Kemasan'
        // Perintah RAW diperlukan karena Doctrine DBAL tidak mendukung CHANGE ENUM
        DB::statement("ALTER TABLE barang CHANGE tipe_barang tipe_barang ENUM('Bahan Baku', 'Barang Jadi', 'Kemasan')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn('kapasitas');
        });

        // Kembalikan ke state semula jika di-rollback
        DB::statement("ALTER TABLE barang CHANGE tipe_barang tipe_barang ENUM('Bahan Baku', 'Barang Jadi')");
    }
};
