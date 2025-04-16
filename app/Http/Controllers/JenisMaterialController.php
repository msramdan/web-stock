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
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            $jenisMaterials = JenisMaterial::query();

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
        return view('jenis-material.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJenisMaterialRequest $request): RedirectResponse
    {

        JenisMaterial::create($request->validated());

        return to_route('jenis-material.index')->with('success', __('The jenis material was created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(JenisMaterial $jenisMaterial): View
    {
        return view('jenis-material.show', compact('jenisMaterial'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JenisMaterial $jenisMaterial): View
    {
        return view('jenis-material.edit', compact('jenisMaterial'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJenisMaterialRequest $request, JenisMaterial $jenisMaterial): RedirectResponse
    {

        $jenisMaterial->update($request->validated());

        return to_route('jenis-material.index')->with('success', __('The jenis material was updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JenisMaterial $jenisMaterial): RedirectResponse
    {
        try {
            $jenisMaterial->delete();

            return to_route('jenis-material.index')->with('success', __('The jenis material was deleted successfully.'));
        } catch (\Exception $e) {
            return to_route('jenis-material.index')->with('error', __("The jenis material can't be deleted because it's related to another table."));
        }
    }
}
