<?php

namespace App\Http\Requests\Boms;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Sesuaikan dengan logic otorisasi Anda, misalnya cek permission
        return true; // return auth()->user()->can('bom create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'barang_id' => ['required', 'integer', Rule::exists('barang', 'id')], // Produk jadi harus ada
            'deskripsi' => ['required', 'string', 'max:65535'],
            'materials' => ['present', 'array'], // Harus ada, meskipun kosong (akan divalidasi di controller)
            'materials.*.barang_id' => ['required', 'integer', Rule::exists('barang', 'id')], // Material harus ada
            'materials.*.jumlah' => ['required', 'numeric', 'min:1'], // Jumlah harus angka > 0
            'materials.*.unit_satuan_id' => ['required', 'integer', Rule::exists('unit_satuan', 'id')], // Unit harus ada
            'kemasan' => ['nullable', 'array'],
            'kemasan.barang_id' => [
                'required_with:kemasan', // Wajib ada jika array 'kemasan' dikirim
                'nullable',              // Boleh null jika user memilih "-- Tidak menggunakan kemasan --"
                'integer',
                Rule::exists('barang', 'id')->where(function ($query) {
                    // Pastikan barang yang dipilih adalah tipe Kemasan
                    $query->where('tipe_barang', 'Kemasan')
                        ->where('company_id', session('sessionCompany'));
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
            'kemasan.*.jumlah' => 'Jumlah Kemasan',
            'kemasan.*.unit_satuan_id' => 'Unit Satuan Kemasan',
            'kemasan.*.kapasitas' => 'Kapasitas Kemasan',

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
            'materials.*.barang_id.required' => 'Material pada baris :position wajib dipilih.',
            'materials.*.jumlah.required' => 'Jumlah material pada baris :position wajib diisi.',
            'materials.*.jumlah.numeric' => 'Jumlah material pada baris :position harus berupa angka.',
            'materials.*.jumlah.min' => 'Jumlah material pada baris :position minimal :min.',
            'materials.*.unit_satuan_id.required' => 'Unit satuan material pada baris :position tidak terdeteksi atau tidak valid.',
            'kemasan.barang_id.exists' => 'Barang kemasan yang dipilih tidak valid atau bukan bertipe Kemasan.',

        ];
    }
}
