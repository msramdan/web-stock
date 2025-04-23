<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo
use Illuminate\Database\Eloquent\Relations\HasMany; // Import HasMany

class Bom extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bom';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['barang_id', 'deskripsi'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['deskripsi' => 'string', 'created_at' => 'datetime:Y-m-d H:i:s', 'updated_at' => 'datetime:Y-m-d H:i:s'];
    }


    /**
     * Relasi ke Barang (Produk Jadi).
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id'); // Pastikan foreign key 'barang_id'
    }

    /**
     * Relasi ke Detail BoM (Material/Komponen). << DITAMBAHKAN
     */
    public function details(): HasMany
    {
        // Relasi ke model BomDetail, menggunakan foreign key 'bom_id' di tabel bom_detail
        return $this->hasMany(BomDetail::class, 'bom_id');
    }
}
