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
        Schema::create('produksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('company')->cascadeOnDelete();
            $table->string('no_produksi');
            $table->integer('batch')->default(1);
            $table->dateTime('tanggal');
            $table->foreignId('barang_id')->comment('ID Produk Jadi')->constrained('barang')->restrictOnDelete();
            $table->foreignId('bom_id')->comment('ID BoM yg digunakan')->constrained('bom')->restrictOnDelete();
            $table->decimal('qty_target', 15, 4)->comment('Jumlah produk jadi yg ditargetkan');
            $table->string('attachment')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('barang_id');
            $table->index('bom_id');
            $table->unique(['company_id', 'no_produksi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produksi');
    }
};
