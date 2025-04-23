<?php

namespace App\Http\Requests\Companies;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
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
            'nama_perusahaan' => 'required|string|max:255',
			'no_telepon' => 'required|max:15',
			'email' => 'required|email|unique:company,email',
			'alamat' => 'required|string',
			'logo_perusahaan' => 'nullable|image|max:4000',
        ];
    }
}
