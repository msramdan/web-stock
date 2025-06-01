<?php

namespace App\Http\Requests\PermintaanBarang;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermintaanBarangRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('permintaan barang create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $companyId = session('company_id') ?? auth()->user()->company_id; // Sesuaikan cara Anda mendapatkan company_id

        return [
            'tgl_pengajuan' => 'required|date',
            'no_permintaan_barang' => [
                'required',
                'string',
                'max:50',
                Rule::unique('permintaan', 'no_permintaan_barang')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })
            ],
            'nama_supplier' => 'required|string|max:150',
            'nama_bank' => 'nullable|string|max:100',
            'account_name_supplier' => 'nullable|string|max:150',
            'account_number_supplier' => 'nullable|string|max:25',
            'keterangan' => 'nullable|string',
            'include_ppn' => 'required|in:yes,no',
            // 'nominal_ppn' => 'required_if:include_ppn,yes|numeric|min:0', // Akan dihitung di controller
            // 'sub_total_pesanan' => 'required|numeric|min:0', // Akan dihitung di controller
            // 'total_pesanan' => 'required|numeric|min:0', // Akan dihitung di controller

            'details' => 'required|array|min:1',
            'details.*.barang_id' => 'required|exists:barang,id',
            'details.*.jumlah_pesanan' => 'required|numeric|min:0.01',
            'details.*.satuan' => 'required|string|max:50',
            'details.*.harga_per_satuan' => 'required|numeric|min:0',
            // 'details.*.stok_terakhir' => 'nullable|numeric', // Tidak perlu divalidasi dari input, diambil dari DB
        ];
    }

    public function messages()
    {
        return [
            'details.required' => 'Minimal harus ada satu barang yang diminta.',
            'details.min' => 'Minimal harus ada satu barang yang diminta.',
            'details.*.barang_id.required' => 'Barang pada detail permintaan harus dipilih.',
            'details.*.barang_id.exists' => 'Barang yang dipilih tidak valid.',
            'details.*.jumlah_pesanan.required' => 'Jumlah pesanan pada detail permintaan harus diisi.',
            'details.*.jumlah_pesanan.numeric' => 'Jumlah pesanan harus berupa angka.',
            'details.*.jumlah_pesanan.min' => 'Jumlah pesanan minimal 0.01.',
            'details.*.satuan.required' => 'Satuan pada detail permintaan harus diisi.',
            'details.*.harga_per_satuan.required' => 'Harga satuan pada detail permintaan harus diisi.',
            'details.*.harga_per_satuan.numeric' => 'Harga satuan harus berupa angka.',
            'details.*.harga_per_satuan.min' => 'Harga satuan minimal 0.',
        ];
    }
}
