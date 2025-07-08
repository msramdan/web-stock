<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BomKemasan extends Model
{
    use HasFactory;

    protected $table = 'bom_kemasan';

    protected $fillable = [
        'bom_id',
        'barang_id',
        'kapasitas',
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
            'kapasitas' => 'integer', // Casting ke integer
        ];
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function unitSatuan(): BelongsTo
    {
        return $this->belongsTo(UnitSatuan::class, 'unit_satuan_id');
    }
}
