<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permintaan extends Model
{
    use HasFactory;

    protected $table = 'permintaan';

    protected $fillable = [
        'tgl_pengajuan',
        'no_permintaan_barang',
        'nama_supplier',
        'nama_bank',
        'account_name_supplier',
        'account_number_supplier',
        'keterangan',
        'include_ppn',
        'nominal_ppn',
        'sub_total_pesanan',
        'total_pesanan',
        'user_id',
        'company_id',
    ];

    protected $casts = [
        'tgl_pengajuan' => 'datetime',
        'include_ppn' => 'string', // atau boolean jika diubah di migrasi
        'nominal_ppn' => 'decimal:2',
        'sub_total_pesanan' => 'decimal:2',
        'total_pesanan' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function details()
    {
        return $this->hasMany(DetailPermintaan::class);
    }
}
