<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Tambahkan import BelongsTo
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- Tambahkan import HasMany

class JenisMaterial extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'jenis_material';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    // Tambahkan 'company_id'
    protected $fillable = ['company_id', 'nama_jenis_material'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nama_jenis_material' => 'string',
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
     * Relasi ke Barang (jika diperlukan, JenisMaterial bisa punya banyak Barang) <-- Tambahkan (opsional, tapi baik)
     */
    public function barangs(): HasMany
    {
        return $this->hasMany(Barang::class, 'jenis_material_id');
    }
}
