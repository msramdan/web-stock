<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPermintaan extends Model
{
    use HasFactory;

    protected $table = 'detail_permintaan';

    protected $fillable = [
        'permintaan_id',
        'barang_id',
        'stok_terakhir',
        'jumlah_pesanan',
        'satuan',
        'harga_per_satuan',
        'total_harga',
    ];

    protected $casts = [
        'stok_terakhir' => 'decimal:2',
        'jumlah_pesanan' => 'decimal:2',
        'harga_per_satuan' => 'decimal:2',
        'total_harga' => 'decimal:2',
    ];

    public function permintaan()
    {
        return $this->belongsTo(Permintaan::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    // Jika 'satuan' ingin direlasikan ke model UnitSatuan (meskipun disimpan sebagai string)
    // public function unitSatuan()
    // {
    //     // Ini memerlukan logika tambahan jika satuan adalah string tapi ingin dicocokkan
    //     // Atau jika Anda mengubah field 'satuan' menjadi foreign key ke 'unit_satuans'
    // }

    /**
     * Accessor untuk mencoba mendapatkan objek UnitSatuan berdasarkan nama_unit_satuan
     * yang cocok dengan nilai string di kolom 'satuan'.
     *
     * @return UnitSatuan|null
     */
    public function getMatchedUnitSatuanAttribute()
    {
        if (empty($this->satuan)) {
            return null;
        }
        // Ambil company_id dari relasi permintaan, lalu ke relasi company di permintaan
        $companyId = $this->permintaan->company_id ?? null;

        return UnitSatuan::where('nama_unit_satuan', $this->satuan)
            ->when($companyId, function ($query, $companyId) {
                // Asumsikan UnitSatuan juga memiliki company_id jika bersifat company-specific
                return $query->where('company_id', $companyId);
            })
            ->first();
    }
}
