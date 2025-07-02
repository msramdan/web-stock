<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- Tambahkan import HasMany

class Barang extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'barang';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    // Tambahkan 'company_id'
    protected $fillable = [
        'company_id',
        'kode_barang',
        'nama_barang',
        'deskripsi_barang',
        'jenis_material_id',
        'unit_satuan_id',
        'tipe_barang',
        'stock_barang',
        'photo_barang',
        'harga'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kode_barang' => 'string',
            'nama_barang' => 'string',
            'tipe_barang' => 'string',
            'deskripsi_barang' => 'string',
            'stock_barang' => 'float',
            'photo_barang' => 'string',
            'created_at' => 'datetime:Y-m-d H:i:s',
            'updated_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    /**
     * Relasi ke Company. <-- Tambahkan
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Relasi ke JenisMaterial.
     */
    public function jenisMaterial(): BelongsTo
    {
        return $this->belongsTo(JenisMaterial::class, 'jenis_material_id');
    }

    /**
     * Relasi ke UnitSatuan.
     */
    public function unitSatuan(): BelongsTo
    {
        return $this->belongsTo(UnitSatuan::class, 'unit_satuan_id');
    }

    /**
     * Relasi ke TransaksiDetail (Barang bisa ada di banyak detail transaksi) <-- Tambahkan (opsional, tapi baik)
     */
    public function transaksiDetails(): HasMany
    {
        return $this->hasMany(TransaksiDetail::class, 'barang_id');
    }

    /**
     * Relasi ke Bom (jika Barang ini adalah produk jadi) <-- Tambahkan (opsional, tapi baik)
     */
    public function boms(): HasMany // Satu barang bisa jadi produk jadi untuk beberapa BoM (jarang, tapi mungkin)
    {
        return $this->hasMany(Bom::class, 'barang_id');
    }

    /**
     * Relasi ke BomDetail (jika Barang ini adalah material/komponen) <-- Tambahkan (opsional, tapi baik)
     */
    public function bomDetails(): HasMany // Satu barang bisa jadi komponen di banyak BoM Detail
    {
        return $this->hasMany(BomDetail::class, 'barang_id');
    }

    public function permintaanDetails(): HasMany
    {
        return $this->hasMany(DetailPermintaan::class, 'barang_id');
    }
}
