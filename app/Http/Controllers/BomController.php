<?php

namespace App\Http\Controllers;

use App\Models\Bom;
use App\Http\Requests\Boms\{StoreBomRequest, UpdateBomRequest};
use Illuminate\Contracts\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\{JsonResponse, RedirectResponse};
use Illuminate\Routing\Controllers\{HasMiddleware, Middleware};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class BomController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('permission:bom view', only: ['index', 'show']),
            new Middleware('permission:bom create', only: ['create', 'store']),
            new Middleware('permission:bom edit', only: ['edit', 'update']),
            new Middleware('permission:bom delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */

    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            $boms = DB::table('bom')
                ->leftJoin('barang', 'bom.barang_id', '=', 'barang.id')
                ->select(
                    'bom.*',
                    'barang.kode_barang',
                    'barang.nama_barang'
                );
    
            return DataTables::of($boms)
                ->addColumn('deskripsi', function ($row) {
                    return Str::limit($row->deskripsi, 100);
                })
                ->addColumn('action', 'bom.include.action')
                ->toJson();
        }
    
        return view('bom.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('bom.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBomRequest $request): RedirectResponse
    {

        Bom::create($request->validated());

        return to_route('bom.index')->with('success', __('The bom was created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Bom $bom): View
    {
        $bom->load(['barang:id']);

        return view('bom.show', compact('bom'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bom $bom): View
    {
        $bom->load(['barang:id']);

        return view('bom.edit', compact('bom'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBomRequest $request, Bom $bom): RedirectResponse
    {

        $bom->update($request->validated());

        return to_route('bom.index')->with('success', __('The bom was updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bom $bom): RedirectResponse
    {
        try {
            $bom->delete();

            return to_route('bom.index')->with('success', __('The bom was deleted successfully.'));
        } catch (\Exception $e) {
            return to_route('bom.index')->with('error', __("The bom can't be deleted because it's related to another table."));
        }
    }
}
