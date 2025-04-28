<?php

namespace App\Http\Controllers;

use App\Models\JenisMaterial;
use App\Http\Requests\JenisMaterials\{StoreJenisMaterialRequest, UpdateJenisMaterialRequest};
use Illuminate\Contracts\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\{JsonResponse, RedirectResponse};
use Illuminate\Routing\Controllers\{HasMiddleware, Middleware};

class JenisMaterialController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('permission:jenis material view', only: ['index', 'show']),
            new Middleware('permission:jenis material create', only: ['create', 'store']),
            new Middleware('permission:jenis material edit', only: ['edit', 'update']),
            new Middleware('permission:jenis material delete', only: ['destroy']),
            // Tambahkan middleware company.access jika sudah dibuat dan ingin diterapkan di level controller
            // new Middleware(\App\Http\Middleware\CheckCompanyAccess::class),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            $jenisMaterials = JenisMaterial::where('company_id', session('sessionCompany'));

            return DataTables::of($jenisMaterials)
                ->addColumn('action', 'jenis-material.include.action')
                ->toJson();
        }

        return view('jenis-material.index');
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        // Tidak perlu validasi company di create
        return view('jenis-material.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJenisMaterialRequest $request): RedirectResponse
    {
        $attr = $request->validated();
        $attr['company_id'] =  session('sessionCompany'); // Sudah benar
        JenisMaterial::create($attr);
        // Pesan sudah diubah ke Bahasa Indonesia di langkah sebelumnya
        return to_route('jenis-material.index')->with('success', __('Jenis material berhasil dibuat.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(JenisMaterial $jenisMaterial): View
    {
        // --- TAMBAHKAN VALIDASI COMPANY ---
        if ($jenisMaterial->company_id != session('sessionCompany')) {
            abort(403, 'Akses ditolak.');
        }
        // --- AKHIR VALIDASI ---
        return view('jenis-material.show', compact('jenisMaterial'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JenisMaterial $jenisMaterial): View
    {
        // --- TAMBAHKAN VALIDASI COMPANY ---
        if ($jenisMaterial->company_id != session('sessionCompany')) {
            abort(403, 'Akses ditolak.');
        }
        // --- AKHIR VALIDASI ---
        return view('jenis-material.edit', compact('jenisMaterial'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJenisMaterialRequest $request, JenisMaterial $jenisMaterial): RedirectResponse
    {
        // --- TAMBAHKAN VALIDASI COMPANY ---
        if ($jenisMaterial->company_id != session('sessionCompany')) {
            abort(403, 'Akses ditolak.');
        }
        // --- AKHIR VALIDASI ---

        // Validated data sudah benar
        $attr = $request->validated();
        // Pastikan company_id tidak ikut terupdate secara tidak sengaja
        // unset($attr['company_id']); // Uncomment jika company ID tidak boleh diubah

        $jenisMaterial->update($attr);

        // Pesan sudah diubah ke Bahasa Indonesia di langkah sebelumnya
        return to_route('jenis-material.index')->with('success', __('Jenis material berhasil diperbarui.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JenisMaterial $jenisMaterial): RedirectResponse
    {
        // --- TAMBAHKAN VALIDASI COMPANY ---
        if ($jenisMaterial->company_id != session('sessionCompany')) {
            abort(403, 'Akses ditolak.');
        }
        // --- AKHIR VALIDASI ---

        try {
            $jenisMaterial->delete();
            // Pesan sudah diubah ke Bahasa Indonesia di langkah sebelumnya
            return to_route('jenis-material.index')->with('success', __('Jenis material berhasil dihapus.'));
        } catch (\Exception $e) {
            // Pesan sudah diubah ke Bahasa Indonesia di langkah sebelumnya
            return to_route('jenis-material.index')->with('error', __("Jenis material tidak dapat dihapus karena terkait dengan tabel lain atau terjadi kesalahan."));
        }
    }
}
