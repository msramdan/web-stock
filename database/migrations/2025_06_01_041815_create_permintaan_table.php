<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permintaan', function (Blueprint $table) {
            $table->id();
            $table->dateTime('tgl_pengajuan');
            $table->string('no_permintaan_barang', 50)->unique();
            $table->string('nama_supplier', 150);
            $table->string('nama_bank', 100)->nullable();
            $table->string('account_name_supplier', 150)->nullable();
            $table->string('account_number_supplier', 25)->nullable();
            $table->text('keterangan')->nullable();
            $table->enum('include_ppn', ['yes', 'no'])->default('no');
            $table->decimal('nominal_ppn', 15, 2)->default(0);
            $table->decimal('sub_total_pesanan', 15, 2);
            $table->decimal('total_pesanan', 15, 2);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('company')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permintaan');
    }
};
