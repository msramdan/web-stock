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
        Schema::create('produksi_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produksi_id')->constrained('produksi')->cascadeOnDelete();
            $table->foreignId('barang_id')->comment('ID Barang Jadi (In) / Material (Out)')->constrained('barang')->restrictOnDelete();
            $table->foreignId('unit_satuan_id')->comment('ID Unit Satuan')->constrained('unit_satuan')->restrictOnDelete(); // Satuan barang_id
            $table->enum('type', ['In', 'Out'])->comment('In: Produk Jadi, Out: Material');
            $table->decimal('qty_rate', 15, 4)->comment('Qty per unit: 1 (In) atau dari BoM (Out)'); // Contoh: 1 (In), 0.2 (Out)
            $table->decimal('qty_target_produksi', 15, 4)->comment('Qty target dari header produksi'); // Contoh: 100
            $table->decimal('qty_total_diperlukan', 15, 4)->comment('Hasil kalkulasi: qty_rate * qty_target_produksi'); // Contoh: 100 (In), 20 (Out)
            $table->timestamps(); // Opsional, bisa dihapus jika tidak perlu

            $table->index('produksi_id');
            $table->index('barang_id');
            $table->index('unit_satuan_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produksi_details');
    }
};
