<?php

namespace App\Http\Controllers;

use App\Models\UnitSatuan;
use App\Http\Requests\UnitSatuans\{StoreUnitSatuanRequest, UpdateUnitSatuanRequest};
use Illuminate\Contracts\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\{JsonResponse, RedirectResponse};
use Illuminate\Routing\Controllers\{HasMiddleware, Middleware};
// Tambahkan use DB jika diperlukan di masa depan, tapi saat ini Eloquent cukup
// use Illuminate\Support\Facades\DB;

class UnitSatuanController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('permission:unit satuan view', only: ['index', 'show']),
            new Middleware('permission:unit satuan create', only: ['create', 'store']),
            new Middleware('permission:unit satuan edit', only: ['edit', 'update']),
            new Middleware('permission:unit satuan delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            // Filter berdasarkan company_id dari session
            $unitSatuans = UnitSatuan::where('company_id', session('sessionCompany')); // Menggunakan Eloquent

            return DataTables::of($unitSatuans)
                ->addColumn('action', 'unit-satuan.include.action')
                ->toJson();
        }

        return view('unit-satuan.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('unit-satuan.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUnitSatuanRequest $request): RedirectResponse
    {
        $attr = $request->validated();
        // Tambahkan company_id dari session
        $attr['company_id'] = session('sessionCompany');

        UnitSatuan::create($attr);

        return to_route('unit-satuan.index')->with('success', __('The unit satuan was created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(UnitSatuan $unitSatuan): View
    {
        // Optional: Tambahkan validasi bahwa data ini milik company user
        if ($unitSatuan->company_id !== session('sessionCompany')) {
            abort(403, 'Unauthorized action.');
        }
        return view('unit-satuan.show', compact('unitSatuan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UnitSatuan $unitSatuan): View
    {
        // Optional: Tambahkan validasi bahwa data ini milik company user
        if ($unitSatuan->company_id !== session('sessionCompany')) {
            abort(403, 'Unauthorized action.');
        }
        return view('unit-satuan.edit', compact('unitSatuan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUnitSatuanRequest $request, UnitSatuan $unitSatuan): RedirectResponse
    {
        // Optional: Tambahkan validasi bahwa data ini milik company user
        if ($unitSatuan->company_id !== session('sessionCompany')) {
            abort(403, 'Unauthorized action.');
        }

        $attr = $request->validated();
        // Pastikan company_id tidak diubah saat update (jika tidak diinginkan)
        // unset($attr['company_id']); // Hapus baris ini jika company_id BOLEH diubah

        $unitSatuan->update($attr);

        return to_route('unit-satuan.index')->with('success', __('The unit satuan was updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UnitSatuan $unitSatuan): RedirectResponse
    {
        // Optional: Tambahkan validasi bahwa data ini milik company user
        if ($unitSatuan->company_id !== session('sessionCompany')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $unitSatuan->delete();

            return to_route('unit-satuan.index')->with('success', __('The unit satuan was deleted successfully.'));
        } catch (\Exception $e) {
            // Log error jika perlu
            \Illuminate\Support\Facades\Log::error("Error deleting UnitSatuan ID {$unitSatuan->id}: " . $e->getMessage());
            return to_route('unit-satuan.index')->with('error', __("The unit satuan can't be deleted because it's related to another table or an error occurred."));
        }
    }
}
