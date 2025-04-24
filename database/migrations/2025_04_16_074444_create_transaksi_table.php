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
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('company')->onDelete('set null');
            $table->string('no_surat', 255);
            $table->dateTime('tanggal');
            $table->enum('type', ['In', 'Out']);
            $table->text('keterangan')->nullable();
            $table->string('attachment')->nullable();
            $table->foreignId('user_id')->constrained('users')->restrictOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
