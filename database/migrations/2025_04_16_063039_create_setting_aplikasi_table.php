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
        Schema::create('setting_aplikasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_aplikasi', 255);
			$table->string('nama_perusahaan', 255);
			$table->string('no_telepon', 15);
			$table->string('email', 100);
			$table->text('alamat');
			$table->string('logo_perusahaan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting_aplikasi');
    }
};
