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
        // Dapatkan ID BoM dari route parameter
        $bomId = $this->route('bom') ? $this->route('bom')->id : null;

        return [
            // Validasi untuk data BoM utama
            'barang_id' => [
                'required',
                'integer',
                Rule::exists('barang', 'id')
            ],
            'deskripsi' => ['required', 'string', 'max:65535'],

            // Validasi untuk array materials
            'materials' => ['present', 'array'], // Harus ada, minimal array kosong (akan divalidasi di controller jika butuh minimal 1 item)

            // Validasi untuk setiap item dalam array materials
            'materials.*.detail_id' => [ // ID detail yang sudah ada (opsional)
                'sometimes', // Hanya ada jika ini adalah update detail yang sudah ada
                'nullable', // Bisa null jika baris baru
                'integer',
                // Pastikan detail_id yang dikirim benar-benar ada dan terkait dengan BoM ini
                Rule::exists('bom_detail', 'id')->where(function ($query) use ($bomId) {
                    if ($bomId) {
                        $query->where('bom_id', $bomId);
                    }
                }),
            ],
            'materials.*.barang_id' => [ // ID Material/Komponen
                'required',
                'integer',
                Rule::exists('barang', 'id'),
                // Opsional: Pastikan barang_id yang dipilih tidak sama dengan barang_id produk jadi
                // Rule::notIn([$this->input('barang_id')]),
            ],
            'materials.*.jumlah' => [ // Jumlah material
                'required',
                'numeric',
                'min:0.01' // Atau sesuai kebutuhan minimal Anda
            ],
            'materials.*.unit_satuan_id' => [ // Unit satuan material
                'required',
                'integer',
                Rule::exists('unit_satuan', 'id')
                // Opsional: Anda bisa menambahkan validasi untuk memastikan unit_satuan_id cocok dengan barang_id yang dipilih,
                // tapi ini mungkin lebih kompleks dan bisa dilakukan di controller atau service layer.
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
            'materials.*.detail_id' => 'ID Detail Material',
            'materials.*.barang_id' => 'Material',
            'materials.*.jumlah' => 'Jumlah Material',
            'materials.*.unit_satuan_id' => 'Unit Satuan Material',
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
        ];
    }
}
