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
        Schema::create('unit_satuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('company')->onDelete('set null');
            $table->string('nama_unit_satuan', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_satuan');
    }
};
