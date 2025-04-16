<?php

namespace App\Http\Controllers;

use App\Models\UnitSatuan;
use App\Http\Requests\UnitSatuans\{StoreUnitSatuanRequest, UpdateUnitSatuanRequest};
use Illuminate\Contracts\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\{JsonResponse, RedirectResponse};
use Illuminate\Routing\Controllers\{HasMiddleware, Middleware};

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
            $unitSatuans = UnitSatuan::query();

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

        UnitSatuan::create($request->validated());

        return to_route('unit-satuan.index')->with('success', __('The unit satuan was created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(UnitSatuan $unitSatuan): View
    {
        return view('unit-satuan.show', compact('unitSatuan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UnitSatuan $unitSatuan): View
    {
        return view('unit-satuan.edit', compact('unitSatuan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUnitSatuanRequest $request, UnitSatuan $unitSatuan): RedirectResponse
    {

        $unitSatuan->update($request->validated());

        return to_route('unit-satuan.index')->with('success', __('The unit satuan was updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UnitSatuan $unitSatuan): RedirectResponse
    {
        try {
            $unitSatuan->delete();

            return to_route('unit-satuan.index')->with('success', __('The unit satuan was deleted successfully.'));
        } catch (\Exception $e) {
            return to_route('unit-satuan.index')->with('error', __("The unit satuan can't be deleted because it's related to another table."));
        }
    }
}
