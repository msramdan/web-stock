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
        // Hapus kolom qty_target dari tabel produksi
        Schema::table('produksi', function (Blueprint $table) {
            if (Schema::hasColumn('produksi', 'qty_target')) {
                $table->dropColumn('qty_target');
            }
        });

        // Hapus kolom qty_target_produksi dari tabel produksi_details
        // dan ubah komentar untuk kolom lain
        Schema::table('produksi_details', function (Blueprint $table) {
            if (Schema::hasColumn('produksi_details', 'qty_target_produksi')) {
                $table->dropColumn('qty_target_produksi');
            }
            // Ubah komentar (opsional tapi bagus)
            $table->decimal('qty_rate', 15, 4)->comment('Qty per batch: 1 (In) atau dari BoM (Out)')->change();
            $table->decimal('qty_total_diperlukan', 15, 4)->comment('Hasil kalkulasi: qty_rate * batch')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tambahkan kembali kolom jika perlu rollback
        Schema::table('produksi', function (Blueprint $table) {
            if (!Schema::hasColumn('produksi', 'qty_target')) {
                $table->decimal('qty_target', 15, 4)->after('bom_id')->comment('Jumlah produk jadi yg ditargetkan (Dihapus)');
            }
        });

        Schema::table('produksi_details', function (Blueprint $table) {
            if (!Schema::hasColumn('produksi_details', 'qty_target_produksi')) {
                $table->decimal('qty_target_produksi', 15, 4)->after('qty_rate')->comment('Qty target dari header produksi (Dihapus)');
            }
            // Kembalikan komentar (opsional)
            $table->decimal('qty_rate', 15, 4)->comment('Qty per unit: 1 (In) atau dari BoM (Out)')->change();
            $table->decimal('qty_total_diperlukan', 15, 4)->comment('Hasil kalkulasi: qty_rate * qty_target_produksi')->change();
        });
    }
};
