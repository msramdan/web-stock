<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produksi extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika berbeda dari penamaan standar Laravel (opsional)
    protected $table = 'produksi';

    // Kolom yang bisa diisi massal (mass assignable)
    protected $fillable = [
        'company_id',
        'no_produksi',
        'batch',
        'tanggal',
        'barang_id', // ID Produk Jadi
        'bom_id',    // ID BoM yang digunakan
        'qty_target', // Target Produksi Produk Jadi
        'attachment',
        'keterangan',
        // 'status', // Jika Anda tambahkan kembali kolom status
        // 'user_id' // Jika Anda tambahkan kembali kolom user
    ];

    // Tipe casting untuk kolom tertentu
    protected $casts = [
        'tanggal' => 'datetime',
        'qty_target' => 'decimal:4', // Sesuaikan presisi jika perlu
        'batch' => 'integer',
    ];

    // --- DEFINISI RELASI ---

    /**
     * Relasi ke Company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relasi ke Barang (Produk Jadi yang diproduksi).
     */
    public function produkJadi(): BelongsTo
    {
        // Nama relasi 'produkJadi' agar tidak bentrok jika ada relasi lain ke 'barang'
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    /**
     * Relasi ke BoM yang digunakan.
     */
    public function bom(): BelongsTo
    {
        return $this->belongsTo(Bom::class);
    }

    /**
     * Relasi ke User (Pembuat/Penanggung Jawab).
     * Uncomment jika Anda menambahkan kembali kolom user_id.
     */
    // public function user(): BelongsTo
    // {
    //     return $this->belongsTo(User::class);
    // }

    /**
     * Relasi ke Detail Produksi.
     */
    public function details(): HasMany
    {
        return $this->hasMany(ProduksiDetail::class);
    }

    /**
     * Relasi untuk mengambil detail bahan baku saja (type='Out').
     */
    public function materialDetails(): HasMany
    {
        return $this->hasMany(ProduksiDetail::class)->where('type', 'Out');
    }

    /**
     * Relasi untuk mengambil detail produk jadi saja (type='In').
     */
    public function finishedGoodDetail(): HasMany // Seharusnya hanya satu, tapi HasMany lebih fleksibel
    {
        return $this->hasMany(ProduksiDetail::class)->where('type', 'In');
    }
}
