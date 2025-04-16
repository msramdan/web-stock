<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingAplikasi extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'setting_aplikasi';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['nama_aplikasi', 'nama_perusahaan', 'no_telepon', 'email', 'alamat', 'logo_perusahaan'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['nama_aplikasi' => 'string', 'nama_perusahaan' => 'string', 'no_telepon' => 'string', 'email' => 'string', 'alamat' => 'string', 'logo_perusahaan' => 'string', 'created_at' => 'datetime:Y-m-d H:i:s', 'updated_at' => 'datetime:Y-m-d H:i:s'];
    }


}
