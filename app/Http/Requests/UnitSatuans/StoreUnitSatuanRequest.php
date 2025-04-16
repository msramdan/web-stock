<?php

namespace App\Http\Requests\UnitSatuans;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitSatuanRequest extends FormRequest
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
            'nama_unit_satuan' => 'required|string|max:100',
        ];
    }
}
