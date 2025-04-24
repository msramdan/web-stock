<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BomDetail extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'bom_detail'; // Sesuaikan jika nama tabel berbeda

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'bom_id',
        'barang_id', // Ini adalah ID material/komponen
        'jumlah',
        'unit_satuan_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jumlah' => 'float', // Casting ke float sesuai migrasi
        ];
    }

    /**
     * Relasi ke model Bom (induk).
     */
    public function bom(): BelongsTo
    {
        return $this->belongsTo(Bom::class, 'bom_id');
    }

    /**
     * Relasi ke model Barang (material/komponen).
     */
    public function material(): BelongsTo
    {
        // Relasi ke Barang, menggunakan foreign key 'barang_id' di tabel bom_detail
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    /**
     * Relasi ke model UnitSatuan (unit dari material/komponen).
     */
    public function unitSatuan(): BelongsTo
    {
        return $this->belongsTo(UnitSatuan::class, 'unit_satuan_id');
    }
}
