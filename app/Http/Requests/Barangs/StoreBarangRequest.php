<?php

namespace App\Http\Requests\Barangs;

use Illuminate\Foundation\Http\FormRequest;

class StoreBarangRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'kode_barang' => 'required|string|max:255',
            'nama_barang' => 'required|string|max:255',
            'deskripsi_barang' => 'required|string',
            'tipe_barang' => 'required|string|in:Bahan Baku,Barang Jadi,Kemasan',
            'jenis_material_id' => 'required|exists:App\Models\JenisMaterial,id',
            'unit_satuan_id' => 'required|exists:App\Models\UnitSatuan,id',
            'harga' => 'required_if:tipe_barang,Bahan Baku|numeric|min:0|nullable',
            'kapasitas' => 'required_if:tipe_barang,Kemasan|numeric|min:0|nullable',
            'stock_barang' => 'nullable',
            'photo_barang' => 'nullable|image|max:4000',
        ];
    }
}
