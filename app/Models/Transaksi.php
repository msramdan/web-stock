<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Transaksi extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transaksi';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    // Tambahkan 'company_id'
    protected $fillable = [
        'company_id',
        'no_surat',
        'tanggal',
        'type',
        'keterangan',
        'attachment',
        'user_id'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'no_surat' => 'string',
            'tanggal' => 'datetime:Y-m-d H:i:s',
            'type' => 'string', // Enum sebaiknya di-cast ke string
            'keterangan' => 'string',
            'attachment' => 'string',
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
     * Relasi ke User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id'); // Lebih baik gunakan User::class
    }

    /**
     * Relasi ke Detail Transaksi.
     */
    public function details(): HasMany
    {
        return $this->hasMany(TransaksiDetail::class, 'transaksi_id');
    }
}
