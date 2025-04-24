<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Pastikan ini di-import

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
    protected $fillable = ['kode_barang', 'nama_barang', 'deskripsi_barang', 'jenis_material_id', 'unit_satuan_id', 'stock_barang', 'photo_barang'];

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
            'deskripsi_barang' => 'string',
            'stock_barang' => 'float',
            'photo_barang' => 'string',
            'created_at' => 'datetime:Y-m-d H:i:s',
            'updated_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    /**
     * Relasi ke JenisMaterial.
     */
    public function jenisMaterial(): BelongsTo // Direkomendasikan menggunakan camelCase juga
    {
        // Pastikan foreign key 'jenis_material_id' sudah benar
        return $this->belongsTo(JenisMaterial::class, 'jenis_material_id');
    }

    /**
     * Relasi ke UnitSatuan.
     * Nama method diubah ke camelCase: unit_satuan -> unitSatuan
     */
    public function unitSatuan(): BelongsTo
    {
        // Pastikan foreign key 'unit_satuan_id' sudah benar
        return $this->belongsTo(UnitSatuan::class, 'unit_satuan_id');
    }
}
