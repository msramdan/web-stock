<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- Tambahkan import HasMany

class Company extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'company';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['nama_perusahaan', 'no_telepon', 'email', 'alamat', 'logo_perusahaan'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nama_perusahaan' => 'string',
            'no_telepon' => 'string',
            'email' => 'string',
            'alamat' => 'string',
            'logo_perusahaan' => 'string',
            'created_at' => 'datetime:Y-m-d H:i:s',
            'updated_at' => 'datetime:Y-m-d H:i:s'
        ];
    }

    // --- Tambahkan Relasi Inverse ---

    /**
     * Relasi ke Jenis Material.
     */
    public function jenisMaterials(): HasMany // <-- Nama jamak (plural)
    {
        return $this->hasMany(JenisMaterial::class, 'company_id');
    }

    /**
     * Relasi ke Unit Satuan.
     */
    public function unitSatuans(): HasMany // <-- Nama jamak (plural)
    {
        return $this->hasMany(UnitSatuan::class, 'company_id');
    }

    /**
     * Relasi ke Barang.
     */
    public function barangs(): HasMany // <-- Nama jamak (plural)
    {
        return $this->hasMany(Barang::class, 'company_id');
    }

    /**
     * Relasi ke Transaksi.
     */
    public function transaksis(): HasMany // <-- Nama jamak (plural)
    {
        return $this->hasMany(Transaksi::class, 'company_id');
    }

    /**
     * Relasi ke BOM.
     */
    public function boms(): HasMany // <-- Nama jamak (plural)
    {
        return $this->hasMany(Bom::class, 'company_id');
    }
}
