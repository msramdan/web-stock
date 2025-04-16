<?php

namespace App\Http\Requests\Barangs;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBarangRequest extends FormRequest
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
			'deskripsi_barang' => 'required|string',
			'jenis_material_id' => 'required|exists:App\Models\JenisMaterial,id',
			'unit_satuan_id' => 'required|exists:App\Models\UnitSatuan,id',
			'stock_barang' => 'nullable',
			'photo_barang' => 'nullable|image|max:4000',
        ];
    }
}
