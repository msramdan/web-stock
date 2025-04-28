<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProduksiDetail extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika berbeda dari penamaan standar Laravel (opsional)
    protected $table = 'produksi_details';

    // Kolom yang bisa diisi massal
    protected $fillable = [
        'produksi_id',
        'barang_id',
        'unit_satuan_id',
        'type',
        'qty_rate',
        'qty_target_produksi',
        'qty_total_diperlukan',
        // 'qty_digunakan' // Jika Anda tambahkan kolom ini
    ];

    // Tipe casting untuk kolom desimal
    protected $casts = [
        'qty_rate' => 'decimal:4',
        'qty_target_produksi' => 'decimal:4',
        'qty_total_diperlukan' => 'decimal:4',
        // 'qty_digunakan' => 'decimal:4', // Jika Anda tambahkan
    ];

    // Menonaktifkan timestamps jika tidak didefinisikan di migrasi
    // public $timestamps = false; // Aktifkan jika kolom timestamps tidak ada

    // --- DEFINISI RELASI ---

    /**
     * Relasi ke Produksi (Header).
     */
    public function produksi(): BelongsTo
    {
        return $this->belongsTo(Produksi::class);
    }

    /**
     * Relasi ke Barang (Material atau Produk Jadi).
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    /**
     * Relasi ke Unit Satuan.
     */
    public function unitSatuan(): BelongsTo
    {
        return $this->belongsTo(UnitSatuan::class);
    }
}
