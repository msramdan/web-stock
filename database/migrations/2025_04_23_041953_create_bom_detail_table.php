<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBomDetailTable extends Migration
{
    public function up(): void
    {
        Schema::create('bom_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_id')->constrained('bom')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('barang')->onDelete('restrict');
            $table->float('jumlah');
            $table->foreignId('unit_satuan_id')->constrained('unit_satuan')->onDelete('restrict');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_detail');
    }
}
