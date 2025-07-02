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
        Schema::create('bom_kemasan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_id')->constrained('bom')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('barang');
            $table->decimal('jumlah', 15, 4);
            $table->foreignId('unit_satuan_id')->constrained('unit_satuan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_kemasan');
    }
};
