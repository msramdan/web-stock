<?php

namespace App\Http\Requests\Transaksis;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransaksiRequest extends FormRequest
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
            'no_surat' => 'required|string|max:255',
			'tanggal' => 'required',
			'type' => 'required|in:In,Out',
			'keterangan' => 'required|string',
			'attachment' => 'nullable|image|max:4000',
        ];
    }
}
