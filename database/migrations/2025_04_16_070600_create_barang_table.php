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
        Schema::create('barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('company')->onDelete('set null');
            $table->string('kode_barang', 255);
            $table->string('nama_barang', 255);
            $table->text('deskripsi_barang');
            $table->foreignId('jenis_material_id')->constrained('jenis_material')->restrictOnUpdate()->restrictOnDelete();
            $table->foreignId('unit_satuan_id')->constrained('unit_satuan')->restrictOnUpdate()->restrictOnDelete();
            $table->float('stock_barang')->nullable();
            $table->string('photo_barang')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
