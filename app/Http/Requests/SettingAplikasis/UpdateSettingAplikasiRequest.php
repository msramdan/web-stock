<?php

namespace App\Http\Requests\SettingAplikasis;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingAplikasiRequest extends FormRequest
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
            'nama_aplikasi' => 'required|string|max:255',
			'nama_perusahaan' => 'required|string|max:255',
			'no_telepon' => 'required|max:15',
			'email' => 'required|string|max:100',
			'alamat' => 'required|string',
			'logo_perusahaan' => 'nullable|image|max:4000',
        ];
    }
}
