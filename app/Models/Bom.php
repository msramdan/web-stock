<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    // Tambahkan 'company_id'
    protected $fillable = ['company_id', 'barang_id', 'deskripsi'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deskripsi' => 'string',
            'created_at' => 'datetime:Y-m-d H:i:s',
            'updated_at' => 'datetime:Y-m-d H:i:s'
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
     * Relasi ke Barang (Produk Jadi).
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    /**
     * Relasi ke Detail BoM (Material/Komponen).
     */
    public function details(): HasMany
    {
        return $this->hasMany(BomDetail::class, 'bom_id');
    }

    /**
     * Relasi ke Kemasan BoM (Opsional).
     */
    public function kemasan(): HasMany
    {
        return $this->hasMany(BomKemasan::class, 'bom_id');
    }
}
