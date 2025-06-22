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
        Schema::table('transaksi_detail', function (Blueprint $table) {
            // Harga satuan barang pada saat transaksi masuk
            $table->decimal('harga_satuan', 20, 2)->default(0)->after('qty');
            // Untuk melacak sisa qty dari batch ini yang bisa digunakan
            $table->decimal('sisa_qty', 20, 2)->default(0)->after('harga_satuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_detail', function (Blueprint $table) {
            $table->dropColumn('harga_satuan');
            $table->dropColumn('sisa_qty');
        });
    }
};
