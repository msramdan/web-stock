<?php

namespace App\Http\Requests\Boms;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Sesuaikan dengan logic otorisasi Anda, misalnya cek permission 'bom edit'
        // return auth()->user()->can('bom edit');
        return true; // Ganti dengan logic otorisasi yang sesuai
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $bomId = $this->route('bom')->id;
        $companyId = session('sessionCompany');

        return [
            'barang_id' => [
                'required',
                'integer',
                Rule::exists('barang', 'id')->where('company_id', $companyId),
            ],
            'deskripsi' => ['required', 'string', 'max:65535'],

            // Validasi untuk array materials
            'materials' => ['present', 'array'],
            'materials.*.detail_id' => [
                'nullable',
                'integer',
                Rule::exists('bom_detail', 'id')->where('bom_id', $bomId),
            ],
            'materials.*.barang_id' => [
                'required',
                'integer',
                Rule::exists('barang', 'id')->where('company_id', $companyId),
            ],
            'materials.*.jumlah' => ['required', 'numeric', 'min:0.00000001'],
            'materials.*.unit_satuan_id' => ['required', 'integer', Rule::exists('unit_satuan', 'id')->where('company_id', $companyId)],


            'kemasan' => ['nullable', 'array'],
            'kemasan.barang_id' => [
                'nullable', // Boleh kosong jika tidak memilih kemasan
                'integer',
                Rule::exists('barang', 'id')->where(function ($query) use ($companyId) {
                    $query->where('tipe_barang', 'Kemasan')
                        ->where('company_id', $companyId);
                }),
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'barang_id' => 'Barang (Produk Jadi)',
            'deskripsi' => 'Deskripsi',
            'materials' => 'Material / Komponen',
            'materials.*.barang_id' => 'Material',
            'materials.*.jumlah' => 'Jumlah Material',
            'materials.*.unit_satuan_id' => 'Unit Satuan Material',
            'kemasan.barang_id' => 'Barang Kemasan',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'materials.present' => 'Data material harus disertakan.',
            'materials.array' => 'Data material harus berupa array.',
            'materials.*.detail_id.exists' => 'Detail material pada baris :position tidak valid atau bukan milik BoM ini.',
            'materials.*.barang_id.required' => 'Material pada baris :position wajib dipilih.',
            'materials.*.barang_id.exists' => 'Material yang dipilih pada baris :position tidak valid.',
            // 'materials.*.barang_id.not_in' => 'Material pada baris :position tidak boleh sama dengan Produk Jadi.', // Jika validasi notIn diaktifkan
            'materials.*.jumlah.required' => 'Jumlah material pada baris :position wajib diisi.',
            'materials.*.jumlah.numeric' => 'Jumlah material pada baris :position harus berupa angka.',
            'materials.*.jumlah.min' => 'Jumlah material pada baris :position minimal :min.',
            'materials.*.unit_satuan_id.required' => 'Unit satuan material pada baris :position wajib diisi.',
            'materials.*.unit_satuan_id.exists' => 'Unit satuan yang dipilih pada baris :position tidak valid.',

            'kemasan.*.barang_id.required_with' => 'Barang kemasan pada baris :position wajib dipilih.',
            'kemasan.*.barang_id.exists' => 'Barang kemasan yang dipilih pada baris :position tidak valid.',
            'kemasan.*.jumlah.required_with' => 'Jumlah kemasan pada baris :position wajib diisi.',
            'kemasan.*.jumlah.numeric' => 'Jumlah kemasan pada baris :position harus berupa angka.',
            'kemasan.*.jumlah.min' => 'Jumlah kemasan pada baris :position minimal :min.',
            'kemasan.*.unit_satuan_id.required_with' => 'Unit satuan kemasan pada baris :position wajib diisi.',
            'kemasan.*.unit_satuan_id.exists' => 'Unit satuan kemasan yang dipilih pada baris :position tidak valid.',

            'kemasan.*.kapasitas.required_with' => 'Kapasitas kemasan pada baris :position wajib diisi.',
            'kemasan.*.kapasitas.numeric' => 'Kapasitas kemasan pada baris :position harus berupa angka.',
            'kemasan.*.kapasitas.min' => 'Kapasitas kemasan pada baris :position minimal :min.',
        ];
    }
}
